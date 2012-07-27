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
/**-------------------------------------------------
Classe Template : Génère du PHP d'une page HTML

Date de finition : 26 Novembre 2009
Dernière modification : 09 Janvier 2010

Créé par Samuel Ronce

-- Tous droits réservés --
//------------------------------------------------\\

Méthode public :

	void assignVars(array('NOM_VALEUR' => VALEUR)) 							: 	assigne un tableau de valeur
					
			 
	void assignBlockVars(String $nom_bloc, array('NOM_VALEUR' => VALEUR)) 	:  assigne un bloc de valeurs (BEGIN)
					
				 
	void setTemplate(String $nom)											: 	assigne un Template au contrôleur et l'affiche
	
	bool varExist(String $key)												:  Vérifie si une valeur est présente dans le tableau
	
	String/bool getVar(String $key)											:  Retourne la valeur de la clé du tableau

----------------------------------------------------**/

class Templates {

	// Nom du Template
	private $name;
	// Tableau contenant les variables
	private $vars;
	// Tableau contenant les blocs de variables
	private $block_vars;
	// Contenu de la page HTML
	private $contents;
	
	private $index_used;
	
	// Nom de la variable d'incrémentation pour le code BEGIN
	private static $index_for_i_name = 0;
	
	private $plugins;
	
	private $js;
	private $css;

	function __construct() {
		$this->vars = array();
		$this->block_vars = array();
		$this->plugins = array();
		$this->js = array();
		$this->css = array();
	}
	
	public function assignVars($array_vars, $encode = true) {
		//array_walk_recursive()
		$encode = false;
		foreach ($array_vars as $key => $val) {
			$this->vars[$key] = $encode ? utf8_encode($val) : $val;
		}
		
	}
	
	public function varExist($key) {
		return isset($this->vars[$key]);
	}
	
	public function getVar($key) {
		return $this->vars[$key];
	}
	
	public function getAllVar() {
		return $this->vars;
	}
	
	public function getBlockVar($key) {
		return $this->block_vars[$key];	
	}
	
	public function getAllBlockVar() {
		return $this->block_vars;
	}
	
	public function setBlockVar($var, $id, $key, $value) {
		$this->block_vars[$var][$id][$key] = $value;	
	}
	
	public function assignBlockVars($name_var, $array_vars) {
		//array_walk_recursive($array_vars, 'to_utf8');
		$array_name_vars = explode('.', $name_var);
		$this->blockDepth(0, $array_name_vars, $this->block_vars, $array_vars);
		
	}
	
	public function includeAssignBlockVars($array_vars) {
		$this->block_vars = $array_vars;
	}
	
	private function blockDepth($i, $name_vars, &$block, $array_vars) {
		
		$t = sizeof($name_vars);
		if (!isset($block[$name_vars[$i]])) {
			$block[$name_vars[$i]] = array();
		}
		
		$t2 = sizeof($block[$name_vars[$i]]);
		
		if ($i == $t-1) {
			
			$block[$name_vars[$i]][] = array();
			$t2 = sizeof($block[$name_vars[$i]]);
		
			foreach ($array_vars as $key => $val) {
				if (!isset($block[$name_vars[$i]][$t2-1][$key])) {
						$block[$name_vars[$i]][$t2-1][$key] = $val;	
				}
			}	
		}
		if ($i  >= $t-1)
			return;
		else
			$this->blockDepth(($i+1),  $name_vars, $block[$name_vars[$i]][$t2-1], $array_vars);
	
	}
	

	public function setTemplate($name, $path = null) {
		$this->name = $name;
		$this->display($path);
		
	}
	
	// Génération du code PHP.
	private function compileCode() {
		foreach ($this->vars as $key => $val) {
			$this->contents = preg_replace('#\\{' . $key . '\\}#', $val, $this->contents);
		}
		
		$this->begin_imbrique($this->contents, 0);
		if (preg_match_all('#\\{.*?\\.[0-9]+.*?\\}#', $this->contents, $matches)) {
			for ($i=0 ; $i < sizeof($matches[0]) ; $i++) {
				$this->contents = preg_replace('#' . $matches[0][$i] . '#', $this->vars_begin(preg_replace('#[\\{\\}]#', '', $matches[0][$i])), $this->contents);
				
			}
			
		}
		
		$this->contents = preg_replace('#<!-- (BEGIN|END) (.*?) -->#', '', $this->contents);
		
		$str_include = '$t_include = new Templates(); 
			$t_include->assignVars($this->vars, false);																	
			$t_include->includeAssignBlockVars($this->block_vars);';
			
		$preg_replace = array(
			'<\\?.*?\\?>'																	  => 'echo \'\\0\';',
			'<!-- IF (!)?([a-zA-Z0-9_]+)([a-zA-Z0-9=><_!" ]+)? -->' 						  => 'if (\\1$this->vars["\\2"]\\3) {',
			'<!-- IF (!)?([a-zA-Z0-9_]+\\.[0-9]+[a-zA-Z0-9_\\.]+)([a-zA-Z0-9=><_!" ]+)? -->'  => 'if (\\1$this->vars_begin("\\2")\\3) {',
			'<!-- ELSEIF (!)?([a-zA-Z0-9_]+)([a-zA-Z0-9=><_!" ]+)? -->'						  =>	'} elseif (\\1$this->vars["\\2"]\\3) {',
			'<!-- ELSEIF (.*?\\.[0-9]+.*?) -->'												  =>	'} elseif ($this->vars_begin("\\1")) {',
			'<!-- ELSE -->'																	  =>	'} else {',
			'<!-- ENDIF -->'																  =>	'}',
			'<!-- INCLUDE (.*?) -->'														  => $str_include . ' $t_include->setTemplate("\\1");',
			'<!-- INCLUDE\\[adm\\] (.*?) -->'												  => $str_include . ' $t_include->setTemplate("\\1", "' . PATH_ADM . '");',
			'<!-- PLUGIN\\[(.*?)\\] (.*?) -->'												  => 'echo Plugins::displayPlugin("\\1", "\\2")'
			
			 
		);
		foreach ($preg_replace as $key => $val) {
			$this->contents = preg_replace('#' . $key . '#', '<?php ' . $val . ' ?>', $this->contents);
		}
		
	}

