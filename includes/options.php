<?php

class ZettleStockSettings
{
	private $zettle_stock_settings_options;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'zettle_stock_settings_add_plugin_page'));
		add_action('admin_init', array($this, 'zettle_stock_settings_page_init'));
	}

	public function zettle_stock_settings_add_plugin_page()
	{
		add_options_page(
			"Zettle Stock's Settings", // page_title
			'Zettle Stock', // menu_title
			'manage_options', // capability
			'zettle-stock-s-settings', // menu_slug
			array($this, 'zettle_stock_settings_create_admin_page') // function
		);
	}

	public function zettle_stock_settings_create_admin_page()
	{
		$this->zettle_stock_settings_options = get_option('zettle_stock_settings_option_name'); ?>

		<div class="wrap">
			<h2>Zettle Stock's Settings</h2>
			<p>Generate credentials by going to <a
					href="https://my.zettle.com/apps/api-keys?name=zettle-stock-wordpress&scopes=READ:PRODUCT">https://my.zettle.com/apps/api-keys?name=zettle-stock-wordpress&scopes=READ:PRODUCT</a>
			</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields('zettle_stock_settings_option_group');
				do_settings_sections('zettle-stock-s-settings-admin');
				submit_button();
				?>
			</form>

			<?php
			try {
				zettle_stock_update_stock();
				$zettle_stock_product_map = get_transient('zettle_stock_product_map');

				echo "
				<table style='text-align: left;'>
					<tr>
						<th>UUID</th>
						<th>Name</th>
						<th>Price</th>
						<th>Stock</th>
					</tr>";

				foreach ($zettle_stock_product_map as $uuid => $product) {
					$name = $product['name'];
					$price = formatCurrency($product['price']);
					$stock = $product['stock'];
					echo "
				<tr>
					<td><code>$uuid</code></td>
					<td>$name</td>
					<td>$price</td>
					<td>$stock</td>
				</tr>";
				}
				echo "</table>";
			} catch (Exception $e){
				echo 'Error:<br>';
				echo '<code>';
				echo $e->getMessage(). '<br>';
				echo $e->getTraceAsString();
				echo '</code>';
			}
			?>
		</div>
	<?php }

	public function zettle_stock_settings_page_init()
	{
		register_setting(
			'zettle_stock_settings_option_group', // option_group
			'zettle_stock_settings_option_name', // option_name
			array($this, 'zettle_stock_settings_sanitize') // sanitize_callback
		);

		add_settings_section(
			'zettle_stock_settings_setting_section', // id
			'Settings', // title
			array($this, 'zettle_stock_settings_section_info'), // callback
			'zettle-stock-s-settings-admin' // page
		);

		add_settings_field(
			'client_id_0', // id
			'Client ID', // title
			array($this, 'client_id_0_callback'), // callback
			'zettle-stock-s-settings-admin', // page
			'zettle_stock_settings_setting_section' // section
		);

		add_settings_field(
			'api_key_1', // id
			'API-key', // title
			array($this, 'api_key_1_callback'), // callback
			'zettle-stock-s-settings-admin', // page
			'zettle_stock_settings_setting_section' // section
		);
	}

	public function zettle_stock_settings_sanitize($input)
	{
		$old_options = get_option('zettle_stock_settings_option_name');
		$has_errors = false;

		$sanitary_values = array();
		if (isset($input['client_id_0'])) {
			$sanitary_values['client_id_0'] = sanitize_text_field($input['client_id_0']);
		}

		if (isset($input['api_key_1'])) {
			$sanitary_values['api_key_1'] = sanitize_text_field($input['api_key_1']);
		}

		if (isset($input['client_id_0']) && isset($input['api_key_1'])) {
			$request_data = array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body' => http_build_query(array(
					'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
					'client_id' => $sanitary_values['client_id_0'],
					'assertion' => $sanitary_values['api_key_1'],
				)),
			);

			// Send a POST request to obtain the token
			$response = wp_safe_remote_post('https://oauth.zettle.com/token', $request_data);

			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				$body = wp_remote_retrieve_body($response);
				$token_data = json_decode($body);

				if (empty($token_data->access_token)) {
					add_settings_error('zettle_stock_messages', 'zettle_stock_message', __('API Credentials are invalid', 'zettle_stock'), 'error');
					$has_errors = true;
				}
			} else {
				add_settings_error('zettle_stock_messages', 'zettle_stock_message', __('API Credentials are invalid: Error ' . wp_remote_retrieve_response_code($response), 'zettle_stock'), 'error');
				$has_errors = true;
			}
		}

		if ($has_errors) {
			return $old_options;
		}

		return $sanitary_values;
	}

	public function zettle_stock_settings_section_info()
	{

	}

	public function client_id_0_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="zettle_stock_settings_option_name[client_id_0]" id="client_id_0" value="%s">',
			isset($this->zettle_stock_settings_options['client_id_0']) ? esc_attr($this->zettle_stock_settings_options['client_id_0']) : ''
		);
	}

	public function api_key_1_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="zettle_stock_settings_option_name[api_key_1]" id="api_key_1" value="%s">',
			isset($this->zettle_stock_settings_options['api_key_1']) ? esc_attr($this->zettle_stock_settings_options['api_key_1']) : ''
		);
	}

}

if (is_admin())
	$zettle_stock_settings = new ZettleStockSettings();
