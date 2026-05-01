<?php

declare(strict_types=1);

namespace Drupal\Tests\kgaut_tools\Unit\Hook;

use Drupal\kgaut_tools\Event\UserLoginEvent;
use Drupal\kgaut_tools\Hook\UserHooks;
use Drupal\user\UserInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\kgaut_tools\Hook\UserHooks
 */
final class UserHooksTest extends TestCase {

  /**
   * @covers ::userLogin
   */
  public function testUserLoginDispatchesEvent(): void {
    $account = $this->createMock(UserInterface::class);
    $dispatcher = $this->createMock(EventDispatcherInterface::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with(
        $this->callback(static function ($event) use ($account): bool {
          return $event instanceof UserLoginEvent && $event->account === $account;
        }),
        UserLoginEvent::EVENT_NAME,
      );

    (new UserHooks($dispatcher))->userLogin($account);
  }

}
