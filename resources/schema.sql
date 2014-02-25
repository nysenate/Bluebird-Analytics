DROP TABLE IF EXISTS request;
DROP TABLE IF EXISTS summary_1m;
DROP TABLE IF EXISTS uniques_1m;
DROP TABLE IF EXISTS summary_15m;
DROP TABLE IF EXISTS uniques_15m;
DROP TABLE IF EXISTS summary_1h;
DROP TABLE IF EXISTS uniques_1h;
DROP TABLE IF EXISTS summary_1d;
DROP TABLE IF EXISTS uniques_1d;
DROP TABLE IF EXISTS instance;
DROP TABLE IF EXISTS apache_cron_runs;


CREATE TABLE instance (
    id int unsigned AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    servername varchar(255) UNIQUE,
    install_class ENUM('prod','test','dev')
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE request (
  id int unsigned AUTO_INCREMENT PRIMARY KEY,
  instance_id int unsigned,

  remote_ip varchar(20),
  response_code int unsigned,
  response_time int unsigned,
  transfer_rx int unsigned,
  transfer_tx int unsigned,
  method ENUM('GET','POST','HEAD','OPTION'),
  path varchar(255),
  query varchar(255),
  time datetime NOT NULL,

  INDEX(time),
  INDEX(remote_ip),
  FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE summary_1m (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    503_errors int unsigned,
    500_errors int unsigned,
    page_views int unsigned,
    response_time int unsigned,
    INDEX(time),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE uniques_1m (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    INDEX(time),
    INDEX(type),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE summary_15m (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    503_errors int unsigned,
    500_errors int unsigned,
    page_views int unsigned,
    response_time int unsigned,
    INDEX(time),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE uniques_15m (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    INDEX(time),
    INDEX(type),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE summary_1h (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    503_errors int unsigned,
    500_errors int unsigned,
    page_views int unsigned,
    response_time int unsigned,
    INDEX(time),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE uniques_1h (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    INDEX(time),
    INDEX(type),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE summary_1d (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    503_errors int unsigned,
    500_errors int unsigned,
    page_views int unsigned,
    response_time int unsigned,
    INDEX(time),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE uniques_1d (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    INDEX(time),
    INDEX(type),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE apache_cron_runs (
  final_offset int unsigned NOT NULL,
  final_ctime datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Insert default values to make life easier...
INSERT INTO apache_cron_runs VALUES (0, '2013-01-01 00:00:00');
