/* New Tables */

/* Table structure for table `report_display` */
DROP TABLE IF EXISTS `report_display`;
CREATE TABLE `report_display` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(11) unsigned NOT NULL,
  `page_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Table structure for table `report_field_defs` */
DROP TABLE IF EXISTS `report_field_defs`;
CREATE TABLE `report_field_defs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `source_table` enum('request','uniques','summary','url','location','instance') COLLATE utf8_unicode_ci NOT NULL,
  `select_sql` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `calculated` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_name_table` (`name`,`source_table`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Table structure for table `report_fields` */
DROP TABLE IF EXISTS `report_fields`;
CREATE TABLE `report_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `field_def_id` int(10) unsigned NOT NULL,
  `aggregate` enum('count','countd','avg','sum','calc','group','none') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'group',
  `fmtcode` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sort_order` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Table structure for table `report_properties` */
DROP TABLE IF EXISTS `report_properties`;
CREATE TABLE `report_properties` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `property_type_id` int(10) unsigned NOT NULL,
  `property_value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_id_property_type_id` (`report_id`,`property_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Table structure for table `report_property_type` */
DROP TABLE IF EXISTS `report_property_type`;
CREATE TABLE `report_property_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `property_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `property_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Table structure for table `reports` */
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `report_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `report_descript` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_name` (`report_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* New View */
DROP VIEW IF EXISTS `v_report_properties`;
CREATE DEFINER=CURRENT_USER SQL SECURITY DEFINER
VIEW `v_report_properties` AS
  SELECT
    `a`.`report_id` AS `report_id`,
    `a`.`id` AS `property_id`,
    `a`.`property_type_id` AS `property_type_id`,
    `b`.`property_name` AS `property_name`,
    `b`.`property_desc` AS `property_desc`,
    `a`.`property_value` AS `property_value`
  FROM
    `report_properties` `a`
      INNER JOIN `report_property_type` `b`
        ON `a`.`property_type_id` = `b`.`id`;

/* Table alterations for removal of remote_ip in favor of trans_ip */
ALTER TABLE request
  DROP COLUMN remote_ip;

ALTER TABLE summary_15m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE summary_1d
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE summary_1h
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE summary_1m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE uniques_15m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip, `type`, value),
  DROP COLUMN remote_ip;

ALTER TABLE uniques_1d
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip, `type`, value),
  DROP COLUMN remote_ip;

ALTER TABLE uniques_1h
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip, `type`, value),
  DROP COLUMN remote_ip;

ALTER TABLE uniques_1m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip, `type`, value),
  DROP COLUMN remote_ip;

/* Trigger alterations to remove remote_ip in favor of trans_ip */
DROP TRIGGER IF EXISTS request_before_insert;
DELIMITER //
CREATE DEFINER=CURRENT_USER TRIGGER request_before_insert
BEFORE INSERT ON request FOR EACH ROW BEGIN
  /* Initialize */
  SET @t_loc_id = NULL;
  SET @t_url_id = NULL;

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

