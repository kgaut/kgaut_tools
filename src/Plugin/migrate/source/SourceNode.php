<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Base class for SQL migrations that produce node entities with paragraphs.
 */
abstract class SourceNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

  /**
   * Creates or updates a paragraph entity attached to a migrated node.
   *
   * @param \Drupal\migrate\Row $row
   *   The migration row.
   * @param string $paragraph_type
   *   The paragraph bundle to create when no existing one is found.
   * @param string $field_name
   *   The paragraph reference field on the destination node.
   * @param int $delta
   *   The delta to read/write within the field.
   * @param array<string, mixed> $fields
   *   The values to set on the paragraph.
   * @param string|null $language
   *   Optional langcode used to handle translations.
   *
   * @return \Drupal\paragraphs\ParagraphInterface
   *   The saved paragraph (existing or new).
   */
  public function createUpdateParagraph(
    Row $row,
    string $paragraph_type,
    string $field_name,
    int $delta,
    array $fields = [],
    ?string $language = NULL,
  ): ParagraphInterface {
    $paragraph = $this->loadExistingParagraph($row, $field_name, $delta, $language);
    if ($paragraph !== NULL) {
      $paragraph = $this->ensureTranslation($paragraph, $language);
      $this->applyValues($paragraph, $fields);
      $paragraph->save();
      return $paragraph;
    }

    $paragraph = Paragraph::create([
      'type' => $paragraph_type,
      'langcode' => $language,
    ]);
    $this->applyValues($paragraph, $fields);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Loads an existing paragraph for the given node/field/delta tuple.
   */
  private function loadExistingParagraph(Row $row, string $field_name, int $delta, ?string $language): ?ParagraphInterface {
    $id_map = $row->getIdMap();
    if (empty($id_map['destid1'])) {
      return NULL;
    }

    $node = Node::load((int) $id_map['destid1']);
    if ($node === NULL) {
      return NULL;
    }
    if ($language !== NULL && $node->hasTranslation($language)) {
      $node = $node->getTranslation($language);
    }

    $paragraphs = $node->get($field_name)->getValue();
    if (!isset($paragraphs[$delta]['target_id'])) {
      return NULL;
    }

    return Paragraph::load((int) $paragraphs[$delta]['target_id']);
  }

  /**
   * Makes sure the paragraph carries a translation for the given language.
   */
  private function ensureTranslation(ParagraphInterface $paragraph, ?string $language): ParagraphInterface {
    if ($language === NULL || $paragraph->get('langcode')->value === $language) {
      return $paragraph;
    }
    return $paragraph->hasTranslation($language)
      ? $paragraph->getTranslation($language)
      : $paragraph->addTranslation($language);
  }

  /**
   * Applies an associative array of field values to the paragraph.
   *
   * @param array<string, mixed> $fields
   */
  private function applyValues(ParagraphInterface $paragraph, array $fields): void {
    foreach ($fields as $key => $value) {
      $paragraph->set($key, $value);
    }
  }

}
