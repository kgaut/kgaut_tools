<?php
namespace Drupal\kgaut_tools\Plugin\migrate\process;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
/**
 * @MigrateProcessPlugin(
 *   id = "body_image_path_process"
 * )
 */
class BodyImagePathProcess extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($html, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Values for the following variables are specified in the YAML file above.
    $destination = $this->configuration['images_destination'];
    $url_source = $this->configuration['url_source'];
    $images_source = $this->configuration['images_source'];
    $replace = (bool) $this->configuration['replace'];
    $rename = (bool) $this->configuration['rename'];

    $html = self::parseTexte($html, $images_source, $url_source, $destination, $row, $replace, $rename);

    return $html;
  }

  public static function parseTexte($html, $images_source, $url_source, $destination, $row, $replace = FALSE, $rename = FALSE) {
    /** @var \Drupal\kgaut_tools\StringCleaner $stringCleaner */
    $stringCleaner = \Drupal::service('kgaut_tools.stringcleaner');
    preg_match_all('/<img[^>]+>/i', $html, $result);
    $sources = [];
    $destinations = [];
    if (!empty($result[0])) {
      $i = 0;
      foreach ($result as $img_tags) {
        foreach ($img_tags as $img_tag) {
          $i++;
          preg_match_all('/(src)=("[^"]*")/i', $img_tag, $tag_attributes);
          if (!empty($tag_attributes[2][0])) {
            $filepath = str_replace('"', '', $tag_attributes[2][0]);
            // Create file object from a locally copied file.
            $pathinfos = pathinfo($filepath);
            $filename = $pathinfos['basename'];
            $path = $pathinfos['dirname'];
            if($rename) {
              $destination_finale = $destination . $stringCleaner->clean($row->getSourceProperty('title'));
              $filename_destination = $stringCleaner->clean($row->getSourceProperty('title')) . '-' . urldecode($filename);
            }
            else {
              $new_path = str_replace($images_source, '', $path);
              $destination_finale = $destination . $new_path;
              $filename_destination = urldecode($filename);
            }
            $new_destination = $destination_finale . '/' . $filename_destination;
            $uri_destination = str_replace('public://', '/' . PublicStream::basePath() . '/', $new_destination);
            if(file_exists($new_destination) && !$replace) {
              $sources[$i] = $filepath;
              $destinations[$i] = $uri_destination;
              continue;
            }
            if (!file_prepare_directory($destination_finale, FILE_CREATE_DIRECTORY)) {
              \Drupal::logger('migrate')->error(t('Error creating folder @folder', ['@folder' => $destination_finale]));
              continue;
            }
            if (filter_var($filepath, FILTER_VALIDATE_URL)) {
              $file_contents = file_get_contents($filepath);
            }
            else {
              $file_contents = file_get_contents($url_source . $filepath);
            }
            if (empty($file_contents)) {
              \Drupal::logger('migrate')->error(t('Error getting content of remote file @file', ['@file' => $file_contents]));
            }
            if ($file = file_save_data($file_contents, $new_destination, FILE_EXISTS_REPLACE)) {
              $sources[$i] = $filepath;
              $destinations[$i] = $uri_destination;
            }
            else {
              \Drupal::logger('migrate')->error(t('Error saving file @file', ['@file' => $new_destination]));
            }
          }
        }
      }
      $html = str_replace($sources, $destinations, $html);
    }

    return $html;
  }
}