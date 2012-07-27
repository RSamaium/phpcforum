<?php
class Forms_Validate {

	public $input;
	public $input_error;
	private $submit;
	
	
	/**
		@brief Constructeur. Initialise les attributs
		@param 
			input : [Array] Tableau contenant les caractèristiques des champs. 
					
					array(
						'name'	=> array(
											'value' 	=>	'',
											'type'		=>	'int',
											'required' 	=>	'Message',
											'remote'	=>	array('myFunction', 'this.value'),
											'equalTo'	=>	'name',
											'minlength'	=>	0,
											'maxlength'	=>  10,
											'messages'	=>	array(
																'required'
															)
										)
						)
		@details
		
		value 		[string]	:	Valeur par défaut ou après submit
		const		[string]	:	Valeur qui reste fixe après submit
		type 		[string]	:	Type (int, string, email, date)
		required 	[string]	:	Si cette clé existe, le champ est requis. Si le champ n'est pas rempli, on affiche le message de cette clé
		remote 		[array]		:	La fonction a appeler avec ses paramètres : array('myFunction', n params...). Mettez "this.value" en paramètre pour prendre la valeur saisie. Si la valeur de retour vaut "false", alors aucune erreur n'est trouvé, le champ est valide.
		equalTo		[string]	:	Vérifier ce champ avec un autre champ
		minlength 	[int]		:	Nombre minimum de caractères 
		maxlength 	[int]		:	Nombre maximum de caractères
		min 		[int]		:	Valeur minimale d'un nombre
		max			[int]		:	Valeur maximale d'un nombre
		disabled	[bool]		:   Désactive le champ : pas de vérification
		can_empty	[bool]		:   Peut être vide mais une vérification sera fait seulement si le champ est remplie
			
	*/
	public function __construct($input, $input_error = null) {
		$this->input = $input;
		$this->input_error = $input_error;
		$this->submit = false;
		if (!empty($_POST)) {
			$this->submit = true;
		}
		$this->initializeValue($_POST);
	}
	
	public function isValidate() {
		$error = array();
		$empty = false;
		if ($this->submit) {
			foreach ($this->input as $name => $shema) {
				if ($shema['type'] == 'file') {
					$value = $_FILES[$name];
				}
				else {
					$value = $shema['value'];
				}
				$error_name = $this->input_error[$name];
				if (isset($shema['disabled']) && $shema['disabled']) {
					continue;
				}
				if (isset($shema['can_empty']) && $shema['can_empty'] && $value == '') {
					continue;
				}
				if (isset($shema['ifFieldIs']) && $this->input[$shema['ifFieldIs'][0]]['value'] != $shema['ifFieldIs'][1]) {
					continue;
				}
				if (isset($shema['required']) && ($value == '' || $shema['type'] == 'file')) {
					if ($shema['type'] == 'file') {
						if (!$this->testFiles($value, $shema, 'required')) {
							$error[] = $shema['required'];
							$empty = true;
						}
						else {
							$empty = false;
						}
					}
					else if ($value == '') {
						$error[] = $shema['required'];
						$empty = true;
					}
					else {
						$empty = false;
					}
					
				}
				elseif (isset($shema['if']) && $value) {
						$error[] = $error_name['if'];
				}
				elseif (!$this->typeValid($shema['type'], $value)) {
						if (isset($error_name['type']))
							$error[] = $error_name['type'];
						else
							$error[] = $error_name[$shema['type']];
				}
				elseif (isset($shema['remote']) && $this->remote_function($shema['remote'], $value) === false) {
						$error[] = $error_name['remote'];
				}
				elseif (isset($shema['equalTo']) && $this->input[$shema['equalTo']]['value'] != $value) {
						$error[] = $error_name['equalTo'];
				}
				
				elseif (isset($shema['maxlength']) &&  $shema['maxlength'] < strlen($value)) {
						$error[] = $error_name['maxlength'];
				}
				else if (!$empty && isset($shema['minlength']) && $shema['minlength'] > strlen($value)) {
					$error[] = $error_name['minlength'];
				}
				elseif (isset($shema['min']) && $shema['min'] > $value) {
						$error[] = $error_name['min'];
				}
				if (!$empty && isset($shema['max'])) {
						if ($shema['type'] == 'file') {
						
						}
						else if ($shema['max'] < $value) {
							$error[] = $error_name['max'];
								
						}
						
				}
				if (!$empty &&  isset($shema['maxlength'])) {
						if ($shema['type'] == 'file') {
							if (!$this->testFiles($value, $shema, 'maxlength')) {
								$error[] = $error_name['maxlength'];
								
							}
						}
						else if ($shema['maxlength'] < strlen($value)) {
							$error[] = $error_name['maxlength'];
						}
						
						
				}
				if (!$empty && isset($shema['extension']) && $shema['type'] == 'file' && !$this->testFiles($value, $shema, 'extension')) {
					$error[] = $error_name['extension'];
				}
				
				if (!$empty && isset($shema['mime']) && $shema['type'] == 'file' && !$this->testFiles($value, $shema, 'mime')) {
					$error[] = $error_name['mime'];
				}
				
				if (!$empty && isset($shema['omit'])) {
						if ($shema['type'] == 'file') {
							if (!$this->testFiles($value, $shema, 'omit')) {
								$error[] = $error_name['omit'];
							}
						}
						else if (preg_match('#' . $shema['omit'] . '#s', $value)) {
							$error[] = $error_name['omit'];
						}
						
				}
			

			}
			
		}
		return $error;
	}
	
