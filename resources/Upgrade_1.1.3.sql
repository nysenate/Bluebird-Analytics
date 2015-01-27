ALTER TABLE request
  DROP COLUMN remote_ip;

ALTER TABLE summary_1m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE summary_1m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE summary_1m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE summary_1m
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip),
  DROP COLUMN remote_ip;

ALTER TABLE uniques_1h
  DROP INDEX `time`,
  ADD UNIQUE INDEX `time` (ts, instance_id, trans_ip, `type`, value),
  DROP COLUMN remote_ip;

ALTER TABLE uniques_1m
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
