<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\UriLinkFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'Button' formatter.
 *
 * @FieldFormatter(
 *   id = "link_button",
 *   label = @Translation("Button"),
 *   field_types = {
 *     "uri",
 *     "link"
 *   }
 * )
 */
final class LinkButtonFormatter extends UriLinkFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'classes' => 'button',
      'label' => 'See',
      'target' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button label'),
      '#default_value' => $this->getSetting('label'),
    ];
    $elements['classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Classes'),
      '#description' => $this->t('Space separated, without dot.'),
      '#default_value' => $this->getSetting('classes'),
    ];
    $elements['target'] = [
      '#type' => 'select',
      '#title' => $this->t('Target'),
      '#empty_option' => $this->t('Undefined'),
      '#options' => ['_blank' => '_blank'],
      '#default_value' => $this->getSetting('target'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Button label: @label', ['@label' => $this->getSetting('label')]);
    $summary[] = $this->t('Classes: @classes', ['@classes' => $this->getSetting('classes')]);
    $summary[] = $this->t('Target: @target', ['@target' => $this->getSetting('target') ?? 'Undefined']);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();
    $classes = array_values(array_filter(array_map('trim', explode(' ', (string) $settings['classes']))));

    foreach ($items as $delta => $item) {
      if ($item->isEmpty()) {
        continue;
      }

      $element = [
        '#type' => 'link',
        '#url' => Url::fromUri($item->value),
        '#title' => $settings['label'],
        '#attributes' => ['class' => $classes],
      ];
      if (!empty($settings['target'])) {
        $element['#attributes']['target'] = $settings['target'];
      }
      $elements[$delta] = $element;
    }

    return $elements;
  }

}
