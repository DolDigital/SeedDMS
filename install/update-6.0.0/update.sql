START TRANSACTION;

ALTER TABLE `tblDocumentContent` ADD COLUMN `revisiondate` datetime default NULL;


CREATE TABLE `tblUserSubstitutes` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) default null,
  `substitute` int(11) default null,
  PRIMARY KEY (`id`),
  UNIQUE (`user`, `substitute`),
  CONSTRAINT `tblUserSubstitutes_user` FOREIGN KEY (`user`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tblUserSubstitutes_substitute` FOREIGN KEY (`user`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
);

CREATE TABLE `tblDocumentCheckOuts` (
  `document` int(11) NOT NULL default '0',
  `version` smallint(5) unsigned NOT NULL default '0',
  `userID` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`document`),
  CONSTRAINT `tblDocumentCheckOuts_document` FOREIGN KEY (`document`) REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tblDocumentCheckOuts_user` FOREIGN KEY (`userID`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblDocumentRecipients` (
  `receiptID` int(11) NOT NULL auto_increment,
  `documentID` int(11) NOT NULL default '0',
  `version` smallint(5) unsigned NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '0',
  `required` int(11) NOT NULL default '0',
  PRIMARY KEY  (`receiptID`),
  UNIQUE KEY `documentID` (`documentID`,`version`,`type`,`required`),
  CONSTRAINT `tblDocumentRecipients_document` FOREIGN KEY (`documentID`) REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblDocumentReceiptLog` (
  `receiptLogID` int(11) NOT NULL auto_increment,
  `receiptID` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  `comment` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `userID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`receiptLogID`),
  CONSTRAINT `tblDocumentReceiptLog_recipient` FOREIGN KEY (`receiptID`) REFERENCES `tblDocumentRecipients` (`receiptID`) ON DELETE CASCADE,
  CONSTRAINT `tblDocumentReceiptLog_user` FOREIGN KEY (`userID`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblDocumentRevisors` (
  `revisionID` int(11) NOT NULL auto_increment,
  `documentID` int(11) NOT NULL default '0',
  `version` smallint(5) unsigned NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '0',
  `required` int(11) NOT NULL default '0',
  `startdate` datetime default NULL,
  PRIMARY KEY  (`revisionID`),
  UNIQUE KEY `documentID` (`documentID`,`version`,`type`,`required`),
  CONSTRAINT `tblDocumentRevisors_document` FOREIGN KEY (`documentID`) REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblDocumentRevisionLog` (
  `revisionLogID` int(11) NOT NULL auto_increment,
  `revisionID` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  `comment` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `userID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`revisionLogID`),
  CONSTRAINT `tblDocumentRevisionLog_revision` FOREIGN KEY (`revisionID`) REFERENCES `tblDocumentRevisors` (`revisionID`) ON DELETE CASCADE,
  CONSTRAINT `tblDocumentRevisionLog_user` FOREIGN KEY (`userID`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblTransmittals` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `comment` text NOT NULL,
  `userID` int(11) NOT NULL default '0',
  `date` datetime,
  `public` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  CONSTRAINT `tblTransmittals_user` FOREIGN KEY (`userID`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblTransmittalItems` (
  `id` int(11) NOT NULL auto_increment,
	`transmittal` int(11) NOT NULL DEFAULT '0',
  `document` int(11) default NULL,
  `version` smallint(5) unsigned NOT NULL default '0',
  `date` datetime,
  PRIMARY KEY  (`id`),
  UNIQUE (document, version),
  CONSTRAINT `tblTransmittalItems_document` FOREIGN KEY (`document`) REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tblTransmittalItem_transmittal` FOREIGN KEY (`transmittal`) REFERENCES `tblTransmittals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE tblVersion set major=6, minor=0, subminor=0;

COMMIT;


