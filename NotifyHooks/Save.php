<?php namespace Model\Notifications\NotifyHooks;

use Model\Core\Autoloader;
use Model\Events\AbstractEvent;
use Model\Notifications\NotifyHook;

class Save extends NotifyHook
{
	/**
	 * @return string
	 */
	public function getEvent(): string
	{
		return \Model\ORM\Events\OrmSave::class;
	}

	/**
	 * @param array $rule
	 * @param AbstractEvent $event
	 * @return bool
	 */
	public function canSend(array $rule, AbstractEvent $event): bool
	{
		if (isset($rule['data']['element']) and !in_array($rule['data']['element'], ['Element', 'ModelNotification']) and $event->element !== $rule['data']['element'])
			return false;

		return true;
	}

	/**
	 * @param array $rule
	 * @param AbstractEvent $event
	 * @return array|null
	 */
	public function getNotificationData(array $rule, AbstractEvent $event): ?array
	{
		$userModule = $this->model->getModule('User', $rule['user_idx']);
		$username = null;
		if ($userModule->logged())
			$username = $userModule->get($userModule->options['username']);

		$articolo = substr($event->element, -1) === 'a' ? 'una ' : 'un ';
		if ($articolo === 'una ' and in_array(strtolower($event->element[0]), ['a', 'e', 'i', 'o', 'u']))
			$articolo = 'un\'';

		$text = ($username ?: 'Un utente') . ' ha salvato ' . $articolo . $event->element . ' (id #' . $event->id . ')';

		/** @var \Model\ORM\Element $elementName */
		$elementName = Autoloader::searchFile('Element', $event->element);
		if (!$elementName)
			return null;

		$url = null;
		if ($this->model->isLoaded('AdminFront')) {
			$adminPage = $this->model->_Admin->getAdminPageForElement($event->element);
			if ($adminPage) {
				$url = $this->model->_AdminFront->getAdminPageUrl($adminPage);
				if ($url)
					$url .= '/edit/' . $event->id;
			}
		}

		if ($url === null and $elementName::$controller)
			$url = $this->model->getUrl($elementName::$controller, $event->id);

		return [
			'text' => $text,
			'url' => $url,
		];
	}
}
