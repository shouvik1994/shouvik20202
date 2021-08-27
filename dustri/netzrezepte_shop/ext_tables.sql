#
# Table structure for table 'tx_netzrezepteshop_basic'
#
CREATE TABLE tx_netzrezepteshop_basic (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	first_name varchar(255) DEFAULT '' NOT NULL,
	last_name varchar(255) DEFAULT '' NOT NULL,
	purchase_date int(11) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	trxuser_id varchar(255) DEFAULT '' NOT NULL,
	addr_name varchar(255) DEFAULT '' NOT NULL,
	addr_street varchar(255) DEFAULT '' NOT NULL,
	addr_city varchar(255) DEFAULT '' NOT NULL,
	addr_zip varchar(255) DEFAULT '' NOT NULL,
	addr_email varchar(255) DEFAULT '' NOT NULL,
	trx_amount varchar(255) DEFAULT '' NOT NULL,
	trx_currency varchar(255) DEFAULT '' NOT NULL,
	trx_paymenttyp varchar(255) DEFAULT '' NOT NULL,
	trx_typ varchar(255) DEFAULT '' NOT NULL,
	ret_transdate varchar(255) DEFAULT '' NOT NULL,
	ret_transtime varchar(255) DEFAULT '' NOT NULL,
	ret_errorcode varchar(255) DEFAULT '' NOT NULL,
	ret_authcode varchar(255) DEFAULT '' NOT NULL,
	ret_ip varchar(255) DEFAULT '' NOT NULL,
	ret_booknr varchar(255) DEFAULT '' NOT NULL,
	ret_trx_number varchar(255) DEFAULT '' NOT NULL,
	redirect_needed varchar(255) DEFAULT '' NOT NULL,
	trx_paymentmethod varchar(255) DEFAULT '' NOT NULL,
	trx_paymentdata_country varchar(255) DEFAULT '' NOT NULL,
	trx_remoteip_country varchar(255) DEFAULT '' NOT NULL,
	addr_check_result varchar(255) DEFAULT '' NOT NULL,
	ret_status varchar(255) DEFAULT '' NOT NULL,
		credit_note tinyint(1) DEFAULT '0' NOT NULL,
		count_reminder int(11) DEFAULT '0' NOT NULL,
	payment_cond varchar(255) DEFAULT '' NOT NULL,
	proforma_to_real tinyint(1) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_netzrezepteshop_temp'
#
CREATE TABLE tx_netzrezepteshop_temp (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	product_id int(11) DEFAULT '0' NOT NULL,
	product_title varchar(500) DEFAULT '' NOT NULL,
	quantity int(11) DEFAULT '0' NOT NULL,
	unit_price float(9,2) NOT NULL DEFAULT '0.00',
	unit_price_for_partly_online_item_for_private float(9,2) NOT NULL DEFAULT '0.00' AFTER unit_price,
	shipping_cost float(9,2) NOT NULL DEFAULT '0.00',
	vat int(5) DEFAULT '7' NOT NULL,
	session_id varchar(128) DEFAULT '' NOT NULL,
	product_type int(11) DEFAULT '0' NOT NULL,
	product_type_title varchar(255) DEFAULT '' NOT NULL,
	currency varchar(5) DEFAULT 'EUR' NOT NULL,
	discount float DEFAULT '0' NOT NULL,
	subscription_type tinyint(4) default '1',
	published tinyint(4) DEFAULT '1' NOT NULL,
	alt_lang tinyint(4) DEFAULT '0' NOT NULL,
	order_no text NOT NULL,
	order_date varchar(25) DEFAULT '' NOT NULL,
	delivery_address text NOT NULL,
	bulk_shipping tinyint(1) DEFAULT '0' NOT NULL,
	subscription_id int(11) DEFAULT '0' NOT NULL,
	iserp tinyint(4) DEFAULT '0' NOT NULL,
	feuser int(11) DEFAULT '0' NOT NULL,
	isfreetext tinyint(1) DEFAULT '0' NOT NULL,
	miscellaneous_sale tinyint(1) DEFAULT '0' NOT NULL,
	manual_payment_id int(11) DEFAULT '0' NOT NULL,
	sale_track_id varchar(30) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY sale_track_id (sale_track_id),
  	KEY session_id (session_id)
);

CREATE TABLE tx_netzrezepteshop_temp (
	vat_online_subscription int(5) DEFAULT '0' NOT NULL AFTER vat,
	unit_price_for_partly_print_item_for_private float(9,2) NOT NULL DEFAULT '0.00' AFTER unit_price_for_partly_online_item_for_private,
	vat_for_print_subs float(9,2) NOT NULL DEFAULT '0.00' AFTER unit_price_for_partly_print_item_for_private,
	vat_for_online_subs float(9,2) NOT NULL DEFAULT '0.00' AFTER vat_for_print_subs,
	vat_per_for_print_subs int(5) DEFAULT '7' NOT NULL AFTER vat_for_online_subs,
	vat_per_for_online_subs int(5) DEFAULT '0' NOT NULL AFTER vat_per_for_print_subs,
	fte_range_type tinyint(1) DEFAULT '0' NOT NULL,
	productcode varchar(255) NOT NULL DEFAULT '',
);
CREATE TABLE tx_netzrezepteshop_temp (
	ebook tinyint(1) DEFAULT '0' NOT NULL,
);
#subscription_type tinyint(4) DEFAULT '1' NOT NULL,

#
# Table structure for table 'tx_netzrezepteshop_details'
#
CREATE TABLE tx_netzrezepteshop_details
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	info_id int(11) DEFAULT '0' NOT NULL,
	product_id int(11) DEFAULT '0' NOT NULL,
	product_title varchar(500) DEFAULT '' NOT NULL,
	product_type int(11) DEFAULT '0' NOT NULL,
	productcode varchar(255) DEFAULT '' NOT NULL,
	quantity int(11) DEFAULT '0' NOT NULL,
	unit_price float(9,2) NOT NULL DEFAULT '0.00',
	unit_price_for_partly_online_item_for_private float(9,2) NOT NULL DEFAULT '0.00' AFTER unit_price,
	total_price float(9,2) NOT NULL DEFAULT '0.00',
	shipping_cost float(9,2) NOT NULL DEFAULT '0.00',
	vat int(5) DEFAULT '7' NOT NULL,
	product_type_title varchar(255) DEFAULT '' NOT NULL,
	currency varchar(5) DEFAULT 'EUR' NOT NULL,
	discount float DEFAULT '0' NOT NULL,
	subscription_type tinyint(4) default '1',
	published tinyint(4) DEFAULT '1' NOT NULL,
	alt_lang tinyint(4) DEFAULT '0' NOT NULL,
	payment_cond varchar(255) DEFAULT '' NOT NULL,
	order_no text NOT NULL,
	order_date varchar(25) DEFAULT '' NOT NULL,
	delivery_address text NOT NULL,
	bulk_shipping tinyint(1) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#subscription_type tinyint(4) DEFAULT '1' NOT NULL,

## Europian Country
CREATE TABLE tx_netzrezepteshop_details (
	vat_online_subscription int(5) DEFAULT '0' NOT NULL AFTER vat,
	unit_price_for_partly_print_item_for_private float(9,2) NOT NULL DEFAULT '0.00' AFTER unit_price_for_partly_online_item_for_private,
	vat_for_print_subs float(9,2) NOT NULL DEFAULT '0.00' AFTER unit_price_for_partly_print_item_for_private,
	vat_for_online_subs float(9,2) NOT NULL DEFAULT '0.00' AFTER vat_for_print_subs,
	vat_per_for_print_subs int(5) DEFAULT '7' NOT NULL AFTER vat_for_online_subs,
	vat_per_for_online_subs int(5) DEFAULT '0' NOT NULL AFTER vat_per_for_print_subs,
	productcode varchar(255) NOT NULL DEFAULT '',
);
CREATE TABLE tx_netzrezepteshop_details (
	ebook tinyint(1) DEFAULT '0' NOT NULL,
);
#
# Table structure for table 'tx_netzrezepteshop_billing_temp'
#
CREATE TABLE tx_netzrezepteshop_billing_temp (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	billing_gender int(11) DEFAULT '0' NOT NULL,
	billing_title varchar(255) DEFAULT '' NOT NULL,
	billing_inst_type varchar(50) DEFAULT '' NOT NULL,
	billing_last_name varchar(255) DEFAULT '' NOT NULL,
	billing_first_name varchar(255) DEFAULT '' NOT NULL,
	billing_address varchar(255) DEFAULT '' NOT NULL,
	billing_address2 varchar(255) DEFAULT '' NOT NULL,
	billing_street varchar(255) DEFAULT '' NOT NULL,
	billing_zip varchar(255) DEFAULT '' NOT NULL,
	billing_city varchar(255) DEFAULT '' NOT NULL,
	billing_country varchar(255) DEFAULT '' NOT NULL,
	billing_country_code char(3) DEFAULT '' NOT NULL,
	billing_email varchar(255) DEFAULT '' NOT NULL,
	billing_telephone varchar(100) DEFAULT '' NOT NULL,
	billing_fax varchar(100) DEFAULT '' NOT NULL,
	shipping_gender int(11) DEFAULT '0' NOT NULL,
	shipping_title varchar(255) DEFAULT '' NOT NULL,
	shipping_inst_type varchar(50) DEFAULT '' NOT NULL,
	shipping_last_name varchar(255) DEFAULT '' NOT NULL,
	shipping_first_name varchar(255) DEFAULT '' NOT NULL,
	shipping_address varchar(255) DEFAULT '' NOT NULL,
	shipping_address2 varchar(255) DEFAULT '' NOT NULL,
	shipping_street varchar(255) DEFAULT '' NOT NULL,
	shipping_zip varchar(255) DEFAULT '' NOT NULL,
	shipping_city varchar(255) DEFAULT '' NOT NULL,
	shipping_country varchar(255) DEFAULT '' NOT NULL,
	shipping_country_code char(3) DEFAULT '' NOT NULL,
	shipping_email varchar(255) DEFAULT '' NOT NULL,
	shipping_telephone varchar(100) DEFAULT '' NOT NULL,
	shipping_fax varchar(100) DEFAULT '' NOT NULL,
	session_id varchar(128) DEFAULT '' NOT NULL,
	tx_netzrezepteaddress_postage_type varchar(9) DEFAULT '' NOT NULL,
	tx_netzrezepteaddress_payment_deadline int(4) DEFAULT '0' NOT NULL,
	payment_cond varchar(255) DEFAULT '' NOT NULL,
	customer_information text NOT NULL,
	message varchar(500) DEFAULT '' NOT NULL,
	sale_track_id varchar(30) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY sale_track_id (sale_track_id),
  	KEY session_id (session_id)
);

CREATE TABLE tx_netzrezepteshop_billing_temp (
	billing_country_code char(3) NOT NULL DEFAULT '',
  	shipping_country_code char(3) NOT NULL DEFAULT '',
  	payment_cond varchar(255) NOT NULL DEFAULT '',
  	billing_inst_type varchar(50) NOT NULL DEFAULT '',
  	shipping_inst_type varchar(50) NOT NULL DEFAULT '',
);

#
# Table structure for table 'tx_netzrezepteshop_billing'
#
CREATE TABLE tx_netzrezepteshop_billing (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	billing_gender int(11) DEFAULT '0' NOT NULL,
	billing_title varchar(255) DEFAULT '' NOT NULL,
	billing_inst_type varchar(50) DEFAULT '' NOT NULL,
	billing_last_name varchar(255) DEFAULT '' NOT NULL,
	billing_first_name varchar(255) DEFAULT '' NOT NULL,
	billing_address varchar(255) DEFAULT '' NOT NULL,
	billing_address2 varchar(255) DEFAULT '' NOT NULL,
	billing_street varchar(255) DEFAULT '' NOT NULL,
	billing_zip varchar(255) DEFAULT '' NOT NULL,
	billing_city varchar(255) DEFAULT '' NOT NULL,
	billing_country varchar(255) DEFAULT '' NOT NULL,
	billing_country_code char(3) DEFAULT '' NOT NULL,
	billing_email varchar(255) DEFAULT '' NOT NULL,
	billing_telephone varchar(100) DEFAULT '' NOT NULL,
	billing_fax varchar(100) DEFAULT '' NOT NULL,
	shipping_gender int(11) DEFAULT '0' NOT NULL,
	shipping_title varchar(255) DEFAULT '' NOT NULL,
	shipping_inst_type varchar(50) DEFAULT '' NOT NULL,
	shipping_last_name varchar(255) DEFAULT '' NOT NULL,
	shipping_first_name varchar(255) DEFAULT '' NOT NULL,
	shipping_address varchar(255) DEFAULT '' NOT NULL,
	shipping_address2 varchar(255) DEFAULT '' NOT NULL,
	shipping_street varchar(255) DEFAULT '' NOT NULL,
	shipping_zip varchar(255) DEFAULT '' NOT NULL,
	shipping_city varchar(255) DEFAULT '' NOT NULL,
	shipping_country varchar(255) DEFAULT '' NOT NULL,
	shipping_country_code char(3) DEFAULT '' NOT NULL,
	shipping_email varchar(255) DEFAULT '' NOT NULL,
	shipping_telephone varchar(100) DEFAULT '' NOT NULL,
	shipping_fax varchar(100) DEFAULT '' NOT NULL,
	shopping_id int(11) DEFAULT '0' NOT NULL,
	tx_netzrezepteaddress_postage_type varchar(9) DEFAULT '' NOT NULL,
	tx_netzrezepteaddress_payment_deadline int(4) DEFAULT '0' NOT NULL,
	payment_cond varchar(255) DEFAULT '' NOT NULL,
	customer_information text NOT NULL,
	message varchar(500) DEFAULT '' NOT NULL,
	sale_track_id varchar(30) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_netzrezepteshop_billing (
	billing_country_code char(3) NOT NULL DEFAULT '',
  	shipping_country_code char(3) NOT NULL DEFAULT '',
  	payment_cond varchar(255) NOT NULL DEFAULT '',
  	billing_inst_type varchar(50) NOT NULL DEFAULT '',
  	shipping_inst_type varchar(50) NOT NULL DEFAULT '',
);

#
# Table structure for table 'tx_netzrezepteshop_log'
#
CREATE TABLE tx_netzrezepteshop_log (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	session_id varchar(255) DEFAULT '' NOT NULL,
	trxuser_id varchar(255) DEFAULT '' NOT NULL,
	addr_name varchar(255) DEFAULT '' NOT NULL,
	addr_street varchar(255) DEFAULT '' NOT NULL,
	addr_city varchar(255) DEFAULT '' NOT NULL,
	addr_zip varchar(255) DEFAULT '' NOT NULL,
	addr_email varchar(255) DEFAULT '' NOT NULL,
	trx_amount varchar(255) DEFAULT '' NOT NULL,
	trx_currency varchar(255) DEFAULT '' NOT NULL,
	trx_paymenttyp varchar(255) DEFAULT '' NOT NULL,
	trx_typ varchar(255) DEFAULT '' NOT NULL,
	ret_transdate varchar(255) DEFAULT '' NOT NULL,
	ret_transtime varchar(255) DEFAULT '' NOT NULL,
	ret_errorcode varchar(255) DEFAULT '' NOT NULL,
	ret_authcode varchar(255) DEFAULT '' NOT NULL,
	ret_ip varchar(255) DEFAULT '' NOT NULL,
	ret_booknr varchar(255) DEFAULT '' NOT NULL,
	ret_trx_number varchar(255) DEFAULT '' NOT NULL,
	redirect_needed varchar(255) DEFAULT '' NOT NULL,
	trx_paymentmethod varchar(255) DEFAULT '' NOT NULL,
	trx_paymentdata_country varchar(255) DEFAULT '' NOT NULL,
	trx_remoteip_country varchar(255) DEFAULT '' NOT NULL,
	addr_check_result varchar(255) DEFAULT '' NOT NULL,
	ret_status varchar(255) DEFAULT '' NOT NULL,
	purchase_sess_id varchar(255) DEFAULT '' NOT NULL,
	ses_userid int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_netzrezepteshop_man_basic'
#
CREATE TABLE tx_netzrezepteshop_man_basic (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	trxuser_id varchar(255) DEFAULT '' NOT NULL,
	trx_amount varchar(255) DEFAULT '' NOT NULL,
	trx_currency varchar(255) DEFAULT '' NOT NULL,
	ret_transdate varchar(255) DEFAULT '' NOT NULL,
	ret_transtime varchar(255) DEFAULT '' NOT NULL,
	credit_note tinyint(1) DEFAULT '0' NOT NULL,
	session_id varchar(200) DEFAULT '' NOT NULL,
	trx_paymenttyp varchar(5) DEFAULT 'manbo' NOT NULL,
	payment_cond varchar(255) DEFAULT '' NOT NULL,
	count_reminder tinyint(4) DEFAULT '0' NOT NULL,
	sale_track_id varchar(30) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY sale_track_id (sale_track_id),
  	KEY session_id (session_id)
);
#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	item varchar(255) NOT NULL default '',
	newsletter tinyint(4) DEFAULT '0' NOT NULL,
);
#
# Table structure for table 'tx_topic_detail'
#
CREATE TABLE  tx_topic_detail (
  uid int(11) NOT NULL AUTO_INCREMENT,
  pid int(11) NOT NULL DEFAULT '0',
  tstamp int(11) NOT NULL DEFAULT '0',
  crdate int(11) NOT NULL DEFAULT '0',
  cruser_id int(11) NOT NULL DEFAULT '0',
  deleted tinyint(4) NOT NULL DEFAULT '0',
  hidden tinyint(4) NOT NULL DEFAULT '0',
  title text NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);
