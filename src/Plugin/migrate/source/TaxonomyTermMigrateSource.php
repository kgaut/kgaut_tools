<?php

namespace Drupal\kgaut_tools\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

abstract class TaxonomyTermMigrateSource extends SqlBase {

  protected static $vocabulary_machine_name;
  protected static $vocabulary_id;

  public function query() {
    $query = $this->select('taxonomy_term_data', 'td');
    $query->fields('td', ['tid', 'name', 'description', 'weight']);
    $query->condition('td.vid', static::$vocabulary_id);
    $query->orderBy('td.name', 'ASC');
    return $query;
  }

  public function fields() {
    return [
      'name' => $this->t('name'),
      'description' => $this->t('Description'),
      'weight' => $this->t('Weight'),
    ];
  }

  public function getIds() {
    return [
      'tid' => [
        'type' => 'string',
        'alias' => 'td',
      ],
    ];
  }

}