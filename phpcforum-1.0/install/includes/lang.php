<p><strong><?php echo _t("Choisissez une langue"); ?></strong></p>


<?php
echo '<ul class="choise_lang">';
	$lang = array();
	$path = '../languages';
	$dir = opendir($path);
	echo '<li data-id="fr_FR"><img src="images/flag.png" alt="fr_FR" /><a id="fr_FR_default_name" ' . ($domain == 'fr_FR' ? 'class="select_lang"' : '') . '>Français</a></li>';
	while($f = readdir($dir)) {
		if ($f != '.' && $f != '..') {
			if (is_dir($path . '/' .$f)) {
				$lang[] = $f;
				echo '<li data-id="' . $f . '"><img src="' . $path . '/' . $f . '/flag.png" alt="' . $f . '" /><a id="' . $f . '_name" ' . ($domain == $f ? 'class="select_lang"' : '') . '></a></li>';
			}
		}
	}
	closedir($dir);
	
echo '</ul>
	<script>
	$(function() {
		display_lang(' . json_encode($lang) . ');
	});
	</script>';

?>