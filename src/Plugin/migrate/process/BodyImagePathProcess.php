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
    $replace = (bool) $this->configuration['replace'];
    dd('Replace'. (int) $replace);
    /** @var \Drupal\kgaut_tools\StringCleaner $stringCleaner */
    $stringCleaner = \Drupal::service('kgaut_tools.stringcleaner');
    preg_match_all('/<img[^>]+>/i', $html, $result);
    if (!empty($result[0])) {
      $i = 0;
      foreach ($result as $img_tags) {
        foreach ($img_tags as $img_tag) {
          $i++;
          preg_match_all('/(alt|title|src)=("[^"]*")/i', $img_tag, $tag_attributes);
          $filepath = str_replace('"', '', $tag_attributes[2][1]);
          if (!empty($tag_attributes[2][1])) {
            // Create file object from a locally copied file.
            $filename = basename($filepath);
            $destination_finale = $destination . $stringCleaner->clean($row->getSourceProperty('title'));
            $filename_destination = $stringCleaner->clean($row->getSourceProperty('title')) . '-' . urldecode($filename);
            $new_destination = $destination_finale . '/' . $filename_destination;
            if($replace || file_exists($new_destination)) {
              dd('file not existing '.$new_destination);
              if (file_prepare_directory($destination_finale, FILE_CREATE_DIRECTORY)) {
                if (filter_var($filepath, FILTER_VALIDATE_URL)) {
                  $file_contents = file_get_contents($filepath);
                }
                else {
                  $file_contents = file_get_contents($url_source . $filepath);
                }
                if (!empty($file_contents)) {
                  if ($file = file_save_data($file_contents, $new_destination, FILE_EXISTS_REPLACE)) {
                    $uri_destination = str_replace('public://', '/' . PublicStream::basePath() . '/', $new_destination);
                    $html = str_replace($filepath, $uri_destination, $html);
                  }
                }
              }
            }
          }
        }
      }
    }
    return $html;
  }
}