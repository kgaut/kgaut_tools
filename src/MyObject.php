<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools;

/**
 * Lightweight active-record style base class for ad-hoc database tables.
 *
 * @internal
 *   Kept for backwards compatibility with legacy projects. New code should
 *   use proper entity types or a dedicated repository.
 */
abstract class MyObject {

  /**
   * Name of the database table backing the object.
   */
  protected static string $dbTableName;

  /**
   * Name of the column used as primary key.
   */
  protected static string $dbTableIdentifier;

  /**
   * Default values applied when no other source provides them.
   *
   * @var array<string, mixed>
   */
  protected static array $defaultData = [];

  /**
   * Constructs the object from an id, an array of data, or scratch.
   *
   * @param array<string, mixed>|object|null $dataObject
   *   - If it contains only the identifier column, the row is loaded.
   *   - If it contains the identifier and additional fields, the row is loaded
   *     and merged with the provided overrides.
   *   - Otherwise, a new in-memory object is initialised with defaults.
   */
  public function __construct($dataObject = NULL) {
    if (is_array($dataObject)) {
      $dataObject = (object) $dataObject;
    }

    $identifier = static::$dbTableIdentifier;

    if (is_object($dataObject)
      && isset($dataObject->{$identifier})
      && is_numeric($dataObject->{$identifier})
    ) {
      $this->{$identifier} = $dataObject->{$identifier};
      $this->load();
      if (count((array) $dataObject) > 1) {
        foreach ($this as $key => &$value) {
          if (isset($dataObject->{$key}) && $dataObject->{$key} !== $value) {
            $value = $dataObject->{$key};
          }
        }
      }
      return;
    }

    foreach ($this as $key => &$value) {
      if (is_object($dataObject) && isset($dataObject->{$key})) {
        $value = $dataObject->{$key};
        continue;
      }
      if (isset(static::$defaultData[$key])) {
        $value = static::$defaultData[$key];
        continue;
      }
      $value = $this->resolveSystemDefault((string) $key);
    }
  }

  /**
   * Provides default values for well-known system columns.
   */
  protected function resolveSystemDefault(string $key): mixed {
    return match ($key) {
      'creator' => (int) \Drupal::currentUser()->id(),
      'created', 'updated' => \Drupal::time()->getRequestTime(),
      default => NULL,
    };
  }

  /**
   * Saves the object, performing an INSERT or UPDATE as appropriate.
   */
  public function save() {
    $unsaved_attributes = [];
    $this->presave($unsaved_attributes);

    $identifier = static::$dbTableIdentifier;
    $result = (isset($this->{$identifier}) && is_numeric($this->{$identifier}))
      ? $this->update()
      : $this->insert();

    $this->postsave($unsaved_attributes);
    return $result;
  }

  /**
   * Returns the primary-key value.
   */
  public function getId() {
    return $this->{static::$dbTableIdentifier};
  }

  /**
   * Deletes the row associated with this object.
   */
  public function delete(): int {
    return \Drupal::database()
      ->delete(static::$dbTableName)
      ->condition(static::$dbTableIdentifier, $this->{static::$dbTableIdentifier})
      ->execute();
  }

  /**
   * Loads every row of the backing table as instances of this class.
   *
   * @return static[]
   */
  public static function loadAll(): array {
    $items = [];
    $identifier = static::$dbTableIdentifier;
    $query = \Drupal::database()->select(static::$dbTableName, 's');
    $query->fields('s', [$identifier]);
    $result = $query->execute();
    while ($row = $result->fetchObject()) {
      $items[] = new static([$identifier => $row->{$identifier}]);
    }
    return $items;
  }

  /**
   * Removes attributes prefixed with `_` so they are not persisted.
   *
   * @param array<string, mixed> $unsaved_attributes
   *   Filled with the stripped attributes so they can be restored later.
   */
  protected function presave(array &$unsaved_attributes): void {
    foreach ($this as $attr => $val) {
      if (str_starts_with((string) $attr, '_')) {
        $unsaved_attributes[$attr] = $val;
        unset($this->{$attr});
      }
    }
  }

