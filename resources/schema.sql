DROP TABLE IF EXISTS request;
DROP TABLE IF EXISTS summary_1m;
DROP TABLE IF EXISTS instance;
DROP TABLE IF EXISTS apache_cron_runs;


CREATE TABLE apache_cron_runs (
  final_offset int unsigned NOT NULL,
  final_ctime datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


create table request (
  id int unsigned AUTO_INCREMENT PRIMARY KEY,
  instance_name varchar(255),
  instance_type ENUM('prod','test','dev'),

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
  INDEX(instance_name),
  INDEX(instance_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Insert default values to make life easier...
INSERT INTO apache_cron_runs VALUES (0, FROM_UNIXTIME(0));
