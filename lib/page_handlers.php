<?php

/**
 * Stripe connect page handler
 * 
 * @param array $page
 * @param string $handler
 */
function stripe_connect_page_handler($page, $handler) {

	switch ($page[0]) {

		case 'connect':

			gatekeeper();

			elgg_set_context('settings');

			if (isset($page[1])) {
				$username = $page[1];
				$user = get_user_by_username($username);
			} else {
				$user = elgg_get_logged_in_user_entity();
			}

			if (!elgg_instanceof($user) || !$user->canEdit()) {
				$logged_in = elgg_get_logged_in_user_entity();
				forward($handler . '/connect/' . $logged_in->username);
			}

			elgg_set_page_owner_guid($user->guid);

			elgg_push_breadcrumb(elgg_echo('stripe:connect'));

			$title = elgg_echo('stripe:connect');
			$content = elgg_view('stripe_connect/pages/connect', array(
				'entity' => $user,
			));

			$layout = elgg_view_layout('content', array(
				'title' => $title,
				'content' => $content,
				'filter' => false,
				'sidebar' => false
			));

			echo elgg_view_page($title, $layout);
			return true;
			break;

		case 'connected' :

			$environment = elgg_extract(1, $page, StripeClientFactory::ENV_SANDBOX);

			if (!StripeConnect::validateStateToken(get_input('state'))) {
				register_error(elgg_echo('stripe:connect:error:state'));
			} else if ($code = get_input('code')) {
				$tokenize = StripeConnect::tokenizeAuthorizationCode($environment, $code);
				if ($tokenize == true) {
					system_message(elgg_echo('stripe:connect:success'));
				} else if ($tokenize->error) {
					register_error(elgg_echo('stripe:connect:error', array(
						$tokenize->error_description,
						$tokenize->error
					)));
				} else {
					register_error(elgg_echo('stripe:connect:error:unknown'));
				}
			} else if ($error = get_input('error')) {
				register_error(elgg_echo('stripe:connect:error', array(
					get_input('error_description'),
					$error
				)));
			}

			forward($handler . '/connect');
			break;
	}

	return false;
}
