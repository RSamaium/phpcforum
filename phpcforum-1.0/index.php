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

$db->dataForums($user);
displayForums($config['order_forums'], true);
displayListForum($config['order_forums']);

displayWhoOnline();
displayListGroup();

//displayListForum($config['order_forums']);

$template->assignVars(array_merge(displayGlobaleTextLegend(), array(
		'SUB_FORUM'				=> false,
		'L_LEGEND'				=> _t('Légende'),
		'I_NEW_POST'			=> image('new_post'),
		'I_NO_NEW_POST'			=> image('no_new_post'),
		'I_POST_LOCK'			=> image('post_lock')
   ))
);



pageHeader($template, 'Index');
$template->setTemplate('index_body.html');

$time_end = microtime(true);

?> 
