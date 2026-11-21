/**
 * forms.js: sends the email signup and failure report forms (inc/lists.php) without leaving the page and shows the
 * answer in place. Without JavaScript the forms post normally and land on /subscription/. The URL is read with
 * getAttribute: the hidden input named "action" (admin-post.php's handler name) shadows form.action.
 */
import { track } from './ui.js';

for (const form of document.querySelectorAll('form[data-signup], form[data-failure]')) {
	form.addEventListener('submit', async (e) => {
		if (!form.checkValidity()) return;
		e.preventDefault();
		const msg = form.querySelector('[data-form-msg]');
		const button = form.querySelector('button[type="submit"]');
		const body = new FormData(form);
		body.set('ajax', '1');
		if (button) button.disabled = true;
		try {
			const res = await fetch(form.getAttribute('action'), { method: 'POST', body, credentials: 'same-origin' });
			const data = await res.json();
			if (msg) msg.textContent = data.message || (data.ok ? 'Done.' : 'Something went wrong. Please try again.');
			if (data.ok) {
				form.classList.add('is-sent');
				track(form.dataset.signup ? 'signup' : 'failure_report', { list: form.dataset.signup || 'failure' });
				if (form.dataset.failure) form.reset();
			}
		} catch {
			if (msg) msg.textContent = 'Could not send. Check your connection and try again.';
		} finally {
			if (button) button.disabled = false;
		}
	});
}
