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

$keywords = htmlspecialchars(requestVar('s', ''));
$start = requestVar('start', 0, 'unsigned int');
$type_search = requestVar('r', 'all');
$author = htmlspecialchars(requestVar('u', ''));

$submit = $keywords != '' || $author != '';

$i = 0;
if ($submit) {
	
	Plugins::flag('search', array(&$keywords, &$start, &$type_search, &$author));
	
	$result = $db->searchKeyWords($start, $config['nb_page_search'], $type_search, $keywords, $author);
	$count = $db->countSearchKeyWords($type_search, $keywords, $author);
	
	while ($row = $result->fetch_array()) {
		$text = $keywords == '' ? strExtractFirstWords($row['post_text']) : strExtract($row['post_text'], $keywords);
		$topic = array(
			'SUBJECT'			=>  $row['post_subject'],
			'POST_TIME'			=>  strTime($row['post_time'], 8),
			'TEXT'				=>  strHighlight($text, $keywords),
			'U_TOPIC'			=>  makeUrl('viewtopic.php', array('f' => $row['forum_id'], 't' => $row['topic_id'])),
			'U_FORUM'			=>  makeUrl('viewtopic.php', array('f' => $row['forum_id'])),
			'HEADER_TITLE'		=>  $i == 0
		);
		$topic = array_merge(viewprofile($row), $topic);
		$template->assignBlockVars('post', $topic);
		
		$i++;
		
	}
	
}
$template->assignVars(displayGlobaleButtons());
$template->assignVars(displayGlobaleTextHeader());
$template->assignVars(array(
	'L_SEARCH'			=> _t('Rechercher'),
	'L_RESULTS'			=> _t('Résultats'),
	'L_BY_KEYWORDS'		=> _t('Par mots clés'),
	'L_HELP_BY_KEYWORDS'=> _t('Insérez + devant un mot qui doit être trouvé et - devant un mot qui ne doit pas être trouvé. Insérez une liste de mots séparés entre des barres verticales discontinues | si seul un des mots doit être trouvé. Utilisez * comme joker pour des recherches partielles.'),
	'L_BY_AUTHOR'		=> _t('Par auteur'),
	'L_HELP_BY_AUTHOR'	=> _t('Utilisez * comme joker pour des recherches partielles.'),
	'L_SEARCH_IN'		=> _t('Rechercher dans'),
	'L_SUBJECT_AND_POST'=> _t('Titres des sujets et texte des messages'),
	'L_POST_ONLY'		=> _t('Texte des messages uniquement'),
	'L_SUBJECT_ONLY'	=> _t('Titres des sujets uniquement'),
	'L_FIRST_POST_ONLY'	=> _t('Premier message des sujets uniquement'),
	'L_VIEW_TOPIC'		=> _t('Voir le sujet'),
	'L_VIEW_FORUM_TO_TOPIC'		=> _t('Voir le forum de ce sujet'),
	'L_NB_POSTS'		=> _t('Messages'),
	'SUBMIT'			=> $submit,
	'KEYWORDS'			=> $keywords,
	'AUTHOR'			=> $author,
	'NB_RESULT'			=> sprintf(_nt('%d résultat', '%d résultats',$count['nb_post']), $count['nb_post']),
	'OPTION_MODE'		=> $type_search,
	'PAGINATION'		=> $count['nb_post'] > 0 ? pagination('search.php', array('s' => $keywords), $count['nb_post'], $config['nb_page_search'], $start) : ''
));

pageHeader($template, 'Rechercher');
$template->setTemplate('search.html');
?> 
