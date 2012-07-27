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
Classe File : Prend les informations d'un fichier

Date de finition : 27 Novembre 2009
Dernière modification --

Créé par Samuel Ronce

-- Tous droits réservés --
//------------------------------------------------\\

Constructeur :

	__construct(String $url)				: 	$url -- Le lien vers le fichier

Méthode public :

	int size() 								: 	retourne la taille du fichier

	String strSize()						: 	retourne la taille du fichier sous forme d'une chaine de caractères 

	String type(boolean $casse = false)		: 	retourne le type du fichier 

	String keyMd5()							: 	retourne la clé Md5 du fichier

	String dirUrl()							: 	retourne le chemin du fichier

	String fileUrl()						: 	retourne le nom du fichier
					
----------------------------------------------------**/
class File {

	protected $file;

	function __construct($url, $create = false) {
		$this->file = $url;	
		if ($create)
			$this->create();	
	}
	
	protected function create() {
		$path_dir = $this->dirUrl();
		if (@opendir($path_dir) === false) {
			mkdir($path_dir, 0775, true);
		}
	}
	
	public function write($text, $ecrase = false) {
		$file = fopen($this->file, 'r+');
		$read = fgets($file); 
		if ($ecrase)
			fputs($file, $text);
		else {
			fseek($file, 3);
			fputs($file, $text);
			fputs($file, "\r\n");
		}
		fclose($file);
	}
	
	public function size() {
		if (file_exists($this->file))
			return @filesize($this->file);
		else
			return null;
	}
	
	public function strSize($round = 1) {
		$size_file = round($this->size() / 1024, $round);
			if ($size_file > 1000) {
				$size_file = round($size_file / 1024, 2);
				$unite = 'Mo';
			}
			else
				$unite = 'Ko';
		$size_file = str_replace('.', ',', $size_file);
		return $size_file . ' ' . $unite;
	}
	
	public function type($casse = false) {
		$type = (str_replace('.', '', strrchr($this->file, '.')));
		if ($casse)
			return $type;
		else
			return (strtoupper($type));
	}
	
	public function keyMd5() {
		return md5_file($this->file);	
	}
	
	public function dirUrl($path = null) {
		return $this->decomposeUrl(0, $path);
	}
	
	public function fileUrl($path = null) {
		return $this->decomposeUrl(1, $path);
	}
	
	public function copy($dest, $this_dir = false) {
		$dest = $this_dir ? $this->dirUrl() . '/' . $dest : $dest;
		if (copy($this->file, $dest))
			return new File($dest);
		else
			return null;
	}
	
	public function read() {
		return file_get_contents($this->file);
	}
	
	public function rename($new_name, $this_dir = false) {
		return rename($this->file, $this_dir ? $this->dirUrl() . '/' . $new_name : $new_name);
	}
	
	public function delete() {
		return unlink($this->file);
	}
	
	private function decomposeUrl($opt, $path) {
		$path = isset($path) ? $path : $this->file;
		$file = strrchr($path, '/');
		if(preg_match_all('#(.*?)(' . $file . ')$#', $path, $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				if ($opt == 0)
					return $match[1];
				else
					return str_replace('/', '', $file);
					
			}		
		}		
	}
	
	public function clear($root = true) {
		return $this->clearDir($this->file, $root);
	}
	
	public function movePath($dest, $exception = array()) {
		$this->moveDir($this->file, $dest, $exception);
	}
	
	private function clearDir($dir, $root, $i=0) {
		$open = @opendir($dir);
		if (!$open) return;
		while($file=readdir($open)) {
			if ($file == '.' || $file == '..') continue;
				if (is_dir($dir."/".$file)) {
					$r=$this->clearDir($dir."/".$file, $i+1);
					if (!$r) return false;
				}
				else {
					$r=@unlink($dir."/".$file);
					if (!$r) return false;
				}
		}
		closedir($open);
		if ($i == 0 && $root) {
			$r=rmdir($dir);
		}
		elseif ($i > 0) {
			$r=@rmdir($dir);
		}
		if (!$r) return false;
			return true;
	}
	
	private function moveDir($dir, $dir_dest, $exception) {
		$open = @opendir($dir);
		if (!$open) return;
		while($file=readdir($open)) {
			if ($file == '.' || $file == '..' || in_array($file, $exception)) continue;
				$path = $dir . '/' . $file;
				if (is_dir($path)) {
					$r=$this->moveDir($path, $dir_dest, $exception);
					if (!$r) return false;
				}
				else {
					$new_path = preg_replace('#^' . $this->file . '#', '', $path);
					$dir_path = $this->dirUrl($new_path);
					if (!file_exists($dir_dest . $dir_path)) {
						mkdir($dir_dest . $dir_path, 0755, true);	
					}
					$r=copy($path, $dir_dest . $new_path);
					if (!$r) return false;
				}
		}
		closedir($open);
		return true;
	}


}
?>