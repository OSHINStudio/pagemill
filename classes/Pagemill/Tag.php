<?php

class Pagemill_Tag implements Event_SubjectInterface {
	private $_before = array();
	private $_after = array();
	/**
	 * Events that occur BEFORE the tag is processed receive an object with
	 * two properties: a Pagemill_Tag and a Pagemill_Datanode.
	 */
	const EVENT_BEFORE = 'before';
	/**
	 * Events that occur AFTER the tag is processed receive a
	 * Pagemill_SimpleXmlElement.
	 */
	const EVENT_AFTER = 'after';
	public function __construct() {
		$this->attach(self::EVENT_BEFORE, new Pagemill_Tag_Event_AttributeHandler());
	}
	public function attach($event, Event_ObserverInterface $observer) {
		switch ($event) {
			case self::EVENT_BEFORE:
				$this->_before[] = $observer;
				break;
			case self::EVENT_AFTER:
				$this->_after[] = $observer;
				break;
			default:
				throw new Exception("Unrecognized Pagemill_Tag event '{$event}'");
		}
	}
	public function detach($event, Event_ObserverInterface $observer) {
		throw new Exception("Detaching events is not implemented");
	}
	public function notify($event, $object = null) {
		switch ($event) {
			case self::EVENT_BEFORE:
				foreach ($this->_before as $observer) {
					$observer->update($object);
				}
				break;
			case self::EVENT_AFTER:
				foreach ($this->_after as $observer) {
					$observer->update($object);
				}
				break;
			default:
				throw new Exception("Unrecognized Pagemill_Tag event '{$event}'");
		}	
	}
	public function output(Pagemill_DataNode as $data) {
		foreach ($this->_before as $handler) {
			$handler->process($this, $data);
		}
		// TODO: Process open()
		// TODO: process content()
		// TODO: process close()
		// At this point, $output is a string containing the output
		foreach ($this->_after as $handler) {
			$output = $handler->process($output);
		}
		return $output;
	}
}
