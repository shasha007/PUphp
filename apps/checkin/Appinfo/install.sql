--
-- 用户签到记录表（非活动签到）
--
CREATE TABLE `ts_check_in` (
  `id` INT(10) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) NOT NULL DEFAULT 0 COMMENT '签到用户',
  `user_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '签到用户名',
  `school_id` INT(10) NOT NULL DEFAULT 0 COMMENT '学校ID',
  `school_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '学校名称',
  `check_in_date` INT(10) NOT NULL DEFAULT 0 COMMENT '签到日期：天',
  `create_time` INT(10) NOT NULL DEFAULT 0 COMMENT '签到时间：时分秒',
  KEY `user_id` (`user_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户签到记录表（非活动签到）' AUTO_INCREMENT=1;

--
-- 签到统计表
--
CREATE TABLE `ts_check_in_total` (
  `id` INT(10) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) NOT NULL DEFAULT 0 COMMENT '签到用户',
  `user_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '签到用户名',
  `school_id` INT(10) NOT NULL DEFAULT 0 COMMENT '学校ID',
  `school_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '学校名称',
  `check_in_type` INT(10) NOT NULL DEFAULT 0 COMMENT '统计类型ID',
  `continue_count` INT(10) NOT NULL DEFAULT 0 COMMENT '连续签到次数',
  `total_count` INT(10) NOT NULL DEFAULT 0 COMMENT '合计签到次数',
  KEY `user_id` (`user_id`),
  KEY `total_count` (`total_count`),
  KEY `school_id` (`school_id`),
  KEY `continue_count` (`continue_count`),
  KEY `check_in_type` (`check_in_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到统计' AUTO_INCREMENT=1;

--
-- 签到统计类型
-- 系统默认按月进行签到统计，当有些时候，需要进行按特定时间段进行统计的时候，需要新建统计事件
--
CREATE TABLE `ts_check_in_type` (
  `id` INT(10) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '统计事件标题',
  `start_time` INT(10) NOT NULL DEFAULT 0 COMMENT '开始时间',
  `end_time` INT(10) NOT NULL DEFAULT 0 COMMENT '结束时间',
  KEY `start_time` (`start_time`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '签到统计类型' AUTO_INCREMENT=1;

