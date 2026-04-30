<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\locale\SourceString;
use Drupal\locale\StringStorageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Imports translations for a given source string and language.
 */
final class TranslationImporter {

  use StringTranslationTrait;

  public function __construct(
    private readonly StringStorageInterface $localeStorage,
    private readonly LanguageManagerInterface $languageManager,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly MessengerInterface $messenger,
    #[Autowire(service: 'logger.channel.kgaut_tools')]
    private readonly LoggerChannelInterface $logger,
  ) {}

  /**
   * Imports a translation, creating the source string when it does not exist.
   *
   * @param string $source
   *   The source string (in the site's default language).
   * @param string $langcode
   *   The target language code.
   * @param string $translation
   *   The translated string. Empty values are skipped.
   */
  public function importTranslation(string $source, string $langcode, string $translation): void {
    if (!$this->moduleHandler->moduleExists('locale')) {
      $this->messenger->addError($this->t('Module Locale has to be enabled.'));
      $this->logger->error('Module Locale is not enabled.');
      return;
    }

    if (!$this->isLangcodeValid($langcode)) {
      $this->logger->error('Langcode @langcode not valid', ['@langcode' => $langcode]);
      return;
    }

    if (trim($translation) === '') {
      return;
    }

    $strings = $this->localeStorage->getStrings(['source' => $source]);
    if ($strings === []) {
      $string = new SourceString();
      $string->setString($source);
      $string->setStorage($this->localeStorage);
      $string->save();
      $strings = [$string];
    }

    foreach ($strings as $string) {
      $this->replaceTranslation($string, $langcode, $translation);
    }
  }

  /**
   * Replaces (or creates) the translation for a given source string.
   */
  private function replaceTranslation(SourceString $string, string $langcode, string $translation): void {
    $existing = $this->localeStorage->getTranslations([
      'language' => $langcode,
      'lid' => $string->lid,
      'context' => $string->getValues(['context'])['context'] ?? '',
    ]);
    foreach ($existing as $existing_translation) {
      $existing_translation->delete();
    }

    try {
      $this->localeStorage->createTranslation([
        'lid' => $string->lid,
        'language' => $langcode,
        'translation' => $translation,
      ])->save();
      $this->logger->info('<em>@source</em> translated to <em>@translation</em> in <code>@langcode</code>', [
        '@source' => $string->getString(),
        '@translation' => $translation,
        '@langcode' => $langcode,
      ]);
    }
    catch (\Exception $exception) {
      $this->logger->error('@file L@line @message', [
        '@file' => $exception->getFile(),
        '@line' => $exception->getLine(),
        '@message' => $exception->getMessage(),
      ]);
    }
  }

  /**
   * Returns TRUE when the language code is enabled on the site.
   */
  private function isLangcodeValid(string $langcode): bool {
    return $this->languageManager->getLanguage($langcode) !== NULL;
  }

}
