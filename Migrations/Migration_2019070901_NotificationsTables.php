<?php namespace Model\Notifications\Migrations;

use Model\Db\Migration;

class Migration_2019070901_NotificationsTables extends Migration
{
	public function exec()
	{
		$this->createTable('model_notification_rules');
		$this->addColumn('model_notification_rules', 'user_idx', ['null' => false]);
		$this->addColumn('model_notification_rules', 'user', ['type' => 'int', 'null' => false]);
		$this->addColumn('model_notification_rules', 'hook', ['null' => false]);
		$this->addColumn('model_notification_rules', 'data', ['type' => 'mediumtext']);
		$this->addColumn('model_notification_rules', 'active', ['type' => 'tinyint', 'null' => false, 'default' => '1']);
		$this->addColumn('model_notification_rules', 'push', ['type' => 'tinyint', 'null' => false]);

		$this->createTable('model_notifications');
		$this->addColumn('model_notifications', 'title');
		$this->addColumn('model_notifications', 'short_text', ['type' => 'text']);
		$this->addColumn('model_notifications', 'text', ['type' => 'text']);
		$this->addColumn('model_notifications', 'url');
		$this->addColumn('model_notifications', 'external', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('model_notifications', 'date', ['type' => 'datetime', 'null' => false]);

		$this->createTable('model_notifications_recipients');
		$this->addColumn('model_notifications_recipients', 'notification', ['type' => 'int', 'null' => false]);
		$this->addColumn('model_notifications_recipients', 'user_idx', ['null' => false]);
		$this->addColumn('model_notifications_recipients', 'user', ['type' => 'int', 'null' => false]);
		$this->addColumn('model_notifications_recipients', 'sent', ['type' => 'datetime']);
		$this->addColumn('model_notifications_recipients', 'read', ['type' => 'datetime']);

		$this->addIndex('model_notifications_recipients', 'model_notifications_recipients_notification_idx', ['notification']);
		$this->addForeignKey('model_notifications_recipients', 'model_notifications_recipients_notification', 'notification', 'model_notifications', 'id', ['on-delete' => 'CASCADE']);
	}

	public function check(): bool
	{
		return $this->tableExists('model_notification_rules');
	}
}
