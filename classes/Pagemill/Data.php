<?php
/**
 * Pagemill data container
 */
class Pagemill_Data implements ArrayAccess, Iterator {
	private $_data = array();
	private static $_compiled = array();
	private $_iteratorPos = -1;
	private static $_exprFuncs = array();
	private static $_classHandlers = array();
	private $_handle = null;
	private $_tines = array();
	public function __construct() {
		
	}
	/**
	 * Determine if a value is an associative array (i.e., it is_array() and
	 * its keys include non-numeric values).
	 * @param mixed $value
	 * @return boolean
	 */
	public static function IsAssoc($value) {
		if (!is_array($value) || empty($value)) return false;
		if (!is_int(key($value))) return true;
		//return (0 !== count(array_diff_key($value, array_keys($value))));
		array_diff_key($value, array_keys(array_keys($value)));
	}
	/**
	 * Determine if a value is either an associative array (IsAssoc()) or it
	 * is an object that can be treated like an associative array. Note that
	 * objects which return true for LikeArray() will also return true for
	 * LikeAssoc().
	 * @param mixed $value
	 * @return boolean
	 */
	public static function LikeAssoc($value) {
		return (
			(self::IsAssoc($value))
			|| (is_a($value, 'Pagemill_Data'))
			|| ($value instanceof ArrayAccess && $value instanceof Iterator)
		);
	}
	/**
	 * Determine if a value is either a numeric array (not IsAssoc()) or it
	 * is an object that can be treated like a numeric array.
	 * @param mixed $value
	 * @return boolean
	 */
	public static function LikeArray($value) {
		return (
			(is_array($value) && !self::IsAssoc($value))
			|| ($value instanceof ArrayAccess && $value instanceof Iterator && $value instanceof Countable)
		);
	}
	public function set($key, $value) {
		//if (is_null($value)) {
		//	unset($this->_data[$key]);
		//} else {
			if (self::IsAssoc($value)) {
				// Convert associative arrays into objects
				$object = new Pagemill_Data();
				$object->_data = $value;
				$this->_data[$key] = $object;
			} else {
				$this->_data[$key] = $value;
			}
		//}
	}
	public function setArray(array $array) {
		foreach($array as $key => $value) {
			$this->set($key, $value);
		}
	}
	public function &get($key) {
		static $null = null;
		/*if ($key == '_tines') {
			return $this->_tines;
		}
		if ($key == '_handle') {
			return $this->_handle;
		}*/
		if (!isset($this->_data[$key])) {
			if ($this->_handle) {
				return $this->_handle->get($key);
			}
			return $null;
		}
		$value =& $this->_data[$key];
		$ok = false;
		if (is_array($value) && self::IsAssoc($value)) {
			$n = new Pagemill_Data();
			$n->_data = $value;
			$value = $n;
			$ok = true;
		} else if (is_null($value) || is_scalar($value) || is_array($value) || is_a($value, 'Pagemill_Data') || self::LikeArray($value) || self::LikeAssoc($value)) {
			$ok = true;
		}
		if (is_object($value)) {
			if (get_class($value) == 'stdClass') {
				$value = (array)$value;
				$ok = true;
			} else {
				foreach(self::$_classHandlers as $cls => $func) {
					if (is_a($value, $cls)) {
						$value = call_user_func($func, $value);
						$this->_data[$key] = $value;
						$ok = true;
						break;
					}
				}
			}
		}
		if (!$ok) {
			throw new Exception("Unable to process object in variable '{$key}' of class '" . get_class($value) . "'");
		}
		// Convert integer 0 to string because arbitrary strings == 0
		//if ($value === 0) $value = '0';
		return $value;
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
											'T_LOGICAL_AND', 'T_LOGICAL_OR', 'T_IS_IDENTICAL', 'T_IS_NOT_IDENTICAL');
		static $additional_operators = array('LT' => '<', 'GT' => '>', 'LE' => '<=', 'GE' => '>=', 'EQ' => '==', 'NE' => '!=');

