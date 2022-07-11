<?php namespace Model\Notifications;

use Model\Core\Autoloader;
use Model\Core\Globals;
use Model\Core\Module;

class Notifications extends Module
{
	/** @var NotifyHook[] */
	private array $hooks = [];

	/**
	 * @param array $options
	 */
	public function init(array $options)
	{
		$this->model->load('Db');

		foreach ($this->model->allModules('Db') as $db) {
			if (isset($db->options['tenant-filter'])) {
				if (!isset($db->options['tenant-filter']['ignore']))
					$db->options['tenant-filter']['ignore'] = [];

				$db->options['tenant-filter']['ignore'][] = 'model_notifications';
				$db->options['tenant-filter']['ignore'][] = 'model_notifications_recipients';
				$db->options['tenant-filter']['ignore'][] = 'model_notification_rules';
			}
		}

		$q = $this->model->_Db->select_all('model_notification_rules', ['active' => 1]);

		$notifications = [];

		foreach ($q as $rule) {
			$rule['data'] = json_decode($rule['data'], true);
			if ($rule['data'] === null)
				continue;

			$idx = sha1($rule['hook'] . json_encode($rule['data']));

			$hook = $this->getHook($rule['hook']);

			if (!isset($notifications[$idx])) {
				$notifications[$idx] = [
					'hook' => $hook,
					'rules' => [],
				];
			}

			$notifications[$idx]['rules'][] = $rule;
		}

		foreach ($notifications as $notification) {
			$event = $notification['hook']->getEvent();

			$this->model->on($event, function ($data) use ($notification) {
				$this->sendNotification($notification, $data);
			}, true);
		}
	}

	/**
	 * @param string $name
	 * @return NotifyHook
	 */
	private function getHook(string $name): NotifyHook
	{
		if (!isset($this->hooks[$name])) {
			$namespaced = Autoloader::searchFile('NotifyHook', $name);
			if (!$namespaced)
				$this->model->error('Hook ' . $name . ' not found');

			$this->hooks[$name] = new $namespaced($this->model);
		}

		return $this->hooks[$name];
	}

	/**
	 * @param array $notification
	 * @param array $data
	 * @return bool
	 */
	public function sendNotification(array $notification, array $data): bool
	{
		try {
			$notificationsToSend = [];
			foreach ($notification['rules'] as $rule) {
				if (!$notification['hook']->canSend($rule, $data))
					continue;

				$notificationData = $notification['hook']->getNotificationData($rule, $data);
				if (!$notificationData)
					continue;

				$idx = sha1(json_encode($notificationData) . $rule['push']);
				if (!isset($notificationsToSend[$idx])) {
					$notificationsToSend[$idx] = [
						'data' => $notificationData,
						'rules' => [],
					];
				}
				$notificationsToSend[$idx]['rules'][] = $rule;
			}

			foreach ($notificationsToSend as $notificationData) {
				$notificationData['data']['date'] = date('Y-m-d H:i:s');

				$notification = $this->model->_ORM->create('ModelNotification');
				$notification->save($notificationData['data']);

				foreach ($notificationData['rules'] as $rule) {
					$recipient = $notification->create('recipients');
					$recipient->save([
						'user_idx' => $rule['user_idx'],
						'user' => $rule['user'],
					]);
				}
			}
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Controller
	 *
	 * @param array $request
	 * @param string $rule
	 * @return array
	 */
	public function getController(array $request, string $rule): ?array
	{
		return [
			'controller' => 'ModelNotifications',
		];
	}

	/**
	 * @param string $user_idx
	 * @return \Generator
	 */
	public function getNotifications(string $user_idx): \Generator
	{
		if (!$this->model->getModule('User', $user_idx)->logged())
			return;

		$countNew = $this->model->_Db->count('model_notifications_recipients', [
			'user_idx' => $user_idx,
			'user' => $this->model->getModule('User', $user_idx)->logged(),
			'read' => null
		]);

		$q = $this->model->_Db->select_all('model_notifications_recipients', [
			'user_idx' => $user_idx,
			'user' => $this->model->getModule('User', $user_idx)->logged(),
		], [
			'joins' => [
				'model_notifications' => [
					'date',
				],
			],
			'order_by' => 'date DESC',
			'limit' => $countNew + 10,
		]);

		foreach ($q as $n) {
			if (!$n['read'])
				$this->model->_Db->update('model_notifications_recipients', $n['id'], ['read' => date('Y-m-d H:i:s')]);

			$notification = $this->model->_ORM->one('ModelNotification', $n['notification']);

			yield [
				'title' => $notification['title'],
				'short_text' => $notification->getShortText(),
				'url' => $notification['url'],
				'external' => $notification['external'],
				'date' => $notification['date'],
				'formatted_date' => date_create($notification['date'])->format('d/m/Y H:i'),
				'read' => $n['read'],
			];
		}
	}
}
