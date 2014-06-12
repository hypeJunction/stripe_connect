<?php

elgg_make_sticky_form('stripe_connect');

$user_guid = get_input('guid');
$user = get_entity($user_guid);

if (!elgg_instanceof($user) || !$user->canEdit()) {
	register_error(elgg_echo('stripe:connect:user:error:state'));
	forward(REFERER);
}

$params['business_name'] = get_input('business_name');
$params['business_url'] = get_input('business_url');
$params['business_type'] = get_input('business_type');
$params['product_category'] = get_input('product_category');
$params['product_description'] = get_input('product_description');
$params['business_address_street'] = get_input('business_address_street');
$params['business_address_city'] = get_input('business_address_city');
$params['business_address_state'] = get_input('business_address_state');
$params['business_address_zip'] = get_input('business_address_zip');
$params['business_address_country'] = get_input('business_address_country');
$params['business_contact_first_name'] = get_input('business_contact_first_name');
$params['business_contact_last_name'] = get_input('business_contact_last_name');
$params['business_contact_dob'] = get_input('business_contact_dob');
$params['business_email'] = get_input('business_email');
$params['business_phone'] = get_input('business_phone');
$params['business_legal'] = get_input('business_legal');
$params['terms'] = (get_input('terms', false)) ? time() : 0;

$validate_exceptions = array('business_url');

foreach ($params as $name => $value) {

	$label = elgg_echo('stripe:connect:user:' . $name);

	if (!in_array($name, $validate_exceptions) && (is_null($value) || strip_tags($value) == '')) {
		register_error(elgg_echo('stripe:connect:user:error:required', array($label)));
		forward(REFERER);
	}

	$md_name = "stripe_{$name}";
	if ($user->$md_name == $value) {
		continue;
	}

	$user->$md_name = $value;
}


if ($user->save()) {

	$icon_sizes = elgg_get_config('icon_sizes');

	if (!empty($_FILES['logo'])) {
		if ($_FILES['logo']['error'] != UPLOAD_ERR_OK) {
			register_error(elgg_echo('stripe:connect:user:logo:upload_fail'));
		} else {
			$files = array();
			foreach ($icon_sizes as $name => $size_info) {
				$resized = get_resized_image_from_uploaded_file('logo', $size_info['w'], $size_info['h'], $size_info['square'], $size_info['upscale']);

				if ($resized) {
					$file = new ElggFile();
					$file->owner_guid = $user->guid;
					$file->setFilename("gateway/{$user->guid}{$name}.jpg");
					$file->open('write');
					$file->write($resized);
					$file->close();
					$files[] = $file;
				} else {
					$error = true;
				}
			}

			if ($error) {
				register_error(elgg_echo('stripe:connect:user:logo:resize_fail'));
				foreach ($files as $file) {
					$file->delete();
				}
			} else {
				$user->business_icontime = time();
			}
		}
	}

	$user->stripe_connect_profile = true;
	elgg_clear_sticky_form('stripe_connect');
	system_message(elgg_echo('stripe:connect:user:success:update'));
} else {
	register_error(elgg_echo('stripe:connect:user:error:unknown'));
}

forward(REFERER);