		// decode the given expression
		$expression = html_entity_decode($expression);
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
						trigger_error("Invalid operator $token ({$expression})");
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
			$compiled = implode(' ', $compiled);
			$compiled = (($isMutator ? '' : 'return ') . $compiled . ';');
			self::$_compiled[$expression] = $compiled;
			// Returning here saves an array lookup.
			return $compiled;
		}
		return self::$_compiled[$expression];
	}
	/**
	 * A method that provides a minimal scope for evaluating a compiled
	 * expression.
	 * @param Pagemill_Data $data
	 * @param string $compiled
	 * @return mixed
	 */
	private static function _Evaluate(Pagemill_Data $data, $compiled) {
		return eval($compiled);
	}
	public function evaluate($expression) {
		$compiled = self::_Compile($expression);
		$result = self::_Evaluate($this, $compiled);
		return $result;
	}
	public function parseVariables($text, Pagemill_Doctype $encoder = null) {
		$result = $text;
		preg_match_all('/@{([\w\W\s\S]*?)}@/i', $text, $matches);
		foreach ($matches[0] as $index => $container) {
			$expression = $matches[1][$index];
			$evaluated = $this->evaluate($expression);
			if (!is_null($evaluated) && !is_scalar($evaluated)) {
				if (is_array($evaluated)) {
					$evaluated = self::IsAssoc($evaluated) ? '(Object)' : '(Array)';
				} else if (is_a($evaluated, 'Pagemill_Data')) {
					$evaluated = '(Object)';
				} else if (Pagemill_Data::LikeArray($evaluated)) {
					$evaluated = '(ArrayInterface)';
				} else if (Pagemill_Data::LikeAssoc($evaluated)) {
					$evaluated = '(Interface)';
				} else {
					$evaluated = '(Unknown)';
				}
			}
			if ($encoder) {
				$evaluated = $encoder->encodeEntities($evaluated);
			}
			$result = str_replace($container, $evaluated, $result);
		}
		preg_match_all('/#{([\w\W\s\S]*?)}#/i', $result, $matches);
		foreach ($matches[0] as $index => $container) {
			$expression = $matches[1][$index];
			$evaluated = $this->evaluate($expression);
			if (!is_null($evaluated) && !is_scalar($evaluated)) {
				if (is_array($evaluated)) {
					$evaluated = self::IsAssoc($evaluated) ? '(Object)' : '(Array)';
				} else if (is_a($evaluated, 'Pagemill_Data')) {
					$evaluated = '(Object)';
				} else if (Pagemill_Data::LikeArray($evaluated)) {
					$evaluated = '(ArrayInterface)';
				} else if (Pagemill_Data::LikeAssoc($evaluated)) {
					$evaluated = '(Interface)';
				} else {
					$evaluated = '(Unknown)';
				}
			}
			if ($encoder) {
				$evaluated = $encoder->encodeEntities($evaluated);
			}
			$result = str_replace($container, '@{' . $evaluated . '}@', $result);
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
	public function &offsetGet($offset) {
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
	public static function RegisterExprFunc($names, $function, $force = false) {
		// names must be an array so the loop below works
		if (!is_array($names))
			$names = array($names);

		// collapse array('Class', 'Function') into 'Class::Function'
		if (is_array($function))
			$function = implode('::', $function);

		// if function is not callable and
		// not being forced, report error
		if (!is_callable($function) && !$force) {
			if (is_array($names)) {
				$s = ((count($names) > 1) ? 's' : '');
				$names = implode("', '", $names);
			}
			trigger_error("Attempted to register invalid function$s '$names' in Pagemill.");
			return;
		}

		// function may be referenced by any of the given names
		foreach ($names as $name)
			self::$_exprFuncs["{$name}"] = $function;
	}
	/**
	 * The sort callback for the sortNode() function.
	 */
	private function _cmp($a, $b) {
		// convert sort key into trimmed array of arguments
		foreach (array_map('trim', explode(',', $this->_sortKey)) as $arg) {	   // set key and direction from arg
			list($key, $dir) = (is_int(strpos($arg, ' ')) ?
							explode(' ', $arg) :
							array($arg, ''));
			// set resulting direction
			$result = (('desc' == strtolower($dir)) ? -1 : 1);
			// get values from a and b using key
			$ak = $a[$key];
			$bk = $b[$key];
			// a is less than b
			if ($ak < $bk)
				return -$result;
			// a is greater than b
			if ($ak > $bk)
				return $result;
		}
		// a and b are equal
		return 0;
	}
	/**
	 * Sort arrays according by the designated keys
	 * @param string $name,... The name of the array or the path to multiple arrays (e.g.: 'parents', 'children')
	 * @param string $sort The sorting rules.  Multiple keys and ASC/DESC keywords are permitted (e.g., 'lastname, firstname' or 'birthday DESC')
	 */
	public function sortNodes(array $args) {
		if (count($args) < 2) {
			trigger_error('Pagemill_Data->sortNodes() requires an array with at least 2 elements');
			return;
		}
		// with two elements
		if (2 == count($args)) {	   // get key and value
			$key = $args[0];
			$value = $this->get($key);
			// stop if value is not an array
			if (!is_array($value)) {
				if ($value instanceof Iterator) {
					// We can try to convert into an array
					$array = array();
					foreach ($value as $k => $v) {
						$array[$k] = $v;
					}
					$value = $array;
				} else {
					/*trigger_error("Could not find '$key' to sort.");*/
					return;
				}
			}
			if (strtolower($args[1]) == 'rand()') {
				shuffle($value);
			} else {
				$this->_sortKey = $args[1];
				usort($value, array($this, '_cmp'));
			}
			$this->set($key, $value);
		} else {
			// with more than two elements
			// get key and value
			$key = array_shift($args);
			$value = $this->get($key);
			// stop if value is not an array
			if (!is_array($value)) {
				trigger_error("Could not find '$key' to sort.");
				return;
			}
			// sort each node separately
			foreach ($value as $node)
				$node->sortNodes($args);
		}
	}
	/**
	 * Create a data fork. The Pagemill can use forks to modify data within a
	 * limited scope while leaving the parent scope intact.
	 * @return \Pagemill_Data
	 */
	public function fork() {
		$forked = new Pagemill_Data();
		$forked->_handle = $this;
		$this->_tines[] = $forked;
		return $forked;
	}
	/**
	 * Get the parent of a forked data object.
	 * @return Pagemill_Data|null
	 */
	public function handle() {
		return $this->_handle;
	}
	/**
	 * Get an array of data objects forked from this one.
	 * @return Pagemill_Data[]
	 */
	public function tines() {
		return $this->_tines;
	}
	/**
	 * Declare a function that casts objects of the specified class
	 * into a type that can be processed in Pagemill_Data.
	 * @param string $class
	 * @param mixed $function
	 */
	public static function ClassHandler($class, $function) {
		self::$_classHandlers[$class] = $function;
	}
}


// Add built-in expression functions
Pagemill_Data::RegisterExprFunc('abs',										'abs');
Pagemill_Data::RegisterExprFunc('addslashes',								'addslashes');
Pagemill_Data::RegisterExprFunc(array('ceil', 'ceiling'),					'ceil');
Pagemill_Data::RegisterExprFunc('explode',									'explode');
Pagemill_Data::RegisterExprFunc('floor',									'floor');
Pagemill_Data::RegisterExprFunc('implode',									'Pagemill_ExprFunc::implode');
Pagemill_Data::RegisterExprFunc('in_array',									'Pagemill_ExprFunc::in_array');
Pagemill_Data::RegisterExprFunc('is_array',									'is_array');
Pagemill_Data::RegisterExprFunc(array('is_bool', 'is_boolean'),				'is_bool');
Pagemill_Data::RegisterExprFunc('is_float',									'is_float');
Pagemill_Data::RegisterExprFunc(array('is_int', 'is_integer'),				'is_int');
Pagemill_Data::RegisterExprFunc('is_null',									'is_null');
Pagemill_Data::RegisterExprFunc('is_object',								'is_object');
Pagemill_Data::RegisterExprFunc('is_scalar',								'is_scalar');
Pagemill_Data::RegisterExprFunc('is_string',								'is_string');
Pagemill_Data::RegisterExprFunc('nl2br',									'nl2br');
Pagemill_Data::RegisterExprFunc(array('format_number', 'number_format'),	'number_format');
Pagemill_Data::RegisterExprFunc('preg_replace',								'preg_replace');
Pagemill_Data::RegisterExprFunc('rand',										'rand');
Pagemill_Data::RegisterExprFunc('round',									'round');
Pagemill_Data::RegisterExprFunc(array('replace', 'str_replace'),			'str_replace');
Pagemill_Data::RegisterExprFunc('strlen',									'strlen');
Pagemill_Data::RegisterExprFunc(array('lowercase', 'strtolower'),			'strtolower');
Pagemill_Data::RegisterExprFunc(array('uppercase', 'strtoupper'),			'strtoupper');
Pagemill_Data::RegisterExprFunc(array('substr', 'substring'),				'substr');
Pagemill_Data::RegisterExprFunc('trim',										'trim');
Pagemill_Data::RegisterExprFunc('urlencode',								'urlencode');
Pagemill_Data::RegisterExprFunc('urldecode',								'urldecode');
Pagemill_Data::RegisterExprFunc('begins',									'Pagemill_ExprFunc::begins');
Pagemill_Data::RegisterExprFunc('contains',									'Pagemill_ExprFunc::contains');
Pagemill_Data::RegisterExprFunc('count',									'Pagemill_ExprFunc::count');
Pagemill_Data::RegisterExprFunc(array('empty', 'is_empty'),					'Pagemill_ExprFunc::is_empty');
Pagemill_Data::RegisterExprFunc('ends',										'Pagemill_ExprFunc::ends');
Pagemill_Data::RegisterExprFunc('format_phone',								'Pagemill_ExprFunc::format_phone');
Pagemill_Data::RegisterExprFunc(array('date', 'format_date'),				'Pagemill_ExprFunc::format_date');
Pagemill_Data::RegisterExprFunc('json_encode',								'Pagemill_ExprFunc::json_encode');
Pagemill_Data::RegisterExprFunc('pluralize',								'Pagemill_ExprFunc::pluralize');
Pagemill_Data::RegisterExprFunc('var_dump',                                 'Pagemill_ExprFunc::var_dump');
Pagemill_Data::RegisterExprFunc('sum',                                     'Pagemill_ExprFunc::sum');
Pagemill_Data::RegisterExprFunc('avg',                                     'Pagemill_ExprFunc::avg');
