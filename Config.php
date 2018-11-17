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

	/**
	 * First initialization of module
	 *
	 * @param array $data
	 * @return bool
	 * @throws \Exception
	 */
	public function install(array $data = []): bool
	{
		$this->model->_Db->query('CREATE TABLE `model_notification_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_idx` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `user` int(11) NOT NULL,
  `hook` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `data` mediumtext COLLATE utf8_unicode_ci,
  `active` tinyint(4) NOT NULL DEFAULT \'1\',
  `push` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE `model_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_text` text COLLATE utf8_unicode_ci,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `external` tinyint(4) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE `model_notifications_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification` int(11) NOT NULL,
  `user_idx` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `user` int(11) NOT NULL,
  `sent` datetime DEFAULT NULL,
  `read` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `model_notifications_recipients_notification_idx` (`notification`),
  CONSTRAINT `model_notifications_recipients_notification` FOREIGN KEY (`notification`) REFERENCES `model_notifications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		return true;
	}
}
