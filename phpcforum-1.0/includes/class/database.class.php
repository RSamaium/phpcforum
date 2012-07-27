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
class MySQL_Result {

	private $result;

	function __construct($result) {
		$this->result = $result;
	}
	
	public function fetch_array() {
		return mysql_fetch_array($this->result);
	}
	
	public function fetch_assoc() {
		return mysql_fetch_assoc($this->result);
	}
}


class MySQL {

	protected $db;
	public static $nb_query = 0;
	public $insert_id;
	private $error_select_db = 0;
	
	function __construct($user, $pass, $db, $server = 'localhost') {
		$this->db = @mysql_connect($server, $user, $pass);
		if (!@mysql_select_db($db, $this->db)) {
			$this->error_select_db =  $this->errno();
		}	
	}

	
	public function query($sql) {
		MySQL::$nb_query++;
		$b = @mysql_query($sql);
		if (!is_bool($b)) {
			$result = new MySQL_Result($b);
		}
		else {
			$result = $b;
		}
		
		$this->insert_id = @mysql_insert_id();
		return $result;
	}
	
	protected function error() {
		return mysql_error();
	}
	
	protected function errno() {
		if ($this->error_select_db != 0) {
			return $this->error_select_db;
		}
		return mysql_errno();
	}
	
	protected function close() {
		@mysql_close($this->db);
	}
	
	
}

class DataBase extends MySQL {
	
	public $forum;
	public $groups;
	public $image_set;
	public $icons;

	function __construct($user, $pass, $db, $server = 'localhost') {
		//parent::mysqli($server, $user, $pass, $db);
		parent::__construct($user, $pass, $db, $server);
		$this->query("SET NAMES 'utf8';");
		$this->forum = array();
	}
	
	/**
		@brief Assigne la ressource à la classe
	*/
	public function setResourceDb($db) {
		$this->db = $db;
	}
	
	public function getError() {
		return $this->error();
	}
	
	public function getErrno() {
		return $this->errno();
	}
	
	public function select($table, $where = null, $orderby = null, $limit = null, $select = '*', $option = 'all') {
		$sql = 'SELECT ' .  $select . ' FROM ' . $table . 
				($where != null ? ' WHERE ' .  $where : '') .
				($orderby != null ? ' ORDER BY ' .  $orderby : '') .
				($limit != null ? ' LIMIT ' .  $limit : '');
		$result = $this->query($sql);
		  // pr($sql);
		if ($option == 'one')
			return $result->fetch_array();
		else
			return $result;
	}
	
	
	public function insert($table, $insert) {
		$str_key = '';
		$str_value = '';
		foreach ($insert as $key => $value) {
			$str_key .= '`' . $key . '`, ';
			$str_value .= '"' . $value . '", ';
		}
		$str_key = preg_replace('#, $#', '', $str_key);
		$str_value = preg_replace('#, $#', '', $str_value);
		$query = $this->query('INSERT INTO ' . $table . ' (' . $str_key . ') VALUES (' . $str_value . ')');
		return $query;
	}
	/**
	 * $set : array
	 * where : string
	 */
	public function update($table, $set, $where = null, $free_sql = false) {
		$str = '';
		$size = sizeof($set);
		$i = 0;
		foreach ($set as $key => $value) {
			if ($free_sql) {
				$str .= $key . $value . '' . ($i != $size-1 ? ', ' : '');
			}
			else {
				$str .= $key . '="' . $value . '"' . ($i != $size-1 ? ', ' : '');
			}
			
			$i++;
		}
		$str = preg_replace('#, $#', '', $str);
		$sql = 'UPDATE ' . $table . ' SET ' . $str . ($where != null ? ' WHERE ' . $where : '');
		// pr($sql);
		return $this->query($sql);
	}
	
	public function delete($table, $where, $secure = true, $free_sql = false) {
		$str = '';
		$size = sizeof($set);
		$i = 0;
		foreach ($where as $key => $value) {
			$str .= $key . ($free_sql ? '' : '="') . $value . ($free_sql ? '' : '"') . ($i != $size-1 ? ' AND ' : '');
			$i++;
		}
		$str = preg_replace('#AND $#', '', $str);
		if ($secure && empty($where)) {
			return false;
		}
		else {
			$sql = 'DELETE FROM ' . $table . (empty($where) ? '' : ' WHERE ' . $str);
			// pr($sql);
			return $this->query($sql);
		}
	}

