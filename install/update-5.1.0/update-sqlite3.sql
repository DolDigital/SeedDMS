BEGIN;

ALTER TABLE `tblDocumentContent` ADD COLUMN `revisiondate` TEXT NOT NULL default '0000-00-00 00:00:00';

CREATE TABLE `tblUserSubstitutes` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  `substitute` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  UNIQUE (`user`, `substitute`)
);

CREATE TABLE `tblDocumentCheckOuts` (
  `document` INTEGER REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  `version` INTEGER unsigned NOT NULL default '0',
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`),
  `date` TEXT NOT NULL default '0000-00-00 00:00:00',
  `filename` varchar(255) NOT NULL default '',
  UNIQUE (`document`)
) ;

CREATE TABLE `tblDocumentRecipients` (
  `receiptID` INTEGER PRIMARY KEY AUTOINCREMENT,
  `documentID` INTEGER NOT NULL default '0' REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  `version` INTEGER unsigned NOT NULL default '0',
  `type` INTEGER NOT NULL default '0',
  `required` INTEGER NOT NULL default '0',
  UNIQUE (`documentID`,`version`,`type`,`required`)
) ;

CREATE TABLE `tblDocumentReceiptLog` (
  `receiptLogID` INTEGER PRIMARY KEY AUTOINCREMENT,
  `receiptID` INTEGER NOT NULL default 0 REFERENCES `tblDocumentRecipients` (`receiptID`) ON DELETE CASCADE,
  `status` INTEGER NOT NULL default 0,
  `comment` TEXT NOT NULL,
  `date` TEXT NOT NULL default '0000-00-00 00:00:00',
  `userID` INTEGER NOT NULL default 0 REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ;

CREATE TABLE `tblDocumentRevisors` (
  `revisionID` INTEGER PRIMARY KEY AUTOINCREMENT,
  `documentID` INTEGER NOT NULL default '0' REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  `version` INTEGER unsigned NOT NULL default '0',
  `type` INTEGER NOT NULL default '0',
  `required` INTEGER NOT NULL default '0',
  `startdate` TEXT default NULL,
  UNIQUE (`documentID`,`version`,`type`,`required`)
) ;

CREATE TABLE `tblDocumentRevisionLog` (
  `revisionLogID` INTEGER PRIMARY KEY AUTOINCREMENT,
  `revisionID` INTEGER NOT NULL default 0 REFERENCES `tblDocumentRevisors` (`revisionID`) ON DELETE CASCADE,
  `status` INTEGER NOT NULL default 0,
  `comment` TEXT NOT NULL,
  `date` TEXT NOT NULL default '0000-00-00 00:00:00',
  `userID` INTEGER NOT NULL default 0 REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ;

CREATE TABLE tblTransmittals (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` text NOT NULL,
  `comment` text NOT NULL,
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  `date` TEXT NOT NULL default '0000-00-00 00:00:00',
  `public` INTEGER NOT NULL default '0'
);

CREATE TABLE `tblTransmittalItems` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
	`transmittal` INTEGER NOT NULL DEFAULT '0' REFERENCES `tblTransmittals` (`id`) ON DELETE CASCADE,
  `document` INTEGER default NULL REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  `version` INTEGER unsigned NOT NULL default '0',
  `date` TEXT NOT NULL default '0000-00-00 00:00:00',
  UNIQUE (document, version)
);

CREATE TABLE `tblRoles` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(50) default NULL,
  `role` INTEGER NOT NULL default '0',
  UNIQUE (`name`)
);

INSERT INTO `tblRoles` (`id`, `name`, `role`) VALUES (1, 'Admin', 1);
INSERT INTO `tblRoles` (`id`, `name`, `role`) VALUES (2, 'Guest', 2);
INSERT INTO `tblRoles` (`id`, `name`, `role`) VALUES (3, 'User', 0);

UPDATE `tblUsers` SET role=3 WHERE role=0;

CREATE TABLE `new_tblUsers` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `login` varchar(50) default NULL,
  `pwd` varchar(50) default NULL,
  `fullName` varchar(100) default NULL,
  `email` varchar(70) default NULL,
  `language` varchar(32) NOT NULL,
  `theme` varchar(32) NOT NULL,
  `comment` text NOT NULL,
  `role` INTEGER NOT NULL REFERENCES `tblRoles` (`id`),
  `hidden` INTEGER NOT NULL default '0',
  `pwdExpiration` TEXT NOT NULL default '0000-00-00 00:00:00',
  `loginfailures` INTEGER NOT NULL default '0',
  `disabled` INTEGER NOT NULL default '0',
  `quota` INTEGER,
  `homefolder` INTEGER default NULL REFERENCES `tblFolders` (`id`),
  UNIQUE (`login`)
);

INSERT INTO new_tblUsers SELECT * FROM tblUsers;

DROP TABLE tblUsers;

ALTER TABLE new_tblUsers RENAME TO tblUsers;

CREATE TABLE `tblAros` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `parent` INTEGER,
  `model` TEXT NOT NULL,
  `foreignid` INTEGER NOT NULL DEFAULT '0',
  `alias` TEXT
) ;

CREATE TABLE `tblAcos` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `parent` INTEGER,
  `model` TEXT NOT NULL,
  `foreignid` INTEGER NOT NULL DEFAULT '0',
  `alias` TEXT
) ;

CREATE TABLE `tblArosAcos` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `aro` INTEGER NOT NULL DEFAULT '0' REFERENCES `tblAros` (`id`) ON DELETE CASCADE,
  `aco` INTEGER NOT NULL DEFAULT '0' REFERENCES `tblAcos` (`id`) ON DELETE CASCADE,
  `create` INTEGER NOT NULL DEFAULT '-1',
  `read` INTEGER NOT NULL DEFAULT '-1',
  `update` INTEGER NOT NULL DEFAULT '-1',
  `delete` INTEGER NOT NULL DEFAULT '-1',
  UNIQUE (aco, aro)
) ;

UPDATE tblVersion set major=5, minor=1, subminor=0;

COMMIT;
