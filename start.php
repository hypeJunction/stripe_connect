<?php

// Composer autoload
require_once __DIR__ . '/vendors/autoload.php';

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/page_handlers.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\stripe_connect_init');
elgg_register_event_handler('pagesetup', 'system', __NAMESPACE__ . '\\stripe_connect_pagesetup');

function stripe_connect_init() {

	elgg_register_action('stripe_connect', __DIR__ . '/actions/stripe_connect.php');
	
	elgg_register_page_handler('stripe_connect', 'stripe_connect_page_handler');

	// Stripe Connect Webhooks
	expose_function('stripe.connect.webhooks', 'stripe_connect_webhook_handler', array(
		'environment' => array(
			'type' => 'string',
			'required' => true,
		)), 'Handles webshooks received from Stripe Connect', 'POST', false, false);

	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', 'stripe_connect_public_pages');
}


/**
 * Handle Stripe Connect webhooks
 */
function stripe_connect_webhook_handler($environment) {

	$body = get_post_data();
	$event_json = json_decode($body);
	$event_id = $event_json->id;

	$gateway = new StripeClient($environment);
	$event = $gateway->getEvent($event_id);

	if (!$event) {
		return array(
			'success' => false,
			'message' => 'Stripe Event for this webhook was not found',
		);
	}

	$ia = elgg_set_ignore_access(true);
	$ha = access_get_show_hidden_status();
	access_show_hidden_entities(true);

	$result = elgg_trigger_plugin_hook_handler($event->type, 'stripe.connect.events', array(
		'environment' => $environment,
		'event' => $event,
			), array(
		'success' => true,
	));

	access_show_hidden_entities($ha);
	elgg_set_ignore_access($ia);

	return $result;
}