	public function insertPm($user, $time, $subject, $text) {
		return $this->insert(PM, array(
			'author_id'		=>  $user->data['user_id'],
			'author_ip'		=>  $user->current_ip,
			'pm_time'		=> 	$time,
			'pm_subject'	=> 	$subject,
			'pm_text'		=> 	addslashes($text)
		
		));
		
	}
	
	
	
	public function insertPmTo($user, $pm_id, $user_dest, $pm_id_replied) {
		return $this->insert(PM_TO, array(
			'pm_id'			=>  $pm_id,
			'user_id'		=>  $user_dest,
			'author_id'		=>  $user->data['user_id'],
			'pm_id_replied'	=> $pm_id_replied
		
		));
		
	}
	
	public function dataUser($username, $exception = null) {
		 return $this->select(USERS, 'username="' . htmlspecialchars($username) . '"' . (isset($exception) ? ' AND user_id != ' . $exception : ''), null, null, 'user_id', 'one');
	}
	
	
	public function dataGlobalUser($user_id, $select = null) {
		 return $this->select(USERS . ' u, ' . GROUPS . ' g', 'u.group_id=g.group_id AND user_id=' . $user_id, null, null, $select == null ? '*' :  implode(',', $select), 'one');
	}
	
	public function forumLastPost() {
		$forum = array();
		$result = $this->select(FORUMS . ' f,' . POSTS . ' p,' . USERS . ' u,' . GROUPS . ' g', 'f.forum_last_post_id=p.post_id AND p.poster_id=u.user_id AND u.group_id=g.group_id');
		while ($data = $result->fetch_array()) {
			$forum[$data['forum_id']] = array(
				'forum_last_post_time' 			=> $data['post_time'],
				'forum_last_post_poster_id'		=> $data['user_id'],
				'forum_last_post_subject'		=> $data['post_subject'],
				'forum_last_post_poster_colour'	=> $data['group_color'],
				'forum_last_post_poster_name'	=> $data['username']
			);
		}
		return $forum;
	}
	
	public function updateLastPostForum($forum_id, $post_id) {
		 return $this->update(FORUMS, array(
		 	'forum_last_post_id'	=>	$post_id
		 ), 'forum_id=' . $forum_id);
	}
	
