BEGIN;

CREATE TABLE `__tblVersion` (
  `date` TEXT default NULL,
  `major` INTEGER,
  `minor` INTEGER,
  `subminor` INTEGER
);

INSERT INTO `__tblVersion` SELECT * FROM `tblVersion`;

DROP TABLE `tblVersion`;

ALTER TABLE `__tblVersion` RENAME TO `tblVersion`;

CREATE TABLE `__tblUserImages` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  `image` blob NOT NULL,
  `mimeType` varchar(100) NOT NULL default ''
);

INSERT INTO `__tblUserImages` SELECT * FROM `tblUserImages`;

DROP TABLE `tblUserImages`;

ALTER TABLE `__tblUserImages` RENAME TO `tblUserImages`;

CREATE TABLE `__tblDocumentContent` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `document` INTEGER NOT NULL default '0' REFERENCES `tblDocuments` (`id`),
  `version` INTEGER unsigned NOT NULL,
  `comment` text,
  `date` INTEGER default NULL,
  `createdBy` INTEGER default NULL,
  `dir` varchar(255) NOT NULL default '',
  `orgFileName` varchar(150) NOT NULL default '',
  `fileType` varchar(10) NOT NULL default '',
  `mimeType` varchar(100) NOT NULL default '',
  `fileSize` INTEGER,
  `checksum` char(32),
  UNIQUE (`document`,`version`)
);

INSERT INTO `__tblDocumentContent` SELECT * FROM `tblDocumentContent`;

DROP TABLE `tblDocumentContent`;

ALTER TABLE `__tblDocumentContent` RENAME TO `tblDocumentContent`;

CREATE TABLE `__tblDocumentFiles` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `document` INTEGER NOT NULL default 0 REFERENCES `tblDocuments` (`id`),
  `userID` INTEGER NOT NULL default 0 REFERENCES `tblUsers` (`id`),
  `comment` text,
  `name` varchar(150) default NULL,
  `date` INTEGER default NULL,
  `dir` varchar(255) NOT NULL default '',
  `orgFileName` varchar(150) NOT NULL default '',
  `fileType` varchar(10) NOT NULL default '',
  `mimeType` varchar(100) NOT NULL default ''
) ;

INSERT INTO `__tblDocumentFiles` SELECT * FROM `tblDocumentFiles`;

DROP TABLE `tblDocumentFiles`;

ALTER TABLE `__tblDocumentFiles` RENAME TO `tblDocumentFiles`;

CREATE TABLE `__tblUsers` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `login` varchar(50) default NULL,
  `pwd` varchar(50) default NULL,
  `fullName` varchar(100) default NULL,
  `email` varchar(70) default NULL,
  `language` varchar(32) NOT NULL,
  `theme` varchar(32) NOT NULL,
  `comment` text NOT NULL,
  `role` INTEGER NOT NULL default '0',
  `hidden` INTEGER NOT NULL default '0',
  `pwdExpiration` TEXT default NULL,
  `loginfailures` INTEGER NOT NULL default '0',
  `disabled` INTEGER NOT NULL default '0',
  `quota` INTEGER,
  `homefolder` INTEGER default NULL REFERENCES `tblFolders` (`id`),
  UNIQUE (`login`)
);

INSERT INTO `__tblUsers` SELECT * FROM `tblUsers`;

DROP TABLE `tblUsers`;

ALTER TABLE `__tblUsers` RENAME TO `tblUsers`;

CREATE TABLE `__tblUserPasswordRequest` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  `hash` varchar(50) default NULL,
  `date` TEXT NOT NULL
);

INSERT INTO `__tblUserPasswordRequest` SELECT * FROM `tblUserPasswordRequest`;

DROP TABLE `tblUserPasswordRequest`;

ALTER TABLE `__tblUserPasswordRequest` RENAME TO `tblUserPasswordRequest`;

CREATE TABLE `__tblUserPasswordHistory` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  `pwd` varchar(50) default NULL,
  `date` TEXT NOT NULL
);

INSERT INTO `__tblUserPasswordHistory` SELECT * FROM `tblUserPasswordHistory`;

