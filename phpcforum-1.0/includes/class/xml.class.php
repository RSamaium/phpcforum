<?php
/**
Copyright © Samuel Ronce 2010
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated 
documentation files (the "Software"), to deal in the Software without restriction, including without limitation 
the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and 
to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions 
of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT 
LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/
class XML {
    
	protected $parser;
	protected $depth;
	private $data;
	private $file;

    function __construct($filename) {
		$this->file = $filename;
		$this->depth = 0;
		$this->data = array();
        $this->parser = xml_parser_create();
		
    }

    public function parse() { 
        xml_parse($this->parser, file_get_contents($this->file));
    }

    protected function tagOpen($parser, $tag_name, $attrs) {
		$this->depth++;
    }

    protected function data($parser, $data) {
    }

    protected function tagClose($parser, $tag_name) {
	   $this->depth--;
    }
	
	private function ignoreTag() {
	
	}
	
	function __destruct() {
		xml_parser_free($this->parser);
	}

} 
?>