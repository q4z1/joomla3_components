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
	
	protected $db_salt = "mySalt";

    protected $currentid=0; // these should always be in a pair
    protected $currentname=""; // these should always be in a pair
 
	/**
	 * Get the message
         *
	 * @return  json 	array of user objects for ajax-called userlist in uddeim pm component
	 */
     // get the other database
     private function mydb()
     {

        $option = array(); //prevent problems
        $option['driver']   = 'mysql';            // Database driver name
        $option['host']     = 'localhost';    // Database host name
        $option['user']     = 'pthrdbuser';       // User for database authentication
        $option['password'] = 'BKmTEOUOeRjgiwyP';   // Password for database authentication
//        $option['user']     = 'root';       // User for database authentication
//        $option['password'] = ' ';   // Password for database authentication
        $option['database'] = 'pokerth_ranking';      // Database name
        $option['prefix']   = '';             // Database prefix (may be empty)
         
        $db = JDatabaseDriver::getInstance( $option );
        return($db); // TODO: maybe remember the result
     }
	 
	 public function getStoreUserdata(){

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
		$act_key = md5(time());

		// @TODO: make some checks

		
		// @XXX: store data into db
		$db = $this->mydb();
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Insert columns.
		$columns = array('username', 'password', 'email', 'created', 'country_iso', 'gender', 'act_key');
		 
		// Insert values.
		$values = array(
			$db->quote($username),
			"AES_ENCRYPT('".mysql_real_escape_string($password)."', '".$this->db_salt."')", // $db->quote destroys the AES_ENCRYPT function - so it's oldschool mysql_real_escape ;)
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
		
		// @XXX: send an email with the activation key & link for activation page
		$email = "ernstlich.heiter@gmail.com"; // @FIXME: debug
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		$sender = array( 
			$config->get( 'mailfrom' ),
			$config->get( 'fromname' ) 
		);
		$mailer->setSender($sender);
		$mailer->addRecipient(array($email, $config->get( 'mailfrom' )));
		$body   = '<h2>Test Mail ('.$config->get( 'mailfrom' ).' is put as recipient too)</h2>'
			. '<div>This is a test mail for the upcoming email validation.... act_key = ' . $act_key
			. '<img src="cid:logo_id" alt="logo"/></div>';
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);
		// Optionally add embedded image
		$mailer->AddEmbeddedImage( JPATH_COMPONENT.'/media/kunena/email/pokerth.png', 'logo_id', 'logo.jpg', 'base64', 'image/jpeg' );
		$send = $mailer->Send();
		if ( $send !== true ) {
			mDebug('Error sending email: ' . $send->__toString());
		} else {
			mDebug('Mail sent');
		}
		
		
		// @XXX: creating a forum account will be done, when email address is validated

		return json_encode(array("status" => "ok", "response" => $res));
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



    public function getSeasonPie()
    {
        $seasononly=TRUE; // False means all-time
        $return_data=TRUE; // should we return the main data
        $return_png_url=FALSE; // should we return url for pie graph

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
        $query->where($db->quoteName('player_idplayer')." = ".$this->currentid,'AND');
        $query->where($db->quoteName('start_time')." >= start_of_this_season('$now')");
        $query->group('place');
        $db->setQuery($query); // TODO: appropriate INDEX for this query
        $rows = $db->loadObjectList();

        $season_pie=array(0,0,0,0,0,0,0,0,0,0,0);
        if(is_array($rows) && count($rows)>=1)
        {
          foreach($rows as $row)   
          {
            $season_pie[$row->place]=$row->counter;
          }
        }
        $season_pie[0]=0; // sum goes here
        for($i=1;$i<11;$i++)
        {
          $season_pie[0]+=$season_pie[$i];
        }
        $season_percent=array(0,0,0,0,0,0,0,0,0,0,0);
        $ret=array();
        for($i=1;$i<11;$i++)
        {
          $retrow=array();
          $retrow["place"]="$i";
          $retrow["count"]=$season_pie[$i];
          if($season_pie[0]>=1) $percent=$season_pie[$i]*100.0/$season_pie[0];
          else $percent=0.0;
          $retrow["percent"]=sprintf("%.1f %%",$percent);
          $ret[]=$retrow;
        }
        $retrow=array();
        $retrow["place"]="sum";
        $retrow["count"]=$season_pie[0]; // TODO: maybe everything as string
        $retrow["percent"]="100.0 %";
        if($season_pie[0]==0) $retrow["perccent"]="0.0 %";
        $ret[]=$retrow;
        return json_encode($ret);
    }
}

// TODO AlltimePie
