<?php

interface Pagemill_DoctypeInterface {
	public function entities();
	public function encodeEntities($text);
	public function tagRegistry();
	public function attributeRegistry();
}
