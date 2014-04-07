DROP TABLE IF EXISTS summary_1m;
DROP TABLE IF EXISTS uniques_1m;
DROP TABLE IF EXISTS summary_15m;
DROP TABLE IF EXISTS uniques_15m;
DROP TABLE IF EXISTS summary_1h;
DROP TABLE IF EXISTS uniques_1h;
DROP TABLE IF EXISTS summary_1d;
DROP TABLE IF EXISTS uniques_1d;

CREATE TABLE summary_1m (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    503_errors int unsigned,
    500_errors int unsigned,
    page_views int unsigned,
    response_time int unsigned,
    UNIQUE KEY (time, instance_id, remote_ip),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE uniques_1m (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    UNIQUE KEY (time, instance_id, remote_ip, type, value),
    INDEX (type),
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
    UNIQUE KEY (time, instance_id, remote_ip),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE uniques_15m (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    UNIQUE KEY (time, instance_id, remote_ip, type, value),
    INDEX (type),
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
    UNIQUE KEY (time, instance_id, remote_ip),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE uniques_1h (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    UNIQUE KEY (time, instance_id, remote_ip, type, value),
    INDEX (type),
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
    UNIQUE KEY (time, instance_id, remote_ip),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE uniques_1d (
    time datetime,
    remote_ip varchar(255),
    instance_id int unsigned,
    type varchar(255),
    value varchar(255),
    UNIQUE KEY (time, instance_id, remote_ip, type, value),
    INDEX (type),
    INDEX (remote_ip),
    FOREIGN KEY (instance_id) REFERENCES instance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
