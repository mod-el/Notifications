<div id="notifications-container">
	<?php
	$notifications = $this->model->_Notifications->getNotifications($this->model->getInput('user_idx'));

	$splitted = [
		'new' => [
			'title' => 'Nuove',
			'notifications' => [],
		],
		'seen' => [
			'title' => 'Già lette',
			'notifications' => [],
		],
	];

	foreach ($notifications as $n) {
		if ($n['read'])
			$splitted['seen']['notifications'][] = $n;
		else
			$splitted['new']['notifications'][] = $n;
	}

	foreach ($splitted as $group) {
		if (count($group['notifications']) === 0)
			continue;
		?>
		<div class="notifications-mid-title"><?= entities($group['title']) ?></div>
		<?php
		foreach ($group['notifications'] as $n) {
			?>
			<a href="<?= $n['url'] ?>" <?= !$n['url'] ? ' onclick="return false"' : '' ?> <?= $n['external'] ? ' target="_blank"' : '' ?> class="notification">
				<?php if ($n['title']) { ?>
					<div class="notification-title"><?= entities($n['title']) ?></div>
				<?php } ?>
				<?php if ($n['short_text']) { ?>
					<div class="notification-text"><?= entities($n['short_text'], true) ?></div>
				<?php } ?>
				<div class="notification-date"><?= date_create($n['date'])->format('d/m/Y H:i') ?></div>
			</a>
			<?php
		}
	}

	if ((count($splitted['new']['notifications']) + count($splitted['seen']['notifications'])) === 0) {
		?>
		<a href="#" onclick="return false" class="notification">
			<div class="notification-text">
				<i>Non ci sono notifiche al momento</i>
			</div>
		</a>
		<?php
	}
	?>
</div>