DROP TABLE `tblUserPasswordHistory`;

ALTER TABLE `__tblUserPasswordHistory` RENAME TO `tblUserPasswordHistory`;

CREATE TABLE `__tblDocumentReviewLog` (
  `reviewLogID` INTEGER PRIMARY KEY AUTOINCREMENT,
  `reviewID` INTEGER NOT NULL default 0 REFERENCES `tblDocumentReviewers` (`reviewID`) ON DELETE CASCADE,
  `status` INTEGER NOT NULL default 0,
  `comment` TEXT NOT NULL,
  `date` TEXT NOT NULL,
  `userID` INTEGER NOT NULL default 0 REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
);

INSERT INTO `__tblDocumentReviewLog` SELECT * FROM `tblDocumentReviewLog`;

DROP TABLE `tblDocumentReviewLog`;

ALTER TABLE `__tblDocumentReviewLog` RENAME TO `tblDocumentReviewLog`;

CREATE TABLE `__tblDocumentStatusLog` (
  `statusLogID` INTEGER PRIMARY KEY AUTOINCREMENT,
  `statusID` INTEGER NOT NULL default '0' REFERENCES `tblDocumentStatus` (`statusID`) ON DELETE CASCADE,
  `status` INTEGER NOT NULL default '0',
  `comment` text NOT NULL,
  `date` TEXT NOT NULL,
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ;

INSERT INTO `__tblDocumentStatusLog` SELECT * FROM `tblDocumentStatusLog`;

DROP TABLE `tblDocumentStatusLog`;

ALTER TABLE `__tblDocumentStatusLog` RENAME TO `tblDocumentStatusLog`;

CREATE TABLE `__tblDocumentApproveLog` (
  `approveLogID` INTEGER PRIMARY KEY AUTOINCREMENT,
  `approveID` INTEGER NOT NULL default '0' REFERENCES `tblDocumentApprovers` (`approveID`) ON DELETE CASCADE,
  `status` INTEGER NOT NULL default '0',
  `comment` TEXT NOT NULL,
  `date` TEXT NOT NULL,
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
);

INSERT INTO `__tblDocumentApproveLog` SELECT * FROM `tblDocumentApproveLog`;

DROP TABLE `tblDocumentApproveLog`;

ALTER TABLE `__tblDocumentApproveLog` RENAME TO `tblDocumentApproveLog`;

CREATE TABLE `__tblWorkflowLog` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `document` INTEGER default NULL REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  `version` INTEGER default NULL,
  `workflow` INTEGER default NULL REFERENCES `tblWorkflows` (`id`) ON DELETE CASCADE,
  `userid` INTEGER default NULL REFERENCES `tblUsers` (`id`) ON DELETE CASCADE,
  `transition` INTEGER default NULL REFERENCES `tblWorkflowTransitions` (`id`) ON DELETE CASCADE,
  `date` datetime NOT NULL,
  `comment` text
);

INSERT INTO `__tblWorkflowLog` SELECT * FROM `tblWorkflowLog`;

DROP TABLE `tblWorkflowLog`;

ALTER TABLE `__tblWorkflowLog` RENAME TO `tblWorkflowLog`;

CREATE TABLE `__tblWorkflowDocumentContent` (
  `parentworkflow` INTEGER DEFAULT 0,
  `workflow` INTEGER DEFAULT NULL REFERENCES `tblWorkflows` (`id`) ON DELETE CASCADE,
  `document` INTEGER DEFAULT NULL REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  `version` INTEGER DEFAULT NULL,
  `state` INTEGER DEFAULT NULL REFERENCES `tblWorkflowStates` (`id`) ON DELETE CASCADE,
  `date` datetime NOT NULL
);

INSERT INTO `__tblWorkflowDocumentContent` SELECT * FROM `tblWorkflowDocumentContent`;

DROP TABLE `tblWorkflowDocumentContent`;

ALTER TABLE `__tblWorkflowDocumentContent` RENAME TO `tblWorkflowDocumentContent`;

UPDATE tblVersion set major=5, minor=1, subminor=0;

COMMIT;

