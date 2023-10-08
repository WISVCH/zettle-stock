<?php


/** Get the field of a product in the product map by UUID.
 *
 * @param Array $atts Attributes from the shortcode. This must include 'uuid'.
 * @param string $field The field to get the value from, like 'name', 'price' or 'stock'.
 * @return string The value of the field, or 'Error' on an exception, like unknown UUID, or invalid API credentials.
 */
function zettle_stock_field_shortcode($atts, string $field): string
{
	// Get the UUID from the shortcode attribute
	$uuid = isset($atts['uuid']) ? sanitize_text_field($atts['uuid']) : '';

	try {
		return zettle_stock_get_field_from_product($uuid, $field);
	} catch (Exception $e) {
		error_log($e);
	}

	return 'Error';
}

/** Get the field of a product in the product map by UUID. Throws exceptions on errors.
 *
 * @param string $uuid The UUID of the product.
 * @param string $field The field to get the value from, like 'name', 'price' or 'stock'.
 * @return string The value of the field.
 * @throws Exception When the product or field is not found, or other things go wrong, like
 *   invalid API credentials.
 */
function zettle_stock_get_field_from_product(string $uuid, string $field): string
{
	// Check if UUID is empty
	if (empty($uuid)) {
		throw new Exception('Invalid UUID');
	}
	$product_map = zettle_stock_get_product_map();

	// Check if the UUID exists in the transient data
	if (isset($product_map[$uuid][$field])) {
		return strval($product_map[$uuid][$field]);
	} else {
		throw new Exception('Product or field not found');
	}
}


/** Get the cashed product map, or updated it if it is invalidated.
 *
 * @return Array The product map.
 * @throws Exception
 */
function zettle_stock_get_product_map(): array
{
	// Get the transient data
	$product_map = get_transient('zettle_stock_product_map');

	// Check if the transient data exists
	if ($product_map === false) {
		// If it doesn't exist, create it
		zettle_stock_update_stock();
		$product_map = get_transient('zettle_stock_product_map');
	}

	if ($product_map === false) {
		throw new Exception("No product map");
	}

	return $product_map;
}

/** Shortcode to get a products name based on UUID.
 * @param $atts
 * @return string
 */
function zettle_stock_product_name_shortcode($atts): string
{
	$field = 'name';
	return zettle_stock_field_shortcode($atts, $field);
}

/** Shortcode to get a products price based on UUID.
 * * @param $atts
 * @return string
 */
function zettle_stock_product_price_shortcode($atts): string
{
	$field = 'price';
	return formatCurrency(zettle_stock_field_shortcode($atts, $field));
}

/** Shortcode to get a products stock based on UUID.
 * * @param $atts
 * @return string
 */
function zettle_stock_product_stock_shortcode($atts): string
{
	$field = 'stock';
	return zettle_stock_field_shortcode($atts, $field);
}


add_shortcode('zettle_name', 'zettle_stock_product_name_shortcode');
add_shortcode('zettle_price', 'zettle_stock_product_price_shortcode');
add_shortcode('zettle_stock', 'zettle_stock_product_stock_shortcode');

