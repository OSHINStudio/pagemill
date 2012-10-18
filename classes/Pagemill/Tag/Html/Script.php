<?php
class Pagemill_Tag_Html_Script extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->puts("<script");
		$stream->puts($this->buildAttributeString($data));
		if ($this->children()) {
			$stream->puts(">/*<![CDATA[*/\n");
			foreach ($this->children() as $child) {
				$child->process($data, $stream, false);
			}
			$stream->puts("\n/*]]>*/");
		} else {
			$stream->puts(">");
		}
		$stream->puts("</script>");
	}
}
