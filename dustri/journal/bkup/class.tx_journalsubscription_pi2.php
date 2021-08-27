<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Anupam Chatterjee <anupam@netzrezepte.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');

require_once(t3lib_extmgm::extPath('netzrezepte_commerce_library') . 'classes/class.tx_netzrezeptecommercelibrary_utility.php');
/**
 * Plugin 'Edit Profile' for the 'journal_subscription' extension.
 *
 * @author	Anupam Chatterjee <anupam@netzrezepte.de>
 * @package	TYPO3
 * @subpackage	tx_journalsubscription
 */
class tx_journalsubscription_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_journalsubscription_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_journalsubscription_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'journal_subscription';	// The extension key.
	var $pi_checkCHash = true;
	var $vatlayerAPIKEY; 
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->vatlayerAPIKEY 			= $this->conf['vatlayerAPIKEY']; // 

		// Obtaining product template
		$this->templateCode=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."res/subscription_template.html");
		//print_r($GLOBALS['TSFE']);
		$conf["code"] = $this->cObj->data["select_key"];
		switch ($conf["code"]){
			case 'CREATE':
				if((t3lib_div::_POST('registration_user') != '') && (t3lib_div::_POST('first_name') != '') && (t3lib_div::_POST('last_name') != '') && (t3lib_div::_POST('email') != '') && (t3lib_div::_POST('password') != '') && (t3lib_div::_POST('confirm_password') != '') && (t3lib_div::_POST('address') != '') && (t3lib_div::_POST('zip') != '') && (t3lib_div::_POST('city') != '')) {
					$content = $this->submitProfile();
				}
				else {
					$content = $this->showRegForm();
				}
				break;
			case 'EDIT':
				if(t3lib_div::_POST('update_s2') != '') $content = $this->updateProfile();
				elseif(t3lib_div::_POST('update_s1') != '') {
					$_SESSION = $_POST;
					$content = $this->confirmProfile();
				}
				else $content = $this->showProfile();
				break;
			default:
				break;
		}

		return $this->pi_wrapInBaseClass($content);
	}

	function showRegForm() {

		$templateHeader['registration_profile_main'] = $this->cObj->getSubpart($this->templateCode,'###REGISTRATION_PROFILE_MAIN###');
		// Assigning values to language markers
			$langArr = array('header_registration_profile','registered_user','personal_info','contact_details','gender_label','first_name_label','last_name_label','title_label','desired_password','confirm_password','address_label','street_label','zip_code_label','town_city_label','country_label','telephone_label','button_submit');
			for($i = 0; $i < count($langArr); $i ++) $marker['###' . strtoupper($langArr[$i]) . '###'] = $this->pi_getLL($langArr[$i]);

			// Assigning country on form
			$countriesList = '';
			$dbCountries = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'cn_short_en',
						'static_countries',
						'','','cn_short_en'
				) or die('Error-Line 86: ' . mysql_error());

			while($rCountries = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCountries)) {
				$countriesList .= '<option value="' . $rCountries['cn_short_en'] . '">' . $rCountries['cn_short_en'] . '</option>';
			}
			$marker['###COUNTRY_LIST###'] = $countriesList;

			//Assigning gender list
			$genderList .= '<option value="0">' . $this->pi_getLL('gender_0') . '</option>';
			$genderList .= '<option value="1">' . $this->pi_getLL('gender_1') . '</option>';
			$marker['###GENDER_LIST###'] = $genderList;

			//Value of fields
			$fieldArr = array('first_name', 'last_name', 'title', 'email', 'address', 'zip', 'city', 'telephone', 'fax','tx_netzrezepteaddress_ust_id_no');
			for($j = 0; $j < count($fieldArr); $j ++) $marker['###' . $fieldArr[$j] . '###'] = t3lib_div::_POST($fieldArr[$j]);

			$requireFieldArr = array('first_name', 'last_name', 'email', 'address', 'zip', 'city', 'confirm_password', 'password');
			for($k = 0; $k < count($requireFieldArr); $k ++) $marker['###' . strtoupper($requireFieldArr[$k]) . '_ERROR###'] = '';

			$nonRequireFieldArr = array('pid','tstamp', 'crdate', 'disable', 'deleted','gender', 'title', 'telephone', 'fax','tx_netzrezepteaddress_ust_id_no');
			while (list($key, $val) = each($_POST)) {
				if(!in_array($key, $nonRequireFieldArr)){
					if($val == '') {
						$marker['###' . strtoupper($key) . '_ERROR###'] = '<font color="red">' . $key .'</font>';
					}
				}
			}


		return $this->cObj->substituteMarkerArrayCached($templateHeader['registration_profile_main'], $marker);

	}

	function showProfile(){

		$uniqueUser = false;
		$submittedVat = false;
		if(t3lib_div::_POST('registration_cart') != ''){
			$_SESSION = $_POST;
			if(t3lib_div::_POST('tx_netzrezepteaddress_ust_id_no') != ''){
				if(t3lib_div::_POST('tx_netzrezepteaddress_ust_id_no') != t3lib_div::_POST('tx_netzrezepteaddress_ust_id_no_old')){
					if(!$this->validateVatId()) { 
						 $submittedVat = true;
					}
				}
				
			}
		}

		$duplicateUser = $this->duplicateUserName();
		if(t3lib_div::_POST('registration_cart') != '' && !$duplicateUser && !$submittedVat) {
			$_SESSION = $_POST;
			return $content = $this->updateUserProfile();
		}
		elseif($GLOBALS['TSFE']->fe_user->user[uid] != '') {

			// Obtaining template subparts
			$templateHeader['edit_profile_main'] = $this->cObj->getSubpart($this->templateCode,'###EDIT_PROFILE_MAIN###');
			$markerArray['###EXTENSION_PATH###'] = t3lib_extMgm::siteRelPath($this->extKey);
			$markerArray['###USERNAME_EXISTS###']='';
			$markerArray['###INVALID_VAT###']='';

			if(t3lib_div::_POST('registration_cart') != '')
				if($duplicateUser)
					$markerArray['###USERNAME_EXISTS###'] = '<p style="color:red;text-align:center">Username already exists</p>';
				
				if($submittedVat)
					$markerArray['###INVALID_VAT###'] = '<p style="color:red;text-align:center">'.$this->pi_getLL('err_invalid_vat_id').'</p>';

			$alert_messages = array('first_name_alert_message','last_name_alert_message',
										'email_alert_message', 'username_alert_message',
										'password_alert_message', 'confirm_password_alert_message',
										'same_password_alert_message', 'address_alert_message','street_alert_message',
										'city_alert_message', 'country_alert_message');

			foreach ($alert_messages as $message){
				$markerArray['###'.$message.'###']= $this->pi_getLL($message);
			}

			// Assigning values to language markers
			$langArr = array('header_edit_profile','registered_user','personal_info','contact_details','gender_label','first_name_label','last_name_label','title_label','desired_password','confirm_password','address_label',
				'street_label','zip_code_label','town_city_label','country_label','telephone_label','button_submit','other_details','enter_vat_code');
			for($i = 0; $i < count($langArr); $i ++) $markerArray['###' . strtoupper($langArr[$i]) . '###'] = $this->pi_getLL($langArr[$i]);

			// Assigning values to form fields
			$selectFields = 'password, username, gender, first_name, last_name, title, email, address,street, zip,
							city, country, country_code, telephone, fax, tx_netzrezepteaddress_ust_id_no';

			$dbUserInfo = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								$selectFields,
								'fe_users',
								'uid=' . $GLOBALS['TSFE']->fe_user->user[uid]
							) or die('Error-Line 158: ' . mysql_error());


			$numFields = mysql_num_fields($dbUserInfo);
			if($rUserInfo = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUserInfo)) {
				for($i = 0; $i < $numFields; $i ++) $markerArray['###' . mysql_field_name($dbUserInfo, $i) . '###'] = $rUserInfo[mysql_field_name($dbUserInfo, $i)];
			}

			// Assigning country on form
			$countriesList .= '<option value=""></option>';
			$dbCountries = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'cn_iso_3, cn_short_en',
								'static_countries',
								'','','cn_short_en'
			) or die('Error-Line 174: ' . mysql_error());

			while($rCountries = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCountries)) {
				$curValue = $rCountries['cn_iso_3'] .'##'. $rCountries['cn_short_en'];
				if($markerArray['###country###'] == $rCountries['cn_short_en'] || $markerArray['###country_code###'] == $rCountries['cn_iso_3']) {
					$countriesList .= '<option value="' . $curValue . '" selected="selected">' . $rCountries['cn_short_en'] . '</option>';
				}
				else $countriesList .= '<option value="' . $curValue . '">' . $rCountries['cn_short_en'] . '</option>';
			}
			$markerArray['###COUNTRY_LIST###'] = $countriesList;

			// Assigning gender
			$genderList = '<option value="3">' . $this->pi_getLL('gender_3') . '</option>';
			if($markerArray['###gender###'] == 0){
				$genderList .= '<option value="0" selected="selected">' . $this->pi_getLL('gender_0') . '</option>';
				$genderList .= '<option value="1">' . $this->pi_getLL('gender_1') . '</option>';
			}
			elseif($markerArray['###gender###'] == 1){
				$genderList .= '<option value="0">' . $this->pi_getLL('gender_0') . '</option>';
				$genderList .= '<option value="1" selected="selected">' . $this->pi_getLL('gender_1') . '</option>';
			}
			else{
				$genderList .= '<option value="0">' . $this->pi_getLL('gender_0') . '</option>';
				$genderList .= '<option value="1">' . $this->pi_getLL('gender_1') . '</option>';
			}
			$markerArray['###GENDER_LIST###'] = $genderList;
			return $this->cObj->substituteMarkerArray($templateHeader['edit_profile_main'], $markerArray);
		}
		else return '<h4>You are currently not logged in!</h4>';
	}

	function updateUserProfile(){

		$ignoreProfileList = array('registration_cart', 'password', 'confirm_password');
		$updateFieldList = "pid='39";
		$userData = array('pid'=>'39');
		$requiredFields = array('first_name','last_name','email','address','street','zip','city','country');
		$isError = false;
		for($i=0; $i<count($requiredFields); $i++){
			if(t3lib_div::_POST($requiredFields[$i]) == ''){
				$markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:0px; padding-left:5px; padding-bottom:0px;"> Please enter ' . $requiredFields[$i] . '</div>';
				$isError = true;
			}
		}
		
		while (list($key, $val) = each($_SESSION)) {
			if(!in_array($key, $ignoreProfileList)){
				if ( $key == 'country') {
					$tempval = t3lib_div::trimExplode('##',$val);
					if ($tempval[1] != '') {
						$val = $tempval[1];
						$userData['country_code'] = $tempval[0];
						$userData['country'] = $tempval[1];
					}
				}
				else {
					$userData[$key] = $val;
				}
			}
				
			if($_SESSION['password'] != '') {
				if($_SESSION['password'] == $_SESSION['confirm_password']) {
					$userData['password'] = $_SESSION['password'];
				}
				if($userData['password'] != $this->getPassword($GLOBALS['TSFE']->fe_user->user[uid])){
					$userData['pwd_rest'] = 0;
				}
			}
		}
		unset($userData['tx_netzrezepteaddress_ust_id_no_old']);
		
		$updateUserQuery = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
									'fe_users',
									'uid = '.$GLOBALS['TSFE']->fe_user->user[uid],
									$userData
						);

		if($updateUserQuery) return $content = '<h2>' . $this->pi_getLL('update_successful') . '</h2>';
	}

	function confirmProfile(){
		// Obtaining template subparts
		$templateHeader['show_edited_data'] = $this->cObj->getSubpart($this->templateCode,'###SHOW_EDITED_DATA###');

		// Assigning values to language markers
		$langArr = array('your_registration_information','registration_information','title_label','address_label','street_label','zip_code_label','town_city_label','country_label','telephone_label','button_submit');
		for($i = 0; $i < count($langArr); $i ++) $markerArray['###' . strtoupper($langArr[$i]) . '###'] = $this->pi_getLL($langArr[$i]);

		// Assigning values to field markers
		while (list($key, $val) = each($_SESSION)) {
			if($key == 'gender') $val = $this->pi_getLL('gender_' . $val);
			$markerArray['###' . $key . '###'] = $val;
		}

		return $this->cObj->substituteMarkerArrayCached($templateHeader['show_edited_data'], $markerArray);
	}

	function updateProfile() {
		$ignoreList = array('update_s1', 'journalPid', 'password', 'confirm_password');
		//$fieldList = "pid='39";
		$update_data = array();
		$update_data['pid'] = 39;
		while (list($key, $val) = each($_SESSION)) {
			if(!in_array($key, $ignoreList)) {
				if ( $key == 'country') {
					$tempval = t3lib_div::trimExplode('##',$val);
					if ($tempval[1] != '') {
						$val = $tempval[1];
						$update_data['country_code'] = $tempval[0];
						$update_data['country'] = $tempval[1];
					}
				}
				else {
					//$fieldList .= "', ". $key . "='" . $val;
					$update_data[$key] = $val;
				}
			}
		}
		if($_SESSION['password'] != '') {
			if($_SESSION['password'] == $_SESSION['confirm_password']) {
				//$fieldList .= "', password='" . $_SESSION['password'];
				$update_data['password'] = $_SESSION['password'];
			}
		}
		$updateQuery = mysql_query("UPDATE fe_users SET " . $fieldList . "' WHERE uid=" . $GLOBALS['TSFE']->fe_user->user[uid]) or die('Error-Line 141: ' . mysql_error());
		$updateResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'fe_users',
				"uid=" . $GLOBALS['TSFE']->fe_user->user[uid],
				$update_data
		) or die($GLOBALS['TYPO3_DB']->sql_error());
		if($updateResult) return '<h2>' . $this->pi_getLL('update_successful') . '</h2>';
	}

	function submitProfile(){

		$ignoreField = array('registration_user','confirm_password');
		$submitFieldsArray = array('pid'=>'39','usergroup'=>'2','deleted'=>'0',
							'disable'=>'0', 'tstamp'=>time(),'crdate'=>time());
		while (list($key, $val) = each($_POST)) {
			if(!in_array($key, $ignoreField)) {
				$submitFieldsArray[$key]=$val;
			}
		}
		$submitFieldsArray['name']			=	t3lib_div::_POST('first_name') .' ' . t3lib_div::_POST('last_name');
		$submitFieldsArray['short_name']	=	t3lib_div::_POST('first_name') .' ' . t3lib_div::_POST('last_name');

		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'fe_users',
				$submitFieldsArray
		) or die('Error-User Profile-Insert-277: ' . mysql_error());

		return $content = 'Your registration is successfully submitted';

	}

	function duplicateUserName(){
		$utility = new tx_netzrezeptecommercelibrary_utility();
		//check duplicate username
		if(t3lib_div::_POST('username') != ''){

			$username = $utility->cleanVar($_POST['username']);
			$whereClause = "uid != ".$GLOBALS['TSFE']->fe_user->user[uid]." and
							username = '$username'";

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'count(*) AS recordCount',
						'fe_users',
						$whereClause
			) or die('Error-Line 278: ' . mysql_error());

			$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if($record['recordCount'] > 0)
				return true;
			return false;
		}
		else
			return false;

	}



	/**
	 * The validateVatId method is used for validating European VAT Id's
	 *
	 *
	 * @param
	 *
	 * @return true(if valid)
	 * @return false(if unvalid)
	 */
	function validateVatId() {
		
		// Obtain the checker class
		require_once(t3lib_extMgm::siteRelPath($this->extKey) . "classes/euvatchecker_class.php");

		// Creating object of checker class
		$vat = new EUVATChecker(t3lib_extMgm::siteRelPath($this->extKey) . "res/data_vat.xml", t3lib_extMgm::siteRelPath($this->extKey) . "res/error_vat.xml", t3lib_extMgm::siteRelPath($this->extKey) . "res/ok_vat.xml", "en");
		// Initializing variables
		$vatIdSubmitted = $_SESSION['tx_netzrezepteaddress_ust_id_no'];

		if($vatIdSubmitted != '') {
			$response  = 
file_get_contents('http://www.apilayer.net/api/validate?access_key='.$this->vatlayerAPIKEY.'&vat_number='.$vatIdSubmitted.'&format=1'); 
			$vatlayer  = json_decode($response);
			if($vatlayer->valid == 1){
				return true;
			}
			if($vat->check($vatIdSubmitted)){
				return true;
			}
			else{
				if($vat->getLastError() == 0) {
					return false;
				}
				else {
					return true;
				}
			}
		}
		else {
			return true;
		}
	}

	function getPassword($uid){
		$pwd = "";
		$dbUserInfo = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								"password",
								'fe_users',
								'uid=' . $uid
							) or die('Error-Line 158: ' . mysql_error());
		if($rUserInfo = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUserInfo)) {
				$pwd = $rUserInfo['password'];
		}
		return $pwd;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/journal_subscription/pi2/class.tx_journalsubscription_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/journal_subscription/pi2/class.tx_journalsubscription_pi2.php']);
}

?>
