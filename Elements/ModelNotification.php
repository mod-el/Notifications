<?php namespace Model\Notifications\Elements;

use Model\ORM\Element;

class ModelNotification extends Element
{
	public static $table = 'model_notifications';

	/**
	 *
	 */
	public function init()
	{
		$this->has('recipients', [
			'table' => 'model_notifications_recipients',
			'field' => 'notification',
		]);
	}

	/**
	 * @return string
	 */
	public function getShortText(): string
	{
		$shortText = trim($this['short_text']);
		if (!$shortText and trim($this['text']))
			$shortText = textCutOff(trim($this['text']), 300);
		return $shortText;
	}
}
