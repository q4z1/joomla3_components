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
 * HTML View class for the HelloWorld Component
 *
 * @since  0.0.1
 */
class PthRankingViewRegistration extends JViewLegacy
{
	
	protected $submit = false;
	protected $exists = false;
	
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
	);
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
        $user = JFactory::getUser();
        if(!$user->guest){
			$uri = JUri::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$url = $base . JRoute::_('index.php?option=com_pthranking&view=activategame', false);
            header("Location: $url");
			die();
        }
		
		
		// @TODO: clause for $this->submit no longer needed as the post will be done by webservice and the redirect to email val by jQuery
		$jinput = JFactory::getApplication()->input;
		$this->submit = $jinput->get('submit', false, 'BOOL');
		
		if($this->submit){

			
		}else{
			// nothing to do - just show the template with the registration form
		}
		
        

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
 
			return false;
		}
		
        parent::display($tpl);
	}
}