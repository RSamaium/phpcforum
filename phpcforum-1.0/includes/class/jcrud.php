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
/*
	Module jCrud 0.9.4 Beta
	
	Dernière modification : 02/04/10
	Distribution et modification possible
	
	Necessaire :
	- Jquery 1.4
	- Jquery Tools
	- Mysqli
	
*/
date_default_timezone_set('Europe/Paris');
include('xml.class.php');

interface iXML {
    public function tagOpen($parser, $tag_name, $attrs);
	public function data($parser, $data);
    public function tagClose($parser, $tag_name);
}

class CrudInput {
	
	public $name; 		// String
	public $name_;		// String
	public $register; 	// Bool
	public $class;		// String
	public $id;			// String
	public $style;		// String
	public $desc;		// String
	public $tableid;	// String
	public $sql_display; // String
	public $sql_register; // String

	public function __construct($attrs) {
		/*if (isset($attrs['TABLEID'])) {
			$this->tableid = $attrs['TABLEID'];
			$this->name = $attrs['TABLEID'];
		}
		else {*/
		if (isset($attrs['NAME'])) {
			$this->name  = $attrs['NAME'];
			$this->name_ = preg_replace('#(.*?)\\[(.*?)\\]#', '\\1', $attrs['NAME']);
		}
		
		//	$this->tableid = null;
		//}
		
		$this->register = isset($attrs['REGISTER']) ? $attrs['REGISTER'] : null;
		$this->class = isset($attrs['CLASS']) ? $attrs['CLASS'] : null;
		$this->id = isset($attrs['ID']) ? $attrs['ID'] : null;
		$this->style = isset($attrs['STYLE']) ? $attrs['STYLE'] : null;
		$this->desc = isset($attrs['DESC']) ? $attrs['DESC'] : null;
		$this->tableid = isset($attrs['TABLEID']) ? $attrs['TABLEID'] : null;
		$this->sql_display = isset($attrs['SQL_DISPLAY']) ? $attrs['SQL_DISPLAY'] : null;
		$this->sql_register = isset($attrs['SQL_REGISTER']) ? $attrs['SQL_REGISTER'] : null;
	}
	
}

class InputText extends CrudInput {

	public $type;
	public $size;
	public $maxlength;
	public $convert;
	public $label_value;

	public function __construct($attrs) {
		parent::__construct($attrs);
		$this->type = isset($attrs['TYPE']) ? $attrs['TYPE'] : null;
		$this->size = isset($attrs['SIZE']) ? $attrs['SIZE'] : null;
		$this->maxlength = isset($attrs['MAXLENGTH']) ? $attrs['MAXLENGTH'] : null;
		$this->convert = isset($attrs['CONVERT']) ? $attrs['CONVERT'] : null;
		$this->label_value = '';
	}

}

class Textarea extends CrudInput {

	public $cols;
	public $rows;

	public function __construct($attrs) {
		parent::__construct($attrs);
		$this->cols = isset($attrs['COLS']) ? $attrs['COLS'] : null;
		$this->rows = isset($attrs['ROWS']) ? $attrs['ROWS'] : null;
	}

}

class Option extends CrudInput {

	public $value;
	public $label_value;

	public function __construct($attrs) {
		parent::__construct($attrs);
		$this->value = $attrs['VALUE'];
		$this->label_value = '';
	}
}

class Select extends CrudInput {

	public $options;

	public function __construct($attrs) {
		parent::__construct($attrs);
		$this->options = array();
	}

}


class Checkbox extends CrudInput {

	public $value;
	public $label_value;

	public function __construct($attrs) {
		parent::__construct($attrs);
		$this->value = $attrs['VALUE'];
		$this->label_value = '';
	}

}

class Radio extends CrudInput {

	public $value;
	public $label_value;

	public function __construct($attrs) {
		parent::__construct($attrs);
			$this->value = $attrs['VALUE'];
			$this->label_value = '';
	}

}

