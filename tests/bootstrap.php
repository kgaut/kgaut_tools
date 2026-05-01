<?php

/**
 * @file
 * Bootstrap for kgaut_tools unit tests.
 *
 * Registers the Drupal module namespaces that aren't exposed through the
 * default Composer PSR-4 mappings (core modules under
 * vendor/drupal/core/modules and contrib modules under vendor/drupal/*) so
 * the test suite can import classes such as Drupal\user\UserInterface or
 * Drupal\paragraphs\ParagraphInterface.
 */

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
  fwrite(STDERR, "Run `composer install` first.\n");
  exit(1);
}

/**
 * The Composer autoloader instance, used to register extra PSR-4 prefixes.
 *
 * @var \Composer\Autoload\ClassLoader $loader
 */
$loader = require $autoload;

$register_namespaces = static function (string $extensions_dir) use ($loader): void {
  if (!is_dir($extensions_dir)) {
    return;
  }
  foreach (scandir($extensions_dir) as $entry) {
    if ($entry === '.' || $entry === '..') {
      continue;
    }
    $extension_path = $extensions_dir . '/' . $entry;
    if (is_dir($extension_path . '/src')) {
      $loader->addPsr4('Drupal\\' . $entry . '\\', $extension_path . '/src');
    }
    if (is_dir($extension_path . '/tests/src')) {
      $loader->addPsr4('Drupal\\Tests\\' . $entry . '\\', $extension_path . '/tests/src');
    }
  }
};

// Drupal core's modules (user, node, taxonomy, ...).
$register_namespaces(__DIR__ . '/../vendor/drupal/core/modules');

// Contrib modules installed via composer (paragraphs, pathauto, ...).
$contrib_dir = __DIR__ . '/../vendor/drupal';
if (is_dir($contrib_dir)) {
  foreach (scandir($contrib_dir) as $entry) {
    if ($entry === '.' || $entry === '..' || $entry === 'core' || $entry === 'core-dev') {
      continue;
    }
    $module_path = $contrib_dir . '/' . $entry;
    if (is_dir($module_path . '/src')) {
      $loader->addPsr4('Drupal\\' . $entry . '\\', $module_path . '/src');
    }
  }
}

// The module being tested.
$module_root = dirname(__DIR__);
$loader->addPsr4('Drupal\\kgaut_tools\\', $module_root . '/src');
$loader->addPsr4('Drupal\\kgaut_tools_paragraphs\\', $module_root . '/kgaut_tools_paragraphs/src');
$loader->addPsr4('Drupal\\kgaut_tools_formatters\\', $module_root . '/kgaut_tools_formatters/src');
