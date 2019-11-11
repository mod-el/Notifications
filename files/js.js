let notificationsInterval = null;

window.addEventListener('load', function () {
	if (typeof model_notifications_user_idx === 'undefined' || typeof model_notifications_user === 'undefined')
		return;

	new Promise(resolve => {
		if ('Notification' in window && navigator.serviceWorker) {
			navigator.serviceWorker.ready.then(reg => {
				Notification.requestPermission().then(result => {
					if (result === 'granted')
						resolve(true);
				});
			});
		}

		resolve(false);
	}).then(r => {
		if (r) {
			navigator.serviceWorker.controller.postMessage({
				'action': 'notifications',
				'path': PATH + 'model-notifications/check',
				'user_idx': model_notifications_user_idx,
				'user': model_notifications_user
			});
		} else {
			notificationsInterval = setInterval(() => {
				checkNotifications();
			}, 10000);

			checkNotifications();
		}
	});
});

function checkNotifications() {
	ajax(PATH + 'model-notifications/check', {
		'user_idx': model_notifications_user_idx,
		'user': model_notifications_user
	}).then(response => {
		let notificationEvent = new CustomEvent('notifications', {
			'detail': {
				'notifications': response
			}
		});
		document.dispatchEvent(notificationEvent);
	});
}