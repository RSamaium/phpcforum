<?php
/*

  Phpcforum API PHP Library
  Version: 1.0
  See the README file for info on how to use this library.

*/
define('PHPCFORUM_LIBRARY_NAME',  "phpcforum-api");
define('PHPCFORUM_LIBRARY_VERSION',  "1.0");

class Phpcforum {

	private $url;
	private $api_key;
	
	private $options = array(
		CURLOPT_RETURNTRANSFER => 1, 
		CURLOPT_HEADER => 0, 
		CURLOPT_CONNECTTIMEOUT => 0, 	
		CURLOPT_SSL_VERIFYPEER => 0, 
		CURLOPT_SSL_VERIFYHOST => 0
	);

	private $connected;
	private $results;
	private $status_code;
	private $error;
  
  function __construct($root_forum, $api_key) {
	session_start();
	$this->url = $root_forum . '/api.php?sid=' . session_id();
	$this->api_key = $api_key;
	session_write_close(); 
  }
   
  /***
  
	READ DATA
  
  */
  public function users() {
		$data = array('action' => 'users');
		return $this->curl($data);
  }
  
   public function userByName($username) {
		$data = array(
			'action' 	=> 'userByName',
			'params'	=> array(
				'username' => $username
			)
		);
		return $this->curl($data);
  }
  
   public function topics($order = null, $limit = null, $display_message = false) {
		$data = array(
			'action' 	=> 'topics',
			'params'	=> array(
				'limit' => 'topic_time ' . $limit,
				'order'	=> (isset($order) ? '0, ' . $order : null),
				'message'	=> $display_message
			
			)
		);
		return $this->curl($data);
  }
  
  public function topic($topic_id) {
		$data = array(
			'action' 	=> 'topic',
			'params'	=> array(
				'topic_id' => $topic_id
			)
		);
		return $this->curl($data);
  }
  
  public function post($post_id) {
		$data = array(
			'action' 	=> 'post',
			'params'	=> array(
				'post_id' => $post_id
			)
		);
		return $this->curl($data);
  }
  
   public function search($start, $keywords, $author = '', $type = 'all') {
		$data = array(
			'action' 	=> 'search',
			'params'	=> array(
				'start' 		=> $start,
				'keywords' 		=> $keywords,
				'author' 		=> $author,
				'type' 			=> $type
			)
		);
		return $this->curl($data);
  }
  
   public function userIsRegister() {
		$data = array(
			'action' 	=> 'userIsRegister'
		);
		$ret = $this->curl($data);
		return (preg_match('#^true#', $ret) ? true : false);
  }
  
    public function usersOnline() {
		$data = array(
			'action' 	=> 'usersOnline'
		);
		return $this->curl($data);
	}
  
  /***
  
	ACTION
  
  */
   public function newUser($username, $email, $password) {
		$data = array(
			'action' 			=> 'newUser',
			'username'			=> $username,
			'email'				=> $email,
			'confirm_email'		=> $email,
			'password'			=> $password,
			'confirm_password'	=> $password,
			'captc'				=> ''
			
		);
		return $this->curl($data);
   }
 
   public function postNewTopic($forum_id, $subject, $text) {
		$data = array(
			'action' 	=> 'postNewTopic',
			'params'	=> array(
				'forum_id' 		=> 	$forum_id,
				'post_subject'	=>	$subject,
				'post_text'		=> 	$text
			)
		);
		return $this->curl($data);
   }
 
	public function replyTopic($forum_id, $topic_id, $subject, $text) {
		$data = array(
			'action' 	=> 'replyTopic',
			'params'	=> array(
				'forum_id' 		=> 	$forum_id,
				'topic_id'		=>  $topic_id,
				'post_subject'	=>	$subject,
				'post_text'		=> 	$text
			)
		);
		return $this->curl($data);
   }
   
   public function postNewPm($pm_text, $pm_subject, $pm_user) {
		$data = array(
			'action' 			=> 'postNewPm',
			'pm_text'			=> $pm_text,
			'pm_subject'		=> $pm_subject,
			'pm_user'			=> $pm_user
			
		);
		return $this->curl($data);
   }
  
   public function login($username, $password, $mask_online = false, $autologin = false) {
		$data = array(
			'action' 			=> 'login',
			'username'			=> $username,
			'password'			=> $password,
			'user_viewonline' 	=> $mask_online,
			'user_autologin'	=> $autologin,
			'submit_login'		=> 1
			
		);
		return $this->curl($data);
  }
  
  public function forgetPassword($email) {
		$data = array(
			'action' 			=> 'forgetPassword',
			'forget_email'		=> $email
		);
		return $this->curl($data);
  }
 
  
   public function logout() {
		$data = array(
			'action' 	=> 'logout'
		);
		return $this->curl($data);
   }
  

  private function curl($data, $options = array()) {
		$this->options[CURLOPT_HTTPHEADER][] = "phpcforum-Library-Name: " . PHPCFORUM_LIBRARY_NAME;
		$this->options[CURLOPT_HTTPHEADER][] = "phpcforum-Library-Version: " .PHPCFORUM_LIBRARY_VERSION;
		$this->options[CURLOPT_POST] = 1;
		$data['api_key'] = $this->api_key;
		$this->options[CURLOPT_POSTFIELDS] = http_build_query($data);
		$this->options[CURLOPT_URL] = $this->url;
		
		foreach($options as $option_key => $option) {
			$this->options[$option_key] = $option;
		}

		$ch = curl_init();
		curl_setopt_array($ch, $this->options);
		$this->results = curl_exec($ch);
		$this->status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return json_decode($this->results);
		
	}
  
}
