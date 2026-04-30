<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools_paragraphs\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Theme/preprocess hook implementations for the paragraphs submodule.
 */
final class ParagraphsThemeHooks {

  /**
   * Bundle suffixes that should be considered "double" layouts.
   */
  private const DOUBLE_BUNDLE_MARKERS = ['_and_', '_double'];

  /**
   * Implements hook_preprocess_HOOK() for paragraph templates.
   */
  #[Hook('preprocess_paragraph')]
  public function preprocessParagraph(array &$variables): void {
    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $variables['elements']['#paragraph'];

    $variables['type'] = $this->isDoubleBundle($paragraph) ? 'double' : 'simple';

    if ($variables['type'] === 'double'
      && $paragraph->hasField('layout')
      && $paragraph->get('layout')->value === 'reverse'
    ) {
      $weight = 1;
      foreach ($variables['content'] as &$content_item) {
        if (is_array($content_item)) {
          $content_item['#weight'] = $weight--;
        }
      }
    }

    $variables['grid'] = $paragraph->hasField('grid')
      ? $paragraph->get('grid')->value
      : NULL;
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter() for paragraph templates.
   */
  #[Hook('theme_suggestions_paragraph_alter')]
  public function themeSuggestionsParagraphAlter(array &$suggestions, array $variables): void {
    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $variables['elements']['#paragraph'];
    if ($this->isDoubleBundle($paragraph)) {
      $suggestions[] = 'paragraph__double';
    }
  }

  /**
   * Returns TRUE when the paragraph bundle name marks it as a "double" layout.
   */
  private function isDoubleBundle(ParagraphInterface $paragraph): bool {
    $bundle = $paragraph->bundle();
    foreach (self::DOUBLE_BUNDLE_MARKERS as $marker) {
      if (str_contains($bundle, $marker)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