class Hidden extends CrudInput {

	public $value;

	public function __construct($attrs) {
		parent::__construct($attrs);
		$this->value = '';
	}

}

class Color extends CrudInput {

	public $value;

	public function __construct($attrs) {
		$this->value = '';
	}

}
//-----------------------------------------------------
class CrudForm {

	public $label;
	public $input;
	
	private $champ_id;
	private $table;
	private $db;
	
	public function __construct($db, $attrs, $champ_id, $table) {
		$this->label = $attrs['NAME'];
		$this->input = array();
		$this->champ_id = $champ_id;
		$this->table = $table;
		$this->db = $db;
	}
	
	private function initializeMainForm($i) {
		return ' name="' . $this->input[$i]->name . '" ' . 
				($this->input[$i]->class != null ? ' class="' . $this->input[$i]->class . '" ' : ' ') . 
				($this->input[$i]->style != null ? ' style="' . $this->input[$i]->style . '" ' : ' ') . 
				($this->input[$i]->desc != null ? ' title="' . $this->input[$i]->desc . '" ' : ' ') . 
				($this->input[$i]->id != null ? ' id="' . $this->input[$i]->id . '" ' : ' ');
	}
	
	public function displayForm($data = null, $many_champ = false) {
		$html = '';
		for ($i = 0 ; $i < sizeof($this->input) ; $i++) {
			if ($many_champ && $this->input[$i]->tableid != null) {
				$data = $this->readTableId($this->input[$i]);
			}
			$main_input = $this->initializeMainForm($i);
			if ($data != null)
				$value = $data[$this->input[$i]->name_];
			else
				$value = null;

			if (isset($value) && isset($this->input[$i]->sql_display)) {
				$sql = $this->input[$i]->sql_display;
				$sql = str_replace('{prefix}', 'phpc', $sql);
				$sql = str_replace('{value}', $value, $sql);
				$result = $this->db->query($sql);
				$data_sql = $result->fetch_array();
				$value = $data_sql[0];
			}
				
			switch (get_class($this->input[$i])) {
				case 'InputText':
					if ($this->input[$i]->type != null && $this->input[$i]->type != 'password') {
						switch($this->input[$i]->type) {
							case 'date': 
								$this->input[$i]->class = 'crud_date';
							break;
							case 'number': 
								$this->input[$i]->class = 'crud_number';
							break;
							case 'hexa': 
								$this->input[$i]->class = 'crud_hexa';
							break;
						}
						$this->input[$i]->type = null;
						$main_input = $this->initializeMainForm($i);
					}
					
					if ($value != null && $this->input[$i]->convert == 'date') {
						$value = date('d-m-y', $value); 
					}
					$html .= '<input type="' . ($this->input[$i]->type != null ? $this->input[$i]->type : 'text') . '"' . 
							  $main_input . ($data != null ? ' value="' . $value . '"' : '') . 
							  ($this->input[$i]->size != null ? ' size="' . $this->input[$i]->size . '"' : '') . 
							  ($this->input[$i]->maxlength != null ? ' maxlength="' . $this->input[$i]->maxlength . '"' : '') . 
							  '/>' . $this->input[$i]->label_value;
				break;
				case 'Checkbox':
					$html .= '<input type="checkbox"' . $main_input . '  value="' . $this->input[$i]->value . '"' . 
					($data != null && $value == $this->input[$i]->value ? ' checked="checked"' : '') . ' />' . 
					$this->input[$i]->label_value;
				break;
				case 'Radio':
					$html .= '<input type="radio"' . $main_input . '  value="' . $this->input[$i]->value . '"' . 
					($data != null && $value == $this->input[$i]->value ? ' checked="checked"' : '') . ' />' . 
					$this->input[$i]->label_value;
				break;
				case 'Select':
					$html .= '<select' . $main_input . '>';
					$size_options = sizeof($this->input[$i]->options);
					for ($j=0 ; $j < $size_options ; $j++) {
						$html .= '<option value="' . $this->input[$i]->options[$j]->value . '"' . 
						($data != null && $value ==  $this->input[$i]->options[$j]->value ? ' selected="selected"' : '') . 
						'>' . $this->input[$i]->options[$j]->label_value . '</option>';
					}
					$html .= '</select>';
				break;
				case 'Textarea':
					$html .= '<textarea ' . $main_input . 
					($this->input[$i]->cols != null ? ' cols="' . $this->input[$i]->cols . '"' : '') .  
					($this->input[$i]->rows != null ? ' rows="' . $this->input[$i]->rows . '"' : '') . '>' . 
					($data != null ? $value : '') . '</textarea>';
				break;
				case 'Hidden':
					$html .= '<input type="hidden" value="' . $this->input[$i]->value . '" name="' . $this->input[$i]->name . '" />';
				break;
				case 'Color':
					$html .= '<input type="text" class="jcrud_color" maxlength="6" value="' . $value . '" name="' . $this->input[$i]->name . '" />';
				break;
			}
		}
		return $html;
	}
	
