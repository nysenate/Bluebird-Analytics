/* Create the debug log procedure */
DROP PROCEDURE IF EXISTS nyss_debug_log;
DELIMITER //
CREATE DEFINER=CURRENT_USER PROCEDURE nyss_debug_log(IN msg TEXT)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	IF @nyss_debug_flag IS NOT NULL THEN
		BEGIN
			SET @nyss_debug_function_thismsg = IFNULL(msg,'');
			IF @nyss_debug_function_thismsg = '' THEN
				SET @nyss_debug_function_thismsg='No Message Provided';
			END IF;
			SELECT COUNT(*) INTO @nyss_debug_function_table_count
				FROM information_schema.tables
				WHERE table_schema = DATABASE() AND table_name = 'nyss_debug';
			IF IFNULL(@nyss_debug_function_table_count,0) < 1 THEN
				BEGIN
					DROP TABLE IF EXISTS nyss_debug;
				  CREATE TABLE nyss_debug (
						id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
						msg TEXT,
						ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP
					);
				END;
			END IF;
			INSERT INTO nyss_debug (msg) VALUES (@nyss_debug_function_thismsg);
			SET @nyss_debug_function_thismsg = NULL;
			SET @nyss_debug_function_table_count = NULL;
		END;
	END IF;
END
//
DELIMITER ;

