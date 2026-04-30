<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\kgaut_tools\Event\UserLoginEvent;
use Drupal\user\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Hook implementations related to users.
 */
final class UserHooks {

  public function __construct(
    private readonly EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * Implements hook_user_login().
   *
   * Dispatches UserLoginEvent::EVENT_NAME for any subscribers.
   */
  #[Hook('user_login')]
  public function userLogin(UserInterface $account): void {
    $this->eventDispatcher->dispatch(new UserLoginEvent($account), UserLoginEvent::EVENT_NAME);
  }

}
