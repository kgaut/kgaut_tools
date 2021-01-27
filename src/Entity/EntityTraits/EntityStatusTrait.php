<?php

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;

trait EntityStatusTrait {

  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }  
  
  public function isEnabled() {
    return $this->isPublished();
  }

  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  public function setUnpublished() {
    $this->set('status', FALSE);
    return $this;
  }

  public static function baseFieldStatus($title = 'Published', $description = NULL) {
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
