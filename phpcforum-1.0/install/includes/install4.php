<div>
<br />
<h3><?php echo sprintf(_t("phpC Forum est en cours d'installation [%s%%]"), '<span id="avanc">0</span>'); ?></h3>

<div id="install_file_config" class="hidden">
	<p><img src="images/accept.png" alt="" />
	<?php echo _t("Création du fichier de configuration"); ?>
	</p>
</div>
<div id="install_table" class="hidden">
	<p><img src="images/accept.png" alt="" />
	<?php echo _t("Création des tables"); ?>
	</p>
</div>
<div id="install_insert_data" class="hidden">
	<p><img src="images/accept.png" alt="" />
	<?php echo _t("Insertion des données"); ?>
	</p>
</div>
<div id="install_delete_file" class="hidden" >
	<p><img src="images/accept.png" alt="" />
	<?php echo _t("Suppression des fichiers d'installation"); ?>
	</p>
</div >
<div class="hidden">
	<p align="center"><button><?php echo _t("Aller sur le forum"); ?></button></p>
</div>
<div id="install_failed" class="hidden">
	<p align="center"><?php echo sprintf(_t("L'installation de phpC Forum a échoué. Veuillez informer ce problème sur %s Merci."), '<a href="http://phpcforum.com">http://phpcforum.com.</a>'); ?></p>
</div>
<div id="install_success" class="hidden">
	<p align="center"><?php echo _t("L'installation de phpC Forum a été un succès. Vous pouvez dès à présent configurer votre forum en cliquant sur ce lien :"); ?></p>
	<div><a href="../"><?php echo _t("Accéder à mon forum"); ?></a></div>
</div>
