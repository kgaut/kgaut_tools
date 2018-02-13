<?php

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

trait EntityCreatorTrait {

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('creator')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('creator')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('creator', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('creator', $account->id());
    return $this;
  }

  public static function baseFieldCreator() {
    return BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Created by'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

}
