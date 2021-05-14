<?php

namespace Drupal\kgaut_tools_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
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
class StringTitleFormatter extends StringFormatter {

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

    $elements['tag'] = [
      '#type' => 'select',
      '#title' => $this->t('Title tag'),
      '#options' => [
        'h1' => 'H1',
        'h2' => 'H2',
        'h3' => 'H3',
        'h4' => 'H4',
        'h5' => 'H5',
        'h6' => 'H6',
      ],
      '#default_value' => $this->getSetting('foo'),
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

  protected function viewValue(FieldItemInterface $item) {
    $tag = $this->getSetting('tag');
    return [
      '#type' => 'inline_template',
      '#template' => '<' .$tag . '>' . '{{ value|nl2br }}' . '</' .$tag . '>',
      '#context' => ['value' => $item->value],
    ];
  }
}
