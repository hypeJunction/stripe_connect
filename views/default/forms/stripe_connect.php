<?php

$user = elgg_extract('entity', $vars);

if (!elgg_instanceof($user)) {
	return;
}

$merchant = new StripeMerchant($user);
$merchant_account = $merchant->getMerchantAccount();

if ($merchant_account) {

	$img = elgg_view('output/img', array(
		'src' => 'mod/stripe/graphics/logo.png',
		'width' => 75
	));

	$brands = $merchant->getSupportedCards();
	foreach ($brands as $brand) {
		$supported_brands .= '<li class="stripe-accepted-card">' . elgg_view('output/img', array(
					'src' => elgg_get_site_url() . 'mod/stripe/graphics/credit_card/' . strtolower(str_replace(' ', '', $brand)) . '.png',
		));
	}
	$supported_brands = '<ul class="stripe-accepted-cards">' . $supported_brands . '</ul>';

	$currencies = strtoupper(implode(', ', $merchant->getSupportedCurrencies()));

	$body = '<p>' . elgg_echo('stripe:connect:currencies', array($currencies)) . '</p>';
	$body .= '<p>' . elgg_echo('stripe:connect:brands') . $supported_brands . '</p>';
	$body .= '<p>' . elgg_echo('stripe:connect:merchant_id', array($merchant_account->id)) . '</p>';
	echo elgg_view_image_block($img, $body, array(
		'class' => 'mam'
	));
} else if ($user->stripe_connect_profile) {

	echo elgg_view('output/url', array(
		'href' => StripeConnect::getAuthorizationURI($merchant),
		'text' => elgg_view('output/img', array(
			'src' => 'mod/stripe_connect/graphics/connect.png',
		))
	));

}

$values = elgg_get_sticky_values('stripe_connect');

$config = array(
	'business_types' => array(
		'sole_prop',
		'corporation',
		'non_profit',
		'partnership',
		'llc'
	),
	'business_categories' => array(
		'art_and_graphic_design',
		'advertising',
		'charity',
		'clothing_and_accessories',
		'consulting',
		'clubs_and_membership_organizations',
		'education',
		'events_and_ticketing',
		'food_and_restaurants',
		'software',
		'professional_services',
		'tourism_and_travel',
		'web_development',
		'other'
	)
);

echo elgg_view('input/hidden', array(
	'name' => 'guid',
	'value' => $user->guid,
));

echo '<div class="stripe-row elgg-body">';

