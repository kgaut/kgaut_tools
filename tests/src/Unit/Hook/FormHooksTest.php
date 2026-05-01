<?php

declare(strict_types=1);

namespace Drupal\Tests\kgaut_tools\Unit\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\kgaut_tools\Hook\FormHooks;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\kgaut_tools\Hook\FormHooks
 */
final class FormHooksTest extends TestCase {

  /**
   * @covers ::alterSystemPerformanceSettings
   */
  public function testCustomLifetimesAreAdded(): void {
    $form = [
      'caching' => [
        'page_cache_maximum_age' => [
          '#options' => [0 => 'No caching', 60 => '1 min'],
        ],
      ],
    ];

    (new FormHooks())->alterSystemPerformanceSettings($form, $this->createMock(FormStateInterface::class));

    $this->assertSame('2 days', $form['caching']['page_cache_maximum_age']['#options'][172800]);
    $this->assertSame('20 days', $form['caching']['page_cache_maximum_age']['#options'][1728000]);
    $this->assertSame('1 min', $form['caching']['page_cache_maximum_age']['#options'][60]);
  }

  /**
   * @covers ::alterSystemPerformanceSettings
   */
  public function testFormWithoutCachingSectionIsLeftAlone(): void {
    $form = ['unrelated' => TRUE];
    (new FormHooks())->alterSystemPerformanceSettings($form, $this->createMock(FormStateInterface::class));
    $this->assertSame(['unrelated' => TRUE], $form);
  }

}