	public function dataForums($user) {
		$session_exist = !empty($user->session_data['session_start']);
		if ($session_exist) {
			$result = $this->select(FORUMS . ' f', null, null, null, '*, (SELECT COUNT(*) FROM ' . TOPICS . ' t, ' . POSTS . ' p 
					WHERE topic_last_post_id=post_id AND post_time > ' . $user->data['user_lastmark'] . ' AND poster_id != ' . $user->data['user_id'] . '
					AND f.forum_id = t.forum_id) AS nb_topics_unread');
		}
		else {
			$result = $this->select(FORUMS);
		}
		
		
		
		$topic_read = array();
		$result_read = $this->select(TOPICS_READ, 'user_id=' . $user->data['user_id'] . ' AND state=1 GROUP BY forum_id', null, null, 'forum_id, COUNT(topic_id) AS nb_topic_read');
		while ($data = $result_read->fetch_array()) {
			$topic_read[$data['forum_id']] = $data['nb_topic_read'];
		}
		
		$last_post = $this->forumLastPost();
		$last_post_time = 0;
		
		while ($data = $result->fetch_array()) {
			$forum = &$this->forum[$data['forum_id']];
			if ($session_exist) {
				$parents_id = threadAriana($data['forum_id'], array());
				if ($user->isRegister()) {
					$nb_topics_unread = $data['nb_topics_unread'] - $topic_read[$data['forum_id']];
					
				}
				else {
					$nb_topics_unread = 0;
				}
				
				
				if (is_array($parents_id)) {
					foreach($parents_id as $key => $value) {
						if (!isset($this->forum[$value])) {
							$this->forum[$value] = array();
						}
						$this->forum[$value]['forum_nb_subject'] += $data['forum_nb_subject'];
						$this->forum[$value]['forum_nb_post'] += $data['forum_nb_post'];
						
						if ($this->forum[$value]['forum_last_post_time'] < $last_post[$data['forum_id']]['forum_last_post_time']) {
								$this->forum[$value]['forum_last_post_id']		=  $data['forum_last_post_id'];
								$this->forum[$value]['forum_last_post_time']		=  $last_post[$data['forum_id']]['forum_last_post_time'];
								$this->forum[$value]['forum_last_post_poster_id']	=  $last_post[$data['forum_id']]['forum_last_post_poster_id'];
								$this->forum[$value]['forum_last_post_poster_name']	= $last_post[$data['forum_id']]['forum_last_post_poster_name'];
								$this->forum[$value]['forum_last_post_poster_colour']	= $last_post[$data['forum_id']]['forum_last_post_poster_colour'];	
						}
					}
				}
				
				if (!isset($this->forum[$data['forum_id']])) {
					$forum = array();
					$forum['nb_topics_unread'] = $nb_topics_unread;
					$forum['forum_nb_subject'] = $data['forum_nb_subject'];
					$forum['forum_nb_post'] = $data['forum_nb_post'];
					$forum['forum_last_post_id']		=  $data['forum_last_post_id'];
					$forum['forum_last_post_time']		=  $last_post[$data['forum_id']]['forum_last_post_time'];
					$forum['forum_last_post_poster_id']	=  $last_post[$data['forum_id']]['forum_last_post_poster_id'];
					$forum['forum_last_post_poster_name']	= $last_post[$data['forum_id']]['forum_last_post_poster_name'];
					$forum['forum_last_post_poster_colour']	= $last_post[$data['forum_id']]['forum_last_post_poster_colour'];
					$last_post_time = $forum['forum_last_post_time'];
				}
			}
			else {
				$forum = array();
			}
			$forum = array_merge($forum, array(
				// 'parent_id'					=>  $data['parent_id'],
				'forum_name'				=>  $data['forum_name'],
				'forum_desc'				=>  $data['forum_desc'],
				'forum_rules'				=>  $data['forum_rules'],
				'forum_iconset_id'			=>  $data['iconset_id'],
				'forum_icon_mandatory'		=>  $data['forum_icon_mandatory'],
				'forum_image'				=>  $data['forum_image'],
				'forum_status'				=>  $data['forum_status'],
				'forum_type'				=>  $data['forum_type'],
				'forum_share_facebook'		=>  $data['forum_share_facebook'],
				'forum_share_twitter'		=>  $data['forum_share_twitter']
			));
		}
	}
	
	/**
	 * Données d'un sujet
	 *
	 */
	public function dataTopic($topic_id) {
		$icons = $this->dataIcons();
		return $this->select(TOPICS . ' t,' . POSTS . ' p',
			   't.topic_id=' . $topic_id . ' AND t.topic_last_post_id=p.post_id', null, null, '*', 'one');
	}
	
	public function dataIcons() {
		$result = $this->select(ICONS);
		$row = array();
		while ($data = $result->fetch_assoc()) {
			$row[$data['icon_id']] = $data;
		}
		$this->icons = $row;
		return $row;
	}
	
	public function dataPost($post_id) {
		return $this->select(POST . ' p,' . ICONS . ' i', 'p.icon_id=i.icon_id AND post_id=' . $post_id);
	}
	
	public function deletePm($tab, $in, $user_id) {
		$b = $this->update(PM_TO, array(
			'pm_deleted'  =>	1
		), 
		'pm_id IN (' . $in . ') AND user_id=' . $user_id);
		$this->deleteRealPm($tab);
		return $b;
	}
	
	public function deleteSendPm($tab, $in) {
		$b = $this->update(PM, array(
			'pm_send_deleted'  =>	1
		), 
		'pm_id IN (' . $in . ')');
		$this->deleteRealPm($tab);
		return $b;
	}
	
