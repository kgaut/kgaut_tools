<?php

declare(strict_types=1);

namespace Drupal\Tests\kgaut_tools\Unit;

use Drupal\kgaut_tools\Event\UserLoginEvent;
use Drupal\user\UserInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\kgaut_tools\Event\UserLoginEvent
 */
final class UserLoginEventTest extends TestCase {

  /**
   * Asserts that the event name constant matches the legacy hook name.
   */
  public function testEventNameConstant(): void {
    $this->assertSame('kgaut_tools_user_login', UserLoginEvent::EVENT_NAME);
  }

  /**
   * Asserts that the user account is exposed as the public ::$account property.
   */
  public function testAccountIsExposed(): void {
    $account = $this->createMock(UserInterface::class);
    $event = new UserLoginEvent($account);
    $this->assertSame($account, $event->account);
  }

}
