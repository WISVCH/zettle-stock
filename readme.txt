=== Zettle Stock ===
Tags: stock, zettle, inventory, products
Requires at least: 6.2.2
Tested up to: 6.2.2
Requires PHP: 8.0
Stable tag: 0.1.0
License: GNU GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Use shortcodes to display Zettle stock on your Wordpress website.


== Description ==
This plugin does the following:

- Allow you to easily connect it to your Zettle with a Zettle Integration.
- Fetch the products and stock/balance from your Zettle.
- Cache that information for at least one minute to avoid making too many API calls.
- Use shortcodes to display the name, price, and stock information of your WordPress website.

These shortcodes are available:

- `zettle_name` to show the name of a product.
- `zettle_price` to show the price of a product.
- `zettle_stock` to show the stock/balance of a product.

Every time you use the shortcode, you must include a `uuid` attribute.

Example:

- `[zettle_name uuid=\"gy5d6ey2-1584-58es-2642-56dls72fpr3c\"]` -> `Socks`
- `[zettle_price uuid=\"gy5d6ey2-1584-58es-2642-56dls72fpr3c\"]` -> `€8,00`
- `[zettle_stock uuid=\"gy5d6ey2-1584-58es-2642-56dls72fpr3c\"]` -> `67`



== Installation ==
1. Clone this repository to to the `/wp-content/plugins/` directory
1. Activate the plugin through the \'Plugins\' menu in WordPress
1. Generate credentials by going to [https://my.zettle.com/apps/api-keys?name=zettle-stock-wordpress&scopes=READ:PRODUCT](https://my.zettle.com/apps/api-keys?name=zettle-stock-wordpress&scopes=READ:PRODUCT).
1. Place the credentials in the correct field in the settings tab.
1. Use the shortcodes on your pages.
