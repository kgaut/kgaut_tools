<?php

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;

trait EntityCreatedTrait {

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * @return BaseFieldDefinition
   */
  public static function baseFieldCreated($title = 'Created', $description = NULL) {
    $field =  BaseFieldDefinition::create('created')->setLabel($title);
    if ($description !== NULL) {
      $field->setDescription($description);
    }
    return $field;
  }

}
