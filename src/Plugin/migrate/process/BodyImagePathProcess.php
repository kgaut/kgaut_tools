<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Plugin\migrate\process;

use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\file\FileRepositoryInterface;
use Drupal\kgaut_tools\StringCleanerInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Rewrites image and asset URLs found in HTML during migrations.
 *
 * @MigrateProcessPlugin(
 *   id = "body_image_path_process"
 * )
 */
final class BodyImagePathProcess extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Attributes whose URLs should be rewritten.
   */
  private const REWRITTEN_ATTRIBUTES = ['src', 'href'];

  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    private readonly StringCleanerInterface $stringCleaner,
    private readonly FileSystemInterface $fileSystem,
    private readonly FileRepositoryInterface $fileRepository,
    private readonly LoggerChannelInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('kgaut_tools.stringcleaner'),
      $container->get('file_system'),
      $container->get('file.repository'),
      $container->get('logger.channel.kgaut_tools'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_string($value) || $value === '') {
      return $value;
    }

    foreach (self::REWRITTEN_ATTRIBUTES as $attribute) {
      $value = $this->rewriteAssets($value, $row, $attribute);
    }
    return $value;
  }

  /**
   * Rewrites either <img src="..."> or <a href="..."> URLs in the markup.
   */
  private function rewriteAssets(string $html, Row $row, string $attribute): string {
    $sources = $this->extractAssets($html, $attribute);
    if ($sources === []) {
      return $html;
    }

    $replacements = [];
    foreach ($sources as $filepath) {
      $public_uri = $this->processAsset($filepath, $row);
      if ($public_uri === NULL) {
        continue;
      }
      $replacements[$attribute . '="' . $filepath] = $attribute . '="' . $public_uri;
      $replacements[$attribute . "='" . $filepath] = $attribute . "='" . $public_uri;
    }

    return $replacements === [] ? $html : strtr($html, $replacements);
  }

  /**
   * Extracts the unique list of URLs to rewrite for the given attribute.
   *
   * @return string[]
   *   The list of URLs found in the HTML for the given attribute.
   */
  private function extractAssets(string $html, string $attribute): array {
    $found = [];

    if ($attribute === 'src') {
      preg_match_all('/<img[^>]+>/i', $html, $tags);
      foreach ($tags[0] as $tag) {
        if (preg_match('/src=("[^"]*"|\'[^\']*\')/i', $tag, $attr)) {
          $found[] = trim($attr[1], "\"'");
        }
      }
    }
    elseif ($attribute === 'href') {
      preg_match_all('/href=("[^"]+\.(?:png|jpg|jpeg|gif|svg)"|\'[^\']+\.(?:png|jpg|jpeg|gif|svg)\')/i', $html, $tags);
      foreach ($tags[1] as $value) {
        $found[] = trim($value, "\"'");
      }
    }

    return array_values(array_unique(array_filter($found)));
  }

  /**
   * Downloads and writes a single asset, returning its new public URI.
   */
  private function processAsset(string $filepath, Row $row): ?string {
    $destination_root = (string) ($this->configuration['images_destination'] ?? 'public://');
    $images_source = (string) ($this->configuration['images_source'] ?? '');
    $url_source = (string) ($this->configuration['url_source'] ?? '');
    $replace = (bool) ($this->configuration['replace'] ?? FALSE);
    $rename = (bool) ($this->configuration['rename'] ?? FALSE);
    $auth = $this->configuration['auth'] ?? FALSE;
    $url_to_replace = $this->configuration['url_to_replace'] ?? FALSE;

    $pathinfo = pathinfo($filepath);
    $filename = $pathinfo['basename'];
    if (($qpos = strpos($filename, '?')) !== FALSE) {
      $filename = substr($filename, 0, $qpos);
    }
    $path = $pathinfo['dirname'] ?? '';

    if ($rename) {
      $title_clean = $this->stringCleaner->clean((string) $row->getSourceProperty('title'));
      $destination_dir = $destination_root . $title_clean;
      $destination_filename = $title_clean . '-' . urldecode($filename);
    }
    else {
      $destination_dir = $destination_root . str_replace($images_source, '', $path);
      $destination_filename = urldecode($filename);
    }

    $destination = $destination_dir . '/' . $destination_filename;
    $public_uri = str_replace('public://', '/' . PublicStream::basePath() . '/', $destination);

    if (file_exists($destination) && !$replace) {
      return $public_uri;
    }

    if (!$this->fileSystem->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY)) {
      $this->logger->error('Error creating folder @folder', ['@folder' => $destination_dir]);
      return NULL;
    }

    $contents = $this->fetchContents($filepath, $url_source, $url_to_replace, $auth);
    if ($contents === NULL) {
      $this->logger->error('Error getting content of remote file @file', ['@file' => $filepath]);
      return NULL;
    }

    try {
      $this->fileRepository->writeData($contents, $destination, FileExists::Replace);
      return $public_uri;
    }
    catch (\Throwable $exception) {
      $this->logger->error('Error saving file @file: @message', [
        '@file' => $destination,
        '@message' => $exception->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * Fetches the remote contents for an asset, returning NULL on failure.
   */
  private function fetchContents(string $filepath, string $url_source, string|false $url_to_replace, string|false $auth): ?string {
    $context = NULL;
    if (is_string($auth) && $auth !== '') {
      $context = stream_context_create([
        'http' => ['header' => 'Authorization: Basic ' . $auth],
      ]);
    }

    if (filter_var($filepath, FILTER_VALIDATE_URL)) {
      if (is_string($url_to_replace) && $url_to_replace !== '') {
        $filepath = str_replace($url_to_replace, $url_source, $filepath);
      }
      $url = $filepath;
    }
    else {
      $url = $url_source . $filepath;
    }

    $contents = @file_get_contents($url, FALSE, $context);
    return ($contents === FALSE || $contents === '') ? NULL : $contents;
  }

}
