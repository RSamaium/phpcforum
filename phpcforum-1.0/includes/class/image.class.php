<?php
/**-------------------------------------------------
Classe Image: Gestion des images (création et information)
Classe parente : File

Date de finition : 28 Novembre 2009
Dernière modification --

Créé par Samuel Ronce

-- Tous droits réservés --
//------------------------------------------------\\

Constructeur :

	__construct(String $url_image)	: 	$url_image : le chemin vers l'image

Méthode public :

	int  witdh()								: Largeur de l'image

	int height()								: Hauteur de l'image

Méthode protected								

	resource imageCreate()							: Créé une image (Nécessite la librairie GD)

	bool imageX()									: retourne une image (Nécessite la librairie GD)
		
--------------------------------------------------- **/
class Image extends File {

	function __construct($url_image) {
		parent::__construct($url_image);
	}

	public function width() {
	 $width = @getimagesize($this->file);
	 return $width[0];
	}

	public function height() {
	 $height = @getimagesize($this->file);
	 return $height[1];
	}
	
	protected function imageCreate() {
		$url_image = $this->file;
		switch ($this->type()) {
			case 'PNG': return imagecreatefrompng($url_image); break;
			case 'JPG': return imagecreatefromjpeg($url_image); break;
			case 'GIF': return imagecreatefromgif($url_image); break;
			case 'XPM': return imagecreatefromxpm($url_image); break;
			case 'XBM': return imagecreatefromxbm($url_image); break;
			case 'WBMP': return imagecreatefromwbmp($url_image); break;
		}	
	}
	
	protected function imageX($url_image, $url_min) {
		switch ($this->type()) {
			case 'PNG': return imagepng($url_image, $url_min); break;
			case 'JPG': return imagejpeg($url_image, $url_min); break;
			case 'GIF': return imagegif($url_image, $url_min); break;
			case 'XPM': return imagexpm($url_image, $url_min); break;
			case 'XBM': return imagexbm($url_image, $url_min); break;
			case 'WBMP': return imagewbmp($url_image, $url_min); break;
		}
	
	}
}
?>