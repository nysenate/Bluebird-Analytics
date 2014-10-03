
CREATE TABLE IF NOT EXISTS apache_cron_runs (
  final_offset int(10) unsigned NOT NULL,
  final_ctime datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS datatable (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  dimensions varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  observations varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS instance (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  servername varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  install_class enum('prod','test','dev') COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY servername (servername)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS request (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  instance_id int(10) unsigned DEFAULT NULL,
  remote_ip varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  time datetime NOT NULL,
  KEY time (time),
  KEY instance_id (instance_id),
  KEY request__trans_ip (trans_ip),
  KEY request__location_id (location_id),
  KEY request__url_id (url_id),
  CONSTRAINT request_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS summary_1d (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip),
  KEY instance_id (instance_id),
  KEY summary_1d__trans_ip (trans_ip),
  KEY summary_1d__location_id (location_id),
  CONSTRAINT summary_1d_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS summary_1h (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip),
  KEY instance_id (instance_id),
  KEY summary_1h__trans_ip (trans_ip),
  KEY summary_1h__location_id (location_id),
  CONSTRAINT summary_1h_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS summary_1m (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip),
  KEY instance_id (instance_id),
  KEY summary_1m__trans_ip (trans_ip),
  KEY summary_1m__location_id (location_id),
  CONSTRAINT summary_1m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS summary_15m (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  503_errors int(10) unsigned DEFAULT NULL,
  500_errors int(10) unsigned DEFAULT NULL,
  page_views int(10) unsigned DEFAULT NULL,
  response_time int(10) unsigned DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip),
  KEY instance_id (instance_id),
  KEY summary_15m__trans_ip (trans_ip),
  KEY summary_15m__location_id (location_id),
  CONSTRAINT summary_15m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS uniques_1d (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip, type, value),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_1d__trans_ip (trans_ip),
  KEY uniques_1d__location_id (location_id),
  CONSTRAINT uniques_1d_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS uniques_1h (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip, type, value),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_1h__trans_ip (trans_ip),
  KEY uniques_1h__location_id (location_id),
  CONSTRAINT uniques_1h_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS uniques_1m (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip, type, value),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_1m__trans_ip (trans_ip),
  KEY uniques_1m__location_id (location_id),
  CONSTRAINT uniques_1m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS uniques_15m (
  time datetime DEFAULT NULL,
  remote_ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  trans_ip int(10) unsigned DEFAULT NULL,
  location_id int(10) unsigned DEFAULT NULL,
  instance_id int(10) unsigned DEFAULT NULL,
  type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  value varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY time (time, instance_id, remote_ip, type, value),
  KEY type (type),
  KEY instance_id (instance_id),
  KEY uniques_15m__trans_ip (trans_ip),
  KEY uniques_15m__location_id (location_id),
  CONSTRAINT uniques_15m_ibfk_1 FOREIGN KEY (instance_id) REFERENCES instance (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS location (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  ipv4_start int(10) unsigned DEFAULT NULL,
  ipv4_end int(10) unsigned DEFAULT NULL,
  KEY location__ipv4_range (ipv4_start, ipv4_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS url (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  name varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  match_full bit(1) DEFAULT NULL,
  action varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  path varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  search varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER
SQL SECURITY DEFINER VIEW all_requests AS
select cast(a.time as date) AS date,
  date_format(a.time,'%a') AS weekday,
  count(0) AS total_requests,
  sum(if((a.response_code >= 400),1,0)) AS total_bad_requests,
  sum(if((a.response_code < 400),1,0)) AS total_good_requests
from request a where a.instance_id not in (
  select id from instance where install_class<>'prod') group by date;

