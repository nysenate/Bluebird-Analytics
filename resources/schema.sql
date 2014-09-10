CREATE TABLE IF NOT EXISTS `all_requests` (
`mytime` date,
`weekday` varchar(32),
`total_requests` bigint(21),
`total_bad_requests` decimal(23,0),
`total_good_requests` decimal(23,0)
);

CREATE TABLE IF NOT EXISTS `apache_cron_runs` (
  `final_offset` int(10) unsigned NOT NULL,
  `final_ctime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `datatable` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dimensions` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `observations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

CREATE TABLE IF NOT EXISTS `instance` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `servername` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `install_class` enum('prod','test','dev') COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=164 ;

CREATE TABLE IF NOT EXISTS `request` (
`id` int(10) unsigned NOT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `remote_ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `response_code` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL,
  `transfer_rx` int(10) unsigned DEFAULT NULL,
  `transfer_tx` int(10) unsigned DEFAULT NULL,
  `method` enum('GET','POST','HEAD','OPTION') COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `query` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13392449 ;

CREATE TABLE IF NOT EXISTS `summary_1d` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `summary_1h` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `summary_1m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `summary_15m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `uniques_1d` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `uniques_1h` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `uniques_1m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `uniques_15m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `all_requests`;

CREATE ALGORITHM=UNDEFINED DEFINER=`crmadmin`@`crmas%.nysenate.gov` SQL SECURITY DEFINER VIEW `all_requests` AS select cast(`a`.`time` as date) AS `mytime`,date_format(`a`.`time`,'%a') AS `weekday`,count(0) AS `total_requests`,sum(if((`a`.`response_code` like '5%'),1,0)) AS `total_bad_requests`,sum(if((not((`a`.`response_code` like '5%'))),1,0)) AS `total_good_requests` from `request` `a` where (`a`.`instance_id` <> 153) group by `mytime`;

ALTER TABLE `datatable`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `instance`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `servername` (`servername`);

ALTER TABLE `request`
 ADD PRIMARY KEY (`id`), ADD KEY `time` (`time`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `summary_1d`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `summary_1h`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `summary_1m`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `summary_15m`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `uniques_1d`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`), ADD KEY `type` (`type`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `uniques_1h`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`), ADD KEY `type` (`type`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `uniques_1m`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`), ADD KEY `type` (`type`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);

ALTER TABLE `uniques_15m`
 ADD UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`), ADD KEY `type` (`type`), ADD KEY `remote_ip` (`remote_ip`), ADD KEY `instance_id` (`instance_id`);


ALTER TABLE `request`
ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `summary_1d`
ADD CONSTRAINT `summary_1d_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `summary_1h`
ADD CONSTRAINT `summary_1h_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `summary_1m`
ADD CONSTRAINT `summary_1m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `summary_15m`
ADD CONSTRAINT `summary_15m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `uniques_1d`
ADD CONSTRAINT `uniques_1d_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `uniques_1h`
ADD CONSTRAINT `uniques_1h_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `uniques_1m`
ADD CONSTRAINT `uniques_1m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);

ALTER TABLE `uniques_15m`
ADD CONSTRAINT `uniques_15m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`);


-- Insert default values to make life easier...
INSERT INTO apache_cron_runs VALUES (0, '2013-01-01 00:00:00');
INSERT INTO location (name,ipv4_start,ipv4_end) VALUES ("Not Found", NULL, NULL);
INSERT INTO url (name,match_full,action,path_hash,search)VALUES ("Not Found",0,"read",NULL,NULL),
