
<div id="accordion">
				<?php
						
				
					if (!empty($error)) {
				?>
				<p align="center">
				<?php
					displayErrorInstall($error);
				?>
				</p>
					<?php
				}
				?>
				
					<h3><?php echo _t('Configuration normale'); ?></h3>
					<div>
						<fieldset><legend><strong><?php echo _t("Administrateur général"); ?>
						</strong></legend>
									
									<p>
										<strong><?php echo _t("Les données de l'administrateur permettront de vous connecter au panneau d'administration après l'installation de phpC Forum"); ?>
										</strong>
									</p>
									<table class="table no-border">
										<tr>
											<td width="30%">
												<p><?php echo _t("Nom d'utilisateur"); ?>
												</p>
											</td>
											<td>
												<input type="text" name="admin_login"  />
											</td>
										</tr>
										</tr>
											<tr>
											<td>
												<p><?php echo _t("Adresse e-mail"); ?>
												</p>
											</td>
											<td>
												<input type="text" name="admin_email" />
											</td>
										</tr>
										<tr>
											<td>
												<p><?php echo _t("Mot de passe"); ?>
												</p>
											</td>
											<td>
												<input type="password" name="admin_pass" id="password"  />
											</td>
										</tr>
											<tr>
											<td>
												<p><?php echo _t("Confirmer le mot de passe"); ?>
												</p>
											</td>
											<td>
												<input type="password" name="admin_confirm_password" />
											</td>
										</tr>
										
									</table>
									
								</fieldset>
								<fieldset><legend><strong><?php echo _t("Base de données"); ?>
								</strong></legend>
								<p>
									<strong><?php echo _t("Les données de la base de données permettront à créer les tables et à créer le fichier de configuration."); ?>
									</strong>
								
								</p>
									<p id="error_db"></p>
									<table class="table no-border">
										<tr>
											<td width="30%">
												<p><?php echo _t("Nom de la base de données"); ?>
												</p>
											</td>
											<td>
												<input type="text" name="db_name"  title="<?php echo _t("Créez une base de données (dans phpMyadmin par exemple) et rentrez son nom dans ce champ"); ?>" />
											</td>
										</tr>
										<tr>
											<td>
												<p><?php echo _t("Serveur"); ?>
												</p>
											</td>
											<td>
												<input type="text" name="db_server" value="localhost" />
											</td>
										</tr>
											<tr>
											<td>
												<p><?php echo _t("Utilisateur"); ?>
												</p>
											</td>
											<td>
												<input type="text" name="db_login"  />
											</td>
										</tr>
										</tr>
											<tr>
											<td>
												<p><?php echo _t("Mot de passe"); ?>
												</p>
											</td>
											<td>
												<input type="password" name="db_pass"  />
											</td>
										</tr>
										</tr>
											<tr>
											<td>
												<p><?php echo _t("Préfixe des tables"); ?>
												</p>
											</td>
											<td>
												<input type="text" name="db_prefix" value="phpc_" title="<?php echo _t("Le préfixe permet de différencier rapidement les tables de phpC Forum de vos tables"); ?>"/>
											</td>
										</tr>
									</table>
								</fieldset>
								<input type="hidden" name="page" value="4" />
	
								
					</div>
					<h3><?php echo _t("Configuration avancée"); ?></h3>
						<div>
								<table>
										<tr>
											<td>
												<p><?php echo _t("Dossier contenant phpC Forum"); ?>
												</p>
											</td>
											<td>
												<input type="text" name="root" value="<?php echo $root; ?>" />
											</td>
										</tr>
								</table>
						</div>
				


			
