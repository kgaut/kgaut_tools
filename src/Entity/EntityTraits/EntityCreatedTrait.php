<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides helpers for entities holding a "created" timestamp field.
 */
trait EntityCreatedTrait {

  /**
   * Returns the entity creation timestamp.
   */
  public function getCreatedTime(): int {
    return (int) $this->get('created')->value;
  }

  /**
   * Returns the entity creation timestamp formatted for display.
   */
  public function getCreatedTimeFormatted(string $format = 'short'): string {
    return \Drupal::service('date.formatter')->format($this->getCreatedTime(), $format);
  }

  /**
   * Sets the entity creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime(int $timestamp): static {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * Builds a base field definition for the "created" column.
   */
  public static function baseFieldCreated(string $title = 'Created', ?string $description = NULL): BaseFieldDefinition {
    $field = BaseFieldDefinition::create('created')->setLabel($title);
    if ($description !== NULL) {
      $field->setDescription($description);
    }
    return $field;
  }

}
