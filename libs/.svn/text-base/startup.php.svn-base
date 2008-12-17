<?
// 
// AdRevenue Startup Manager
// startup.php
//
// (C) 2004,2005,2006,2007 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Reject old PHP version installs
$ver = phpversion();
if(version_compare($ver, "4.2") < 0)
{
	print "<h2>You are running an old version of PHP - ($ver)</h2>";
	print "We do not support AdRevenue on this version of php due ";
	print "to serious PHP security vulnerabilities in versions ";
	print "lower than 4.2, and buggy session handling in PHP version 4.1.2<p>";
	print "Please contact your hosting provider, or seek a more ";
	print "up-to-date host. Alternatively, we can host the program ";
	print "for you for an additional fee. <hr>";
	print "<hr>";
	phpinfo();
	exit(0);
}

// Start Session and make it last for 1 day (advertisers hate to be logged out)
session_set_cookie_params(86400);
session_start();

// Deal with annoying Windows Servers that turn on error reporting too high
error_reporting(E_ALL ^ E_NOTICE);

// Loadup the default library files
include_once("libs/lib.php");
include_once("libs/controller.php");
include_once("libs/db.php");
include_once("libs/http.php");
include_once("libs/input.php");
include_once("libs/output.php");
include_once("libs/xtpl.php");
include_once("libs/formgen.php");
include_once("libs/stopwords.php");

// Set some other defaults
$DEFAULT[adrevenue] = array();

// Loadup our settings file
@include_once("settings.php");

$DEFAULT[ad_types] = array(
			'CPC' => '@@CPC - Cost Per Click@@',
			'CPM' => '@@CPM - Cost Per 1000 Impressions@@',
			'CPD' => '@@CPD - Cost Per Day@@',
			'CPA' => '@@CPA - Cost Per Action/Order@@',
			'CPI' => '@@CPI - Cost Per Impression@@'
		);

// Ad Status
$DEFAULT[status] = array(
			2	=> '<font color=#999999>Pending</font>',
			-1	=> '<font color=red>Deactivated</font>',
			1	=> '<font color=green>Active</font>',
			3	=> '<font color=#999999>Paused</font>',
			-2	=>	'<font color=orange>Expired</font>'
		);
		
$DEFAULT[status_color] = array(
			2	=> '#999999',
			-1	=> 'red',
			1	=> 'green',
			3	=> '#999999',
			-2	=> 'orange'
		);
		
$DEFAULT[rstatus] = array(
			'all' => '-- All --',
			-1	=> 'Deactivated',
			1	=> 'Active',
			2	=> 'Pending',
			3	=> 'Paused',
			-2	=> 'Expired'
		);
		
// Main template
$DEFAULT[template] = "templates/main.html";

