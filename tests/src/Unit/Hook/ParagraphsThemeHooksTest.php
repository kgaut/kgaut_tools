<?php

declare(strict_types=1);

namespace Drupal\Tests\kgaut_tools\Unit\Hook;

use Drupal\kgaut_tools_paragraphs\Hook\ParagraphsThemeHooks;
use Drupal\paragraphs\ParagraphInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\kgaut_tools_paragraphs\Hook\ParagraphsThemeHooks
 */
final class ParagraphsThemeHooksTest extends TestCase {

  /**
   * Skips the suite when the paragraphs contrib module is not installed.
   */
  protected function setUp(): void {
    parent::setUp();
    if (!interface_exists(ParagraphInterface::class)) {
      $this->markTestSkipped('Skipping: drupal/paragraphs is not installed.');
    }
  }

  /**
   * @covers ::themeSuggestionsParagraphAlter
   *
   * @dataProvider doubleBundleProvider
   */
  public function testDoubleBundleAddsSuggestion(string $bundle, bool $expected): void {
    $paragraph = $this->createMock(ParagraphInterface::class);
    $paragraph->method('bundle')->willReturn($bundle);

    $suggestions = [];
    $variables = ['elements' => ['#paragraph' => $paragraph]];
    (new ParagraphsThemeHooks())->themeSuggestionsParagraphAlter($suggestions, $variables);

    if ($expected) {
      $this->assertContains('paragraph__double', $suggestions);
    }
    else {
      $this->assertNotContains('paragraph__double', $suggestions);
    }
  }

  /**
   * Data provider for ::testDoubleBundleAddsSuggestion().
   *
   * @return array<string, array{0: string, 1: bool}>
   *   Each row contains the paragraph bundle name and the expected outcome.
   */
  public static function doubleBundleProvider(): array {
    return [
      'block_and_text' => ['block_and_text', TRUE],
      'block_double' => ['block_double', TRUE],
      'simple text' => ['text', FALSE],
      'media only' => ['media', FALSE],
    ];
  }

}
