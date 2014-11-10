DROP VIEW all_requests;

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `menu` VALUES (1,'Navigation');

DROP TABLE IF EXISTS `menuitem`;
CREATE TABLE `menuitem` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int(10) unsigned NOT NULL,
  `menu_title` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `content_title` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `data_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `icon_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'fa-arrow-right',
  `is_link` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `target` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `menuitem` WRITE;
/*!40000 ALTER TABLE `menuitem` DISABLE KEYS */;
INSERT INTO `menuitem` VALUES
  (1,1,'Dashboard','Statistics Overview','dashboard','fa-inbox',1,'/dashboard',0,1,1),
  (2,1,'Performance','Statistics Overview','performance','fa-inbox',1,'/performance',0,2,1),
  (3,1,'Users','User Overview','users','fa-users',1,'',0,3,1),
  (4,1,'Users','User Overview','users','fa-users',1,'/users/list',3,1,1),
  (5,1,'User Details','User Details','userdetails','fa-sitemap',1,'/users/details',3,2,1),
  (6,1,'Custom Query','Custom Queries','datatable','fa-list',1,'/datatable',0,4,1);
/*!40000 ALTER TABLE `menuitem` ENABLE KEYS */;
UNLOCK TABLES;

ALTER TABLE request
  CHANGE `time` ts DATETIME NOT NULL,
  DROP INDEX `time`,
  ADD INDEX `timerange (`ts`);

ALTER TABLE summary_15m
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

ALTER TABLE summary_1d
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

ALTER TABLE summary_1h
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

ALTER TABLE summary_1m
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

ALTER TABLE uniques_15m
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

ALTER TABLE uniques_1d
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

ALTER TABLE uniques_1h
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

ALTER TABLE uniques_1m
  CHANGE `time` ts DATETIME DEFAULT NULL,
  ADD INDEX timerange (`ts`);

DELIMITER ;;
CREATE DEFINER=CURRENT_USER PROCEDURE `nyss_debug_log`(IN msg TEXT)
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
END ;;
DELIMITER ;
