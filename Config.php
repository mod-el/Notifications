<?php namespace Model\Notifications;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	/**
	 * Rule for API actions
	 *
	 * @return array
	 */
	public function getRules(): array
	{
		return [
			'rules' => [
				'model-notifications' => 'model-notifications',
			],
			'controllers' => [
				'ModelNotifications',
			],
		];
	}

	public function getConfigData(): ?array
	{
		return [];
	}
}
