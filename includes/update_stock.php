<?php

/** Get the products and the stock from Zettle, and combines the
 * data to make a map of product UUID to product information.
 * This information is the name, price, and stock/balance.
 * The map is saved as a transient named 'zettle_stock_product_map',
 * and valid vor 60 seconds.
 *
 * @throws Exception
 */
function zettle_stock_update_stock(): void
{
	$zettleStockProductMap = [];
	$products = zettle_stock_get_products();
	$stock = zettle_stock_get_stock();

	if ($products && $stock) {
		foreach ($products as $product) {
			$productUuid = $product->uuid;
			$productName = $product->name;
			$productPrice = $product->variants[0]->price->amount;
			$productStock = 0;

			foreach ($stock as $s) {
				if ($s->productUuid == $productUuid) {
					$productStock = intval($s->balance);
				}
			}

			$zettleStockProductMap[$productUuid] = [
				'name' => $productName,
				'price' => $productPrice,
				'stock' => $productStock,
			];
		}
		set_transient('zettle_stock_product_map', $zettleStockProductMap, 60);
	} else {
		throw new Exception("No products or stock");
	}
}