	private function deleteRealPm($tab) {
		$size = sizeof($tab);
		for ($i=0 ; $i < $size ; $i++) {
			$pm_id = $tab[$i];
			$data = $this->select(PM . ' pm,' . PM_TO . ' pt', 'pt.pm_id=pm.pm_id AND pt.pm_id=' . $pm_id . ' AND (pm_deleted = 0 OR pm_send_deleted = 0)', null, null, 'pm.pm_id', 'one');
			if (empty($data['pm_id'])) {
				$this->delete(PM, array(
					'pm_id'	=> $pm_id
				));
				$this->delete(PM_TO, array(
					'pm_id'	=> $pm_id
				));
			}
		}
	}
	
	/**
	 * Liste de tous les topics
	 *
	 */
	public function topic($forum_id, $topic_type, $key, $start, $subject_per_page) {
		return $this->select(TOPICS . ' t, ' . USERS . ' u, ' . GROUPS . ' g, ' . POSTS . ' p',
		't.forum_id=' . $forum_id . ' AND t.topic_last_post_id=p.post_id AND t.topic_poster=u.user_id AND u.group_id=g.group_id AND t.topic_type ' . $topic_type . ' 0',
		't.topic_type DESC, p.post_time DESC',
		($key == 'topic' ? $start . ', ' . $subject_per_page : null), '*, t.icon_id AS topic_icon_id');

	}
	
	
	public function updateProfile($name_post, $user) {
		$ret = $this->db->update(USERS, dataPost($name_post), 'user_id=' . $user->data['user_id']);
		$user->updateData();
		return $ret;
	}
	
	public function pmCount($user_id) {
		return $this->select(PM_TO, 'user_id=' . $user_id . ' AND pm_deleted=0 AND pm_read=0', null, null, 'COUNT(*) AS nb_pm', 'one');
	}
	
	public function pmLast($user_id, $time) {
		return $this->select(PM_TO . ' pt, ' . PM . ' pm, ' . USERS . ' u', 'pt.user_id=u.user_id AND pt.user_id=' . $user_id . ' AND pm.pm_id=pt.pm_id AND pm_deleted=0 AND pm_read=0 AND pm_time > ' . $time, 'pm_time DESC');
	}
	
	public function login($username, $password) {
		return $this->select(USERS, 'username="' . htmlspecialchars($username) . '" AND user_password="' . md5($password) . '" AND user_activ=1', null, null, '*', 'one');
	}
	
	public function selector() {
		return $this->select(DESIGN);
	}
	
	public function plugins() {
		return $this->select(PLUGINS, "plugin_autoload=1 AND plugin_activ=1");
	}
	
	public function imageset($lang) {
		return $this->select(DESIGN_IMAGESET, 'image_lang="' . $lang . '"');
		
	}
	
	public function updateCreatePostInTopic($topic_id, $post_id) {
		$this->update(TOPICS, array(
			'topic_first_post_id'	=> $post_id,
			'topic_last_post_id'	=> $post_id
		),
		'topic_id=' . $topic_id);
	}
	
	public function updateReplyPostInTopic($topic_id, $post_id) {
		$this->update(TOPICS, array(
			'topic_last_post_id'	=> $post_id
		),
		'topic_id=' . $topic_id);
	}
	
	public function changePollOptionTotal($type, $poll_option_id) {
		$this->update(POLL_OPTIONS, array(
				'poll_option_total' => '=poll_option_total' . ($type ? '+' : '-') . '1'
		),
		'poll_option_id=' . $poll_option_id,
		true);
		
	}
	
	/*
	 * $activ :
	 * 	0 : Compte automatiquement activé
	 *  1 : Compte activé après activation par email
	 *  2 : Compte activé seulement pas l'admin.
	 */
	public function newUser($username, $password, $email, $activ) {
			
			do {
				$activ_id = md5(rand());
				$data = $this->select(USERS, 'user_activ_id="' . $activ_id, null, null . '"', '*', 'one');
			}
			while (!empty($data));
		$b = $this->insert(USERS, array(
			'group_id'			=>		GROUP_MEMBER_ID,
			'username'			=>		utf8_encode($username),
			'user_password'		=>		md5($password),
			'user_regdate'		=>		time(),
			'user_ip'			=>		'',
			'user_email'		=> 		$email,
			'user_activ'		=>		$activ > 0 ? 0 : 1,
			'user_activ_id'		=>		$activ_id,
			'user_options'		=>		'fb3'
		));
		if ($b) {
			return $activ_id;
		}
		else
			return false;
	}
	
