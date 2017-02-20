START TRANSACTION;

ALTER TABLE `tblDocumentContent` CHANGE `mimeType` `mimeType` varchar(100) NOT NULL default '';

ALTER TABLE `tblDocumentFiles` CHANGE `mimeType` `mimeType` varchar(100) NOT NULL default '';

ALTER TABLE `tblUserImages` CHANGE `mimeType` `mimeType` varchar(100) NOT NULL default '';

ALTER TABLE `tblUsers` CHANGE `pwdExpiration` `pwdExpiration` datetime default NULL;

ALTER TABLE `tblUserPasswordRequest` CHANGE `date` `date` datetime NOT NULL;

ALTER TABLE `tblUserPasswordHistory` CHANGE `date` `date` datetime NOT NULL;

ALTER TABLE `tblDocumentApproveLog` CHANGE `date` `date` datetime NOT NULL;

ALTER TABLE `tblDocumentReviewLog` CHANGE `date` `date` datetime NOT NULL;

ALTER TABLE `tblDocumentStatusLog` CHANGE `date` `date` datetime NOT NULL;

ALTER TABLE `tblWorkflowLog` CHANGE `date` `date` datetime NOT NULL;

ALTER TABLE `tblWorkflowDocumentContent` CHANGE `date` `date` datetime NOT NULL;

ALTER TABLE `tblVersion` CHANGE `date` `date` datetime NOT NULL;

COMMIT;