  /**
   * Restores attributes stripped by ::presave().
   *
   * @param array<string, mixed> $unsaved_attributes
   *   The attributes captured during presave.
   */
  protected function postsave(array &$unsaved_attributes): void {
    foreach ($unsaved_attributes as $attr => $val) {
      $this->{$attr} = $val;
    }
  }

  /**
   * Inserts the in-memory object into the database.
   */
  private function insert(): bool {
    $this->encodeJsonColumns();
    $identifier = static::$dbTableIdentifier;
    $query = \Drupal::database()->insert(static::$dbTableName);
    $query->fields(get_object_vars($this));
    $this->{$identifier} = $query->execute();
    $this->decodeJsonColumns();
    return is_numeric($this->{$identifier}) && $this->{$identifier} > 0;
  }

  /**
   * Updates the database row matching this object.
   */
  private function update(): int {
    $this->encodeJsonColumns();
    $identifier = static::$dbTableIdentifier;
    $fields = get_object_vars($this);
    unset($fields[$identifier]);

    $result = \Drupal::database()->update(static::$dbTableName)
      ->fields($fields)
      ->condition($identifier, $this->{$identifier}, '=')
      ->execute();

    $this->decodeJsonColumns();
    return (int) $result;
  }

  /**
   * Loads the row matching the current identifier into the object.
   */
  protected function load(): bool {
    $identifier = static::$dbTableIdentifier;
    $result = \Drupal::database()->select(static::$dbTableName, 't')
      ->fields('t')
      ->condition($identifier, $this->{$identifier})
      ->execute()
      ->fetchObject();

    if (!$result) {
      return FALSE;
    }

    foreach ($this as $key => &$value) {
      if (!isset($result->{$key})) {
        continue;
      }
      $raw = $result->{$key};
      $value = (in_array($key, ['data', 'stage'], TRUE) && is_string($raw) && $raw !== '')
        ? json_decode($raw)
        : $raw;
    }
    return TRUE;
  }

  /**
   * Loads items based on conditions.
   *
   * @param array<int, array{0: string, 1: mixed, 2?: ?string}> $conditions
   *   Tuples of [field, value, operator].
   * @param bool $asArray
   *   When FALSE (default), a single hit is returned as the object itself.
   *   When TRUE, results are always returned as an array.
   *
   * @return static|static[]
   */
  protected static function _load(array $conditions = [], bool $asArray = FALSE): static|array {
    $objects = [];
    $query = \Drupal::database()->select(static::$dbTableName, 't');
    $query->fields('t');
    foreach ($conditions as $cond) {
      $query->condition($cond[0], $cond[1], $cond[2] ?? NULL);
    }

    $result = $query->execute();
    while ($row = $result->fetchObject()) {
      $instance = new static([]);
      foreach ($instance as $key => &$value) {
        if (isset($row->{$key})) {
          $value = $row->{$key};
        }
      }
      $objects[] = $instance;
    }

    if (!$asArray && count($objects) === 1) {
      return array_pop($objects);
    }
    return $objects;
  }

  /**
   * JSON-encodes well-known structured columns prior to a database write.
   */
  private function encodeJsonColumns(): void {
    foreach (['data', 'stage'] as $key) {
      if (isset($this->{$key}) && is_object($this->{$key})) {
        $this->{$key} = json_encode($this->{$key});
      }
    }
  }

  /**
   * Reverses ::encodeJsonColumns() once a write completes.
   */
  private function decodeJsonColumns(): void {
    foreach (['data', 'stage'] as $key) {
      if (isset($this->{$key}) && is_string($this->{$key})) {
        $this->{$key} = json_decode($this->{$key});
      }
    }
  }

}