	private function readTableId($input) {
		$sql = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->champ_id . '="' . $input->tableid . '"';
		$result = $this->db->query($sql);
		$data = $result->fetch_array();
		return $data;
	}
	
	
	
}

// Module C.R.U.D. (Create, Read, Update, Delete)
class Crud  extends XML  implements iXML {

	public $labels;
	public $html;
	public $many_champ;
	
	private $table;
	private $champ_id;
	private $db;
	private $tag_value;
	private $file_struct;
	private $contents;
	private $unique;
	
	private $json_edit;
	
	public static $form_id = 1;
	
	public function __construct($xml_file, $champ_id, $table, $db) {
		parent::__construct($xml_file);
		$this->labels = '';
		$this->db = $db;
		$this->champ_id = 'id';
		$this->tag_value = false;
		$this->champ_id = $champ_id;
		$this->table = $table;
		$this->file_struct = '';
		$this->contents = array('GLOBAL' => '', 'ADD' => '', 'EDIT_AND_DELETE' => '');
		$this->many_champ = false;
		$this->unique = '';
		$this->html = array();
		$this->json_edit = array();
		
		xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "tagOpen", "tagClose");
        xml_set_character_data_handler($this->parser, "data");
		$this->parse();
		
		$this->initializeDisplay();
	}
	
	public function unique($get) {
		$this->unique = $get;
	}
	
	 public function tagOpen($parser, $tag_name, $attrs) {
		parent::tagOpen($parser, $tag_name, $attrs);
		if ($this->depth == 1) {
			$this->file_struct = strtolower($tag_name) . '.html';
		}
		switch($tag_name) {
			case 'LABEL':
				$this->createLabel($attrs);
			break;
			case 'HTML':
				//$this->createHtml($attrs);
			break;
		
			case 'TEXT':
				end($this->labels)->input[] = new InputText($attrs);
				$this->tag_value = true;
			break;
			case 'TEXTAREA':
				end($this->labels)->input[] = new Textarea($attrs);
			break;
			case 'CHECKBOX':
				end($this->labels)->input[] = new Checkbox($attrs);
				$this->tag_value = true;
			break;
			case 'RADIO':
				end($this->labels)->input[] = new Radio($attrs);
				$this->tag_value = true;
			break;
			case 'HIDDEN':
				end($this->labels)->input[] = new Hidden($attrs);
			break;
			case 'COLOR':
				end($this->labels)->input[] = new Color($attrs);
			break;
			case 'SELECT':
				end($this->labels)->input[] = new Select($attrs);
			break;
			case 'OPTION':
				$input = end(end($this->labels)->input);
				$input->options[] = new Option($attrs);
				$this->tag_value = true;
			break;
		}
    }
	
	private function createLabel($attrs) {
		$this->labels[] = new CrudForm($this->db, $attrs, $this->champ_id, $this->table);
	}

	
	public function setValueHidden($name, $value) {
		$size = sizeof($this->labels);
		for ($i = 0 ; $i < $size ; $i++) {
			$size_input = sizeof($this->labels[$i]->input);
			for ($j = 0 ; $j < $size_input ; $j++) {
				if (get_class($this->labels[$i]->input[$j]) == 'Hidden') {
					if ($this->labels[$i]->input[$j]->name == $name) {
						$this->labels[$i]->input[$j]->value = $value;
						return;
					}
				}
			}
		}
	}

    public function data($parser, $data) {
		parent::data($parser, $data);
		
		
		if (!empty($this->labels)) {
			if ($this->tag_value) {
				$label = end($this->labels);
				$input = end($label->input);
				
				switch (get_class($input)) {
					case 'Checkbox':	
						$input->label_value = $data;
					break;
					case 'InputText':	
						$input->label_value = $data;
					break;
					case 'Radio':	
						$input->label_value = $data;
					break;
					case 'Select':	
						end($input->options)->label_value = $data;
					break;
				}
			}
		}
		
		$this->tag_value = false;
		
    }

    public function tagClose($parser, $tag_name) {
		parent::tagClose($parser, $tag_name);
    }
	
	public function nbDataEdit($condition = null) {
		$sql = 'SELECT COUNT(*) AS nb FROM ' . $this->table . ($condition != null ? ' WHERE ' . $condition : '');
		$result = $this->db->query($sql);
		$data = $result->fetch_array();
		return $data['nb'];
	}
	
	public function read($order_by = null, $condition = null, $limit = null) {
		$sql = 'SELECT * FROM ' . $this->table . ($condition != null ? ' WHERE ' . $condition : '') . ' ORDER BY ' . ($order_by != null ? $order_by : $this->champ_id) . ' DESC ' . ($limit != null ? ' LIMIT ' . $limit : '');
		$result = $this->db->query($sql);
		return $result;
	}
	
	private function date2timestamp($date) {
		$date_array = explode('-', $date);
		return mktime(0, 0, 0, $date_array[1], $date_array[0], $date_array[2]);
	}
	
	/***
		Construit la chaine de caractères qui permettra de créer ou mettre à jour
		les données
	/***/
	private function constructDataCU($mode) {
		global $db;
		// Prend la taille du tableau "labels"
		$size =  sizeof($this->labels);
		// Initialisation des variables
		$str_col = '';
		$str_val = '';
		$str_update = '';
		$input_table = array();
		$k=0;
		$bool = true;
		$register = true;
		$post = null;
		// Parcours le tableau et donc le nombre de "labels"
		for ($i = 0 ; $i < $size ; $i++) {
			// Prend le nombre de champs pour chaque label
			$size_input = sizeof( $this->labels[$i]->input);
			// Parcours les champs du label
			for ($j = 0 ; $j < $size_input ; $j++) {
				// Prend le nom du champ
				$input_name = $this->labels[$i]->input[$j]->name_;
				// Si le modèle accepte l'enregistrement du champ
				if ($this->labels[$i]->input[$j]->register != 'no') {
					// On prend la données dans la variable super-globale $_POST
					if ($this->many_champ) {
							if (!in_array($this->labels[$i]->input[$j]->tableid, $input_table)) {
								$post = htmlspecialchars($_POST[$input_name][$k]);
								$input_table[] = $this->labels[$i]->input[$j]->tableid;
								$register = true;
							}
							else
								$register = false;
							
					}
					else {
						if (!is_array($_POST[$input_name])) {
							$post = htmlspecialchars($_POST[$input_name]);
						}
					}
					// Si données existante et que cette donnée n'est déjà pas enregistrée
					if ($post != null && !in_array($input_name, $input_table) && $register) {
						if ($this->labels[$i]->input[$j]->register == 'timestamp') {
							$post = $this->date2timestamp($post);
						}
						if (isset($this->labels[$i]->input[$j]->sql_register)) {
							$sql = $this->labels[$i]->input[$j]->sql_register;
							$sql = str_replace('{prefix}', 'phpc', $sql);
							$sql = str_replace('{value}', $post, $sql);
							$result = $this->db->query($sql);
							$data_sql = $result->fetch_array();
							$post = $data_sql[0];
						}
						if ($this->many_champ) {
							$bool &= $this->db->query('UPDATE ' . $this->table . '
													   SET ' . $input_name . '="' . $post . '" 
													   WHERE ' . $this->champ_id . '="' . $this->labels[$i]->input[$j]->tableid . '"');
						}
						else {
							// Selon le mode
							switch($mode) {
								case 'edit': 
									// on construit le SQL pour UPDATE
									$str_update .= ($k != 0 ? ', ' : '') . $input_name . '="' . $post . '"';
								break;
								case 'add':
									// on construit le SQL pour INSERT INTO
									$str_val .= ($k != 0 ? ', ' : '') . '"' . $post . '"';
									$str_col .= ($k != 0 ? ', ' : '') . $input_name;
									$str_update = ' (' . $str_col . ') VALUES (' . $str_val . ')';
									
								break;
							}
						
						// On rentre dans le tableau le nom de la donnée pour indiquer qu'on la enregistrée
						$input_table[] = $input_name;
						}
						// Savoir le numéro du champ du label
						$k++;
						
						$this->json_edit[$input_name] = $post;
					}
				}
			}
		}
		if ($this->many_champ)
			return $bool;
		else
			return $str_update;
	}
	
	public function update() {
		if (!empty($_POST) && isset($_POST['mode'])) {
			$mode = $_POST['mode'];
			if (isset($_POST['condition'])) {
				$condition = $_POST['condition'];
			}
			else {
				$condition = $_POST['crud_condition'];
			}

			$value = "";
			switch($mode) {
				case 'edit':
					$bool = false;
					if ($this->many_champ) {
						$bool = $this->constructDataCU($mode);
					}
					else {
						$str_update = $this->constructDataCU($mode);
						$bool = $this->db->query('UPDATE ' . $this->table . ' SET ' . $str_update . ' WHERE ' . $condition);
					}
					$value = $this->json_edit;
					// Add 30/09/10
						array_walk_recursive ($value, 'to_utf8');
					// --
					
				break;
				case 'add':
					$str_update = $this->constructDataCU($mode);
					//echo $str_update;
					if ($this->db->query('INSERT INTO ' . $this->table . $str_update)) {
						$result = $this->read($this->champ_id, null, '0, 1');
						$value = $result->fetch_array();
						// Add 30/09/10
							array_walk_recursive ($value, 'to_utf8');
						// --
					}
					else
						$value = array();
				break;
				case 'delete':
					$value = array('result' => $this->db->query('DELETE FROM ' . $this->table . ' WHERE ' . $condition));
				break;
			}
			
			echo json_encode($value);
		}
		/*elseif(!empty($_GET)) {
			$mode = $_GET['mode'];
			$start = $_GET['start'];
			switch($mode) {
				case 'next_page':
					$result = $this->read('id', null, $start . ', 4');
					while ($data = $result->fetch_array()) {
						echo $this->display($data);
					}	
				break;
			}
		}*/
	}
	
	public function pagination($page, $vars, $nb_items, $per_page, $first_item) {
	/*	$echo = '';
		$nb_pages  = ceil($nb_items / $per_page); 
		for ($i=0; $i < $nb_pages ; $i++) {
			if ($first_item == $i*$per_page)
				$echo .= ' <strong class="pagination_close">' . ($i+1) . '</strong>';
			else {
				$vars['start'] = $i*$per_page;
				$echo .= ' <a href="' . makeUrl($page, $vars) . '" class="pagination">' . ($i+1) . '</a>';
			}
		}
		return $echo;
	 */
	}
	
	private function initializeDisplay() {
		
		ob_start();
		include('forms.xml/' . $this->file_struct);
		$contents = ob_get_clean();
		
		if (preg_match('#<!-- GLOBAL -->(.*?)<!-- ADD -->#s', $contents, $matches))
			$this->contents['GLOBAL'] =  $matches[1];
		if (preg_match('#<!-- ADD -->(.*?)<!-- EDIT_AND_DELETE -->#s', $contents, $matches))
			$this->contents['ADD'] =  $matches[1];
		if (preg_match('#<!-- EDIT_AND_DELETE -->(.*)#s', $contents, $matches))
			$this->contents['EDIT_AND_DELETE'] =  $matches[1];
	}
	
	private function replaceTplBegin($contents, $data = null) {
		$begin = '';
		$k = 0;
		
		if (preg_match_all('#<!-- BEGIN_LABEL (([0-9]+)(\\.\\.([0-9]+))? )?-->(.*?)<!-- END_LABEL -->#s', $contents, $matches)) {
				$nb_champ = sizeof($this->labels);
				$j = 0;
				$nb_begin = sizeof($matches[0]);
				for ($i=0 ; $i < $nb_begin ; $i++) {
					//$k = $val[1];
					//echo $i . ' => ' . ($matches[2][$i]-1) . ' ; ' . $matches[3][$i] . ' |  ';
					$begin = '';
					if (!empty($matches[2][0])) {
						$j = $matches[2][$i]-1;
						$nb_champ = $matches[4][$i];
						if (empty($nb_champ)) {
							$nb_champ = $j+1;
						}
					}
					for ($j ; $j < $nb_champ  ; $j++) {
						$begin .= $matches[5][$i];
						$begin = str_replace('{LABEL}', $this->labels[$j]->label, $begin);
						$begin = str_replace('{FORM}', $this->labels[$j]->displayForm($data, $this->many_champ), $begin);
						//var_dump($begin);
					}
					$contents = str_replace($matches[0][$i], $begin, $contents);
				}

				
		}
		return $contents;
	}
	private function replaceTplAdd($contents) {
		$contents = preg_replace('#\\{add\\[(.*?)\\]\\}#', '<button type="button" name="crud_add"  class="crud_add">\\1</button>', $contents);
		$begin = '';
		$contents = $this->replaceTplBegin($contents);
		return $contents;
	}
	
	private function replaceTplEdit($contents, $data) {
		$contents = preg_replace('#\\{(edit|delete)\\[(.*?)\\]\\}#', '<button type="button" name="crud_\\1" class="crud_\\1" id="crud_\\1[' . ($data != null ? $data[$this->champ_id] : Crud::$form_id) . ']">\\2</button>', $contents);
		$contents = str_replace('{condition}', '<input type="hidden" name="crud_condition" value="' . $this->champ_id . '=' . $data[$this->champ_id] . '"/>', $contents);
		$contents = str_replace('<!-- BEGIN_FORM -->', '<form method="post" id="crud_form[' . ($data != null ? $data[$this->champ_id] : Crud::$form_id) . ']">', $contents);
		Crud::$form_id++;
		$contents = str_replace('<!-- END_FORM -->', '</form>', $contents);
		$contents = $this->replaceTplBegin($contents, $data);
		return $contents;	
	}
		
	public function display($data = null) {
		$nb_champ = sizeof($this->labels);
		
		if ($data == null && !$this->many_champ) {
			return $this->replaceTplAdd($this->contents['ADD']);
		}
		else {
			//var_dump($this->contents['EDIT_AND_DELETE']);
			return $this->replaceTplEdit($this->contents['EDIT_AND_DELETE'], $data);
		}
		
	}
	
	public function displayHeader() {
		$content = $this->contents['GLOBAL'];
		$content .= '<form>
						<input type="hidden" name="crud_unique" value="' . $this->unique . '" />
					</form>';
		return $content;
		
	}

}

?>