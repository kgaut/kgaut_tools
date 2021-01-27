<?php

namespace Drupal\kgaut_tools;
use Drupal\clearblue\Services\ClearblueLanguageManager;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\locale\StringStorageInterface;

/**
 * Class TranslationImporter.
 */
class TranslationImporter {

  /**
   * Drupal\locale\StringStorageInterface definition.
   *
   * @var \Drupal\locale\StringStorageInterface
   */
  protected $localeStorage;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new TranslationImporter object.
   */
   public function __construct(StringStorageInterface $locale_storage, LanguageManagerInterface $languageManager) {
     $this->languageManager = $languageManager;
     if(!\Drupal::moduleHandler()->moduleExists('locale')) {
       \Drupal::messenger()->addError(t('Module Locale has to be enabled.'));
       \Drupal::logger('kgaut_tools')->error(t('Module Locale is not enabled.'));
     }
     $this->localeStorage = \Drupal::service('locale.storage');
   }

  public function importTranslation($source, $langcode, $translation) {
    if(!$this->isLangcodeValid($langcode)) {
      \Drupal::logger('kgaut_tools')->error(t('Langcode @langcode not valid', ['@langcode' => $langcode]));
      return;
    }
    $strings = $this->localeStorage->getStrings(['source' => $source]);
    foreach ($strings as $string) {
      if(!$string) {
        $string = new \Drupal\locale\SourceString();
        $string->setString($source);
        $string->setStorage($this->localeStorage);
        $string->save();
      }
      if(trim($translation) === '') {
        continue;
      }
      $stringTranslations = $this->localeStorage->getTranslations([
        'language' => $langcode,
        'lid' => $string->lid,
        'context' => $string->getValues(['context'])['context'],
      ]);
      if($stringTranslations) {
        foreach ($stringTranslations as $stringTranslation) {
          $stringTranslation->delete();
        }
      }
      try {
        $target = $this->localeStorage->createTranslation([
          'lid' => $string->lid,
          'language' => $langcode,
          'translation' => $translation,
        ])->save();
        if($target) {
          \Drupal::logger('kgaut_tools')->info(t('<em>@source</em> translated to <em>@translation</em> in <code>@langcode</code>', ['@source' => $source, '@translation' => $translation, '@langcode' => $langcode]));
        }
      }
      catch (\Exception $e) {
        \Drupal::logger('kgaut_tools')->error($e->getFile() . ' L' . $e->getLine() . ' ' . $e->getMessage());
      }
    }
  }

  protected function isLangcodeValid($langcode) {
    if($language = $this->languageManager->getLanguage($langcode)) {
       return TRUE;
    }
    return FALSE;
  }

}
