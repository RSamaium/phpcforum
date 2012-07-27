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

//setLang('_body', $lang);

$forum_id = requestVar('f', 0, 'unsigned int');
$permission_view_forum = true;

$db->dataForums($user);

displayAriana($forum_id);


if ($user->isCanForum($permission['forum']['forum_view'], $forum_id))
	displayForums(orderSubForum($forum_id));
else
	$permission_view_forum = false;

$template->assignVars(array(
			'AUTORIZED_VIEW_FORUM'		=> $permission_view_forum,
			'TXT_AUTORIZED_VIEW_FORUM'	=>	'Vous n\'avez pas la permission de voir ce forum',
			'SUB_FORUM'					=> true
	)
);


pageHeader($template, 'Index');
$template->setTemplate('viewforum_body.html');
?> 
