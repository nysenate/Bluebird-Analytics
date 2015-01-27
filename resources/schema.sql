
DROP TABLE IF EXISTS apache_cron_runs;
CREATE TABLE apache_cron_runs (
  final_offset int(10) unsigned NOT NULL,
  final_ctime datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS datatable;
CREATE TABLE datatable (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  dimensions varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  observations varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS instance;
CREATE TABLE instance (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  servername varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  install_class enum('prod','test','dev') COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY servername (servername)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS request;
CREATE TABLE request (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  instance_id int(10) unsigned DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  url_id int(10) unsigned DEFAULT NULL,
  response_code int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  transfer_rx int(10) unsigned DEFAULT NULL,
  transfer_tx int(10) unsigned DEFAULT NULL,
  method enum('GET','POST','HEAD','OPTION') COLLATE utf8_unicode_ci DEFAULT NULL,
  path varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  query varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ts datetime NOT NULL,
  KEY timerange (ts),
  KEY instance_id (instance_id),
  KEY request__trans_ip (trans_ip),
  KEY request__location_id (location_id),
  KEY request__url_id (url_id),
  CONSTRAINT request_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS summary_1d;
CREATE TABLE summary_1d (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip),
  KEY timerange (ts),
  KEY instance_id (instance_id),
  KEY summary_1d__trans_ip (trans_ip),
  KEY summary_1d__location_id (location_id),
  CONSTRAINT summary_1d_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS summary_1h;
CREATE TABLE summary_1h (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip),
  KEY timerange (ts),
  KEY instance_id (instance_id),
  KEY summary_1h__trans_ip (trans_ip),
  KEY summary_1h__location_id (location_id),
  CONSTRAINT summary_1h_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS summary_1m;
CREATE TABLE summary_1m (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip),
  KEY timerange (ts),
  KEY instance_id (instance_id),
  KEY summary_1m__trans_ip (trans_ip),
  KEY summary_1m__location_id (location_id),
  CONSTRAINT summary_1m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS summary_15m;
CREATE TABLE summary_15m (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip),
  KEY timerange (ts),
  KEY instance_id (instance_id),
  KEY summary_15m__trans_ip (trans_ip),
  KEY summary_15m__location_id (location_id),
  CONSTRAINT summary_15m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS uniques_1d;
CREATE TABLE uniques_1d (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip, type, value),
  KEY timerange (ts),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_1d__trans_ip (trans_ip),
  KEY uniques_1d__location_id (location_id),
  CONSTRAINT uniques_1d_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS uniques_1h;
CREATE TABLE uniques_1h (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip, type, value),
  KEY timerange (ts),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_1h__trans_ip (trans_ip),
  KEY uniques_1h__location_id (location_id),
  CONSTRAINT uniques_1h_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS uniques_1m;
CREATE TABLE uniques_1m (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip, type, value),
  KEY timerange (ts),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_1m__trans_ip (trans_ip),
  KEY uniques_1m__location_id (location_id),
  CONSTRAINT uniques_1m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS uniques_15m;
CREATE TABLE uniques_15m (
  ts datetime DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (ts, instance_id, remote_ip, type, value),
  KEY timerange (ts),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_15m__trans_ip (trans_ip),
  KEY uniques_15m__location_id (location_id),
  CONSTRAINT uniques_15m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS location;
CREATE TABLE location (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  ipv4_start int(10) unsigned DEFAULT NULL,
  ipv4_end int(10) unsigned DEFAULT NULL,
  KEY location__ipv4_range (ipv4_start, ipv4_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS url;
CREATE TABLE url (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  match_full bit(1) DEFAULT NULL,
  action varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  path varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  search varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS menu;
CREATE TABLE menu (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  menu_name varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS menuitem;
CREATE TABLE menuitem (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  menu_id int(10) unsigned NOT NULL,
  menu_title varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  content_title varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  data_name varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  icon_name varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'fa-arrow-right',
  is_link tinyint(3) unsigned NOT NULL DEFAULT '1',
  target varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  parent_id int(10) unsigned NOT NULL DEFAULT '0',
  weight int(10) unsigned NOT NULL DEFAULT '0',
  active tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

