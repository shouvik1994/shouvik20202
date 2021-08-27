<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Sushil Gupta <sushilkumar.gupta@netzrezepte.de>
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


/**
 * Plugin 'Mini cart' for the 'netzrezepte_shop' extension.
 *
 * @author	Sushil Gupta <sushilkumar.gupta@netzrezepte.de>
 * @package	TYPO3
 * @subpackage	tx_netzrezepteshop
 */
class tx_netzrezepteshop_pi4 extends tslib_pibase {
	var $prefixId      = 'tx_netzrezepteshop_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_netzrezepteshop_pi4.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'netzrezepte_shop';	// The extension key.
	var $pi_checkCHash = true;
	
	
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
		
		if(t3lib_div::_GET('mode') == 'showmessage'){
			echo $this->getThemengebietesById(t3lib_div::_POST('messageid'));
			exit();
		}
	
		$content = $this->getThemengebietes();
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Function For Predefined Messages
	 * 
	 */
	protected function getThemengebietes(){
		// Predefined Messages Template
		$this->templateCode = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."pi4/res/themengebiete.html");
		
		$templateHeader['message_main'] = $this->cObj->getSubpart($this->templateCode,'###PREDEFINED_MESSAGES_MAIN###');
		$marksMain['###EXTENSION_PATH###'] = t3lib_extMgm::siteRelPath($this->extKey);
		
		if(t3lib_div::_POST('btnAddMessage') != '') {
			$status = $this->addThemengebietes();
			if($status) $marksMain['###SUBMIT_INFO###'] = 'Topic added successfully';
		} elseif(t3lib_div::_POST('btnUpdMessage') != '') {
			$status = $this->updateThemengebietes();
			if($status) $marksMain['###SUBMIT_INFO###'] = 'Topic updated successfully';
		} elseif(t3lib_div::_POST('btnDeleteMsg') != '') { //ID273
			$status = $this->deleteThemengebietes();
			if($status) $marksMain['###SUBMIT_INFO###'] = 'Topic deleted successfully';
		} else {
			$marksMain['###SUBMIT_INFO###'] = '';
		}
		
		$strMessageOption = $this->getThemengebieteOption();

		$marksMain['###PAGEID###'] = $GLOBALS['TSFE']->id;
		$marksMain['###MESSAGE_LIST###'] = $strMessageOption;
		$content = $this->cObj->substituteMarkerArrayCached($templateHeader['message_main'], $marksMain);
		return $content;
	}
	
	/**
	 * Function for adding predefined messages
	 * 
	 */
	function addThemengebietes(){
		$status = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_topic_detail', array('title'=>htmlentities(t3lib_div::_POST('title'),ENT_QUOTES, 'UTF-8')));
		return $status;
	}
	
	/**
	 * Function for updating predefined messages
	 * 
	 */
	function updateThemengebietes() {
		$status = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_topic_detail', 'uid='.t3lib_div::_POST('uid'), array('title'=>htmlentities(t3lib_div::_POST('title'),ENT_QUOTES, 'UTF-8')));
		return $status;
	}
	
	/**
	 * Function for diplaying messages in the option list
	 * 
	 */
	function getThemengebieteOption(){
		$strThemengebiete = '';
		$resThemengebiete = $GLOBALS['TYPO3_DB']-> exec_SELECTquery('*', 'tx_topic_detail', 'deleted=0 AND hidden=0');
		if(mysql_num_rows($resThemengebiete) > 0){
			while($rowThemenge = mysql_fetch_array($resThemengebiete)){
				$strThemengebiete .= '<option value="' . $rowThemenge['uid'] . '">' . $rowThemenge['title'] . '</option>';
			}
		}
		
		return $strThemengebiete;
	}
	
	/**
	 * Function for getting the message by Id
	 * 
	 */
	function getThemengebietesById($messageId)
	{
		$strMessage = '';
		$resMessage = $GLOBALS['TYPO3_DB']-> exec_SELECTquery('title', 'tx_topic_detail', 'uid='.$messageId);
		if($rowMessage = mysql_fetch_array($resMessage)) {
			$strMessage = mb_convert_encoding($rowMessage['title'], 'UTF-8', 'HTML-ENTITIES');
		}
		
		return $strMessage;
	}
	
	/**
	 * Function for deleting predefined message
	 * ID273
	 */
	function deleteThemengebietes() {
		$status = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_topic_detail', 'uid='.intval(t3lib_div::_POST('uid')));
		return $status;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/netzrezepte_shop/pi3/class.tx_netzrezepteshop_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/netzrezepte_shop/pi3/class.tx_netzrezepteshop_pi4.php']);
}

?>