	public function insertDb($db, $table) {	
		foreach ($this->input as $name => $shema) {
			if (isset($shema['register']) && !$shema['register']) continue;
			$str_key .= '`' . $name . '`, ';
			$str_value .= '"' . $shema['value'] . '", ';
		}
		$str_key = preg_replace('#, $#', '', $str_key);
		$str_value = preg_replace('#, $#', '', $str_value);
		$sql = 'INSERT INTO ' . $table . ' (' . $str_key . ') VALUES (' . $str_value . ')';
		// pr($sql);
		$query = $db->query($sql);
		return array('success' => $query, 'id' => $db->insert_id);	
	}
	
	public function updateDb($db, $table, $where) {	
		$str = '';
		foreach ($this->input as $name => $shema) {
			if (isset($shema['register']) && !$shema['register']) continue;
			$str .= $name. '="' . $shema['value'] . '",';
		}
		$str = preg_replace('#,$#', '', $str);
		$sql = 'UPDATE ' . $table . ' SET ' . $str . ($where != null ? ' WHERE ' . $where : '');
		return $db->query($sql);
	
	}
	
	private function initializeValue($post) {
		foreach ($this->input as $name => $value) {
			if ($this->submit) {
				if (isset($value['const'])) {
					$this->input[$name]['value'] = $value['const'];
				}
				else {
					$this->input[$name]['value'] = $post[$name];
				}
			}
			else {
				if (!isset($value['value'])) {
					$this->input[$name]['value'] = '';
				}
			}
		}
	}
	
	private function isEmail($string) {
		return preg_match('/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/', $string) || (preg_match('/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/', $string));
	}
	
	private function typeValid($type, $value) {
		$bool = false;
		switch($type) {
			case 'email': 
				$bool =  $this->isEmail($value);
			break;
			case 'int': 
				$bool = is_int($value);
			break;
			case 'string': 
				$bool =  is_string($value);
			break;
			case 'hexa': 
				$bool = ctype_xdigit($value);
			break;
			case 'captcha': 
				$bool = $value == '';
			break;
			case 'file': 
				$bool = true;
			break;
		}
		return $bool;
	}
	
	private function remote_function($params, $value) {	
		$function_name = array_shift($params);
		$size = sizeof($params);
		for ($i=0 ; $i < $size ; $i++) {
			if ($params[$i] == 'this.value') {
				$params[$i] = str_replace('this.value', $value, $params[$i]);
			}
		}
        $ret = !call_user_func_array($function_name, $params);  
		if (is_bool($ret)) {
			return $ret;
		}
		return false;
	}
	
	private function testFiles($value, $shema, $field) {
		if ($shema['type'] != 'file') {
			return false;
		}
		$files = array();
		if (!is_array($value['name'])) {
			foreach ($value as $key => $file_value) {
				$value[$key] = array($file_value);
			}
		}
		$files = $value;
		for ($i=0 ; $i < count($files['name']) ; $i++) {
			if ($field == 'required' && $files['name'][$i] == '') {
				return false;
			}
			elseif ($field == 'extension') {
				$extension = strtoupper(substr(strrchr($files['name'][$i],'.'), 1));
				if (!in_array($extension, $shema['extension'])) {
					return false;
				}
				
				
			}
			elseif ($field == 'maxlength' && $shema['maxlength'] < $files['size'][$i]) {
				return false;
			}
			elseif ($field == 'mime' ) {
				$finfo = new finfo(FILEINFO_MIME, $files['tmp_name'][$i]);
				if (!in_array(finfo_file($finfo, $files['tmp_name'][$i]), $shema['mime'])) {
					finfo_close($finfo);
					return false;
				}
				finfo_close($finfo);
				
			}
			elseif ($field == 'omit' && preg_match('#' . $files['tmp_name'][$i] . '#s', $value)) {
				return false;
			}
		}
		return true;
	}
}
?>