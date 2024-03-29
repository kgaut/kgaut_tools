<?php

namespace Drupal\kgaut_tools;

/**
 * Interface StringCleanerInterface.
 *
 * @package Drupal\kgaut_tools
 */
interface StringCleanerInterface {

  /**
   * @param $string
   * @param false $no_dash
   *
   * @return string
   */
  public function clean($string, $no_dash = FALSE);
  
}