	public function firstPost($topic_id) {
		return $this->select(POSTS, 'topic_id=' . $topic_id, null, null, '*, MIN(post_time)', 'one');
	}
	
	public function lastPost($topic_id) {
		return $this->select(POSTS, 'topic_id=' . $topic_id, null, null, '*, MAX(post_time)', 'one');
	}
	
	public function synchronizedPostInTopic($topic_id, $post, $last_post_id) {
		$stat_post = $this->select(POSTS, 'topic_id=' . $topic_id, null, null, 'COUNT(*) AS nb_replies', 'one');
		return $this->update(TOPICS, array(
			'topic_poster'				=> $post['poster_id'],
			'topic_first_post_id'		=> $post['post_id'],
			'topic_last_post_id'		=> $last_post_id,
			'topic_time'				=> $post['post_time'],
			'topic_title'				=> $post['post_subject'],
			'topic_replies'				=> $stat_post['nb_replies']-1
		),
		'topic_id=' . $topic_id);
	}
	
	public function changeTopicId_post($new_topic_id, $in) {
		return $this->update(POSTS, array(
					'topic_id'	=> $new_topic_id
				),
				'post_id IN (' . $in . ')');
	}
	
	public function changeForumId_post($new_forum_id, $in) {
		return $this->update(POSTS, array(
					'forum_id'	=> $new_forum_id
				),
				'topic_id IN (' . $in . ')');
	}
	
	public function synchronizedTopicReply($topic_id) {
		$stat_post = $this->select(POSTS, 'topic_id=' . $topic_id, null, null, 'COUNT(*) AS nb_replies', 'one');
		return $this->update(TOPICS, array(
			'topic_replies'				=> $stat_post['nb_replies']-1
		),
		'topic_id=' . $topic_id);
	}
	
	public function searchKeyWords($start, $nb, $type_search, $key, $auth, $ret_row = false) {
		$str = $this->searchCombin($type_search, $key, $auth);
		$result = $this->select(POSTS . ' p,' . USERS . ' u,' . GROUPS . ' g' . ($type_search == "first" ? ', ' . TOPICS . ' t' : ''), $str . ' AND u.user_id=p.poster_id AND u.group_id=g.group_id', 'post_time DESC',  $start . ', ' . $nb);
		if ($ret_row) {
			return $this->rowData($result);
		}
		return $result;
	}
	public function countSearchKeyWords($type_search, $key, $auth) {
		$str = $this->searchCombin($type_search, $key, $auth);
		return $this->select(POSTS . ' p,' . USERS . ' u' . ($type_search == "first" ? ', ' . TOPICS . ' t' : ''), $str . ' AND u.user_id=p.poster_id', null,  null, 'COUNT(*) AS nb_post', 'one');
	}
	
	private function searchCombin($type_search, $key, $auth) {
		if ($type_search == "text")
			$str = 'post_text';
		elseif ($type_search == "title")
			$str = 'post_subject';
		else
			$str = 'post_text, post_subject';
		
		$keyword = 'MATCH (' . $str . ') AGAINST ("' . $key . '" IN BOOLEAN MODE)';
		$author = 'u.username="' . $auth . '"';
		$str = '';
		if ($key != '') {
			$str = $keyword;
			if ($auth != '') {	
				$str .= ' AND ';
			}
		}
		if ($auth != '') {
			$str .= $author;
		}
		if ($type_search == "first") {
			$str .= ' AND t.topic_first_post_id=p.post_id ';
			
		}
		return $str;
	}
	
