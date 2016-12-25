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
class PthRankingModelEmailval extends JModelItem
{
	/**
	 * @var string message
	 */
	protected $message;
    
    protected $act_key;
	
	protected $gender = array("m" => 1, "f" => 2);
	
	protected $country_iso = array(
		"Afghanistan" => "AF",
		"Albania" => "AL",
		"Algeria" => "DZ",
		"Andorra" => "AD",
		"Angola" => "AO",
		"Antigua and Barbuda" => "AG",
		"Argentina" => "AR",
		"Armenia" => "AM",
		"Australia" => "AU",
		"Austria" => "AT",
		"Azerbaijan" => "AZ",
		"Bahamas, The" => "BS",
		"Bahrain" => "BH",
		"Bangladesh" => "BD",
		"Barbados" => "BB",
		"Belarus" => "BY",
		"Belgium" => "BE",
		"Belize" => "BZ",
		"Benin" => "BJ",
		"Bhutan" => "BT",
		"Bolivia" => "BO",
		"Bosnia and Herzegovina" => "BA",
		"Botswana" => "BW",
		"Brazil" => "BR",
		"Brunei" => "BN",
		"Bulgaria" => "BG",
		"Burkina Faso" => "BF",
		"Burundi" => "BI",
		"Cambodia" => "KH",
		"Cameroon" => "CM",
		"Canada" => "CA",
		"Cape Verde" => "CV",
		"Central African Republic" => "CF",
		"Chad" => "TD",
		"Chile" => "CL",
		"China" => "CN",
		"Colombia" => "CO",
		"Comoros" => "KM",
		"Congo" => "CD",
		"Congo" => "CG",
		"Costa Rica" => "CR",
		"Cote d'Ivoire (Ivory Coast)" => "CI",
		"Croatia" => "HR",
		"Cuba" => "CU",
		"Cyprus" => "CY",
		"Czech Republic" => "CZ",
		"Denmark" => "DK",
		"Djibouti" => "DJ",
		"Dominica" => "DM",
		"Dominican Republic" => "DO",
		"Ecuador" => "EC",
		"Egypt" => "EG",
		"El Salvador" => "SV",
		"Equatorial Guinea" => "GQ",
		"Eritrea" => "ER",
		"Estonia" => "EE",
		"Ethiopia" => "ET",
		"Fiji" => "FJ",
		"Finland" => "FI",
		"France" => "FR",
		"Gabon" => "GA",
		"Gambia, The" => "GM",
		"Georgia" => "GE",
		"Germany" => "DE",
		"Ghana" => "GH",
		"Greece" => "GR",
		"Grenada" => "GD",
		"Guatemala" => "GT",
		"Guinea" => "GN",
		"Guinea-Bissau" => "GW",
		"Guyana" => "GY",
		"Haiti" => "HT",
		"Honduras" => "HN",
		"Hungary" => "HU",
		"Iceland" => "IS",
		"India" => "IN",
		"Indonesia" => "ID",
		"Iran" => "IR",
		"Iraq" => "IQ",
		"Ireland" => "IE",
		"Israel" => "IL",
		"Italy" => "IT",
		"Jamaica" => "JM",
		"Japan" => "JP",
		"Jordan" => "JO",
		"Kazakhstan" => "KZ",
		"Kenya" => "KE",
		"Kiribati" => "KI",
		"Korea, North" => "KP",
		"Korea, South" => "KR",
		"Kuwait" => "KW",
		"Kyrgyzstan" => "KG",
		"Laos" => "LA",
		"Latvia" => "LV",
		"Lebanon" => "LB",
		"Lesotho" => "LS",
		"Liberia" => "LR",
		"Libya" => "LY",
		"Liechtenstein" => "LI",
		"Lithuania" => "LT",
		"Luxembourg" => "LU",
		"Macedonia" => "MK",
		"Madagascar" => "MG",
		"Malawi" => "MW",
		"Malaysia" => "MY",
		"Maldives" => "MV",
		"Mali" => "ML",
		"Malta" => "MT",
		"Marshall Islands" => "MH",
		"Mauritania" => "MR",
		"Mauritius" => "MU",
		"Mexico" => "MX",
		"Micronesia" => "FM",
		"Moldova" => "MD",
		"Monaco" => "MC",
		"Mongolia" => "MN",
		"Montenegro" => "ME",
		"Morocco" => "MA",
		"Mozambique" => "MZ",
		"Myanmar (Burma)" => "MM",
		"Namibia" => "NA",
		"Nauru" => "NR",
		"Nepal" => "NP",
		"Netherlands" => "NL",
		"New Zealand" => "NZ",
		"Nicaragua" => "NI",
		"Niger" => "NE",
		"Nigeria" => "NG",
		"Norway" => "NO",
		"Oman" => "OM",
		"Pakistan" => "PK",
		"Palau" => "PW",
		"Panama" => "PA",
		"Papua New Guinea" => "PG",
		"Paraguay" => "PY",
		"Peru" => "PE",
		"Philippines" => "PH",
		"Poland" => "PL",
		"Portugal" => "PT",
		"Qatar" => "QA",
		"Romania" => "RO",
		"Russia" => "RU",
		"Rwanda" => "RW",
		"Saint Kitts and Nevis" => "KN",
		"Saint Lucia" => "LC",
		"Saint Vincent and the Grenadines" => "VC",
		"Samoa" => "WS",
		"San Marino" => "SM",
		"Sao Tome and Principe" => "ST",
		"Saudi Arabia" => "SA",
		"Senegal" => "SN",
		"Serbia" => "RS",
		"Seychelles" => "SC",
		"Sierra Leone" => "SL",
		"Singapore" => "SG",
		"Slovakia" => "SK",
		"Slovenia" => "SI",
		"Solomon Islands" => "SB",
		"Somalia" => "SO",
		"South Africa" => "ZA",
		"Spain" => "ES",
		"Sri Lanka" => "LK",
		"Sudan" => "SD",
		"Suriname" => "SR",
		"Swaziland" => "SZ",
		"Sweden" => "SE",
		"Switzerland" => "CH",
		"Syria" => "SY",
		"Tajikistan" => "TJ",
		"Tanzania" => "TZ",
		"Thailand" => "TH",
		"Timor-Leste (East Timor)" => "TL",
		"Togo" => "TG",
		"Tonga" => "TO",
		"Trinidad and Tobago" => "TT",
		"Tunisia" => "TN",
		"Turkey" => "TR",
		"Turkmenistan" => "TM",
		"Tuvalu" => "TV",
		"Uganda" => "UG",
		"Ukraine" => "UA",
		"United Arab Emirates" => "AE",
		"United Kingdom" => "GB",
		"United States" => "US",
		"Uruguay" => "UY",
		"Uzbekistan" => "UZ",
		"Vanuatu" => "VU",
		"Vatican City" => "VA",
		"Venezuela" => "VE",
		"Vietnam" => "VN",
		"Yemen" => "YE",
		"Zambia" => "ZM",
		"Zimbabwe" => "ZW",
		"Abkhazia" => "GE",
		"China, Republic of (Taiwan)" => "TW",
		"Nagorno-Karabakh" => "AZ",
		"Northern Cyprus" => "CY",
		"Pridnestrovie (Transnistria)" => "MD",
		"Somaliland" => "SO",
		"South Ossetia" => "GE",
		"Ashmore and Cartier Islands" => "AU",
		"Christmas Island" => "CX",
		"Cocos (Keeling) Islands" => "CC",
		"Coral Sea Islands" => "AU",
		"Heard Island and McDonald Islands" => "HM",
		"Norfolk Island" => "NF",
		"New Caledonia" => "NC",
		"French Polynesia" => "PF",
		"Mayotte" => "YT",
		"Saint Barthelemy" => "GP",
		"Saint Martin" => "GP",
		"Saint Pierre and Miquelon" => "PM",
		"Wallis and Futuna" => "WF",
		"French Southern and Antarctic Lands" => "TF",
		"Clipperton Island" => "PF",
		"Bouvet Island" => "BV",
		"Cook Islands" => "CK",
		"Niue" => "NU",
		"Tokelau" => "TK",
		"Guernsey" => "GG",
		"Isle of Man" => "IM",
		"Jersey" => "JE",
		"Anguilla" => "AI",
		"Bermuda" => "BM",
		"British Indian Ocean Territory" => "IO",
		"British Sovereign Base Areas" => "UK",
		"British Virgin Islands" => "VG",
		"Cayman Islands" => "KY",
		"Falkland Islands (Islas Malvinas)" => "FK",
		"Gibraltar" => "GI",
		"Montserrat" => "MS",
		"Pitcairn Islands" => "PN",
		"Saint Helena" => "SH",
		"South Georgia & South Sandwich Islands" => "GS",
		"Turks and Caicos Islands" => "TC",
		"Northern Mariana Islands" => "MP",
		"Puerto Rico" => "PR",
		"American Samoa" => "AS",
		"Baker Island" => "UM",
		"Guam" => "GU",
		"Howland Island" => "UM",
		"Jarvis Island" => "UM",
		"Johnston Atoll" => "UM",
		"Kingman Reef" => "UM",
		"Midway Islands" => "UM",
		"Navassa Island" => "UM",
		"Palmyra Atoll" => "UM",
		"U.S. Virgin Islands" => "VI",
		"Wake Island" => "UM",
		"Hong Kong" => "HK",
		"Macau" => "MO",
		"Faroe Islands" => "FO",
		"Greenland" => "GL",
		"French Guiana" => "GF",
		"Guadeloupe" => "GP",
		"Martinique" => "MQ",
		"Reunion" => "RE",
		"Aland" => "AX",
		"Aruba" => "AW",
		"Netherlands Antilles" => "AN",
		"Svalbard" => "SJ",
		"Ascension" => "AC",
		"Tristan da Cunha" => "TA",
		"Australian Antarctic Territory" => "AQ",
		"Ross Dependency" => "AQ",
		"Peter I Island" => "AQ",
		"Queen Maud Land" => "AQ",
		"British Antarctic Territory" => "AQ",
		"Catalonia" => "catalonia",
	);

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
     
