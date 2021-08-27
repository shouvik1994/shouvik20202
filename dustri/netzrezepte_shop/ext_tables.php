<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_netzrezepteshop_basic"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_basic',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_netzrezepteshop_basic.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, first_name, last_name, purchase_date",
	)
);

$TCA["tx_netzrezepteshop_temp"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_netzrezepteshop_temp.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, product_id, quantity, unit_price, session_id, product_type",
	)
);

$TCA["tx_netzrezepteshop_details"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_details',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_netzrezepteshop_details.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, info_id, product_id, quantity, unit_price, total_price",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:netzrezepte_shop/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Add to cart");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:netzrezepte_shop/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","Cart Main");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:netzrezepte_shop/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi3/static/","Mini cart");

t3lib_extMgm::addPlugin(array('LLL:EXT:netzrezepte_shop/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY.'_pi4'),'list_type');

?>
