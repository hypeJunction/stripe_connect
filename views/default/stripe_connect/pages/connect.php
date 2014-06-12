<?php

$sticky = elgg_get_sticky_values('stripe_connect');
if (is_array($sticky)) {
	$vars = array_merge($vars, $sticky);
}

echo elgg_view_form('stripe_connect', array(
	'data-parsley-validate' => true,
	'class' => 'stripe-form',
), $vars);