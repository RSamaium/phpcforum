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
include('commons.php');

$mode = requestVar('mode', 'reportlist');
$start = requestVar('start', 0, 'unsigned int');

$access = '';
etatSubmitConfirm(false);

if ($mode == 'send') {

	$forum_id = requestVar('f', 0, 'unsigned int');
	$post_id = requestVar('p', 0, 'unsigned int');
	
	$submit = isset($_POST['submit_report']);
	$reason = requestVarPost('report_reason', '');
	$notify = requestVarPost('report_notify', '');
	$report_text = requestVarPost('report_text', '');
	
	
	if (idIsExist($forum_id, 'forum')) {
		if (idIsExist($post_id, 'post')) {
		
			if ($submit) {
				$etat = $db->query('INSERT INTO ' . REPORTS . ' VALUES(
						"",
						' . $reason . ',
						' . $post_id . ',
						' . $user->data['user_id'] . ',
						' . $notify[0] . ',
						0,
						' . time() . ',
						"' . htmlspecialchars($report_text) . '",
						"",
						"")'
						);
				$url_retour = makeUrl('viewtopic.php', array('f' => $forum_id, 'p' => $post_id));
				metaRefresh(3, $url_retour);
				etatSubmitConfirm(true, array(_t('Le rapport a bien été envoyé'), _t('L\'envoie du rapport a échoué')), $url_retour, $etat);
				
			}
			else {
				etatSubmitConfirm(false);
			}
		
			$sql = 'SELECT * FROM ' . REPORTS_REASONS . ' ORDER BY reason_order';
			$result = $db->query($sql);
			while ($data = $result->fetch_array()) {
				$reasonrow = array(
							'ID'			=>	$data['reason_id'],
							'DESC'			=>	$data['reason_text']
						);

				$template->assignBlockVars('report_reason', $reasonrow);	
			}
		}
		else
			$access = _('Ce message n\'existe pas');
	}
	else
		$access = _('Ce forum n\'existe pas');

}
elseif ($mode == 'read') {

	$report_id = requestVar('r', 0, 'unsigned int');
	
	$submit_delete = isset($_POST['submit_report_delete']);
	$submit_close = isset($_POST['submit_report_close']);
	$url_retour = makeUrl('report.php');
	
	if ($user->isCan($permission['users']['read_reports'])) {
		if (idIsExist($report_id, 'report')) {

			if ($submit_close ||$submit_delete) {
				if ($submit_close) {
					$etat = $db->query('UPDATE ' . REPORTS . ' SET report_closed=1, report_close_time=' . time() . ', report_close_user_id=' . $user->data['user_id'] . '
								WHERE report_id=' . $report_id);
					$str = _t('Le rapport a bien été fermé');
					$str2 = _t('La fermeture du rapport a échoué');
					$url_retour = makeUrl('report.php', array('mode' => $mode, 'r'	=> $report_id));
				}
				elseif ($submit_delete) {
					$etat = $db->query('DELETE FROM ' . REPORTS . ' WHERE report_id=' . $report_id);
					$str = _t('Le rapport a bien été supprimé');
					$str2 = _t('La suppression du rapport a échoué');
				}
				
				
				metaRefresh(3, $url_retour);
				etatSubmitConfirm(true, array($str, $str2), $url_retour, $etat);
			
					
			}
			else {
				etatSubmitConfirm(false);
			}

				$template->assignVars(viewReports(reportsData('report_id=' . $report_id)));
			}
		else
			$access = _t('Ce rapport n\'existe pas');
	}
	else
		$access = _t('Vous n\'avez pas la permission de lire ce rapport');

}

if ($mode == 'reportlist' || $mode == 'reportlist_closed') {

	$submit_delete = isset($_POST['submit_delete_report']);
	$submit_close = isset($_POST['submit_close_report']);
	$report_check = requestVarPost('report_gestion', '');
	
	$str = $str2 = '';
	$etat = false;
	if ($submit_close || $submit_delete) {
			if ($report_check != '') {
				$in = dataCheckBox($report_check);
				if ($submit_close) {
					$etat = $db->query('UPDATE ' . REPORTS . ' SET report_closed=1, report_close_time=' . time() . ', report_close_user_id=' . $user->data['user_id'] . '
								WHERE report_id IN (' . $in . ')');
					$str = _t('Le rapport a bien été fermé');
					$str2 = _t('La fermeture du rapport a échoué');
				}
				elseif ($submit_delete) {
					$etat = $db->query('DELETE FROM ' . REPORTS . ' WHERE report_id IN (' . $in . ')');
					$str = _t('Le rapport a bien été supprimé');
					$str2 = _t('La suppression du rapport a échoué');
				}
				
				$url_retour = makeUrl('report.php', array('start' => $start));
				metaRefresh(3, $url_retour);
				etatSubmitConfirm(true, array($str, $str2), $url_retour, $etat);
			}	
	}
	else {
		etatSubmitConfirm(false);
	}

	if ($user->isCan($permission['users']['read_reports'])) {
			
			$report_closed = $mode == 'reportlist_closed' ? 1 : 0;
 
			$result = reportsData('report_closed=' . $report_closed . ' ORDER BY report_time DESC LIMIT ' . $start . ', ' . PER_PAGE_REPORTS, true);
			$nb_report = 0;
			while ($data = $result->fetch_array()) {
				$reportrow = array(
							'REPORT_ID'			=>	$data['report_id'],
							'REASON_DESC'		=>	$data['reason_text'],
							'POST_SUBJECT'		=>	$data['post_subject'],
							'POST_TIME'			=>	sprintf(_t('Posté le %s'), strTime($data['post_time'], 8)),
							'U_POST_REPORT'		=>  makeUrl('viewtopic.php', array('f' => $data['forum_id'], 't' => $data['topic_id'])) . '#p' . $data['post_id'],
							'USERNAME'			=>	$data['username'],
							'REPORT_CLOSED'		=>	$data['report_closed'],
							'REPORT_TIME'		=>	sprintf(_t('Envoyé le %s'), strTime($data['report_time'], 8)),
							'REPORT_TEXT'		=>	$data['report_text']  != '' ? $data['report_text'] : _t('Aucune autres informations')
						);

				$template->assignBlockVars('report', $reportrow);	
				$nb_report++;
		}
	}
	else
		$access = _t('Vous n\'avez pas la permission de voir les rapports envoyés');
		
	$result = $db->query('SELECT COUNT(*) AS nb_reports FROM ' . REPORTS);
	$data = $result->fetch_array();
	$template->assignVars(array(
		  'PAGINATION' 					=> pagination('report.php', null, $data['nb_reports'], PER_PAGE_REPORTS, $start),
		  'GESTION_REPORT'			 	=> $nb_report > 0,
		  'L_TITLE_REPORT'				=> _t('Liste des rapports ') . ($mode == 'reportlist_closed' ? _t('traités') : ''),
		  'REPORT_CLOSED'				=> $mode == 'reportlist_closed',
		  'U_MODE_REPORT'				=> $mode == 'reportlist_closed' ?  makeUrl('report.php') : makeUrl('report.php', array('mode' => 'reportlist_closed')),
		  'L_MODE_REPORT'				=> $mode == 'reportlist_closed' ? _t('Voir les rapports en cours') : _t('Voir les rapports traités')
		)
	);	
	
	$mode = 'reportlist';

}
	
$template->assignVars(displayGlobaleButtons());
$template->assignVars(array(
		'MODE' 				=> $mode,
		'ACCESS'			=> $access,
		'L_DELETE_REPORT'	=> _t('Supprimer les rapports cochés'),
		'L_CLOSE_REPORT'	=> _t('Fermer les rapports cochés'),
		'L_AVERT_REPORT'	=> _t('M\'informer si le rapport a été traité'),
		'L_OTHER_INFO'		=> _t('Ajouter d\'autres informations'),
		'L_REASON'			=> _t('Raison'),
		'L_REPORT_POST'		=> _t('Rapporter ce message'),
		'L_READ_REPORT'		=> _t('Lire le rapport'),
		'L_INFO_MORE'		=> _t('Informations supplémentaires'),
		'L_VIEW_POST'			=> _t('Voir le message'),
		'L_CLOSE_THIS_REPORT'	=> _t('Fermer ce rapport'),
		'L_DELETE_THIS_REPORT'	=> _t('Supprimer ce rapport'),
		'L_INFO'			=> _t('Information'),
		'L_POST_THE'		=> _t('Posté le'),
		'L_BY_MEMBER'		=> _t('Par le membre'),
		'L_REPORT_THE'		=> _t('Rapporté le'),
		'L_TITLE_POST'		=> _t('Titre du message'),
		'L_REPORT_TREATY'	=> _t('Rapport non traité'),
		'L_POST_DETAILS'	=> _t('Détails du messages'),
		'L_REPORT_USER'		=> _t('Rapporteur'),
		'L_CHECK'			=> _t('Cocher')
	)
);

pageHeader($template, _t('Rapports'));
$template->setTemplate('report.html');
?> 
