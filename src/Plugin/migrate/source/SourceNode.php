<?php

namespace Drupal\kgaut_tools\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

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

  public function createUpdateParagraph(Row $row, $paragraphType, $field_name, $delta, $fields = [], $language = NULL) {
    $paragraph = NULL;
    if($row->getIdMap()['destid1'] !== NULL) {
      $node = Node::load($row->getIdMap()['destid1']);
      if($language) {
        $node = $node->getTranslation($language);
      }
      $paragraphs = $node->get($field_name)->getValue();
      if(isset($paragraphs[$delta]) && $paragraph = Paragraph::load($paragraphs[$delta]['target_id'])) {
        foreach ($fields as $key => $value) {
          $paragraph->set($key, $value);
        }
        $paragraph->save();
      }
    }
    if(!$paragraph) {
      $paragraph = Paragraph::create(['type' => $paragraphType]);
      foreach ($fields as $key => $value) {
        $paragraph->set($key, $value);
      }
      $paragraph->isNew();
      $paragraph->save();
    }
    return $paragraph;
  }
}