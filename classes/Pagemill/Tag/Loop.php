<?php

class Pagemill_Tag_Loop extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		// TODO: Cloning the data here to resolve scope issues.  This will
		// require extensive testing.  See the other TODO notices later in
		// this function.
		//$data = clone $data;
		$originalData = $data->getArray();

		$cycle = explode(',', $data->parseVariables($this->getAttribute('cycle')));
		$name = $data->parseVariables($this->getAttribute('name'));
		$times = $data->parseVariables($this->getAttribute('times'));
		$delimiter = $this->getAttribute('delimiter');

		$timeCounter = 0;
		$actualIteration = 0;

		// if name given...
		if ($name) {
			// get children
			$children = $data->get($name);
			// if sort set and we have more than 0 children
			if ($this->hasAttribute('sort') && (count($children) > 0)) {	   // sort and get children again
				$sort = $data->parseVariables($this->getAttribute('sort'));
				$data->sortNodes(array($name, $sort));
				$children = $data->get($name);
			}

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
			if (is_a($children, 'PMDataNode')) {
				$children = $children->getArray();
			}
			// if we have a non-empty array of children
			if (is_array($children) && (count($children) > 0)) {
				// figure out start and end from limit attribute, if any
				list($start, $end) = $this->getLimits($data, count($children));
				// define index, actual iteration, last child, old values
				$index = 0;
				$lastChild = null;
				$oldValues = array();

				// count cycles; define prefix from as, if given
				$cycles = count($cycle);
				$keys = array_keys($children);
				$resetNames = null;
				$prefix = '';
				for ($index = $start; $index < $end; $index++) {
					//$loopData = clone $data;
					$loopData = $data;
					$child = $children[$keys[$index]];
					if (is_null($resetNames)) {
						if ($as) {
							$resetNames = array($as);
							if (is_a($child, 'PMDataNode')) {
								$prefix = "{$as}->";
							} else {
								$prefix = '';
							}
							if ($asKey) {
								$resetNames[] = $asKey;
							}
						} else {
							$prefix = '';
							if (is_a($child, 'PMDataNode')) {
								$resetNames = array_keys($child->getArray());
							} else {
								$resetNames[] = 'loop_value';
							}
						}
						$resetNames[] = "{$prefix}loop_index";
						$resetNames[] = "{$prefix}loop_number";
						$resetNames[] = "{$prefix}loop_start";
						$resetNames[] = "{$prefix}loop_end";
						if ($cycles) {
							$resetNames[] = "{$prefix}cycle";
						}
					}
					if ($as) {
						/*if (is_a($child, 'PMDataNode')) {
							$loopData->set($as, $child);
						} else {
							$loopData->set($as, $child);
						}
						if ($asKey) {
							$loopData->set($asKey, $keys[$index]);
						}*/
						if (Pagemill_Data::LikeArray($loopData)) {
							$loopData[$as] = $child;
							if ($asKey) {
								$loopData[$asKey] = $keys[$index];
							}
						} else {
							throw new Exception("Not like an array?");
						}
					} else {
						if (Pagemill_Data::LikeArray($child)) {
							foreach ($child as $key => $value) {
								$loopData[$key] = $value;
							}
						} else {
							//throw new Exception("Not lika an array?");
							$loopData['loop_value'] = $child;
						}
						/*if (is_a($child, 'Pagemill_Data')) {
							//$loopData->merge($child);
							$loopData = array_merge($loopData, $child);
						} else {
							//$loopData->set('loop_value', $child);
							$loopData = array_merge($loopData->getArray(), $child);
						}*/
					}
					// set loop index, loop number
					$loopData->set("{$prefix}loop_index", $index);
					$loopData->set("{$prefix}loop_number", ($index + 1));
					$loopData->set("{$prefix}loop_start", ($index == $start));
					$loopData->set("{$prefix}loop_end", ($index == $end - 1));
					// set cycle
					$loopData->set("{$prefix}cycle", $cycle[$actualIteration % $cycles]);
					// add content to loops
					//$loops[] = $this->inner($loopData);
					foreach ($this->children() as $node) {
						$node->process($loopData, $stream);
						$stream->append($index < $end - 1 ? $delimiter : '');
					}
					//$stream->append($this->inner($loopData) . ($index < $end - 1 ? $delimiter : ''));
					// reset references
					// TODO: This might not be necessary anymore since (or IF)
					// the data node gets cloned at the beginning of the
					// function
					if ($as) {
						$data->set($as, null);
					} else {
						if (is_a($child, 'PMDataNode')) {
							foreach ($child->keys() as $k)
								$data->set($k, (isset($oldValues[$k]) ? $oldValues[$k] : null));
						}
					}
					// ???
					//$tmp = new PMDataNode($child);
					// update child
					$children[$keys[$index]] = $child;
					// next!
					++$actualIteration;
					/*foreach ($resetNames as $n) {
						$data->set($n, $originalData->get($n));
					}*/
				}
				foreach ($resetNames as $n) {
					$data->set($n, isset($originalData[$n]) ? $originalData[$n] : null);
				}
				// reset times, time counter, and iterated variable
				$times -= ($end - $start);
				$timeCounter = $times;
				$data->set($name, $children);
			}
		}

		// if times given...
		if ($times > 0) {	   // figure out start and end from limit attribute, if any
			$resetNames = null;
			list($start, $end) = $this->getLimits($data, $times);

			// count cycles
			$cycles = count($cycle);

			// loop between limits
			//for ($i = $start; $i < $end; ++$i) {	   // get old values
			for ($i = 0; $i < $times; $i++) {
				$resetNames = array();
				$loopData = $data;
				// set current values
				$resetNames[] = 'loop_index';
				$loopData->set('loop_index', $actualIteration);
				$resetNames[] = 'loop_number';
				$loopData->set('loop_number', ($actualIteration + 1));
				// set cycle
				if ($cycles) {
					$resetNames[] = 'cycle';
					$loopData->set('cycle', $cycle[$actualIteration % $cycles]);
				}
				// add content to loops
				$stream->append($this->inner($loopData) . ($i < $end - 1 ? $delimiter : ''));
				// reset values
				//foreach ($oldValues as $key => $value)
				//	$data->set($key, $value);
				// next!
				++$timeCounter;
				++$actualIteration;
			}
			/*foreach ($resetNames as $name) {
				$data->set($name, @$originalData->get($name));
			}*/
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
