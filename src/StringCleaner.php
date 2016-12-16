<?php

namespace Drupal\kgaut_tools;
use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\pathauto\AliasCleaner;

/**
 * Class StringCleaner.
 *
 * @package Drupal\kgaut_tools
 */
class StringCleaner implements StringCleanerInterface {

  /**
   * Drupal\Core\Transliteration\PhpTransliteration definition.
   *
   * @var \Drupal\Core\Transliteration\PhpTransliteration
   */
  protected $transliteration;
  /**
   * Drupal\pathauto\AliasCleaner definition.
   *
   * @var \Drupal\pathauto\AliasCleaner
   */
  protected $pathautoAliasCleaner;
  /**
   * Constructor.
   */
  public function __construct(PhpTransliteration $transliteration, AliasCleaner $pathauto_alias_cleaner) {
    $this->transliteration = $transliteration;
    $this->pathautoAliasCleaner = $pathauto_alias_cleaner;
  }

  public function clean($string, $no_dash = FALSE) {
    $string = $this->transliteration->transliterate($string);
    $string = $this->pathautoAliasCleaner->cleanString($string);
    if ($no_dash) {
      $string = str_replace('-', '_', $string);
    }
    return $string;
  }

}
