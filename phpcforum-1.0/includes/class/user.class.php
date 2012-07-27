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
/**-------------------------------------------------
Classe User : Prend les caractéristiques de l'utilisateur + gestion des permissions

Date de finition : 
Dernière modification --

Créé par Samuel Ronce

-- Tous droits réservés --
//------------------------------------------------\\
					
----------------------------------------------------**/
class User {

	public $data;
	public $session_data;
	private $db;
	private $autologin;
	public $current_ip;
	public $session_id;
	public $in_admin_panel;
	private $browser;
	private $permission_forum = array();
	private $permission_userforum = array();
	private $option = array(
			'user_allow_contact_email'		=> 	 0x1,
			'user_allow_contact_pm'			=> 	 0x2,
			'user_mask_statut'				=> 	 0x4,
			'new_pm_avert'					=> 	 0x8,
			'new_pm_popup'					=> 	 0x10,
			'user_sig'						=> 	 0x20,
			'user_reply_avert'				=> 	 0x40,
			'display_img_post'				=> 	 0x80,
			'display_flash'					=> 	 0x100,
			'display_smilies_img'			=> 	 0x200,
			'display_sig'					=> 	 0x400,
			'display_sig'					=> 	 0x400,
			'display_avatar'				=> 	 0x800
			);

	function __construct($db, $session) {
		$this->db = $db;	
		$this->current_ip = $_SERVER['REMOTE_ADDR'];
		$this->session_id = session_id();
		$user_id = $this->autologin();
		if ($this->autologin) {
			$session['user_id'] = $user_id;
			$session['user_ip'] = $_SERVER['REMOTE_ADDR'];
		}
		$this->browser = $_SERVER['HTTP_USER_AGENT'];
		if (isset($session['user_ip']) && $this->current_ip != $session['user_ip']) {
			session_destroy();
		}
		$this->in_admin_panel  = isset($session['adm_user_ip']) && $this->current_ip == $session['adm_user_ip'];
		$this->updateIP($session);
		$this->iniUserData($session);
		$this->session_register();
		$this->assignPermission();
		$this->initPermissionGroupForum();
		$this->initPermissionUserForum();
	}
	
	public function autologin() {
		$session_id = $this->loadCookie('auth');
		$data = $this->db->select(SESSIONS, 'session_id="' . $session_id . '" AND session_autologin=1 AND session_real_user_id != 0', null, null, '*', 'one');
		if (isset($data['session_real_user_id'])) {
			$this->autologin = true;
			return $data['session_real_user_id'];
		}
		else {
			$this->autologin = false;
			return false;
		}
	}
	
	public function isRegister() {
		return $this->data['user_id'] != ANONYMOUS_ID;
	}
	
	public function updateData() {
		$session = array('user_id' => $this->data['user_id']);
		$this->iniUserData($session);
	}
	
