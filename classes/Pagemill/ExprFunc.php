<?php

class Pagemill_ExprFunc {
	// determines if haystack begins with needle
	public static function begins($haystack, $needle) {	   // if array, must be first value;
		// if string, must be beginning substring;
		// otherwise, ???
		return (is_array($haystack) ?
				($needle === array_shift(array_values($haystack))) :
				(is_string($haystack) ?
						(0 === strpos($haystack, $needle)) :
						false));
	}

	// determines if haystack ends with needle
	public static function ends($haystack, $needle) {	   // if array, must be last value;
		// if string, must be ending substring;
		// otherwise, ???
		return (is_array($haystack) ?
				($needle === array_pop(array_values($haystack))) :
				(is_string($haystack) ?
						(0 === strpos(strrev($haystack), strrev($needle))) :
						false));
	}

	// determines if haystack contains needle
	public static function contains($haystack, $needle) {	   // if array, must be in array;
		// if string, must be a substring;
		// otherwise, ???
		return (is_array($haystack) ?
				in_array($needle, $haystack) :
				(is_string($haystack) ?
						is_int(strpos($haystack, $needle)) :
						false));
	}

	// converts a given date into a given format
	public static function format_date($format, $date = null) {	   // convert the date as needed:
		if ($date === '') return '';
		// if string and all digits, convert to integer;
		// if string and non-digits, convert to time from string;
		// if integer, leave as-is;
		// otherwise, set to null
		$date = (is_string($date) ?
						(ctype_digit($date) ? intval($date) : strtotime($date)) :
						(is_int($date) ? $date : null));
		// return date in the given format
		return (is_null($date) ? date($format) : date($format, $date));
	}

	/**
	 * Format a phone number for in the US standard, or at least something close to it.
	 * @param string $input
	 * @return string
	 */
	public static function format_phone($input) {
		// Don't know why, but....
		//var_dump($input); die();
		if (is_array($input) && sizeof($input) == 1)
			$input = $input[0];

		if ($input instanceof DAO)
			$input = $input->get('phone');

		// Phone numbers should be only numbers.
		$input = preg_replace('/[^0-9]/', '', $input);

		// If it's blank... just return blank damnit!
		if(!trim($input)) return '';

		//var_dump($input); die();
		// Do various logic depending on the input string.
		// ie:
		// 4567890 -> Just the base identifier, 456-7890
		// 1234567890 -> full US standard, (123) 456-7890
		// 18004567890 -> Full with one +1 (123) 456-7890

		if (strlen($input) == 7)
			return substr($input, 0, 3) . '-' . substr($input, 3);
		elseif (strlen($input) == 10)
			return '(' . substr($input, 0, 3) . ') ' . substr($input, 3, 3) . '-' . substr($input, 6);
		elseif (strlen($input) == 11)
			return '+' . substr($input, 0, 1) . ' (' . substr($input, 1, 3) . ') ' . substr($input, 4, 3) . '-' . substr($input, 7);
		else
			return substr($input, 0, -4) . ' ' . substr($input, -4);
	}

	// handles the corner case of arrays that are not set
	// in Pagemill returning a count of 1 instead of 0
	public static function count($array) {
		$count = ( $array instanceof Countable || is_array($array) ? count($array) : 0);
		return $count;
	}

	private static function _convertObject($object) {
		$result = $object;
		if ( (is_object($result)) || (is_array($result)) ) {
			$result = array();
			foreach ($object as $k => $v) {
				$result[$k] = self::_convertObject($v);
			}
		}
		return $result;
	}
	public static function json_encode($object) {
		return json_encode(self::_convertObject($object));
	}
	public static function is_empty($object) {
		return empty($object);
	}
	public static function pluralize($count, $singular, $plural) {
		return sprintf(((1 == $count) ? $singular : $plural), $count);
	}
	public static function var_dump() {
		ob_start();
		$args = func_get_args();
		call_user_func_array('var_dump', $args);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public static function sum($array, $prop) {
		if ($array instanceof Countable || is_array($array)) {
			$sum = 0;
			foreach ($array as $item) {
				$sum += (isset($item[$prop]) ? $item[$prop] : 0);
			}
			return $sum;
		}
		return 0;
	}
	public static function avg($array, $prop) {
		if ($array instanceof Countable || is_array($array)) {
			$sum = self::sum($array, $prop);
			return $sum / self::count($array);
		}
		return 0;
	}
	public static function implode($delimiter, $array) {
		if (!is_array($array)) return null;
		return implode($delimiter, $array);
	}
	public static function in_array($needle, $haystack) {
		if (!is_array($haystack)) return false;
		return in_array($needle, $haystack);
	}
}
