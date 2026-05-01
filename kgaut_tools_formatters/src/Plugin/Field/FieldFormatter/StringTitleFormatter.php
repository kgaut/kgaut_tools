<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'Title' formatter.
 *
 * @FieldFormatter(
 *   id = "string_title",
 *   label = @Translation("Title"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
final class StringTitleFormatter extends StringFormatter {

  /**
   * Allowed heading tags.
   */
  private const ALLOWED_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'tag' => 'h1',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['tag'] = [
      '#type' => 'select',
      '#title' => $this->t('Title tag'),
      '#options' => array_combine(self::ALLOWED_TAGS, array_map('strtoupper', self::ALLOWED_TAGS)),
      '#default_value' => $this->getSetting('tag'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Tag: @tag', ['@tag' => $this->getSetting('tag')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewValue(FieldItemInterface $item) {
    $tag = $this->getSetting('tag');
    if (!in_array($tag, self::ALLOWED_TAGS, TRUE)) {
      $tag = 'h1';
    }

    return [
      '#type' => 'inline_template',
      '#template' => '<{{ tag }}>{{ value|nl2br }}</{{ tag }}>',
      '#context' => [
        'tag' => $tag,
        'value' => $item->getValue()['value'] ?? '',
      ],
    ];
  }

}