	public function session_delete() {
		 $this->db->query('DELETE FROM ' . SESSIONS . ' 
						   WHERE session_id="' . $this->session_id . '" AND session_user_id=' . $this->data['user_id']);
		session_destroy();
	}
	
	public function dataBan() {
		$sql = 'SELECT ban_id, ban_expire, ban_date FROM ' . USERS_BAN . '
				WHERE ban_user_id=' . $this->data['user_id'] . ' OR ban_ip = "' . $this->current_ip . '"';
		$result = $this->db->query($sql);
		return $result->fetch_array();
	}
	
	private function updateIP($session) {
		if (isset($session['user_id']) && $session['user_id'] != ANONYMOUS_ID) {
			$sql = 'UPDATE ' . USERS . ' 
					SET user_ip="' . $this->current_ip. '"
					WHERE user_id="' . $session['user_id'] . '"';
			$this->db->query($sql);
		}
	}
	
	private function session_register() {
		$time = time();
		
		$this->db->query('DELETE FROM ' . SESSIONS . ' WHERE session_time <= "' . ($time-3600*24*5) . '"');
		
		$ban = $this->dataBan();
		if (empty($ban['ban_id']) || !$this->isRegister())  {
			$this->session_data  = $this->db->select(SESSIONS, 'session_ip="' . $this->current_ip . '"', null, null, '*', 'one');
			
		
		if ($this->session_data['session_autologin']) {
			$this->setCookie('auth', $this->session_id);
		}
		
		$real_user_id = null;
		if ($this->session_data['session_real_user_id'] == 0 && $this->isRegister()) {
			$real_user_id = $this->data['user_id'];
		}
		
		if (empty($this->session_data['session_id'])) {
				$sql =  array(
					'session_id'			=> $this->session_id,
					'session_user_id'		=> $this->data['user_id'],
					'session_start'			=> $time,
					'session_time'			=> $time,
					'session_ip'			=> $this->current_ip,
					'session_browser'		=> $this->browser,
					'session_page'			=> $_SERVER['REQUEST_URI'],
					'session_viewonline'	=> '1',
					'session_autologin'		=> $this->autologin ? 1 : 0
				);
				if (isset($real_user_id)) {
					$sql['session_real_user_id'] = $real_user_id;
				}
				$this->db->insert(SESSIONS, $sql);
			}
			else {
				$sql = array(
					'session_id'		=> $this->session_id,
					'session_time'		=> $time,
					'session_ip'		=> $this->current_ip,
					'session_browser'	=> $this->browser,
					'session_page'		=> $_SERVER['REQUEST_URI'],
					'session_user_id'	=> $this->data['user_id']
				);
				if (isset($real_user_id)) {
					$sql['session_real_user_id'] = $real_user_id;
				}
				$this->db->update(SESSIONS, $sql, 'session_ip="' . $this->session_data['session_ip'] . '"');
			}
			$this->session_data  = $this->db->select(SESSIONS, 'session_ip="' . $this->current_ip . '"', null, null, '*', 'one');
		}

		
	}
	
	public function updateViewOnline($value) {
		$sql = 'UPDATE ' . SESSIONS . ' 
				SET session_viewonline=' . $value . '
				WHERE session_id="' . $this->session_id . '"';
		return $this->db->query($sql);
	}
	
	public function updateAutoLogin($value) {
		return $this->db->update(SESSIONS, array('session_autologin' => $value), 'session_id="' . $this->session_id . '"');
	}
	
	private function iniUserData($session) {
		$result = $this->db->query('SELECT * FROM ' . USERS . ' u, ' . GROUPS . ' g
									WHERE u.group_id=g.group_id AND user_id=' . (isset($session['user_id']) ? $session['user_id'] : ANONYMOUS_ID));
		$this->data = $result->fetch_array();
		if (empty($this->data) && isset($session['user_id'])) {
			session_destroy();
			$this->iniUserData(null);
		}
		$this->data['user_options'] = hexdec($this->data['user_options']);
	}
	
	private function assignPermission() {
		$user_permission = 0x0;
		if ($this->data['user_permissions'] == '') {
			$result = $this->db->query('SELECT group_permissions FROM ' . USERS_GROUP . ' ug, ' . GROUPS . ' g
										WHERE g.group_id = ug.group_id AND user_status=1 AND user_id=' . $this->data['user_id']);
			while ($data = $result->fetch_array()) {
				$user_permission |= hexdec($data['group_permissions']);
				
			}
			
		}
		$this->data['user_permissions'] = $user_permission;
	}

	public function isCan($permission) {
		return ($permission & $this->data['user_permissions']) == $permission;
	}
	
	public function removePermission($permission) {
		$this->data['user_permissions'] -= $permission;
		$this->registerPermission();
	}
	
	public function addPermission($permission) {
		$this->data['user_permissions'] += $permission;
		$this->registerPermission();	
	}
	
	private function registerPermission() {
		$sql = 'UPDATE ' . USERS . ' SET user_permissions="' . dechex($this->data['user_permissions']) . '" 
				WHERE user_id=' . $this->data['user_id'];
		return $this->db->query($sql);
	}
	
	public function isCanForum($permission, $forum_id) {
		if (isset($this->permission_userforum[$forum_id])) {
			$forum_permission = $this->permission_userforum[$forum_id];
		}
		else {
			// $forum_permission = $this->dataPermissionGroupForum($forum_id);
			$forum_permission = $this->permission_forum[$forum_id];
			
		}
		return ($permission & $forum_permission) == $permission;
	}
	
	private function initPermissionUserForum() {
		$result = $this->db->query('SELECT * FROM ' . USERS_PERMISSION . ' 
									WHERE user_id=' . $this->data['user_id']);
		while ($data = $result->fetch_assoc()) {
			$this->permission_userforum[$data['forum_id']] = hexdec($data['user_permission']);
		}	
	}
	
	private function dataPermissionForum($forum_id) {
		
		$result = $this->db->query('SELECT user_permission FROM ' . USERS_PERMISSION . ' 
									WHERE user_id=' . $this->data['user_id'] . ' AND forum_id="' . $forum_id . '"');
		$data = $result->fetch_array();
		$data['user_permission'] = hexdec($data['user_permission']);
		return $data['user_permission'];
		
	}
	
	private function initPermissionGroupForum() {
		if ($this->data['user_id'] == ANONYMOUS_ID) {
			$result = $this->db->select(GROUPS_PERMISSION,  'group_id=' . GROUP_VISITOR_ID, null, null, '*');
			
		}
		else {
			$result = $this->db->query('SELECT * FROM ' . GROUPS_PERMISSION . ' gp, ' . USERS_GROUP . ' ug
									WHERE gp.group_id = ug.group_id AND user_status=1 AND ug.user_id=' . $this->data['user_id']);
		}
		if ($user->data['group_id'] == GROUP_MEMBER_ID) {
		
		
		}
		while ($data = $result->fetch_assoc()) {
			if (isset($this->permission_forum[$data['forum_id']])) {
				$this->permission_forum[$data['forum_id']] |= hexdec($data['group_permission']);
			}
			else {
				$this->permission_forum[$data['forum_id']] =  hexdec($data['group_permission']);
			}
			
		}
	}
	
	/*private function dataPermissionGroupForum($forum_id) {
		$group_permission = 0x0;
		if ($this->data['user_id'] == ANONYMOUS_ID) {
			$result = $this->db->select(GROUPS_PERMISSION,  'group_id=' . GROUP_VISITOR_ID . ' AND forum_id=' . $forum_id, null, null, 'group_permission');
		}
		else {
			$result = $this->db->query('SELECT group_permission FROM ' . GROUPS_PERMISSION . ' gp, ' . USERS_GROUP . ' ug
									WHERE gp.group_id = ug.group_id AND user_status=1 AND ug.user_id=' . $this->data['user_id'] . ' AND forum_id="' . $forum_id . '"');
		}
		while ($data = $result->fetch_array()) {
			$group_permission |= hexdec($data['group_permission']);
		}
		return $group_permission;
	}*/
	
	
	public function removePermissionForum($permission, $forum_id) {
		$data = $this->dataPermissionForum($forum_id);
		$permission -= $data;
		$this->registerPermissionForum($permission, $forum_id);
	}
	
	public function addPermissionForum($permission, $forum_id) {
		$data = $this->dataPermissionForum($forum_id);
		$permission  += $data;
		$this->registerPermissionForum($permission, $forum_id);
	}
	
	private function registerPermissionForum($permission, $forum_id) {
		$sql = 'UPDATE ' . USERS_PERMISSION . ' SET user_permission="' . dechex($permission) . '" 
				WHERE user_id=' . $this->data['user_id'];
		return $this->db->query($sql);
	}
	
	public function profileOptions($label_option) {
		return ($this->option[$label_option] & $this->data['user_options']) == $this->option[$label_option];
	}
	
	public function removeProfileOptions($label_option) {
		$this->data['user_options'] -= $this->option[$label_option];
	}
	
	public function addProfileOptions($label_option) {
		$this->data['user_options'] += $this->option[$label_option];	
	}
	
	public function registerProfileOptions() {
		$sql = 'UPDATE ' . USERS . ' SET user_options="' . dechex($this->data['user_options']) . '" 
				WHERE user_id=' . $this->data['user_id'];
		return $this->db->query($sql);
	}
	
	public function getOption($label_option) {
		return $this->option[$label_option];
	}
	
	public function incrementeMsg() {
		$result = $this->db->query('SELECT user_nb_message  FROM ' . USERS . ' 
							  WHERE user_id=' . $this->data['user_id']);
		$data = $result->fetch_array();
		return $this->db->query('UPDATE ' . USERS . ' SET user_nb_message=' . ($data['user_nb_message']+1) . ' 
						   WHERE user_id=' . $this->data['user_id']);
	}
	
	public function isAdmin($permission) {
		return $this->isCan($permission['users']['admin_permission']) 		|| 
			   $this->isCan($permission['users']['admin_style']) 			||
			   $this->isCan($permission['users']['admin_group'])			||
			   $this->isCan($permission['users']['admin_delete_user'])		||
			   $this->isCan($permission['users']['admin_add_forum'])		||
			   $this->isCan($permission['users']['admin_del_forum'])		||
			   $this->isCan($permission['users']['admin_edit_forum']);
	}
	
	public function setCookie($key, $value, $name = 'phpcforum', $path = ROOT) {
		setcookie($name . '[' . $key . ']', $value, time()+3600*24*30, $path);
	}
	
	public function loadCookie($key, $name = 'phpcforum') {
		if (isset($_COOKIE[$name])) {
			return $_COOKIE[$name][$key];
		}
		else
			return null;
	}
	
	public function removeCookie($name = 'phpcforum') {
		setcookie($name, '', 1); 
	}
	
	public function setValueSession($name, $value) {
		$_SESSION[$name] = $value;
	}
	
	public function getValueSession($name) {
		return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
	}
	
	public function addValueSession($name, $value, $key = null) {
		if (isset($key)) {
			$_SESSION[$name][$key] = $value;
		}
		else {
			$_SESSION[$name][] = $value;
		}
		
	}
	
	public function currentPage() {
			$current_page = $_SERVER['REQUEST_URI'];
			return $current_page;
	}
	
	public function pageIsAdm() {
		$page = $this->currentPage();
		if (preg_match('#^(' . ROOT . '/adm)#', $page)) 
			return true;
		else
			return false;
	}
	
}
?>