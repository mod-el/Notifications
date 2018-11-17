<?php namespace Model\Notifications\AdminPages;

use Model\Admin\AdminPage;

class ModelNotifications extends AdminPage
{
	public function list()
	{
		$this->model->viewOptions['template-module'] = 'Notifications';
		$this->model->viewOptions['template'] = 'model-notifications';
	}
}
