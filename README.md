# Toolbox module for Drupal 8

Currently provide two features.

## CleanString Service
Transliterate and remove special char from a string.
```php
$mystring = "Hello World";
$cleanString = \Drupal::service('kgaut_tools.stringcleaner')->clean($categorie->name); //'hello-world
$cleanStringWithoutDash = \Drupal::service('kgaut_tools.stringcleaner')->clean($categorie->name,true); //'hello_world
```
