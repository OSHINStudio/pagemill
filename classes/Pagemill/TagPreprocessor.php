<?php
abstract class Pagemill_TagPreprocessor {
	abstract public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream);
}
