<?php

namespace Drupal\kgaut_tools\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\text\Plugin\Field\FieldFormatter\TextSummaryOrTrimmedFormatter;
use Drupal\text\Plugin\Field\FieldFormatter\TextTrimmedFormatter;

/**
 * Plugin implementation of the 'text_summary_or_trimmed' formatter.
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
class TextStrippedFormatter extends TextTrimmedFormatter {
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $render_as_summary = function (&$element) {
      // Make sure any default #pre_render callbacks are set on the element,
      // because text_pre_render_summary() must run last.
      $element += \Drupal::service('element_info')->getInfo($element['#type']);
      // Add the #pre_render callback that renders the text into a summary.
      $element['#pre_render'][] = [TextTrimmedFormatter::class, 'preRenderSummary'];
      // Pass on the trim length to the #pre_render callback via a property.
      $element['#text_summary_trim_length'] = $this->getSetting('trim_length');
    };

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'processed_text',
        '#text' => NULL,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
      if ($this->getPluginId() === 'text_summary_or_trimmed_then_stripped' && !empty($item->summary)) {
        $elements[$delta]['#text'] = strip_tags($item->summary);
      }
      else {
        $elements[$delta]['#text'] = strip_tags($item->value);
        $render_as_summary($elements[$delta]);
      }
      $elements[$delta]['#text'] = trim($elements[$delta]['#text']);
      $elements[$delta]['#text'] = str_replace('&nbsp;', '', $elements[$delta]['#text']);
    }

    return $elements;
  }

}
