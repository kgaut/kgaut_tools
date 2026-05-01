<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Hook;

use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Theme, preprocess and theme-suggestions hook implementations.
 */
final class ThemeHooks {

  public function __construct(
    private readonly ThemeManagerInterface $themeManager,
    private readonly ThemeHandlerInterface $themeHandler,
    private readonly ThemeExtensionList $themeExtensionList,
    private readonly RequestStack $requestStack,
    private readonly RouteMatchInterface $routeMatch,
    private readonly Settings $settings,
  ) {}

  /**
   * Implements hook_preprocess().
   *
   * Adds frequently used path/URL helpers to every template.
   */
  #[Hook('preprocess')]
  public function preprocess(array &$variables, string $hook): void {
    $active_theme = $this->themeManager->getActiveTheme();
    $is_https = $this->isHttps();

    $base_path = base_path();
    $front_url = Url::fromRoute('<front>', [], [
      'absolute' => TRUE,
      'https' => $is_https,
    ])->toString();

    $default_theme_path = $base_path . $this->themeHandler
      ->getTheme($this->themeHandler->getDefault())
      ->getPath();

    $public_files_path = PublicStream::basePath();

    $variables['basepath'] = $base_path;
    $variables['baseurl_front'] = $front_url;
    $variables['pathtotheme'] = $base_path . $active_theme->getPath();
    $variables['path_default_theme'] = $default_theme_path;
    $variables['baseurl_theme'] = $front_url . '/' . $active_theme->getPath();
    $variables['baseurl_default_theme'] = $front_url . $default_theme_path;
    $variables['pathtotfiles'] = $public_files_path;
    $variables['baseurl_files'] = $front_url . $public_files_path;
  }

  /**
   * Implements hook_page_attachments().
   *
   * Mirrors the preprocess variables in drupalSettings for JS consumers.
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments): void {
    $active_theme_name = $this->themeManager->getActiveTheme()->getName();
    $attachments['#attached']['drupalSettings']['basepath'] = base_path();
    $attachments['#attached']['drupalSettings']['pathtotheme'] = base_path() . $this->themeExtensionList->getPath($active_theme_name);
    $attachments['#attached']['drupalSettings']['pathtotfiles'] = PublicStream::basePath();
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for user templates.
   */
  #[Hook('theme_suggestions_user')]
  public function themeSuggestionsUser(array $variables): array {
    $sanitized_view_mode = $this->sanitize($variables['elements']['#view_mode']);
    return [$variables['theme_hook_original'] . '__' . $sanitized_view_mode];
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for node templates.
   */
  #[Hook('theme_suggestions_node')]
  public function themeSuggestionsNode(array $variables): array {
    $node = $variables['elements']['#node'] ?? NULL;
    if (!$node instanceof NodeInterface) {
      return [];
    }

    $base = $variables['theme_hook_original'];
    $sanitized_view_mode = $this->sanitize($variables['elements']['#view_mode']);
    $sanitized_bundle = $this->sanitize($node->bundle());

    return [
      $base . '__' . $sanitized_view_mode,
      $base . '__' . $sanitized_bundle,
      $base . '__' . $sanitized_bundle . '__' . $sanitized_view_mode,
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for taxonomy term templates.
   */
  #[Hook('theme_suggestions_taxonomy_term')]
  public function themeSuggestionsTaxonomyTerm(array $variables): array {
    $term = $variables['elements']['#taxonomy_term'] ?? NULL;
    if (!$term instanceof TermInterface) {
      return [];
    }

    $base = $variables['theme_hook_original'];
    $sanitized_view_mode = $this->sanitize($variables['elements']['#view_mode']);
    $sanitized_bundle = $this->sanitize($term->bundle());

    return [
      $base . '__' . $sanitized_view_mode,
      $base . '__' . $sanitized_bundle . '__' . $sanitized_view_mode,
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for page templates.
   *
   * Adds page--node--<bundle>.html.twig suggestions on canonical node routes.
   */
  #[Hook('theme_suggestions_page')]
  public function themeSuggestionsPage(array $variables): array {
    if ($this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return [];
    }

    $node = $this->routeMatch->getParameter('node');
    if (!$node instanceof NodeInterface) {
      return [];
    }

    return [$variables['theme_hook_original'] . '__node__' . $node->bundle()];
  }

  /**
   * Replaces dots with underscores so values are safe to use as suggestions.
   */
  private function sanitize(string $value): string {
    return strtr($value, '.', '_');
  }

  /**
   * Determines whether the current request is HTTPS (or forced via settings).
   */
  private function isHttps(): bool {
    $request = $this->requestStack->getCurrentRequest();
    if ($request !== NULL && $request->isSecure()) {
      return TRUE;
    }
    return (bool) $this->settings->get('force_https', FALSE);
  }

}
