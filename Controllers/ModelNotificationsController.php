<?php namespace Model\Notifications\Controllers;

use Model\Core\Controller;

class ModelNotificationsController extends Controller
{
	function index()
	{
		switch ($this->model->getRequest(1)) {
			case 'check':
				$q = $this->model->_Db->select_all('model_notifications_recipients', [
					'user_idx' => $this->model->getInput('user_idx'),
					'user' => $this->model->getInput('user'),
					'read' => null,
				]);

				$notifications = [];
				foreach ($q as $n) {
					if (!$n['sent'])
						$this->model->_Db->update('model_notifications_recipients', $n['id'], ['sent' => date('Y-m-d H:i:s')]);

					$notification = $this->model->_ORM->one('ModelNotification', $n['notification']);

					$notifications[] = [
						'id' => $notification['id'],
						'title' => $notification['title'],
						'short_text' => $notification->getShortText(),
						'url' => $notification['url'],
						'external' => $notification['external'],
						'date' => $notification['date'],
						'sent' => $n['sent'],
					];
				}

				echo json_encode($notifications);
				break;
		}
		die();
	}
}
