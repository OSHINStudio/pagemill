<?php
class Pagemill_Tag_Html_Script extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->append("<script");
		$stream->append($this->buildAttributeString($data));
		if ($this->children()) {
			$stream->append(">/*<![CDATA[*/\n");
			foreach ($this->children() as $child) {
				$child->process($data, $stream, false);
			}
			$stream->append("\n/*]]>*/");
		} else {
			$stream->append(">");
		}
		$stream->append("</script>");
	}
}
