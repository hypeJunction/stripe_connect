<?php

/**
 * Setup menus at page setup
 */
function stripe_connect_pagesetup() {

	$user = elgg_get_page_owner_entity();

	elgg_register_menu_item('page', array(
		'name' => 'stripe:connect:user:profile',
		'href' => "stripe_connect/connect/$user->username",
		'text' => elgg_echo('stripe:connect:user:profile'),
		'selected' => (substr_count(current_page_url(), 'stripe_connect/profile')),
		'context' => 'settings',
		'section' => 'stripe',
	));

}