	 // Cherche la donnée de la clé dans le tableau "block_vars"
	private function vars_begin($var_name) {
		$array_vars = explode('.', $var_name);
		$block = $this->block_vars;
		for ($i=0; $i < sizeof($array_vars) ; $i++) {
			$block = &$block[$array_vars[$i]];
		}
		return $block;
	}
	
	// Génération du code pour les boucles et les boucles imbriquées (Utilisation de la récursivité)
	private function begin_imbrique($text_begin, $imbrique) {
		
		// Limitation des imbriquations (à définir dans constants.php)
		if ($imbrique > LIMIT_IMBRIQUE_BEGIN)
			return;

		$regex_begin = '<!-- BEGIN (.*?) -->(.*?)(<!-- BEGINELSE -->(.*?))?<!-- END \\1 -->';
		if (preg_match_all('#' . $regex_begin . '#s', $text_begin, $matches)) {
				for ($i=0 ; $i < sizeof($matches[0]) ; $i++) {
					$text = $matches[2][$i];
					$begin_var = $matches[1][$i];
					$new_text = '';
					$var = $this->vars_begin($begin_var);
					for ($j=0 ; $j < sizeof($var) ; $j++) {
							$tmp_text = $text;
							$tmp_text = preg_replace('#(\\{' . $begin_var . ')(.*?\\})#s', '\\1.' . $j . '\\2', $tmp_text);
							$tmp_text = preg_replace('#(<!-- (BEGIN|END|IF|ELSEIF) !?' . $begin_var . ')(.*? -->)#s', '\\1.' . $j . '\\3', $tmp_text);
							$new_text .= $tmp_text;
							
					}
					
					$this->contents = str_replace($text, $new_text, $this->contents);
					if (preg_match('#' . $regex_begin . '#s', $new_text))
						$this->begin_imbrique($new_text, $imbrique+1);
			  }
		}
	}
	
	// Assigne un affichage d'un template par temporisation de sortie
	private function assignDisplay($path) {
		global $db, $user, $permission;
		$test = requestVar('test', null);
		if ($test == 'templates' && $user->isAdmin($permission)) {
			$content = $db->select(TEMPLATES, 'template_filename="' . $this->name . '"', null, null, 'template_content', 'one');
			$this->contents = htmlDoubleQuoteRev($content['template_content']);
		}
		else {
			ob_start();
			if (preg_match('#\\.js$#', $this->name)) {
				$path = 'js/tpl';
			}
			if ($path == null)
				include('styles/' . STYLE . '/templates/' . $this->name);
			else {
				include($path . '/' . $this->name);
			}
			$this->contents = utf8_encode(ob_get_clean());
		}
	}
	
	private function display($path) {
		$this->assignDisplay($path);
		$this->setAutoPlugins();
		$this->compileCode();
		eval(' ?>' . $this->contents . ' <?php ');
	}
	
	private function setAutoPlugins() {
		if (preg_match_all('#<!-- PLUGIN\\[(.*?)\\] (.*?) -->#', $this->contents, $matches)) {
			$plugins = array_unique($matches[1]);
			foreach ($plugins as $key => $plugin_name) {
				$this->setPlugin($plugin_name);
			}
		}
	}
	
	public function setPlugin($plugin_name, $ajax = false) {
		if (empty(Plugins::$plugins[$plugin_name])) {
			if (is_dir(($ajax ? '../' : '') . DIR_PLUGINS . '/' . $plugin_name)) {
				$class_path = ($ajax ? '../' : '') . DIR_PLUGINS . '/' . $plugin_name . '/' . $plugin_name . '.class.php';
				if (file_exists($class_path)) {
					require_once($class_path);				
					Plugins::$plugins[$plugin_name] = new $plugin_name($this, $plugin_name);
				}
			}
		}
	}

	public function getVarPlugin() {
		return $this->vars;
	}
	
	public function getBlockVarPlugin() {
		return $this->block_vars;
	}
	
	
	
	
	/** 
		@brief Ajoute des fichiers javascript
		@param
			name_var [string] Nom de la boucle
		@param
			paths [Array] Tableau contenant des chemins vers des fichiers javascript
		@return Void
	*/
	public function addJs($name_var, $paths_js, $path = false, $extension = false) {
		foreach ($paths_js as $key => $value) {
			$this->assignBlockVars($name_var, array(
				'SRC' => ($path ? $value : DIR_JS . '/'. $value) . (!$extension ? '.js' : '') 
			));
		}
	}
	
	/** 
		@brief Ajoute des fichiers CSS
		@param
			name_var [string] Nom de la boucle
		@param
			path [string] Le chemin vers les fichiers CSS
		@return Void
	*/
	public function addCss($name_var, $paths_css, $path = false, $extension = false) {
		global $config;
		foreach ($paths_css as $key => $value) {
			$this->assignBlockVars($name_var, array(
				'HREF' =>  ($path? $value : DIR_STYLES . '/' . $config['style'] . '/'. $value) . (!$extension ? '.css' : '') 
			));
		}
		// pr($this->block_vars);
	}
	
}
?>