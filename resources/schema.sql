DROP TABLE IF EXISTS datatable;
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
DROP TABLE IF EXISTS mysql_cron_runs;
DROP TABLE IF EXISTS mysql;
DROP TABLE IF EXISTS mysql_1m;
DROP TABLE IF EXISTS mysql_15m;
DROP TABLE IF EXISTS mysql_1h;
DROP TABLE IF EXISTS mysql_1d;
CREATE TABLE mysql (
  time datetime PRIMARY KEY,
  aborted_connects int unsigned,
  bytes_received int unsigned,
  bytes_sent int unsigned,
  connections int unsigned,
  created_tmp_disk_tables int unsigned,
  created_tmp_files int unsigned,
  created_tmp_tables int unsigned,
  delayed_errors int unsigned,
  delayed_insert_threads int unsigned,
  delayed_writes int unsigned,
  innodb_row_lock_current_waits int unsigned,
  innodb_row_lock_time int unsigned,
  innodb_row_lock_time_avg int unsigned,
  innodb_row_lock_time_max int unsigned,
  innodb_row_lock_waits int unsigned,
  innodb_rows_deleted int unsigned,
  innodb_rows_inserted int unsigned,
  innodb_rows_read int unsigned,
  innodb_rows_updated int unsigned,
  max_used_connections int unsigned,
  open_files int unsigned,
  open_streams int unsigned,
  open_table_definitions int unsigned,
  open_tables int unsigned,
  opened_files int unsigned,
  queries int unsigned,
  questions int unsigned,
  slow_launch_threads int unsigned,
  slow_queries int unsigned,
  table_locks_immediate int unsigned,
  table_locks_waited int unsigned,
  threads_cached int unsigned,
  threads_connected int unsigned,
  threads_created int unsigned,
  threads_running int unsigned,
  uptime int unsigned
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE mysql_1m (
  time datetime PRIMARY KEY,
  aborted_connects int unsigned,
  bytes_received int unsigned,
  bytes_sent int unsigned,
  connections int unsigned,
  created_tmp_disk_tables int unsigned,
  created_tmp_files int unsigned,
  created_tmp_tables int unsigned,
  delayed_errors int unsigned,
  delayed_insert_threads int unsigned,
  delayed_writes int unsigned,
  innodb_row_lock_current_waits int unsigned,
  innodb_row_lock_time int unsigned,
  innodb_row_lock_time_avg int unsigned,
  innodb_row_lock_time_max int unsigned,
  innodb_row_lock_waits int unsigned,
  innodb_rows_deleted int unsigned,
  innodb_rows_inserted int unsigned,
  innodb_rows_read int unsigned,
  innodb_rows_updated int unsigned,
  max_used_connections int unsigned,
  open_files int unsigned,
  open_streams int unsigned,
  open_table_definitions int unsigned,
  open_tables int unsigned,
  opened_files int unsigned,
  queries int unsigned,
  questions int unsigned,
  slow_launch_threads int unsigned,
  slow_queries int unsigned,
  table_locks_immediate int unsigned,
  table_locks_waited int unsigned,
  threads_cached int unsigned,
  threads_connected int unsigned,
  threads_created int unsigned,
  threads_running int unsigned,
  availability float unsigned
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE mysql_15m (
  time datetime PRIMARY KEY,
  aborted_connects int unsigned,
  bytes_received int unsigned,
  bytes_sent int unsigned,
  connections int unsigned,
  created_tmp_disk_tables int unsigned,
  created_tmp_files int unsigned,
  created_tmp_tables int unsigned,
  delayed_errors int unsigned,
  delayed_insert_threads int unsigned,
  delayed_writes int unsigned,
  innodb_row_lock_current_waits int unsigned,
  innodb_row_lock_time int unsigned,
  innodb_row_lock_time_avg int unsigned,
  innodb_row_lock_time_max int unsigned,
  innodb_row_lock_waits int unsigned,
  innodb_rows_deleted int unsigned,
  innodb_rows_inserted int unsigned,
  innodb_rows_read int unsigned,
  innodb_rows_updated int unsigned,
  max_used_connections int unsigned,
  open_files int unsigned,
  open_streams int unsigned,
  open_table_definitions int unsigned,
  open_tables int unsigned,
  opened_files int unsigned,
  queries int unsigned,
  questions int unsigned,
  slow_launch_threads int unsigned,
  slow_queries int unsigned,
  table_locks_immediate int unsigned,
  table_locks_waited int unsigned,
  threads_cached int unsigned,
  threads_connected int unsigned,
  threads_created int unsigned,
  threads_running int unsigned,
  availability float unsigned
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE mysql_1h (
  time datetime PRIMARY KEY,
  aborted_connects int unsigned,
  bytes_received int unsigned,
  bytes_sent int unsigned,
  connections int unsigned,
  created_tmp_disk_tables int unsigned,
  created_tmp_files int unsigned,
  created_tmp_tables int unsigned,
  delayed_errors int unsigned,
  delayed_insert_threads int unsigned,
  delayed_writes int unsigned,
  innodb_row_lock_current_waits int unsigned,
  innodb_row_lock_time int unsigned,
  innodb_row_lock_time_avg int unsigned,
  innodb_row_lock_time_max int unsigned,
  innodb_row_lock_waits int unsigned,
  innodb_rows_deleted int unsigned,
  innodb_rows_inserted int unsigned,
  innodb_rows_read int unsigned,
  innodb_rows_updated int unsigned,
  max_used_connections int unsigned,
  open_files int unsigned,
  open_streams int unsigned,
  open_table_definitions int unsigned,
  open_tables int unsigned,
  opened_files int unsigned,
  queries int unsigned,
  questions int unsigned,
  slow_launch_threads int unsigned,
  slow_queries int unsigned,
  table_locks_immediate int unsigned,
  table_locks_waited int unsigned,
  threads_cached int unsigned,
  threads_connected int unsigned,
  threads_created int unsigned,
  threads_running int unsigned,
  availability float unsigned
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE mysql_1d (
  time datetime PRIMARY KEY,
  aborted_connects int unsigned,
  bytes_received int unsigned,
  bytes_sent int unsigned,
  connections int unsigned,
  created_tmp_disk_tables int unsigned,
  created_tmp_files int unsigned,
  created_tmp_tables int unsigned,
  delayed_errors int unsigned,
  delayed_insert_threads int unsigned,
  delayed_writes int unsigned,
  innodb_row_lock_current_waits int unsigned,
  innodb_row_lock_time int unsigned,
  innodb_row_lock_time_avg int unsigned,
  innodb_row_lock_time_max int unsigned,
  innodb_row_lock_waits int unsigned,
  innodb_rows_deleted int unsigned,
  innodb_rows_inserted int unsigned,
  innodb_rows_read int unsigned,
  innodb_rows_updated int unsigned,
  max_used_connections int unsigned,
  open_files int unsigned,
  open_streams int unsigned,
  open_table_definitions int unsigned,
  open_tables int unsigned,
  opened_files int unsigned,
  queries int unsigned,
  questions int unsigned,
  slow_launch_threads int unsigned,
  slow_queries int unsigned,
  table_locks_immediate int unsigned,
  table_locks_waited int unsigned,
  threads_cached int unsigned,
  threads_connected int unsigned,
  threads_created int unsigned,
  threads_running int unsigned,
  availability float unsigned
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE datatable (
    id int unsigned AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) UNIQUE KEY,
    dimensions varchar(255),
    observations varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

