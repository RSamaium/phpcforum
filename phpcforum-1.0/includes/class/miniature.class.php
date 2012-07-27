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
Classe Miniature: Crée une miniature d'une image (nécessite la librairie GD)
Classe parente : Image

Date de finition : 28 Novembre 2009
Dernière modification --

Créé par Samuel Ronce

-- Tous droits réservés --
//------------------------------------------------\\

Constructeur :

	__construct(String $filename, String $name_dir_min, int $taille_x, int $taille_y, int $pourcent = null)	
	: 	création automatique de la miniature à l'appel du construction
	$filename : chemin vers l'image à miniaturiser
	$name_dir_min : chemin vers le nouveau dossier où sera stocké l'image miniaturisée
	$taille_x : la largeur de l'image miniaturisée
	$taille_y : la hauteur de l'image miniaturisée
	$pourcent : si le pourcentage est différent de null, l'image sera miniaturisée du pourcentage donné et non des tailles.
	
Méthode public :

Image createMinImage() 	: créer et retourne la nouvelle image miniaturisée de type Image
				
--------------------------------------------------- **/
class Miniature extends Image {

	private $path_min;
	private $path_dir;
	private $pourcent, $taille_x, $taille_y;

	function __construct($filename, $name_dir_min, $taille_x, $taille_y, $pourcent = null) {
	
		// Vérifie si image existant
		if (!file_exists($filename)) return;
			$this->file = $filename;
		
		// Initialisation des attributs
		$this->pourcent = $pourcent;
		$this->taille_x = $taille_x;
		$this->taille_y = $taille_y;
		// Définit les chemins
		$this->path($name_dir_min);	
		if (file_exists($this->path_min)) return;
		
		// Création de dossiers si-besoin
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