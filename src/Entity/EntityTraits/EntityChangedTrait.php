<?php


namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for accessing changed time.
 */
trait EntityChangedTrait {

  /**
   * Returns the timestamp of the last entity change across all translations.
   *
   * @return int
   *   The timestamp of the last entity save operation across all
   *   translations.
   */
  public function getChangedTimeAcrossTranslations() {
    $changed = $this->getUntranslated()->getChangedTime();
    foreach ($this->getTranslationLanguages(FALSE) as $language) {
      $translation_changed = $this->getTranslation($language->getId())->getChangedTime();
      $changed = max($translation_changed, $changed);
    }
    return $changed;
  }

  /**
   * Gets the timestamp of the last entity change for the current translation.
   *
   * @return int
   *   The timestamp of the last entity save operation.
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * Sets the timestamp of the last entity change for the current translation.
   *
   * @param int $timestamp
   *   The timestamp of the last entity save operation.
   *
   * @return $this
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * @return BaseFieldDefinition
   */
  public static function baseFieldChanged($title = 'Changed', $description = NULL) {
    $field =  BaseFieldDefinition::create('changed')->setLabel($title);
    if ($description !== NULL) {
      $field->setDescription($description);
    }
    return $field;
  }

}
