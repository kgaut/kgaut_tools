<?php

/**
 * @file
 * Minimal stubs for the drupal/paragraphs contrib module.
 *
 * Loaded by PHPStan via `parameters.bootstrapFiles` so the analyser can
 * resolve type-hints in code that depends on Paragraphs without requiring
 * the contrib module to be installed in CI. These declarations are
 * intentionally minimal and cover only what kgaut_tools/kgaut_tools_paragraphs
 * actually consumes; the real Paragraphs module always takes precedence when
 * installed.
 */

declare(strict_types=1);

namespace Drupal\paragraphs {

  if (!interface_exists(ParagraphInterface::class, FALSE)) {
    /**
     * Stub of \Drupal\paragraphs\ParagraphInterface.
     */
    interface ParagraphInterface {

      /**
       * Returns the bundle.
       */
      public function bundle(): string;

      /**
       * Returns TRUE if the entity has the field.
       */
      public function hasField(string $field_name): bool;

      /**
       * Returns the field item list for the given field.
       */
      public function get(string $field_name): mixed;

      /**
       * Returns TRUE if the entity already has a translation.
       */
      public function hasTranslation(string $langcode): bool;

      /**
       * Returns the entity in the requested translation.
       */
      public function getTranslation(string $langcode): self;

      /**
       * Adds and returns a new translation.
       */
      public function addTranslation(string $langcode): self;

      /**
       * Saves the entity.
       */
      public function save(): mixed;

      /**
       * Sets the value of the given field.
       */
      public function set(string $name, mixed $value): self;

    }
  }

}

namespace Drupal\paragraphs\Entity {

  use Drupal\paragraphs\ParagraphInterface;

  if (!class_exists(Paragraph::class, FALSE)) {
    /**
     * Stub of \Drupal\paragraphs\Entity\Paragraph.
     */
    class Paragraph implements ParagraphInterface {

      /**
       * Creates a new paragraph.
       */
      public static function create(array $values = []): self {
        return new self();
      }

      /**
       * Loads a paragraph by id.
       */
      public static function load(int|string $id): ?ParagraphInterface {
        return NULL;
      }

      public function bundle(): string {
        return '';
      }

      public function hasField(string $field_name): bool {
        return FALSE;
      }

      public function get(string $field_name): mixed {
        return NULL;
      }

      public function hasTranslation(string $langcode): bool {
        return FALSE;
      }

      public function getTranslation(string $langcode): ParagraphInterface {
        return $this;
      }

      public function addTranslation(string $langcode): ParagraphInterface {
        return $this;
      }

      public function save(): mixed {
        return NULL;
      }

      public function set(string $name, mixed $value): ParagraphInterface {
        return $this;
      }

    }
  }

}
