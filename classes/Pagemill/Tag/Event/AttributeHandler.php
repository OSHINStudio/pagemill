<?php
class Pagemill_Tag_Event_AttributeHandler implements Event_ObserverInterface {
	public function update($subject) {
		foreach ($subject->tag->children() as $child) {
			if ($child->name() == 'pm:attribute') {
				// TODO: Process the attribute
			}
		}
	}
}
