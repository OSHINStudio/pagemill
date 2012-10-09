<?php

interface Pagemill_DoctypeInterface {
	public function entityReferences();
	public function encodeEntities($text);
	public function decodeEntities($text);
	public function tagRegistry();
}
