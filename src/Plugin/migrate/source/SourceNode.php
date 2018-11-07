<?php

namespace Drupal\kgaut_tools\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

abstract class SourceNode extends SqlBase {

  public function fields() {
    return [];
  }

  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }
}