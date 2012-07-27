<?php
/**-------------------------------------------------
Classe Image: Gestion des images (cr�ation et information)
Classe parente : File

Date de finition : 28 Novembre 2009
Derni�re modification --

Cr�� par Samuel Ronce

-- Tous droits r�serv�s --
//------------------------------------------------\\

Constructeur :

	__construct(String $url_image)	: 	$url_image : le chemin vers l'image

M�thode public :

	int  witdh()								: Largeur de l'image

	int height()								: Hauteur de l'image

M�thode protected								

	resource imageCreate()							: Cr�� une image (N�cessite la librairie GD)

	bool imageX()									: retourne une image (N�cessite la librairie GD)
		
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