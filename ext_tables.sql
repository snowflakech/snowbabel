#
# Add field to table 'be_groups'
#
CREATE TABLE be_groups (
	tx_snowbabel_extensions tinytext,
	tx_snowbabel_languages tinytext
);

#
# Add field to table 'be_users'
#
CREATE TABLE be_users (
	tx_snowbabel_extensions tinytext,
	tx_snowbabel_languages tinytext
);

CREATE TABLE tx_snowbabel_users (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	be_users_uid int(11) DEFAULT '0' NOT NULL,
	SelectedLanguages tinytext NOT NULL,
	ShowColumnLabel tinyint(4) DEFAULT '1' NOT NULL,
	ShowColumnDefault tinyint(4) DEFAULT '1' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_temp (
	TableId int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_snowbabel_indexing_extensions_0 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	ExtensionKey tinytext NOT NULL,
	ExtensionTitle tinytext NOT NULL,
	ExtensionDescription text,
	ExtensionCategory tinytext NOT NULL,
	ExtensionIcon tinytext NOT NULL,
	ExtensionLocation tinytext NOT NULL,
	ExtensionPath text,
	ExtensionLoaded tinyint(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_extensions_1 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	ExtensionKey tinytext NOT NULL,
	ExtensionTitle tinytext NOT NULL,
	ExtensionDescription text,
	ExtensionCategory tinytext NOT NULL,
	ExtensionIcon tinytext NOT NULL,
	ExtensionLocation tinytext NOT NULL,
	ExtensionPath text,
	ExtensionLoaded tinyint(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_files_0 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	ExtensionId int(11) DEFAULT '0' NOT NULL,
	FileKey tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_files_1 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	ExtensionId int(11) DEFAULT '0' NOT NULL,
	FileKey tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_labels_0 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	FileId int(11) DEFAULT '0' NOT NULL,
	LabelName tinytext NOT NULL,
	LabelDefault text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_labels_1 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	FileId int(11) DEFAULT '0' NOT NULL,
	LabelName tinytext NOT NULL,
	LabelDefault text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_translations_0 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	LabelId int(11) DEFAULT '0' NOT NULL,
	TranslationValue text,
	TranslationLanguage tinytext NOT NULL,
	TranslationEmpty tinyint(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY `labellang` (`LabelId`,`TranslationLanguage`(4))
);

CREATE TABLE tx_snowbabel_indexing_translations_1 (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	LabelId int(11) DEFAULT '0' NOT NULL,
	TranslationValue text,
	TranslationLanguage tinytext NOT NULL,
	TranslationEmpty tinyint(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY `labellang` (`LabelId`,`TranslationLanguage`(4))
);