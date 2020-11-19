<?php

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

trait EntityTitleTrait {

  public function getTitle() {
    return $this->get('title')->value;
  }

  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  public static function baseFieldTitle($title = 'Title', $maxLength = 255) {
    return BaseFieldDefinition::create('string')
      ->setLabel($title)
      ->setRequired(TRUE)
      ->setSetting('max_length', $maxLength)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

}
