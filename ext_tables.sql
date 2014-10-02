#
# Add field to table 'be_groups'
#
CREATE TABLE be_groups (
	tx_snowbabel_extensions TINYTEXT,
	tx_snowbabel_languages  TINYTEXT
);

#
# Add field to table 'be_users'
#
CREATE TABLE be_users (
	tx_snowbabel_extensions TINYTEXT,
	tx_snowbabel_languages  TINYTEXT
);

CREATE TABLE tx_snowbabel_users (
	uid               INT(11)                NOT NULL AUTO_INCREMENT,
	pid               INT(11) DEFAULT '0'    NOT NULL,
	tstamp            INT(11) DEFAULT '0'    NOT NULL,
	crdate            INT(11) DEFAULT '0'    NOT NULL,
	deleted           TINYINT(4) DEFAULT '0' NOT NULL,
	be_users_uid      INT(11) DEFAULT '0'    NOT NULL,
	SelectedLanguages TINYTEXT               NOT NULL,
	ShowColumnLabel   TINYINT(4) DEFAULT '1' NOT NULL,
	ShowColumnDefault TINYINT(4) DEFAULT '1' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_temp (
	TableId INT(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_snowbabel_indexing_extensions_0 (
	uid                  INT(11)                NOT NULL AUTO_INCREMENT,
	pid                  INT(11) DEFAULT '0'    NOT NULL,
	tstamp               INT(11) DEFAULT '0'    NOT NULL,
	crdate               INT(11) DEFAULT '0'    NOT NULL,

	ExtensionKey         TINYTEXT               NOT NULL,
	ExtensionTitle       TINYTEXT               NOT NULL,
	ExtensionDescription TEXT,
	ExtensionCategory    TINYTEXT               NOT NULL,
	ExtensionIcon        TINYTEXT               NOT NULL,
	ExtensionLocation    TINYTEXT               NOT NULL,
	ExtensionPath        TEXT,
	ExtensionLoaded      TINYINT(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_extensions_1 (
	uid                  INT(11)                NOT NULL AUTO_INCREMENT,
	pid                  INT(11) DEFAULT '0'    NOT NULL,
	tstamp               INT(11) DEFAULT '0'    NOT NULL,
	crdate               INT(11) DEFAULT '0'    NOT NULL,

	ExtensionKey         TINYTEXT               NOT NULL,
	ExtensionTitle       TINYTEXT               NOT NULL,
	ExtensionDescription TEXT,
	ExtensionCategory    TINYTEXT               NOT NULL,
	ExtensionIcon        TINYTEXT               NOT NULL,
	ExtensionLocation    TINYTEXT               NOT NULL,
	ExtensionPath        TEXT,
	ExtensionLoaded      TINYINT(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_files_0 (
	uid         INT(11)             NOT NULL AUTO_INCREMENT,
	pid         INT(11) DEFAULT '0' NOT NULL,
	tstamp      INT(11) DEFAULT '0' NOT NULL,
	crdate      INT(11) DEFAULT '0' NOT NULL,

	ExtensionId INT(11) DEFAULT '0' NOT NULL,
	FileKey     TINYTEXT            NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_files_1 (
	uid         INT(11)             NOT NULL AUTO_INCREMENT,
	pid         INT(11) DEFAULT '0' NOT NULL,
	tstamp      INT(11) DEFAULT '0' NOT NULL,
	crdate      INT(11) DEFAULT '0' NOT NULL,

	ExtensionId INT(11) DEFAULT '0' NOT NULL,
	FileKey     TINYTEXT            NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_labels_0 (
	uid          INT(11)             NOT NULL AUTO_INCREMENT,
	pid          INT(11) DEFAULT '0' NOT NULL,
	tstamp       INT(11) DEFAULT '0' NOT NULL,
	crdate       INT(11) DEFAULT '0' NOT NULL,

	FileId       INT(11) DEFAULT '0' NOT NULL,
	LabelName    TINYTEXT            NOT NULL,
	LabelDefault TEXT,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_labels_1 (
	uid          INT(11)             NOT NULL AUTO_INCREMENT,
	pid          INT(11) DEFAULT '0' NOT NULL,
	tstamp       INT(11) DEFAULT '0' NOT NULL,
	crdate       INT(11) DEFAULT '0' NOT NULL,

	FileId       INT(11) DEFAULT '0' NOT NULL,
	LabelName    TINYTEXT            NOT NULL,
	LabelDefault TEXT,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_snowbabel_indexing_translations_0 (
	uid                 INT(11)                NOT NULL AUTO_INCREMENT,
	pid                 INT(11) DEFAULT '0'    NOT NULL,
	tstamp              INT(11) DEFAULT '0'    NOT NULL,
	crdate              INT(11) DEFAULT '0'    NOT NULL,

	LabelId             INT(11) DEFAULT '0'    NOT NULL,
	TranslationValue    TEXT,
	TranslationLanguage TINYTEXT               NOT NULL,
	TranslationEmpty    TINYINT(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY `labellang` (`LabelId`, `TranslationLanguage`(4))
);

CREATE TABLE tx_snowbabel_indexing_translations_1 (
	uid                 INT(11)                NOT NULL AUTO_INCREMENT,
	pid                 INT(11) DEFAULT '0'    NOT NULL,
	tstamp              INT(11) DEFAULT '0'    NOT NULL,
	crdate              INT(11) DEFAULT '0'    NOT NULL,

	LabelId             INT(11) DEFAULT '0'    NOT NULL,
	TranslationValue    TEXT,
	TranslationLanguage TINYTEXT               NOT NULL,
	TranslationEmpty    TINYINT(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY `labellang` (`LabelId`, `TranslationLanguage`(4))
);