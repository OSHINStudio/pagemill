<?php
class Pagemill_Tag_Html_Script extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->puts("<script");
		$stream->puts($this->buildAttributeString($data));
		if ($this->children()) {
			$stream->puts(">/*<![CDATA[*/\n");
			foreach ($this->children() as $child) {
				$tmp = new Pagemill_Stream(true);
				$child->process($data, $tmp);
				$stream->puts(html_entity_decode($tmp->clean()));
			}
			$stream->puts("\n/*]]>*/");
		} else {
			$stream->puts(">");
		}
		$stream->puts("</script>");
	}
}
