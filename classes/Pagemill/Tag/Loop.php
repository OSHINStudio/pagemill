<?php

class Pagemill_Tag_Loop extends Pagemill_Tag {
	private $_data;
	private $_stream;
	private $_as;
	private $_asKey;
	private $_cycle;
	private $_delimiter;
	private $_originalData;
	
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$cycle = explode(',', $data->parseVariables($this->getAttribute('cycle')));
		$name = $data->parseVariables($this->getAttribute('name'));
		$times = $data->parseVariables($this->getAttribute('times'));
		$delimiter = $this->getAttribute('delimiter');
		$loopTimes = 0;

		// get as attribute
		$as = $this->getAttribute('as');
		$asKey = null;
		if (strpos($as, ' ') !== false) {
			list($asKey, $as) = explode(' ', $as);
			$as = trim($as);
			$asKey = trim($asKey);
			if (!$as) {
				$as = $asKey;
				$asKey = null;
			}
		}

		$this->_originalData = $data->getArray();
		$this->_data = $data;
		$this->_stream = $stream;
		$this->_as = $as;
		$this->_asKey = $asKey;
		$this->_cycle = $cycle;
		
		
		// if name given...
		if ($name) {
			$children = $data->evaluate($name);
			
			if (is_array($children) || $children instanceof Countable) {
				if (count($children) == 0) return;
			}
			
			if (!is_array($children)) {
				if (is_a($children, 'Pagemill_Data')) {
					$children = $children->getArray();
				} else if ($children instanceof ArrayObject) {
					$children = $children->getArrayCopy();
				} else if (!$children instanceof Iterator) {
					throw new Exception('Unable to loop over object');
				}
			}
			
			if ($this->hasAttribute('sort')) {
				$sort = $data->parseVariable($this->getAttribute('sort'));
				// TODO: Figure out how to sort this thing. If it's not an
				// array of Pagemill_Data objects, this probably won't work.
				$data->sortNodes(array($name, $sort));
				$children = $data->get($name);
			}
			
			if ($this->hasAttribute('limit')) {
				// We have to do a numeric iteration.
				$start = null;
				$end = null;
				$parts = explode(',', $data->parseVariables($this->getAttribute('limit')));
				if (count($parts) == 2) {
					$start = $parts[0];
					$end = $parts[1];
				} else {
					$start = 0;
					$end = $parts[1];
				}
				if (is_array($children) || $children instanceof Countable) {
					if (count($children) < ($end - $start)) {
						$end = count($children) - $start;
					}
				}
				if (is_array($children)) {
					// TODO: for $start to $end
					$loopTimes = $this->_forLimit($children, $start, $end);
				} else if ($children instanceof SeekableIterator) {
					// TODO: seek to $start and do foreach
					$loopTimes = $this->_forEachLimit($children, $start, $end);
				} else if ($children instanceof ArrayAccess) {
					// TODO: for $start to $end
					$loopTimes = $this->_forLimit($children, $start, $end);
				} else {
					// TODO: Iterate to lower limit and proccess through upper limit
					$loopTimes = $this->_forEachLimit($array, $start, $end);
				}
			} else {
				$loopTimes = $this->_forEach($children);
			}
		}
		if ($times) {
			$start = $loopTimes;
			$this->_forTimes($start, $times);
		}
	}
	private function _forLimit($array, $start, $end) {
		$loopTimes = 0;
		for ($i = $start; $i < $end; $i++) {
			$delimit = ($i < $end - 1);
			$this->_processIteration($i, $array[$i], $delimit, $loopTimes);
			$loopTimes++;
		}
		return $loopTimes;
	}
	private function _forEach($array) {
		$loopTimes = 0;
		$count = null;
		if (is_array($array) || $array instanceof Countable) {
			$count = count($array);
		}
		foreach ($array as $key => $value) {
			// A foreach loop cannot delimit if the object is not countable
			$delimit = (!is_null($count) && $count > $loopTimes + 1);
			$this->_processIteration($key, $value, $delimit, $loopTimes);
			$loopTimes++;
		}
		return $loopTimes;
	}
	private function _forEachLimit($array, $start, $end) {
		if (is_null($start) && is_null($end)) {
			return $this->_forEach($array);
		}
		$index = 0;
		$loopTimes = 0;
		foreach ($array as $key => $value) {
			if ($index < $start && $array instanceof SeekableIterator) {
				$array->seek($start);
				$index = $start;
				$key = $array->key();
				$value = $array->current();
			}
			if ($index >= $start) {
				$this->_processIteration($key, $value, $delimit, $loopTimes);
			}
			$index++;
			if (!is_null($end) && $index >= $end) {
				break;
			}
			$loopTimes++;
		}
		return $loopTimes;
	}
	private function _forTimes($start, $end) {
		for ($i = $start; $i < $end; $i++) {
			$delimit = ($i < $end - 1);
			$this->_processIteration($i, array(), $delimit, $i);
		}
	}
	private function _processIteration($key, $value, $delimit, $loopTimes) {
		$resetKeys = array();
		if ($this->_as) {
			$this->_data[$this->_as] = $value;
			$resetKeys[] = $this->_as;
			if ($this->_cycle) {
				if (is_array($value) || $value instanceof ArrayAccess) {
					$this->_data[$this->_as]['cycle'] = $this->_cycle[$loopTimes % count($this->_cycle)];
					$this->_data[$this->_as]['loop_index'] = $loopTimes;
				} else {
					$this->_data['cycle'] = $this->_cycle[$loopTimes % count($this->_cycle)];
					$resetKeys[] = 'cycle';
					$this->_data['loop_index'] = $loopTimes;
					$resetKeys[] = 'loop_index';
				}
			}
			if ($this->_asKey) {
				$this->_data[$this->_asKey] = $key;
				$resetKeys[] = $this->_asKey;
			}
		} else {
			if (is_array($value) || $value instanceof ArrayAccess) {
				foreach ($value as $k => $v) {
					$this->_data[$k]= $v;
					$resetKeys[] = $k;
				}
			} else {
				$this->_data['loop_value'] = $value;
				$resetKeys[] = 'loop_value';
			}
			$this->_data['cycle'] = $this->_cycle[$loopTimes % count($this->_cycle)];
			$resetKeys[] = 'cycle';
			$this->_data['loop_index'] = $loopTimes;
			$resetKeys[] = 'loop_index';
		}
		foreach ($this->children() as $child) {
			$child->process($this->_data, $this->_stream);
		}
		if ($delimit) {
			$this->_stream->append($this->_delimiter);
		}
		foreach ($resetKeys as $k) {
			if (isset($this->_originalData[$k])) {
				$this->_data[$k] = $this->_originalData[$k];
			} else {
				unset($this->_data[$k]);
			}
		}
	}
	
	private function getLimits($data, $max)
	{
		if ($this->hasAttribute('limit')) {
			$limits = explode(',', $data->parseVariables($this->getAttribute('limit')));
			if (count($limits) > 1) {
				$start = $limits[0];
				$end = ($start + $limits[1]);
			} else {
				$start = 0;
				$end = $limits[0];
			}
			if ($end > $max) {
				$end = $max;
			}
		} else {
			$start = 0;
			$end = $max;
		}
		return array($start, $end);
	}
}
