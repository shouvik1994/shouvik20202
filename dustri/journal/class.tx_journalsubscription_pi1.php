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
 * Plugin 'journal_subscription' for the 'journal_subscription' extension.
 *
 * @author	Anupam Chatterjee <anupam@netzrezepte.de>
 * @package	TYPO3
 * @subpackage	tx_journalsubscription
 */
class tx_journalsubscription_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_journalsubscription_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_journalsubscription_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'journal_subscription';	// The extension key.
	var $pi_checkCHash = true;
	var $pid = 0;
	var $utility = null;
	function __construct() {
		$this->utility = new tx_netzrezeptecommercelibrary_utility();
	}
	
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
		
		// Preconfigure the typolink
		$this->local_cObj = t3lib_div::makeInstance("tslib_cObj");
		$this->local_cObj->setCurrentVal($GLOBALS["TSFE"]->id);
		$this->typolink_conf = $this->conf["typolink."];
		$this->typolink_conf["parameter."]["current"] = 1;
		$this->typolink_conf["additionalParams"] =
	    $this->cObj->stdWrap($this->typolink_conf["additionalParams"],
	    $this->typolink_conf["additionalParams."]);
	    unset($this->typolink_conf["additionalParams."]);

	    // Configure caching
		$GLOBALS["TSFE"]->set_no_cache();	

		// Obtaining global vars
		$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['article_list']);
		if($_extConfig != '') $this->pid = $_extConfig['addToCartPage'];				
		
		// Obtaining product template
		$this->templateCode=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."res/subscription_template.html");
		
		$content = $this->showIntro();			
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	// Function to show the subscription details page
	function showIntro() {
		// TypoLink Initial Setup		
		$temp_conf = $this->typolink_conf;				
		$temp_conf["useCacheHash"] = $this->allowCaching;
		$temp_conf["no_cache"] = !$this->allowCaching;
	
		// Obtaining template subparts
		$templateHeader['subscription_intro'] = $this->cObj->getSubpart($this->templateCode,'###SUBSCRIPTION_INTRO###');
		$templateHeader['subscription_details'] = $this->cObj->getSubpart($this->templateCode,'###SUBSCRIPTION_DETAILS###');
		
		$templateHeader['subscription_details_box'] = $this->cObj->getSubpart($this->templateCode,'###SUBSCRIPTION_DETAILS_BOX###');

		//echo t3lib_div::_GET('magId');
		
		$whereClause = "deleted=0 AND hidden=0 AND 
						journal_id=" . t3lib_div::_GET('magId') . " AND	type=2";
		$dbLatestIssue = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'upload_file',
							'tx_datamasters_products',
							$whereClause, '','uid DESC','1'
						) or die('Error-Line 90: ' . mysql_error());
		
		if($rLatestIssue = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbLatestIssue)) $marksArray['###ISSUE_IMAGE_FILE###'] = $rLatestIssue['upload_file'];
		
		$dbJournal = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_datamasters_journal',
						'code=' . t3lib_div::_GET('magId')
					) or die('Error-Line 102: ' . mysql_error());
		
		$journal_title="";
		if($rJournal = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbJournal)){
			if($rJournal['available_online'] == 1) $typesArray = array(31, 32, 33, 34);
			else $typesArray = array(32);

			$journal_title = $rJournal['title'];
		}
		$marksArray['###SUBSCRIPTION_HEADING###'] = $journal_title;
		$subscriptionList = '';
		$marksBox['###ADD_CART_PID###'] = $this->pid;

		if($_COOKIE['currency'] == 'EUR'){
			$currency = 'euro';
			$currency_marker = '&euro;';
		}
		else{
			$currency = 'dollar';
			$currency_marker = '$';
		}
		
		if(t3lib_div::_GET('L') == 1) $typesField = "_de";
		else $typesField = "";
		
		// Defining Additional Extension params for Typolink 
		$temp_conf["additionalParams"] = "&magId=" . t3lib_div::_GET('magId');
		
		// Generating Return path
		$temp_conf["returnLast"] = 'url';			
		$returnPath = $this->local_cObj->typolink(NULL, $temp_conf);
		
		for($i = 0; $i < count($typesArray); $i ++){
			$whereClause = "deleted=0 AND hidden=0 AND 
							journal_id=" . t3lib_div::_GET('magId') . " 
							AND type=" . $typesArray[$i];
			
			$dbPrdDetails = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								"uid, title, subscription_prices_" . $currency . " AS subscription_prices",
								'tx_datamasters_products',
								$whereClause
							) or die('Error-Line 136: ' . mysql_error());
			
			if($rPrdDetails = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbPrdDetails)){
				$dbTypesInfo = mysql_query("SELECT title" . $typesField . " AS title FROM tx_datamasters_product_types WHERE code='" . $this->utility->cleanVar($typesArray[$i])."'") or die('Error-Line 111: ' . mysql_error());

				

				if($rTypesInfo = mysql_fetch_array($dbTypesInfo)) {
					$marksInner['###SUBSCRIPTION_TYPE###'] = $rTypesInfo['title'];
				}
				
				
				$pricesArr = explode('#', $rPrdDetails['subscription_prices']);
				
				$subscriptionListBox = '';
				
				$marksBox['###PRODUCT_ID###'] 		= $rPrdDetails['uid'];
				$marksBox['###RETURN_PATH###'] 		= urlencode($returnPath);			
				$marksBox['###PRODUCT_TYPE###'] 		= $typesArray[$i];
				$marksBox['###ADD_CART_BUTTON###'] 	= $this->pi_getLL('add_cart_button');
				
				// DUS-619 Print + Online price for private
				if ($pricesArr[1] != 'n/a') {
					$tempPriceArr = explode('@', $pricesArr[1]);
					$productPrice = $tempPriceArr[0] + $tempPriceArr[1];
				}
				else {
					$productPrice = 'N/A';
				}
				$marksBox['###PRICE###'] = ($productPrice != 'N/A') ? number_format($productPrice, 2) : "N/A";
				$marksBox['###NAME###'] 	= $this->pi_getLL('private_label');
				$marksBox['###CAT###'] 	= 1;
				$marksBox['###CURRENCY###']=($pricesArr[1]!='n/a')?$currency_marker:'';
				$marksBox['###DISABLED###']=($pricesArr[1]!='n/a')?"":"disabled";
				$subscriptionListBox .= $this->cObj->substituteMarkerArrayCached($templateHeader['subscription_details_box'], $marksBox);
			
				// DUS-619 Print + Online price for private
				if ($pricesArr[2] != 'n/a') {
					$tempPriceArr = explode('@', $pricesArr[2]);
					$productPrice = $tempPriceArr[0] + $tempPriceArr[1];
				}
				else {
					$productPrice = 'N/A';
				}
				$marksBox['###PRICE###'] 	= ($productPrice != 'N/A') ? number_format($productPrice, 2) : "N/A";
				$marksBox['###NAME###'] 	= $this->pi_getLL('institution_label');
				$marksBox['###CAT###'] 	= 2;
				$marksBox['###CURRENCY###']=($pricesArr[2]!='n/a')?$currency_marker:'';
				$marksBox['###DISABLED###']=($pricesArr[2]!='n/a')?"":"disabled";
				$subscriptionListBox .= $this->cObj->substituteMarkerArrayCached($templateHeader['subscription_details_box'], $marksBox);
				
				
				
				$marksInner['###SUBSCRIPTION_BOX###'] 	= $subscriptionListBox;
				
				if(t3lib_div::_GET('L') != '') 
					$marksInner['###PRODUCT_TYPE###']  .= '&L=' . t3lib_div::_GET('L');
				$subscriptionList .= $this->cObj->substituteMarkerArrayCached($templateHeader['subscription_details'], $marksInner);			
			}
		}
		$marksArray['###MARKER_SUBSCRIPTION_DETAILS###'] = $subscriptionList;
		return $this->cObj->substituteMarkerArrayCached($templateHeader['subscription_intro'], $marksArray);
	}	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/journal_subscription/pi1/class.tx_journalsubscription_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/journal_subscription/pi1/class.tx_journalsubscription_pi1.php']);
}

?>
