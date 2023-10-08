<?php
/**
 * Plugin Name:     Zettle Stock
 * Description:     Use shortcodes to display Zettle stock on your Wordpress website.
 * Author:          Robert van Dijk
 * Text Domain:     zettle-stock
 * Version:         0.1.0
 *
 * @package         Zettle_Stock
 */

define('ZETTLE_STOCK_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ZETTLE_STOCK_PLUGIN_FILE_URL', __FILE__);

include(ZETTLE_STOCK_PLUGIN_PATH . 'includes/helpers.php');
include(ZETTLE_STOCK_PLUGIN_PATH . 'includes/options.php');
include(ZETTLE_STOCK_PLUGIN_PATH . 'includes/zettle-communication.php');
include(ZETTLE_STOCK_PLUGIN_PATH . 'includes/update_stock.php');
include(ZETTLE_STOCK_PLUGIN_PATH . 'includes/shortcodes.php');
