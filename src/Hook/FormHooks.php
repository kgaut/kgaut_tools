<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations related to forms.
 */
final class FormHooks {

  /**
   * Adds longer cache lifetimes to the system performance form.
   */
  #[Hook('form_system_performance_settings_alter')]
  public function alterSystemPerformanceSettings(array &$form, FormStateInterface $form_state): void {
    if (!isset($form['caching']['page_cache_maximum_age']['#options'])) {
      return;
    }

    $form['caching']['page_cache_maximum_age']['#options'] += [
      172800 => '2 days',
      345600 => '4 days',
      432000 => '5 days',
      864000 => '10 days',
      1728000 => '20 days',
    ];
  }

}
