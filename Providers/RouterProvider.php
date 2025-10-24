<?php namespace Model\Notifications\Providers;

use Model\Router\AbstractRouterProvider;

class RouterProvider extends AbstractRouterProvider
{
	public static function getRoutes(): array
	{
		return [
			[
				'pattern' => 'model-notifications',
				'controller' => 'ModelNotifications',
			],
		];
	}
}