// Countries
$DEFAULT[country] = array(
            'AF' => 'Afghanistan',
            'AL' => 'Albania, People\'s Socialist Republic of',
            'DZ' => 'Algeria, People\'s Democratic Republic of',
            'AS' => 'American Samoa',
            'AD' => 'Andorra, Principality of',
            'AO' => 'Angola, Republic of',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica (the territory South of 60 deg S)',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina, Argentine Republic',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia, Commonwealth of',
            'AT' => 'Austria, Republic of',
            'AZ' => 'Azerbaijan, Republic of',
            'BS' => 'Bahamas, Commonwealth of the',
            'BH' => 'Bahrain, Kingdom of',
            'BD' => 'Bangladesh, People\'s Republic of',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium, Kingdom of',
            'BZ' => 'Belize',
            'BJ' => 'Benin, People\'s Republic of',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan, Kingdom of',
            'BO' => 'Bolivia, Republic of',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana, Republic of',
            'BV' => 'Bouvet Island (Bouvetoya)',
            'BR' => 'Brazil, Federative Republic of',
            'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria, People\'s Republic of',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi, Republic of',
            'KH' => 'Cambodia, Kingdom of',
            'CM' => 'Cameroon, United Republic of',
            'CA' => 'Canada',
            'CV' => 'Cape Verde, Republic of',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad, Republic of',
            'CL' => 'Chile, Republic of',
            'CN' => 'China, People\'s Republic of',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia, Republic of',
            'KM' => 'Comoros, Federal and Islamic Republic of',
            'CD' => 'Congo, Democratic Republic of',
            'CG' => 'Congo, People\'s Republic of',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica, Republic of',
            'CI' => 'Cote D\'Ivoire, Ivory Coast, Republic of the',
            'CU' => 'Cuba, Republic of',
            'CY' => 'Cyprus, Republic of',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark, Kingdom of',
            'DJ' => 'Djibouti, Republic of',
            'DM' => 'Dominica, Commonwealth of',
            'DO' => 'Dominican Republic',
            'TL' => 'Timor-Leste',
            'EC' => 'Ecuador, Republic of',
            'EG' => 'Egypt, Arab Republic of',
            'SV' => 'El Salvador, Republic of',
            'GQ' => 'Equatorial Guinea, Republic of',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FO' => 'Faeroe Islands',
            'FK' => 'Falkland Islands (Malvinas)',
            'FJ' => 'Fiji, Republic of the Fiji Islands',
            'FI' => 'Finland, Republic of',
            'FR' => 'France, French Republic',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon, Gabonese Republic',
            'GM' => 'Gambia, Republic of the',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana, Republic of',
            'GI' => 'Gibraltar',
            'GR' => 'Greece, Hellenic Republic',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadaloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala, Republic of',
            'GN' => 'Guinea, Revolutionary People\'s Rep\'c of',
            'GW' => 'Guinea-Bissau, Republic of',
            'GY' => 'Guyana, Republic of',
            'HT' => 'Haiti, Republic of',
            'HM' => 'Heard and McDonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras, Republic of',
            'HK' => 'Hong Kong, Special Administrative Region of China',
            'HR' => 'Hrvatska (Croatia)',
            'HU' => 'Hungary, Hungarian People\'s Republic',
            'IS' => 'Iceland, Republic of',
            'IN' => 'India, Republic of',
            'ID' => 'Indonesia, Republic of',
            'IR' => 'Iran, Islamic Republic of',
            'IQ' => 'Iraq, Republic of',
            'IE' => 'Ireland',
            'IL' => 'Israel, State of',
            'IT' => 'Italy, Italian Republic',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JO' => 'Jordan, Hashemite Kingdom of',
            'KZ' => 'Kazakhstan, Republic of',
            'KE' => 'Kenya, Republic of',
            'KI' => 'Kiribati, Republic of',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait, State of',
            'KG' => 'Kyrgyz Republic',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon, Lebanese Republic',
            'LS' => 'Lesotho, Kingdom of',
            'LR' => 'Liberia, Republic of',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein, Principality of',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg, Grand Duchy of',
            'MO' => 'Macao, Special Administrative Region of China',
            'MK' => 'Macedonia, the former Yugoslav Republic of',
            'MG' => 'Madagascar, Republic of',
            'MW' => 'Malawi, Republic of',
            'MY' => 'Malaysia',
            'MV' => 'Maldives, Republic of',
            'ML' => 'Mali, Republic of',
            'MT' => 'Malta, Republic of',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania, Islamic Republic of',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico, United Mexican States',
            'FM' => 'Micronesia, Federated States of',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco, Principality of',
            'MN' => 'Mongolia, Mongolian People\'s Republic',
            'MS' => 'Montserrat',
            'MA' => 'Morocco, Kingdom of',
            'MZ' => 'Mozambique, People\'s Republic of',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru, Republic of',
            'NP' => 'Nepal, Kingdom of',
            'AN' => 'Netherlands Antilles',
            'NL' => 'Netherlands, Kingdom of the',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua, Republic of',
            'NE' => 'Niger, Republic of the',
            'NG' => 'Nigeria, Federal Republic of',
            'NU' => 'Niue, Republic of',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway, Kingdom of',
            'OM' => 'Oman, Sultanate of',
            'PK' => 'Pakistan, Islamic Republic of',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama, Republic of',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay, Republic of',
            'PE' => 'Peru, Republic of',
            'PH' => 'Philippines, Republic of the',
            'PN' => 'Pitcairn Island',
            'PL' => 'Poland, Polish People\'s Republic',
            'PT' => 'Portugal, Portuguese Republic',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar, State of',
            'RE' => 'Reunion',
            'RO' => 'Romania, Socialist Republic of',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda, Rwandese Republic',
            'SH' => 'St. Helena',
            'KN' => 'St. Kitts and Nevis',
            'LC' => 'St. Lucia',
            'PM' => 'St. Pierre and Miquelon',
            'VC' => 'St. Vincent and the Grenadines',
            'WS' => 'Samoa, Independent State of',
            'SM' => 'San Marino, Republic of',
            'ST' => 'Sao Tome and Principe, Democratic Republic of',
            'SA' => 'Saudi Arabia, Kingdom of',
            'SN' => 'Senegal, Republic of',
            'SC' => 'Seychelles, Republic of',
            'SL' => 'Sierra Leone, Republic of',
            'SG' => 'Singapore, Republic of',
            'SK' => 'Slovakia (Slovak Republic)',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia, Somali Republic',
            'ZA' => 'South Africa, Republic of',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain, Spanish State',
            'LK' => 'Sri Lanka, Democratic Socialist Republic of',
            'SD' => 'Sudan, Democratic Republic of the',
            'SR' => 'Suriname, Republic of',
            'SJ' => 'Svalbard & Jan Mayen Islands',
            'SZ' => 'Swaziland, Kingdom of',
            'SE' => 'Sweden, Kingdom of',
            'CH' => 'Switzerland, Swiss Confederation',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan, Province of China',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic of',
            'TH' => 'Thailand, Kingdom of',
            'TG' => 'Togo, Togolese Republic',
            'TK' => 'Tokelau (Tokelau Islands)',
            'TO' => 'Tonga, Kingdom of',
            'TT' => 'Trinidad and Tobago, Republic of',
            'TN' => 'Tunisia, Republic of',
            'TR' => 'Turkey, Republic of',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'VI' => 'US Virgin Islands',
            'UG' => 'Uganda, Republic of',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom of Great Britain & N. Ireland',
            'UM' => 'United States Minor Outlying Islands',
            'US' => 'United States of America',
            'UY' => 'Uruguay, Eastern Republic of',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela, Bolivarian Republic of',
            'VN' => 'Viet Nam, Socialist Republic of',
            'WF' => 'Wallis and Futuna Islands',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'YU' => 'Yugoslavia, Socialist Federal Republic of',
            'ZM' => 'Zambia, Republic of',
            'ZW' => 'Zimbabwe'
        );

