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
class GlobalPlugins  {
	
	protected $template;
	protected $name;
	
	protected $user;
	protected $db;
	protected $permission;
	protected $config;
	
	protected $p_db;
	
	
	public function __construct($plugin_name) {
		$this->name = $plugin_name;
		$this->initializeGlobalVar();
	}
	
	
	protected function database($class_name) {
		$this->p_db = new $class_name($this->db);
	}
	
	protected function setTemplate($filename, $path = null) {
		if ($path == null) {
			$path = DIR_PLUGINS . '/' . $this->name . '/' . DIR_PLUGINS_TEMPLATES;
		}
		$t_include = new Templates(); 
		$t_include->assignVars($this->template->getVarPlugin());
		$t_include->includeAssignBlockVars($this->template->getBlockVarPlugin());
		$t_include->setTemplate($filename, $path);
	}
	
	protected function addJs() {
		$tab_js = array();
		$path_js = DIR_PLUGINS . '/' . $this->name . '/js';
		if ($handle = opendir($path_js)) {
			while (($file = readdir($handle)) !== false) {
				$fullpath_file = $path_js . '/' . $file;
				if (is_file($fullpath_file) && preg_match('#\\.js$#', $file)) {
					$tab_js[] = preg_replace('#\\.js$#', '', $fullpath_file);
				}
			}
		closedir($handle);
		}
		$this->template->addJs('js', $tab_js, true);
	}
	
	protected function addCss() {
		$tab_css = array();
		$path_css = DIR_PLUGINS . '/' . $this->name . '/styles';
		if ($handle = opendir($path_css)) {
			while (($file = readdir($handle)) !== false) {
				$fullpath_file = $path_css . '/' . $file;
				if (is_file($fullpath_file) && preg_match('#\\.css$#', $file)) {
					$tab_css[] = preg_replace('#\\.css$#', '', $fullpath_file);
				}
			}
		closedir($handle);
		}
		$this->template->addCss('css', $tab_css, true);
	}
	
	protected function initializeGlobalVar() {
		global $user, $db, $permission, $config;
		$this->user = $user;
		$this->db = $db;
		$this->permission = $permission;
		$this->config = $config;
	}
	
}
?>