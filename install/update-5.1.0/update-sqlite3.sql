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

UPDATE tblVersion set major=5, minor=1, subminor=0;

COMMIT;
