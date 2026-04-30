<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is dispatched when a user logs in.
 */
final class UserLoginEvent extends Event {

  public const EVENT_NAME = 'kgaut_tools_user_login';

  public function __construct(public readonly UserInterface $account) {}

}