	public function loadPm($user_id, $limit, $pm_id, $read = 0) {
		return $this->select(PM_TO . ' pt, ' . PM . ' pm, ' . USERS . ' u, ' . GROUPS . ' g', 'pt.pm_id=pm.pm_id AND pt.author_id=u.user_id AND u.group_id=g.group_id  AND pm_deleted=0' . ($pm_id != null ? ' AND pm.pm_id=' . $pm_id : ' AND pt.user_id=' . $user_id . ' AND pt.pm_read=' . $read), 'pm_time DESC', $limit);
	}
	
	public function loadPmSend($author_id, $limit, $pm_id) {
		return $this->select(PM_TO . ' pt, ' . PM . ' pm, ' . USERS . ' u, ' . GROUPS . ' g', 'pt.pm_id=pm.pm_id AND pt.user_id=u.user_id AND u.group_id=g.group_id AND pt.author_id=' . $author_id . ' AND pm_send_deleted=0' . ($pm_id != null ? ' AND pm.pm_id=' . $pm_id : ''), 'pm_time DESC', $limit);
		
	}
	
	public function pmReadAutorized($pm_id, $user_id, $action) {
		return $this->select(PM_TO,'pm_id="' . htmlspecialchars($pm_id) . '" AND ' . ($action == 'read_send' ? 'author_id' : 'user_id') . '="' . $user_id . '"', null, null, 'pm_id', 'one');
	}
	
	
	public function loadImageSet() {
		$result = $this->select(DESIGN_IMAGESET);
		while ($data = $result->fetch_array()) {
			$this->image_set[$data['image_name']] = array(
				'filename'  	=> PATH_IMG . $data['image_filename'],
				'width'  		=> $data['image_width'],
				'height'  		=> $data['image_height']
			);
		}	
	}
	
	public function addLog($operation, $data, $user, $type, $forum_id, $topic_id) {
		return $this->insert(LOGS, array(
			'log_type'			=> $type,
			'user_id'	 		=> $user->data['user_id'],
			'forum_id'			=> $forum_id,
			'topic_id'	 		=> $topic_id,
			'log_ip'	 		=> $user->data['user_ip'],
			'log_time'	 		=> time(),
			'log_operation'		=> $operation,
			'log_data'			=> $data
		));
	}
	
	public function totalChange($config_name) {
		return $this->update(CONFIG, array('config_value' => '=config_value+1'), 'config_name="' . $config_name . '"', true);
	}
	
	public function purgeTopicRead($days) {
		return $this->delete(TOPICS_READ, array('time' => '<' . (time() - 3600*24*$days)), true, true);
	}
	
	public function purgeTopicReadUser($user_id, $lastmark = null) {
		$where = array('user_id' => '=' . $user_id);
		if (isset($lastmark)) {
			$where['time'] = '<' . $lastmark;
		}
		return $this->delete(TOPICS_READ, $where, true, true);
	}
	
	public function deleteTopicRead($topic_id, $user_id = null) {
		$query = array('topic_id' => $topic_id);
		if (isset($user_id)) {
			 $query['user_id'] = $user_id;
		}
		return $this->delete(TOPICS_READ, $query);
	}
	
	/*public function updateLastPostForum($forum_id) {
		return $this->db->update(FORUMS, array(
			'forum_last_post_id'		=> $post_id,
			'forum_last_poster_id'		=> $poster_id,
			'forum_last_post_subject'	=> $post_subject,
			'forum_last_post_time'		=> $post_time,
			'forum_last_poster_name'	=> $poster_name	,
			'forum_last_poster_colour'	=> $poster_color
		));
	}*/
	
	
 public function dataTopics($limit = null, $order = null, $message = false) {
	$where = null;
	$select = TOPICS;
	if ($message) {
		$select .= ' t, ' . POSTS . ' p';
		$where = 't.topic_first_post_id=p.post_id';
	}
	$result = $this->select($select, $where, $order, $limit);
	return $this->rowData($result);
 }
 
 private function rowData($result) {
	$row = array();
	while ($data = $result->fetch_assoc()) {
		$row[] = $data;
	}
	return $row;
 }
	
	
	function __destruct() {
       $this->close();
    }
}
?>