<?php

class StripeConnect {

	const TokenURI = 'https://connect.stripe.com/oauth/token';
	const AuthorizationURI = 'https://connect.stripe.com/oauth/authorize';

	public static function getClientId($environment = null) {

		StripeClientFactory::stageEnvironment($environment);
		if ($environment !== StripeClientFactory::ENV_PRODUCTION) {
			return elgg_get_plugin_setting('development_client_id', 'stripe_connect');
		}

		return elgg_get_plugin_setting('production_client_id', 'stripe_connect');
	}

	public static function getClientSecret($environment = null) {

		StripeClientFactory::stageEnvironment($environment);
		return StripeClientFactory::getSecretKey();
	}

	/**
	 * Generate state token for Stripe Connect auth request
	 * @return string
	 */
	public static function generateStateToken() {
		$user = (elgg_is_logged_in()) ? elgg_get_logged_in_user_entity() : elgg_get_site_entity();
		return md5(get_site_secret() . $user->name . $user->guid);
	}

	/**
	 * Validate state token returned by Stripe Connect
	 * @param string $token
	 * @return boolean
	 */
	public static function validateStateToken($token) {
		return ($token == self::generateStateToken());
	}

	/**
	 * Construct authorization URI
	 * @param string $environment
	 * @return string
	 */
	public static function getAuthorizationURI($environment = null) {

		$environment = StripeClientFactory::filterEnvironment($environment);
		$authorize_request_body = array(
			'response_type' => 'code',
			'scope' => 'read_write',
			'client_id' => self::getClientId($environment),
			'state' => self::generateStateToken(),
			'stripe_user' => self::getPrefilledData(),
			'stripe_landing' => 'register',
			'redirect_uri' => elgg_normalize_url('stripe_connect/connected/' . $environment),
		);

		$url = self::AuthorizationURI . '?' . http_build_query($authorize_request_body);

		return $url;
	}

	/**
	 * Get prefilled merchant data
	 * 
	 * @param ElggEntity $user
	 * @return array
	 */
	private static function getPrefilledData() {

		$user = elgg_get_logged_in_user_entity();
		if (!$user) {
			return array();
		}

		$metadata = array(
			'stripe_business_name',
			'stripe_business_type',
			'stripe_product_category',
			'stripe_product_description',
			'stripe_business_address_street',
			'stripe_business_address_city',
			'stripe_business_address_state',
			'stripe_business_address_zip',
			'stripe_business_address_country',
			'stripe_business_contact_first_name',
			'stripe_business_contact_last_name',
			'stripe_business_contact_dob',
			'stripe_business_email',
			'stripe_business_url',
			'stripe_business_phone',
		);

		foreach ($metadata as $name) {

			$value = strip_tags($user->$name);

			switch ($name) {

				default :
					$prefilled_data[$name] = $value;
					break;

				case 'stripe_business_contact_first_name' :
					$prefilled_data['first_name'] = $value;
					break;

				case 'stripe_business_contact_last_name' :
					$prefilled_data['last_name'] = $value;
					break;

				case 'stripe_business_email' :
					$prefilled_data['email'] = $value;
					break;

				case 'stripe_business_phone' :
					$prefilled_data['phone_number'] = $value;
					break;

				case 'stripe_business_address_street' :
					$prefilled_data['street_address'] = $value;
					break;

				case 'stripe_business_address_city' :
					$prefilled_data['city'] = $value;
					break;

				case 'stripe_business_address_state' :
					$prefilled_data['street_state'] = $value;
					break;

				case 'stripe_business_address_zip' :
					$prefilled_data['zip'] = $value;
					break;

				case 'stripe_business_address_country' :
					$prefilled_data['country'] = $value;
					break;

				case 'stripe_business_contact_dob' :
					$prefilled_data['dob_day'] = date('j', $value);
					$prefilled_data['dob_month'] = date('n', $value);
					$prefilled_data['dob_year'] = date('Y', $value);
					break;

				case 'stripe_product_description' :
					$prefilled_data['product_description'] = elgg_get_excerpt($value, 200);
					break;

				case 'stripe_business_url' :
					$prefilled_data['url'] = $value;
					breka;
			}
		}

		if (!isset($prefilled_data['url'])) {
			$prefilled_data['url'] = $user->getURL();
		}

		return $prefilled_data;
	}

	/**
	 * Tokenize authorization code recieved from stripe
	 * @return \self
	 */
	public static function tokenizeAuthorizationCode($environment = null, $code = null) {

		$user = elgg_get_logged_in_user_entity();
		if (!$user) {
			return false;
		}
		
		$token_request_body = array(
			'grant_type' => 'authorization_code',
			'client_secret' => self::getClientSecret($environment),
			'client_id' => self::getClientId($environment),
			'code' => $code,
		);

		$ch = curl_init(self::TokenURI);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_request_body));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$token_json = curl_exec($ch);
		curl_close($ch);

		$token = json_decode($token_json);

		if ($token && !$token->error) {
			$user->setPrivateSetting('stripe_access_token', $token->access_token);
			$user->setPrivateSetting('stripe_publishable_key', $token->stripe_publishable_key);
			return true;
		}

		return $token;
	}

}
