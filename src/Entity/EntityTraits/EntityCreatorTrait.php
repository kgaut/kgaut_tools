<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Provides helpers for entities holding a "creator" entity reference field.
 */
trait EntityCreatorTrait {

  /**
   * Returns the user account that created the entity.
   */
  public function getOwner(): ?UserInterface {
    return $this->get('creator')->entity;
  }

  /**
   * Returns the user id of the entity creator.
   */
  public function getOwnerId(): ?int {
    $value = $this->get('creator')->target_id;
    return $value === NULL ? NULL : (int) $value;
  }

  /**
   * Sets the entity creator by user id.
   *
   * @return $this
   */
  public function setOwnerId($uid): static {
    $this->set('creator', $uid);
    return $this;
  }

  /**
   * Sets the entity creator from a user account.
   *
   * @return $this
   */
  public function setOwner(UserInterface $account): static {
    $this->set('creator', $account->id());
    return $this;
  }

  /**
   * Builds a base field definition for the "creator" column.
   */
  public static function baseFieldCreator(): BaseFieldDefinition {
    return BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Created by'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

}
