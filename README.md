# Toolbox module for Drupal 8

Currently provide two features.

## CleanString Service
Transliterate and remove special char from a string.
```php
$mystring = "Hello World";
// 'hello-world'
$cleanString = \Drupal::service('kgaut_tools.stringcleaner')->clean($categorie->name); 
//'hello_world'
$cleanStringWithoutDash = \Drupal::service('kgaut_tools.stringcleaner')->clean($categorie->name,true); 
```

## Create image derivates during upload
Inspired by @flocondetoile's post : http://flocondetoile.fr/blog/generate-programmatically-image-styles-drupal-8

## Add new usefull var to all templates : 

  - basepath : Drupal basepath() value (not always available)
  - pathtotheme : path to active theme
  - pathtotfiles : path to public files directory