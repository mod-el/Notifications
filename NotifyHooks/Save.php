<?php namespace Model\Notifications\NotifyHooks;

use Model\Core\Autoloader;
use Model\Notifications\NotifyHook;

class Save extends NotifyHook
{
	/**
	 * @return string
	 */
	public function getEvent(): string
	{
		return 'ORM_save';
	}

	/**
	 * @param array $rule
	 * @param array $data
	 * @return bool
	 */
	public function canSend(array $rule, array $data): bool
	{
		if (isset($rule['element']) and !in_array($rule['element'], ['Element', 'ModelNotification']) and ($data['element'] ?? '') !== $rule['element'])
			return false;

		return true;
	}

	/**
	 * @param array $rule
	 * @param array $data
	 * @return array|null
	 */
	public function getNotificationData(array $rule, array $data): ?array
	{
		$userModule = $this->model->getModule('User', $rule['user_idx']);
		$username = null;
		if ($userModule->logged())
			$username = $userModule->get($userModule->options['username']);

		$articolo = substr($data['element'], -1) === 'a' ? 'una ' : 'un ';
		if ($articolo === 'una ' and in_array(strtolower($data['element']{0}), ['a', 'e', 'i', 'o', 'u']))
			$articolo = 'un\'';

		$text = ($username ?: 'Un utente') . ' ha salvato ' . $articolo . $data['element'] . ' (id #' . $data['id'] . ')';

		$elementName = Autoloader::searchFile('Element', $data['element']);
		if (!$elementName)
			return null;

		$url = null;
		if ($this->model->isLoaded('AdminFront')) {
			$adminPage = $this->model->_Admin->getAdminPageForElement($data['element']);
			if ($adminPage) {
				$url = $this->model->_AdminFront->getAdminPageUrl($adminPage);
				if ($url)
					$url .= '/edit/' . $data['id'];
			}
		}

		if ($url === null and $elementName::$controller)
			$url = $this->model->getUrl($elementName::$controller, $data['id']);

		return [
			'text' => $text,
			'url' => $url,
		];
	}
}
