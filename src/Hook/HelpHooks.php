<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations related to help pages.
 */
final class HelpHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help(string $route_name, RouteMatchInterface $route_match): string {
    if ($route_name !== 'help.page.kgaut_tools') {
      return '';
    }

    $output = '<h3>' . $this->t('About') . '</h3>';
    $output .= '<p>' . $this->t('Tools and services for Drupal.') . '</p>';

    return $output;
  }

}
