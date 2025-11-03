<?php namespace Model\Notifications;

use Model\Core\Autoloader;
use Model\Core\Module;
use Model\Events\AbstractEvent;
use Model\Events\Events;

class Notifications extends Module
{
	/** @var NotifyHook[] */
	private array $hooks = [];

	/**
	 * @param array $options
	 */
	public function init(array $options)
	{
		if (class_exists('\\Model\\Multitenancy\\MultiTenancy')) {
			\Model\Multitenancy\MultiTenancy::ignoreTable('primary', 'model_notifications');
			\Model\Multitenancy\MultiTenancy::ignoreTable('primary', 'model_notifications_recipients');
			\Model\Multitenancy\MultiTenancy::ignoreTable('primary', 'model_notification_rules');
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

			Events::subscribeTo($event, function (AbstractEvent $event) use ($notification) {
				$this->sendNotification($notification, $event);
			});
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
	 * @param AbstractEvent $event
	 * @return bool
	 */
	public function sendNotification(array $notification, AbstractEvent $event): bool
	{
		try {
			$notificationsToSend = [];
			foreach ($notification['rules'] as $rule) {
				if (!$notification['hook']->canSend($rule, $event))
					continue;

				$notificationData = $notification['hook']->getNotificationData($rule, $event);
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
