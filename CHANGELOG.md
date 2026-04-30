# kgaut_tools 2.0.x-dev
 - refactor(hooks): convert procedural hooks to OOP using #[Hook] attributes,
   grouped by theme under `src/Hook/` and `kgaut_tools_paragraphs/src/Hook/`
 - refactor: modernise services and plugins for Drupal 10.3+/11 coding standards
   (constructor property promotion, strict types, dependency injection, return
   types, removal of `\Drupal::service()` lookups inside services)
 - fix(formatter): `string_title` formatter no longer reads from a non-existent
   `foo` setting
 - fix(migrate): `body_image_path_process` no longer calls
   `FileRepositoryInterface` as a function/static method; uses `file_system` and
   `file.repository` services properly and avoids deprecated `file_prepare_directory()`
 - fix(translation-importer): no longer overrides the injected locale storage
   and removes a stray reference to an external `clearblue` namespace
 - feat(ci): add Drupal coding standards (phpcs), PHPStan and PHPUnit
   configuration files plus a GitHub Actions workflow running on push and pull
   requests
 - feat(tests): add PHPUnit unit tests for `StringCleaner`, `UserLoginEvent`,
   `UserHooks`, `FormHooks`, `ParagraphsThemeHooks`
 - chore(info): bump core compatibility to `^10.3 || ^11` and require PHP 8.1+

# kgaut_tools 2.0.23 05/09/2024
 - fix(info): missing dependencies

# kgaut_tools 2.0.22 21/08/2024
 - feat(drupal11): make module drupal 11 ready

# kgaut_tools 2.0.21 30/07/2024
 - feat(translation-importer): allow to create string if not existing yet

# kgaut_tools 2.0.20 30/07/2024
 - fix(translation-importer): service definition

# kgaut_tools 2.0.19 30/04/2024
 - fix(preprocess): var pathtotheme and baseurl_default_theme

# kgaut_tools 2.0.14 20/02/2024
 - fix(drupal10): fix Drupal\kgaut_tools\Event\UserLoginEvent dispatch

# kgaut_tools 2.0.13 16/02/2024
 - fix(drupal10): fix Drupal\kgaut_tools\Event\UserLoginEvent

# kgaut_tools 2.0.12 18/01/2024
 - fix(drupal10): compat and deprecated

# kgaut_tools 2.0.9 06/12/2022
 - Drupal 10 compat

# kgaut_tools 2.0.8 09/06/2022
 - Drupal 9 compat

# kgaut_tools 2.0.7 04/10/2021
 - define StringCleanerInterface

# kgaut_tools 2.0.6 05/07/2021
- Add getChangedTimeFormatted() and getCreatedTimeFormatted()
- Add new submodule kgaut_tools_formatters
- kgaut_tools_formatters - Add StringTitleFormatter
- kgaut_tools_formatters - Add LinkButtonFormatter
- kgaut_tools_formatters - LinkButtonFormatter - Add target option

# kgaut_tools 2.0.5 27/01/2021
- EntityStatusTrait - fix method isPublished()

# kgaut_tools 2.0.4 27/01/2021
- EntityStatusTrait - add method setStatus($status)

# kgaut_tools 2.0.3 27/01/2021
 - EntityStatusTrait - add method isEnabled()

# kgaut_tools 2.0.2 - 27/01/2021
 - Set dates configuration in a submodule.

# kgaut_tools 2.0.1 - 27/01/2021
  - Service TranslationImporter : fix error when locale is not installed

# kgaut_tools 2.0.0 - 27/01/2021
 - kgaut_tools_paragraphs : fix fresh install
 - Create `EntityTitleTrait`
 - Add submodule `kgaut_tools_paragraphs`
 - Create a view pager PagerFullWithSpecificFirstPage
 - Clean start for 2.0 version
