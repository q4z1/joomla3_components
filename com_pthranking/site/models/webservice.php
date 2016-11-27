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
	
     // get the other database
     private function mydb()
     {
        $option = array(); //prevent problems
        $option['driver']   = RDB_DRIVER;
        $option['host']     = RDB_HOST;
        $option['user']     = RDB_USER;
        $option['password'] = RDB_PASS;
        $option['database'] = RDB_DB;
        $option['prefix']   = RDB_PREF;
        $db = JDatabaseDriver::getInstance( $option );
        return($db); // TODO: maybe remember the result
     }
	 
	 public function getStoreUserdata(){
		$return = false;
		$response = "unspecified";
		$status = "nok";
		
		$jinput = JFactory::getApplication()->input;
		
		$submit = $jinput->post->get('submit', false, 'BOOL');
		
		if(!$submit){
			return json_encode(array("status" => "nok", "reason" => "submit not set or false"));
		}
		
		$email = $jinput->post->get('email', "", 'STRING');
		$username = $jinput->post->get('username', "", 'STRING');
		$password = base64_decode($jinput->post->get('password', "", 'BASE64'));
		$gender = $jinput->post->get('gender', "", 'WORD');
		$country = $jinput->post->get('country', "", 'WORD');
		$act_key = md5(microtime()); // microtime() instead of time() - just to be safe

		// @TODO: make some checks?

		// @XXX: store data into db
		$db = $this->mydb();
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Insert columns.
		$columns = array('username', 'password', 'email', 'created', 'country_iso', 'gender', 'act_key');
		 
		// Insert values.
		$values = array(
			$db->quote($username),
			"AES_ENCRYPT('".mysql_real_escape_string($password)."', '".RDB_SALT."')", // $db->quote destroys the AES_ENCRYPT function - so it's oldschool mysql_real_escape_string ;)
			$db->quote($email),
			$db->quote(date("Y-m-d H:i:s")),
			$db->quote($country),
			$db->quote($gender),
			$db->quote($act_key), // maybe a shorter activation key for email validation?
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
			// @XXX: send an email with the activation key & link for activation page - partly taken from components/com_users/models/registration.php
			$config = JFactory::getConfig();
	
			$data['name'] = $username;
			$data['fromname'] = $config->get('fromname');
			$data['mailfrom'] = $config->get('mailfrom');
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
						"<h3>Hello %s,</h3>\n\n<p>Thank you for registering for the game at %s.</p>\n\n<p>Your game-account is created and must be activated before you can use it.</p>\n\n<p>To activate the game-account select the following link or copy-paste it in your browser:\n\n<br /><a href='%s'>%s</a></p>\n\n<p>After activation you may login to the <a href='%s'>Forum</a> and to the Game using the following username and the password you entered during registration:\n\n<br /><br />Username: %s<br />\n\nPassword: %s</p><br /><br />\n\nKind regards,<br />\n\nYour PokerTH Team",
						$data['name'],
						$data['sitename'],
						$data['activate'],
						$data['activate'],
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
        $db = $this->mydb();
        $query = $db->getQuery(true);
        $query->select('player_id,username');
        $query->from('#__player');
        $query->where($db->quoteName('username') . " = ".$db->quote($username) );
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			$return = true;
		}
		
		// next check if nick with forum account exists
		if(!$return){
			$db2    = JFactory::getDBO();
			$query = $db2->getQuery(true);
			$query->select('id,username,email,lastvisitDate');
			$query->from('#__users');
            $query->where($db2->quoteName('username') . " = ".$db2->quote($username) );
			$query->where($db2->quoteName('lastvisitDate') . " > '". date("Y-m-d H:i:s", strtotime('-12 month', time())) . "'");
			$db2->setQuery($query);
			$rows = $db2->loadObjectList();
			if(is_array($rows) && count($rows) > 0){
				$return = true;
			}
		}
		
		return json_encode(array("status" => "ok", "response" => $return));
	}

	public function getCheckEmail()
	{
        $return = false;
        
        $jinput = JFactory::getApplication()->input;
        $email = $jinput->get('pthemail', "", 'STRING');
		
		if($email == "") return json_encode(array("status" => "nok", "reason" => "email empty"));
        
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


        $start=(int)$start;
        $size=(int)$size;
        if($size<=0) $size=50;
        if($start<=0) $start=1;

        // TODO: maybe get from input/jinput like in webservice
        
        $db=$this->mydb();
        
        $return=false;
        $start2=$start-1;
        // start query
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__player_ranking');
        $query->where('1');
        $query->order('final_score DESC, season_games DESC, player_id ASC');
        $query->setLimit($size,$start2);
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			$return = true;
		}
        if(!$return) return(json_encode(array("Error: nothing found")));

        $table=array();

        $rank=$start;
        foreach($rows as $row) {
            $tableentry=array(); // associative array, maybe dict/object?
            $tableentry["username"]=$row->username;
            $final_score=sprintf("%.2f %%",max(0.0,($row->final_score)/10000.0));
            $tableentry["final_score"]=$final_score;
            $average_score=sprintf("%.2f %%",max(0.0,($row->average_score)/10000.0));
            $tableentry["average_score"]=$average_score;
            $tableentry["season_games"]=$row->season_games;
            $tableentry["rank"]=$rank;
            $table[]=$tableentry;
            $rank+=1;
        }

       return json_encode($table);
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
          $query->from('#__player_ranking');
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
            $query->where($db->quoteName('start_time')." >= start_of_this_season('$now')");
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
        $return_url=array("pic1.php?d=".$url_data,"pic1.php?t=".$url_data);
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

    public function getBasicInfo()
    {
        $db=$this->mydb();

        $this->set_user_id_pair(); // reading parameters userid and username

        $query = $db->getQuery(true);
        $query->select('*, rank(final_score,season_games,player_id) AS myrank');
        $query->from('#__player_ranking');
        $query->where('player_id'. " = ".$this->currentid);
        $db->setQuery($query);
        $ret=array();
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
            $row=$rows[0];
            $ret["final score"]=sprintf("%.2f %%",max(0.0,($row->final_score)/10000.0));
            $ret["average score"]=sprintf("%.2f %%",max(0.0,($row->average_score)/10000.0));
            $ret["points sum"]=$row->points_sum;
            $ret["username"]=$row->username;
            $ret["playerid"]=$row->player_id;
            $ret["season games"]=$row->season_games;
            $ret["games seven days"]=$row->games_seven_days;
            $ret["rank"]=$row->myrank;
            // TODO: more in-between calculation, bonus/malus explained
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
        return json_encode($ret);
    }

}

// TODO AlltimePie
