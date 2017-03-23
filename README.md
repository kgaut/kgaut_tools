# Toolbox module for Drupal 8

## Installation
Add the following repo to the "repositories" section of you composer.json file :

```
{
    "type": "vcs",
    "url": "https://github.com/kgaut/kgaut_tools"
}
```
Then execute : 
`composer require kgaut/kgaut_tools`

## Features
### CleanString Service
Transliterate and remove special char from a string.
```php
$mystring = "Hello World";
// 'hello-world'
$cleanString = \Drupal::service('kgaut_tools.stringcleaner')->clean($categorie->name); 
//'hello_world'
$cleanStringWithoutDash = \Drupal::service('kgaut_tools.stringcleaner')->clean($categorie->name,true); 
```

### Create image derivates during upload
Inspired by @flocondetoile's post : http://flocondetoile.fr/blog/generate-programmatically-image-styles-drupal-8

### Add new useful var to all templates : 

  - basepath : Drupal basepath() value (not always available)
  - pathtotheme : path to active theme
  - pathtotfiles : path to public files directory

### User templates suggestions :

Add templates suggestions for user entity based on the view mode ie : user--compact.html.twig