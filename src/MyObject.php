<?php

namespace Drupal\kgaut_tools;

abstract class MyObject {
  protected static $db_table_name;
  protected static $db_table_identifier;
  protected static $default_data = array();

  public function __construct($dataObject = NULL) {
    global $user;
    if (is_array($dataObject)) {
      $dataObject = (object) $dataObject;
    }
    if (isset($dataObject->{static::$db_table_identifier}) && is_numeric($dataObject->{static::$db_table_identifier}) && count((array) $dataObject) == 1) {
      $this->{static::$db_table_identifier} = $dataObject->{static::$db_table_identifier};
      $this->load();
    }
    elseif (isset($dataObject->{static::$db_table_identifier}) && is_numeric($dataObject->{static::$db_table_identifier}) && count((array) $dataObject) > 1) {
      $this->{static::$db_table_identifier} = $dataObject->{static::$db_table_identifier};
      $this->load();
      foreach ($this as $key => &$value) {
        if (isset($dataObject->{$key}) && $dataObject->{$key} != $value) {
          $value = $dataObject->{$key};
        }
      }
    }
    else {
      //on rempli d'abord avec les elements explicitements passé lors de la création de l'objet
      foreach ($this as $key => &$val) {
        if (isset($dataObject->{$key})) {
          $val = $dataObject->{$key};
          continue;
        }
        //puis avec les datas par default de la classe
        if (isset(static::$default_data[$key])) {
          $val = static::$default_data[$key];
          continue;
        }
        //puis enfin avec des valeurs de bases standards
        switch ($key) {
          case 'creator':
            $val = (int) $user->uid;
            break;
          case 'created':
            $val = time();
            break;
          case 'updated':
            $val = time();
            break;
        }
      }
    }
  }
  /**
   * Cette fonction enlève tous les attributs qui ne doivent pas être enregistrés
   * entre autres ceux commencant par un underscore
   * @param $unsaved_attributes
   */
  protected function presave(&$unsaved_attributes) {
    foreach ($this as $attr => $val) {
      if (substr($attr, 0, 1) == '_') {
        unset($this->{$attr});
        $unsaved_attributes[$attr] = $val;;
      }
    }
  }
  public function save() {
    $unsaved_attributes = array();
    $this->presave($unsaved_attributes);
    if (isset($this->{static::$db_table_identifier}) && is_numeric($this->{static::$db_table_identifier})) {
      $res = $this->update();
    }
    else {
      $res = $this->insert();
    }
    $this->postsave($unsaved_attributes);
    return $res;
  }
  public function getId() {
    return $this->{static::$db_table_identifier};
  }
  /**
   * Cette fonction remets les attributs qui ne doivent pas être enregistrés
   * entre autres ceux commencant par un underscore
   * @param $unsaved_attributes
   */
  protected function postsave(&$unsaved_attributes) {
    foreach ($unsaved_attributes as $attr => $val) {
      $this->{$attr} = $val;
    }
  }
  private function insert() {
    if (isset($this->data) && is_object($this->data)) {
      $this->data = json_encode($this->data);
    }
    if (isset($this->stage) && is_object($this->stage)) {
      $this->stage = json_encode($this->stage);
    }
    $q = db_insert(static::$db_table_name);
    $q->fields(get_object_vars($this));
    $this->{static::$db_table_identifier} = $q->execute();
    if (isset($this->data) && is_string($this->data)) {
      $this->data = json_decode($this->data);
    }
    return is_numeric($this->{static::$db_table_identifier}) && $this->{static::$db_table_identifier} > 0;
  }
  private function update() {
    $q = db_update(static::$db_table_name);
    if (isset($this->data) && is_object($this->data)) {
      $this->data = json_encode($this->data);
    }
    if (isset($this->stage) && is_object($this->stage)) {
      $this->stage = json_encode($this->stage);
    }
    $fields = get_object_vars($this);
    //on jarte la clé primaire
    if (isset($fields[static::$db_table_identifier])) {
      unset($fields[static::$db_table_identifier]);
    }
    $q->fields($fields);
    $q->condition(static::$db_table_identifier, $this->{static::$db_table_identifier}, '=');
    if (isset($this->data) && is_string($this->data)) {
      $this->data = json_decode($this->data);
    }
    return $q->execute();
  }
  protected function load() {
    $query = db_select(static::$db_table_name, 't');
    $query->fields('t');
    $query->condition(static::$db_table_identifier, $this->{static::$db_table_identifier});
    $result = $query->execute()->fetchObject();
    if ($result) {
      foreach ($this as $k => &$v) {
        if (isset($result->{$k})) {
          if (in_array($k, array('data', 'stage')) && $result->{$k} != '' && is_string($result->{$k})) {
            $v = json_decode($result->{$k});
          }
          else {
            $v = $result->{$k};
          }
        }
      }
    }
    else {
      return FALSE;
    }
  }
  /**
   * Load items based on conditions
   * @param array $conditions
   * @param bool|FALSE $asArray if true and only one items, it will be returned
   * as an object, if true, in a array containing this object
   * @return array|mixed
   */
  protected static function _load($conditions = array(), $asArray = FALSE) {
    $objectsArray = array();
    $query = db_select(static::$db_table_name, 't');
    $query->fields('t');
    foreach ($conditions as $cond) {
      $cond1 = $cond[0];
      $cond2 = $cond[1];
      $cond3 = isset($cond[2]) ? $cond[2] : NULL;
      $query->condition($cond1, $cond2, $cond3);
    }
    $result = $query->execute();
    if ($result) {
      while ($row = $result->fetchObject()) {
        $instance = new static(array());
        foreach ($instance as $k => &$v) {
          if (isset($row->{$k})) {
            $v = $row->{$k};
          }
        }
        $objectsArray[] = $instance;
      }
    }
    if (count($objectsArray) == 1 && !$asArray) {
      return array_pop($objectsArray);
    }
    else {
      return $objectsArray;
    }
  }
  public static function loadAll() {
    $steps = array();
    $query = db_select(static::$db_table_name, 's');
    $query->fields('s', array(static::$db_table_identifier));
    $result = $query->execute();
    while ($row = $result->fetchObject()) {
      $steps[] = new static(array(static::$db_table_identifier => $row->sid));
    }
    return $steps;
  }
  public function delete() {
    $q = db_delete(static::$db_table_name);
    $q->condition(static::$db_table_identifier, $this->{static::$db_table_identifier});
    return $q->execute();
  }
}