echo '<fieldset class="has-legend">';
echo '<legend>' . elgg_echo('stripe:connect:user:business_info') . '</legend>';
echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_name') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_name',
	'value' => elgg_extract('business_name', $values, $user->stripe_business_name),
	'required' => true,
	'parsley-trigger' => 'keyup focusout',
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1
));
echo '<span class="elgg-text-help">' . elgg_echo('stripe:connect:user:business_name:help') . '</span>';
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label>' . elgg_echo('stripe:connect:user:business_logo') . '</label>';
echo elgg_view('input/file', array(
	'name' => 'logo',
	'value' => ($user->stripe_business_icontime),
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_type') . '</label>';
$business_type_options = array(0 => elgg_echo('stripe:connect:user:business_type:select'));
foreach ($config['business_types'] as $cat) {
	$business_type_options[$cat] = elgg_echo('stripe:connect:user:business_type:' . $cat);
}
echo elgg_view('input/dropdown', array(
	'name' => 'business_type',
	'value' => elgg_extract('business_type', $values, $user->stripe_business_type),
	'options_values' => $business_type_options,
	'required' => true,
));
echo '<span class="elgg-text-help">' . elgg_echo('stripe:connect:user:business_type:help') . '</span>';
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:product_category') . '</label>';
$category_options = array(0 => elgg_echo('stripe:connect:user:product_category:select'));
foreach ($config['business_categories'] as $cat) {
	$category_options[$cat] = elgg_echo('stripe:connect:user:product_category:' . $cat);
}
echo '<div>';
echo elgg_view('input/dropdown', array(
	'name' => 'product_category',
	'value' => elgg_extract('product_category', $values, $user->stripe_product_category),
	'options_values' => $category_options,
	'required' => true
));
echo '</div>';
echo '<span class="elgg-text-help">' . elgg_echo('stripe:connect:user:product_category:help') . '</span>';
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label>' . elgg_echo('stripe:connect:user:business_url') . '</label>';
echo elgg_view('input/url', array(
	'name' => 'business_url',
	'value' => elgg_extract('business_url', $values, $user->stripe_business_url),
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:product_description') . '</label>';
echo '<div>';
echo elgg_view('input/plaintext', array(
	'name' => 'product_description',
	'value' => elgg_extract('product_description', $values, $user->stripe_product_description),
	'parsley-trigger' => 'keyup focusout',
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1
));
echo '</div>';
echo '<span class="elgg-text-help">' . elgg_echo('stripe:connect:user:product_description:help') . '</span>';
echo '</div>';

echo '</fieldset>';

echo '<fieldset class="has-legend">';
echo '<legend>' . elgg_echo('stripe:connect:user:contact_person') . '</legend>';
echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_contact_first_name') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_contact_first_name',
	'value' => elgg_extract('business_contact_first_name', $values, $user->stripe_business_contact_first_name),
	'required' => true,
	'parsley-trigger' => 'keyup focusout',
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_contact_last_name') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_contact_last_name',
	'value' => elgg_extract('business_contact_last_name', $values, $user->stripe_business_contact_last_name),
	'required' => true,
	'parsley-trigger' => 'keyup focusout',
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_contact_dob') . '</label>';
echo elgg_view('input/date', array(
	'name' => 'business_contact_dob',
	'value' => elgg_extract('business_contact_dob', $values, $user->stripe_business_contact_dob),
	'timestamp' => true,
	'required' => true,
	'parsley-trigger' => 'keyup focusout',
	'parsley-type' => 'dateIso',
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_email') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_email',
	'value' => elgg_extract('business_email', $values, (($user->stripe_business_email) ? $user->stripe_business_email : $user->email)),
	'required' => true,
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1,
	'parsley-type' => 'email'
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_phone') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_phone',
	'value' => elgg_extract('business_phone', $values, (($user->stripe_business_phone) ? $user->stripe_business_phone : $user->contact_phone)),
	'required' => true,
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1,
	'parsley-type' => 'phone'
));
echo '</div>';

echo '</fieldset>';

echo '<fieldset class="has-legend">';
echo '<legend>' . elgg_echo('stripe:connect:user:business_address') . '</legend>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_address_street') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_address_street',
	'value' => elgg_extract('business_address_street', $values, $user->stripe_business_address_street),
	'required' => true,
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1,
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_address_city') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_address_city',
	'value' => elgg_extract('business_address_city', $values, $user->stripe_business_address_city),
	'required' => true,
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1,
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_address_state') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_address_state',
	'value' => elgg_extract('business_address_state', $values, $user->stripe_business_address_state),
	'required' => true,
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1,
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_address_zip') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'business_address_zip',
	'value' => elgg_extract('business_address_zip', $values, $user->stripe_business_address_zip),
	'required' => true,
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1,
));
echo '</div>';

echo '<div class="stripe-col-6of6">';
echo '<label class="required">' . elgg_echo('stripe:connect:user:business_address_country') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'business_address_country',
	'value' => elgg_extract('business_address_zip', $values, $user->stripe_business_address_country),
	'options_values' => StripeCountries::getCountries('iso', 'name', 'name'),
));
echo '</div>';

echo '</fieldset>';

echo '<fieldset class="has-legend">';
echo '<legend class="required">' . elgg_echo('stripe:connect:user:business_legal') . '</legend>';

echo '<div class="stripe-col-6of6">';
echo '<span class="elgg-text-help">' . elgg_echo('stripe:connect:user:business_legal:help') . '</span>';
echo '<div>';
echo elgg_view('input/plaintext', array(
	'name' => 'business_legal',
	'value' => elgg_extract('business_legal', $values, $user->stripe_business_legal),
	'parsley-validation-minlength' => 1,
	'parsley-minlength' => 1,
	'required' => true,
));
echo '</div>';
echo '</div>';
echo '</fieldset>';

$terms = elgg_get_plugin_setting('stripe_connect_terms', 'stripe');
if ($terms) {
	echo '<fieldset class="has-legend">';
	echo '<legend>' . elgg_echo('stripe:connect:user:terms') . '</legend>';
	echo elgg_view('output/longtext', array(
		'value' => $terms,
		'class' => 'stripe-user-terms',
	));
	echo '<label class="required">';
	echo elgg_view('input/checkbox', array(
		'name' => 'stripe_connect_terms',
		'value' => 1,
		'default' => 0,
		'checked' => $user->stripe_connect_terms > 0
	));
	echo elgg_echo('stripe:connect:user:stripe_connect_terms:agree');
	echo '</label>';
	echo '</fieldset>';
} else {
	echo elgg_view('input/hidden', array(
		'name' => 'stripe_connect_terms',
		'value' => true,
	));
}

echo '</div>';

echo '<div class="elgg-foot">';
echo elgg_view('input/submit', array(
	'value' => elgg_echo('save'),
	'class' => 'elgg-button elgg-button-action'
));
echo '</div>';