	public function getDoValidation()
	{
        $return = false;
        $player_entry = null;
        
        // @XXX: have to catch the get param again as $this->act_key is not inherited from view :(
		$jinput = JFactory::getApplication()->input;
		$this->act_key = $jinput->get('actkey', "", 'ALNUM');
        
        $db = $this->mydb();
        $query = $db->getQuery(true);
        $query->select("player_id,username,CAST(AES_DECRYPT(password, '".RDB_SALT."') AS CHAR) as password,email,gender,country_iso");
        $query->from('#__player');
        $query->where($db->quoteName('act_key') . " = ".$db->quote($this->act_key) );
        $query->where($db->quoteName('active') . " = 0" );
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			$return = true;
            $player_entry = $rows[0];
		}
        
        
        if($return === true){
            // @XXX: set active to 1
            $query = $db->getQuery(true);
            // Fields to update.
            $fields = array(
                $db->quoteName('active') . ' = 1',
            );
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('player_id') . ' = ' . $player_entry->player_id
            );
            $query->update($db->quoteName('#__player'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();
			
			// @XXX: create entry in #__player_ranking
			// Create a new query object.
			$query = $db->getQuery(true);
			
			// Insert columns.
			$columns = array(
				'player_id',
				'username',
			);
			 
			// Insert values.
			$values = array(
				$db->quote($player_entry->player_id),
				$db->quote($player_entry->username),
			);
			
			// Prepare the insert query.
			$query
				->insert($db->quoteName('#__player_ranking'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			 
			// Set the query using our newly populated query object and execute it.
			$db->setQuery($query);
			$res = $db->execute();
            
            // create entry in #__users table
			
			// create a joomla3 encrypted password
			jimport('joomla.user.helper');
			$joomla3password = JUserHelper::hashPassword($player_entry->password);
			
            $db = JFactory::getDBO(); // db object for joomla database
			// Create a new query object.
			$query = $db->getQuery(true);
			
			// Insert columns.
			$columns = array(
				'name',
				'username',
				'email',
				'password',
				'registerDate',
				'params',
			);
			 
			// Insert values.
			$values = array(
				$db->quote($player_entry->username),
				$db->quote($player_entry->username),
				$db->quote($player_entry->email),
				$db->quote($joomla3password),
				$db->quote(date("Y-m-d H:i:s")),
				$db->quote("{}"),
			);
			
			// Prepare the insert query.
			$query
				->insert($db->quoteName('#__users'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			 
			// Set the query using our newly populated query object and execute it.
			$db->setQuery($query);
			$res = $db->execute();
			
			if($res){
				$userid = $db->insertid(); // fetch last insert id
				
				// entry in `#__user_usergroup_map` table necessary
				$query = $db->getQuery(true);
				// Insert columns.
				$columns = array(
					'user_id',
					'group_id',
				);
				// Insert values.
				$values = array(
					$userid,
					2,
				);
				// Prepare the insert query.
				$query
					->insert($db->quoteName('#__user_usergroup_map'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));
				 
				// Set the query using our newly populated query object and execute it.
				$db->setQuery($query);
				$res = $db->execute();

				// create entry in #__kunenan_users table
				$query = $db->getQuery(true);
				
				// Insert columns.
				$columns = array(
					'userid',
					'signature',
					'personalText',
					'ip',
					'gender',
					'location',
				);
				
				// Insert values.
				$values = array(
					$userid,
					"NULL",
					"NULL",
					$db->quote($_SERVER['REMOTE_ADDR']),
					$db->quote(($player_entry->gender != "") ? $this->gender[$player_entry->gender] : 0),
					$db->quote(($player_entry->country_iso != "") ? array_search($player_entry->country_iso, $this->country_iso) : ''),
				);
				
				// Prepare the insert query.
				$query
					->insert($db->quoteName('#__kunena_users'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));
				 
				// Set the query using our newly populated query object and execute it.
				$db->setQuery($query);
				$res = $db->execute();
			}
        }
        return $return;
	}

}