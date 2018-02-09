<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_pthranking
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * HelloWorld Model
 *
 * @since  0.0.1
 */
class PthRankingModelWebservice extends JModelItem
{
	/**
	 * @var string message
	 */
	protected $message;

    protected $currentid=0; // these should always be in a pair
    protected $currentname=""; // these should always be in a pair

    protected $mydatabase=""; // has database object, as a cache
	
	public function getFlagTooltip($country_iso){
		$falgTooltip = '<span class="hasTip" title="'.array_search($country_iso, PthRankingDefines::$country_iso).'" >'
				.'<img src="/media/flags_iso/'.$country_iso.'.png" border="0" alt=""/>'
				. '</span>';
		return $falgTooltip;
	}
	
	public function getGenderIcon($gender){
		$g = array("m" => "male", "f" => "female");
		$genderIcon = '<span class="hasTip" title="'.$g[$gender].'" >'
				.'<img src="/media/flags_iso/'.$g[$gender].'.png" border="0" alt=""/>'
				. '</span>';
		return $genderIcon;
	}
	
     // get the other database
     private function mydb()
     {
        if(!($this->mydatabase === "")) {
            return ($this->mydatabase); // caching database
        }
        // no database found, connect to one
        $option = array(); //prevent problems
        $option['driver']   = RDB_DRIVER;
        $option['host']     = RDB_HOST;
        $option['user']     = RDB_USER;
        $option['password'] = RDB_PASS;
        $option['database'] = RDB_DB;
        $option['prefix']   = RDB_PREF;
        $db = JDatabaseDriver::getInstance( $option );
        $this->mydatabase = $db;
        return($db);
     }
	 
	 public function getDailyChampions(){
		$return = false;
		$db = $this->mydb();
	 }
	 
