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
/**
 * Légende : 
 * 	[W] : Droit de modification
 * 
 * Flag :
 * 
 *  - header
 *  Appel en entête du script
 *  
 *  - replyPost ($subject, $text, $topic_id, $forum_id)
 *  Appel lorsque l'utilisateur répond à un message
 *  @param $subject [W] Le sujet du message
 *  @param $text [W] Le texte du message
 *  @param $topic_id L'identifiant du sujet
 *  @param $forum_id L'identifiant du forum
 *  
 *  - login ($data)
 *  Appel lorsque l'utilisateur se logue (Mise à jour du compte pas encore effectué)
 *  @param $data Tableau de données de l'utilisateur
 *  
 *  - sendPassword ($new_password)
 *  Appel lors de l'envoie du nouveau mot de passe
 *  @param $new_password [W] Mot de passe en clair
 *  
 *  - register ($username, $password, $email, $activ)
 *  Appel lorsque un visiteur s'inscrit et envoie le formulaire
 *  @param $username [W] Son pseudo
 *  @param $password [W] Son mot de passe
 *  @param $email Son adresse email
 *  @param $activ [W] Type d'activation
 *  
 *  - registerSendEmail ($code)
 *  Appel si le type d'activation vaut 1 et avant l'envoie de l'email d'activation de compte
 *  @param $code Code d'activation
 *  
 *  - search ($keywords, $start, $type_search, $author)
 *  Appel après la soumission de la recherche
 *  @param $keywords [W] Mots clés
 *  @param $start [W] La page courante (=identifiant de la page * nombre de messages par page)
 *  @param $type_search [W] Le type de la recherche
 *  @param $author [W] Recherche de l'auteur indiqué
 *  
 *  - viewtopics($start)
 *  Appel lors de la lecture de la liste d'un sujet (forum existant et permission autorisée)
 *  @param $start [W] La page courante (=identifiant de la page * nombre de messages par page)
 *  
 *   - viewposts($start)
 *  Appel lors de la lecture d'un sujet (sujet existant, forum existant et permission autorisée)
 *  @param $start [W] La page courante (=identifiant de la page * nombre de messages par page)
 *
 *	- ajax ($data)
 *	Appel lors d'une requête Ajax de la façon suivante (en Jquery) : $.post('ajax/plugin.php', {data : ... }, function(data) { ... }, "json");
 *	@param $data Les données envoyées via la requête Ajax
 * 
 * @author Samarium
 *
 */
class Plugins extends GlobalPlugins {
	
	protected $template;

	public static $action = array();
	public static $plugins = array();

	public function __construct($template, $plugin_name) {
		parent::__construct($plugin_name);
		$this->template = $template;
		$this->initAction();
	}
	
	
	public function display($var) {
		if (method_exists($this, $var)) {
			$this->$var();
		}
	}
	
	protected function database($class_name) {
		$this->p_db = new $class_name($this->db);
	}
	
	
	protected function addAction($flag, $class, $method_call, $params = array()) {
		Plugins::$action[$flag][] = array(
			'class' 		=> $class,
			'method_call' 	=> $method_call,
			'params'		=> $params
		);
	}
	
	public static function flag($flag_name, $arg_list = array()) {
	/*	$arg_list = func_get_args();
		array_shift($arg_list);*/
		if (empty($arg_list)) {
			$arg_list = $action['params'];
		}
		$do_action = Plugins::$action[$flag_name];
		if (isset($do_action))
			foreach ($do_action as $key => $action) {
				call_user_func_array(array($action['class'], $action['method_call']), $arg_list);	
			}

	}
	
	protected function initAction() {
	}
	
	public function displayPlugin($plugin, $display_var) {
		if (isset(Plugins::$plugins[$plugin])) {
			return Plugins::$plugins[$plugin]->display($display_var);
		}
		else
			return '';
	}
	
}
?>