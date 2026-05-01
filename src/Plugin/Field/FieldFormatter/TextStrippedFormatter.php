<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\text\Plugin\Field\FieldFormatter\TextTrimmedFormatter;

/**
 * Plugin implementation of the strip-tags variant of the trimmed formatter.
 *
 * @FieldFormatter(
 *   id = "text_summary_or_trimmed_then_stripped",
 *   label = @Translation("Summary or trimmed then stripped"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary"
 *   },
 *   quickedit = {
 *     "editor" = "form"
 *   }
 * )
 */
final class TextStrippedFormatter extends TextTrimmedFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $element_info = \Drupal::service('element_info');

    foreach ($items as $delta => $item) {
      $is_summary = $this->getPluginId() === 'text_summary_or_trimmed_then_stripped'
        && !empty($item->summary);
      $text = $is_summary ? $item->summary : $item->value;

      $element = [
        '#type' => 'processed_text',
        '#text' => trim(str_replace('&nbsp;', '', strip_tags((string) $text))),
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];

      if (!$is_summary) {
        $element += $element_info->getInfo($element['#type']);
        $element['#pre_render'][] = [TextTrimmedFormatter::class, 'preRenderSummary'];
        $element['#text_summary_trim_length'] = $this->getSetting('trim_length');
      }

      $elements[$delta] = $element;
    }

    return $elements;
  }

}
