<?php

/**
 * @file
 * Bootstrap for kgaut_tools unit tests.
 *
 * The unit tests intentionally avoid full Drupal bootstrapping. Each test
 * provides its own narrow stubs for the Drupal services it touches.
 */

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
  fwrite(STDERR, "Run `composer install` first.\n");
  exit(1);
}

require $autoload;
