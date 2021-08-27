<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 netzrezepte.de <info@netzrezepte.de>
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
//error_reporting(E_ALL);
ini_set("display_errors","0");
ini_set("memory_limit","512M");
@set_time_limit(1000000);

require_once(t3lib_extMgm::siteRelPath('netzrezepte_commerce_library') . 'pi1/class.tx_netzrezeptecommercelibrary_pi1.php');
require_once(t3lib_extmgm::extPath('netzrezepte_commerce_library') . 'classes/class.tx_netzrezeptecommercelibrary_utility.php');

/**
 * Plugin 'Cart Main' for the 'netzrezepte_shop' extension.
 *
 * @author	Anupam Chatterjee <anupam@netzrezepte.de>
 * @package	TYPO3
 * @subpackage	tx_netzrezepteshop
 */
class tx_netzrezepteshop_pi2 extends tx_netzrezeptecommercelibrary_pi1 {

	var $prefixId      = 'tx_netzrezepteshop_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_netzrezepteshop_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'netzrezepte_shop';	// The extension key.
	var $pi_checkCHash = true;
	var $pid 		   = 0;
	var $customerServiceEmailAddress;
	var $orderServiceEmailAddress;
	var $dustriContacttelephoneNo = '+49-89-6138610';
	var $dustriContactEmailAddress = 'info@dustri.com';
	var $paypalURL = '';
	var $iPaymentURL = '';
	var $iPaymentTrxuserId = '';
	var $iPaymentPassword = '';
	var $sessionId;
	var $location;
	var $currencyInt;
	var $currency;

	//
	var $shipCountryStore ='';
	var $shipcostZero =array('Germany');

	// DUSE-9 :: Vat
	var $billingTempFields = array('billing_gender', 'billing_title', 'billing_last_name',
			'billing_first_name', 'billing_address','billing_street', 'billing_zip', 'billing_city',
			'billing_country','billing_country_code', 'billing_email', 'billing_telephone', 'billing_fax',
			'shipping_gender', 'shipping_title', 'shipping_last_name', 'shipping_first_name',
			'shipping_address','shipping_street', 'shipping_zip', 'shipping_city', 'shipping_country', 'shipping_country_code',
			'shipping_email', 'shipping_telephone', 'shipping_fax');
	var $vatId;
	var $discount;
	var $billingCountry;


	var $PayPalMode; // sandbox or live
	var $PayPalApiUsername; //PayPal API Username
	var $PayPalApiPassword; //Paypal API password
	var $PayPalApiSignature; //Paypal API Signature
	var $PayPalReturnURL; //Point to process.php page
	var $PayPalCancelURL; //Cancel URL if user clicks cancel
	var $baseURL;
	var $ebook = false;
	var $chapter_ebook_path = 'uploads/book_chapters/04/';
	var $dustribook_ebook_path = 'uploads/repository/04/';

	var $vatlayerAPIKEY; 
	var $country_vat;
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
		
		if ($GLOBALS['TSFE']->sys_language_uid == 1) {
			$this->LLkey = 'de';
		}
		$this->country_vat = $this->getCustomerVat($_SESSION['billing_country_code']);
		// echo "---".$_SESSION['billing_country_code']."----".$this->country_vat."--p2-----";
		
		$this->pi_loadLL();


		$utility = new tx_netzrezeptecommercelibrary_utility();	
		//echo '<pre>'; print_r($_GET);print_r($_POST); die;
		// set PayPal & iPayment settings as per BE configuration
		$this->paypalURL = $this->conf['paypalURL'];
		$this->iPaymentURL = $this->conf['iPaymentURL'];
		$this->iPaymentTrxuserId = $this->conf['iPaymentTrxuserId'];
		$this->iPaymentPassword = $this->conf['iPaymentPassword'];
		$this->customerServiceEmailAddress = $this->conf['customerServiceEmailAddress'];
		$this->orderServiceEmailAddress = $this->conf['orderServiceEmailAddress'];
		
		// Base URL
		$this->baseURL = $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'] . 'index.php';
		$this->vatlayerAPIKEY 			= $this->conf['vatlayerAPIKEY']; // 
		//echo $this->vatlayerAPIKEY;

		//
		$this->PayPalMode 			= $this->conf['PayPalMode']; // sandbox or live
		$this->PayPalApiUsername 		= $this->conf['PayPalApiUsername']; //PayPal API Username
		$this->PayPalApiPassword 		= $this->conf['PayPalApiPassword']; //Paypal API password
		$this->PayPalApiSignature 	= $this->conf['PayPalApiSignature']; //Paypal API Signature
		$this->PayPalReturnURL 		= $this->baseURL . '?id=' . $GLOBALS["TSFE"]->id . '&return=success&no_cache=1';
		if (t3lib_div::_GET('L') != '') {
			$this->PayPalReturnURL .= '&L=' . t3lib_div::_GET('L');
		}
		$this->PayPalCancelURL 		= $this->baseURL . '?id=' . $GLOBALS["TSFE"]->id . '&return=cancel&no_cache=1';
		if (t3lib_div::_GET('L') != '') {
			$this->PayPalCancelURL .= '&L=' . t3lib_div::_GET('L');
		}
		
		
		$config["pid_list"] = trim($this->cObj->stdWrap($this->conf["pid_list"],$this->conf["pid_list."]));
		$config["pid_list"] = $config["pid_list"] ? implode(t3lib_div::intExplode(",",$config["pid_list"]),",") : $GLOBALS["TSFE"]->id;
		list($this->pid) = explode(",", $config["pid_list"]);

