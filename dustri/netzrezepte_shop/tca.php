<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_netzrezepteshop_basic"] = array (
	"ctrl" => $TCA["tx_netzrezepteshop_basic"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,first_name,last_name,purchase_date"
	),
	"feInterface" => $TCA["tx_netzrezepteshop_basic"]["feInterface"],
	"columns" => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"first_name" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_basic.first_name",
			"config" => Array (
				"type" => "input",
				"size" => "45",
				"max" => "255",
				"eval" => "required,trim",
			)
		),
		"last_name" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_basic.last_name",
			"config" => Array (
				"type" => "input",
				"size" => "45",
				"max" => "255",
				"eval" => "trim",
			)
		),
		"purchase_date" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_basic.purchase_date",
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, first_name, last_name, purchase_date")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_netzrezepteshop_temp"] = array (
	"ctrl" => $TCA["tx_netzrezepteshop_temp"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,product_id,quantity,unit_price,session_id,product_type,subscription_type,iserp,feuser,isfreetext,miscellaneous_sale"
	),
	"feInterface" => $TCA["tx_netzrezepteshop_temp"]["feInterface"],
	"columns" => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"product_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.product_id",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"quantity" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.quantity",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"unit_price" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.unit_price",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"session_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.session_id",
			"config" => Array (
				"type" => "input",
				"size" => "45",
				"max" => "128",
				"eval" => "required,trim",
			)
		),
		"product_type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.product_type",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		'subscription_type' => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.subscription_type",
			"config" => Array (
					"type"     => "select",
					"items" => array(
							array('Trainee', 0),
							array('Private', 1),
							array('Institution', 2),
					),
					"size"     => "1",
					"maxitem"     => "1",

			)
		),
		'iserp' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.iserp',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'feuser' => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.feuser",
			"config" => Array (
					"type"     => "select",
					"foreign_table" => "fe_users",
					"items" => array(
							array('-- select --', 0),
					),
					"size"     => "1",
					"maxitem"     => "1",

			)
		),
		'isfreetext' => array (
				'exclude' => 1,
				'label'   => 'LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.isfreetext',
				'config'  => array (
						'type'    => 'check',
						'default' => '0'
				)
		),
		'miscellaneous_sale' => array (
				'exclude' => 1,
				'label'   => 'LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_temp.miscellaneous_sale',
				'config'  => array (
						'type'    => 'check',
						'default' => '0'
				)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, product_id, quantity, unit_price, session_id, product_type, subscription_type, iserp, feuser, isfreetext, miscellaneous_sale")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_netzrezepteshop_details"] = array (
	"ctrl" => $TCA["tx_netzrezepteshop_details"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,info_id,product_id,productcode,quantity,unit_price,total_price"
	),
	"feInterface" => $TCA["tx_netzrezepteshop_details"]["feInterface"],
	"columns" => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"info_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_details.info_id",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"product_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_details.product_id",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"productcode" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_details.productcode",
			"config" => Array (
				"type"     => "input",
				"size"     => "25",
				"eval"     => "trim",
			)
		),
		"quantity" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_details.quantity",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"unit_price" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_details.unit_price",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"total_price" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:netzrezepte_shop/locallang_db.xml:tx_netzrezepteshop_details.total_price",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, info_id, product_id, productcode, quantity, unit_price, total_price")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>
