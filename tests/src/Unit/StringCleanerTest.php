<?php

declare(strict_types=1);

namespace Drupal\Tests\kgaut_tools\Unit;

use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\kgaut_tools\StringCleaner;
use Drupal\pathauto\AliasCleanerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\kgaut_tools\StringCleaner
 */
final class StringCleanerTest extends TestCase {

  /**
   * Skips the suite when the pathauto contrib module is not installed.
   */
  protected function setUp(): void {
    parent::setUp();
    if (!interface_exists(AliasCleanerInterface::class)) {
      $this->markTestSkipped('Skipping: drupal/pathauto is not installed.');
    }
  }

  /**
   * @covers ::clean
   */
  public function testCleanReturnsAliasFriendlyValue(): void {
    $transliteration = $this->createMock(PhpTransliteration::class);
    $transliteration
      ->expects($this->once())
      ->method('transliterate')
      ->with('Héllo Wörld')
      ->willReturn('Hello World');

    $alias_cleaner = $this->createMock(AliasCleanerInterface::class);
    $alias_cleaner
      ->expects($this->once())
      ->method('cleanString')
      ->with('Hello World')
      ->willReturn('hello-world');

    $cleaner = new StringCleaner($transliteration, $alias_cleaner);
    $this->assertSame('hello-world', $cleaner->clean('Héllo Wörld'));
  }

  /**
   * @covers ::clean
   */
  public function testCleanReplacesDashesWithUnderscoresWhenRequested(): void {
    $transliteration = $this->createMock(PhpTransliteration::class);
    $transliteration->method('transliterate')->willReturnArgument(0);

    $alias_cleaner = $this->createMock(AliasCleanerInterface::class);
    $alias_cleaner->method('cleanString')->willReturn('hello-world');

    $cleaner = new StringCleaner($transliteration, $alias_cleaner);
    $this->assertSame('hello_world', $cleaner->clean('Hello World', TRUE));
  }

  /**
   * @covers ::clean
   */
  public function testCleanLeavesDashesIntactByDefault(): void {
    $transliteration = $this->createMock(PhpTransliteration::class);
    $transliteration->method('transliterate')->willReturnArgument(0);

    $alias_cleaner = $this->createMock(AliasCleanerInterface::class);
    $alias_cleaner->method('cleanString')->willReturn('a-b-c');

    $cleaner = new StringCleaner($transliteration, $alias_cleaner);
    $this->assertSame('a-b-c', $cleaner->clean('A B C'));
  }

}