/* INSERT all new data */
INSERT INTO `report_display` VALUES (1,1,'dashboard'),(2,2,'dashboard'),(3,3,'dashboard'),(4,4,'dashboard'),(5,5,'dashboard'),(6,6,'dashboard'),(7,7,'dashboard'),(8,4,'performance'),(9,8,'performance'),(10,9,'performance'),(11,10,'performance'),(12,11,'performance'),(13,12,'performance'),(14,13,'usage'),(15,14,'usage');
INSERT INTO `report_field_defs` VALUES (1,'timerange','summary','ts',0),(2,'instance_id','summary','instance_id',0),(3,'remote_ip','summary','INET_NTOA(dt.trans_ip)',1),(4,'http_500','summary','500_errors',0),(5,'http_503','summary','503_errors',0),(6,'page_views','summary','page_views',0),(7,'resp_time','summary','response_time',0),(8,'uptime','summary','IFNULL((1-((SUM(dt.500_errors)+SUM(dt.503_errors))/SUM(dt.page_views)))*100,0)',1),(9,'avg_resp_time','summary','IFNULL(SUM(dt.response_time),0)/IFNULL(SUM(dt.page_views),1)',1),(10,'timerange','uniques','ts',0),(11,'instance_id','uniques','instance_id',0),(12,'remote_ip','uniques','INET_NTOA(dt.trans_ip)',1),(13,'path','uniques','value',0),(14,'timerange','request','ts',0),(15,'instance_id','request','instance_id',0),(16,'remote_ip','request','INET_NTOA(dt.trans_ip)',1),(17,'resp_code','request','response_code',0),(18,'resp_time','request','response_time',0),(19,'rcvd_xfer','request','transfer_rx',0),(20,'send_xfer','request','transfer_tx',0),(21,'method','request','method',0),(22,'path','request','path',0),(23,'query','request','query',0),(24,'avg_resp_time','request','IFNULL(SUM(dt.response_time),0)/IFNULL(COUNT(dt.response_time),1)',1),(25,'instance_name','instance','name',0),(26,'location_name','location','name',0),(27,'url_name','url','name',0);
INSERT INTO `report_fields` VALUES (1,1,6,'sum','intcomma',0),(2,2,12,'countd','intcomma',0),(3,3,11,'countd','intcomma',0),(4,4,8,'calc','percent|2',0),(5,5,6,'sum','',0),(6,6,25,'group','',0),(7,6,3,'countd','intcomma',-2),(8,6,6,'sum','intcomma',-1),(9,7,26,'group','',2),(10,7,25,'group','',3),(11,7,6,'sum','intcomma',-1),(12,8,4,'sum','intcomma',0),(13,9,5,'sum','intcomma',0),(14,10,9,'calc','microsec|2',0),(15,11,4,'sum','intcomma',0),(16,11,5,'sum','intcomma',0),(17,11,6,'sum','floatperk',0),(18,11,9,'calc','microsec',0),(19,12,27,'group','',0),(20,12,17,'count','',0),(21,12,24,'calc','microsec',-1),(22,13,25,'group','',0),(23,13,6,'sum','int',0),(24,13,9,'calc','microsec',0),(25,14,25,'group','',0),(26,14,27,'group','',0),(27,14,14,'count','int',0),(28,14,24,'calc','microsec',0);
INSERT INTO `report_properties` VALUES (1,1,1,'summary'),(2,1,2,'summary'),(3,1,3,'fa fa-files-o fa-3x'),(4,1,4,'Pages Served'),(5,1,5,'/datatable'),(6,1,6,'Browse Content'),(7,1,7,'Pages Served'),(8,1,8,'page_views'),(9,1,9,'col-sm-3 col-xs-6'),(10,1,13,'#summary-wrapper'),(12,2,1,'summary'),(13,2,2,'summary'),(14,2,3,'fa fa-files-o fa-3x'),(15,2,4,'Active Users'),(16,2,5,'/users/list'),(17,2,6,'User Overview'),(18,2,7,'Active Users'),(19,2,8,'unique_users'),(20,2,9,'col-sm-3 col-xs-6'),(21,2,13,'#summary-wrapper'),(27,2,12,'fa fa-arrow-circle-right'),(28,1,12,'fa fa-arrow-circle-right'),(29,3,1,'summary'),(30,3,2,'summary'),(31,3,3,'fa fa-files-o fa-3x'),(32,3,4,'Active Instances'),(33,3,5,'/users/list'),(34,3,6,'Office Overview'),(35,3,7,'Active Offices'),(36,3,8,'active_instances'),(37,3,9,'col-sm-3 col-xs-6'),(38,3,12,'fa fa-arrow-circle-right'),(39,3,13,'#summary-wrapper'),(44,4,1,'summary'),(45,4,2,'summary'),(46,4,3,'fa fa-files-o fa-3x'),(47,4,4,'Uptime'),(48,4,5,'/performance'),(49,4,6,'Performance Overview'),(50,4,7,'Uptime'),(51,4,8,'uptime'),(52,4,9,'col-sm-3 col-xs-6'),(53,4,12,'fa fa-arrow-circle-right'),(54,4,13,'#summary-wrapper'),(59,5,1,'chart'),(60,5,2,'summary'),(61,5,3,'fa fa-bar-chart-o'),(62,5,4,'Page Views'),(65,5,7,'Page Views'),(66,5,8,'view_history'),(67,5,9,'col-lg-12'),(69,5,13,'#chart-wrapper'),(70,5,10,'{ \"chart\":{\"type\":\"spline\"}, \"title\":{\"text\":\"Page Views\"} }'),(71,6,4,'Most Active Instances'),(72,6,8,'top_instances'),(73,6,1,'list'),(74,6,2,'summary'),(75,6,3,'fa fa-building-o'),(76,6,12,'fa fa-arrow-circle-right'),(77,6,13,'#list-wrapper'),(78,6,9,'col-lg-6'),(79,7,4,'Most Active users'),(80,7,8,'top_users'),(81,7,1,'list'),(82,7,2,'summary'),(83,7,3,'fa fa-building-o'),(84,7,12,'fa fa-arrow-circle-right'),(85,7,13,'#list-wrapper'),(86,7,9,'col-lg-6'),(87,8,1,'summary'),(88,8,2,'summary'),(89,8,3,'fa fa-files-o fa-3x'),(90,8,4,'App (500) Errors'),(93,8,7,'App (500) Errors'),(94,8,8,'http_500'),(95,8,9,'col-sm-3 col-xs-6'),(96,8,13,'#summary-wrapper'),(97,9,1,'summary'),(98,9,2,'summary'),(99,9,3,'fa fa-files-o fa-3x'),(100,9,4,'DB (503) Errors'),(103,9,7,'DB (503) Errors'),(104,9,8,'http_503'),(105,9,9,'col-sm-3 col-xs-6'),(106,9,13,'#summary-wrapper'),(107,10,1,'summary'),(108,10,2,'summary'),(109,10,3,'fa fa-files-o fa-3x'),(110,10,4,'Avg Resp Time'),(111,10,7,'Avg Resp Time'),(112,10,8,'avg_response_time'),(113,10,9,'col-sm-3 col-xs-6'),(114,10,13,'#summary-wrapper'),(115,11,1,'chart'),(116,11,2,'summary'),(117,11,3,'fa fa-bar-chart-o'),(118,11,4,'Performance Stats'),(119,11,7,'Performance Stats'),(120,11,8,'perf_stats'),(121,11,9,'col-lg-12'),(122,11,10,'{ \"chart\":{\"type\":\"spline\"}, \"title\":{\"text\":\"Performance Stats\"} }'),(123,11,13,'#chart-wrapper'),(124,12,1,'list'),(125,12,2,'request'),(126,12,3,'fa fa-building-o'),(127,12,4,'Query Performance'),(128,12,8,'query_perf'),(129,12,9,'col-lg-9 center-block'),(130,12,12,'fa fa-arrow-circle-right'),(131,12,13,'#list-wrapper'),(132,13,1,'list'),(133,13,2,'summary'),(134,13,3,'fa fa-building-o'),(135,13,4,'Instance Usage'),(136,13,8,'instance_usage'),(137,13,9,'col-lg-9 center-block'),(138,13,12,'fa fa-arrow-circle-right'),(139,13,13,'#list-wrapper'),(140,14,1,'list'),(141,14,2,'request'),(142,14,3,'fa fa-building-o'),(143,14,4,'Request Usage'),(144,14,8,'request_usage'),(145,14,9,'col-lg-9 center-block'),(146,14,12,'fa fa-arrow-circle-right'),(147,14,13,'#list-wrapper');
INSERT INTO `report_property_type` VALUES (1,'report_type','Type of report widget: \'summary\', \'list\', \'chart\''),(2,'target_table','Table to use as source data: request, summary or uniques'),(3,'icon_class','CSS classes to place on the report icon field'),(4,'report_title','A title to display'),(5,'link_target','URL to use as destination for report links'),(6,'link_text','Text to display as a link to link_target'),(7,'value_caption','(For Summary only) A caption to include for a summary report data point'),(8,'wrapper_id','ID attribute of the report\'s parent HTML element'),(9,'wrapper_class','Class attribute of the report\'s parent HTML element'),(10,'widget_properties','Additional properties to include in the report\'s widget initialization.  JSON format'),(11,'sort_order','A list of field names and sort directions, i.e., appropriate to an ORDER BY clause'),(12,'link_icon','CSS class to use for link\'s icon decoration'),(13,'target_wrapper','CSS selector used to identify top-level parent to which the report should be appended');
INSERT INTO `reports` VALUES (1,'Page Views','A summary of all page views rendered'),(2,'Active Users','Count of unique IPs hitting Bluebird'),(3,'Active Instances','Count of all instances'),(4,'Uptime','Percentage of requests resulting in an error'),(5,'Page View History','A historical graph of cumulative page views per period'),(6,'Most Active Instances','List of instances sorted by request activity'),(7,'Most Active Users','List of users sorted by request activity'),(8,'Application (500) Errors','Number of requests receiving a 500 response'),(9,'Application (503) Errors','Number of requests receiving a 503 response'),(10,'Average Response Time','Average time spent processing requests'),(11,'General Metrics','A grouping of general metrics'),(12,'Query Stats','List of URLs with associated query/response time performance'),(13,'Instance Statistics','List of views and response time by instance'),(14,'Common Tasks','Lists of requests grouped by instance and URL');

/* UPDATE the menuitems table with new navigation */
DELETE FROM `menuitem`;
INSERT INTO `menuitem`
  (`menu_id`, `menu_title`, `content_title`, `data_name`, `icon_name`,
   `is_link`, `target`, `parent_id`, `weight`, `active`)
VALUES
  (1, 'Dashboard', 'Statistics Overview', 'dashboard', 'fa-inbox', 1, '/dashboard', 0, 1, 1),
  (1, 'Performance', 'Statistics Overview', 'performance', 'fa-inbox', 1, '/performance', 0, 2, 1),
  (1, 'Usage', 'Usage Overview', 'usage', 'fa-users', 1, '/usage', 0, 3, 1),
  (1, 'Users Overview', 'User Overview', 'users', 'fa-users', 1, '/users/list', 3, 1, 0),
  (1, 'User Details', 'User Details', 'userdetails', 'fa-sitemap', 1, '/users/details', 3, 2, 0);
