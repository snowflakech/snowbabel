#
# Table structure for table 'tx_snowbabel_domain_model_extensionsetting'
#
CREATE TABLE tx_snowbabel_domain_model_extensionsetting (

  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,

  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(3) unsigned DEFAULT '0' NOT NULL,

	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	extension_key varchar(255) DEFAULT '' NOT NULL,
	selected tinyint(1) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid,sorting)
);
