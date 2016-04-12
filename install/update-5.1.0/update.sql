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

CREATE TABLE `tblRoles` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `role` smallint(1) NOT NULL default '0',
  `noaccess` varchar(30) NOT NULL default '',
  PRIMARY KEY (`id`),
  UNIQUE (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tblRoles` (`id`, `name`, `role`) VALUES (1, 'Admin', 1);
INSERT INTO `tblRoles` (`id`, `name`, `role`) VALUES (2, 'Guest', 2);
INSERT INTO `tblRoles` (`id`, `name`, `role`) VALUES (3, 'User', 0);
ALTER TABLE `tblRoles` AUTO_INCREMENT=4;

ALTER TABLE tblUsers CHANGE role role int(11) NOT NULL;
UPDATE `tblUsers` SET role=3 WHERE role=0;
ALTER TABLE tblUsers ADD CONSTRAINT `tblUsers_role` FOREIGN KEY (`role`) REFERENCES `tblRoles` (`id`);

CREATE TABLE `tblAros` (
  `id` int(11) NOT NULL auto_increment,
  `parent` int(11),
  `model` text NOT NULL,
  `foreignid` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblAcos` (
  `id` int(11) NOT NULL auto_increment,
  `parent` int(11),
  `model` text NOT NULL,
  `foreignid` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tblArosAcos` (
  `id` int(11) NOT NULL auto_increment,
  `aro` int(11) NOT NULL DEFAULT '0',
  `aco` int(11) NOT NULL DEFAULT '0',
  `create` tinyint(4) NOT NULL DEFAULT '-1',
  `read` tinyint(4) NOT NULL DEFAULT '-1',
  `update` tinyint(4) NOT NULL DEFAULT '-1',
  `delete` tinyint(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE (aco, aro),
  CONSTRAINT `tblArosAcos_acos` FOREIGN KEY (`aco`) REFERENCES `tblAcos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tblArosAcos_aros` FOREIGN KEY (`aro`) REFERENCES `tblAros` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE tblVersion set major=5, minor=1, subminor=0;

COMMIT;