$DEFAULT[states] = array(
   'AL'=>'Alabama',
   'AK'=>'Alaska',
   'AZ'=>'Arizona',
   'AR'=>'Arkansas',
   'CA'=>'California',
   'CO'=>'Colorado',
   'CT'=>'Connecticut',
   'DE'=>'Delaware',
   'DC'=>'District of Columbia',
   'FL'=>'Florida',
   'GA'=>'Georgia',
   'HI'=>'Hawaii',
   'ID'=>'Idaho',
   'IL'=>'Illinois',
   'IN'=>'Indiana',
   'IA'=>'Iowa',
   'KS'=>'Kansas',
   'KY'=>'Kentucky',
   'LA'=>'Louisiana',
   'ME'=>'Maine',
   'MD'=>'Maryland',
   'MA'=>'Massachusetts',
   'MI'=>'Michigan',
   'MN'=>'Minnesota',
   'MS'=>'Mississippi',
   'MO'=>'Missouri',
   'MT'=>'Montana',
   'NE'=>'Nebraska',
   'NV'=>'Nevada',
   'NH'=>'New Hampshire',
   'NJ'=>'New Jersey',
   'NM'=>'New Mexico',
   'NY'=>'New York',
   'NC'=>'North Carolina',
   'ND'=>'North Dakota',
   'OH'=>'Ohio',
   'OK'=>'Oklahoma',
   'OR'=>'Oregon',
   'PA'=>'Pennsylvania',
   'RI'=>'Rhode Island',
   'SC'=>'South Carolina',
   'SD'=>'South Dakota',
   'TN'=>'Tennessee',
   'TX'=>'Texas',
   'UT'=>'Utah',
   'VT'=>'Vermont',
   'VA'=>'Virginia',
   'WA'=>'Washington',
   'WV'=>'West Virginia',
   'WI'=>'Wisconsin',
   'WY'=>'Wyoming'
);

$DEFAULT[field_types] = array(
	'TITLE' => lib_lang("Title"),
	'DESCRIPTION'=>lib_lang('Description'), 
	'URL'=>lib_lang('URL'), 
	'DISPLAY_URL'=>lib_lang('Display URL'), 
	'EMAIL'=>lib_lang('Email'), 					
	'PHONE'=>lib_lang('Phone'), 
	'FAX'=>lib_lang('Fax'), 
	'IMAGE'=>lib_lang('Image Upload'), 
	'CONTENT'=>lib_lang('Content'), 
	'CUSTOM1'=>lib_lang('Custom Field 1'), 
	'CUSTOM2'=>lib_lang('Custom Field 2'), 
	'CUSTOM3'=>lib_lang('Custom Field 3'), 
	'CUSTOM4'=>lib_lang('Custom Field 4'), 
	'CUSTOM5'=>lib_lang('Custom Field 5'), 
	'CUSTOM6'=>lib_lang('Custom Field 6')
); 

?>
