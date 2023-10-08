<?php


/** Using the set credentials, this function gets a bearer token
 * from Zettle. If the previous token has not expired yet, it
 * will return that one.
 *
 * @throws Exception When the API credentials are not set or invalid, or when no access token is received.
 */
function zettle_stock_get_bearer_token(): string
{
	// Check if the token is cached and not expired
	$cached_token = get_transient('zettle_bearer_token');

	if (false === $cached_token) {
		// Token is not cached or expired, fetch a new one

		// Check if credentials are set
		$zettle_stock_settings_options = get_option('zettle_stock_settings_option_name'); // Array of All Options
		$client_id_0 = $zettle_stock_settings_options['client_id_0'] ?? '';
		$api_key_1 = $zettle_stock_settings_options['api_key_1'] ?? '';


		if (!empty($client_id_0) && !empty($api_key_1)) {
			// Prepare the request data
			$request_data = array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body' => http_build_query(array(
					'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
					'client_id' => $client_id_0,
					'assertion' => $api_key_1,
				)),
			);

			// Send a POST request to obtain the token
			$response = wp_safe_remote_post('https://oauth.zettle.com/token', $request_data);

			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				$body = wp_remote_retrieve_body($response);
				$token_data = json_decode($body);

				if (!empty($token_data->access_token) && !empty($token_data->expires_in)) {
					// Cache the token for the specified duration (expires_in)
					set_transient('zettle_bearer_token', $token_data->access_token, $token_data->expires_in);
					return $token_data->access_token;
				} else {
					throw new Exception("No access token received. Response: " . print_r(wp_remote_retrieve_body($response), true));
				}
			} else {
				throw new Exception("API Credentials not valid. Response: " . print_r(wp_remote_retrieve_response_code($response), true) . print_r(wp_remote_retrieve_body($response), true));
			}
		} else {
			throw new Exception("API Credentials not set");
		}
	} else {
		// Return the cached token
		return $cached_token;
	}
}

/** Invalidate the bearer token, by deleting the transient.
 * @return void
 */
function zettle_stock_invalidate_token(): void
{
	delete_transient('zettle_bearer_token');
}

/** Make a get authenticated get request to a Zettle URL.
 *
 * @param $url string The URL to request.
 * @return mixed The response body parsed as JSON.
 * @throws Exception Then the bearer token is absent, or the API call returns an error.
 */
function zettle_stock_get_request(string $url): mixed
{
	// Get the bearer token
	$bearer_token = zettle_stock_get_bearer_token();

	// Check if the bearer token is available
	if (!$bearer_token) {
		throw new Exception("No Bearer token");
	}

	// Prepare the request headers with the bearer token
	$request_headers = array(
		'Authorization' => 'Bearer ' . $bearer_token,
	);

	// Prepare the request arguments
	$request_args = array(
		'headers' => $request_headers,
	);

	// Make the GET request to the Zettle API
	$response = wp_safe_remote_get($url, $request_args);

	if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
		// Parse and return the JSON response
		$body = wp_remote_retrieve_body($response);
		return json_decode($body);
	} else {
		zettle_stock_invalidate_token();
		throw new Exception("API Call not valid. Response: " . print_r(wp_remote_retrieve_response_code($response), true) . print_r(wp_remote_retrieve_body($response), true));
	}
}


/** Get all the product from Zettle.
 *
 * @return Array Array with products.
 * @throws Exception
 */
function zettle_stock_get_products(): array
{
	return zettle_stock_get_request('https://products.izettle.com/organizations/self/products/v2');
}


/** Get the stock from Zettle.
 *
 * @return Array Array with stock.
 * @throws Exception
 */
function zettle_stock_get_stock(): array
{
	return zettle_stock_get_request('https://inventory.izettle.com/v3/stock');
}
