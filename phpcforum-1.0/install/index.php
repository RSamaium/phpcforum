<?php
header('Content-type: text/html; charset=UTF-8'); 

require_once('../includes/functions.php');
require_once('functions_install.php');
require_once('../includes/libs/gettext.inc');

$root = requestVarPost('db_root', displayRoot());

$domain = requestVar('lang', 'fr_FR');

T_setlocale(LC_MESSAGES, $domain);
bindtextdomain($domain, '../languages');
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);


?> 
<!DOCTYPE html PUBliC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo _t('Installation de phpC Forum'); ?></title>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">

<script type="text/javascript" src="../js/jquery/jquery/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="../js/jquery/jquery/jquery.tools.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui.js"></script>
<script type="text/javascript" src="../js/jquery-ui.js"></script>
<script type="text/javascript" src="../js/jquery.validate.min.js"></script>
<script type="text/javascript" src="jquery.stepy.js"></script>
<link type="text/css" href="styles.css" rel="stylesheet" />


<script>
	$(function() {
		$("input[title]").tooltip({ 
			position: "center right", 
			offset: [-20, 5], 
			effect: "fade", 
			opacity: 0.7, 
			tip: '.tooltip' 
		});	
		
		$('#form').stepy({
			  backLabel:      '<span><?php echo _t("Précédent"); ?></span>',
			  block:          true,
			  errorImage:     true,
			  nextLabel:      '<span><?php echo _t("Suivant"); ?></span>',
			  titleClick:     false,
			  validate:       true,
			  exceptionLabel: {
				back: [5],
				next: []
			 }
		});
		
		$('#form').validate({
			errorClass: "form_error",
			validClass: "form_valid",
			success: function(label) {
				label.html("&nbsp;").addClass("form_valid");
			},
			rules: {
				'license_ok':	'required',
				admin_login: {
					required: true,
					minlength: 3
				},
				admin_pass: {
					required: true,
					minlength: 5
				},
				admin_email: {
					required: true,
					email: true
				},
				admin_confirm_password: {
					required: true,
					minlength: 5,
					equalTo: "#password"
				},
				db_name: {
					required: true
				},
				db_server: {
					required: true
				},
				db_login: {
					required: true
				}


			},
			messages: {
				'license_ok':	{required: "<?php echo _t("Acceptez la license pour continuer."); ?>"},
				admin_login: {
					required: "<?php echo _t('Un nom d\'utilisateur est requis'); ?>",
					minlength: "<?php echo _t('Le nom d\'utilisateur doit avoir minimum 3 caractères'); ?>"
				},
				admin_pass: {
					required: "<?php echo _t('Veuillez rentrer un mot de passe'); ?>",
					minlength: "<?php echo _t('Veuillez un mot de passe avec 5 caractères minimum'); ?>"
				},
				admin_confirm_password: {
					required: "<?php echo _t('Veuillez rentrer un mot de passe'); ?>",
					minlength: "<?php echo _t('Veuillez un mot de passe avec 5 caractères minimum'); ?>",
					equalTo: "<?php echo _t('le mot de passe n\'est pas identique au précédent'); ?>"
				},
				admin_email: {
					required: "<?php echo _t('Veuillez rentrer une adresse e-mail'); ?>",
					email: "<?php echo _t('Veuillez rentrer une adresse e-mail valide'); ?>"
				},
				db_name: {
					required: "<?php echo _t('Veuillez rentrer le nom de la base de données'); ?>"
				},
				db_server: {
					required: "<?php echo _t('Veuillez rentrer le nom du serveur'); ?>"
				},
				db_login: {
					required: "<?php echo _t('Veuillez rentrer le nom de l\'utilisateur de la base de données'); ?>"
				}
			}
		
		});
		
		$('#form-next-3').live('click', function() {
			$('#error_db').html('');
			var object = $('#form').serialize();
			$.post('install.php', 'f=connectToDb&' + object, function(data) {
				if (data.code_error == 0) {
					if ($('#form').valid()) {
						 install();
					}
				}
				else {
					var html = '';
					var img = '<img src="images/exclamation.png" alt="" /> ';
					if (data.error.length == 0) {
						html = img + "<?php echo _t("Impossible de joindre la base de données"); ?> [" + data.code_error + "]";
					}
					else {
						for (var i=0 ; i < data.error.length ; i++) {
							html += img + data.error[i] + '<br />';
						}
					}
					$('#error_db').html(html);
				}
			}, 'json');
			
		});
		
		$('.choise_lang li').click(function() {
			window.location = '?lang=' + $(this).attr('data-id');
		});
	
});


var nb_success = 0;
var max = 4;
var avanc = 0;

function display_lang(lang) {

	$.each(lang, function(index, value){
		$.get('../languages/' + value + '/' + value + '.xml', function(xml) {
			var text = $(xml).find('name').text();
			var isCustom = $(xml).find('version').attr('custom') == 'yes';
			if (!isCustom) {
				$('#' + value + '_name').text(text);
			}
			else {
				$('#' + value + '_name').parent('li').remove();
			}
		});
	});

}

function install() {
	var object = $('#form').serialize();
	$.post('install.php', 'f=writeConfigFile&' + object, function(success) {
		$('#install_file_config').show();
		displayAvanc(10);
		displayImg(success, '#install_file_config');
		$.post('install.php', 'f=createTable&' + object, function(success) {
			$('#install_table').show();
			displayAvanc(40);
			displayImg(success, '#install_table');
			$.post('install.php', 'f=insertData&' + object + '&lang=<?php echo $domain; ?>', function(success) {
				$('#install_insert_data').show();
				displayAvanc(40);
				displayImg(success, '#install_insert_data');
				$.post('install.php', 'f=deleteFile&' + object + '&all_success=' + nb_success, function(success) {
					//$('#install_delete_file').show();
					displayAvanc(10);
					displayImg(1, '#install_delete_file');
					if (nb_success == max) {
						$('#install_success').show();
					}
					else {
						$('#install_failed').show();
					}
				});
			});
		});
	});
}

function displayAvanc(txt) {
	$('#avanc').text(parseInt($('#avanc').text()) + txt);
}

function displayImg(success, id) {
	if (success == 1) {
		$(id + ' img').attr('src', 'images/accept.png');
		$(id + ' p').css('color', 'green');
		nb_success++;
	}
	else {
		$(id + ' img').attr('src', 'images/exclamation.png');
		$(id + ' p').css('color', 'red');
	}
}

</script>



</head>
<body>
	<div class="tooltip"></div>
		
	
<div id="container">
	<center><img src="images/logo.png"></center>
	
	
	<div class="wizard-default-style">
		<div class="step_content">
			<form id="form"> 

			<fieldset title="Etape 1"> 
				<legend><?php echo _t("Langue"); ?></legend>
					<?php include('includes/lang.php'); ?>

					
					
					
				</fieldset>
				<fieldset title="Etape 2"> 
					<legend><?php echo _t("License"); ?></legend>
					<?php include('includes/install1.php'); ?>
				</fieldset>

			<fieldset title="Etape 3"> 
					<legend><?php echo _t("Vérification"); ?></legend>
					<?php include('includes/install2.php'); ?>
			</fieldset>
			<fieldset title="Etape 4"> 
					<legend><?php echo _t("Configuration"); ?></legend>
					<?php include('includes/install3.php'); ?>
				
			</fieldset>
			<fieldset title="Etape 5"> 
					<legend><?php echo _t("Installation"); ?></legend>
					<?php include('includes/install4.php'); ?>
			</fieldset>
			</form>
		</div>
		
	</div>
		
	
</body>
</html>