/* add location table, structure and data */
DROP TABLE IF EXISTS location;
CREATE TABLE location (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64),
    ipv4_start INT UNSIGNED,
    ipv4_end INT UNSIGNED,
    INDEX location__ipv4_range (ipv4_start,ipv4_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO location (name,ipv4_start,ipv4_end) VALUES
('Not Found', NULL, NULL),
('LOB Fl B3', INET_ATON('10.11.3.26'), INET_ATON('10.11.3.254')),
('LOB Fl B2', INET_ATON('10.12.3.26'), INET_ATON('10.12.3.254')),
('LOB Fl 1', INET_ATON('10.13.4.26'), INET_ATON('10.13.5.254')),
('LOB Fl 2', INET_ATON('10.14.3.26'), INET_ATON('10.14.3.254')),
('LOB Fl 3', INET_ATON('10.15.3.26'), INET_ATON('10.15.3.254')),
('LOB Fl 4', INET_ATON('10.16.3.26'), INET_ATON('10.16.3.254')),
('LOB Fl 5', INET_ATON('10.17.3.26'), INET_ATON('10.17.3.254')),
('LOB Fl 6', INET_ATON('10.18.3.26'), INET_ATON('10.18.3.254')),
('LOB Fl 7', INET_ATON('10.19.3.26'), INET_ATON('10.19.3.254')),
('LOB Fl 8', INET_ATON('10.20.4.26'), INET_ATON('10.20.5.254')),
('LOB Fl 9', INET_ATON('10.21.4.26'), INET_ATON('10.21.5.254')),
('LOB 250 Broadway', INET_ATON('10.28.3.26'), INET_ATON('10.28.3.254')),
('A.E.S. Fl 13', INET_ATON('10.23.3.26'), INET_ATON('10.23.3.254')),
('A.E.S. Fl 14', INET_ATON('10.23.4.26'), INET_ATON('10.23.4.254')),
('A.E.S. Fl 15', INET_ATON('10.23.5.26'), INET_ATON('10.23.5.254')),
('A.E.S. Fl 16', INET_ATON('10.23.6.26'), INET_ATON('10.23.6.254')),
('A.E.S. Fl 24', INET_ATON('10.23.7.26'), INET_ATON('10.23.7.254')),
('A.E.S. Fl 25', INET_ATON('10.23.8.26'), INET_ATON('10.23.8.254')),
('A.E.S. Fl 26', INET_ATON('10.23.9.26'), INET_ATON('10.23.9.254')),
('A.E.S. Basement', INET_ATON('10.23.10.26'), INET_ATON('10.23.10.254')),
('Corporate Woods', INET_ATON('10.31.3.26'), INET_ATON('10.31.3.254')),
('Capitol West', INET_ATON('10.24.4.26'), INET_ATON('10.24.5.254')),
('Capitol East Fl 3', INET_ATON('10.25.3.26'), INET_ATON('10.25.3.254')),
('Capitol East Fl 4', INET_ATON('10.25.4.26'), INET_ATON('10.25.4.254')),
('Capitol East Fl 5', INET_ATON('10.25.5.26'), INET_ATON('10.25.5.254')),
('Agency-4 Fl 2 & Fl 11', INET_ATON('10.26.3.26'), INET_ATON('10.26.3.254')),
('Agency-4 Fl 16 & Fl 17', INET_ATON('10.26.4.26'), INET_ATON('10.26.4.254')),
('Agency-4 Fl 18', INET_ATON('10.26.5.26'), INET_ATON('10.26.5.254')),
('Satellite Offices', INET_ATON('10.99.1.0'), INET_ATON('10.99.40.254')),
('VPN User', INET_ATON('10.99.96.0'), INET_ATON('10.99.96.255')),
('VPN ?', INET_ATON('10.99.97.0'), INET_ATON('10.99.97.240')),
('VPN Sfms', INET_ATON('10.99.97.241'), INET_ATON('10.99.97.254')),
('VPN Telecom Vendor', INET_ATON('10.99.97.230'), INET_ATON('10.99.97.239')),
('VPN Asax', INET_ATON('10.99.98.0'), INET_ATON('10.99.98.255')),
('District Offices', INET_ATON('10.41.0.0'), INET_ATON('10.41.255.255')),
('District Offices', INET_ATON('10.42.0.0'), INET_ATON('10.42.255.255')),
('District Offices', INET_ATON('172.18.0.0'), INET_ATON('172.18.255.255')),
('District Offices', INET_ATON('172.28.0.0'), INET_ATON('172.28.255.255')),
('District Offices Visitor', INET_ATON('172.19.0.0'), INET_ATON('172.19.255.255')),
('District Offices Visitor', INET_ATON('172.29.0.0'), INET_ATON('172.29.255.255')),
('Wireless', INET_ATON('10.3.10.1'), INET_ATON('10.3.10.255')),
('Wireless LOB', INET_ATON('10.3.12.0'), INET_ATON('10.3.12.255')),
('Wireless Agency-4', INET_ATON('10.3.13.0'), INET_ATON('10.3.13.255')),
('Wireless A.E.S.', INET_ATON('10.3.14.0'), INET_ATON('10.3.14.255')),
('Wireless Capitol', INET_ATON('10.3.15.0'), INET_ATON('10.3.15.255')),
('Wireless C.Woods', INET_ATON('10.3.16.0'), INET_ATON('10.3.16.255')),
('Wireless District Offices', INET_ATON('10.3.17.0'), INET_ATON('10.3.17.255')),
('Wireless LOB-Top-Fls', INET_ATON('10.3.18.0'), INET_ATON('10.3.18.255')),
('Wireless Visitor', INET_ATON('10.99.70.0'), INET_ATON('10.99.71.254')),
('Wireless Visitor LOB', INET_ATON('10.99.72.0'), INET_ATON('10.99.72.255')),
('Wireless Visitor Agency-4', INET_ATON('10.99.73.0'), INET_ATON('10.99.73.255')),
('Wireless Visitor A.E.S.', INET_ATON('10.99.74.0'), INET_ATON('10.99.74.255')),
('Wireless Visitor Capitol', INET_ATON('10.99.75.0'), INET_ATON('10.99.75.255')),
('Wireless Visitor C.Woods', INET_ATON('10.99.76.0'), INET_ATON('10.99.76.255')),
('Wireless Visitor District Offices', INET_ATON('10.99.77.0'), INET_ATON('10.99.77.255')),
('Wireless Visitor LOB-Top-Fls', INET_ATON('10.99.78.0'), INET_ATON('10.99.78.255')),
('Serverfarm 1', INET_ATON('10.1.3.1'), INET_ATON('10.1.3.30')),
('Serverfarm 1', INET_ATON('10.1.4.1'), INET_ATON('10.1.4.254')),
('Serverfarm 2', INET_ATON('10.1.3.33'), INET_ATON('10.1.3.62')),
('Serverfarm 2', INET_ATON('10.1.5.1'), INET_ATON('10.1.5.254')),
('Serverfarm 3', INET_ATON('10.2.3.1'), INET_ATON('10.2.3.30')),
('Serverfarm 3', INET_ATON('10.1.6.1'), INET_ATON('10.1.6.254')),
('Serverfarm 4', INET_ATON('10.2.3.33'), INET_ATON('10.2.3.62')),
('Serverfarm 4', INET_ATON('10.1.7.1'), INET_ATON('10.1.7.254')),
('Serverfarm 5', INET_ATON('10.2.3.65'), INET_ATON('10.2.3.126')),
('AVAYA', INET_ATON('10.1.3.129'), INET_ATON('10.1.3.254')),
('AVAYA', INET_ATON('10.1.8.1'), INET_ATON('10.1.8.254'));


/* add url table, structure and data */
DROP TABLE IF EXISTS url;
CREATE TABLE url (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64),
    match_full BIT(1),
    action VARCHAR(6),
    path VARCHAR(255),
    search VARCHAR(32)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO url (name, match_full, action, path, search) VALUES
('Not Found', 0, 'read', NULL, NULL),
('Contact Add', 0, 'read', '/civicrm/contact', NULL),
('Contact View', 0, 'read', '/civicrm/contact/view', NULL),
('Contact View', 0, 'read', '/civicrm/contact/view/rel', NULL),
('Contact View Ajax', 0, 'read', '/civicrm/ajax/contact', NULL),
('Contact Delete', 0, 'delete', '/civicrm/contact/view/delete', NULL),
('Contact Merge', 0, 'update', '/civicrm/contact/merge', NULL),
('Contact Merge', 0, 'read', '/civicrm/contact/map', NULL),
('Contact Dedupe', 0, 'read', '/civicrm/contact/dedupefind', NULL),
('Contact Dedupe', 0, 'read', '/civicrm/ajax/dedupefind', NULL),
('Contact Print', 0, 'read', '/civicrm/contact/view/print', NULL),
('Contact Log View', 0, 'read', '/civicrm/contact/view/log', NULL),
('Contact Mailing', 0, 'read', '/civicrm/ajax/contactmailing', NULL),
('Contact Mailing', 0, 'read', '/civicrm/contact/view/mailing', NULL),
('Contact Tags', 0, 'read', '/civicrm/contact/view/tag', NULL),
('Contact Cases View', 0, 'read', '/civicrm/contact/view/case', NULL),
('Contact Cases Edit', 0, 'read', '/civicrm/contact/view/case/editClient', NULL),
('Contact Tag Create', 0, 'create', '/civicrm/ajax/entity_tag/create', NULL),
('Contact Tag Process', 0, 'create', '/civicrm/ajax/processTags', NULL),
('Contact Tag View', 0, 'read', '/civicrm/ajax/entity_tag/get', NULL),
('Contact Hover Details', 0, 'read', '/civicrm/profile/view', NULL),
('Contact Groups View', 0, 'read', '/civicrm/contact/view/group', NULL),
('Contact View', 0, 'read', '/civicrm/contact/view', NULL),
('Contact Changelog Count', 0, 'read', '/civicrm/ajax/count/changelog', NULL),
('Contact Activity Count', 0, 'read', '/civicrm/ajax/count/activity', NULL),
('Contact Inline Edit', 0, 'update', '/civicrm/ajax/inline', NULL),
('Contact Tag Tree', 0, 'update', '/civicrm/ajax/tag/tree', NULL),
('Contact Relationships', 0, 'read', '/civicrm/ajax/globalrelationships', NULL),
('Contact Relationships', 0, 'read', '/civicrm/ajax/relation', NULL),
('Contact Relationships', 0, 'read', '/civicrm/ajax/relationshipcontacts', NULL),
('Contact Relationships', 0, 'read', '/civicrm/ajax/relationshipContactTypeList', NULL),
('Contact Relationships', 0, 'read', '/civicrm/ajax/contactrelationships', NULL),
('Contact Relationships', 0, 'read', '/civicrm/ajax/clientrelationships', NULL),
('Contact Smartgroup View', 0, 'read', '/civicrm/contact/view/smartgroup', NULL),
('Contact Note View', 0, 'read', '/civicrm/contact/view/note', NULL),
('Contact Note Add', 0, 'create', '/civicrm/contact/addnode', NULL),
('Contact Changelog View', 0, 'read', '/civicrm/ajax/changelog', NULL),
('Contact Activity View', 0, 'read', '/civicrm/contact/view/activity', NULL),
('Contact Vcard', 0, 'read', '/civicrm/contact/view/vcard', NULL),
('Contact Election District', 0, 'read', '/civicrm/ajax/ed', NULL),
('Contact Update Script', 0, 'update', '/civicrm/scripts/updateAddresses.php', NULL),
('Contact Inline Edit', 0, 'update', '/civicrm/ajax/inlinenode/1,destination=civicrm/ajax/inlinenode', NULL),
('Contact Inline Edit', 0, 'update', '/civicrm/ajax/inlinenode', NULL),
('Contact Cases', 0, 'read', '/civicrm/contact/view/casenode', NULL),
('Activity View', 0, 'read', '/civicrm/imap/ajax/getActivityDetails', NULL),
('Activity View', 0, 'read', '/civicrm/ajax/activity', NULL),
('Activity View', 0, 'read', '/civicrm/activity/view', NULL),
('Activity Create', 0, 'read', '/civicrm/activity', NULL),
('Activity Update', 0, 'update', '/civicrm/case/activity', NULL),
('Activity Convert', 0, 'update', '/civicrm/ajax/activity/convert', NULL),
('Activity Types', 0, 'read', '/civicrm/admin/options/activity_type', NULL),
('Activity Email Add', 0, 'create', '/civicrm/activity/email/add', NULL),
('Activity Add Activity', 0, 'create', '/civicrm/activity/pdf/add', NULL),
('Activity Types', 0, 'read', '/civicrm/ajax/subtype', NULL),
('Activity New', 0, 'read', '/civicrm/activity/addnode', NULL),
('Activity Add', 0, 'create', '/civicrm/activity/add', NULL),
('Case View', 0, 'read', '/civicrm/case', NULL),
('Case Add', 0, 'create', '/civicrm/case/add', NULL),
('Case List', 0, 'read', '/civicrm/case/ajax/unclosed', NULL),
('Case Details', 0, 'read', '/civicrm/case/details', NULL),
('Case Details Ajax', 0, 'read', '/civicrm/case/ajax/details', NULL),
('Case Add To', 0, 'update', '/civicrm/case/addToCase', NULL),
('Case Roles', 0, 'read', '/civicrm/ajax/caseroles', NULL),
('Case', 0, 'read', '/civicrm/case/activity/view', NULL),
('Case Report Print', 0, 'read', '/civicrm/case/report/print', NULL),
('Case Status Update', 0, 'update', '/civicrm/case/changeactivitystatus', NULL),
('Case Delete Role', 0, 'delete', '/civicrm/ajax/delcaserole', NULL),
('InboundEmail Contact Search', 0, 'read', '/civicrm/imap/ajax/searchContacts', NULL),
('InboundEmail Contact Create', 0, 'create', '/civicrm/imap/ajax/createNewContact', NULL),
('InboundEmail Contact Search', 0, 'read', '/civicrm/imap/ajax/contact/search', NULL),
('InboundEmail Contact Add Email', 0, 'create', '/civicrm/imap/ajax/addEmail', NULL),
('InboundEmail Contact Add Email', 0, 'create', '/civicrm/imap/ajax/contact/addEmail', NULL),
('InboundEmail Contact Add', 0, 'create', '/civicrm/imap/ajax/contact/add', NULL),
('InboundEmail Matched List', 0, 'read', '/civicrm/imap/matched', NULL),
('InboundEmail Matched List Ajax', 0, 'read', '/civicrm/imap/ajax/listMatchedMessages', NULL),
('InboundEmail Matched List Ajax', 0, 'read', '/civicrm/imap/ajax/matched/list', NULL),
('InboundEmail Matched Clear', 0, 'read', '/civicrm/imap/ajax/matched/clear', NULL),
('InboundEmail Matched Clear', 0, 'update', '/civicrm/imap/ajax/untagActivity', NULL),
('InboundEmail Matched Delete', 0, 'read', '/civicrm/imap/ajax/matched/delete', NULL),
('InboundEmail Matched Details', 0, 'read', '/civicrm/imap/ajax/matched/details', NULL),
('InboundEmail Matched Reassign', 0, 'read', '/civicrm/imap/ajax/matched/reassign', NULL),
('InboundEmail Matched Reassign', 0, 'read', '/civicrm/imap/ajax/matched/edit', NULL),
('InboundEmail Unmatched List', 0, 'read', '/civicrm/imap/unmatched', NULL),
('InboundEmail Unmatched List Ajax', 0, 'read', '/civicrm/imap/ajax/listUnmatchedMessages', NULL),
('InboundEmail Unmatched List Ajax', 0, 'read', '/civicrm/imap/ajax/unmatched/list', NULL),
('InboundEmail Unmatched Assign', 0, 'update', '/civicrm/imap/ajax/unmatched/assign', NULL),
('InboundEmail Unmatched Details', 0, 'read', '/civicrm/imap/ajax/unmatched/details', NULL),
('InboundEmail Unmatched Delete', 0, 'read', '/civicrm/imap/ajax/unmatched/delete', NULL),
('InboundEmail Message Details', 0, 'read', '/civicrm/imap/ajax/getMessageDetails', NULL),
('InboundEmail Assign Message', 0, 'update', '/civicrm/imap/ajax/assignMessage', NULL),
('InboundEmail Delete Message', 0, 'delete', '/civicrm/imap/ajax/deleteMessage', NULL),
('InboundEmail Delete Message', 0, 'delete', '/civicrm/imap/ajax/deleteActivity', NULL),
('InboundEmail Reassign Message', 0, 'update', '/civicrm/imap/ajax/reassignActivity', NULL),
('InboundEmail Tag Assign Issuecode', 0, 'update', '/civicrm/imap/ajax/issuecode', NULL),
('InboundEmail Tag Search', 0, 'read', '/civicrm/imap/ajax/searchTags', NULL),
('InboundEmail Tag Search', 0, 'read', '/civicrm/imap/ajax/tag/search', NULL),
('InboundEmail Tag Add', 0, 'create', '/civicrm/imap/ajax/addTags', NULL),
('InboundEmail Tag Add', 0, 'create', '/civicrm/imap/ajax/tag/add', NULL),
('InboundEmail Report', 0, 'read', '/civicrm/imap/reports', NULL),
('InboundEmail Report List', 0, 'read', '/civicrm/imap/ajax/reports/list', NULL),
('InboundEmail File Bug', 0, 'create', '/civicrm/imap/ajax/fileBug', NULL),
('Dashlet News', 0, 'read', '/civicrm/dashlet/news', NULL),
('Dashlet My Cases', 0, 'read', '/civicrm/dashlet/myCases', NULL),
('Dashlet All Cases', 0, 'read', '/civicrm/dashlet/allCases', NULL),
('Dashlet Activites', 0, 'read', '/civicrm/dashlet/activity', NULL),
('Dashlet Report', 0, 'read', '/civicrm/report/instance', NULL),
('Dashlet Activity View', 0, 'read', '/civicrm/ajax/contactactivity', NULL),
('Dashlet Group List', 0, 'read', '/civicrm/ajax/grouplist', NULL),
('Dashlet Twitter', 0, 'read', '/civicrm/dashlet/twitter', NULL),
('Dashlet Report', 0, 'read', '/civicrm/dashlet/districtstats', NULL),
('Dashlet Report', 0, 'read', '/civicrm/dashlet', NULL),
('Dashlet Report', 0, 'read', '/civicrm/dashlet/districtstatsnode', NULL),
('User Login', 0, 'update', '/login', NULL),
('User Login', 0, 'update', '/user/login', NULL),
('User Login', 0, 'read', '/node', NULL),
('User Logout', 0, 'update', '/user/logout', NULL),
('User Logout', 0, 'update', '/logoutenter', NULL),
('User Logout', 0, 'update', '/civicrm/logoutenter', NULL),
('User Logout', 0, 'update', '/logout%20enter', NULL),
('User Logout', 0, 'update', '/logout', NULL),
('User Logout', 0, 'update', '/logoff', NULL),
('User Logout', 0, 'update', '/signout', NULL),
('User Logout', 0, 'update', '/exit', NULL),
('User Logout', 0, 'update', '/civicrm/logout', NULL),
('User Logout', 0, 'update', '/civicrm/dashboardlogoutenter', NULL),
('User Logout', 0, 'update', '/civicrm/logout\,enter', NULL),
('User Logout', 0, 'update', '/logout', NULL),
('User Logout', 0, 'update', '/logoff', NULL),
('User Logout', 0, 'update', '/logout\,enter.', NULL),
('User Logout', 0, 'update', '/logout\,enter', NULL),
('User Logout', 0, 'update', '/logoutnode', NULL),
('User Logout', 0, 'update', '/civicrm/dashboard/logout', NULL),
('Dashboard', 0, 'read', '/civicrm/dashboard', NULL),
('Dashboard', 0, 'read', '/civicrm/ajax/dashboard', NULL),
('Dashboard', 0, 'read', '/', NULL),
('Dashboard', 0, 'update', '/civicrm', NULL),
('Dashboard', 0, 'read', '/node', NULL),
('Dashboard', 0, 'read', '\N', NULL),
('Dashboard', 0, 'read', '/index.php', NULL),
('Dashboard', 0, 'read', '/%22/icons/graycol.gif/%22', NULL),
('Dashboard', 0, 'read', '/civicrm/%5C', NULL),
('Dashboard', 0, 'update', '/civicrm/logoutenterdashboard', NULL),
('Dashboard', 0, 'read', '/node/1/edit', NULL),
('Dashboard', 0, 'read', '/]', NULL),
('Search Case', 0, 'read', '/civicrm/case/search', NULL),
('Search Group', 0, 'read', '/civicrm/group/search', NULL),
('Search Custom Select', 0, 'update', '/civicrm/ajax/markSelection', NULL),
('Search Setup', 0, 'read', '/civicrm/contact/search/custom', NULL),
('Search Basic', 0, 'read', '/civicrm/contact/search/basic', NULL),
('Search Advanced', 0, 'read', '/civicrm/contact/search/advanced', NULL),
('Search Activity', 0, 'read', '/civicrm/activity/search', NULL),
('Search Contact', 0, 'read', '/civicrm/contact/search', NULL),
('Search Builder', 0, 'read', '/civicrm/contact/search/builder', NULL),
('Search Contact', 0, 'read', '/civicrm/contact/search/basicnode', NULL),
('Search Contact', 0, 'read', '/civicrm/contact/search/', NULL),
('Search Advanced', 0, 'read', '/civicrm/contact/search/advanced', NULL),
('Search Group', 0, 'read', '/civicrm/group/search/advanced', NULL),
('Search Custom', 0, 'read', 'civicrm/contact/search/custom', NULL),
('Search Contact', 0, 'read', '/civicrm/contact/search/search', NULL),
('Tag Case', 0, 'update', '/civicrm/case/ajax/processtags', NULL),
('Tag Add', 0, 'create', '/civicrm/ajax/tag/create', NULL),
('Tag Delete', 0, 'delete', '/civicrm/ajax/tag/delete', NULL),
('Tag Manage', 0, 'read', '/civicrm/admin/tag', NULL),
('Tag Delete', 0, 'delete', '/civicrm/ajax/entity_tag/delete', NULL),
('Tag Update', 0, 'update', '/civicrm/ajax/tag/update', NULL),
('Tags Merge', 0, 'update', '/civicrm/ajax/mergeTags', NULL),
('Mailing Ajax', 0, 'read', '/civicrm/NYSS/AJAX/Mailing', NULL),
('Mailing Update', 0, 'update', '/civicrm/mailing/component', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/dilan.nysenate.gov', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/view', NULL),
('Mailing View Scheduled', 0, 'read', '/civicrm/mailing/browse', NULL),
('Mailing View Scheduled', 0, 'read', '/civicrm/mailing/browse/scheduled', NULL),
('Mailing Greeting', 0, 'update', '/civicrm/admin/options/postal_greeting', NULL),
('Mailing Greeting Settings', 0, 'read', '/civicrm/admin/options/email_greeting', NULL),
('Mailing From Settings', 0, 'read', '/civicrm/admin/options/from_email_address', NULL),
('Mailing From Settings', 0, 'read', '/civicrm/admin/options/from_email', NULL),
('Mailing Archive', 0, 'update', '/civicrm/mailing/browse/archived', NULL),
('Mailing Signature ajax', 0, 'read', '/civicrm/ajax/signature', NULL),
('Mailing Signature', 0, 'read', '/civicrm/mailing/signiture', NULL),
('Mailing Addressee', 0, 'read', '/civicrm/admin/options/addressee', NULL),
('Mailing Browse', 0, 'read', '/civicrm/mailing/browse/unscheduled', NULL),
('Mailing Report', 0, 'read', '/civicrm/mailing/report', NULL),
('Mailing', 0, 'read', '/civicrm/admin/mail', NULL),
('Mailing', 0, 'read', '/civicrm/admin/mailSettings', NULL),
('Mailing Send', 0, 'update', '/civicrm/mailing/send', NULL),
('Mailing Preview', 0, 'read', '/civicrm/mailing/preview', NULL),
('Mailing Report ', 0, 'read', '/civicrm/mailing/report/event', NULL),
('Mailing Approve ', 0, 'update', '/civicrm/mailing/approve', NULL),
('Mailing Body Content', 0, 'read', '/0', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/%3Ciframe%20width=%22420%22%20height=%22315%22%20src=%22//www.youtube.com/embed/lI_zjRSffO0%22%20frameborder=%220%22%20allowfullscreen%3E%3C/iframe%3E', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/GOLDEN%20GATHERING%20SENIOR%20HEALTH%20FAIR:%20This%20Friday\,%20October%2018th', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/white', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/sendnode', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/STAND%20UP%20FOR%20REPOWERING%20NRG!%20%20Forward%20to%20Friends\,%20Family\,%20and%20Neighbors!%20%20Monday\,%20July%2015\,%202013%206:00%20PM%20%20SUNY%20Fredonia%20Williams%20Center%20280%20Central%20Avenue%20Fredonia\,%20New%20York%20%20Pu,%20%20If%20NRG%20leaves%20Dunkirk\,%20every%20homeowner%20in%20Chautauqua%20County%20faces%20the%20potential%20for%20huge%20tax%20increases.%20%20NRG%20is%20the%20largest%20taxpayer%20in%20the%20County\,%20and%20if%20NRG%20leaves\,%20our%20tax%20base%20will%', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/www.facebook.com/SenatorBettyLittle', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/%3Ciframe%20width=%22560%22%20height=%22315%22%20src=%22//www.youtube.com/embed/1Zup63UYb34%22%20frameborder=%220%22%20allowfullscreen%3E%3C/iframe%3E', NULL),
('Mailing Opt-out', 0, 'update', '/civicrm/mailing/optout', NULL),
('Mailing Unsubscribe', 0, 'update', '/civicrm/mailing/unsubscribe', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/www.donotcall.gov', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/Bike%20Safety.docx', NULL),
('Mailing View', 0, 'read', '/civicrm/mailing/www.savethecenter.net', NULL),
('Admin Dashboard', 0, 'read', '/admin', NULL),
('Admin Menu', 0, 'read', '/civicrm/ajax/menu', NULL),
('Admin Menu Tree', 0, 'read', '/civicrm/ajax/menutree', NULL),
('Admin Menu Rebuild', 0, 'read', '/civicrm/admin/menu', NULL),
('Admin My Contacts', 0, 'read', '/civicrm/user', NULL),
('Admin Workflow Rules', 0, 'update', '/admin/config/workflow/rules', NULL),
('Admin Workflow Reaction', 0, 'update', '/admin/config/workflow/rules/reaction/manage', NULL),
('Admin Workflow Reaction', 0, 'update', '/admin/config/workflow/rules/reaction/manage/rules_notify_approvers_of_submission/edit/', NULL),
('Admin Workflow Rules', 0, 'update', '/admin/config/workflow/rules', NULL),
('Admin User Protect', 0, 'update', '/admin/config/people/userprotect', NULL),
('Admin User Menu', 0, 'read', '/admin/user/user', NULL),
('Admin User Create', 0, 'create', '/civicrm/profile/create', NULL),
('Admin User Update', 0, 'update', '/user/edit', NULL),
('Admin User', 0, 'read', '/user', NULL),
('Admin User Manage', 0, 'read', '/manage/users', NULL),
('Admin User List', 0, 'read', '/admin/users', NULL),
('Admin User List', 0, 'read', '/people/users', NULL),
('Admin User Reset Password ', 0, 'read', '/user/password', NULL),
('Admin User Assign Roles', 0, 'update', '/admin/people/permissions/roleassign', NULL),
('Admin User Roles List', 0, 'read', '/admin/people/permissions/roles', NULL),
('Admin User View', 0, 'read', '/user/', NULL),
('Admin User Delete', 0, 'delete', '/user/delete', NULL),
('Admin Users Manage', 0, 'read', '/admin/people', NULL),
('Admin User Permissions', 0, 'read', '/admin/people/permissions', NULL),
('Admin User Permissions Edit', 0, 'update', '/admin/people/permissions/roles/edit', NULL),
('Admin User Permissions', 0, 'read', '/admin/user/permissions', NULL),
('Admin User Confirm Delete', 0, 'read', '/user/cancel', NULL),
('Admin User Account Settings', 0, 'read', '/admin/config/people/accounts', NULL),
('Admin User Settings', 0, 'read', '/admin/config/people', NULL),
('Admin User Permissions', 0, 'read', '/admin/people/permissions', NULL),
('Admin User Field Settings', 0, 'read', '/admin/config/people/accounts/fields', NULL),
('Admin User Display Settings', 0, 'read', '/admin/config/people/accounts/display', NULL),
('Admin LDAP Help', 0, 'read', '/admin/config/people/ldap/help', NULL),
('Admin LDAP Status', 0, 'read', '/admin/config/people/ldap/help/status', NULL),
('Admin LDAP Issues', 0, 'read', '/admin/config/people/ldap/help/issues', NULL),
('Admin LDAP Servers', 0, 'read', '/admin/config/people/ldap/servers', NULL),
('Admin LDAP Servers edit', 0, 'update', '/admin/config/people/ldap/servers/edit/nyss_ldap', NULL),
('Admin LDAP Drupal Role', 0, 'update', '/admin/config/people/ldap/authorization/edit/drupal_role', NULL),
('Admin LDAP Users', 0, 'read', '/admin/config/people/ldap', NULL),
('Admin LDAP Authorization', 0, 'read', '/admin/config/people/ldap/authorization', NULL),
('Admin LDAP Authentication', 0, 'read', '/admin/config/people/ldap/authentication', NULL),
('Admin LDAP Config', 0, 'read', '/admin/config/people/ldap/help/watchdog', NULL),
('Admin Get Template', 0, 'read', '/civicrm/ajax/template', NULL),
('Admin Maintence Mode', 0, 'read', '/admin/config/development/maintenance', NULL),
('Admin Load Data', 0, 'read', '/civicrm/nyss/getoutput', NULL),
('Admin Load Data', 0, 'create', '/civicrm/nyss/loaddata', NULL),
('Admin Load Sample Data', 0, 'create', '/civicrm/nyss/loadsampledata', NULL),
('Admin Empty Trash', 0, 'delete', '/civicrm/nyss/deletetrashed', NULL),
('Admin Process Trash', 0, 'delete', '/civicrm/nyss/processtrashed', NULL),
('Admin Subscription View', 0, 'delete', '/civicrm/nyss/subscription/view', NULL),
('Admin Subscription Manage', 0, 'delete', '/civicrm/nyss/subscription/manage', NULL),
('Admin Dedupe Rules', 0, 'read', '/civicrm/ajax/dedupeRules', NULL),
('Admin Dedupe Contact', 0, 'read', '/civicrm/contact/deduperules', NULL),
('Admin Dedupe Address', 0, 'update', '/civicrm/dedupe/dupeaddress', NULL),
('Admin Batch', 0, 'update', '/batch', NULL),
('Admin Backup', 0, 'read', '/civicrm/admin/job', NULL),
('Admin Upgrade', 0, 'read', '/civicrm/upgrade', NULL),
('Admin Import/Export Mappings', 0, 'read', '/civicrm/admin/mapping', NULL),
('Admin Settings Caches', 0, 'update', '/civicrm/admin/setting/updateConfigBackend', NULL),
('Admin Settings SMTP', 0, 'read', '/civicrm/admin/setting/smtp', NULL),
('Admin Settings Misc', 0, 'read', '/civicrm/admin/setting/misc', NULL),
('Admin Settings Path', 0, 'read', '/civicrm/admin/setting/path', NULL),
('Admin Settings Component', 0, 'read', '/civicrm/admin/setting/component', NULL),
('Admin Settings Debug', 0, 'read', '/civicrm/admin/setting/debug', NULL),
('Admin Settings Maintenance Mode', 0, 'update', '/admin/settings/site-maintenance', NULL),
('Admin Settings Drupal Integration', 0, 'read', '/civicrm/admin/setting/uf', NULL),
('Admin Settings Relationships', 0, 'read', '/civicrm/admin/setting/mapping', NULL),
('Admin Settings Mailing', 0, 'read', '/civicrm/admin/setting/preferences/mailing', NULL),
('Admin Settings Appearance ', 0, 'read', '/admin/appearance/settings/Bluebird', NULL),
('Admin Message Templates', 0, 'read', '/civicrm/admin/messageTemplates', NULL),
('Admin Performance', 0, 'read', '/admin/config/development/performance', NULL),
('Admin Config', 0, 'read', '/admin/config', NULL),
('Admin Modules', 0, 'read', '/admin/modules', NULL),
('Admin File System Config', 0, 'read', '/admin/config/media/file-system', NULL),
('Admin Logging', 0, 'update', '/admin/config/development/logging', NULL),
('Admin Reports', 0, 'read', '/admin/reports', NULL),
('Admin', 0, 'read', '/civicrm/admin', NULL),
('Admin Appearance Component', 0, 'read', '/civicrm/admin/component', NULL),
('Admin Appearance', 0, 'read', '/admin/appearance', NULL),
('Admin Report Job', 0, 'read', '/civicrm/admin/joblog', NULL),
('Admin Modules List', 0, 'read', '/admin/modules/list/confirm', NULL),
('Admin Report', 0, 'read', '/admin/reports/event', NULL),
('Admin Report Status', 0, 'read', '/admin/reports/status', NULL),
('Admin Groups', 0, 'read', '/group', NULL),
('Admin Block Structure', 0, 'read', '/admin/structure', NULL),
('Admin', 0, 'read', '/admin/index', NULL),
('Admin Modules', 0, 'read', '/admin/build/modules', NULL),
('Admin Modules Remove', 0, 'delete', '/admin/modules/uninstall', NULL),
('Admin Report Errors', 0, 'read', '/admin/reports/civicrm_error', NULL),
('Report List Template', 0, 'read', '/civicrm/admin/report/template/list', NULL),
('Report List Template', 0, 'read', '/civicrm/admin/reports/civicrm_error', NULL),
('Report List', 0, 'read', '/civicrm/admin/report', NULL),
('Report List', 0, 'read', '/civicrm/admin/reports', NULL),
('Report List', 0, 'read', '/civicrm/report', NULL),
('Report List', 0, 'read', '/civicrm/report/list', NULL),
('Report', 0, 'read', '/civicrm/imap/ajax/getReports', NULL),
('Report Proofing', 0, 'read', '/civicrm/logging/proofingreport', NULL),
('Report Activity Summary', 0, 'read', '/civicrm/report/activitySummary', NULL),
('Report Activity', 0, 'read', '/civicrm/report/activity', NULL),
('Report Activity Tag', 0, 'update', '/civicrm/report/activity/tag', NULL),
('Report Case Summary', 0, 'read', '/civicrm/report/case/summary', NULL),
('Report Case Detail', 0, 'read', '/civicrm/report/case/detail', NULL),
('Report Contact Log', 0, 'read', '/civicrm/report/contact/log', NULL),
('Report Contact Detail', 0, 'read', '/civicrm/report/contact/detail', NULL),
('Report Contact Log Summary', 0, 'read', '/civicrm/report/logging/contact/summary', NULL),
('Report Contact Summary', 0, 'read', '/civicrm/report/contact/summary', NULL),
('Report DB Errors', 0, 'read', '/admin/reports/dblog', NULL),
('Report Signup', 0, 'read', '/signupreports', NULL),
('Report Signup Download', 0, 'read', '/signupreports_download', NULL),
('Report Mailing Opened', 0, 'read', '/civicrm/report/Mailing/opened', NULL),
('Report Mailing Bounce', 0, 'read', '/civicrm/report/Mailing/bounce', NULL),
('Report Mailing Summary', 0, 'read', '/civicrm/report/Mailing/summary', NULL),
('Report Apachesolr', 0, 'read', '/admin/reports/apachesolr', NULL),
('Report Apachesolr', 0, 'read', '/admin/reports/apachesolr/solr', NULL),
('Report Error PHP', 0, 'read', '/admin/reports/status/php', NULL),
('Report Mailing Detail', 0, 'read', '/civicrm/report/mailing/detail', NULL),
('Report', 0, 'read', '/civicrm/report/template/list', NULL),
('Report Demographics', 0, 'read', '/civicrm/report/case/demographics', NULL),
('Report Timespent', 0, 'read', '/civicrm/report/case/timespent', NULL),
('Check Email', 0, 'read', '/civicrm/ajax/checkemail', NULL),
('Group Manage', 0, 'read', '/civicrm/group', NULL),
('Group Add', 0, 'create', '/civicrm/group/add', NULL),
('Group Custom Data', 0, 'read', '/civicrm/admin/custom/group', NULL),
('Group Extra Field', 0, 'read', '/civicrm/admin/custom/group/field', NULL),
('Group Extra Field', 0, 'update', '/civicrm/admin/custom/group/field/update', NULL),
('File Create', 0, 'create', '/file', NULL),
('File View ', 0, 'read', '/civicrm/file', NULL),
('File Delete', 0, 'delete', '/civicrm/file/delete', NULL),
('File Print', 0, 'read', '/nyss_getfile', NULL),
('File', 0, 'read', '/civicrm/eee55631bd4b3dd1f8d05bd472985ae3/Body/M2.1.2,OpenElement&cid=image001.jpg@01CE2099.4D3977A0', NULL),
('File', 0, 'read', '/civicrm/0a5642c4dc5e579f90e1105fb697a3d3/Body/M1.2,OpenElement&cid=image001.jpg@01CE8DDC.30E94AC0', NULL),
('File', 0, 'read', '/civicrm/ajax/pdfFormat', NULL),
('Backup Data', 0, 'create', '/backupdata', NULL),
('Backup Data', 0, 'create', '/backupData', NULL),
('Backup', 0, 'create', '/backup', NULL),
('Backup NYSS', 0, 'create', '/nyss_backup', NULL),
('Import Data', 0, 'create', '/importData', NULL),
('Import Activity', 0, 'create', '/civicrm/import/activity', NULL),
('Import Contact', 0, 'create', '/civicrm/import/contact', NULL),
('Import', 0, 'create', '/civicrm/import', NULL),
('State Counties', 0, 'read', '/civicrm/ajax/jqCounty', NULL),
('Get All Cases', 0, 'read', '/civicrm/ajax/getallcases', NULL),
('Print Paper Size', 0, 'read', '/civicrm/ajax/paperSize', NULL),
('Export Permissions', 0, 'read', '/civicrm/nyss/exportpermissions', NULL),
('Status Message', 0, 'read', '/civicrm/ajax/statusmsg', NULL),
('Dedupe', 0, 'update', '/civicrm/dedupe/exception', NULL),
('Client Virus', 0, 'read', '/_vti_', NULL),
('iNotes Proxy', 0, 'read', '/iNotes/Forms85.nsf/iNotes/Proxy', NULL),
('iNotes Proxy', 0, 'read', '/iNotes/Forms9.nsf/iNotes/Proxy', NULL),
('iNotes Proxy', 0, 'read', '/iNotes/Proxy', NULL),
('iNotes Welcome', 0, 'read', '/iNotes/Welcome', NULL),
('processDupes', 1, 'read', '/civicrm/ajax/rest', 'processDupes'),
('Search Auto-Dropdown', 1, 'read', '/civicrm/ajax/rest', 'getContactList'),
('location', 1, 'read', '/civicrm/ajax/rest', 'location'),
('Activity Create', 1, 'read', '/civicrm/ajax/rest', 'activity'),
('Contact Create', 1, 'read', '/civicrm/ajax/rest', 'contact'),
('Tag Keyword Search', 1, 'read', '/civicrm/ajax/taglist', '296'),
('Tag Position Search', 1, 'read', '/civicrm/ajax/taglist', '292'),
('Mailing List', 1, 'delete', '/civicrm/mailing/browse', 'delete'),
('Report Delete', 1, 'read', '/civicrm/report/instance/', 'delete'),
('Admin User Update', 1, 'update', '/civicrm/admin/options/from_email_address', 'update'),
('Contact Edited', 1, 'create', '/civicrm/contact/add', 'update'),
('Contact Create', 1, 'create', '/civicrm/contact/add', 'Individual'),
('Contact Create Household', 1, 'create', '/civicrm/contact/add', 'Household'),
('Contact Create Organization', 1, 'create', '/civicrm/contact/add', 'Organization');

/* Create the upgrade procedure */
DROP PROCEDURE IF EXISTS upgrade_11;
DELIMITER //
CREATE DEFINER=CURRENT_USER PROCEDURE upgrade_11()
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
  DECLARE cur_table_name VARCHAR(255);
  DECLARE cursor_done TINYINT DEFAULT FALSE;
  DECLARE cur1 CURSOR FOR
    SELECT table_name FROM information_schema.tables
    WHERE table_schema=database()
          AND (table_name LIKE 'summary%' OR table_name LIKE 'uniques%' OR table_name='request');
  DECLARE CONTINUE HANDLER FOR 1060 BEGIN CALL nyss_debug_log('Column already exists'); END;
  DECLARE CONTINUE HANDLER FOR 1064 BEGIN CALL nyss_debug_log('Column was not found'); END;
  DECLARE CONTINUE HANDLER FOR 1061 BEGIN CALL nyss_debug_log('Index already exists'); END;
  DECLARE CONTINUE HANDLER FOR 1091 BEGIN CALL nyss_debug_log('Index was not found'); END;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET cursor_done=1;

  /* Preserve previous debug flag value */
  SET @nyss_old_debug_value = IFNULL(@nyss_debug_flag,0);
  /* Set logging on */
  SET @nyss_debug_flag = 1;

  /* add new fields to all tables and populate trans_ip and location_id */
  CALL nyss_debug_log('Opening cursor to add fields location_id and trans_ip');
  OPEN cur1;
  drop_location_loop: LOOP
    FETCH cur1 INTO cur_table_name;
    IF cursor_done THEN LEAVE drop_location_loop; END IF;
    IF IFNULL(cur_table_name,'') != '' THEN
      BEGIN
        /* Add trans_ip */
        CALL nyss_debug_log(CONCAT('Adding field trans_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD trans_ip INT UNSIGNED DEFAULT NULL AFTER remote_ip');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
        /* Add location_id */
        CALL nyss_debug_log(CONCAT('Adding field location_id on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD location_id INT UNSIGNED DEFAULT NULL AFTER trans_ip');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
        /* Populate trans_ip */
        CALL nyss_debug_log(CONCAT('Populating field trans_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('UPDATE ',cur_table_name,' SET trans_ip=INET_ATON(remote_ip)');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
        /* Drop index on remote_ip */
        CALL nyss_debug_log(CONCAT('Drop index remote_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' DROP INDEX remote_ip');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
        /* Add index on trans_ip */
        CALL nyss_debug_log(CONCAT('Add index trans_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD INDEX ',cur_table_name,'__trans_ip (trans_ip)');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
        /* Populate location_id */
        CALL nyss_debug_log(CONCAT('Populating field location_id on table ',cur_table_name));
        SET @newstmt=CONCAT('UPDATE ',cur_table_name,
                            ' a, location b SET a.location_id=IFNULL(b.id,1) ',
                            'WHERE a.trans_ip BETWEEN b.ipv4_start AND b.ipv4_end');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
        /* Add index on location_id */
        CALL nyss_debug_log(CONCAT('Add index location_id on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD INDEX ',cur_table_name,'__location_id (location_id)');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
      END;
    END IF;
  END LOOP;
  CLOSE cur1;

  /* Add field url_id and index for url_id to table request */
  CALL nyss_debug_log('Adding field url_id to table request');
  ALTER TABLE request ADD url_id int unsigned DEFAULT NULL AFTER location_id;
  CALL nyss_debug_log('Adding index on url_id to table request');
  ALTER TABLE request ADD INDEX request__url_id (url_id);

  CALL nyss_debug_log('SP upgrade_11 complete');

  /* Revert debug flag value */
  SET @nyss_debug_flag = @nyss_old_debug_value;
  SET @nyss_old_debug_value = NULL;
END
//
DELIMITER ;

/* this is the actual data manipulation, should take several hours to run */
CALL upgrade_11();

/* Adding trigger to request table */
DROP TRIGGER IF EXISTS request_before_insert;
DELIMITER //
CREATE DEFINER=CURRENT_USER TRIGGER request_before_insert BEFORE INSERT ON request FOR EACH ROW BEGIN
  /* Initialize */
  SET @t_loc_id = NULL;
  SET @t_url_id = NULL;
  /* Translate IP to integer */
  SET NEW.trans_ip = INET_ATON(NEW.remote_ip);
  /* Populate location_id based on IP */
  SELECT id INTO @t_loc_id FROM location WHERE NEW.trans_ip BETWEEN ipv4_start AND ipv4_end;
  SET NEW.location_id = IFNULL(@t_loc_id,1);
  /* Clean the URL and try matching it to the known list */
  SET @clean_url = preg_replace(
     '#(.+)/(?:[0-9]+)?$|([a-z0-9]+),.*$|(/_vti_).*|(/user)/[0-9]+#',
     '$1$2$3$4', NEW.path);

  SELECT id INTO @t_url_id
    FROM url
    WHERE
      (match_full = 0 AND path=@clean_url)
      OR (match_full = 1 AND path=@clean_url AND preg_rlike(search, NEW.query))
    ORDER BY match_full DESC, path
    LIMIT 1;
  SET NEW.url_id = IFNULL(@t_url_id,1);
END
//
DELIMITER ;
