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

// require_once(PATH_tslib.'class.tslib_pibase.php');

require_once(t3lib_extMgm::siteRelPath('netzrezepte_commerce_library') . 'pi1/class.tx_netzrezeptecommercelibrary_pi1.php');


/**
 * Plugin 'Mini cart' for the 'netzrezepte_shop' extension.
 *
 * @author	Sushil Gupta <sushilkumar.gupta@netzrezepte.de>
 * @package	TYPO3
 * @subpackage	tx_netzrezepteshop
 */
class tx_netzrezepteshop_pi3 extends tx_netzrezeptecommercelibrary_pi1 {
	var $prefixId      = 'tx_netzrezepteshop_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_netzrezepteshop_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'netzrezepte_shop';	// The extension key.
	var $pi_checkCHash = true;
	var $currencyInt;
	var $currency;
	var $sessionId;
	var $billingCountry;
	var $billingCountryCode;

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
		$this->pi_loadLL();
		
		// Obtaining global vars
		$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['netzrezepte_shop']);
		if($_extConfig != '') $cartCheckoutPage = $_extConfig['cartCheckoutPage'];
		
		require_once('./typo3conf/ext/netzrezepte_commerce_library/pi1/class.tx_netzrezeptecommercelibrary_pi1.php');
		$objCommLib = t3lib_div::makeInstance('tx_netzrezeptecommercelibrary_pi1');
		
		$this->location = ($_SESSION['location'] != '') ? $_SESSION['location'] : $_COOKIE['location'];
		if ($GLOBALS['TSFE']->fe_user->user[uid] != '') {
			
			if ($objCommLib->isEuropeanCountry($GLOBALS['TSFE']->fe_user->user['country'])) {
				$this->location = 'EU';
			}
			$this->currencyInt = $_COOKIE['currency'];
		} else {
		$this->currencyInt = $_COOKIE['currency'];
		}
		$this->currency = ($this->currencyInt == 'EUR') ? 'euro' : 'dollar' ;
		
		
		
		/////////////// Set VAT & Discount (Start) ///////////////
		$this->sessionId = session_id();
		// Set Discount/Vat/Country Code
		$this->setInitialVariables();
		/////////////// Set VAT & Discont (End) ///////////////
		
		
		// Obtaining product template
		$this->templateCode=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."res/shopping_template.html");
		$templateHeader['mini_basket'] = $this->cObj->getSubpart($this->templateCode,'###MINI_BASKET###');
		$templateHeader['mini_basket_inner'] = $this->cObj->getSubpart($this->templateCode,'###MINI_BASKET_INNER###');
		$templateHeader['mini_basket_shipping'] = $this->cObj->getSubpart($this->templateCode,'###MINIBASKET_SHIPPING###');

		$listInner = '';
		$listTotal = 0;
		$totalShippingCost = 0;
		$markerArray['###HEADER_TYPE###'] = $this->pi_getLL('header_type');
		$markerArray['###HEADER_QUANTITY###'] = $this->pi_getLL('header_quantity');
		$markerArray['###HEADER_PRICE###'] = $this->pi_getLL('header_price');
		$markerArray['###VIEWCARTLABEL###'] = $this->pi_getLL('button_view_cart_label');
		$dbProdType = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'product_type, product_type_title,product_title',
						'tx_netzrezepteshop_temp',
						"deleted=0 AND hidden=0 AND session_id='" . session_id() . "'",'product_type','uid ASC'
					)or die('Error-Line 68 : ' . $GLOBALS['TYPO3_DB']->sql_error());

		$showShipping = false;
		$totalShippingCost = 0;
		$totalDiscountPrice = 0;
		$totalProductPrice = 0; // Product+vat+shipping
		$totalShippingVat = 0;
		if($GLOBALS['TYPO3_DB']->sql_num_rows($dbProdType) > 0) {
			while($rProdType = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbProdType)) {
				$whereClause = "deleted=0 AND hidden=0 AND product_type=" . $rProdType['product_type'] . " AND
								session_id='" . session_id() . "'";
				
				// Get cart detail on product type basis
				$cartProduct = $this->getCartProductByProductType($rProdType['product_type']); 
				// PRODUCT INFORMATION
				$markerInner['###PRODUCT_TYPE###'] 		= $rProdType['product_title'].", ".$rProdType['product_type_title'];
				$markerInner['###PRODUCT_QUANTITY###'] 	= $cartProduct['quantity'];
				$markerInner['###PRODUCT_DISCOUNT###']  = number_format($cartProduct['discountPriceTotal'], 2);
				$markerInner['###PRODUCT_VAT###']		= number_format($cartProduct['vatAmountTotal'], 2);
				$markerInner['###PRODUCT_PRICE###']  	= number_format($cartProduct['productPriceTotal'], 2);
				
				// TOTAL CALCULATION
				$totalShippingCost					+= $cartProduct['shippingCostTotal'];
				$totalShippingVat					+= $cartProduct['shippingVatTotal'];
				$totalDiscountPrice					+= $cartProduct['discountPriceTotal'];
				$totalProductPrice					+= $cartProduct['productPriceTotal'];
														
				
				
				/*
				$dbProdList = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'SUM(quantity) AS totalQtty, SUM(quantity*unit_price) AS totalPrice, SUM(quantity*shipping_cost) AS totShipping',
								'tx_netzrezepteshop_temp',
								$whereClause,'product_type'
							) or die('Error-Line 68 : ' . $GLOBALS['TYPO3_DB']->sql_error());

				if($rProdList = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbProdList)) {
					$markerInner['###PRODUCT_TYPE###'] = $rProdType['product_type_title'];
					$markerInner['###PRODUCT_QUANTITY###'] = $rProdList['totalQtty'];

					$dbProdDisc = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
										'discount,vat',
										'tx_netzrezepteshop_temp',
										"deleted=0 AND hidden=0 AND product_type=" . $rProdType['product_type'] . " AND session_id='" . session_id() . "'"
								) or die('Error-Line 90 : ' . $GLOBALS['TYPO3_DB']->sql_error());

					if($rProdDisc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbProdDisc)) {
						$discount = $rProdDisc['discount'];
						$vat = $rProdDisc['vat'];
					
						/////////////////// VAT & DISCOUNT (Start) ///////////////////////
						$markerInner['###PRODUCT_DISCOUNT###'] = 0;
						$markerInner['###PRODUCT_VAT###']=0;
						$discountAmount = $rProdList['totalPrice'] * $discount / 100;
						$markerInner['###PRODUCT_DISCOUNT###'] = number_format($discountAmount, 2);
						$markerInner['###PRODUCT_VAT###'] = $vat; 
						
						/////////////////// VAT & DISCOUNT (End) ///////////////////////
						
						
						
					}
					$totDiscAmount =  number_format((($rProdList['totalPrice']  * $discount) / 100),2);
					$markerInner['###PRODUCT_PRICE###'] = number_format(($rProdList['totalPrice'] - $totDiscAmount), 2);
					
					$listTotal += (($rProdList['totalPrice'] - $totDiscAmount + $rProdList['totShipping']));
					$totalShippingCost += $rProdList['totShipping'];
				}
				$listInner .= $this->cObj->substituteMarkerArray($templateHeader['mini_basket_inner'], $markerInner);
				if($rProdType['product_type']!=21 && $totalShippingCost>0)
				{
					$showShipping = true;
				}
				*/
				
				$listInner .= $this->cObj->substituteMarkerArray($templateHeader['mini_basket_inner'], $markerInner);
			}
			$markerArray['###BASKET_INNER###'] 	= $listInner;
			
			// Shipping Cost (Start)
			$markerShip['###SHIPPING_LABEL###'] = $this->pi_getLL('cart_shipping_label');
			$markerShip['###SHIPPING_VAT###'] = number_format($totalShippingVat, 2);
			$markerShip['###SHIPPING_PRICE###'] = number_format($totalShippingCost, 2);
			if( $totalShippingCost > 0 ) {
				$showShipping = true;
			}
			if ( $showShipping ) {
				$shippingHtml = $this->cObj->substituteMarkerArray($templateHeader['mini_basket_shipping'], $markerShip);
				$markerArray['###MINI_SHIPPING###'] = $shippingHtml;
			}
			else {
				$markerArray['###MINI_SHIPPING###'] = "";
			}
			// Shipping Cost (End)
			
			$totalText = $this->pi_getLL('gross_total');
			$markerArray['###TOTAL_PRICE###'] = $totalText . ': ' . number_format(($totalProductPrice + $totalShippingCost), 2) . '  <span id="miniCartCurrency">' . $this->currencyInt . '</span>';
			
			
			$markerArray['###CART_PAGE_LINK###'] = 'index.php?id=' . $cartCheckoutPage . '&no_cache=1';
			if(t3lib_div::_GET('L') != '') $markerArray['###CART_PAGE_LINK###'] .= '&L=' . t3lib_div::_GET('L');
			$markerArray['###BUTTON_VIEW_CART###'] = $this->pi_getLL('button_view_cart');
			if($this->currencyInt == 'EUR') {
				$markerArray['###TRANS_CURRENCY###'] = '(&euro;)';
				$markerArray['###TRANS_TOTAL_CURRENCY###'] = 'EUR';
			}
			else {
				$markerArray['###TRANS_CURRENCY###'] = '($)';
				$markerArray['###TRANS_TOTAL_CURRENCY###'] = 'USD';
			}
		}
		else {
			$markerArray['###BASKET_INNER###'] = '<tr><td colspan="3"><span style="font-family:Arial, Helvetica, sans-serif; font-size:10px;">' . $this->pi_getLL('text_cart_empty') . '</span></td></tr>';
			$markerArray['###TOTAL_PRICE###'] = '';
			$markerArray['###CART_PAGE_LINK###'] = '#';
			$markerArray['###BUTTON_VIEW_CART###'] = 'disabled';
			$markerArray['###TRANS_CURRENCY###'] = '';
			$markerArray['###TRANS_TOTAL_CURRENCY###'] = '';
			$markerArray['###MINI_SHIPPING###'] = '';
		}
		$markerArray['###HEADER_MINI_CART###'] = $this->pi_getLL('header_mini_cart');
		$markerArray['###HEADER_VAT###'] = $this->pi_getLL('vat_header');
		$markerArray['###HEADER_DISCOUNT###'] = $this->pi_getLL('discount_header');
		return $this->cObj->substituteMarkerArray($templateHeader['mini_basket'], $markerArray);
	}
	
	
	public function getCartProductByProductType($product_type) {
		$dbCartProduct = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_netzrezepteshop_temp',
				"deleted=0 AND hidden=0 AND session_id='" . $this->sessionId . "' AND product_type='$product_type'"
		) or die ('Error-Line '. __LINE__ .': ' . $GLOBALS['TYPO3_DB']->sql_error());
		
		$this->activeDebug = 1;
		$cartProductArr = array();
		$quantity = 0;
		$productPriceTotal = 0;
		$shippingCostTotal = 0;
		$vatAmountTotal = 0;
		$discountPriceTotal = 0;
		$shippingVatTotal = 0;
		
		 //ON FOR 2 DIFF SALES TAXES FOR PRINT + ONLINE SUBSCRIPTIONS
		  $this->setCommonVatPercentageForShipping('Webshop');
		 
		while($rCart = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbCartProduct)) {
			$shippingCost = $vatOnProduct = $vatAmount = $shippingVat = 0;
			$quantity += $rCart['quantity'];
			$productPrice = $rCart['quantity'] * $rCart['unit_price'];
			$shippingCost = $rCart['quantity'] * $rCart['shipping_cost'];
			$discountPrice = ($productPrice * $rCart['discount']) / 100;
			
			$currencyInitArr = $this->currencyInit($this->billingCountryCode, $this->vatId);
			// DUSE-9 :: Vat logic, set var precentage for digital product based on country
			$vatPercentage = $rCart['vat'];
			$digitalProduct = $this->isDigitalProduct($rCart['product_type']);
			if ($digitalProduct) {
				if (empty($currencyInitArr[5])) { // if EU country
					$vatPercentage = $currencyInitArr[4];
				} else {
					// if non EU European country
					$vatPercentage = 0;
				}
			}
			//  ON FOR 2 DIFF SALES TAXES FOR PRINT + ONLINE SUBSCRIPTIONS
			$subscriptionType = $rCart['subscription_type'];
			// Start of DUS-619:: for print + online subscription for private subscriber(1)
			if ($rCart['product_type'] == 34 && ($subscriptionType == 1 || $subscriptionType == 2)) {
				$this->cartHasDifferentVatPercentage = true;
				if ($subscriptionType == 1) {
					$productPriceForPrintSubs 	= $productPrice - $rCart['unit_price_for_partly_online_item_for_private'];
					$productPriceForOnlineSubs 	= $rCart['unit_price_for_partly_online_item_for_private'];
					// Get vat amount and percentages
					$vatPriceDiscount = $this->getVatPriceForPrintAndOnlineSubscription($subscriptionType, $productPrice, ($rCart['unit_price_for_partly_online_item_for_private'] * $rCart['quantity']), $rCart['vat'], $rCart['vat_online_subscription'], $shippingCost,$rCart['discount']);
				}
				else if ($subscriptionType == 2) {
					$productPriceForPrintSubs 	= ($productPrice * $this->printSubscriptionPricePercentage)/100;
					$productPriceForOnlineSubs 	= $productPrice - $productPriceForPrintSubs;
					// calculate vat, discount
					$vatPriceDiscount = $this->getVatPriceForPrintAndOnlineSubscription( $subscriptionType, $productPrice, $productPriceForOnlineSubs, $rCart['vat'], $rCart['vat_online_subscription'], $shippingCost, $rCart['discount']);
					$vatAmount = $vatPriceDiscount['vatTotalOnProduct'] + $vatPriceDiscount['vatforShipping'];
				}
				$vatAmount 		= $vatPriceDiscount['vatTotalOnProduct']; // + $vatPriceDiscount['vatforShipping'];
				$shippingVat 	= $vatPriceDiscount['vatforShipping'];
				$discountPrice 	= $vatPriceDiscount['discountCost'];
			}
			else { 
				if ($vatPercentage > 0) {
					$shippingPercentage = $vatPercentage;
					// if cart has different vat percentage (start)
					// ON FOR 2 DIFF SALES TAXES FOR PRINT + ONLINE SUBSCRIPTIONS 
					  if ($this->cartHasDifferentVatPercentage) {
						$shippingPercentage = $this->commonVatPercentageForShipping;
					}
					else {
						$shippingPercentage = $vatPercentage;
					}
					// if cart has different vat percentage (end)
					$vatAmount = $this->getVatPrices($productPrice, $currencyInitArr[2], $vatPercentage, $currencyInitArr[0], $discountPrice);
					$shippingVat 	= (($shippingCost / (100 + $shippingPercentage)) * $shippingPercentage);
				}
				else {
					$vatAmount = $this->getVatPrices($productPrice, $currencyInitArr[2], $vatPercentage, $currencyInitArr[0], $discountAmount);
					$shippingPercentage	= '';
					$shippingVat	= 0;
				}
			 }
			
			$discountPriceTotal += $discountPrice;
			$vatAmountTotal += $vatAmount; 
			$productPriceTotal += $productPrice;
			$shippingCostTotal += $shippingCost;
			$shippingVatTotal += $shippingVat;
			
		}
		$cartProductArr['quantity'] 		 	= $quantity;
		$cartProductArr['discountPriceTotal'] 	= $discountPriceTotal;
		$cartProductArr['vatAmountTotal'] 	 	= $vatAmountTotal;
		$cartProductArr['productPriceTotal'] 	= $productPriceTotal-$discountPriceTotal;
		$cartProductArr['shippingCostTotal'] 	= $shippingCostTotal;
		$cartProductArr['shippingVatTotal'] 	= $shippingVatTotal;
		return $cartProductArr;
	}
	
	public function setInitialVariables() {
		
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
		
		// Set Billing Country detail if any
		if (isset($_SESSION['billing_country'])) {
			$this->billingCountry = $_SESSION['billing_country'];
			$this->billingCountryCode = $_SESSION['billing_country_code'];
			if (empty($this->billingCountryCode)) {
				$this->billingCountryCode = $this->getCountryCode($_SESSION['billing_country']);
			}
		}
		else {
			$dbBill_Country = $GLOBALS['TYPO3_DB']->exec_SELECTquery('billing_country,billing_country_code',
					'tx_netzrezepteshop_billing_temp', "session_id='" . $this->sessionId . "' AND deleted=0");
			if ($rBill_Country = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbBill_Country)) {
				$_SESSION['billing_country'] 		= $rBill_Country['billing_country'];
				$_SESSION['billing_country_code'] 	= $rBill_Country['billing_country_code'];
				$this->billingCountry 				= $rBill_Country['billing_country'];
				$this->billingCountryCode 			= $rBill_Country['billing_country_code'];
				if (empty($this->billingCountryCode)) {
					$this->billingCountryCode = $this->getCountryCode($rBill_Country['billing_country']);
				}
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/netzrezepte_shop/pi3/class.tx_netzrezepteshop_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/netzrezepte_shop/pi3/class.tx_netzrezepteshop_pi3.php']);
}

?>
