<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools_paragraphs\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Entity-related hook implementations for the paragraphs submodule.
 */
final class ParagraphsEntityHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_entity_base_field_info().
   *
   * Adds two base fields to every paragraph entity:
   * - layout: lets editors flip the visual order of "double" paragraphs.
   * - grid: choose the column ratio used in "double" paragraphs.
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type): array {
    if ($entity_type->id() !== 'paragraph') {
      return [];
    }

    $fields = [];

    $fields['layout'] = BaseFieldDefinition::create('list_string')
      ->setLabel($this->t('Layout'))
      ->setSetting('allowed_values', [
        'normal' => 'Current',
        'reverse' => 'Reverse',
      ])
      ->setDefaultValue('normal')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['grid'] = BaseFieldDefinition::create('list_string')
      ->setLabel($this->t('Grid'))
      ->setSetting('allowed_values', [
        '6_6' => 'Equal size (6/12 - 6/12)',
        '7_5' => '60% - 40% (7/12 - 5/12)',
        '5_7' => '40% - 60% (5/12 - 7/12)',
        '8_4' => '66% - 33% (8/12 - 4/12)',
        '4_8' => '33% - 66% (4/12 - 8/12)',
        '10_2' => '83% - 17% (10/12 - 2/12)',
        '2_10' => '17% - 83% (2/12 - 10/12)',
      ])
      ->setDefaultValue('6_6')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
