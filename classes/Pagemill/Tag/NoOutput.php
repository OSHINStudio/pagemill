<?php

class Pagemill_Tag_NoOutput extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		return;
	}
	public function rawOutput(\Pagemill_Stream $stream) {
		return;
	}
}
