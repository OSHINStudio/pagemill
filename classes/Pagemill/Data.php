<?php

class Pagemill_Data implements ArrayAccess, Iterator {
	private $_data = array();
	private static $_compiled = array();
	private $_iteratorPos = -1;
	public function __construct() {
		
	}
	public static function LikeArray($value) {
		return (
			(is_array($value))
			|| ($value instanceof ArrayAccess && $value instanceof Iterator && $value instanceof Countable)
		);
	}
	public function set($key, $value) {
		$this->_data[$key] = $value;
	}
	public function get($key) {
		if (!isset($this->_data[$key])) return null;
		$value = $this->_data[$key];
		if (is_scalar($value) || is_array($value) || self::LikeArray($value)) {
			return $value;
		}
		die('What is this?');
		// TODO: Make the value somethng useful
		return (isset($this->_data[$key]) ? $this->_data[$key] : null);
	}
	public function getArray() {
		return $this->_data;
	}
	private static function _Compile($expression, $dataNodeName = 'data') {
		static $defaultBlank = "return '';";
		static $expressionCache = array();
		static $permitted_chars = array('.', '+', '-', ',', '/', '*', '(', ')', '!', '<', '>', '?', ':', '[', ']', '=', '%');
		static $permitted_tokens = array('T_STRING', 'T_CONSTANT_ENCAPSED_STRING', 'T_LNUMBER', 'T_DNUMBER', 'T_IS_EQUAL',
											'T_IS_GREATER_OR_EQUAL', 'T_IS_NOT_EQUAL', 'T_IS_SMALLER_OR_EQUAL', 'T_BOOLEAN_AND',
											'T_BOOLEAN_OR', 'T_WHITESPACE', 'T_VARIABLE', 'T_CLASS', 'T_OBJECT_OPERATOR',
											'T_LOGICAL_AND', 'T_LOGICAL_OR');
		static $additional_operators = array('LT' => '<', 'GT' => '>', 'LE' => '<=', 'GE' => '>=', 'EQ' => '==', 'NE' => '!=');

		// decode the given expression
		//$expression = html_entity_decode($expression);
		// if this expression is not in our cache yet, compile and cache it
		if (!isset(self::$_compiled[$expression])) {
			// first step: validate expression tokens and determine if "is mutator"
			$compiled = array();
			$isMutator = false;
			$parentheses = 0;
			$brackets = 0;
			foreach (array_slice(token_get_all("<?php $expression ?>"), 1, -1) as $token) {
				if (is_string($token)) {
					if ($token == '(') {
						$parentheses++;
					} else if ($token == ')') {
						if ($parentheses < 1) {
							trigger_error('Unbalanced parentheses');
							return $defaultBlank;
						}
						$parentheses--;
					} else if ($token == '[') {
						$brackets++;
					} else if ($token == ']') {
						if ($brackets < 1) {
							trigger_error('Unbalanced brackets');
							return $defaultBlank;
						}
						$brackets--;
					}
					if (!in_array($token, $permitted_chars)) {
						trigger_error("Invalid operator $token.");
						return $defaultBlank;
					}
					if ('=' == $token) {
						$isMutator = true;
					}
					$compiled[] = $token;
				}
				elseif (is_array($token)) {
					$token_name = token_name($token[0]);
					$token_value = $token[1];

					// catch additional operators
					if (('T_STRING' == $token_name) && isset($additional_operators[$token_value]))
					{
						$compiled[] = $additional_operators[$token_value];
						continue;
					}
					
					if (!in_array($token_name, $permitted_tokens)) {
						trigger_error("Invalid token $token_name ($token_value).");
						return $defaultBlank;
					}

					// treat T_CLASS tokens and a few other keywords as strings
					if (in_array($token_name, array('T_CLASS', 'T_STRING', 'T_VARIABLE', 'T_DEFAULT'))) {
						// save token value as an array so we can detect it in the
						// second step and convert it into a variable or function
						$compiled[] = array($token_value);
					} elseif ('T_WHITESPACE' != $token_name) {
						$compiled[] = $token_value;
					}
				}
			}
			if ($parentheses != 0) {
				trigger_error('Unbalanced parentheses');
				return $defaultBlank;
			}
			if ($brackets != 0) {
				trigger_error('Unbalanced parentheses');
				return $defaultBlank;
			}
			// second step: compile the prepared tokens
			$max = count($compiled);
			$null = null;
			$inVariable = false;
			for ($i = 0; $i < $max; $i++) {
				$current =& $compiled[$i];
				if ($i > 0) {
					$previous =& $compiled[$i - 1];
				} else {
					$previous =& $null;
				}
				if (($i + 1) < $max) {
					$next =& $compiled[$i + 1];
				} else {
					$next =& $null;
				}
				if (is_array($current)) {
					// This is a function or a variable
					$compiled[$i] = array_pop($current);
					$current =& $compiled[$i];
					if ('(' == $next) {
						// It's a function
						if ('->' === $previous) {
							trigger_error('Cannot call methods on objects in Pagemill.');
							return $defaultBlank;
						}
						if ('$' === substr($current, 0, 1)) {
							trigger_error("Variable name '$current' where function name expected.");
							return $defaultBlank;
						}
						if (!isset(self::$_exprFuncs[$current])) {
							trigger_error("Invalid function '$current'.");
							return $defaultBlank;
						}
						$current = self::$_exprFuncs[$current];
					} else {
						// It's a variable
						$current = '@$data[\'' . $current . '\']';
					}
				} else {
					// This is some other type of string
					if ('->' == $current) {
						$current = '[\'' . $next[0] . '\']';
						$next = null;
					}
					if ('[' == $current) {
						if ($previous == ')') {
							// Expression uses func()[] syntax. Fix it here because
							// PHP doesn't support it.
							// Crawl back to matching parenthesis
							$depth = 0;
							$beginning = $i - 2;
							while ( ($compiled[$beginning] != '(') || ($depth > 0) ) {
								if ($compiled[$beginning] == ')') {
									$depth++;
								}
								if ($compiled[$beginning] == '(') {
									$depth--;
								}
								$beginning--;
								if ($beginning < 0) break;
							}
							if ($beginning < 0) {
								trigger_error('Mismatched parentheses');
							} else {
								$compiled[$beginning - 1] = 'PMDataNode::ArrayMember(' . $compiled[$beginning - 1];
								$current = ',';
								// Crawl forward to matching bracket
								$depth = 0;
								$ending = $i + 1;
								while ( ($compiled[$ending] != ']') || ($depth > 0) ) {
									if ($compiled[$ending] == '[') {
										$depth++;
									}
									if ($compiled[$ending] == ']') {
										$depth--;
									}
									$ending++;
									if ($ending >= $max) break;
								}
								if ($ending >= $max) {
									trigger_error('Mismatched brackets');
								} else {
									$compiled[$ending] = ')';
								}
							}
						}
					}
				}
			}
			$compiled = implode('', $compiled);
			$compiled = (($isMutator ? '' : 'return ') . $compiled . ';');
			self::$_compiled[$expression] = $compiled;
			// Returning here saves an array lookup.
			return $compiled;
		}
		return self::$_compiled[$expression];
	}
	/**
	 * A method that provides the minimum scope possible for evaluating a
	 * compiled expression.
	 * @param Pagemill_Data $data
	 * @param string $compiled
	 * @return mixed
	 */
	private static function _Evaluate(Pagemill_Data $data, $compiled) {
		return eval($compiled);
	}
	public function evaluate($expression) {
		$compiled = self::_Compile($expression);
		return self::_Evaluate($this, $compiled);
	}
	public function parseVariables($text) {
		$result = $text;
		preg_match_all('/@{([\w\W\s\S]*?)}@/i', $text, $matches);
		foreach ($matches[0] as $index => $container) {
			$expression = $matches[1][$index];
			$evaluated = $this->evaluate($expression);
			$result = str_replace($container, $evaluated, $result);
		}
		return $result;
	}
	//##################   ArrayAccess special methods.  #####################\\
	public function offsetSet($offset, $value) {
		$this->set($offset, $value, false);
	}
	public function offsetExists($offset) {
		return isset($this->_data[$offset]);
	}
	public function offsetUnset($offset) {
		unset($this->_data[$offset]);
	}
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	//###################   Iterator special methods.  #######################\\
	public function rewind() {
		$this->_iteratorPos = 0;
	}
	public function current() {
		$keys = array_keys($this->_data);
		if(!isset($keys[$this->_iteratorPos])) return null;
		return $this->get($keys[$this->_iteratorPos]);
	}
	public function key() {
		$keys = array_keys($this->_data);
		if (!isset($keys[$this->_iteratorPos])) return null;
		return $keys[$this->_iteratorPos];
	}
	public function next() {
		$this->_iteratorPos++;
		return $this->current();
	}
	public function valid() {
		return ($this->key() !== null);
	}
}
