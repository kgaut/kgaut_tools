<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides helpers for entities holding a "title" string field.
 */
trait EntityTitleTrait {

  /**
   * Returns the entity title.
   */
  public function getTitle(): string {
    return (string) $this->get('title')->value;
  }

  /**
   * Sets the entity title.
   *
   * @return $this
   */
  public function setTitle(string $title): static {
    $this->set('title', $title);
    return $this;
  }

  /**
   * Builds a base field definition for the "title" column.
   */
  public static function baseFieldTitle(string $title = 'Title', int $maxLength = 255): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel($title)
      ->setRequired(TRUE)
      ->setSetting('max_length', $maxLength)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

}
