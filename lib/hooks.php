<?php

/**
 * Add stripe connect to public pages
 *
 * @param string $hook		Equals 'public_pages'
 * @param string $type		Equals 'walled_garden'
 * @param array $return		Current list of public pages
 * @param array $params		Additional params
 * @return array			Filtered list of public pages
 */
function stripe_connect_public_pages($hook, $type, $return, $params) {

	$return[] = "stripe_connect/.*";
	return $return;
}

