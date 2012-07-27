<?php
/**-------------------------------------------------
Classe Upload: Charge un fichier sur le serveur
Classe parente : File

Date de finition : 28 Novembre 2009
Dernière modification --

Créé par Samuel Ronce

-- Tous droits réservés --
//------------------------------------------------\\

Constructeur :

	__construct(String $post_file, String $upload_dir)	: 	$post_file : la variable superglobale $_FILES['file']
															$upload_dir : le chemin où le fichier sera uploadé.

Méthode public :

	bool hasUploadError() 								: vérifie si il y a une erreur

	int uploadTypeError()								: retourne l'erreur

	String uploadName()									: retourne le nom du fichier uploadé

	String uploadType()	

	String uploadSize()									: retourne la taille du fichier uploadé

	bool uploadMoveFile(String $new_name, String $prefix = '', array $tab_safe = null)	
	: déplace le fichier vers un dossier. Retourne true si il est déplacé
	$new_name : Le nouveau nom du fichier. Si il vaut "null", le fichier aura un nom aléatoire avec un préfixe ($prefix) pouvant être définie.
	$tab_safe : Tableau contenant les extensions des fichiers pouvant être uploadés.
	
	bool extensionAutorised(array $tab_safe)			: vérifie si l'extension du fichier est contenu dans le tableau $tab_safe
	
	String keyMd5()										: retourne la clé md5 du fichier temporaire ou du nouveau fichier uploadé
					
--------------------------------------------------- **/
class Upload extends File {

	private $n_file;
	private $upload_dir;
	private $post_file;

	function __construct($post_file, $upload_dir) {
		parent::__construct($post_file['name']);
		$this->upload_dir = $upload_dir;
		$this->post_file = $post_file;
	}
	
	public function hasUploadError() {
		if ($this->post_file['error'] > 0) 
			return true;
		else
			return false;	
	}
	
	public function uploadTypeError() {
		if ($this->uploadError())
			return $this->post_file['error'];
	}
	
	public function uploadName() {
		return $this->post_file['name'];
	}
	
	public function uploadTmp() {
		return $this->post_file['tmp_name'];
	}
	
	public function uploadType() {
		$array_name = explode('/', $this->post_file['type']);
		return strtoupper($array_name[sizeof($array_name)-1]);
	}
	
	public function uploadSize() {
		return $this->post_file['size'];	
	}
	
	public function uploadMoveFile($new_name, $prefix = '', $tab_safe = null) {
		$move = false;
		if ($new_name == null)
			$rename = $this->newNameRandom($prefix);
		else
			$rename = $this->upload_dir . '/' . $new_name;
			
		if ($tab_safe != null or !empty($tab_safe)) {
			if ($this->extensionAutorised($tab_safe))
				return move_uploaded_file($this->post_file['tmp_name'], $rename);
		}
		else
			return move_uploaded_file($this->post_file['tmp_name'], $rename);
	}
	
	private function newNameRandom($prefix) {
		$size_time = strlen(time());
		$size_name = strlen($this->uploadName());
		$exten_upload = strrchr($this->upload_name(), '.');
		$sub_time = substr(time(), $size_time-6, $size_time-1);
		$sub_name = $size_name <= 5 ? $this->uploadName() : substr($this->uploadName(), 0, 5);
		$new_name = $prefix . str_replace(' ','', $sub_name . $sub_time) . $exten_upload;
		$this->n_file = $new_name;	
		return $this->upload_dir . '/' . $new_name;
	}
	
	public function extensionAutorised($tab_safe) {
		if (in_array($this->uploadType(), $tab_safe)) {
			return true;
		}
		else
			return false;
	}
	
	public function keyMd5() {
		if ($this->n_file != '')
			$name = $this->n_file;
		else
			$name = $this->uploadName();
		return md5_file($this->upload_dir . '/' . $name);
	}
}
?>