	 public function getStoreUserdata(){
		$return = false;
		$response = "unspecified";
		$status = "nok";
				
		$jinput = JFactory::getApplication()->input;
		
		// re-captcha
		$post = JRequest::get('post');
		JPluginHelper::importPlugin('captcha');
		$dispatcher = JDispatcher::getInstance();
		$res = $dispatcher->trigger('onCheckAnswer',$post['g-recaptcha-response']);
		if(!is_array($res) || count($res) == 0 || !$res[0]){
			return json_encode(array("status" => "nok", "response" => "re-captcha response wrong"));
			//die('Invalid Captcha');
		}
		
		$submit = $jinput->post->get('submit', false, 'BOOL');
		
		if(!$submit){
			return json_encode(array("status" => "nok", "response" => "submit not set or false"));
		}
		
		$email = $jinput->post->get('email', "", 'STRING');
		$username = $jinput->post->get('username', "", 'STRING');
		$password = base64_decode($jinput->post->get('password', "", 'BASE64'));
		$gender = $jinput->post->get('gender', "", 'WORD');
		$country = $jinput->post->get('country', "", 'WORD');
		$act_key = md5(microtime()); // microtime() instead of time() - just to be safe
		$fp = $jinput->post->get('fp', "", 'STRING');
		// @TODO: make some checks?
		

		// @XXX: store data into db
		$db = $this->mydb();
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Insert columns.
		$columns = array('username', 'password', 'email', 'created', 'country_iso', 'gender', 'act_key', 'fp');
		 
		// Insert values.
		$values = array(
			$db->quote($username),
			"AES_ENCRYPT('".mysql_real_escape_string($password)."', '".RDB_SALT."')", // $db->quote destroys the AES_ENCRYPT function - so it's oldschool mysql_real_escape_string ;)
			$db->quote($email),
			$db->quote(date("Y-m-d H:i:s")),
			$db->quote($country),
			$db->quote($gender),
			$db->quote($act_key), // maybe a shorter activation key for email validation?
			$db->quote($fp),
		);
		
		$query
			->insert($db->quoteName('#__player'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
		 
		// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);
		$res = $db->execute();
		
		if($res){
			
			// @XXX: fp exceptions - no email sending
			if(in_array($fp, PthRankingDefines::$fpbl) || $fp == ""){
				$status = "ok";
				$response = "mail sent";
				return json_encode(array("status" => $status, "response" => $response));
			}
			
			// @XXX: send an email with the activation key & link for activation page - partly taken from components/com_users/models/registration.php
			$config = JFactory::getConfig();
	
			$data['name'] = $username;
			$data['fromname'] = $config->get('fromname');
			//$data['mailfrom'] = $config->get('mailfrom');
			$data['mailfrom'] = "account@pokerth.net";
			$data['sitename'] = $config->get('sitename');
			$data['siteurl'] = JUri::root();
			$uri = JUri::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base . JRoute::_('index.php?option=com_pthranking&view=emailval&actkey=' . $act_key, false);
			// Remove administrator/ from activate url in case this method is called from admin
			// @XXX: propably not needed - but just to be safe
			if (JFactory::getApplication()->isAdmin())
			{
				$adminPos         = strrpos($data['activate'], 'administrator/');
				$data['activate'] = substr_replace($data['activate'], '', $adminPos, 14);
			}
			$emailSubject = JText::sprintf(
				"Game-Account Details for %s at %s",
				$data['name'],
				$data['sitename']
			);
			// @XXX: text for activation email
			$emailBody = JText::sprintf(
						"<h3>Hello %s,</h3>\n\n<p>Thank you for registering for the game at %s.</p>\n\n<p>Your game-account is created and must be activated before you can use it.</p>\n\n<p>To activate the game-account select the following link or copy-paste it in your browser:\n\n<br /><br /><a href='%s'>%s</a></p>\n\n<p>You can also enter your activation-code manually on the site shown after registration:<br /><br />\n\n%s</p>\n\n<p>After activation you may login to the <a href='%s'>Forum</a> and to the Game using the following username and the password you entered during registration:\n\n<br /><br />Username: %s<br />\n\nPassword: %s</p><br /><br />\n\nKind regards,<br />\n\nYour PokerTH Team",
						$data['name'],
						$data['sitename'],
						$data['activate'],
						$data['activate'],
						$act_key,
						$data['siteurl'],
						$data['name'],
						$password
					);
			
			// Send the registration email.
			$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $email, $emailSubject, $emailBody, $html=true, null, $bcc=$config->get('mailfrom')); // @FIXME: for the start send bcc copies to webmaster - disable it later!?
	
			if ( $return !== true ) {
				$status = "nok";
				$response = "mail not sent";
				//mDebug('Error sending email: ' . $return->__toString());
			} else {
				$status = "ok";
				$response = "mail sent";
				//mDebug('Mail sent');
			}
		}else{
			$status = "nok";
			$response = "data not stored in db - mail will not be sent";
		}

		return json_encode(array("status" => $status, "response" => $response));
	 }


	public function getCheckUsername()
	{
        $return = false;
        
        $jinput = JFactory::getApplication()->input;
        $username = $jinput->get('pthusername', "", 'STRING');
		
		if($username == "") return json_encode(array("status" => "nok", "reason" => "username empty"));
		if(strpos($username, "deleted_") !== false){ $return = true; }
		
		if(!$return){
			$db = $this->mydb();
			$query = $db->getQuery(true);
			$query->select('player_id,username');
			$query->from('#__player');
			$query->where('LOWER(username) LIKE '.$db->quote(strtolower($username), false) );
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			if(is_array($rows) && count($rows) > 0){
				$return = true;
			}
		}
		// @XXX: suspended usernames
		if(!$return){
			$query = $db->getQuery(true);
			$query->select('username');
			$query->from('#__suspended_usernames');
			$query->where('LOWER(username) LIKE '.$db->quote(strtolower($username), false) );
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if(is_array($rows) && count($rows) > 0){
				$return = true;
			}
		}
		
		// next check if nick with forum account exists
		if(!$return){
			$db2    = JFactory::getDBO();
			$query = $db2->getQuery(true);
			$query->select('id,username,email,lastvisitDate');
			$query->from('#__users');
			$query->where('LOWER(username) LIKE '.$db2->quote(strtolower($username), false) );
			$query->where($db2->quoteName('lastvisitDate') . " > '". date("Y-m-d H:i:s", strtotime('-12 month', time())) . "'");
			$db2->setQuery($query);
			$rows = $db2->loadObjectList();
			if(is_array($rows) && count($rows) > 0){
				$return = true;
			}
		}
		// @XXX: include chatcleaner config in order to filter not allowed words
		if(!$return && file_exists(RPT_CCCFG)){
			$reason = "";
			preg_match_all("|BadWords value=\"(.*)\"|U",
				file_get_contents(RPT_CCCFG),
				$matches, PREG_PATTERN_ORDER);
			if(is_array($matches) && count($matches) > 1){
				foreach($matches[1] as $match){
					// check if username contains a bad word
					$bad_word = strtolower(trim(preg_replace('/\s+/', '', $match)));
					if(strlen($bad_word) < 4) continue;
					if(strpos(strtolower(preg_replace('/\s+/', '', $username)), $bad_word)  !== false){
						// bad word exists
						$reason = "bad word: " . $bad_word;
						$return = true;
						break;
					}
				}
			}
			// exception for wkD
			if(!$return &&
			   (
			   strpos(strtolower(preg_replace('/\s+/', '', $username)), "sp0ck")  !== false ||
			   strpos(strtolower(preg_replace('/\s+/', '', $username)), "spock")  !== false ||
			   strpos(strtolower(preg_replace('/\s+/', '', $username)), "janedeau")  !== false
			   )
			){
				// bad word exists
				$reason = "bad word!";
				$return = true;
			}
			
			
			return json_encode(array("status" => "ok", "response" => $return, "reason" => $reason));
		}
	
		return json_encode(array("status" => "ok", "response" => $return));
	}

	public function getCheckEmail()
	{
        $return = false;
        
        $jinput = JFactory::getApplication()->input;
        $email = $jinput->get('pthemail', "", 'STRING');
		
		if($email == "") return json_encode(array("status" => "nok", "reason" => "email empty"));
		
        // exception for wkD
		preg_match('/^.*(@w[0-9]+).*$/i',
			$email, $match);
		if(is_array($match) && count($match) > 1){
			return json_encode(array("status" => "nok", "response" => true, "reason" => "email already used"));
		}
		
        $db = $this->mydb();
        
        $query = $db->getQuery(true);
        $query->select('player_id,username');
        $query->from('#__player');
        $query->where($db->quoteName('email') . " = ".$db->quote($email) );
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			$return = true;
		}
		
		// next check if email with forum account exists
		if(!$return){
			$db2    = JFactory::getDBO();
			$query = $db2->getQuery(true);
			$query->select('id,username,email,lastvisitDate');
			$query->from('#__users');
            $query->where($db2->quoteName('email') . " = ".$db2->quote($email) );
			$query->where($db2->quoteName('lastvisitDate') . " > '". date("Y-m-d H:i:s", strtotime('-12 month', time())) . "'");
			$db2->setQuery($query);
			$rows = $db2->loadObjectList();
			if(is_array($rows) && count($rows) > 0){
				$return = true;
			}
		}
		
		return json_encode(array("status" => "ok", "response" => $return));
	}
	
