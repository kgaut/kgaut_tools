<?php

/**
 * @file
 * Minimal stub for the drupal/pathauto contrib module.
 *
 * Only used by PHPStan via `parameters.bootstrapFiles` so the analyser can
 * type-check StringCleaner without forcing the contrib module to be present
 * on every analysis run. The real interface always wins when installed.
 */

declare(strict_types=1);

namespace Drupal\pathauto;

if (!interface_exists(AliasCleanerInterface::class, FALSE)) {
  /**
   * Stub of \Drupal\pathauto\AliasCleanerInterface.
   */
  interface AliasCleanerInterface {

    /**
     * Cleans up a string for use as an alias component.
     */
    public function cleanString(string $string, array $options = []): string;

  }
}
