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
class PthRankingModelTable extends JModelItem
{
	/**
	 * @var string message
	 */
	protected $message;

    // TODO: database
 

	public function getTable()
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
        
        // get database part
        $option = array(); //prevent problems
        $option['driver']   = 'mysql';            // Database driver name
        $option['host']     = 'localhost';    // Database host name
        $option['user']     = 'root';       // User for database authentication
        $option['password'] = ' ';   // Password for database authentication
        $option['database'] = 'pokerth_ranking';      // Database name
        $option['prefix']   = '';             // Database prefix (may be empty)
         
        $db = JDatabaseDriver::getInstance( $option );
        // end get database
        
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
}