		$this->templateCode=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."res/shopping_template.html"); //For live server
		$this->sessionId = session_id();
		
		// set location & currency
		$this->location = ($_SESSION['location'] != '') ? $_SESSION['location'] : $_COOKIE['location'];
		if ($GLOBALS['TSFE']->fe_user->user[uid] != '') {
			$this->location = ($_SESSION['location'] != '') ? $_SESSION['location'] : $_COOKIE['location'];
			//$this->currencyInt = ($_SESSION['currency'] != '') ? $_SESSION['currency'] : $_COOKIE['currency'];
			$this->currencyInt = $_COOKIE['currency'];
		} else {
			$this->currencyInt = $_COOKIE['currency'];
		}
		$this->currency = ($this->currencyInt == 'EUR') ? 'euro' : 'dollar' ;
		
		// Obtaining product template
	/*
		if (!empty($this->conf['paymentTestMode'])) {
			$this->templateCode=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."res/shopping_template_offline.html"); //For test server
			$this->customerServiceEmailAddress = $this->conf['testEmail'];
		}
	*/
		//if not in session, then call once
		if (!isset($_SESSION['discount'])) {
			if ($GLOBALS['TSFE']->fe_user->user['uid'] != '') {
				$dbUserDisc = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'tx_netzrezepteaddress_rebate, tx_netzrezepteaddress_ust_id_no',
						'fe_users', 'uid=' .$GLOBALS['TSFE']->fe_user->user['uid']);
				$resUserDisc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUserDisc);
				$this->discount = $resUserDisc['tx_netzrezepteaddress_rebate'];
				$this->vatId = $resUserDisc['tx_netzrezepteaddress_ust_id_no'];
				$_SESSION['discount'] = $this->discount;
				$_SESSION['vatId'] = $this->vatId;

			} else {
				$_SESSION['discount'] = 0;
				$_SESSION['vatId'] = '';
				$this->discount = 0;
				$this->vatId = '';
			}

		} else {
			// Reset discount anv vatid for visitors
			if ($GLOBALS['TSFE']->fe_user->user['uid'] == '') {
				$_SESSION['discount'] = 0;
				$_SESSION['vatId'] = '';
			}
			else {
				$this->discount = $_SESSION['discount'];
				$this->vatId = $_SESSION['vatId'];
			}
		}
		// DUSE-9 :: Vat
		if (isset($_SESSION['billing_country'])) {
			$this->billingCountry = $_SESSION['billing_country'];
			$this->billingCountryCode = $_SESSION['billing_country_code'];
		}

		if(t3lib_div::_GET('payment') == 'success') {
			$content = $this->showCartPage5();
		}
		elseif(t3lib_div::_GET('mode') == 'doubleoptmsg') {
			$content = $this->pi_getLL('doubleoptmsg');
		}
		elseif(t3lib_div::_GET('mode') == 'emailvarified') {
			$email = t3lib_div::_GET('email');

			$dbuser = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 
						"email='" . $email."'"
					) or die($GLOBALS['TYPO3_DB']->sql_error());

			if($userData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbuser)) {
				 $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users',
					"uid='" . $userData['uid']. "'", array('disable' => 0));
				 $content = $this->pi_getLL('emailvarified_msg');
				$this->sendConfirmationMail($userData);
				$cObj = t3lib_div::makeInstance('tslib_cObj');
				$pageid = intval($GLOBALS['TSFE']->id);
				$conf = array();
				$conf['parameter'] = $pageid; 
				$conf['additionalParams'] = '&mode=register';
				$conf['forceAbsoluteUrl'] = true;
				$linkText = $this->pi_getLL('login_link_text');
				$loginlink = $cObj->typoLink($linkText,$conf);
				$content = str_replace(array('###LOGIN_LINK###'), array($loginlink), $content);
			 }else{
			 	header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&mode=register&no_cache=1');
						exit();

			 }
			
		}
		elseif(t3lib_div::_GET('return') == 'success') {
			$content = $this->paypalExpressSuccess();
		}
		elseif(t3lib_div::_GET('return') == 'cancel') {
			$content = '<div style="color:red"><b>Transaction canceled</b></div>';
		}
		elseif(t3lib_div::_GET('articleId') != '') {
			$content = $this->articleDownLoader();
		}
		elseif(t3lib_div::_GET('unpub_articleId') != '') {
			$content = $this->unpublishArticleDownLoader();
		}
		elseif(t3lib_div::_GET('bookId') != '') {
			$content = $this->bookDownLoader();
		} // Dustri Book (ebook)
		elseif(t3lib_div::_GET('ebookId') != '' && t3lib_div::_GET('ebook') == 1) {
			$content = $this->ebookDownLoader();
		} // Dustri Book (ebook)
		elseif(t3lib_div::_POST('address_cart') != '') {
			//loop through post
			foreach ($_POST as $key=>$pVal) {
				// DUSE-9 :: Vat
				if ( $key == 'billing_country') {
						$tempval = t3lib_div::trimExplode('##',$pVal);
						if ($tempval[1] != '') {
							$pVal = $tempval[1];
							$_SESSION['billing_country_code'] = $tempval[0];
						}
						// Vat Logic country code
						if ($this->isEuropeanCountry(null, $_SESSION['billing_country_code'])) {
							$this->location = 'EU';
						} else {
							$this->location = 'OT';
						}
						//$_SESSION['location'] = $this->location;
						#setcookie('location', $this->location, time()+3600000, '/');
						setcookie('location', $this->location, 0, '/');
				}elseif ( $key == 'shipping_country') {
						$tempval = t3lib_div::trimExplode('##',$pVal);
						if ($tempval[1] != '') {
							$pVal = $tempval[1];
							$_SESSION['shipping_country_code'] = $tempval[0];
						}
				}
				$_SESSION["$key"] = $pVal;
			}
			
			$content = $this->showCartPage4();
		} elseif(t3lib_div::_POST('registration_cart') != '')  {
			$requiredFields = array('username','first_name','last_name','email','password','confirm_password','address','zip','city', 'country');
			for($i=0; $i<count($requiredFields); $i++){
				if(t3lib_div::_POST($requiredFields[$i]) == ''){
					$error .= $requiredFields[$i];
				}
				else {
					if(t3lib_div::_POST('email') != ''){
						$dbEmailTest = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
										'uid',
										'fe_users',
										"email='" . t3lib_div::_POST('email') . "' AND deleted=0 AND disable=0"
									) or die('Error-Email_check-Fetch-Line 84 : ' . $GLOBALS['TYPO3_DB']->sql_error());
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($dbEmailTest) > 0) {
							$error.= 'email';
						}
					}
				}
			}

			if ($error == ''){
				$captchaCode = $_SESSION['tx_captcha_string'];

				foreach ($_POST as $key=>$pVal) {
					$_SESSION["$key"] = $utility->cleanVar($pVal);
				}

				$_SESSION['tx_captcha_string'] = $captchaCode;
				$content = $this->showCartPage3();
			} else {
				$content = $this->showCartPage2();

			}
		} elseif(t3lib_div::_POST('preLogin') != ''){
			$content = $this->showCartPage3();

		} elseif(t3lib_div::_POST('login_cart') != '' && t3lib_div::_POST('preLogin') == ''){
			$captchaCode = $_SESSION['tx_captcha_string'];
			foreach ($_POST as $key=>$pVal) {
				$_SESSION["$key"] = $utility->cleanVar($pVal);
			}
			$_SESSION['tx_captcha_string'] = $captchaCode;
			$content = $this->showCartPage3();

		} elseif ((t3lib_div::_POST('submit_1') != '') || (t3lib_div::_GET('mode') == 'register') ||
				(t3lib_div::_GET('login') == 'fail') ||
				(t3lib_div::_GET('regMode') != '') ||
				(t3lib_div::_GET('forgotMode') != '') ||
				(t3lib_div::_POST('forgot_pswd') != '') ||
				(t3lib_div::_GET('backMode') != '')) {
				if ($GLOBALS['TSFE']->fe_user->user['uid'] == '') {
					$content = $this->showCartPage2();
				} else{
					// For removing text block for logged in users based on mail sent on 21.10.2015
					$content = $this->showCartPage3();
				}
		} elseif (t3lib_div::_POST('submit_order') != '') {
			$content = $this->manualPayment();
		}elseif (t3lib_div::_POST('express_checkout') != '') {
			$content = $this->processSetExpressCheckout();
		}
		else {
			$content = $this->showCartPage1();
		}
		return $content;
	}
	
	
	function paypalExpressArrayMap($postArr){
		$templateArray = array(
				'cruser_id' 			=>	'',		//user id
				'purchase_date'		=>	'payment_date',
				'first_name'			=>	'first_name',
				'last_name'			=>	'last_name',
				'trxuser_id'			=>	'payer_id',
				'addr_name'			=>	'address_name',
				'addr_street'		=>	'',
				'addr_city'			=>	'',
				'addr_zip'			=>	'',
				'addr_email'			=>	'payer_email',
				'trx_amount'			=>	'payment_gross',
				'trx_currency'		=>	'mc_currency',
				'trx_paymentdata_country'=> 'residence_country',
				'trx_paymenttyp'		=>	'paypal',		//paypal
				'trx_typ'			=>	'txn_type',
				'ret_transdate'		=>	'',
				'ret_transtime'		=>	'',
				'ret_errorcode'		=>	'',
				'ret_authcode'		=>	'',
				'ret_ip'				=>	'',
				'trx_paymentmethod'	=>	'paypal',
				'ret_booknr'			=>	'txn_id',
				'ret_trx_number'		=>	'txn_id',
				'trx_paymentmethod'	=>	'paypal',
				'ret_status'			=>	'payment_status',
				'ses_userid'			=>	'',
				'purchase_sess_id'	=>	''
		);
		$mappedArray = array();
		foreach($templateArray as $key => $value){
			if ($key == 'purchase_date') {
				$timpestamp = strtotime(urldecode($postArr['TIMESTAMP']));
				$mappedArray[$key] = $timpestamp;
					
			} 
			elseif ($key == 'first_name') {
				$mappedArray[$key] = urldecode($postArr['FIRSTNAME']);
				$_POST[$key] = urldecode($postArr['FIRSTNAME']);
			}
			elseif ($key == 'last_name') {
				$mappedArray[$key] = urldecode($postArr['LASTNAME']);
				$_POST[$key] = urldecode($postArr['LASTNAME']);
			}
			elseif ($key == 'trx_currency') {
				$mappedArray[$key] = urldecode($postArr['PAYMENTREQUEST_0_CURRENCYCODE']);
				$_POST[$key] = urldecode($postArr['PAYMENTREQUEST_0_CURRENCYCODE']);
			}
			elseif ($key == 'addr_name') {
				$mappedArray[$key] = urldecode($postArr['FIRSTNAME']).' '.urldecode($postArr['LASTNAME']);
				$_POST[$key] = urldecode($postArr['FIRSTNAME']).' '.urldecode($postArr['LASTNAME']);
			}
			elseif ($key == 'trx_typ') {
				$mappedArray[$key] = "web_accept";
				$_POST[$key] = "web_accept";
			}
			elseif ($key == 'addr_email') {
				$mappedArray[$key] = urldecode($postArr['EMAIL']);
				$_POST[$key] = urldecode($postArr['EMAIL']);
			} 
			elseif ($key == 'trxuser_id') {
				$mappedArray[$key] = urldecode($postArr['PAYERID']);
				$_POST[$key] = urldecode($postArr['PAYERID']);
			}
			elseif ($key == 'trx_amount') {
				$mappedArray[$key] = urldecode($postArr['AMT']);
			} 
			elseif ($key == 'cruser_id') {
				$customArray = explode(',',$_SESSION['custom'] );
				$mappedArray[$key] = $customArray['1'];
				$_POST[$key]=$customArray['1'];
			}
			elseif ($key == 'ses_userid') {
				$customArray = explode(',',$_SESSION['custom'] );
				$mappedArray[$key] = $customArray['1'];
				$_POST[$key]=$customArray['1'];
					
			} elseif ($key == 'purchase_sess_id') {
				$customArray = explode(',',$_SESSION['custom'] );
				$mappedArray[$key] = $customArray['0'];
				$_POST['purchase_sess_id']=$customArray['0'];
			} 
			elseif ($key == 'trx_paymenttyp') {
				$mappedArray[$key] = 'paypal';
			}
			elseif ($key == 'trx_paymentmethod') {
				$mappedArray[$key] = 'instant';
			}
			elseif ($key == 'ret_booknr') {
				$mappedArray[$key] = urldecode($postArr['PAYMENTREQUEST_0_TRANSACTIONID']);
				$_POST[$key]=urldecode($postArr['PAYMENTREQUEST_0_TRANSACTIONID']);
					
			}elseif ($key == 'ret_trx_number') {
				$mappedArray[$key] = urldecode($postArr['PAYMENTREQUEST_0_TRANSACTIONID']);
				$_POST[$key]=urldecode($postArr['PAYMENTREQUEST_0_TRANSACTIONID']);
					
			} elseif ($key == 'ret_status') {
				$mappedArray[$key] = 'SUCCESS';
			} elseif ($key == 'ret_transdate') {
				$mappedArray[$key] 	= 	date('d.m.y',time());
					
			} elseif ($key == 'ret_transtime') {
				$mappedArray[$key] 	= 	date('H:i:s',time());
					
			} 
			elseif ($key == 'addr_street') {
				$mappedArray[$key] 	= 	urldecode($postArr['SHIPTOSTREET']);
			}
			elseif ($key == 'addr_city') {
				$mappedArray[$key] 	= 	urldecode($postArr['SHIPTOCITY']);
			}
			elseif ($key == 'addr_zip') {
				$mappedArray[$key] 	= 	urldecode($postArr['SHIPTOZIP']);
			}
			elseif ($key == 'trx_paymentdata_country') {
				$mappedArray[$key] 	= 	urldecode($postArr['SHIPTOCOUNTRYCODE']);
				$_POST[$key]=urldecode($postArr['SHIPTOCOUNTRYCODE']);
			}
			else {
				// if $value is empty it means cc & paypal array index is same then retrieve data using its key
				$mappedArray[$key] = empty($value) ? $postArr[$key] : $postArr[$value];
			}
		}
	
		return $mappedArray;
	}
	function insertExpressLog($postArr) {
		$arrBasicInsertFields = array();
		$dbGetInsertFields = mysql_query("SHOW COLUMNS FROM tx_netzrezepteshop_log") or die('Error-Obtaining Log Fields: ' . mysql_error());
		if (mysql_num_rows($dbGetInsertFields) > 0) {
			while ($rGetInsertFields = mysql_fetch_assoc($dbGetInsertFields)) {
				array_push($arrBasicInsertFields, $rGetInsertFields['Field']);
			}
		}
	
		$billingMainArr = array('x', 'y');
		$billingMainFields = 'pid, session_id, tstamp';
		$billingMainValues = "0,'" . session_id() . "', '" . time();
		while (list($key, $val) = each($postArr)) {
			if((!in_array($key, $billingMainArr)) && (in_array($key, $arrBasicInsertFields))) {
				$billingMainFields .= ', ' . $key;
				$billingMainValues .= "', '" . mysql_real_escape_string($val);
			}
		}
		$insertQuery = "INSERT INTO tx_netzrezepteshop_log(" . $billingMainFields . ") VALUES(" . $billingMainValues . "')";
		mysql_query($insertQuery) or die('Error-Log-Write: ' . mysql_error());
	}
	function insertExpressPurchaseDetails($postArr) {
		$arrBasicInsertFields = array();
		$dbGetInsertFields = mysql_query("SHOW COLUMNS FROM tx_netzrezepteshop_basic") or die('Error-Obtaining Basic Fields: ' . mysql_error());
		if (mysql_num_rows($dbGetInsertFields) > 0) {
			while ($rGetInsertFields = mysql_fetch_assoc($dbGetInsertFields)) {
				array_push($arrBasicInsertFields, $rGetInsertFields['Field']);
			}
		}
		
		$checkSql = "SELECT uid FROM tx_netzrezepteshop_basic WHERE ret_booknr='" . t3lib_div::_POST('ret_booknr') . "'";
		$dbCheck = mysql_query($checkSql) or die('Error-Check-Fetch: ' . mysql_error());
	
		if(mysql_num_rows($dbCheck) == 0){
			$insertSql = "INSERT INTO tx_netzrezepteshop_basic SET
											pid = '52',
											tstamp = '".time()."',
											crdate = '".time()."',
											cruser_id =  '".t3lib_div::_POST('ses_userid')."',
											first_name = '".mysql_real_escape_string($postArr['first_name'])."',
											last_name = '".mysql_real_escape_string($postArr['last_name'])."',
											purchase_date = '".time()."',
											trxuser_id = '".$postArr['trxuser_id']."',
											addr_name = '".mysql_real_escape_string($postArr['first_name'])." ".mysql_real_escape_string($postArr['last_name'])."',
											addr_street = '".mysql_real_escape_string($postArr['addr_street'])."',
											addr_city = '".mysql_real_escape_string($postArr['addr_city'])."',
											addr_zip = '".mysql_real_escape_string($postArr['addr_zip'])."',
											addr_email = '".mysql_real_escape_string($postArr['addr_email'])."',
											trx_amount = '".$postArr['trx_amount']."',
											trx_currency = '".$postArr['trx_currency']."',
											trx_paymenttyp = '" . $postArr['trx_paymenttyp'] ."',
											trx_typ = '".$postArr['addr_email']."',
											ret_transdate = '".date('d.m.y',time())."',
											ret_transtime = '".date('H:i:s',time())."',
											ret_errorcode = '".$postArr['ret_errorcode']."',
											ret_authcode = '".$postArr['ret_authcode']."',
											ret_ip = '".$postArr['ret_ip']."',
											ret_booknr = '".$postArr['ret_booknr']."',
											ret_trx_number = '".$postArr['ret_trx_number']."',
											redirect_needed = '".$postArr['redirect_needed']."',
											trx_paymentmethod = '".$postArr['trx_paymentmethod']."',
											trx_paymentdata_country = '".$postArr['trx_paymentdata_country']."',
											trx_remoteip_country = '".$postArr['trx_remoteip_country']."',
											addr_check_result = '".$postArr['addr_check_result']."',
											ret_status = '".$postArr['ret_status']."',
											credit_note = '".$postArr['credit_note']."',
											count_reminder = '".$postArr['count_reminder']."',
											payment_cond= '".$postArr['payment_cond']."'
			";
			
			$_SESSION['express']['ret_booknr']=$postArr['ret_booknr'];
			$_SESSION['express']['trx_currency']=$postArr['trx_currency'];
			$_SESSION['express']['trx_paymentdata_country']=$postArr['trx_paymentdata_country'];
			$_SESSION['express']['trx_paymenttyp']=$postArr['trx_paymenttyp'];
			
			mysql_query($insertSql) or die('Error-Billing_info_main-Insert: ' . mysql_error());
			$basicId = mysql_insert_id();
		}
		else $basicId = 0;
		
		if($basicId > 0) {
			$insertFields = '';
			$insertValues = '';
			$dbBillingInfo = mysql_query("SELECT * FROM tx_netzrezepteshop_billing_temp WHERE session_id='" . t3lib_div::_POST('purchase_sess_id') . "'") or die('Error-Billing_temp-Fetch: ' . mysql_error());
			$count = mysql_num_fields($dbBillingInfo);
			$forbiddenFields = array('uid');
			if($rBillingInfo = mysql_fetch_array($dbBillingInfo)){
				// For fixing record set as deleted from any other activity in ERP
				$rBillingInfo['deleted'] = 0;
				$rBillingInfo['hidden'] = 0;
				for($i = 0; $i < $count; $i ++){
					if(mysql_field_name($dbBillingInfo, $i) == 'session_id') {
						$fieldName = 'shopping_id';
						$fieldValue = $basicId;
					}
					else {
						$fieldName = mysql_field_name($dbBillingInfo, $i);
						$fieldValue = mysql_real_escape_string($rBillingInfo[mysql_field_name($dbBillingInfo, $i)]);
					}
					if($i > 1) {
						$insertFields .= ',';
						$insertValues .= "', '";
					}
					if(!in_array($fieldName, $forbiddenFields)) {
						$insertFields .= $fieldName;
						$insertValues .= mysql_real_escape_string($fieldValue);
					}
				}
			}
			mysql_query("INSERT INTO tx_netzrezepteshop_billing(" . $insertFields . ") VALUES('" . $insertValues . "')") or die('Error-Data_transfer_billing-Insert: ' . mysql_error());
			mysql_query("UPDATE tx_netzrezepteshop_billing_temp SET deleted=1, hidden=1 WHERE session_id='" . t3lib_div::_POST('purchase_sess_id') . "'");
				
			/* Processing Temporary Shopping Cart - Begin */
			$dbCartTemp = mysql_query("SELECT c.*,p.productcode FROM tx_netzrezepteshop_temp c, tx_datamasters_products p WHERE c.deleted=0 AND c.hidden=0 AND c.iserp=0 AND p.deleted=0 AND p.hidden=0 AND p.uid=c.product_id AND c.session_id='" . t3lib_div::_POST('purchase_sess_id') . "'");
	
			while($rCartTemp = mysql_fetch_assoc($dbCartTemp)) {
	
				$insertSql = "INSERT INTO tx_netzrezepteshop_details SET
				pid 						= " . $rCartTemp['pid'] . ",
				tstamp 					= ". time() . ",
				crdate 					= " . time() . ",
				info_id 					= '" . $basicId . "',
				cruser_id 				= " . t3lib_div::_POST('ses_userid') . ",
				product_id 			 	= '" . $rCartTemp['product_id'] . "',
				product_title			= '" . mysql_real_escape_string($rCartTemp['product_title']) . "',
				product_type 			= '" . $rCartTemp['product_type'] . "',
				quantity 				= " . $rCartTemp['quantity'] . ",
				unit_price 				= '" . $rCartTemp['unit_price'] . "',
				shipping_cost 			= '" . $rCartTemp['shipping_cost'] . "',
				product_type_title 		= '" . mysql_real_escape_string($rCartTemp['product_type_title']) . "',
				currency 				= '" . $rCartTemp['currency'] . "',
				vat 						= '" . $rCartTemp['vat'] . "',
				discount 				= '" . $rCartTemp['discount'] . "',
				subscription_type 		= '" . $rCartTemp['subscription_type'] . "',
				published 				= '" . $rCartTemp['published'] . "',
				alt_lang 				= '" . 	$rCartTemp['alt_lang'] . "',
				order_no 				= '" . 	mysql_real_escape_string($rCartTemp['order_no']) . "',
				order_date 				= '" . $rCartTemp['order_date'] . "',
				delivery_address 		= '" . mysql_real_escape_string($rCartTemp['delivery_address']) . "',
				bulk_shipping 			= '" . $rCartTemp['bulk_shipping'] . "',
				payment_cond 			= '" . mysql_real_escape_string($rCartTemp['payment_cond']) . "',
				productcode 				= '" . 	$rCartTemp['productcode'] . "',
			    unit_price_for_partly_online_item_for_private = '" . $rCartTemp['unit_price_for_partly_online_item_for_private'] . "',
				vat_online_subscription = '" . $rCartTemp['vat_online_subscription'] . "',
				vat_per_for_print_subs = '" . $rCartTemp['vat_per_for_print_subs'] . "',
				vat_per_for_online_subs = '" . $rCartTemp['vat_per_for_online_subs'] . "',
				ebook 					= '" . $rCartTemp['ebook'] . "'";
				mysql_query($insertSql) or die(mysql_error());
	
				// Updating inventory ALSO Dustri Book (ebook)
				if ($rCartTemp['product_type'] == 24 || $rCartTemp['product_type'] == 02 || ($rCartTemp['product_type'] == 4 && empty($rCartTemp['ebook'])) || $rCartTemp['product_type'] == 5 || $rCartTemp['product_type'] == 6){
					$this->updateExpressStockOnSales($rCartTemp['product_id'],$rCartTemp['product_type'] , $rCartTemp['quantity']);
				}
			}
			mysql_query("UPDATE tx_netzrezepteshop_temp SET deleted=1, hidden=1 WHERE deleted=0 AND hidden=0 AND session_id='" . t3lib_div::_POST('purchase_sess_id') . "'") or die('Error-Shop_temp-Delete: ' . mysql_error());
			/* Processing Temporary Shopping Cart - End */
		}
		/* Transferring Temporary Data - End */
	}
	function updateExpressStockOnSales($productId, $productType, $quantity = 1) {
		//Product type: 2(Single Copy), 4(Dustri Book), 5(Book), 6(Pabst Book),  24(Journal Volume (Print))
		$productTypesWithStock = array(24,2,4,5,6,9);
		if(in_array($productType,$productTypesWithStock)){
			mysql_query("UPDATE tx_datamasters_products SET stock=stock-$quantity WHERE uid =".$productId) or die(mysql_error());
		}
	}
	function paypalExpressSuccess()
	{
		$GrandTotal = $_SESSION['GrandTotal'];
		$PayPalCurrencyCode =  $_SESSION['PayPalCurrencyCode'];
		$token = t3lib_div::_GET('token');
		$payer_id = t3lib_div::_GET('PayerID');
		$padata = 	'&TOKEN='.urlencode($token).
		'&PAYERID='.urlencode($payer_id).
		'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
		'&PAYMENTREQUEST_0_AMT='.urlencode($GrandTotal).
		'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode);
		$httpParsedResponseAr =  $this->PPHttpPost('DoExpressCheckoutPayment', $padata, $this->PayPalApiUsername, $this->PayPalApiPassword, $this->PayPalApiSignature, $this->PayPalMode);
		
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
		{
			$padata = 	'&TOKEN='.urlencode($token);
			$httpParsedResponseAr =  $this->PPHttpPost('GetExpressCheckoutDetails', $padata, $this->PayPalApiUsername, $this->PayPalApiPassword, $this->PayPalApiSignature, $this->PayPalMode);
			if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
			{
				$mappedArray = $this->paypalExpressArrayMap($httpParsedResponseAr);
				$this->insertExpressLog($mappedArray);
				unset($mappedArray['purchase_date']);
				unset($mappedArray['cruser_id']);
				$this->insertExpressPurchaseDetails($mappedArray);
				$paypalSucessURL 		= $this->baseURL . '?id=' . $GLOBALS["TSFE"]->id . '&payment=success&no_cache=1';
				if (t3lib_div::_GET('L') != '') {
					$paypalSucessURL .= '&L=' . t3lib_div::_GET('L');
				}
				header('Location: '.$paypalSucessURL);
				die();
			}
			else
			{
					return '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			}
		}
		else{
				return '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
		}
	}
	function processSetExpressCheckout() {
		//Parameters for SetExpressCheckout, which will be sent to PayPal
		$paypalmode = ($this->PayPalMode=='sandbox') ? '.sandbox' : '';
		$ItemName = t3lib_div::_POST('item_name');
		$ItemNumber = t3lib_div::_POST('item_number');
		$ItemQty = t3lib_div::_POST('item_number');
		$ItemPrice = t3lib_div::_POST('amount');
		$GrandTotal = t3lib_div::_POST('amount');
		$PayPalCurrencyCode = t3lib_div::_POST('currency_code');
		$custom = t3lib_div::_POST('custom');
		
		$addr_name = t3lib_div::_POST('addr_name');
		$addr_street = t3lib_div::_POST('addr_street');
		$addr_city = t3lib_div::_POST('addr_city');
		$addr_zip = t3lib_div::_POST('addr_zip');
		$addr_country = t3lib_div::_POST('addr_country');
		
		$padata = 	'&METHOD=SetExpressCheckout'.
		'&RETURNURL='.urlencode($this->PayPalReturnURL ).
		'&CANCELURL='.urlencode($this->PayPalCancelURL).
		'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
		'&NOSHIPPING=1'.
		'&PAYMENTREQUEST_0_AMT='.urlencode($GrandTotal).
		'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode).
		'&PAYMENTREQUEST_0_SHIPTONAME='.urlencode($addr_name).
		'&PAYMENTREQUEST_0_SHIPTOSTREET='.urlencode($addr_street).
		'&PAYMENTREQUEST_0_SHIPTOCITY='.urlencode($addr_city).
		'&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE='.urlencode($addr_country).
		'&PAYMENTREQUEST_0_SHIPTOZIP='.urlencode($addr_zip).
		'&ALLOWNOTE=1';
		
		
		$_SESSION['GrandTotal'] 			=  $GrandTotal;
		$_SESSION['custom'] 				=  $custom;
		$_SESSION['PayPalCurrencyCode'] 	=  $PayPalCurrencyCode;
		
		$custom_data = explode(",",$custom);
		
		$dbCart = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_netzrezepteshop_temp',
				"deleted=0 AND hidden=0 AND session_id='" . $custom_data[0]."'",'','uid'
		) or die($GLOBALS['TYPO3_DB']->sql_error());
		$showShipping = false;
		$prdCount = 0;
		while ($rCart = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCart)) {
			$productPrice 					= $rCart['quantity'] * $rCart['unit_price'];
			$shippingCost 					= $rCart['quantity'] * $rCart['shipping_cost'];
			$discountAmount 				= $productPrice * $rCart['discount'] / 100;
			
			$padata.='&L_PAYMENTREQUEST_0_NAME'.$prdCount.'='.urlencode(stripslashes(str_replace("<br />", "", $rCart['product_title'])));
			$padata.='&L_PAYMENTREQUEST_0_NUMBER'.$prdCount.'='.stripslashes($rCart['product_id']);
			$padata.='&L_PAYMENTREQUEST_0_DESC'.$prdCount.'='.urlencode(stripslashes(str_replace("<br />", "", $rCart['product_type_title'])));
			$padata.='&L_PAYMENTREQUEST_0_AMT'.$prdCount.'='.number_format($rCart['unit_price'], 2);
			$padata.='&L_PAYMENTREQUEST_0_QTY'.$prdCount.'='.$rCart['quantity'];
			
			$shippingVat 				 = ($shippingCost *  $rCart['vat'])/(100 + $rCart['vat']);
			$totalShippingVat 			+= $shippingVat;
			$totalShippingCost 			+=  $shippingCost;
		
			$prdCount ++;
			if($rCart['product_type']!=21 && $totalShippingCost>0)
			{
				$showShipping = true;
			}
		}
		if($showShipping)
		{
			$padata.='&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($totalShippingCost);
			$padata.='&PAYMENTREQUEST_0_ITEMAMT='.urlencode($GrandTotal-$totalShippingCost);
		}
		//echo $padata;
		//die();
		$httpParsedResponseAr = $this->PPHttpPost('SetExpressCheckout', $padata, $this->PayPalApiUsername, $this->PayPalApiPassword, $this->PayPalApiSignature, $this->PayPalMode);
		
		//Respond according to message we receive from Paypal
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
		{
			//Redirect user to PayPal store with Token received.
			$paypalurl ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpParsedResponseAr["TOKEN"].'';
			header('Location: '.$paypalurl);
			die();
		
		}else{
			return  '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
		}
	}
	
	private function PPHttpPost($methodName_, $nvpStr_, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode) {
		// Set up your API credentials, PayPal end point, and API version.
		$API_UserName = urlencode($PayPalApiUsername);
		$API_Password = urlencode($PayPalApiPassword);
		$API_Signature = urlencode($PayPalApiSignature);
			
		$paypalmode = ($PayPalMode=='sandbox') ? '.sandbox' : '';
	
		$API_Endpoint = "https://api-3t".$paypalmode.".paypal.com/nvp";
		$version = urlencode('109.0');
	
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
	
		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
	
		// Get response from the server.
		$httpResponse = curl_exec($ch);
	
		if(!$httpResponse) {
			exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}
	
		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);
	
		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}
	
		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
			//exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
			$httpParsedResponseAr["ACK"]='FAILD';
			$httpParsedResponseAr["L_LONGMESSAGE0"]="Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.";
		}
		return $httpParsedResponseAr;
	}
	
	
	
	/**
	 * The showCartPage1 method is used for add to cart products
	 *
	 * @param is mot used
	 *
	 * @return	The shopping basket is displayed
	 */

	function showCartPage1() {
		$templateHeader['cart_main'] = $this->cObj->getSubpart($this->templateCode,'###CART_MAIN###');
		$templateHeader['cart_main_inner'] = $this->cObj->getSubpart($this->templateCode,'###CART_MAIN_INNER###');

		// Obtaining global vars
		$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['article_list']);
		if($_extConfig != '') {
			$addCartPageId = $_extConfig['addToCartPage'];
		}

		
		if (!isset($this->billingCountry)) {
			// DUSE-9 :: Vat
			$dbBill_Country = $GLOBALS['TYPO3_DB']->exec_SELECTquery('billing_country,billing_country_code',
					'tx_netzrezepteshop_billing_temp', "session_id='" . $this->sessionId . "' AND deleted=0");
		
			if ($rBill_Country = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbBill_Country)) {
				$this->billingCountry = $rBill_Country['billing_country'];
				$_SESSION['billing_country'] = $rBill_Country['billing_country'];
				$this->billingCountryCode = $rBill_Country['billing_country_code'];
				$_SESSION['billing_country_code'] = $rBill_Country['billing_country_code'];
			}
		}
		
		$currencyInitArr = $this->currencyInit($this->billingCountryCode, $this->vatId);
		
		
		$prdCount = 1;
		$prdList = '';
		$prdTotal = 0;

		//Check whether any record present in temp table
		$checkRec = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_netzrezepteshop_temp',
				"deleted=0 AND hidden=0 AND session_id='" . $this->sessionId . "'");
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($checkRec) == 0) {
			//redirect to home
			header('location: index.php');
		} else {
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_netzrezepteshop_temp',
					"session_id='" . $this->sessionId . "'", array('discount' => $this->discount));

			$dbCart = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_netzrezepteshop_temp', 
						"deleted=0 AND hidden=0 AND session_id='" . $this->sessionId."'",'','uid'
					) or die($GLOBALS['TYPO3_DB']->sql_error());

			$showShipping = false;
			while ($rCart = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCart)) {
			//	$productPrice 					= number_format(($rCart['quantity'] * $rCart['unit_price']), 2, '.', '');
				$productPrice 					= $rCart['quantity'] * $rCart['unit_price'];
			//	$shippingCost 					= number_format(($rCart['quantity'] * $rCart['shipping_cost']), 2);
				$shippingCost 					= $rCart['quantity'] * $rCart['shipping_cost'];
			//	$discountAmount 				= number_format(($productPrice * $rCart['discount'] / 100),2);
				$discountAmount 				= $productPrice * $rCart['discount'] / 100;
				
				
				
				// DUSE-9 Vat Logic
				$digitalProduct = $this->isDigitalProduct($rCart['product_type']);
				// DUSE-9 :: Vat logic, set var precentage for digital product based on country
				$vatPercentage = $rCart['vat'];
				if ($digitalProduct) {
					if (empty($currencyInitArr[5])) { // if EU country
						$vatPercentage = $currencyInitArr[4];
					} else {
						// if non EU European country
						$vatPercentage = 0;
					}
				} else {
					// Vat calculation flag reset for non digital (print) products and leave the vat percentage as it is
					$currencyInitArr[2] =	$this->priceCalculationFlagForPrint($this->billingCountryCode, $this->vatId);
				}
				
				$marksInner['###SERIAL_NO###'] 	= $prdCount;
				$marksInner['###TITLE###'] 		= stripslashes($rCart['product_title']);
				$marksInner['###TYPE###'] 		= stripslashes($rCart['product_type_title']);
				$marksInner['###QUANTITY###'] 	= $rCart['quantity'];
				$marksInner['###PRICE###'] 		= number_format($productPrice, 2);
				$marksInner['###PRODUCT_ID###'] = $rCart['product_id'];
				$marksInner['###CART_ID###'] = $rCart['uid'];
				
				if (t3lib_div::_GET('L') != '') {
					$marksInner['###PRODUCT_ID###'] .= '&L=' . t3lib_div::_GET('L');
				}
				$marksInner['###ADD_CART_ID###'] = $addCartPageId;
				$subscriptionType = $rCart['subscription_type'];
				
				  if ($rCart['product_type'] == 34 && ($subscriptionType == 1 || $subscriptionType == 2)) {
					$this->cartHasDifferentVatPercentage = true;
					if ($subscriptionType == 1) {
						$productPriceForPrintSubs 	= $productPrice - $rCart['unit_price_for_partly_online_item_for_private'];
						$productPriceForOnlineSubs 	= $rCart['unit_price_for_partly_online_item_for_private'];

						$vatPriceDiscount = $this->getVatPriceForPrintAndOnlineSubscription($subscriptionType, $productPrice, ($rCart['unit_price_for_partly_online_item_for_private'] * $rCart['quantity']), $rCart['vat'], $rCart['vat_online_subscription'], $shippingCost,$rCart['discount']);
					}
					else if ($subscriptionType == 2) {
						$productPriceForPrintSubs 	= ($productPrice * $this->printSubscriptionPricePercentage)/100;
						$productPriceForOnlineSubs 	= $productPrice - $productPriceForPrintSubs;

						$vatPriceDiscount = $this->getVatPriceForPrintAndOnlineSubscription( $subscriptionType, $productPrice, $productPriceForOnlineSubs, $rCart['vat'], $rCart['vat_online_subscription'], $shippingCost, $rCart['discount']);
						$vatAmount = $vatPriceDiscount['vatTotalOnProduct'] + $vatPriceDiscount['vatforShipping'];
					}
					$vatAmount 		= $vatPriceDiscount['vatTotalOnProduct']; // + $vatPriceDiscount['vatforShipping'];
					$shippingVat 	= $vatPriceDiscount['vatforShipping'];
					$discountCost 	= $vatPriceDiscount['discountCost'];
				} else { 

					if ($vatPercentage > 0) {
						$shippingPercentage = $vatPercentage;

						if ($this->cartHasDifferentVatPercentage) {
							$shippingPercentage = $this->commonVatPercentageForShipping;
						}
						else {
							$shippingPercentage = $vatPercentage;
						}

						$vatAmount = $this->getVatPrices($productPrice, $currencyInitArr[2], $vatPercentage, $currencyInitArr[0], $discountAmount);
						$shippingVat 	= (($shippingCost / (100 + $shippingPercentage)) * $shippingPercentage);
					}
					else {
						$vatAmount = $this->getVatPrices($productPrice, $currencyInitArr[2], $vatPercentage, $currencyInitArr[0], $discountAmount);
						$shippingPercentage	= '';
						$shippingVat	= 0;
					}
					//$vatAmount = ((($productPrice + $productShipping) / (100 + $rCart['vat'])) * $rCart['vat']);
					//$shippingVat  = ($shippingCost *  $rCart['vat'])/(100 + $rCart['vat']);	
				
				 }
				
				//$shippingVat 				 = ($shippingCost *  $rCart['vat'])/(100 + $rCart['vat']);
				$totalShippingVat 			+= $shippingVat;
				$totalShippingCost 			+=  $shippingCost;
				
				$marksInner['###VAT###'] = number_format($vatAmount, 2); //number_format($this->getVatPrices($productPrice, $currencyInitArr[2], $vatPercentage, $currencyInitArr[0], $discountAmount), 2);
				$marksInner['###DISCOUNT###'] = number_format($discountAmount, 2);
				$marksInner['###TOTAL###'] = number_format(($productPrice - $discountAmount), 2);
				// Marker language for cart main inner for mobile view
				$marksInner['###HEADER_SHOPPING_DETAILS###'] = $this->pi_getLL('shopping_details');
				$marksInner['###HEADER_NO###'] = $this->pi_getLL('no');
				$marksInner['###HEADER_TITLE###'] = $this->pi_getLL('title');
				$marksInner['###HEADER_TYPE###'] = $this->pi_getLL('type');
				$marksInner['###HEADER_QUANTITY###'] = $this->pi_getLL('quantity');
				$marksInner['###HEADER_PRICE###'] = $this->pi_getLL('price');
				$marksInner['###HEADER_SHIPPING###'] = $this->pi_getLL('shipping');
				
				$marksInner['###HEADER_VAT###'] = $this->pi_getLL('vat_header');
				$marksInner['###HEADER_DISCOUNT###'] = $this->pi_getLL('discount_header');
				
				$marksInner['###HEADER_TOTAL###'] = $this->pi_getLL('total');
				$marksInner['###HEADER_TOTAL###'] = $this->pi_getLL('total');
				$marksInner['###BUTTON_CONTINUE###'] = $this->pi_getLL('button_continue');
				
				$prdTotal += ($productPrice - $discountAmount);
				$prdList .= $this->cObj->substituteMarkerArrayCached($templateHeader['cart_main_inner'], $marksInner);
				$prdCount ++;
				
				if($rCart['product_type']!=21 && $totalShippingCost>0)
				{
					$showShipping = true;
				}
				
			}

			/**********************shipping-23.12.2008********************/
			if($showShipping)
			{
				$markerArray['###SHIPPING_SHOW###']="";
			}
			else
			{
				$markerArray['###SHIPPING_SHOW###']="none";
			}
			$markerArray['###SHIPPING###'] = $this->pi_getLL('cart_shipping_label');
			$markerArray['###SHIPPING_PRICE###'] = number_format($totalShippingCost, 2);
			$markerArray['###SHIIPING_VAT###'] = number_format($totalShippingVat, 2);
			$markerArray['###SHIPPING_TOTAL###'] = number_format($totalShippingCost, 2);
			
			/**********************shipping-23.12.2008********************/


			if ($prdCount > 1) {
				$markerArray['###TOTAL_PRICE###'] = $this->pi_getLL('net_total') . ': ' . number_format(($prdTotal + $totalShippingCost), 2) . ' ' . $this->currencyInt;
				$markerArray['###STAGE1SUBMIT_STATUS###'] = '';
			}
			else {
				$markerArray['###TOTAL_PRICE###'] = '';
				$markerArray['###STAGE1SUBMIT_STATUS###'] = 'disabled="disabled"';
			}
			$markerArray['###CART_INNER###'] = $prdList;
			$markerArray['###HEADER_SHOPPING_DETAILS###'] = $this->pi_getLL('shopping_details');
			$markerArray['###HEADER_NO###'] = $this->pi_getLL('no');
			$markerArray['###HEADER_TITLE###'] = $this->pi_getLL('title');
			$markerArray['###HEADER_TYPE###'] = $this->pi_getLL('type');
			$markerArray['###HEADER_QUANTITY###'] = $this->pi_getLL('quantity');
			$markerArray['###HEADER_PRICE###'] = $this->pi_getLL('price');
			$markerArray['###HEADER_SHIPPING###'] = $this->pi_getLL('shipping');
			$markerArray['###HEADER_VAT###'] = $this->pi_getLL('vat_header');
			$markerArray['###HEADER_DISCOUNT###'] = $this->pi_getLL('discount_header');
			$markerArray['###HEADER_TOTAL###'] = $this->pi_getLL('total');
			$markerArray['###HEADER_TOTAL###'] = $this->pi_getLL('total');
			$markerArray['###BUTTON_CONTINUE###'] = $this->pi_getLL('button_continue');
			$markerArray['###EXTENSION_PATH###'] = t3lib_extMgm::siteRelPath($this->extKey);
			if(t3lib_div::_GET('L') == 1) {
				$markerArray['###LANGUAGE_SETTING###'] = '&L=1';
			}
			else {
				$markerArray['###LANGUAGE_SETTING###'] = '';
			}
			return $this->cObj->substituteMarkerArrayCached($templateHeader['cart_main'], $markerArray);
		}

	}

	/**
	 * The showCartPage2 method is used for existing user & new user
	 * Step 1 for online process
	 * @param is mot used
	 *
	 * @return	The login form/ Registration Form is displayed
	 */
	function showCartPage2() {
		$utility = new tx_netzrezeptecommercelibrary_utility();
		$templateHeader['checkout_step2'] = $this->cObj->getSubpart($this->templateCode,'###CHECKOUT_STEP2###');
		$templateHeader['step2old'] = $this->cObj->getSubpart($this->templateCode,'###STEP2_OLD###');
		$templateHeader['step2new'] = $this->cObj->getSubpart($this->templateCode,'###STEP2_NEW###');

		if ($GLOBALS['TSFE']->fe_user->user['uid'] != '') {
			//old user
			$templateHeader['logged_in_form'] = $this->cObj->getSubpart($this->templateCode,'###LOGGED_IN_FORM###');
			$marksLogin['###REGISTERED_USER###'] = $this->pi_getLL('registered_user');
			$marksLogin['###LOGGED_IN_MESSAGE###'] = $this->pi_getLL('LOGGED_IN_MESSAGE');
			$marksLogin['###LOGGED_IN_USERNAME###'] = $GLOBALS['TSFE']->fe_user->user['username'];
			$marksLogin['###LOGGED_IN_SUB_MESSAGE1###'] = $this->pi_getLL('LOGGED_IN_SUB_MESSAGE1');
			$marksLogin['###LOGGED_IN_SUB_MESSAGE2###'] = $this->pi_getLL('LOGGED_IN_SUB_MESSAGE2');
			$marksLogin['###button_continue###'] = $this->pi_getLL('button_continue');
			if (t3lib_div::_GET('L') == 1) {
				$marksLogin['###LANGUAGE_SETTING###'] = '&L=1';
			} else {
				$marksLogin['###LANGUAGE_SETTING###'] = '';
			}
			$markerArray['###LOGGED_IN_STATUS_SECTION###'] = $this->cObj->substituteMarkerArrayCached($templateHeader['logged_in_form'], $marksLogin);
			$markerArray['###HEADER_EXISTING_USER###'] = $this->pi_getLL('header_existing_user');
			$markerMain['###STEP2###'] = $this->cObj->substituteMarkerArrayCached($templateHeader['step2old'], $markerArray);

		} else {
			//new user
			$markerArray = array();
			$fieldsArr = array('gender', 'title', 'first_name', 'last_name', 'email', 'username', 'address','street', 'zip', 'city', 'country', 'telephone', 'fax', 'tx_netzrezepteaddress_ust_id_no');
			foreach ($fieldsArr as $field) {
				$markerArray['###'.$field.'###'] = '';
			}
			if (t3lib_div::_POST('registration_cart') != '')  {
				foreach ($_POST as $key=>$pVal) {
					$_SESSION["$key"] = $utility->cleanVar($pVal);
					$markerArray['###'.$key.'###'] = $pVal;
				}

				$requiredFields = array('username','first_name','last_name','email','password','confirm_password','address','street','zip','city','country');
				$isError = false;
				for($i=0; $i<count($requiredFields); $i++){
					if(t3lib_div::_POST($requiredFields[$i]) == ''){
						$markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:0px; padding-left:5px; padding-bottom:0px;"> Please enter ' . $requiredFields[$i] . '</div>';
						$isError = true;
					}
				}
				if(t3lib_div::_POST('username') != '' && !$isError){

					$dbUserTest = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid',
							'fe_users',
							"username='" . t3lib_div::_POST('username') . "'"
					)or die('Error-Line 265 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if($GLOBALS['TYPO3_DB']->sql_num_rows($dbUserTest) > 0) {
						header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=existing&no_cache=1');
						exit();
					}
				}
				if(t3lib_div::_POST('email') != ''  && !$isError){
					$dbEmailTest = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid',
							'fe_users',
							"email='" . t3lib_div::_POST('email') . "'"
					)or die('Error-Line 278 : ' . $GLOBALS['TYPO3_DB']->sql_error());



					if($GLOBALS['TYPO3_DB']->sql_num_rows($dbEmailTest) == 0) {
						if($this->validateEmailFormat()) {
							$content = $this->showCartPage3();
						}
						else {
							header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=invalid&no_cache=1');
							exit();
						}
					}
					else {
						$markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:0px; padding-left:5px; padding-bottom:0px;">Email address is already used.</div>';
					}


				}

				while (list($key, $val) = each($_POST)) {
					$markerArray['###' . $key . '###'] = $val;
					if ($key == 'country') {
						$selectedCountry = $utility->cleanVar($val);
					}
				}
				$markerArray['###GENDER_LIST###'] = $genderList;
				$markerArray['###COUNTRY_LIST###'] = $this->getCountries($_SESSION['country']);
			} // End of registration 
			else {

				$fieldArr = array('username','first_name', 'last_name', 'title', 'email', 'address','street', 'zip', 'city', 'telephone', 'fax', 'tx_netzrezepteaddress_ust_id_no','password','confirm_password');
				for($i = 0; $i < count($fieldArr); $i ++) {
					$markerArray['###' . $fieldArr[$i] . '###'] = '';
				}
				$markerArray['###COUNTRY_LIST###'] = $this->getCountries();
				$markerArray['###ERROR_MESSAGE###'] = '';
			}

			if (t3lib_div::_POST('forgot_pswd') != '') {
				foreach ($_POST as $key=>$pVal) {
					$_SESSION["$key"] = $utility->cleanVar($pVal);
				}

				$requiredFields = array('username','first_name','last_name','email','password','confirm_password','address','street','zip','city','country');
				for($i=0; $i<count($requiredFields); $i++){
					if(t3lib_div::_POST($requiredFields[$i]) == ''){
						$markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:0px; padding-left:5px; padding-bottom:0px;"> Please enter ' . $requiredFields[$i] . '</div>';
					}
				}
				if(t3lib_div::_POST('username') != ''){

					$dbUserTest = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid',
							'fe_users',
							"username='" . t3lib_div::_POST('username') . "'"
					)or die('Error-Line 265 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if($GLOBALS['TYPO3_DB']->sql_num_rows($dbUserTest) > 0) {
						header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=existing&no_cache=1');
						exit();
					}
				}
				if(t3lib_div::_POST('email') != ''){
					$dbEmailTest = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid',
							'fe_users',
							"email='" . t3lib_div::_POST('email') . "'"
					)or die('Error-Line 278 : ' . $GLOBALS['TYPO3_DB']->sql_error());



					if($GLOBALS['TYPO3_DB']->sql_num_rows($dbEmailTest) == 0) {
						if($this->validateEmailFormat()) {
							$content = $this->showCartPage3();
						}
						else {
							header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=invalid&no_cache=1');
							exit();
						}
					}
					else {
						$markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:0px; padding-left:5px; padding-bottom:0px;">Email address is already used.</div>';
					}


				}

				while (list($key, $val) = each($_POST)) {
					$markerArray['###' . $key . '###'] = $val;
					if ($key == 'country') {
						$selectedCountry = $val;
					}
				}

				$markerArray['###GENDER_LIST###'] = $genderList;
				$markerArray['###COUNTRY_LIST###'] = $this->getCountries($_SESSION['country']);
			

				$templateHeader['forgotten_password_result'] = $this->cObj->getSubpart($this->templateCode,'###FORGOTTEN_PASSWORD_RESULT###');
				if(t3lib_div::_POST('forgot_email') == ''){
					header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&forgotMode=1&no_cache=1');
					exit();
				}
				$dbGetPassword = $GLOBALS['TYPO3_DB']->exec_SELECTquery('password',
						'fe_users', "username='" . t3lib_div::_POST('forgot_email') . "'
						OR email='" . t3lib_div::_POST('forgot_email') . "'");

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($dbGetPassword) > 0) {

					if ($rsGetPassword = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbGetPassword)) {
						$getPswd = $rsGetPassword['password'];
						include_once(t3lib_extMgm::siteRelPath($this->extKey) . 'classes/mime_mail.class.php');
						$mail = new mime_mail();
						$mail->from = "Dustri-Verlag <no-reply@dustri.com>";
						$mail->to = $name . "<" . t3lib_div::_POST('forgot_email') . ">";
						$mail->subject = $this->pi_getLL('FORGOT_PSWD_SUBJECT');
						$mail->body = "Hi ". t3lib_div::_POST('forgot_email') ." , ";
						$mail->body .= "Your password is ". $getPswd ;
						$mail->send();
					}

					$marksLogin['###FORGOTTEN_EMAIL###'] = t3lib_div::_POST('forgot_email');
					$marksLogin['###LINK_TO_LOGIN###'] = '<a href= '. $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&backMode=logIn&no_cache=1>'. $this->pi_getLL('LINK_TO_LOGIN') .'</a>';
					$marksLogin['###PASSWORD_FORGOTTON_RESULT_MESSAGE###'] = $this->pi_getLL('PASSWORD_FORGOTTON_RESULT_MESSAGE');
					$marksLogin['###REGISTERED_USER###'] = $this->pi_getLL('registered_user');
					$marksLogin['###PASSWORD_FORGOTTEN_ERROR_MSG###'] = '';
				} else {
					$marksLogin['###FORGOTTEN_EMAIL###'] = '';
					$marksLogin['###PASSWORD_FORGOTTON_RESULT_MESSAGE###'] = '';
					$marksLogin['###LINK_TO_LOGIN###'] = '<a href= '. $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&backMode=logIn&no_cache=1>'. $this->pi_getLL('LINK_TO_LOGIN') .'</a>';
					$marksLogin['###REGISTERED_USER###'] = '';
					$marksLogin['###PASSWORD_FORGOTTEN_ERROR_MSG###'] = '<div style="color:#ff0000; padding-top:5px; padding-left:5px; padding-bottom:5px;">' . $this->pi_getLL('PASSWORD_FORGOTTEN_ERROR_MSG') .'</div>';
				}

				$markerArray['###LOGGED_IN_STATUS_SECTION###'] = $this->cObj->substituteMarkerArrayCached($templateHeader['forgotten_password_result'], $marksLogin);

			} else {

				if (t3lib_div::_GET('forgotMode') != '') {
					$templateHeader['password_forgotton_form'] = $this->cObj->getSubpart($this->templateCode,'###PASSWORD_FORGOTTEN_FORM###');
					$marksLogin['###SEND_PASSWORD###'] = $this->pi_getLL('SEND_PASSWORD');
					$marksLogin['###PASSWORD_FORGOTTON_MESSAGE###'] = $this->pi_getLL('PASSWORD_FORGOTTON_MESSAGE');
					$marksLogin['###REGISTERED_USER###'] = $this->pi_getLL('registered_user');
					$markerArray['###LOGGED_IN_STATUS_SECTION###'] = $this->cObj->substituteMarkerArrayCached($templateHeader['password_forgotton_form'], $marksLogin);

				} else {
					$templateHeader['login_form'] = $this->cObj->getSubpart($this->templateCode,'###LOGIN_FORM###');
					$marksLogin['###REGISTERED_USER###'] = $this->pi_getLL('registered_user');
					$marksLogin['###USERNAME_LABEL###'] = $this->pi_getLL('username_label');
					$marksLogin['###USERNAME###'] = $_SESSION['username'];
					$marksLogin['###PASSWORD###'] = $_SESSION['password'];
					$marksLogin['###PASSWORD_LABEL###'] = $this->pi_getLL('password_label');
					$marksLogin['###LOGIN_BUTTON_LABEL###'] = $this->pi_getLL('LOGIN_BUTTON_LABEL');
					$marksLogin['###PASSWORD_FORGOTTON###'] = '<a href= '. $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&forgotMode=1&no_cache=1>'. $this->pi_getLL('PASSWORD_FORGOTTON') .'</a>';

					if (t3lib_div::_GET('login') == 'fail') {
						$marksLogin['###LOGIN_ERROR_MESSAGE###'] = '<div style="color:#ff0000; padding-top:5px; padding-left:5px; padding-bottom:5px;">' . $this->pi_getLL('err_invalid_login') . '</div>';
					} else {
						$marksLogin['###LOGIN_ERROR_MESSAGE###'] = '';
					}
					if (t3lib_div::_GET('L') == 1) {
						$marksLogin['###LANGUAGE_SETTING###'] = '&L=1';
					} else {
						$marksLogin['###LANGUAGE_SETTING###'] = '';
					}

				//	$markerArray['###LOGGED_IN_STATUS_SECTION###'] = $this->cObj->substituteMarkerArrayCached($templateHeader['login_form'], $marksLogin);
					$markerArray['###LOGGED_IN_STATUS_SECTION###'] = $this->showFelogin();
				}
			}

			$markerArray['###HEADER_EXISTING_USER###'] = $this->pi_getLL('header_existing_user');
			$markerArray['###HEADER_NEW_USER###'] = $this->pi_getLL('header_new_user');
			$markerArray['###PERSONAL_INFO###'] = $this->pi_getLL('personal_info');
			
			$markerArray['###LOGIN_DETAILS###'] = $this->pi_getLL('login_details');
			$markerArray['###CONTACT_DETAILS###'] = $this->pi_getLL('contact_details');
			$markerArray['###OTHER_DETAILS###'] = $this->pi_getLL('other_details');
			$markerArray['###enter_vat_code###'] = $this->pi_getLL('enter_vat_code');
			$markerArray['###GENDER_LABEL###'] = $this->pi_getLL('gender_label');
			$markerArray['###GENDER_0###'] = $this->pi_getLL('gender_0');
	        $markerArray['###GENDER_2###'] = $this->pi_getLL('gender_2');
		    $markerArray['###GENDER_1###'] = $this->pi_getLL('gender_1');
			$markerArray['###FIRST_NAME_LABEL###'] = $this->pi_getLL('first_name_label');
			$markerArray['###LAST_NAME_LABEL###'] = $this->pi_getLL('last_name_label');
			$markerArray['###TITLE_LABEL###'] = $this->pi_getLL('title_label');
			$markerArray['###DESIRED_PASSWORD###'] = $this->pi_getLL('desired_password');
			$markerArray['###CONFIRM_PASSWORD###'] = $this->pi_getLL('confirm_password');
			$markerArray['###ADDRESS_LABEL###'] = $this->pi_getLL('address_label');
			$markerArray['###STREET_LABEL###'] = $this->pi_getLL('street_label');
			$markerArray['###ZIP_CODE_LABEL###'] = $this->pi_getLL('zip_code_label');
			$markerArray['###TOWN_CITY_LABEL###'] = $this->pi_getLL('town_city_label');
			$markerArray['###COUNTRY_LABEL###'] = $this->pi_getLL('country_label');
			$markerArray['###TELEPHONE_LABEL###'] = $this->pi_getLL('telephone_label');
			$markerArray['###IMAGE_PAY_STAGE1###'] = $this->pi_getLL('image_pay_stage1');
			$markerArray['###BUTTON_SUBMIT###'] = $this->pi_getLL('button_submit');
			
			$markerArray['###ITEM_LABEL###'] = $this->pi_getLL('item_label');
			$markerArray['###NEWSLETTER_TEXT###'] = $this->pi_getLL('newsletter_text');
			$markerArray['###ITEM_LIST###'] = $this->getItems();
			if(t3lib_div::_GET('L') == 1) $markerArray['###LANGUAGE_SETTING###'] = '&L=1';
			else $markerArray['###LANGUAGE_SETTING###'] = '';

			$markerArray['###EXTENSION_PATH###'] = t3lib_extMgm::siteRelPath($this->extKey);

			if(t3lib_div::_GET('L') == 1) {
				$markerArray['###TERMS_CONDITION_TEXT###'] = "Ich akzeptiere die <a href=\"javascript: void(0)\" onclick=\"javascript: var x; x=window.open('static/allgemeine.html', 'tac','menubar=0,status=0,toolbar=0,location=0,resizable=0,width=800,height=500'); if(window.focus) {x.focus()};\">Allgemeinen Gesch&auml;ftsbedingen (AGB).</a><br />
		Ja. Ich habe die Allgemeinen Gesch&auml;ftsbedingungen gelesen! ";
				$markerArray['###DATA_PRIVACY_TEXT###'] = "Ich akzeptiere die <a href=\"javascript: void(0)\" onclick=\"javascript: var x; x=window.open('static/data_privacy_de.html', 'tac','menubar=0,status=0,toolbar=0,location=0,resizable=0,width=800,height=500'); if(window.focus) {x.focus()};\">Datenschutzerkl&auml;rung.</a><br />
		Ja. Ich habe die Datenschutzerkl&auml;rung gelesen! ";
				$markerArray['###LANGUAGE_VAL###'] = 1;
			}
			else {
				$markerArray['###TERMS_CONDITION_TEXT###'] = "I accept the <a href=\"javascript: void(0)\" onclick=\"javascript: var x; x=window.open('static/terms_conditions.html', 'tac','menubar=0,status=0,toolbar=0,location=0,resizable=0,width=800,height=500'); if(window.focus) {x.focus()};\">Terms and Conditions.</a><br />
		Yes, I have read the Terms and Conditions.";
				$markerArray['###DATA_PRIVACY_TEXT###'] = "I accept the <a href=\"javascript: void(0)\" onclick=\"javascript: var x; x=window.open('static/data_privacy.html', 'tac','menubar=0,status=0,toolbar=0,location=0,resizable=0,width=800,height=500'); if(window.focus) {x.focus()};\">Data Protection and Personal Privacy Statement.</a><br />
		Yes, I have read the Data Protection and Personal Privacy Statement.";
				$markerArray['###LANGUAGE_VAL###'] = 0;
			}

			if(t3lib_div::_GET('regMode') != '') {

				if(t3lib_div::_GET('regMode') == 'invalid') $markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:5px; padding-left:5px; padding-bottom:5px;">' . $this->pi_getLL('err_invalid_email') . '</div>';
				elseif(t3lib_div::_GET('regMode') == 'invalidVat') $markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:5px; padding-left:5px; padding-bottom:5px;">' . $this->pi_getLL('err_invalid_vat_id') . '</div>';
				else $markerArray['###ERROR_MESSAGE###'] .= '<div style="color:#ff0000; padding-top:5px; padding-left:5px; padding-bottom:5px;">' . $this->pi_getLL('err_user_exists') . '</div>';

				while (list($key, $val) = each($_SESSION)) $markerArray['###' . $key . '###'] = $val;

				// Assigning gender
				$genderList = '<option value="3">' . $this->pi_getLL('gender_3') . '</option>';
				if($_SESSION['gender'] == 0){
					$genderList .= '<option value="0" selected="selected">' . $this->pi_getLL('gender_0') . '</option>';
					$genderList .= '<option value="1">' . $this->pi_getLL('gender_1') . '</option>';
				}
				elseif($_SESSION['gender'] == 1){
					$genderList .= '<option value="0">' . $this->pi_getLL('gender_0') . '</option>';
					$genderList .= '<option value="1" selected="selected">' . $this->pi_getLL('gender_1') . '</option>';
				}
				else{
					$genderList .= '<option value="0">' . $this->pi_getLL('gender_0') . '</option>';
					$genderList .= '<option value="1">' . $this->pi_getLL('gender_1') . '</option>';
				}
				$markerArray['###GENDER_LIST###'] = $genderList;
				$markerArray['###COUNTRY_LIST###'] = $this->getCountries($_SESSION['country']);
			}
			$markerMain['###STEP2###'] = $this->cObj->substituteMarkerArray($templateHeader['step2new'], $markerArray);
		}
		return $this->cObj->substituteMarkerArrayCached($templateHeader['checkout_step2'], $markerMain);
	}

	/**
	 * The showCartPage3 method is used for address confirmation of user
	 * It also shows the Billing Address & Shipping Address of user.
	 * Step 2 for online process
	 * @param is mot used
	 *
	 * @return	The registration address, billing address and shipping address of user are displayed.
	 */

	function showCartPage3() {
		$utility = new tx_netzrezeptecommercelibrary_utility();
		$templateHeader['checkout_step3'] = $this->cObj->getSubpart($this->templateCode,'###CHECKOUT_STEP3###');
		$markerArray['###EXTENSION_PATH###'] = t3lib_extMgm::siteRelPath($this->extKey);
		if(t3lib_div::_GET('L') == 1) {
			$markerArray['###LANGUAGE_VAL###'] = 1;
		}
		else $markerArray['###LANGUAGE_VAL###'] = 0;
		if(t3lib_div::_POST('registration_cart') != '') {
			foreach ($_POST as $key=>$pVal) {
				$_SESSION["$key"] = $utility->cleanVar($pVal);
			}
			//echo 'Post Val: ' . t3lib_div::_POST('tx_captcha_string') . '  Session Val: ' . $_SESSION['tx_captcha_string'];
			$nonEssentialFields = array('registration_cart', 'confirm_password', 'tx_captcha_string', 'tac', 'prst');
			$insertFields = "pid,usergroup,tstamp,name";
			$insertValues = "39,2,'" . time() . "', '" . t3lib_div::_POST('first_name') . " " . t3lib_div::_POST('last_name');
			$hiddenFields = '';
			$insertData = array();
			$updateData = array();
			$insertData['pid'] 			= 39;
			$insertData['usergroup'] 	= 2;
			$insertData['tstamp'] 		= time();
			$insertData['name'] 		= t3lib_div::_POST('first_name') . " " . t3lib_div::_POST('last_name');


			while (list($key, $val) = each($_POST)) {
				if(!in_array($key, $nonEssentialFields)) {
					// DUSE-9 :: Vat
					if ( $key == 'country') {
						$tempval = t3lib_div::trimExplode('##',$val);
						if ($tempval[1] != '') {
							$val = $tempval[1];
							$insertData['country_code'] = $tempval[0];
							$updateData['country_code'] = $tempval[0];
						}
					}elseif ($key == 'item'){
						$val = implode(',', $val);
					}
					$markerArray['###' . $key . '###'] = $val;
					$markerArray['###billing_' . $key . '###'] = $val;
					$markerArray['###shipping_' . $key . '###'] = $val;
					$hiddenFields .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
					$insertData[$key] 	= $val;
					$updateData[$key] 	= $val;
				}



				if($key == 'country') $selectedCountry = $val;
			}
			$dbUserTest = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid',
							'fe_users',
							"username='" . t3lib_div::_POST('username') . "'"
						)or die('Error-Line 504 : ' . $GLOBALS['TYPO3_DB']->sql_error());

			if($GLOBALS['TYPO3_DB']->sql_num_rows($dbUserTest) == 0) {
				if($this->validateEmailFormat()) {
					if($this->validateVatId()) {

						/*
						 COMMENTED Double-Optin-sushil
						*/
						// Set disable =1 for double opting.
						$insertData['disable'] = 1;
						
						$GLOBALS['TYPO3_DB']->exec_INSERTquery(
								'fe_users',
							$insertData
						);
						$insertId = $GLOBALS['TYPO3_DB']->sql_insert_id();
						//$GLOBALS['TSFE']->fe_user->user[uid] = $insertId;
						// Update Customer Id for user
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
								'fe_users',
								"uid=" . $insertId,
								array('customer_id' => ($insertId + 500000))
						) or die('Error-Updating Cust. Id Line 530 : ' . $GLOBALS['TYPO3_DB']->sql_error());
    					
						/*
						COMMENTED Double-Optin-sushil
						*/
						$this->sendDoubleOptingMail($insertData);
						header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&mode=doubleoptmsg&no_cache=1');
						exit();
						
						/* Double-Optin-Sushil :: Comment the following "if" (auto login) block when we activate the Double-Optin */
						if (!$this->login($insertData['username'],$insertData['password']) ) {
							header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=invalidVat&no_cache=1');
							exit();
						}
					}
					else {
						header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=invalidVat&no_cache=1');
						exit();
					}
				}
				else {
					header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=invalid&no_cache=1');
					exit();
				}
			}
			else {
				header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . '&regMode=existing&no_cache=1');
				exit();
			}
		} else {
			$addOnParams = '&login=fail&logintype=login';
			if(t3lib_div::_GET('L') != '') $addOnParams .= '&L=' . t3lib_div::_GET('L');
			if(!$GLOBALS['TSFE']->fe_user->user['uid']) {
				// @TODO make the url to typolink
				header('location: ' . $_SERVER['PHP_SELF'] . '?id=' . $this->pid . $addOnParams);
			}
			 
		}
		if(t3lib_div::_POST('username') != '') {

			$userName = t3lib_div::_POST('username');
		} else {

			$userName = $GLOBALS['TSFE']->fe_user->user['username'];
		}
	
		$dbUserBillingInfo = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								'tx_netzrezepteshop_billing_temp',
								"session_id='" . $this->sessionId ."' AND deleted=0"
							)or die($GLOBALS['TYPO3_DB']->sql_error());
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($dbUserBillingInfo) == 0) {
			$selectFields = 'uid,gender,address,telephone,fax,email,title,zip,city,country,first_name,last_name,tx_netzrezepteaddress_ust_id_no,street';
			$dbUser = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							$selectFields,
							'fe_users',
							"username='" . $userName . "'"
					)or die($GLOBALS['TYPO3_DB']->sql_error());


			if($rUser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUser)) {
				for($i = 0; $i <= 14; $i ++){
					$markerArray['###'. mysql_field_name($dbUser, $i) . '###'] = $rUser[mysql_field_name($dbUser, $i)];
					$markerArray['###billing_'. mysql_field_name($dbUser, $i) . '###'] = $rUser[mysql_field_name($dbUser, $i)];
					$markerArray['###shipping_'. mysql_field_name($dbUser, $i) . '###'] = $rUser[mysql_field_name($dbUser, $i)];
					$hiddenFields .= '<input type="hidden" name="' . mysql_field_name($dbUser, $i) . '" value="' . $rUser[mysql_field_name($dbUser, $i)] . '" />';
				}
				$selectedCountry = $rUser['country'];
			}
			// if country empty
			if (empty($markerArray['###country###'])) {
				$markerArray['###country###'] = '<font color="red">N/A</font>';
			}
		// DUSE-9 :: Vat
		$countriesList = $this->getCountries($selectedCountry);
		$markerArray['###COUNTRY_LIST###'] 						= $countriesList;
		$markerArray['###COUNTRY_LIST_BILLING###'] 				= $countriesList;
		$markerArray['###COUNTRY_LIST_SHIPPING###'] 			= $countriesList;

		} elseif($GLOBALS['TSFE']->fe_user->user[uid] 	!= ''  && t3lib_div::_POST('registration_cart') != '' ){
			// DUSE-9 :: Vat
			$countriesList = $this->getCountries($selectedCountry);
			$markerArray['###COUNTRY_LIST###'] = $countriesList;
			$markerArray['###COUNTRY_LIST_BILLING###'] = $countriesList;
			$markerArray['###COUNTRY_LIST_SHIPPING###'] = $countriesList;

		} else {
			if ($rUserBillingInfo = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUserBillingInfo)) {
				if($rUserBillingInfo['cruser_id'] == $GLOBALS['TSFE']->fe_user->user['uid']) {
					for($i = 0; $i <= mysql_num_fields($dbUserBillingInfo); $i++){
						$markerArray['###'. mysql_field_name($dbUserBillingInfo, $i) . '###'] = $rUserBillingInfo[mysql_field_name($dbUserBillingInfo, $i)];
						$hiddenFields .= '<input type="hidden" name="' . mysql_field_name($dbUserBillingInfo, $i) . '" value="' . $rUserBillingInfo[mysql_field_name($dbUserBillingInfo, $i)] . '" />';
					}
					$selectedCountryBilling = $rUserBillingInfo['billing_country'];
					$selectedCountryShipping = $rUserBillingInfo['shipping_country'];
					// DUSE-9 :: Vat
					$countriesListBilling = $this->getCountries($selectedCountryBilling);
					$countriesListShipping = $this->getCountries($selectedCountryShipping);
					$selectFields = 'uid, gender, address, telephone, fax, email,
									title, zip, city, country, first_name,
									last_name, tx_netzrezepteaddress_ust_id_no,street';

					$dbUser = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								$selectFields,
								'fe_users',
								"username='" . $userName . "'"
							)or die('Error-Line 747 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if($rUser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUser)) {
						for($i = 0; $i <= 14; $i ++){
							$markerArray['###'. mysql_field_name($dbUser, $i) . '###'] = $rUser[mysql_field_name($dbUser, $i)];
						}
					}
					$diffBillingShiping =  $this->diffBillingShiping($rUser,$rUserBillingInfo); 
					$markerArray['###DIFF_BILLING###'] = $diffBillingShiping['diffbilling'];
					$markerArray['###DIFF_SHIPPING###'] = $diffBillingShiping['diffshiping'];
					$markerArray['###COUNTRY_LIST_BILLING###'] = $countriesListBilling;
					$markerArray['###COUNTRY_LIST_SHIPPING###'] = $countriesListShipping;
					
				} else {
					$selectFields = 'uid,gender,address,telephone,fax,email,title,zip,
					city,country,first_name,last_name,tx_netzrezepteaddress_ust_id_no,street';

					$dbUser = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								$selectFields,
								'fe_users',
								"username='" . $userName . "'"
							)or die('Error-Line 767 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if($rUser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUser)) {
						for($i = 0; $i <= 14; $i ++){
							$markerArray['###'. mysql_field_name($dbUser, $i) . '###'] = $rUser[mysql_field_name($dbUser, $i)];
							$markerArray['###billing_'. mysql_field_name($dbUser, $i) . '###'] = $rUser[mysql_field_name($dbUser, $i)];
							$markerArray['###shipping_'. mysql_field_name($dbUser, $i) . '###'] = $rUser[mysql_field_name($dbUser, $i)];
							$hiddenFields .= '<input type="hidden" name="' . mysql_field_name($dbUser, $i) . '" value="' . $rUser[mysql_field_name($dbUser, $i)] . '" />';
						}
						$selectedCountry = $rUser['country'];
					}
					// DUSE-9 :: Vat
					$countriesList = $this->getCountries($selectedCountry);
					$markerArray['###COUNTRY_LIST###'] = $countriesList;
					$markerArray['###COUNTRY_LIST_BILLING###'] = $countriesList;
					$markerArray['###COUNTRY_LIST_SHIPPING###'] = $countriesList;


				}
			}

		}

		if($markerArray['###gender###'] == 0){
			$markerArray['###gender###'] = 'Mr';
			$markerArray['###MR_SELECT###'] = 'selected="selected"';
			$markerArray['###MRS_SELECT###'] = '';
		}
		else{
			$markerArray['###gender###'] = 'Ms';
			$markerArray['###MRS_SELECT###'] = 'selected="selected"';
			$markerArray['###MR_SELECT###'] = '';
		}
		$markerArray['###HIDDEN_FIELDS###'] = $hiddenFields;
		$markerArray['###IMAGE_PAY_STAGE2###'] = $this->pi_getLL('image_pay_stage2');
		$markerArray['###YOUR_REGISTRATION_INFORMATION###'] = $this->pi_getLL('your_registration_information');
		$markerArray['###REGISTRATION_INFORMATION###'] = $this->pi_getLL('registration_information');
		$markerArray['###GENDER_LABEL###'] = $this->pi_getLL('gender_label');
	    $markerArray['###GENDER_0###'] = $this->pi_getLL('gender_0');
	    $markerArray['###GENDER_2###'] = $this->pi_getLL('gender_2');
		$markerArray['###GENDER_1###'] = $this->pi_getLL('gender_1');

		$markerArray['###FIRST_NAME_LABEL###'] = $this->pi_getLL('first_name_label');
		$markerArray['###LAST_NAME_LABEL###'] = $this->pi_getLL('last_name_label');
		$markerArray['###TITLE_LABEL###'] = $this->pi_getLL('title_label');
		$markerArray['###ADDRESS_LABEL###'] = $this->pi_getLL('address_label');
		$markerArray['###STREET_LABEL###'] = $this->pi_getLL('street_label');
		$markerArray['###ZIP_CODE_LABEL###'] = $this->pi_getLL('zip_code_label');
		$markerArray['###TOWN_CITY_LABEL###'] = $this->pi_getLL('town_city_label');
		$markerArray['###COUNTRY_LABEL###'] = $this->pi_getLL('country_label');
		$markerArray['###TELEPHONE_LABEL###'] = $this->pi_getLL('telephone_label');
		$markerArray['###BILL_ADDRESS###'] = $this->pi_getLL('bill_address');
		$markerArray['###SHIP_ADDRESS###'] = $this->pi_getLL('ship_address');
		$markerArray['###BILL_ADDRESS_CHECK###'] = $this->pi_getLL('bill_address_check');
		$markerArray['###SHIP_ADDRESS_CHECK###'] = $this->pi_getLL('ship_address_check');
		$markerArray['###BUTTON_CONFIRM###'] = $this->pi_getLL('button_confirm');
		$markerArray['###BUTTON_EDIT_PROFILE###'] = $this->pi_getLL('button_edit_profile');
		
		// TypoLink Initial Setup
		$this->local_cObj = t3lib_div::makeInstance("tslib_cObj");
		$this->local_cObj->setCurrentVal($GLOBALS['TSFE']->tmpl->setup['editProfilePageId']);
		$this->typolink_conf["parameter."]["current"] = 1;
		$this->typolink_conf["additionalParams"] =
		$this->cObj->stdWrap($this->typolink_conf["additionalParams"],
		$this->typolink_conf["additionalParams."]);
		unset($this->typolink_conf["additionalParams."]);
		
		$temp_conf = $this->typolink_conf;
		$temp_conf["returnLast"] = 'url';
		$markerArray['###EDIT_PROFILE_PAGE###'] = $this->local_cObj->typolink(NULL, $temp_conf);
		
		
		if(t3lib_div::_GET('L') == 1) $markerArray['###LANGUAGE_SETTING###'] = '&L=1';
		else $markerArray['###LANGUAGE_SETTING###'] = '';
		//$countriesList = '';


		return $this->cObj->substituteMarkerArrayCached($templateHeader['checkout_step3'], $markerArray);
	}

	function sendDoubleOptingMail($insertData){

		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$name = $insertData['first_name'].' '.$insertData['last_name'];
		$recipient = array($insertData['email'] => $name );
		$sender = array('info@dustri.com' => 'Dustri-Verlag');
		$subject = $this->pi_getLL('doubleopt_email_subject');
		$body = $this->pi_getLL('doubleopt_email_body');
		
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$pageid = intval($GLOBALS['TSFE']->id);
		$conf = array();
		$conf['parameter'] = $pageid; 
		$conf['additionalParams'] = '&mode=emailvarified&email='.$insertData['email'];
		$conf['forceAbsoluteUrl'] = true;
		$verifylink = $cObj->typoLink_URL($conf);
		$body = str_replace(array('###NAME###','###VERIFY_LINK###'), array($name,$verifylink), $body);
		$message->setTo($recipient)
		->setFrom($sender)
		->setSubject($subject);
		$message->setBody($body, 'text/html');
		$message->send();

	}

	function sendConfirmationMail($insertData){
		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$name = $insertData['first_name'].' '.$insertData['last_name'];
		$recipient = array($this->conf["newsletter."]["adminEmail"] => $this->conf["newsletter."]["adminName"]);
		$sender = array($insertData['email'] => $name);
		$subject = $this->pi_getLL('email_confirm_subject');
		$body = $this->pi_getLL('email_confirm_body');
		$userData = '';
		$userData .= '<p>'.$this->pi_getLL('name_label').':'.$name.'</p>';
		$userData .= '<p>'.$this->pi_getLL('email_label').':'.$insertData['email'].'</p>';
		$arrItem = array();
		if(!empty($insertData['item'])){
			$dbitem = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_topic_detail',
						"uid in (".$insertData['item'].") AND deleted = 0 AND hidden = 0"
					) or die('Error-Line 904 : ' . $GLOBALS['TYPO3_DB']->sql_error());
			while ($ritems = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbitem)) {
				$arrItem[] = $ritems['title'];
			}
		}
		
		$userData .= '<p>'.$this->pi_getLL('item_label').':'.implode(', ',$arrItem).'</p>';
		
		
		$body = str_replace(array('###USER_DATA###'), array($userData), $body);
		$message->setTo($recipient)
		->setFrom($sender)
		->setSubject($subject);
		$message->setBody($body, 'text/html');
		$message->send();

	}

	/**
	 * The showCartPage4 method is used for overview of online payment
	 *
	 * Step 3 for online process
	 * @param is mot used
	 *
	 * @return	check out for ipayment
	 */

	function showCartPage4() {
		// Update User ID in Temporary Table
		$GLOBALS['TYPO3_DB']->debugOutput = true;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;

		$checkRec = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_netzrezepteshop_temp', "deleted=0 AND hidden=0 AND session_id='" . session_id() . "'");
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($checkRec) == 0) {
			//redirect to home
			header('location: index.php');
		}

		$updArray = array('cruser_id' => $GLOBALS['TSFE']->fe_user->user['uid']);
		if ($this->getCustomerVat()) {
			$updArray['vat_online_subscription'] = $this->getCustomerVat();
			$updArray['vat'] = $this->getCustomerVat();
			$updArray['vat_per_for_print_subs'] = $this->getCustomerVat();
			$updArray['vat_per_for_online_subs'] = $this->getCustomerVat();
		}
		
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_netzrezepteshop_temp', "deleted=0 AND hidden=0 AND session_id='" . session_id() . "'", $updArray);

		$templateHeader['checkout_step4'] = $this->cObj->getSubpart($this->templateCode,'###CHECKOUT_STEP4###');
		$updateFields = "cruser_id=". $GLOBALS['TSFE']->fe_user->user['uid'] .",tstamp='" . time();
		$nonEssentialFields = array('address_cart', 'billing', 'shipping');

		$insertData 				= array();
		$updateData 				= array();
		$insertData['pid'] 			= 39;
		$insertData['tstamp'] 		= time();
		$insertData['cruser_id']	= $GLOBALS['TSFE']->fe_user->user['uid'];
		$insertData['session_id'] 	= $this->sessionId;

		$updateData['cruser_id'] 	= $GLOBALS['TSFE']->fe_user->user['uid'];
		$updateData['tstamp'] 		= time();
		foreach ($this->billingTempFields as $tmpField) {
			$val = (isset($_SESSION[$tmpField])) ? $_SESSION[$tmpField] : '';
			$markerArray['###' . $tmpField . '###'] = stripslashes($val);
			$insertData[$tmpField] = htmlentities($val, ENT_QUOTES, 'UTF-8');
			$updateData[$tmpField] = htmlentities($val, ENT_QUOTES, 'UTF-8');
		}
	
		$dbBillingTempTest = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid, billing_country',
							'tx_netzrezepteshop_billing_temp',
							"session_id='" . session_id() . "' AND deleted=0"
						)or die($GLOBALS['TYPO3_DB']->sql_error());
		if($GLOBALS['TYPO3_DB']->sql_num_rows($dbBillingTempTest) == 0) {
				$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tx_netzrezepteshop_billing_temp',
					$insertData
				) or die('Error-Billing_info_temp-Insert-895: ' . mysql_error());


		} else{
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'tx_netzrezepteshop_billing_temp',
						"session_id='" . session_id() . "' AND deleted=0",
						$updateData
			)or die('Error-Billing_info_temp-Update Line-896: ' . mysql_error());
		}

		/* Updating Currency and Price based on billing address - Begin */
		$dbVatId = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'tx_netzrezepteaddress_ust_id_no, country, tx_netzrezepteaddress_rebate',
						'fe_users',
						"uid='" . $GLOBALS['TSFE']->fe_user->user['uid'] . "'"
					) or die('Error-Line 904 : ' . $GLOBALS['TYPO3_DB']->sql_error());


		if ($rVatId = mysql_fetch_object($dbVatId))	{
			$vatId = $rVatId->tx_netzrezepteaddress_ust_id_no;
			if ($vatId == '') {
				$_SESSION['vatId'] = '-';
			}
			else {
				$_SESSION['vatId'] = $vatId;
			}
		}

		$dbBillingCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'billing_country, shipping_country,billing_country_code',
								'tx_netzrezepteshop_billing_temp',
								"session_id ='" . session_id() . "' AND deleted=0"
							) or die($GLOBALS['TYPO3_DB']->sql_error());

		if ($rBillingCountry = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbBillingCountry)) {
			$billing_country = $rBillingCountry['billing_country'];
			$shippingCountry = $rBillingCountry['shipping_country'];
			$_SESSION['billing_country_code'] = $rBillingCountry['billing_country_code'];
				// All currency initializations go in here
			// DUSE-9 :: Vat
			//echo $rBillingCountry['billing_country_code'] .' '. $vatId;
			$currencyInitArr = $this->currencyInit($rBillingCountry['billing_country_code'], $vatId);
			#setcookie('currency', $currencyInitArr[0], time()+3600000, '/');
			setcookie('currency', $currencyInitArr[0], 0, '/');
			$globalPriceCalculationFlag = $currencyInitArr[2];
		}
		$dbShippingInfo = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'uid',
								'static_countries',
								"cn_short_en='" . $shippingCountry . "' AND	cn_eu_member=1"
						) or die($GLOBALS['TYPO3_DB']->sql_error());

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($dbShippingInfo) > 0){
			$shippingField = 'shipping_cost_EU_'. $currencyInitArr[1];
		}
		elseif($shippingCountry == 'United States') {
			$shippingField = 'shipping_cost_US_'. $currencyInitArr[1];
		}
		else {
			$shippingField = 'shipping_cost_OT_'. $currencyInitArr[1];
		}
		$this->shipCountryStore = $shippingCountry;

		$dbPriceShipping = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'product_id, uid, product_type, subscription_type, discount, published, ebook',
								'tx_netzrezepteshop_temp',
								"deleted=0 AND hidden=0 AND session_id='" . session_id() . "'"
						) or die($GLOBALS['TYPO3_DB']->sql_error());

		while ($rPriceShipping = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbPriceShipping)) {

			// DUSE-9 :: Vat
			// Vat calculation flag reset for non digital (print) products
			$digitalProduct = $this->isDigitalProduct($rPriceShipping['product_type']);
			
			// Dustri Book (ebook)
			if ($rPriceShipping['ebook'] > 0) {
				$this->ebook = true;
			}
			if ($this->ebook) $digitalProduct = true;
			// Dustri Book (ebook)
			
			if (!$digitalProduct) {
				$currencyInitArr[2] =	$this->priceCalculationFlagForPrint($rBillingCountry['billing_country_code'], $vatId);
			} else {
				$currencyInitArr[2] = $globalPriceCalculationFlag;
			}

			$updShippingFlag = false;
			$updateData = array();
			$updateData['currency'] = $currencyInitArr[0];
			$dbVat = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						$currencyInitArr[3] . " AS vat, " . $shippingField . " AS shipping",
						'tx_datamasters_product_types',
						"code=" . $rPriceShipping['product_type']
					) or die('Error-Line 974 : ' . $GLOBALS['TYPO3_DB']->sql_error());


			if ($rVat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbVat)) {
				if($this->getCustomerVat()!=''){
					$vatPercentage = $this->getCustomerVat();
				}
				else{
					$vatPercentage = $rVat['vat'];
				}
				

				if ($rPriceShipping['product_type'] == 24)	{ // print or non-digital

					$dbShippingJournal = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
										"price_". $currencyInitArr[1] ." AS price, " . $shippingField . " AS shipping",
										'tx_datamasters_journal_volumes',
										"uid=" . $rPriceShipping['product_id']
					)or die('Error-Line 988 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if ($rShippingJournal = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbShippingJournal)) {
						$updateData['unit_price'] = $this->getEntryPrices($rShippingJournal['price'], $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
						if($rShippingJournal['shipping'] > 0){
							$updateData['shipping_cost'] = $this->getEntryPrices($rShippingJournal['shipping'], $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
						}
						else {
							$db_Shipping_vat = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
												$shippingField . " AS shipping",
												'tx_datamasters_product_types',
												"code = " . $rPriceShipping['product_type']
							)or die('Error-Line 996 : ' . $GLOBALS['TYPO3_DB']->sql_error());


							if($r_shipping_vat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($db_Shipping_vat)) {
								$updateData['shipping_cost'] = $this->getEntryPrices($r_shipping_vat['shipping'], $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
							}
							$updShippingFlag = true;
						}
					}
					// DUSE-9 :: Vat
					$updateData['vat'] = $this->getVatPercentage($currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);

				}
				elseif($rPriceShipping['product_type'] >= 31) { // Subsriptions digital and non-digital
					$dbShippingProduct = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
										"type, subscription_prices_". $currencyInitArr[1] ." AS price, " . $shippingField . " AS shipping",
										'tx_datamasters_products',
										'uid=' . $rPriceShipping['product_id']
										)or die('Error-Line 996 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if ($rShippingProduct = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbShippingProduct)) {
						$priceArr = explode('#', $rShippingProduct['price']);

						if($rPriceShipping['subscription_type'] == '0') {
							$unit_price_value 	=  $priceArr[0];
						}
						elseif($rPriceShipping['subscription_type'] == '1') {
							$unit_price_value 	=  $priceArr[1];

							if (trim($rPriceShipping['product_type']) == 34) {

								if (strpos($priceArr[1], '@') !== false) {
									$subscriptionPrice = explode('@', $priceArr[1]);
									$unit_price_value = $subscriptionPrice[0] + $subscriptionPrice[1];
								}
							}
						}
						else{
							$unit_price_value 	=  $priceArr[2];
						}
						
						if($this->getCustomerVat()!=''){
							$vatPercentage = $this->getCustomerVat();
						}
						else{
							$vatPercentage = $rVat['vat'];
						}
							
						if ($digitalProduct) {
							if (empty($currencyInitArr[5])) {
								$vatPercentage = $currencyInitArr[4];
							} else {
								$vatPercentage = 0;
							}
						}
						$updateData['unit_price'] = $this->getEntryPrices($unit_price_value, $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
						
						if($rShippingProduct['shipping'] > 0)
							$updateData['shipping_cost'] = $this->getEntryPrices($rShippingProduct['shipping'], $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
						else {
							$db_Shipping_vat = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
													$shippingField . " AS shipping",
													'tx_datamasters_product_types',
													'code = ' . $rPriceShipping['product_type']
												)or die('Error-Line 996 : ' . $GLOBALS['TYPO3_DB']->sql_error());

							if($r_shipping_vat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($db_Shipping_vat)) {
								$updateData['shipping_cost'] = $this->getEntryPrices($r_shipping_vat['shipping'], $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
							}
							$updShippingFlag = true;
						}
					}
					$updateData['vat'] = $this->getVatPercentage($currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
					
				}
				else {
					$dbShippingProduct = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
											"type, price_". $currencyInitArr[1] ." AS price, " . $shippingField . " AS shipping, ebook_price_". $currencyInitArr[1] ." AS ebook_price",
											'tx_datamasters_products',
											'uid=' . $rPriceShipping['product_id']
										)or die('Error-Line 1042 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if($rShippingProduct = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbShippingProduct)) {
						if($this->getCustomerVat()!=''){
							$vatPercentage = $this->getCustomerVat();
						}
						else{
							$vatPercentage = $rVat['vat'];
						}
						if ($digitalProduct) {
							if (empty($currencyInitArr[5])) { // if EU country
								$vatPercentage = $currencyInitArr[4];
							} else {
								$vatPercentage = 0;
							}
						}
						$product_unit_price = $rShippingProduct['price'];
						// Dustri Book (ebook)
						if ($this->ebook) {
							$product_unit_price = $rShippingProduct['ebook_price'];
							// Replace "$rShippingProduct['price']" with "$product_unit_price"   
						}
						// Dustri Book (ebook)
						
						
						$updateData['unit_price'] =  $this->getEntryPrices($product_unit_price, $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
						if($rShippingProduct['shipping'] > 0) {
							$updateData['shipping_cost'] = $this->getEntryPrices($rShippingProduct['shipping'], $currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
						}
						else {
								$db_Shipping_vat = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
													$shippingField . " AS shipping",
													'tx_datamasters_product_types',
													'code = ' . $rPriceShipping['product_type']
												)or die('Error-Line 1054 : ' . $GLOBALS['TYPO3_DB']->sql_error());


							if($r_shipping_vat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($db_Shipping_vat)) {
								$updateData['shipping_cost'] = $this->getEntryPrices($r_shipping_vat['shipping'], $currencyInitArr[2], $rVat['vat'], $currencyInitArr[0]);
							}
							$updShippingFlag = true;
						}
					}
					// Dustri Book (ebook)
					if ($this->ebook) {
						$updateData['shipping_cost'] = 0;
					}
					// Dustri Book (ebook)
					// saurav
					if(in_array($this->shipCountryStore, $this->shipcostZero)){
							$updateData['shipping_cost'] = 0;
					}
					
					$updateData['vat'] = $this->getVatPercentage($currencyInitArr[2], $vatPercentage, $currencyInitArr[0]);
				}
				/*
					// DUSE-9 :: Vat
				if ($this->isDigitalProduct($rPriceShipping['product_type'])) {
					$updateData['vat'] = 0;
				}
				*/
			}
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
										'tx_netzrezepteshop_temp',
										"uid = " . $rPriceShipping['uid'],
									$updateData
			)or die('Error-shop_temp-update Line-1071: ' . $GLOBALS['TYPO3_DB']->sql_error());
		}

		/*obtaining shipping field - end*/

		$dbPrice = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'currency, quantity, unit_price, shipping_cost, vat, discount',
						'tx_netzrezepteshop_temp',
						"deleted=0 AND hidden=0 AND session_id='" . session_id() . "'"
				)or die('Error-Line 1054 : ' . $GLOBALS['TYPO3_DB']->sql_error());


		if($GLOBALS['TYPO3_DB']->sql_num_rows($dbPrice) > 0) {
			while($rPrice = mysql_fetch_object($dbPrice)) {

				$prdAmount = number_format(($rPrice->quantity * $rPrice->unit_price),2, '.', '');
				$shippingAmount = number_format(($rPrice->quantity * $rPrice->shipping_cost),2);
				$discountAmount = number_format((($prdAmount * $rPrice->discount) / 100),2);
				$prdVat = number_format(((($prdAmount - $discountAmount + $shippingAmount)/(100 + $rPrice->vat)) * $rPrice->vat),2);
				$totAmount += ($prdAmount - $discountAmount + $shippingAmount);
				$markerArray['###CURRENCY###'] = $rPrice->currency;
			}
			$markerArray['###AMOUNT_TOTAL###'] = number_format($totAmount, 2);
			$markerArray['###AMOUNT_TOTAL_IPAYMENT###'] = ($totAmount * 100);
			$markerArray['###MANUAL_AMOUNT_TOTAL###'] = ($totAmount * 100);
			$markerArray['###AMOUNT_TOTAL_PAYPAL###'] = $totAmount;
		}

		$markerArray['###iPaymentURL###'] = $this->iPaymentURL;
		$markerArray['###iPaymentTrxuserId###'] = $this->iPaymentTrxuserId;
		$markerArray['###iPaymentPassword###'] = $this->iPaymentPassword;

		$markerArray['###REDIRECT_FROM_IPAYMENT_URL###'] = $this->baseURL . '?id=' . $this->pid . '&payment=success&no_cache=1';
		if (t3lib_div::_GET('L') != '') {
			$markerArray['###REDIRECT_FROM_IPAYMENT_URL###'] .= '&L=' . t3lib_div::_GET('L');
		}

		$markerArray['###IMAGE_PAY_STAGE3###'] = $this->pi_getLL('image_pay_stage3');
		$markerArray['###TOTAMT_LABEL###'] = $this->pi_getLL('totamt_label');
		$markerArray['###CURRENCY_LABEL###'] = $this->pi_getLL('currency_label');
		$markerArray['###BILL_ADDRESS###'] = $this->pi_getLL('bill_address');
		$markerArray['###SHIP_ADDRESS###'] = $this->pi_getLL('ship_address');
		$markerArray['###TITLE_LABEL###'] = $this->pi_getLL('title_label');
		$markerArray['###ADDRESS_LABEL###'] = $this->pi_getLL('address_label');
		$markerArray['###STREET_LABEL###'] = $this->pi_getLL('street_label');
		$markerArray['###ZIP_CODE_LABEL###'] = $this->pi_getLL('zip_code_label');
		$markerArray['###TOWN_CITY_LABEL###'] = $this->pi_getLL('town_city_label');
		$markerArray['###COUNTRY_LABEL###'] = $this->pi_getLL('country_label');
		$markerArray['###TELEPHONE_LABEL###'] = $this->pi_getLL('telephone_label');
		$markerArray['###PAYOPT_LABEL###'] = $this->pi_getLL('payopt_label');
		$markerArray['###ONLINEPAY_LABEL###'] = $this->pi_getLL('onlinepay_label');
		$markerArray['###IPAYMENT_PRESUBMIT_TEXT###'] = $this->pi_getLL('ipayment_presubmit_text');
		$markerArray['###MANUALPAYMENT_PRESUBMIT_TEXT###'] = $this->pi_getLL('manualpayment_presubmit_text');
		$markerArray['###PAYMENT_BUTTON###'] = $this->pi_getLL('manualpayment_button_text');
		$markerArray['###OFFLINEPAY_LABEL###'] = $this->pi_getLL('offlinepay_label');
		$markerArray['###HEADER_OVERVIEW###'] = $this->pi_getLL('header_overview');
		$markerArray['###purchase_sess_id###'] = session_id();
		$markerArray['###ses_userid###'] = $GLOBALS['TSFE']->fe_user->user[uid];
		$markerArray['###HIDDEN_TRIGGER_URL###'] = $this->baseURL . '?id=82';
		$markerArray['###PAYPALURL###']=$this->conf['paypalURL'];
		$markerArray['###PAYPAL_BUSINESS###']=$this->conf['paypalBusiness'];
		$markerArray['###PAYPAL_EMAIL###']=$this->conf['customerServiceEmailAddress'];
		$markerArray['###PAYPAL_TEST_IPN###']=$this->conf['paypalTestIpn'];
 		
		$markerArray['###PAYPAL_PRESUBMIT_TEXT###'] = $this->pi_getLL('paypal_presubmit_text');
		$markerArray['###CARD_PAYMENT_LABEL###'] = $this->pi_getLL('card_payment_label');
 		
		//$markerArray['###HIDDEN_TRIGGER_URL###'] = 'http://dustri.com/index.php?id=82';
		//$markerArray['###HIDDEN_TRIGGER_URL###'] = 'http://' . $_SERVER['HTTP_HOST'] . '/trigger.php';
		//$markerArray['###HIDDEN_TRIGGER_URL###'] = 'http://78.46.77.77/dustri/trigger.php';
		$markerArray['###billing_gender###'] = ($markerArray['###billing_gender###']==0) ? $this->pi_getLL('gender_0') : $this->pi_getLL('gender_1');
		$markerArray['###shipping_gender###'] = ($markerArray['###shipping_gender###']==0) ? $this->pi_getLL('gender_0') : $this->pi_getLL('gender_1');
		$markerArray['###LANG###'] = (t3lib_div::_GET('L')=='')?0:t3lib_div::_GET('L');

		return $this->cObj->substituteMarkerArrayCached($templateHeader['checkout_step4'], $markerArray);
	}

	/**
	 * The showCartPage5 method is used for payment
	 *
	 * Step 4 for online process
	 * @param is not used
	 *
	 * @return	payment completion information
	 */

	function showCartPage5() {
		
		$postArr = $_POST;
		//For testing purpose the following session data set in triggerme
		$isPaypal = false;
		if(isset($postArr['custom'])){
			foreach($postArr as $key => $value){
				$isExists = preg_match("/paypal/", $value);
				if ($isExists){
					//$this->arrayMap($postArr);
					$_POST['ret_booknr'] = $_POST['txn_id'];
					$_POST['trx_currency'] = $_POST['mc_currency'];
					$_POST['trx_paymentdata_country'] = $_POST['residence_country'];
					$_POST['trx_paymenttyp'] =	'paypal';
					$isPaypal = true;
					break;
				}
			}
		}
		
		if(count($postArr)==0 && count($_SESSION['express'])>0)
		{
			$_POST['ret_booknr'] = $_SESSION['express']['ret_booknr'];
			$_POST['trx_currency'] = $_SESSION['express']['trx_currency'];
			$_POST['trx_paymentdata_country'] = $_SESSION['express']['trx_paymentdata_country'];
			$_POST['trx_paymenttyp'] =	$_SESSION['express']['trx_paymenttyp'];
			$isPaypal = true;
		}
		
		// Obtaining template headers
		$templateHeader['checkout_complete'] = $this->cObj->getSubpart($this->templateCode,'###CHECKOUT_COMPLETE###');

		/* Code Re-Work for this section initiates here */
		// Obtaining the Transaction Id
		$dbBasicInfo = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid',
							'tx_netzrezepteshop_basic',
							"ret_booknr='" . t3lib_div::_POST('ret_booknr') . "'"
						) or die('Error-Line ' . __LINE__ . ' : ' . $GLOBALS['TYPO3_DB']->sql_error());

		if($rdbBasicInfo = $GLOBALS['TYPO3_DB']->sql_fetch_row($dbBasicInfo)) {
			$basicId = $rdbBasicInfo[0];
		}

		if ($basicId > 0) {
			// Obtaining the list of items in transaction
			$dbCartTemp = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'*',
							'tx_netzrezepteshop_details',
							"info_id='" . $basicId . "' AND deleted=0 AND hidden=0"
						)or die('Error-Line 1186 : ' . $GLOBALS['TYPO3_DB']->sql_error());

			$purchaseOptions = '';
			while($rCartTemp = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCartTemp)) {
				if($rCartTemp['product_type'] == 21) {
						if($rCartTemp['published'] == '0'){
							if($_SESSION['articleId'] == '') {
								$_SESSION['articleId'] = $rCartTemp['product_id'];
							}
							else {
								$_SESSION['articleId'] .= ',' . $rCartTemp['product_id'];
							}
							$templateHeader['unpublished_article_download'] = $this->cObj->getSubpart($this->templateCode,'###UNPUBLISHED_ARTICLE_DOWNLOAD###');
							$marksInner['###ARTICLE_DOWNLOAD_TITLE###'] = $rCartTemp['product_title'];
							$marksInner['###PAGE_ID###'] = $this->pid;
							$marksInner['###ARTICLE_DOWNLOAD_ID###'] = $rCartTemp['product_id'];
							if(t3lib_div::_GET('L') != '') $marksInner['###ARTICLE_DOWNLOAD_ID###'] .= '&L=' . t3lib_div::_GET('L');
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['unpublished_article_download'], $marksInner);

						} else {
							if($_SESSION['articleId'] == '') {
								$_SESSION['articleId'] = $rCartTemp['product_id'];
							}
							else {
								$_SESSION['articleId'] .= ',' . $rCartTemp['product_id'];
							}
							$templateHeader['article_download'] = $this->cObj->getSubpart($this->templateCode,'###ARTICLE_DOWNLOAD###');
							$marksInner['###ARTICLE_DOWNLOAD_TITLE###'] = $rCartTemp['product_title'];
							$marksInner['###PAGE_ID###'] = $this->pid;
							$marksInner['###ARTICLE_DOWNLOAD_ID###'] = $rCartTemp['product_id'];
							if(t3lib_div::_GET('L') != '') {
								$marksInner['###ARTICLE_DOWNLOAD_ID###'] .= '&L=' . t3lib_div::_GET('L');
							}
							$marksInner['###ALTERNATE_LANG_DL###'] = $rCartTemp['alt_lang'];
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['article_download'], $marksInner);
						}
					}
					elseif($rCartTemp['product_type'] >= 31) {
							$templateHeader['subscribed_journal'] = $this->cObj->getSubpart($this->templateCode,'###SUBSCRIBED_JOURNAL###');
							$marksInner['###SUBSCRIPTION_TITLE###'] = $rCartTemp['product_title'];

							$dbUserInfo = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
											'username, password',
											'fe_users',
											"uid=" . intval($GLOBALS['TSFE']->fe_user->user['uid'])
										)or die('Error-Line 1186 : ' . $GLOBALS['TYPO3_DB']->sql_error());

							if($rUserInfo = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbUserInfo)) {
								$marksInner['###ACCESS_USERNAME###'] = $rUserInfo['username'];
								$marksInner['###ACCESS_PASSWORD###'] = $rUserInfo['password'];
							}
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['subscribed_journal'], $marksInner);

							$dbJournal = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
											'journal_id',
											'tx_datamasters_products',
											"uid=" . $rCartTemp['product_id']
										)or die('Error-Line 1186 : ' . $GLOBALS['TYPO3_DB']->sql_error());

							if($rJournal = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbJournal))
								$journalId = $rJournal['journal_id'];
							// Obtaining global vars and current date
							$dateArr = explode('-', date('Y-m-d'));
							$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['journal_subscription']);

							// Obtaining expiry date of subscriptions
							if($rCartTemp['product_type'] == 31) {
								$dateExpiry = date('Y-m-d', mktime(0, 0, 0, $dateArr[1], ($dateArr[2] + intval($_extConfig['validityAllVolumes'])), $dateArr[0]));
							}
							else {
								$dateExpiry = date('Y-m-d', mktime(0, 0, 0, $dateArr[1], ($dateArr[2] + intval($_extConfig['validityCurVolume'])), $dateArr[0]));
							}

							// Obtaining Volume Info
							$dbProduct = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
											'journal_id',
											'tx_datamasters_products',
											"uid=" . $rCartTemp['product_id']
							)or die('Error-Line 1254 : ' . $GLOBALS['TYPO3_DB']->sql_error());


							if($rProduct = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbProduct)) {
								$journalId = $rProduct['journal_id'];

								$dbVolume = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
												'uid',
												'tx_datamasters_journal_volumes',
												"journal_id=" . $rProduct['journal_id'] . " AND current_volume=1"
											)or die('Error-Line 1264 : ' . $GLOBALS['TYPO3_DB']->sql_error());


								if($rVolume = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbVolume))
									$volumeId = $rVolume['uid'];
							}

							// Obtaining Subscription type
							if(t3lib_div::_POST('trx_currency') == 'EUR') $currency = 'euro';
							else $currency = 'dollar';
							$dbSubType = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
												"subscription_prices_" . $currency . " AS subscription_prices",
												'tx_datamasters_products',
												"uid=" . $rCartTemp['product_id']
										)or die('Error-Line 1278 : ' . $GLOBALS['TYPO3_DB']->sql_error());

							$symbolArr = array('T', 'P', 'I');
							if($rSubType = mysql_fetch_object($dbSubType)) {
								$pricesArr = explode('#', $rSubType->subscription_prices);
								for($i = 0; $i <= 2; $i ++) {
									if($rCartTemp['unit_price'] == $pricesArr[$i]) $subType = $symbolArr[$i];
								}
							}
							//check if subscription exists
							$whereClause = "deleted = 0 AND hidden = 0 AND
											user_id=" . intval($GLOBALS['TSFE']->fe_user->user['uid']) ." AND
											journal_id=" . $journalId;

							$dbSubscriptionCheck = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
														"*",
														'tx_journalsubscription_details',
														$whereClause
													)or die('Error-Line 1296 : ' . $GLOBALS['TYPO3_DB']->sql_error());



							if($GLOBALS['TYPO3_DB']->sql_num_rows($dbSubscriptionCheck) > 0) {

								$dbSubScriptId = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
													'uid, date_expiry',
													'tx_journalsubscription_details',
													"user_id = " . intval($GLOBALS['TSFE']->fe_user->user['uid']) ." AND  journal_id=" . $journalId
												)or die('Error-Line 1305 : ' . $GLOBALS['TYPO3_DB']->sql_error());

								if($rsSubscriptId = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbSubScriptId)){
									$prevExpiry = $rsSubscriptId['date_expiry'];
									$subscription_id = $rsSubscriptId['uid'];

								}
								$updateData = array();
								$updateData['volume_id'] = $volumeId;
								$updateData['product_type'] = $rCartTemp['product_type'];
								$updateData['subscription_type'] = $subType;
								$updateData['date_subscription'] = date('Y-m-d');
								$updateData['date_expiry'] = $dateExpiry;
								$updateData['ip_number'] = t3lib_div::_POST('ret_booknr');


								$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
									'tx_journalsubscription_details',
									"user_id = " . intval($GLOBALS['TSFE']->fe_user->user['uid']) ." AND  journal_id=" . $journalId,
									$updateData
								)or die('Error-shop_temp-update Line-1326: ' . $GLOBALS['TYPO3_DB']->sql_error());

							} else {
								$insertData 					= array();
								$insertData['pid'] 				=  39;
								$insertData['tstamp'] 			= time();
								$insertData['cruser_id'] 		= intval($GLOBALS['TSFE']->fe_user->user['uid']);
								$insertData['journal_id'] 		= $journalId;
								$insertData['volume_id']		= $volumeId;
								$insertData['product_type']		= $rCartTemp['product_type'];
								$insertData['subscription_type']= $subType;
								$insertData['date_subscription']= date('Y-m-d');
								$insertData['date_expiry'] 		= $dateExpiry;
								$insertData['ip_number'] 		= t3lib_div::_POST('ret_booknr');

								$GLOBALS['TYPO3_DB']->exec_INSERTquery(
									'tx_journalsubscription_details',
									$insertData
								) or die('Error-Line 1337: ' . $GLOBALS['TYPO3_DB']->sql_error());

								$subscription_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
								$prevExpiry = date("Y-m-d");
							}
							$insertData 					= array();
							$insertData['pid'] 				= 39;
							$insertData['hidden'] 			= 0;
							$insertData['tstamp'] 			= time();
							$insertData['deleted'] 			= 0;
							$insertData['subscription_id'] 	= $subscription_id;
							$insertData['previous_expiry'] 	= $prevExpiry;
							$insertData['new_expiry_date'] 	= $dateExpiry;
							$insertData['date_today'] 		= date("Y-m-d");

							$GLOBALS['TYPO3_DB']->exec_INSERTquery(
									'tx_managesubscription_gensub_log',
									$insertData
							) or die('Error-Line 1357: ' . $GLOBALS['TYPO3_DB']->sql_error());


					}
					elseif($rCartTemp['product_type'] == 04) {
							if (empty($rCartTemp['ebook'])) {
								$templateHeader['online_book_purchase'] = $this->cObj->getSubpart($this->templateCode,'###ONLINE_BOOK_PURCHASE###');
								$marksInner['###BOOK_TITLE###'] = $rCartTemp['product_title'];
								$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['online_book_purchase'], $marksInner);
							}
							else { // Dustri Book (ebook)
								$templateHeader['dustri_ebook'] = $this->cObj->getSubpart($this->templateCode,'###DUSTRI_EBOOK###');
								$marksInner['###BOOK_TITLE###'] = $rCartTemp['product_title'];
								$marksInner['###PAGE_ID###'] = $this->pid;
								$marksInner['###BOOK_ARTICLE_DOWNLOAD_ID###'] = $rCartTemp['product_id'];
								if(t3lib_div::_GET('L') != '') 
									$marksInner['###BOOK_ARTICLE_DOWNLOAD_ID###'] .= '&L=' . t3lib_div::_GET('L');
								$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['dustri_ebook'], $marksInner);
								// Dustri Book (ebook)
							}
					}
					elseif($rCartTemp['product_type'] == 02) {
							$templateHeader['issue_purchase'] = $this->cObj->getSubpart($this->templateCode,'###ISSUE_PURCHASE###');
							$marksInner['###ISSUE_TITLE###'] = $rCartTemp['product_title'];
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['issue_purchase'], $marksInner);
					}
					elseif($rCartTemp['product_type'] == 24) {
							$templateHeader['volume_purchase'] = $this->cObj->getSubpart($this->templateCode,'###VOLUME_PURCHASE###');
							$marksInner['###VOLUME_TITLE###'] = $rCartTemp['product_title'];
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['volume_purchase'], $marksInner);
					}
					elseif($rCartTemp['product_type'] == 05) {
							$templateHeader['online_book_other_publishers'] = $this->cObj->getSubpart($this->templateCode,'###ONLINE_BOOK_OTHER_PUBLISHERS###');
							$marksInner['###OTHER_BOOK_TITLE###'] = $rCartTemp['product_title'];
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['online_book_other_publishers'], $marksInner);
					}
					elseif($rCartTemp['product_type'] == 22) {
							$templateHeader['online_book_article'] = $this->cObj->getSubpart($this->templateCode,'###ONLINE_BOOK_ARTICLE###');
							$marksInner['###BOOK_ARTICLE###'] = $rCartTemp['product_title'];
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['online_book_article'], $marksInner);
					}
					elseif($rCartTemp['product_type'] == 23) {
							$templateHeader['online_fortsetzungswerk'] = $this->cObj->getSubpart($this->templateCode,'###ONLINE_FORTSETZUNGSWERK###');
							$marksInner['###FORTSETZUNGSWERK###'] = $rCartTemp['product_title'];
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['online_fortsetzungswerk'], $marksInner);
					}
					elseif($rCartTemp['product_type'] == 7) {
							$templateHeader['online_others'] = $this->cObj->getSubpart($this->templateCode,'###ONLINE_OTHERS###');
							$marksInner['###OTHERS###'] = $rCartTemp['product_title'];
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['online_others'], $marksInner);
					}
					elseif($rCartTemp['product_type'] == 25) {
							$templateHeader['online_book'] = $this->cObj->getSubpart($this->templateCode,'###ONLINE_BOOK###');
							$marksInner['###ONLINE_BOOK_DOWNLOAD_TITLE###'] = $rCartTemp['product_title'];
							$marksInner['###PAGE_ID###'] = $this->pid;
							$marksInner['###ONLINE_BOOK_DOWNLOAD_ID###'] = $rCartTemp['product_id'];
							if(t3lib_div::_GET('L') != '') $marksInner['###ONLINE_BOOK_DOWNLOAD_ID###'] .= '&L=' . t3lib_div::_GET('L');
							$purchaseOptions .= $this->cObj->substituteMarkerArrayCached($templateHeader['online_book'], $marksInner);
					}
					else {
						//$purchaseOptions .= 'Unknown';
					}
				}
				
				
			$invoiceFile = $this->createInvoice($basicId, $_POST['trx_paymenttyp']);
		}

		/* Code Re-Work for this section ends here */

		/* Start of displaying invoice download link */
		$invoiceDownloadBlock = '';
		if (!empty($invoiceFile)) {
			$templateHeader['invoice_download'] = $this->cObj->getSubpart($this->templateCode,'###INVOICE_DOWNLOAD###');
			$marksInner['###INVOICE_FILE###'] = $invoiceFile;
			$marksInner['###DOWNLOAD_INVOICE###'] = $this->pi_getLL('download_invoice');
			$marksInner['###DOWNLOAD_INVOICE_TEXT###'] = $this->pi_getLL('download_invoice_text');
			$invoiceDownloadBlock = $this->cObj->substituteMarkerArrayCached($templateHeader['invoice_download'], $marksInner);
		}
		$markerArray['###DOWNLOAD_INVOICE###'] = $invoiceDownloadBlock;
		/* End of displaying invoicload link */
		
		$markerArray['###PURCHASE_OPTIONS###'] = $purchaseOptions;
		$markerArray['###IMAGE_PAY_STAGE5###'] = $this->pi_getLL('image_pay_stage5');
		$markerArray['###CHECKOUT_HEADER###'] = $this->pi_getLL('CHECKOUT_HEADER');
		$markerArray['###CHECKOUT_THANKS_MESSAGE###'] = $this->pi_getLL('CHECKOUT_THANKS_MESSAGE');
		
		//$markerArray['###CHECKOUT_LINE1###'] = sprintf($this->pi_getLL('CHECKOUT_LINE1'), '<a href="mailto:'.$this->customerServiceEmailAddress.'">'.$this->customerServiceEmailAddress.'</a>');
		$dbCustInfo = $GLOBALS['TYPO3_DB']->exec_SELECTquery('b.customer_id AS customerId,b.email', 'tx_netzrezepteshop_basic a, fe_users b', "a.cruser_id=b.uid AND a.uid=" . $basicId) or die('Error-Fetch Cust Info: ' . mysql_error());
		$markerArray['###CHECKOUT_LINE1###']='';
		if($rdbCustInfo = mysql_fetch_row($dbCustInfo)){ 
			$markerArray['###CHECKOUT_CUSTOMER_NUMBER###'] = $this->pi_getLL('CHECKOUT_CUSTOMER_NUMBER') . $rdbCustInfo[0];
			$markerArray['###CHECKOUT_LINE1###'] = sprintf($this->pi_getLL('CHECKOUT_LINE1'), '<a href="mailto:'.$rdbCustInfo[1].'">'.$rdbCustInfo[1].'</a>');
		}
		$markerArray['###CHECKOUT_HEADLINE_ORDER_DETAILS###'] = $this->pi_getLL('CHECKOUT_HEADLINE_ORDER_DETAILS');
		$markerArray['###CHECKOUT_INVOICE_NUMBER###'] = $this->pi_getLL('CHECKOUT_INVOICE_NUMBER') . $basicId;

		$markerArray['###CHECKOUT_ORDER_DATE###'] = $this->pi_getLL('CHECKOUT_ORDER_DATE') . date('d.m.Y');
		$markerArray['###CHECKOUT_HEADLINE_CONTACT_OPT###'] = $this->pi_getLL('CHECKOUT_HEADLINE_CONTACT_OPT');
		$markerArray['###CHECKOUT_CONTACT_TEL_NO###'] = $this->dustriContacttelephoneNo;
		$markerArray['###CHECKOUT_CONTACT_EMAIL###'] = $this->dustriContactEmailAddress;
		return $this->cObj->substituteMarkerArrayCached($templateHeader['checkout_complete'], $markerArray);
	}


	/**
	 * The articleDownLoader method is used for downloading article after compleated online payment.
	 *
	 *
	 * @param is not used
	 *
	 * @return
	 */

	function articleDownLoader() {

		$dbArticleFile = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'upload_file, upload_file',
							'tx_datamasters_products',
							"uid=" . t3lib_div::_GET('articleId')
						)or die('Error-Line 1305 : ' . $GLOBALS['TYPO3_DB']->sql_error());


		if($rArticleFile = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbArticleFile)) {
			$articleDlArr = explode(',', $_SESSION['articleId']);
			if(in_array(t3lib_div::_GET('articleId'), $articleDlArr)) {
				if(t3lib_div::_GET('alt_lang') == 1) $artFile = $rArticleFile['upload_file2'];
				else $artFile = $rArticleFile['upload_file'];
				if(file_exists('uploads/repository/21/' . $artFile))	{
					header("Cache-Control: private");
					header("Content-Type: application/octet-stream");
					header("Content-Type: application/force-download");
					header("Content-Type: application/pdf");
					header("Content-Transfer-Encoding: Binary");
					header("Content-length: ".filesize('uploads/repository/21/' . $artFile));
					header("Content-disposition: attachment; filename=\"".basename('uploads/repository/21/' . $artFile)."\"");
					ob_clean();
					flush();
					readfile('uploads/repository/21/' . $artFile);
					exit;
				}
				else return "file not found: " . $file;
			}
			else return 'Technical error. Please contact the administrator of this website.<br />Sorry for the inconvenience';
		}
	}

	/**
	 * The bookDownLoader method is used for downloading online book after compleated online payment.
	 *
	 *
	 * @param is not used
	 *
	 * @return
	 */

	function bookDownLoader() {
		$dbBookFile = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'upload_file2',
						'tx_datamasters_products',
						"uid=" . t3lib_div::_GET('bookId')
					)or die('Error-Line 1576 : ' . $GLOBALS['TYPO3_DB']->sql_error());

		if($rBookFile = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbBookFile)) {

				if(file_exists('uploads/repository/25/' . $rBookFile['upload_file2']))	{
					header("Content-type: application/force-download");
					header("Cache-Control: private");
					header("Content-Transfer-Encoding: Binary");
					header("Content-length: ".filesize('uploads/repository/25/' . $rBookFile['upload_file2']));
					header("Content-disposition: attachment; filename=\"". basename('uploads/repository/25/' . $rBookFile['upload_file2'])."\"");
					ob_clean();
					flush();
					readfile('uploads/repository/25/' . $rBookFile['upload_file2']);
					exit;
				}
				else return "file not found: " . $file;
		}

	}

	// Dustri Book (ebook)
	function ebookDownLoader() {
		$dbBookFile = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'ebook, book_id',
				'tx_datamasters_products',
				"uid=" . t3lib_div::_GET('ebookId')
		)or die('Error-Line '. __LINE__ .' : ' . $GLOBALS['TYPO3_DB']->sql_error());
		
		if($rBookFile = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbBookFile)) {
			if (empty($rBookFile['book_id'])) {
				$source_path = $this->dustribook_ebook_path . $rBookFile['ebook'];
			}
			else {
				$source_path = $this->chapter_ebook_path . $rBookFile['ebook'];
			}
			$ebook = $source_path . $rBookFile['ebook'];
			if(file_exists($source_path))	{
				header("Content-type: application/force-download");
				header("Cache-Control: private");
				header("Content-Transfer-Encoding: Binary");
				header("Content-length: ".filesize($source_path));
				header("Content-disposition: attachment; filename=\"". basename($source_path)."\"");
				ob_clean();
				flush();
				readfile($source_path);
				exit;
			}
			else return "file not found: " . $file;
		}
	}


	function unpublishArticleDownLoader(){
		$dbArticleFile = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'upload_file',
							'tx_futurearticlelist_products',
							"uid=" . t3lib_div::_GET('unpub_articleId')
						)or die('Error-Line 1576 : ' . $GLOBALS['TYPO3_DB']->sql_error());


		if($rArticleFile = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbArticleFile)) {
			$articleDlArr = explode(',', $_SESSION['articleId']);
			if(in_array(t3lib_div::_GET('unpub_articleId'), $articleDlArr)) {
				if(file_exists('uploads/future_articles/' . $rArticleFile['upload_file']))	{
					header("Content-type: application/force-download");
					header("Cache-Control: private");
					header("Content-Transfer-Encoding: Binary");
					header("Content-length: ".filesize('uploads/future_articles/' . $rArticleFile['upload_file']));
					header("Content-disposition: attachment; filename=\"".basename('uploads/future_articles/' . $rArticleFile['upload_file'])."\"");
					ob_clean();
					flush();
					readfile('uploads/future_articles/' . $rArticleFile['upload_file']);
					exit;
				}
				else return "file not found: " . $file;
			}
			else return 'Technical error. Please contact the administrator of this website.<br />Sorry for the inconvenience';
		}
	}

	/**
	 * The sendMail method is used for the mail which is attached invoice is sending to the customer.
	 *
	 *
	 * @param $basicId which is the shopping_id(unique id)
	 *
	 * @return
	 */
	function sendMail($basicId, $invoiceFilePath, $mode='ccPaypal') {
		
		$online_product_type = array(25,21,31,22);


		// Get billing information
		if ($mode != 'manbo') {
			$dbBilling = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'billing_email, billing_first_name, billing_last_name',
							'tx_netzrezepteshop_billing',
							"shopping_id = ".$basicId
						)or die($GLOBALS['TYPO3_DB']->sql_error());
		
			if ($recBilling = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbBilling))	{
				$emailId = $recBilling['billing_email'];
				$usrName = stripslashes($recBilling['billing_first_name']).'_'.stripslashes($recBilling['billing_last_name']);
				$customerName = $usrName;
			}
		}
		else {
			$dbBilling 	= $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'first_name, last_name, email', 'fe_users', 'uid="'.$GLOBALS['TSFE']->fe_user->user['uid'] .'"'
						) or die($GLOBALS['TYPO3_DB']->sql_error());
			$recBilling = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbBilling);
			$emailId = $recBilling['email'];
			$customerName = stripslashes($recBilling['first_name']).' '.stripslashes($recBilling['last_name']);
			$customerName = html_entity_decode($customerName, ENT_QUOTES, "UTF-8");
		}
		// Get shop basic information	
		$dbshop = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'trx_paymenttyp',
									'tx_netzrezepteshop_basic',
									"uid=" . $basicId
							) or die( $GLOBALS['TYPO3_DB']->sql_error());	
		
		if ($shopRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbshop)) {
			$paymentType = $shopRow['trx_paymenttyp'];
		}	
		
		// Get purchased product details
		$dbArticle = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'product_id, product_type',
									'tx_netzrezepteshop_details',
									"info_id=" . $basicId
					) or die($GLOBALS['TYPO3_DB']->sql_error());
		$checkmeno = $GLOBALS['TYPO3_DB']->sql_num_rows($dbArticle);
		$send_order_email = false;
		while ($resArticle = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbArticle)) {

			$count = 1;
			$articleProd = $resArticle['product_type'];

			if(!in_array($articleProd,$online_product_type)){
				$send_order_email = true;
			}

			if ($paymentType == 'cc' || $paymentType == 'paypal') {
				if($articleProd == 21) {
				//Article Attachment-start
				$dbArticleAttach = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
										'upload_file',
										'tx_datamasters_products',
										"uid=" . $resArticle['product_id']
									) or die($GLOBALS['TYPO3_DB']->sql_error());
				
					if($rArticleAttach = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbArticleAttach)) {	
						if($GLOBALS['TYPO3_DB']->sql_num_rows($dbArticle) == $count)	
							$attachmentFile .=  $rArticleAttach['upload_file'];
						else  $attachmentFile .=  $rArticleAttach['upload_file'].'#';	
					}
				}	
					//Article Attachment-end
				elseif($articleProd == 25) {
						//Article Attachment-start
						$dbArticleAttach = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
										'upload_file2',
										'tx_datamasters_products',
										"uid=" . $resArticle['product_id']
								) or die($GLOBALS['TYPO3_DB']->sql_error());
						
				
					if($rArticleAttach = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbArticleAttach)) {	
						if($GLOBALS['TYPO3_DB']->sql_num_rows($dbArticle) == $count)	$attachmentFile .=  $rArticleAttach['upload_file2'];
						else  $attachmentFile .=  $rArticleAttach['upload_file2'].'#';	
					}
					//Article Attachment-end			
				} 
			}
		}
		if($checkmeno <= 0){
			$send_order_email = true;	
		}
		
		// replacing existing mime mail
		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$recipient = array($emailId => $customerName);
		$sender = array('no-reply@dustri.com' => 'Dustri-Verlag');
		
		
		if($paymentType == 'manbo' || $mode == 'manbo')	{

		$pay_link = "";
		$db_basic = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_netzrezepteshop_man_basic',
			"uid=" . $basicId
		) or die( $GLOBALS['TYPO3_DB']->sql_error());	
		if ($man_basic_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($db_basic)) {
			$trx_amount = $man_basic_row['trx_amount'];
			$trx_currency = $man_basic_row['trx_currency'];
			$trx_amount = ($trx_amount/100);
			$mcode = $trx_amount.",".$trx_currency.",Proforma Invoice: ".$basicId;
			$mcode = base64_encode($mcode);

			$pay_link = "https://www.dustri.com/index.php?id=".$this->conf['paymentURLHandlerPageId']."&no_cache=1&mcode=".$mcode;
			//$pay_link = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $url;
		}

		if(t3lib_div::_GET('L')==1){
			$subject = 'Bestellbestätigung';
	$body = "<p>Ihre Bestellung ist eingegangen!<br>Sehr geehrte Kundin, sehr geehrter Kunde,<br>Ihre Bestellung mit der Vorausrechnung ".$basicId." ist bei uns eingegangen. Die Lieferung erfolgt nach Erhalt der Zahlung.<br>Sollten Sie sich nachträglich für eine Online-Zahlung entscheiden, folgen Sie bitte folgendem Link: <a href=\"".$pay_link."\">Jetzt Bezahlen</a><br>In dieser E-Mail erhalten Sie zu Ihrer Übersicht die Rechnung sowie unsere Allgemeinen Geschäftsbedingungen und die Widerrufsbelehrung.<br>Bei Fragen senden Sie uns bitte eine E-mail an order@dustri.de.<br>Bitte beachten Sie, dass diese E-Mail noch keine Annahmeerklärung darstellt, sondern lediglich den Eingang Ihrer Bestellung bestätigt.<br>Weitere Informationen erhalten Sie in unseren Allgemeinen Geschäftsbedingungen.<br><br><b>Widerrufsbelehrung</b><br>
==================<br>
<b>Widerrufrecht</b><br>
Sie haben das Recht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen.<br>
Die Widerrufsfrist beträgt vierzehn Tage ab dem Tag, an dem Sie oder ein von Ihnen benannter Dritter, der nicht der Beförderer ist, die letzte Ware in Besitz genommen haben bzw. hat. <br><br>
Um Ihr Widerrufsrecht auszuüben, müssen Sie uns, der Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG, Bajuwarenring 4, D-82041 Oberhaching, Tel. +49/89/6138610, E-mail: order@dustri.de, mittels eindeutiger Erklärung (z.B. ein mit der Post versandter Brief, Telefax oder E-Mail) über Ihren Entschluss, diesen Vertrag zu widerrufen, informieren. Sie können dafür das beigefügte Muster-Widerrufsformular verwenden, das jedoch nicht vorgeschrieben ist.<br>
Zur Wahrung der Widerrufsfrist reicht es aus, dass Sie die Mitteilung über die Ausübung des Widerrufsrechts vor Ablauf der Widerrufsfrist absenden.<br><br>
<b>Widerrufsfolgen</b><br><br>
Wenn Sie diesen Vertrag widerrufen, haben wir Ihnen alle Zahlungen, die wir von Ihnen erhalten haben, einschließlich der Lieferkosten (mit Ausnahme der zusätzlichen Kosten, die sich daraus ergeben, dass Sie eine andere Art der Lieferung als die von uns angebotene, günstigste Standardlieferung gewählt haben), unverzüglich und spätestens binnen vierzehn Tagen ab dem Tag zurückzuzahlen, an dem die Mitteilung über Ihren Widerruf dieses Vertrags bei uns eingegangen ist. Für diese Rückzahlung verwenden wir dasselbe Zahlungsmittel, das Sie bei der ursprünglichen Transaktion eingesetzt haben, es sei denn, mit Ihnen wurde ausdrücklich etwas anderes vereinbart; in keinem Fall werden Ihnen wegen dieser Rückzahlung Entgelte berechnet. Wir können die Rückzahlung verweigern, bis wir die Waren wieder zurückerhalten haben oder bis Sie den Nachweis erbracht haben, dass Sie die Waren zurückgesandt haben, je nachdem, welches der frühere Zeitpunkt ist.<br><br>
Sie haben die Waren unverzüglich und in jedem Fall spätestens binnen vierzehn Tagen ab dem Tag, an dem Sie uns über den Widerruf dieses Vertrags unterrichten, an uns zurückzusenden oder zu übergeben. Die Frist ist gewahrt, wenn Sie die Waren vor Ablauf von vierzehn Tagen absenden. Sie tragen die unmittelbaren Kosten der Rücksendung der Waren. Sie müssen für einen etwaigen Wertverlust der Waren nur aufkommen, wenn dieser Wertverlust auf einen zur Prüfung der Beschaffenheit, Eigenschaften und Funktionsweise der Waren nicht notwendigen Umgang mit ihnen zurückzuführen ist.<br><br>
Ende der Widerrufsbelehrung<br><br>
****************************************************************************************************<br><br>
§7 Widerrufsformular<br><br>
Muster-Widerrufsformular<br>
(Wenn Sie den Vertrag widerrufen wollen, dann füllen Sie bitte dieses Formular aus und senden Sie es zurück.)<br><br>
An :<br>
Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG<br>
Frank Feistle, Gerhard Feistle, Jörg Feistle<br>
Bajuwarenring 4<br>
D-82041 Oberhaching<br>
E-Mail info@dustri.de<br><br>
Hiermit widerrufe(n) ich/wir (*) den von mir/uns (*) abgeschlossenen Vertrag über den Kauf der folgenden Waren (*)/die Erbringung der folgenden Dienstleistung (*)<br><br>
_____________________________________________________<br>
Bestellt am (*)/erhalten am (*)<br><br>
_____________________________________________________<br>
Name des/der Verbraucher(s)<br><br>
_____________________________________________________<br>
Anschrift des/der Verbraucher(s)<br><br>
_____________________________________________________<br>
Unterschrift des/der Verbraucher(s) (nur bei Mitteilung auf Papier)<br><br>
__________________<br>
Datum<br><br>
__________________________________________________________________________________<br>
(*) Unzutreffendes streichen.<br><br>
§8 Gewährleistung <br>
Es gelten die gesetzlichen Gewährleistungsregelungen. <br><br>
§9 Vertragssprache<br>
Als Vertragssprache steht ausschließlich Deutsch zur Verfügung.<br><br>
****************************************************************************************************<br><br><br>
<b>Allgemeine Geschäftsbedingungen der Firma Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG</b><br><br>
§ 1 Geltungsbereich<br>
Für die Geschäftsbeziehung zwischen der der Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG  (im Folgenden: Verkäufer) und dem Besteller gelten die nachfolgenden Allgemeinen Geschäftsbedingungen. <br><br>
§ 2 Bestellung<br>
Sie haben die Möglichkeit, unser Angebot in aller Ruhe zu durchstöbern. Wenn Sie etwas Interessantes entdeckt haben, legen Sie das Produkt in Ihren virtuellen Warenkorb. Dazu klicken Sie auf das Feld „In den Warenkorb“. Eine neue Seite öffnet, hier klicken Sie auf „select“ und wählen die benötigte Anzahl. Danach klicken Sie unten auf der Seite auf „Ihr Warenkorb“. Selbstverständlich haben Sie die Möglichkeit, Produkte jederzeit wieder aus Ihrem Warenkorb herauszunehmen.<br><br>
Nun klicken Sie auf „weiter“. Sie werden hierauf gebeten, sich mit Ihren Nutzerdaten anzumelden bzw. als Neukunde ein Nutzerkonto einzurichten. Sind Sie im System angemeldet, werden Ihnen die Produkte angezeigt, die Sie in den Warenkorb gelegt haben. Sie haben hier die Möglichkeit, Produkte wieder aus dem Warenkorb zu entfernen, indem Sie auf „Ihr Warenkorb“ klicken. Sie erhalten hier eine Übersicht über sämtliche Produkte, die Sie in den Warenkorb gelegt haben, sowie übersämtliche anderen etwaigen Kosten, die im Zusammenhang mit Ihrer Bestellung anfallen.<br>
Ist nun alles in Ordnung, klicken Sie auf „weiter“.  Nun bestätigen Sie Ihre Adressdaten, indem Sie auf „bestätigen“ klicken. Haben Sie hier Änderungswünsche, klicken Sie auf „Profil bearbeiten“.<br>
Vor dem Absenden Ihrer Bestellung durch die Schaltfläche „Kostenpflichtig bestellen“ haben Sie in jeder Stufe des Bestellprozesses die Möglichkeit, Ihre Angaben durch Klicken der Schaltflächen „Änderungen bestätigen“ bzw. „Löschen bestätigen“ zu korrigieren. Zum Ändern bzw. Löschen der Bestellmenge überschreiben Sie die Mengenangabe im entsprechenden Feld und klicken dann auf „Änderungen bestätigen“.<br>
Nun können Sie die Zahlungsmöglichkeiten auswählen. Wollen Sie online bezahlen oder per Rechnung? Dann klicken Sie auf die entsprechende Option.
Durch Betätigen der Schaltfläche „Kostenpflichtig bestellen“ wird Ihre Bestellung ausgelöst und an uns weitergeleitet. Mit Ihrer Bestellung erklären Sie verbindlich, die Ware erwerben zu wollen.<br><br>
§ 3 Vertragsschluss<br>
Bei einer Bestellung schließen Sie einen Vertrag mit: Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG,  Bajuwarenring 4, 82041 Oberhaching, Deutschland.<br><br>
Sie werden über den Eingang Ihrer Bestellung umgehend mit einer als „Bestellbestätigung“ bezeichneten E-Mail informiert. Die Bestellbestätigung erfolgt automatisch und stellt noch keine Vertragsannahme dar. Der Vertrag über den Erwerb eines Produktes kommt dadurch zustande, dass wir die Annahme Ihrer Bestellung und damit den Abschluss des Vertrages in einer weiteren als „Auftragsbestätigung“ bezeichneten E-Mail bestätigen. Diese Nachricht erhalten Sie unverzüglich, spätestens jedoch 3 Tage nachdem Ihre Bestellung bei uns eingegangen ist.<br>
Der Vertragstext einschließlich Ihrer Bestellung wird von uns gespeichert und wird Ihnen auf Wunsch zusammen mit unseren Allgemeinen Geschäftsbedingungen zugesandt.<br>
Die für den Vertragsschluss zur Verfügung stehende Sprache ist Deutsch.<br><br>
§ 4 Vorbehalt der Nichtverfügbarkeit<br>
Wir behalten uns vor, von einer Ausführung Ihrer Bestellung abzusehen, wenn wir den bestellten Titel nicht vorrätig haben, der nicht vorrätige Titel beim Verlag vergriffen und die bestellte Ware infolgedessen nicht verfügbar ist. In diesem Fall werden wir Sie unverzüglich über die Nichtverfügbarkeit informieren und einen gegebenenfalls von Ihnen bereits gezahlten Kaufpreis unverzüglich rückerstatten.<br><br>
§ 5 Versandkosten<br>
Bücher liefern wir an Privatkunden versandkostenfrei. Bei Zeitschriften werden Versandkosten erhoben.<br><br>
§ 6 Zahlung<br>
Sofern keine Vorauszahlung erfolgt ist, ist der Kaufpreis fällig und ohne Abzug zahlbar innerhalb von 30 Tagen nach Rechnungsstellung. Zahlen Sie bitte spätestens 30 Tage nach Erhalt der Ware auf das in der Rechnung angegebene Konto.<br>
Sie können mittels Kreditkarte, durch Überweisung, im Lastschriftverfahren, per Paypal bezahlen.<br><br>
§ 7 Eigentumsvorbehalt<br>
Bis zur vollständigen Erfüllung der Kaufpreisforderung durch den Besteller verbleibt die gelieferte Ware im Eigentum des Verkäufers.<br><br>
§ 8 Gewährleistung<br>
Es gilt das gesetzliche Mängelhaftungsrecht.<br><br>
§ 9 Gerichtsstand<br>
Ist der Besteller Kaufmann oder juristische Person des öffentlichen Rechts, ist ausschließlicher Gerichtsstand für alle Streitigkeiten aus dem Vertragsverhältnis das für unseren Firmensitz [München] zuständige Gericht.
</p>";
		
		}
		else{
			$subject = 'Order Confirmation';
			$body = "<p>Thank you for your order!,<br>Dear Customer,<br>We have received your order. Please find attached the proforma invoice ".$basicId.". Delivery will take place after we have received your payment.<br>If you later opt for online payment, please use the following link: <a href=\"".$pay_link."\">Pay Now</a>.<br>In case of any further questions, please contact order@dustri.com.<br><br><b>Widerrufsbelehrung</b><br>
==================<br>
<b>Widerrufrecht</b><br>
Sie haben das Recht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen.<br>
Die Widerrufsfrist beträgt vierzehn Tage ab dem Tag, an dem Sie oder ein von Ihnen benannter Dritter, der nicht der Beförderer ist, die letzte Ware in Besitz genommen haben bzw. hat. <br><br>
Um Ihr Widerrufsrecht auszuüben, müssen Sie uns, der Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG, Bajuwarenring 4, D-82041 Oberhaching, Tel. +49/89/6138610, E-mail: order@dustri.de, mittels eindeutiger Erklärung (z.B. ein mit der Post versandter Brief, Telefax oder E-Mail) über Ihren Entschluss, diesen Vertrag zu widerrufen, informieren. Sie können dafür das beigefügte Muster-Widerrufsformular verwenden, das jedoch nicht vorgeschrieben ist.<br>
Zur Wahrung der Widerrufsfrist reicht es aus, dass Sie die Mitteilung über die Ausübung des Widerrufsrechts vor Ablauf der Widerrufsfrist absenden.<br><br>
<b>Widerrufsfolgen</b><br><br>
Wenn Sie diesen Vertrag widerrufen, haben wir Ihnen alle Zahlungen, die wir von Ihnen erhalten haben, einschließlich der Lieferkosten (mit Ausnahme der zusätzlichen Kosten, die sich daraus ergeben, dass Sie eine andere Art der Lieferung als die von uns angebotene, günstigste Standardlieferung gewählt haben), unverzüglich und spätestens binnen vierzehn Tagen ab dem Tag zurückzuzahlen, an dem die Mitteilung über Ihren Widerruf dieses Vertrags bei uns eingegangen ist. Für diese Rückzahlung verwenden wir dasselbe Zahlungsmittel, das Sie bei der ursprünglichen Transaktion eingesetzt haben, es sei denn, mit Ihnen wurde ausdrücklich etwas anderes vereinbart; in keinem Fall werden Ihnen wegen dieser Rückzahlung Entgelte berechnet. Wir können die Rückzahlung verweigern, bis wir die Waren wieder zurückerhalten haben oder bis Sie den Nachweis erbracht haben, dass Sie die Waren zurückgesandt haben, je nachdem, welches der frühere Zeitpunkt ist.<br><br>
Sie haben die Waren unverzüglich und in jedem Fall spätestens binnen vierzehn Tagen ab dem Tag, an dem Sie uns über den Widerruf dieses Vertrags unterrichten, an uns zurückzusenden oder zu übergeben. Die Frist ist gewahrt, wenn Sie die Waren vor Ablauf von vierzehn Tagen absenden. Sie tragen die unmittelbaren Kosten der Rücksendung der Waren. Sie müssen für einen etwaigen Wertverlust der Waren nur aufkommen, wenn dieser Wertverlust auf einen zur Prüfung der Beschaffenheit, Eigenschaften und Funktionsweise der Waren nicht notwendigen Umgang mit ihnen zurückzuführen ist.<br><br>
Ende der Widerrufsbelehrung<br><br>
****************************************************************************************************<br><br>
§7 Widerrufsformular<br><br>
Muster-Widerrufsformular<br>
(Wenn Sie den Vertrag widerrufen wollen, dann füllen Sie bitte dieses Formular aus und senden Sie es zurück.)<br><br>
An :<br>
Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG<br>
Frank Feistle, Gerhard Feistle, Jörg Feistle<br>
Bajuwarenring 4<br>
D-82041 Oberhaching<br>
E-Mail info@dustri.de<br><br>
Hiermit widerrufe(n) ich/wir (*) den von mir/uns (*) abgeschlossenen Vertrag über den Kauf der folgenden Waren (*)/die Erbringung der folgenden Dienstleistung (*)<br><br>
_____________________________________________________<br>
Bestellt am (*)/erhalten am (*)<br><br>
_____________________________________________________<br>
Name des/der Verbraucher(s)<br><br>
_____________________________________________________<br>
Anschrift des/der Verbraucher(s)<br><br>
_____________________________________________________<br>
Unterschrift des/der Verbraucher(s) (nur bei Mitteilung auf Papier)<br><br>
__________________<br>
Datum<br><br>
__________________________________________________________________________________<br>
(*) Unzutreffendes streichen.<br><br>
§8 Gewährleistung <br>
Es gelten die gesetzlichen Gewährleistungsregelungen. <br><br>
§9 Vertragssprache<br>
Als Vertragssprache steht ausschließlich Deutsch zur Verfügung.<br><br>
****************************************************************************************************<br><br><br>
<b>Allgemeine Geschäftsbedingungen der Firma Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG</b><br><br>
§ 1 Geltungsbereich<br>
Für die Geschäftsbeziehung zwischen der der Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG  (im Folgenden: Verkäufer) und dem Besteller gelten die nachfolgenden Allgemeinen Geschäftsbedingungen. <br><br>
§ 2 Bestellung<br>
Sie haben die Möglichkeit, unser Angebot in aller Ruhe zu durchstöbern. Wenn Sie etwas Interessantes entdeckt haben, legen Sie das Produkt in Ihren virtuellen Warenkorb. Dazu klicken Sie auf das Feld „In den Warenkorb“. Eine neue Seite öffnet, hier klicken Sie auf „select“ und wählen die benötigte Anzahl. Danach klicken Sie unten auf der Seite auf „Ihr Warenkorb“. Selbstverständlich haben Sie die Möglichkeit, Produkte jederzeit wieder aus Ihrem Warenkorb herauszunehmen.<br><br>
Nun klicken Sie auf „weiter“. Sie werden hierauf gebeten, sich mit Ihren Nutzerdaten anzumelden bzw. als Neukunde ein Nutzerkonto einzurichten. Sind Sie im System angemeldet, werden Ihnen die Produkte angezeigt, die Sie in den Warenkorb gelegt haben. Sie haben hier die Möglichkeit, Produkte wieder aus dem Warenkorb zu entfernen, indem Sie auf „Ihr Warenkorb“ klicken. Sie erhalten hier eine Übersicht über sämtliche Produkte, die Sie in den Warenkorb gelegt haben, sowie übersämtliche anderen etwaigen Kosten, die im Zusammenhang mit Ihrer Bestellung anfallen.<br>
Ist nun alles in Ordnung, klicken Sie auf „weiter“.  Nun bestätigen Sie Ihre Adressdaten, indem Sie auf „bestätigen“ klicken. Haben Sie hier Änderungswünsche, klicken Sie auf „Profil bearbeiten“.<br>
Vor dem Absenden Ihrer Bestellung durch die Schaltfläche „Kostenpflichtig bestellen“ haben Sie in jeder Stufe des Bestellprozesses die Möglichkeit, Ihre Angaben durch Klicken der Schaltflächen „Änderungen bestätigen“ bzw. „Löschen bestätigen“ zu korrigieren. Zum Ändern bzw. Löschen der Bestellmenge überschreiben Sie die Mengenangabe im entsprechenden Feld und klicken dann auf „Änderungen bestätigen“.<br>
Nun können Sie die Zahlungsmöglichkeiten auswählen. Wollen Sie online bezahlen oder per Rechnung? Dann klicken Sie auf die entsprechende Option.
Durch Betätigen der Schaltfläche „Kostenpflichtig bestellen“ wird Ihre Bestellung ausgelöst und an uns weitergeleitet. Mit Ihrer Bestellung erklären Sie verbindlich, die Ware erwerben zu wollen.<br><br>
§ 3 Vertragsschluss<br>
Bei einer Bestellung schließen Sie einen Vertrag mit: Dustri-Verlag Dr. Karl Feistle GmbH & Co. KG,  Bajuwarenring 4, 82041 Oberhaching, Deutschland.<br><br>
Sie werden über den Eingang Ihrer Bestellung umgehend mit einer als „Bestellbestätigung“ bezeichneten E-Mail informiert. Die Bestellbestätigung erfolgt automatisch und stellt noch keine Vertragsannahme dar. Der Vertrag über den Erwerb eines Produktes kommt dadurch zustande, dass wir die Annahme Ihrer Bestellung und damit den Abschluss des Vertrages in einer weiteren als „Auftragsbestätigung“ bezeichneten E-Mail bestätigen. Diese Nachricht erhalten Sie unverzüglich, spätestens jedoch 3 Tage nachdem Ihre Bestellung bei uns eingegangen ist.<br>
Der Vertragstext einschließlich Ihrer Bestellung wird von uns gespeichert und wird Ihnen auf Wunsch zusammen mit unseren Allgemeinen Geschäftsbedingungen zugesandt.<br>
Die für den Vertragsschluss zur Verfügung stehende Sprache ist Deutsch.<br><br>
§ 4 Vorbehalt der Nichtverfügbarkeit<br>
Wir behalten uns vor, von einer Ausführung Ihrer Bestellung abzusehen, wenn wir den bestellten Titel nicht vorrätig haben, der nicht vorrätige Titel beim Verlag vergriffen und die bestellte Ware infolgedessen nicht verfügbar ist. In diesem Fall werden wir Sie unverzüglich über die Nichtverfügbarkeit informieren und einen gegebenenfalls von Ihnen bereits gezahlten Kaufpreis unverzüglich rückerstatten.<br><br>
§ 5 Versandkosten<br>
Bücher liefern wir an Privatkunden versandkostenfrei. Bei Zeitschriften werden Versandkosten erhoben.<br><br>
§ 6 Zahlung<br>
Sofern keine Vorauszahlung erfolgt ist, ist der Kaufpreis fällig und ohne Abzug zahlbar innerhalb von 30 Tagen nach Rechnungsstellung. Zahlen Sie bitte spätestens 30 Tage nach Erhalt der Ware auf das in der Rechnung angegebene Konto.<br>
Sie können mittels Kreditkarte, durch Überweisung, im Lastschriftverfahren, per Paypal bezahlen.<br><br>
§ 7 Eigentumsvorbehalt<br>
Bis zur vollständigen Erfüllung der Kaufpreisforderung durch den Besteller verbleibt die gelieferte Ware im Eigentum des Verkäufers.<br><br>
§ 8 Gewährleistung<br>
Es gilt das gesetzliche Mängelhaftungsrecht.<br><br>
§ 9 Gerichtsstand<br>
Ist der Besteller Kaufmann oder juristische Person des öffentlichen Rechts, ist ausschließlicher Gerichtsstand für alle Streitigkeiten aus dem Vertragsverhältnis das für unseren Firmensitz [München] zuständige Gericht.
</p>";
		}
			
				
			
			if($invoiceFilePath != ''){
			      if(file_exists($invoiceFilePath)){
					$message->attach( Swift_Attachment::fromPath( $invoiceFilePath ) );
				}
			}	
		} elseif($paymentType == 'cc' || $paymentType == 'paypal') {
			
			$subject = 'Invoice - purchase';

			$body = "Please find enclosed the invoice against your purchases from Dustri Online Services";

			if($invoiceFilePath != '') {
				if(file_exists($invoiceFilePath)){
					$message->attach( Swift_Attachment::fromPath( $invoiceFilePath ) );
				}
			}
			
			if($articleProd == 21) {
				$fileattachArr = explode("#", $attachmentFile);
				for($i = 0; $i< count($fileattachArr) ; $i++) {
					$fileName = 'uploads/repository/21/' . $fileattachArr[$i];
					if(file_exists($fileName) && !is_dir($fileName)){
						$message->attach( Swift_Attachment::fromPath($fileName));
					}
				}
			}
			elseif($articleProd == 25) {
				$fileattachArr = explode("#", $attachmentFile);
				for($i = 0; $i< count($fileattachArr) ; $i++) {
					$fileName = 'uploads/repository/25/' . $fileattachArr[$i];
					if(file_exists($fileName) && !is_dir($fileName)){
						$message->attach( Swift_Attachment::fromPath($fileName));
					}
				}
			} 
		} else {
			// To do .... ???
		}
		$message->setTo($recipient)
		->setFrom($sender)
		->setSubject($subject);
		$message->setBody($body, 'text/html');
		$message->send();
		
		
		/******** Send invoice mail to dustri customer service *************/
		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$recipient = array($this->customerServiceEmailAddress => 'Dustri-Verlag Customer Service');
		$sender = array('no-reply@dustri.com' => 'Dustri-Verlag');
		$subject = "New Invoice::$basicId";
		$body = 'Hello, <br /><br />
				Please find enclosed the invoice against a new order.<br />
				<p>
				Regards,<br />
				Dustri Team,
				</p>
				<br />
				';
		$message->setTo($recipient)
		->setFrom($sender)
		->setSubject($subject);
		$message->setBody($body, 'text/html');
		if($invoiceFilePath != '') {
		      if(file_exists($invoiceFilePath)){
				$message->attach( Swift_Attachment::fromPath( $invoiceFilePath ) );
			}
		}
		$message->send();
		/******* Send invoice mail to dustri customer service *******/
		/******** Send invoice mail to dustri customer service *************/
		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$recipient = array('dustri.communication@netzrezepte.de' => 'Dustri-Verlag Customer Service');
		$sender = array('no-reply@dustri.com' => 'Dustri-Verlag');
		$subject = "New Invoice::$basicId";
		$body = 'Hello, <br /><br />
				Please find enclosed the invoice against a new order.<br />
				<p>
				Regards,<br />
				Dustri Team,
				</p>
				<br />
				';
		$message->setTo($recipient)
		->setFrom($sender)
		->setSubject($subject);
		$message->setBody($body, 'text/html');
		if($invoiceFilePath != '') {
		      if(file_exists($invoiceFilePath)){
				$message->attach( Swift_Attachment::fromPath( $invoiceFilePath ) );
			}
		}
		$message->send();
		/******* Send invoice mail to dustri customer service *******/

		if($send_order_email){
		/********* order tracker **********/
		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$recipientOrder = array($this->orderServiceEmailAddress => "Dustri-Verlag Order Tracker");
		$subjectOrder = "Dustri-Verlag Order information  New Invoice::$basicId";
		$sender = array('no-reply@dustri.com' => 'Dustri-Verlag');
		$body = 'Hello, <br /><br />
				Please find enclosed the invoice against a new order.<br />
				<p>
				Regards,<br />
				Dustri Team,
				</p>
				<br />
				';
		$message->setTo($recipientOrder)
		->setFrom($sender)
		->setSubject($subjectOrder);
		$message->setBody($body, 'text/html');
		if($invoiceFilePath != '') {
		      if(file_exists($invoiceFilePath)){
				$message->attach( Swift_Attachment::fromPath( $invoiceFilePath ) );
			}
		}
		$message->send();
		/********* order tracker ***********/

		/********* order tracker **********/
		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$recipientOrder = array('dustri.communication@netzrezepte.de' => "Dustri-Verlag Order Tracker");
		$subjectOrder = "Dustri-Verlag Order information  New Invoice::$basicId";
		$sender = array('no-reply@dustri.com' => 'Dustri-Verlag');
		$body = 'Hello, <br /><br />
				Please find enclosed the invoice against a new order.<br />
				<p>
				Regards,<br />
				Dustri Team,
				</p>
				<br />
				';
		$message->setTo($recipientOrder)
		->setFrom($sender)
		->setSubject($subjectOrder);
		$message->setBody($body, 'text/html');
		if($invoiceFilePath != '') {
		      if(file_exists($invoiceFilePath)){
				$message->attach( Swift_Attachment::fromPath( $invoiceFilePath ) );
			}
		}
		$message->send();
		/********* order tracker ***********/
		
		}

	}

	/**
	 * The validateEmailFormat method is used for checking Email which is valid or not.
	 *
	 *
	 * @param
	 *
	 * @return true(if valid)
		* @return false(if unvalid)
	 */

	function validateEmailFormat() {
		$x1 = strpos($_SESSION['email'], '@');
		$x2 = strpos($_SESSION['email'], '.', $x1);
		if($x1 && $x2) return true;
		else return false;
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

/**
	 * The getItems method is used for getting the items list for user registration
	 *
	 */

	function getItems(){
		$itemList = '';
		// DUSE-9 :: Vat
		$dbItem = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'*',
							'tx_topic_detail',
							'','','title'
		)or die('Error-Line 1826 : ' . $GLOBALS['TYPO3_DB']->sql_error());
		
		while ($rItems = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbItem)) {
			$itemList .= '<input type="checkbox" name="item[]" value="'.$rItems['uid'].'" />
			'.$rItems['title'].'<div class="clear"></div>';
		}
		return $itemList;
	}

	/**
	 * The getCountries method is used for getting the countries list for user registration
	 *
	 *
	 * @param $selCountry
	 *
	 * @return countriesList
		*
	 */
	function getCountries($selCountry = '') {
		$countriesList = '';
		// DUSE-9 :: Vat
		$dbCountries = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'cn_iso_3,cn_short_en',
							'static_countries',
							'','','cn_short_en'
		)or die('Error-Line 1826 : ' . $GLOBALS['TYPO3_DB']->sql_error());
		$countriesList .= '<option value=""></option>';
		while ($rCountries = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCountries)) {
			$curValue = $rCountries['cn_iso_3'] .'##'. $rCountries['cn_short_en'];
			if ($selCountry == $rCountries['cn_short_en']) {
				$countriesList .= '<option value="'.$curValue. '" selected="selected">' . $rCountries['cn_short_en'] . '</option>';
			}
			else { 
				$countriesList .= '<option value="'. $curValue . '">'. $rCountries['cn_short_en'] . '</option>';
			}
		}
		return $countriesList;
	}

	/**
	 * The manualPayment method is used for manual payment
	 *
	 *
	 * @param
	 *
	 * @return
	 *
	 */
	function manualPayment(){

		/* Billing Details(Main) - Begin */
		$billingMainArr = array('x', 'y', 'submit_order');
		$userId = $GLOBALS['TSFE']->fe_user->user[uid];

		$insArray = array(
						'pid' => 52,
						'tstamp' => time(),
						'cruser_id' => $userId,
						'trxuser_id' => $userId,
						'trx_amount' => t3lib_div::_POST('trx_amount'),
						'trx_currency' => t3lib_div::_POST('trx_currency'),
						'ret_transdate' => date('Y-m-d'),
						'ret_transtime' => date('H:i'),
						'session_id' => session_id()
					);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_netzrezepteshop_man_basic', $insArray) or die('Error Insert Manual Payment: ' . mysql_error());

		$basicId = mysql_insert_id();

		if($basicId > 0) {

			// get cart data
		$dbCartTemp = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_netzrezepteshop_temp',
				"deleted=0 AND hidden=0 AND session_id = '" . session_id() . "'"
		) or die($GLOBALS['TYPO3_DB']->sql_error());
		while($rCartTemp = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCartTemp)) {
			$data = array();
			$data['pid']=$rCartTemp['pid'];
			$data['tstamp']=$rCartTemp['tstamp'];
			$data['crdate']=$rCartTemp['crdate'];
			$data['cruser_id']=$rCartTemp['cruser_id'];
			$data['deleted']=$rCartTemp['deleted'];
			$data['hidden']=$rCartTemp['hidden'];
			$data['product_id']=$rCartTemp['product_id'];
			$data['product_title']=$rCartTemp['product_title'];
			$data['quantity']=$rCartTemp['quantity'];
			$data['unit_price']=$rCartTemp['unit_price'];
			$data['unit_price_for_partly_online_item_for_private']=$rCartTemp['unit_price_for_partly_online_item_for_private'];
			$data['unit_price_for_partly_print_item_for_private']=$rCartTemp['unit_price_for_partly_print_item_for_private'];
			$data['vat_for_print_subs']=$rCartTemp['vat_for_print_subs'];
			$data['vat_for_online_subs']=$rCartTemp['vat_for_online_subs'];
			$data['vat_per_for_print_subs']=$rCartTemp['vat_per_for_print_subs'];
			$data['vat_per_for_online_subs']=$rCartTemp['vat_per_for_online_subs'];
			$data['vat']=$rCartTemp['vat'];
			$data['shipping_cost']=$rCartTemp['shipping_cost'];
			$data['session_id']=$rCartTemp['session_id'];
			$data['product_type']=$rCartTemp['product_type'];
			$data['product_type_title']=$rCartTemp['product_type_title'];
			$data['currency']=$rCartTemp['currency'];
			$data['vat_online_subscription']=$rCartTemp['vat_online_subscription'];
			$data['discount']=$rCartTemp['discount'];
			$data['subscription_type']=$rCartTemp['subscription_type'];
			$data['published']=$rCartTemp['published'];
			$data['alt_lang']=$rCartTemp['alt_lang'];
			$data['order_no']=$rCartTemp['order_no'];
			$data['order_date']=$rCartTemp['order_date'];
			$data['delivery_address']=$rCartTemp['delivery_address'];
			$data['bulk_shipping']=$rCartTemp['bulk_shipping'];
			$data['subscription_id']=$rCartTemp['subscription_id'];
			$data['iserp']=$rCartTemp['iserp'];
			$data['feuser']=$rCartTemp['feuser'];
			$data['isfreetext']=$rCartTemp['isfreetext'];
			$data['miscellaneous_sale']=$rCartTemp['miscellaneous_sale'];
			$data['sale_track_id']=$rCartTemp['sale_track_id'];
			$data['fte_range_type']=$rCartTemp['fte_range_type'];
			$data['productcode']=$rCartTemp['productcode'];

			$data['ebook']=$rCartTemp['ebook'];
			$data['fyear']=$rCartTemp['fyear'];
			$data['vat_price_discount_data']=$rCartTemp['vat_price_discount_data'];
			$data['product_added_in']=$rCartTemp['product_added_in'];
			$data['man_id'] = $basicId;
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_netzrezepteshop_man_temp', $data) or die('Error Insert Manual Payment: ' . $GLOBALS['TYPO3_DB']->sql_error());
		}


			$this->createInvoice($basicId, 'manbo');
			// Obtaining name of invoice file
			$dbInvoiceAddress = $GLOBALS['TYPO3_DB']->exec_SELECTquery('billing_last_name, billing_first_name', 'tx_netzrezepteshop_billing_temp', "session_id='" . session_id() . "' AND deleted=0") or die('Error Line 1386: ' . mysql_error());
			if($rInvoiceAddress = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbInvoiceAddress))	{
				$rInvoiceAddress = array_map('stripslashes',$rInvoiceAddress);
				$usrName = str_replace(" ","_",trim($rInvoiceAddress['billing_first_name'])).'_'.str_replace(" ","_",trim($rInvoiceAddress['billing_last_name']));
			}
			// Delete product from shop_temp is not required in online as it is handled in ERP
			session_regenerate_id();

			// Obtaining template headers
			$templateHeader['manual_payment_complete'] = $this->cObj->getSubpart($this->templateCode,'###MANUAL_PAYMENT_COMPLETE###');
			// $content = 'Thank you for your order.<br>Include your order Id -' . $basicId . ' for your payment.';
			$markerArray['###MANUAL_PAYMENT_HEADER###'] = $this->pi_getLL('manual_header');
			$markerArray['###BASIC_ID###'] = $basicId;
			$markerArray['###IMAGE_PAY_STAGE5###'] = $this->pi_getLL('image_pay_stage5');
			$usrName = str_replace('&amp;','',$usrName);
			$markerArray['###INVOICE_FILE###'] = $basicId . '_' .  $usrName . '.pdf';
			$content = $this->cObj->substituteMarkerArrayCached($templateHeader['manual_payment_complete'], $markerArray);
		}
		return $content;
	}
	
	private function showFelogin() {
		$tsFelogin = $GLOBALS['TSFE']->tmpl->setup['lib.']['shop_felogin.']['10.'];
		$felogin = $this->cObj->cObjGetSingle('USER',$tsFelogin);
		return $felogin;
	}
	/**
	 * login user usging api
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	private function login ($username, $password ) {
		$result = TRUE;
			// Log the user in
		$loginData = array(
			'uname' => $username,
			'uident' => $password,
			'uident_text' => $password,
			'status' => 'login',
		);
			// Do not use a particular page id
		$GLOBALS['TSFE']->fe_user->checkPid = 0;
			// Get authentication info array
		$authInfo = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
			// Get user info
		$user = $GLOBALS['TSFE']->fe_user->fetchUserRecord($authInfo['db_user'], $loginData['uname']);
		if (is_array($user)) {
				// Get the appropriate authentication service
			$authServiceObj = t3lib_div::makeInstanceService('auth', 'authUserFE');
				// Check authentication
			if (is_object($authServiceObj)) {
				$ok = $authServiceObj->compareUident($user, $loginData);
				if ($ok) {
						// Login successfull: create user session
					$GLOBALS['TSFE']->fe_user->createUserSession($user);
					$GLOBALS['TSFE']->fe_user->user = $GLOBALS['TSFE']->fe_user->fetchUserSession();
					$GLOBALS['TSFE']->fe_user->fetchGroupData();
					$GLOBALS['TSFE']->loginUser = 1;
					$GLOBALS['TSFE']->gr_list = '0,-2';
					
					
				} else {
					$result = FALSE;
				}
			} else {
				$result = FALSE;
			}
		} else {
			$result = FALSE;
		}
		return $result;
	}
	
	private function diffBillingShiping($rUser,$rUserBillingInfo) {
		$return['diffbilling'] = 0;
		if (
				$rUser['title'] != $rUserBillingInfo['billing_title'] ||
				$rUser['gender'] != $rUserBillingInfo['billing_gender'] || 
				$rUser['first_name'] != $rUserBillingInfo['billing_first_name'] ||
				$rUser['last_name'] != $rUserBillingInfo['billing_last_name'] ||
				$rUser['address'] != $rUserBillingInfo['billing_address'] || 
				$rUser['street'] != $rUserBillingInfo['billing_street'] || 
				$rUser['city'] != $rUserBillingInfo['billing_city'] ||
				$rUser['zip'] != $rUserBillingInfo['billing_zip'] ||
				$rUser['country'] != $rUserBillingInfo['billing_country'] ||
				$rUser['telephone'] != $rUserBillingInfo['billing_telephone'] || 
				$rUser['fax'] != $rUserBillingInfo['billing_fax'] || 
				$rUser['email'] != $rUserBillingInfo['billing_email']	
			) {
			$return['diffbilling'] = 1;
		}
		$return['diffshiping'] = 0;
		if (
				$rUser['title'] != $rUserBillingInfo['shipping_title'] ||
				$rUser['gender'] != $rUserBillingInfo['shipping_gender'] ||
				$rUser['first_name'] != $rUserBillingInfo['shipping_first_name'] ||
				$rUser['last_name'] != $rUserBillingInfo['shipping_last_name'] ||
				$rUser['address'] != $rUserBillingInfo['shipping_address'] ||
				$rUser['street'] != $rUserBillingInfo['shipping_street'] ||
				$rUser['city'] != $rUserBillingInfo['shipping_city'] ||
				$rUser['zip'] != $rUserBillingInfo['shipping_zip'] ||
				$rUser['country'] != $rUserBillingInfo['shipping_country'] ||
				$rUser['telephone'] != $rUserBillingInfo['shipping_telephone'] ||
				$rUser['fax'] != $rUserBillingInfo['shipping_fax'] ||
				$rUser['email'] != $rUserBillingInfo['shipping_email']
		) {
			$return['diffshiping'] = 1;
		}
		return $return;
		
	}
	
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/netzrezepte_shop/pi2/class.tx_netzrezepteshop_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/netzrezepte_shop/pi2/class.tx_netzrezepteshop_pi2.php']);
}

?>
