<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools;

use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\pathauto\AliasCleanerInterface;

/**
 * Default implementation of StringCleanerInterface.
 */
final class StringCleaner implements StringCleanerInterface {

  public function __construct(
    private readonly PhpTransliteration $transliteration,
    private readonly AliasCleanerInterface $pathautoAliasCleaner,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function clean(string $string, bool $no_dash = FALSE): string {
    $cleaned = $this->transliteration->transliterate($string);
    $cleaned = $this->pathautoAliasCleaner->cleanString($cleaned);
    if ($no_dash) {
      $cleaned = str_replace('-', '_', $cleaned);
    }
    return $cleaned;
  }

}
