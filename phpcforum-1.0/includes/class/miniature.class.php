<?php
/**
Copyright � Samuel Ronce 2010
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
Classe Miniature: Cr�e une miniature d'une image (n�cessite la librairie GD)
Classe parente : Image

Date de finition : 28 Novembre 2009
Derni�re modification --

Cr�� par Samuel Ronce

-- Tous droits r�serv�s --
//------------------------------------------------\\

Constructeur :

	__construct(String $filename, String $name_dir_min, int $taille_x, int $taille_y, int $pourcent = null)	
	: 	cr�ation automatique de la miniature � l'appel du construction
	$filename : chemin vers l'image � miniaturiser
	$name_dir_min : chemin vers le nouveau dossier o� sera stock� l'image miniaturis�e
	$taille_x : la largeur de l'image miniaturis�e
	$taille_y : la hauteur de l'image miniaturis�e
	$pourcent : si le pourcentage est diff�rent de null, l'image sera miniaturis�e du pourcentage donn� et non des tailles.
	
M�thode public :

Image createMinImage() 	: cr�er et retourne la nouvelle image miniaturis�e de type Image
				
--------------------------------------------------- **/
class Miniature extends Image {

	private $path_min;
	private $path_dir;
	private $pourcent, $taille_x, $taille_y;

	function __construct($filename, $name_dir_min, $taille_x, $taille_y, $pourcent = null) {
	
		// V�rifie si image existant
		if (!file_exists($filename)) return;
			$this->file = $filename;
		
		// Initialisation des attributs
		$this->pourcent = $pourcent;
		$this->taille_x = $taille_x;
		$this->taille_y = $taille_y;
		// D�finit les chemins
		$this->path($name_dir_min);	
		if (file_exists($this->path_min)) return;
		
		// Cr�ation de dossiers si-besoin
		$this->create();
	
	}
	
	private function path($name_dir_min) {
		$chemin = $this->dirUrl();
		$name = $this->fileUrl();
		$this->path_min = $name_dir_min . '/min_' . $name;
		$this->path_dir = $name_dir_min;		
	}
	
	public function createMinImage() {
		$pourcentage = 1;
		if ($this->pourcent != null)
			$pourcentage = $this->pourcent / 100;
		elseif ($this->width() >= $this->taille_x or  $this->height() >= $this->taille_y) {
			$taille_sup = $this->width() < $this->height() ? $this->height() : $this->width();
			$pourcentage = $this->taille_x < $this->taille_y ? $this->taille_x / $taille_sup : $this->taille_y / $taille_sup;	
		}
		
		$newwidth = $this->width()  * $pourcentage; 
		$newheight = $this->height()  * $pourcentage; 
			
		//header('Content-type: image/' . strtolower($this->type(true)));		
		$source = $this->imageCreate();
		$thumb = imagecreatetruecolor($newwidth, $newheight);	
		// Redimensionnement et affichage
		imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $this->width(), $this->height());
		$this->imageX($thumb, $this->path_min);
		
		return new Image($this->path_min);
	}	
}
?>