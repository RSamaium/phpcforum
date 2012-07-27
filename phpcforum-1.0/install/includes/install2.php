<p><strong><?php echo _t("phpC Forum vérifie si votre serveur possède une bonne configuration pour procéder à l'installation."); ?>
</strong></p>
						<table class="table">
							<tr>
								<th>
								<?php echo _t("Configuration"); ?>
									
								</th>
								<th>
								<?php echo _t("Valeur"); ?>
									
								</th>
							</tr>
							<tr>
								<td>
									<p>
									<?php displayConfig(phpversion() >= 5); ?>
									<?php echo _t("Version de PHP"); ?>
									
									</p>
								</td>
								<td>
								<?php echo phpversion(); ?>
								</td>
							</tr>
							<tr>
							<tr>
								<td>
									<p>
									<?php displayConfig(extension_loaded('mysql')); ?>
									<?php echo _t("Mysql"); ?>
									
									</p>
								</td>
								<td>
								<?php displayExtension('mysql');
								if (extension_loaded('mysqli')) {
									echo _t(" avec l'extension mysqli");
								}
								?>
								</td>
							</tr>
							<tr>
								<td>
									<p>
									<?php displayConfig(file_exists(session_save_path())); ?>
									<?php echo _t("Chemin des sessions"); ?>
									
									</p>
								</td>
								<td>
								<?php echo session_save_path()  ?>
								</td>
							</tr>
							<tr>
								<td>
									<p>
									<?php 
									$file = '../config.php';
									displayConfig(is_readable($file) && is_writable($file)); ?>
									<?php echo _t("Fichier de configuration."); ?>
									
									</p>
								</td>
								<td>
								<?php displayRw($file); ?>
								</td>
							</tr>
							<tr>
								<td>
									<p>
									<?php 
									$file = '../images/avatars';
									displayConfig(is_readable($file) && is_writable($file)); ?>
									<?php echo _t("Dossier des avatars"); ?>
									
									</p>
								</td>
								<td>
								<?php displayRw($file); ?>
								</td>
							</tr>
							<tr>
								<td>
									<p>
									<?php 
									$file = '../images/icons';
									displayConfig(is_readable($file) && is_writable($file)); ?>
									<?php echo _t("Dossier des icônes"); ?>
									
									</p>
								</td>
								<td>
								<?php displayRw($file); ?>
								</td>
							</tr>
							<tr>
								<td>
									<p>
									<?php 
									$file = '../languages';
									displayConfig(is_readable($file) && is_writable($file)); ?>
									<?php echo _t("Dossier des langues"); ?>
									
									</p>
								</td>
								<td>
								<?php displayRw($file); ?>
								</td>
							</tr>
							<tr>
								<td>
									<p>
									<?php 
									$file = '../plugins';
									displayConfig(is_readable($file) && is_writable($file)); ?>
									<?php echo _t("Dossier des plugins"); ?>
									
									</p>
								</td>
								<td>
								<?php displayRw($file); ?>
								</td>
							</tr>
							<tr>
								<td>
									<p>
									<?php 
									$file = '../update';
									displayConfig(is_readable($file) && is_writable($file)); ?>
									<?php echo _t("Dossier des mises à jour"); ?>
									
									</p>
								</td>
								<td>
								<?php displayRw($file); ?>
								</td>
							</tr>
						</table>
