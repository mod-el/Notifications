<?php namespace Model\Notifications;

use Model\Core\Core;
use Model\Events\AbstractEvent;

abstract class NotifyHook
{
	public function __construct(protected Core $model)
	{
	}

	/**
	 * @return string
	 */
	abstract public function getEvent(): string;

	/**
	 * @param array $rule
	 * @param AbstractEvent $event
	 * @return bool
	 */
	abstract public function canSend(array $rule, AbstractEvent $event): bool;

	/**
	 * @param array $rule
	 * @param AbstractEvent $event
	 * @return array|null
	 */
	abstract public function getNotificationData(array $rule, AbstractEvent $event): ?array;
}
