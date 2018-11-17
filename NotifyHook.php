<?php namespace Model\Notifications;

use Model\Core\Core;

abstract class NotifyHook
{
	/** @var Core */
	protected $model;

	/**
	 * NotifyHook constructor.
	 * @param Core $model
	 */
	public function __construct(Core $model)
	{
		$this->model = $model;
	}

	/**
	 * @return string
	 */
	abstract public function getEvent(): string;

	/**
	 * @param array $rule
	 * @param array $data
	 * @return bool
	 */
	abstract public function canSend(array $rule, array $data): bool;

	/**
	 * @param array $rule
	 * @param array $data
	 * @return array|null
	 */
	abstract public function getNotificationData(array $rule, array $data): ?array;
}
