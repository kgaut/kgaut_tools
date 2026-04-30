<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools;

/**
 * Cleans arbitrary strings into safe, machine-readable identifiers.
 */
interface StringCleanerInterface {

  /**
   * Returns a transliterated, alias-clean version of the given string.
   *
   * @param string $string
   *   The raw input string.
   * @param bool $no_dash
   *   When TRUE, dashes are replaced with underscores (useful for machine
   *   names).
   *
   * @return string
   *   The cleaned string.
   */
  public function clean(string $string, bool $no_dash = FALSE): string;

}
