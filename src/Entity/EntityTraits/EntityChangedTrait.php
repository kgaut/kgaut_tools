<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Entity\EntityTraits;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides helpers for entities holding a "changed" timestamp field.
 */
trait EntityChangedTrait {

  /**
   * Returns the latest changed timestamp across every translation.
   */
  public function getChangedTimeAcrossTranslations(): int {
    $changed = (int) $this->getUntranslated()->getChangedTime();
    foreach ($this->getTranslationLanguages(FALSE) as $language) {
      $translation_changed = (int) $this->getTranslation($language->getId())->getChangedTime();
      $changed = max($translation_changed, $changed);
    }
    return $changed;
  }

  /**
   * Returns the formatted "changed" timestamp for the current translation.
   */
  public function getChangedTimeFormatted(string $format = 'short'): string {
    return \Drupal::service('date.formatter')->format($this->getChangedTime(), $format);
  }

  /**
   * Gets the timestamp of the last entity change for the current translation.
   */
  public function getChangedTime(): int {
    return (int) $this->get('changed')->value;
  }

  /**
   * Sets the timestamp of the last entity change for the current translation.
   *
   * @return $this
   */
  public function setChangedTime(int $timestamp): static {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * Builds a base field definition for the "changed" column.
   */
  public static function baseFieldChanged(string $title = 'Changed', ?string $description = NULL): BaseFieldDefinition {
    $field = BaseFieldDefinition::create('changed')->setLabel($title);
    if ($description !== NULL) {
      $field->setDescription($description);
    }
    return $field;
  }

}
