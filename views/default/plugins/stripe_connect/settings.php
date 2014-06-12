<?php

$entity = elgg_extract('entity', $vars);

echo '<div>';
echo '<label>' . elgg_echo('stripe:connect:client_id:development') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[development_client_id]',
	'value' => $entity->development_client_id,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('stripe:connect:client_id:production') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[production_client_id]',
	'value' => $entity->production_client_id,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('stripe:connect:stripe_connect_terms') . '</label>';
echo elgg_view('input/longtext', array(
	'name' => 'params[stripe_connect_terms]',
	'value' => $entity->stripe_connect_terms,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('stripe:connect:application_fee') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[application_fee]',
	'value' => $entity->application_fee,
));
echo '</div>';




