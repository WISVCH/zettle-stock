<?php

/** Format an integer price to a string in euros.
 *
 * Example:
 *  531 -> €5.31
 *  6000 -> €60.00
 *
 * @param int $amount The amount of euro-cents.
 * @return string The formatted price.
 */
function formatCurrency(int $amount): string
{
	// Convert the integer to a float with two decimal places
	$floatAmount = number_format($amount / 100, 2, '.', '');

	// Format the float as a currency string with the euro symbol
	return "€" . $floatAmount;
}