	public function getCheckPassword(){
		$return = false;
		jimport('joomla.user.helper');
		$user = JFactory::getUser();
		if($user->guest){
			// user is not logged in
			return json_encode(array("status" => "ok", "response" => $return));
		}
		$jinput = JFactory::getApplication()->input;
		$password = base64_decode($jinput->get('pthpassword', "", 'BASE64'));
		if($password == ""){
			// password empty
			return json_encode(array("status" => "ok", "response" => $return));
		}
		$return = JUserHelper::verifyPassword( $password , $user->password, $user->id );
		return json_encode(array("status" => "ok", "response" => $return));
	}
	
	public function getAutocompleteUser(){
		$return = false;
		$jinput = JFactory::getApplication()->input;
		$username = $jinput->get('username', "", 'STRING');
		$a_json = array();
		if($username == ""){
			return json_encode($a_json);
		}
		$db = $this->mydb();
		$username = '%' . $db->escape( $username, true ) . '%';
        $query = $db->getQuery(true);
        $query->select('username');
        $query->from('#__player');
        $query->where('LOWER(username) LIKE '.$db->quote(strtolower($username), false) );
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			foreach($rows as $row){
				$a_json[] = array("value" => $row->username);
			}
		}
		return json_encode($a_json);
	}
	
	public function getDoForumAccountTransfer(){
		$return = false;
		$response = "unspecified";
		$status = "nok";
		jimport('joomla.user.helper');
		$user = JFactory::getUser();
		if($user->guest){
			// user is not logged in
			$status = "nok";
			$response = "not logged in";
			return json_encode(array("status" => $status, "response" => $response));
		}
		$jinput = JFactory::getApplication()->input;
		$password = base64_decode($jinput->post->get('password', "", 'BASE64'));
		$gender = $jinput->post->get('gender', "", 'STRING');
		$country = $jinput->post->get('country', "", 'STRING');
		if($password == ""){
			// password empty
			$status = "nok";
			$response = "password empty";
			return json_encode(array("status" => $status, "response" => $response));
		}
		$db = $this->mydb();
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Insert columns.
		$columns = array('username', 'password', 'email', 'created', 'country_iso', 'gender', 'active');
		 
		// Insert values.
		$values = array(
			$db->quote($user->username),
			"AES_ENCRYPT('".mysql_real_escape_string($password)."', '".RDB_SALT."')", // $db->quote destroys the AES_ENCRYPT function - so it's oldschool mysql_real_escape_string ;)
			$db->quote($user->email),
			$db->quote(date("Y-m-d H:i:s")),
			$db->quote($country),
			$db->quote($gender),
			1
		);
		
		// Prepare the insert query.
		$query
			->insert($db->quoteName('#__player'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
		 
		// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);
		$res = $db->execute();
		if($res){
			$status = "ok";
			$response = "forum account transfered to ranking player table";
			
			$player_id = $db->insertid(); // fetch last insert id
			// Create a new query object.
			$query = $db->getQuery(true);
			
			// Insert columns.
			$columns = array(
				'player_id',
				'username',
			);
			 
			// Insert values.
			$values = array(
				$db->quote($player_id),
				$db->quote($user->username),
			);
			
			// Prepare the insert query.
			$query
				->insert($db->quoteName('#__player_ranking'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			 
			// Set the query using our newly populated query object and execute it.
			$db->setQuery($query);
			$res = $db->execute();
			
			
		}else{
			$status = "nok";
			$response = "forum account NOT transfered to ranking player table";
		}
		return json_encode(array("status" => $status, "response" => $response));
	}

	public function getRankingTable()
	{
        //
        $jinput = JFactory::getApplication()->input;
        $start= $jinput->get('start',0,'INT');
        $size= $jinput->get('size',0,'INT');
		$username = $jinput->get('username',"",'STRING');
		
		$num_total = 0;
		$max_page = 0;
		$page = 1;


        $start=(int)$start;
        $size=(int)$size;
        if($size<=0) $size=50;
        if($start<=0) $start=1;

        $searchplayer=$jinput->get('searchplayer',0,'INT');
        if($searchplayer==1)
        {
          $basicinfo=json_decode($this->getBasicInfo(),true);
          if(count($basicinfo)==0)
          {
            return(json_encode(array()));
          }
          $rank=(int)($basicinfo["rank"]);
          $start=$rank-(($rank-1)%$size); // adjust to start of page
        }

        $db=$this->mydb();
        
		// get num rows for pagination calc => e.g. page 1 of $max_page - will be added later
        $query = $db->getQuery(true);
        $query->select('COUNT(player_id) as num');
        $query->from('#__player_ranking');
		$query->where("username NOT LIKE 'deleted_%'");
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			$num_total = $rows[0]->num;
			$max_page = ceil($num_total / $size);
			$page = ceil($start / $size);
		}
		
        $return=false;
        $start2=$start-1;
        // start query
        $query = $db->getQuery(true);
        $query->select('pr.*, p.country_iso, p.gender');
        $query->from('#__player_ranking AS pr');
		$query->join('LEFT', '#__player AS p ON p.player_id = pr.player_id');
        $query->where("pr.username NOT LIKE 'deleted_%'");
        $query->order('final_score DESC, season_games DESC, player_id ASC');
        $query->setLimit($size,$start2);
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			$return = true;
		}
        if(!$return) return(json_encode(array()));

        $table=array();

        $rank=$start;
        foreach($rows as $row) {
            $tableentry=array(); // associative array, maybe dict/object?
			if(strtolower($row->username) == strtolower($username)){
				$tableentry["username"] = "<span class='text-danger'>".$row->username."</span>";
			}else{
				$tableentry["username"]=$row->username;
			}
            $final_score=sprintf("%.2f %%",max(0.0,($row->final_score)/10000.0));
            $tableentry["final_score"]=$final_score;
//             $average_score=sprintf("%.2f %%",max(0.0,($row->average_score)/10000.0));
//             $tableentry["average_score"]=$average_score;
            $average_points=sprintf("%.2f",max(0.0,($row->average_score)*6.2/(25.0*1000000.0)));
            $tableentry["average_points"]=$average_points;
            $tableentry["userid"]=$row->player_id;
            $tableentry["season_games"]=$row->season_games;
            $tableentry["rank"]=$rank;
			$tableentry["gender"] = ($row->gender != "") ? $this->getGenderIcon($row->gender) : '';
			$tableentry["country"] = ($row->country_iso != "") ? $this->getFlagTooltip($row->country_iso) : '';
            $table[]=$tableentry;
            $rank+=1;
        }
		$return = array("pagination" => array("page" => $page, "max_page" => $max_page, "size" => $size, "start" => $start), "table" => $table);
       return json_encode($return);
	}

    private function set_user_id_pair() // reads from jinput
    {
        $jinput = JFactory::getApplication()->input;
        $inputid= $jinput->get('userid',0,'INT');
        $inputname= $jinput->get('username',"",'STRING');
        if($inputid==$this->currentid && $inputid>0) return; // input is up to date
        if($inputname==$this->currentname && $inputname!="") return; // input is up to date

        if($inputid<=0 && $inputname=="")
        {
          $this->currentid=0;
          $this->currentname=""; // set invalid
          return;
        }
        $db=$this->mydb();
        if($inputid>0) // && $inputname=="")
        {
          // check if id is valid
          // assign new id and name
          $query = $db->getQuery(true);
          $query->select('username');
          $query->from('#__player_ranking');
          $query->where($db->quoteName('player_id') ." = $inputid");
          $db->setQuery($query);
          $rows = $db->loadObjectList();
          if(is_array($rows) && count($rows)==1)
          {
            // ID is correct
            // id has priority over name
            $this->currentname=$rows[0]->username;
            $this->currentid=$inputid;
            return;
          }
          // else: id not valid, check if name is valid
        }
        if($inputname!="")
        {
          $query = $db->getQuery(true);
          $query->select('player_id');
          $query->from('#__player');
          $query->where($db->quoteName('username')." = ".$db->quote($inputname));
          $db->setQuery($query);
          $rows = $db->loadObjectList();
          if(is_array($rows) && count($rows)==1)
          {
            // name is correct
            $this->currentname=$inputname;
            $this->currentid=$rows[0]->player_id;
            return;
          }
        }
        // failed to find anything
        $this->currentid=0;
        $this->currentname="";
        return;
    }


    public function PieData($seasononly=FALSE) // not to be used for get["..."] directly
    {
//         $seasononly=TRUE; // False means all-time - TODO: make as an parameter

        $db=$this->mydb();

        $jinput = JFactory::getApplication()->input;
        if($seasononly)
        {
            $nowinput= $jinput->get('now','','STRING');
            if($nowinput=="")
            {
              $query = $db->getQuery(true);
//               $query->select(array('MAX(start_time)'),array('res')); // AS res
              $query->select('MAX(start_time) AS res'); // the joomla database api sucks
              $query->from('#__game');
              $db->setQuery($query);
              $rows = $db->loadObjectList();
              if(is_array($rows) && count($rows)==1)
              {
                $now=$rows[0]->res;
//                 var_dump($rows);
              }
              else $now="1970-01-01 00:00:00";
            }
            else
            {
              $unixtime=strtotime($nowinput);
              if($unixtime==FALSE) $now="1970-01-01 00:00:00";
              else $now=date("Y-m-d H:i:s",$unixtime);
            }
        }

        $this->set_user_id_pair();

        $query = $db->getQuery(true);
       
//         $query->select(array('place','COUNT(*)'),array(null,'counter'));
        $query->select('place , COUNT(*) AS counter');
        $query->from('#__game_has_player');
        if($seasononly)
        {
            $query->where($db->quoteName('player_idplayer')." = ".$this->currentid,'AND');
            $query->where($db->quoteName('end_time')." >= start_of_this_season('$now')");
        }
        else
        {
            $query->where($db->quoteName('player_idplayer')." = ".$this->currentid,'AND');
        }
        $query->group('place');
        $db->setQuery($query); // TODO: appropriate INDEX for this query
        $rows = $db->loadObjectList();

        $place_count=array(0,0,0,0,0,0,0,0,0,0,0);
        if(is_array($rows) && count($rows)>=1)
        {
          foreach($rows as $row)   
          {
            $place_count[$row->place]=$row->counter;
          }
        }
        $place_count[0]=0; // sum goes here
        for($i=1;$i<11;$i++)
        {
          $place_count[0]+=$place_count[$i];
        }
        $season_percent=array(0,0,0,0,0,0,0,0,0,0,0);
        $retdata=array();
        for($i=1;$i<11;$i++)
        {
          $retrow=array();
          $retrow["place"]="$i";
          $retrow["count"]=$place_count[$i];
          if($place_count[0]>=1) $percent=$place_count[$i]*100.0/$place_count[0];
          else $percent=0.0;
          $retrow["percent"]=sprintf("%.1f %%",$percent);
          $retdata[]=$retrow;
        }
        $url_data=implode(",",array_slice($place_count,1,10));
        $return_url=array("d=".$url_data,"t=1&d=".$url_data);
        // TODO: modify according to graphics view
        $retrow=array();
        $retrow["place"]="sum";
        $retrow["count"]=$place_count[0]; // TODO: maybe everything as string
        $retrow["percent"]="100.0 %";
        if($place_count[0]==0) $retrow["percent"]="0.0 %";
        $retdata[]=$retrow;
        $ret=array();
        $ret["data"]=$retdata;
        $ret["url"]=$return_url;
        return json_encode($ret);
        // TODO: maybe return object/array is too complicated?
    }

    public function getSeasonPie()
    {
        return $this->PieData(TRUE);
    }

    public function getAlltimePie()
    {
        return $this->PieData(FALSE);
    }
	
	private function getLastFiveGames(){
		$db=$this->mydb();
        $query = $db->getQuery(true);
        $query->select('place');
        $query->from('#__game_has_player');
        $query->where('player_idplayer'. " = ".$this->currentid);
		$query->order('start_time DESC');
		$query->setLimit('5');
        $db->setQuery($query);
        $ret=array();
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
			$last5 = array();
			foreach($rows as $row){
				$last5[] = $row->place;
			}
			return json_encode($last5);
		}else{
			return json_encode(array());
		}
	}

    public function getBasicInfo() // gets also used by getRankingTable
    {
        $db=$this->mydb();

        $this->set_user_id_pair(); // reading parameters userid and username

        $query = $db->getQuery(true);
        $query->select('pr.*, rank(pr.final_score,pr.season_games,pr.player_id) AS myrank, p.gender, p.country_iso, p.avatar_hash, p.avatar_mime');
        $query->from('#__player_ranking as pr');
		$query->join('LEFT', '#__player AS p ON p.player_id = pr.player_id');
        $query->where('pr.player_id'. " = ".$this->currentid);
        $db->setQuery($query);
        $ret=array();
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
            $row=$rows[0];
            $ret["final_score"]=sprintf("%.2f %%",max(0.0,($row->final_score)/10000.0));
//             $ret["average score"]=sprintf("%.2f %%",max(0.0,($row->average_score)/10000.0));
            $average_points=sprintf("%.2f",max(0.0,($row->average_score)*6.2/(25.0*1000000.0)));
            $ret["average_points"]=$average_points;
            $ret["points_sum"]=(int)(($row->points_sum)/25);
            $ret["username"]=$row->username;
            $ret["playerid"]=$row->player_id;
            $ret["season_games"]=$row->season_games;
            $ret["games_seven_days"]=$row->games_seven_days;
			$ret["country"] = $row->country_iso;
			$ret["gender"] = $row->gender;
            $ret["rank"]=$row->myrank;
			$ret["avatar"] = $row->avatar_hash . "." . $row->avatar_mime;

            $ava_hash=$row->avatar_hash;
            // SELECT * FROM `avatar_blacklist` WHERE avatar_hash='23a77825c1d4c0e785dd0cd83eddaf4f'
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__avatar_blacklist');
            $query->where($db->quoteName('avatar_hash')." = ".$db->quote($ava_hash));
            $query->setLimit('1');
            $db->setQuery($query);
            $rows = $db->loadObjectList();
            if(is_array($rows) && count($rows) >0)
            {
                // bad avatar found
                $ret["avatar"]="."; // default to "no avatar"
            }
        }
        return json_encode($ret,JSON_FORCE_OBJECT);
    }

    private function getSeasonBasicInfo($season) {
        /// returns data for a historic season
        /// $season has format "2016-09_"


		$tbl_pref = $season . "_";
        $db=$this->mydb();
        $this->set_user_id_pair(); // reading parameters userid and username

        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('`'.RDB_PREF.$tbl_pref.'player_ranking`');
		$query->where($db->quoteName('player_id')." = ".$this->currentid);
		$db->setQuery($query);
        // SELECT * FROM #__2017-01_player_ranking WHERE playerid = this->currentid
		// TODO
        $ret=array();
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0) {
			$row=$rows[0];
			$ret["final_score"]=sprintf("%.2f %%",max(0.0,($row->final_score)/10000.0));
            $xfinal=(int)$row->final_score;
            $average_points=sprintf("%.2f",max(0.0,($row->average_score)*6.2/(25.0*1000000.0)));
            $ret["average_points"]=$average_points;
            $ret["points_sum"]=(int)(($row->points_sum)/25);
            $xgames=(int)$row->season_games;
            $ret["season_games"]=$xgames;
            $xid=(int)$row->player_id;
        }
        else {
            return json_encode(array());
        }

		//   SELECT ( SELECT COUNT(*) FROM player_ranking WHERE final_score>xfinal ) + ( SELECT COUNT(*) FROM player_ranking WHERE final_score=xfinal AND season_games>xgames )+( SELECT COUNT(*) FROM player_ranking WHERE final_score=xfinal AND season_games=xgames AND player_id<xid)+1 ; 
        $subQuery1 = $db->getQuery(true);
        $subQuery1->select('COUNT(*)');
        $subQuery1->from('`'.RDB_PREF.$tbl_pref.'player_ranking`');
        $subQuery1->where($db->quoteName('final_score')." > "."$xfinal");

        $subQuery2 = $db->getQuery(true);
        $subQuery2->select('COUNT(*)');
        $subQuery2->from('`'.RDB_PREF.$tbl_pref.'player_ranking`');
        $subQuery2->where($db->quoteName('final_score')." = "."$xfinal","AND");
        $subQuery2->where($db->quoteName('season_games')." > "."$xgames");

        $subQuery3 = $db->getQuery(true);
        $subQuery3->select('COUNT(*)');
        $subQuery3->from('`'.RDB_PREF.$tbl_pref.'player_ranking`');
        $subQuery3->where($db->quoteName('final_score')." = "."$xfinal","AND");
        $subQuery3->where($db->quoteName('season_games')." = "."$xgames","AND");
        $subQuery3->where($db->quoteName('player_id')." < "."$xid");

        $query = $db->getQuery(true);
        $query->select("(".$subQuery1->__toString() . ") + (".$subQuery2->__toString() . ") + (".$subQuery3->__toString() . ") + 1");
        $db->setQuery($query);

        $rank = (int)$db->loadResult();
        $ret["rank"]=$rank;
        return json_encode($ret);
	}
	
	public function getLastGames(){
        $db=$this->mydb();

		$jinput = JFactory::getApplication()->input;
        $userid = $jinput->get('userid',0,'INT');
		$more = $jinput->getBool('more',false);
		
		
		
		$ret = array();
		if($userid == 0) return json_encode($ret);

        $query = $db->getQuery(true);
        $query->select('ghp.*, g.name, g.end_time');
        $query->from('#__game_has_player as ghp');
		$query->join('LEFT', '#__game AS g ON g.idgame = ghp.game_idgame');
        $query->where('ghp.player_idplayer'. " = ".$db->quote($userid));
		$query->where('ghp.game_idgame'. " != 0");
		$query->order("start_time DESC");
		if(!$more){
			$query->setLimit('20');	
		}else{
			$query->setLimit('20000', '20');	
		}
		
        $db->setQuery($query);
        $ret=array();
        $rows = $db->loadObjectList();

        if(is_array($rows) && count($rows) > 0)
        {
			$ret = $rows;
		}
		return json_encode($ret);
	}

    public function getProfile() // maybe not needed
    {
        // collecting the single parts and putting it in one ass.array
        $ret=array();
        $ret["basic"]=json_decode($this->getBasicInfo());
        $ret["seasonpie"]=json_decode($this->getSeasonPie());
        $ret["alltimepie"]=json_decode($this->getAlltimePie());
		$ret["last5"]=json_decode($this->getLastFiveGames());
		$ret["all"] = json_decode($this->getAllSeasonsData());
        return json_encode($ret);
    }

    public function getGamingtable() // for click on players during games
    {
        // TODO: what args can appear?

     // http://pokerth.net/component/pthranking/?view=pthranking&layout=profile&tableview=1&nick1=weinertb&nick2=Pheekreore&nick3=sasha&nick4=Ezza&nick5=yagiello&nick6=Miss%20Groves&nick7=zotti&nick8=szynser&nick9=MindReader&nick10=TheBrain&table=My%20Online%20Game456

        $db=$this->mydb();
        $nickinputs=array();
        $nicksearches=array();
        $jinput = JFactory::getApplication()->input;
        $gamename=$jinput->get('table','','STRING');
        $ret=array(); // assoc
        $ret["gamename"]=$gamename;
        $ret["table"]=array();
        $ret["notfound"]=array();
        for($i=1;$i<11;$i++)
        {
            $nickx = trim($jinput->get("nick$i","",'STRING'));
            if($nickx!="") {
                $nickinputs[$i]=$nickx;
                $nicksearches[]=$db->quote($nickx);
            }
        }
        if(count($nickinputs)<=0)
        {
            return(json_encode($ret));
        }
        // ignore GET tableview=1

        // TODO: sql query
        $query = $db->getQuery(true);
        $query->select('*, rank(final_score,season_games,player_id) AS myrank');
        $query->from('#__player_ranking');
        $query->where('BINARY username in ('.implode(',',$nicksearches).')');
//         $query->order('myran'); // TODO: this or username?
//         $query->order('username');
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
            $return = true;
        }
        else return(json_encode($ret));

        $table=array();
        foreach($rows as $row) {
            $tableentry=array(); // assoc
			// @XXX: show only usnernames that are exactly in $nickinputs array
			if(!in_array($row->username, $nickinputs)) continue;
            $tableentry["username"]=$row->username;
            $index=99;
            for($i=1;$i<11;$i++) {
                if(!(array_key_exists($i,$nickinputs))) continue;
                if($nickinputs[$i]==$row->username) $index=$i;
                if($nickinputs[$i]==$row->username) $nickinputs[$i]="";
            } // remove found nicknames
            $final_score=sprintf("%.2f %%",max(0.0,($row->final_score)/10000.0));
            $tableentry["final_score"]=$final_score;
//             $average_score=sprintf("%.2f %%",max(0.0,($row->average_score)/10000.0));
//             $tableentry["average_score"]=$average_score;
            $average_points=sprintf("%.2f",max(0.0,($row->average_score)*6.2/(25.0*1000000.0)));
            $tableentry["average_points"]=$average_points;
            $tableentry["userid"]=$row->player_id;
            $tableentry["season_games"]=$row->season_games;
            $tableentry["rank"]=$row->myrank;
            $table[$index]=$tableentry;
        }
        ksort($table);
        $table2=array_values($table);
        $notfound=array();
        for($i=1;$i<11;$i++) {
            if(!(array_key_exists($i,$nickinputs))) continue;
            if($nickinputs[$i]!="") $notfound[]=$nickinputs[$i];
        }
        $ret["table"]=$table2;
        $ret["notfound"]=$notfound;
        return json_encode($ret);
    }

    public function getGameInfo() // call by gameid
    {
        $ret=array(); // return value
        $db=$this->mydb();
        $jinput = JFactory::getApplication()->input;
        $gameid=$jinput->get('gameid',-1,'INT'); // dunno if 0 is already used

        // query for #__game
        $query = $db->getQuery(true);
        $query->select('*, TIMEDIFF(end_time,start_time) AS dur');
        $query->from('#__game');
        $query->where($db->quoteName('idgame')." = $gameid");
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if(!(is_array($rows) && count($rows) > 0))
        {
            return json_encode(array()); // error - game not found
        }
        $row=$rows[0];
        $ret["gamename"]=$row->name; // why is "name" highlited in my editor?
        $ret["start_time"]=$row->start_time; // formatting necessary?
        $ret["end_time"]=$row->end_time; // formatting necessary?
        $ret["duration"]=$row->dur; // formatting necessary?
        $ret["gameid"]=$gameid;

        $places=array(); // for results

        $query = $db->getQuery(true);
        $query->select('ghp.place, ghp.player_idplayer, pr.*');
        $query->from('#__game_has_player AS ghp');
        $query->join('LEFT', '#__player_ranking AS pr ON pr.player_id=ghp.player_idplayer');
        $query->where($db->quoteName('game_idgame')." = $gameid");
		$query->order("place ASC");
        $db->setQuery($query); // TODO: get also names from players

        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
            foreach($rows as $row)
            {
                $tableentry=array();
                $tableentry["place"]=$row->place;
                $tableentry["userid"]=$row->player_idplayer;
                $tableentry["username"]=$row->username;
                // TODO: more info - but we have already enough for link
                $places[]=$tableentry;
            }
        }
        $ret["places"]=$places;
        return json_encode($ret);
    }
	
	public function getDelAcc(){
		$return = false;
		$user = JFactory::getUser();
		$jinput = JFactory::getApplication()->input;
        $email = $jinput->get('email',"",'STRING');
		if($user->guest || $user->email != $email){
			return "nok";
		}
		
		//die(var_export($user, true));
		
        $db = $this->mydb();

        // query for #__game
        $query = $db->getQuery(true);
        $query->select('player_id, username');
        $query->from('#__player');
        $query->where($db->quoteName('email')." = '".$user->email."'");
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
			$player = $rows[0];
			// create an entry in suspended usernames
			$query = $db->getQuery(true);
			$columns = array('username', 'suspended_date');
			$values = array(
				$db->quote($player->username),
				$db->quote(date("Y-m-d H:i:s")),
			);
			$query
				->insert($db->quoteName('#__suspended_usernames'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			$db->setQuery($query);
			$res = $db->execute();
			// rename username and email
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('username') . " = 'deleted_".$player->player_id."'",
				$db->quoteName('email') . " = 'deleted_".$player->player_id."'",
            );
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('player_id') . ' = ' . $player->player_id
            );
            $query->update($db->quoteName('#__player'))->set($fields)->where($conditions);
            $db->setQuery($query);
			try{
				$result = $db->execute();
			}
			catch(Exception $e){
				die($e->getMessage());
			}
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('username') . " = 'deleted_".$player->player_id."'",
            );
            $conditions = array(
                $db->quoteName('player_id') . ' = ' . $player->player_id
            );
            $query->update($db->quoteName('#__player_ranking'))->set($fields)->where($conditions);
            $db->setQuery($query);
			try{
				$result = $db->execute();
			}
			catch(Exception $e){
				die($e->getMessage());
			}
        }else{
			// no game account - delete forum account only
		}
		// delete joomla account
		//die(var_export($user->id,true));
		$db = JFactory::getDBO(); // db object for joomla database
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($user->id),
		);
		$query->delete($db->quoteName('#__users'));
		$query->where($conditions);
		$db->setQuery($query);
		try{
			$result = $db->execute();
		}
		catch(Exception $e){
			die($e->getMessage());
		}
		// delete kunena forum user
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('userid') . ' = ' . $db->quote($user->id),
		);
		$query->delete($db->quoteName('#__kunena_users'));
		$query->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		// delete other entries = entry in `#__user_usergroup_map`
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('user_id') . ' = ' . $db->quote($user->id),
		);
		$query->delete($db->quoteName('#__user_usergroup_map'));
		$query->where($conditions);
		$db->setQuery($query);
		try{
			$result = $db->execute();
		}
		catch(Exception $e){
			die($e->getMessage());
		}
		
		return "ok";
	}
	
    public function getAllSeasonsData()
    {
		
		$this->set_user_id_pair();
		// @XXX: iterate through each season - finally calculate alltime from every season
		$first = explode("-", FIRST_SEAS);
		$current = array(date("Y") , ceil(date("m")/3));
		$seasons = array();
		$seasons[] = FIRST_SEAS . "_";
		for($year=$first[0];$year<=$current[0];$year++){
			for($quart=1;$quart<=4;$quart++){
				if($year == $first[0] && $quart <= $first[1]) continue;
				if($year < $current[0] || ($year == $current[0] && $quart < $current[1])){
					$seasons[] = $year."-".$quart."_";
					continue;
				}
			}
		}
		$seasons[] = "";
		$seasonData = array();
		$allTime = array("place" => array(), "sum" => 0);
		for($i=1;$i<11;$i++){
			$allTime["place"][$i] = 0;
		}
		foreach($seasons as $season){
			$tbl_pref = $season;
			$db=$this->mydb();
			$query = $db->getQuery(true);
			$query->select('place , COUNT(*) AS counter');
			$query->from('`'.RDB_PREF.$tbl_pref.'game_has_player`');
			$query->where($db->quoteName('player_idplayer')." = ".$this->currentid,'AND');
			$query->group('place');
			try{
				$db->setQuery($query); // TODO: appropriate INDEX for this query
				$rows = $db->loadObjectList();
			}catch(Exception $e){
				die($e->getMessage());
			}
	
			$place_count=array(0,0,0,0,0,0,0,0,0,0,0);
			if(is_array($rows) && count($rows)>=1)
			{
			  foreach($rows as $row)   
			  {
				$place_count[$row->place]=$row->counter;
			  }
			}
			$place_count[0]=0; // sum goes here
			for($i=1;$i<11;$i++)
			{
			  $place_count[0]+=$place_count[$i];
			}
			$season_percent=array(0,0,0,0,0,0,0,0,0,0,0);
			$retdata=array();
			for($i=1;$i<11;$i++)
			{
			  $retrow=array();
			  $retrow["place"]="$i";
			  $retrow["count"]=$place_count[$i];
			  if($place_count[0]>=1) $percent=$place_count[$i]*100.0/$place_count[0];
			  else $percent=0.0;
			  $retrow["percent"]=sprintf("%.1f %%",$percent);
			  $allTime["place"][$i] += $place_count[$i];
			  $allTime["sum"] += $place_count[$i];
			  $retdata[]=$retrow;
			}
			$url_data=implode(",",array_slice($place_count,1,10));
			$return_url=array("d=".$url_data,"t=1&d=".$url_data);
			// TODO: modify according to graphics view
			$retrow=array();
			$retrow["place"]="sum";
			$retrow["count"]=$place_count[0]; // TODO: maybe everything as string
			$retrow["percent"]="100.0 %";
			if($place_count[0]==0) $retrow["percent"]="0.0 %";
			$retdata[]=$retrow;
			$ret=array();
			$ret["data"]=$retdata;
			$ret["url"]=$return_url;
			if($season == ""){
				$season = date("Y") ."-". ceil(date("m")/3);
//                 $ret["basic"]=array();
			}else{
				$season = str_replace("_", "", $season);
                $ret["basic"]=json_decode($this->getSeasonBasicInfo($season));
			}
			// @TODO: fill alltime array! doing it in above foreach or ieterate again?

			$seasonData[$season] = $ret;
		}
		$seasonData["total"] = $allTime;
        return json_encode($seasonData);
        // TODO: maybe return object/array is too complicated?
    }
}

// TODO AlltimePie
