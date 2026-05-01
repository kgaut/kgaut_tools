<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\text\Plugin\Field\FieldFormatter\TextTrimmedFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
final class TextStrippedFormatter extends TextTrimmedFormatter implements ContainerFactoryPluginInterface {

  /**
   * Constructs the formatter, capturing the element-info manager via DI.
   *
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array<string, mixed> $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label.
   * @param string $view_mode
   *   The view mode.
   * @param array<string, mixed> $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $elementInfo
   *   The element info manager.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    private readonly ElementInfoManagerInterface $elementInfo,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.element_info'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $values = $item->getValue();
      $summary = (string) ($values['summary'] ?? '');
      $value = (string) ($values['value'] ?? '');
      $format = $values['format'] ?? NULL;

      $is_summary = $this->getPluginId() === 'text_summary_or_trimmed_then_stripped'
        && $summary !== '';
      $text = $is_summary ? $summary : $value;

      $element = [
        '#type' => 'processed_text',
        '#text' => trim(str_replace('&nbsp;', '', strip_tags($text))),
        '#format' => $format,
        '#langcode' => $item->getLangcode(),
      ];

      if (!$is_summary) {
        $element += $this->elementInfo->getInfo($element['#type']);
        $element['#pre_render'][] = [TextTrimmedFormatter::class, 'preRenderSummary'];
        $element['#text_summary_trim_length'] = $this->getSetting('trim_length');
      }

      $elements[$delta] = $element;
    }

    return $elements;
  }

}
