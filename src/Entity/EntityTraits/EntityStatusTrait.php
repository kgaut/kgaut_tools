<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides helpers for entities holding a "status" boolean field.
 */
trait EntityStatusTrait {

  /**
   * Returns TRUE when the entity is published / enabled.
   */
  public function isPublished(): bool {
    return (bool) $this->get('status')->value;
  }

  /**
   * Alias of ::isPublished() for entities labelled with an "enabled" status.
   */
  public function isEnabled(): bool {
    return $this->isPublished();
  }

  /**
   * Sets the published status.
   *
   * @return $this
   */
  public function setStatus(bool $status): static {
    return $this->setPublished($status);
  }

  /**
   * Sets the published status.
   *
   * @return $this
   */
  public function setPublished($published): static {
    $this->set('status', (bool) $published);
    return $this;
  }

  /**
   * Marks the entity as unpublished.
   *
   * @return $this
   */
  public function setUnpublished(): static {
    $this->set('status', FALSE);
    return $this;
  }

  /**
   * Builds a base field definition for the "status" column.
   */
  public static function baseFieldStatus(string $title = 'Published', ?string $description = NULL): BaseFieldDefinition {
    $field = BaseFieldDefinition::create('boolean')
      ->setLabel(t($title))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    if ($description !== NULL) {
      $field->setDescription($description);
    }
    return $field;
  }

}
