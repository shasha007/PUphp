/*
Navicat MySQL Data Transfer
Source Host     : localhost:3306
Source Database : sociax_2_0
Target Host     : localhost:3306
Target Database : sociax_2_0
Date: 2011-01-20 15:16:57
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for ts_kc
-- ----------------------------
DROP TABLE IF EXISTS `ts_kc`;
CREATE TABLE `ts_kc` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `courseName` varchar(20) default '' comment '课程名称',
  `teacher` varchar(20) default '' comment '老师姓名',
  `begin` tinyint(1) NOT NULL comment '起始周',
  `end` tinyint(1) NOT NULL comment ' 起始周',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_kc_opt
-- ----------------------------
DROP TABLE IF EXISTS `ts_kc_opt`;
CREATE TABLE `ts_kc_opt` (
  `id` int(11) NOT NULL auto_increment,
  `kcid` int(11) NOT NULL,
  `occur` tinyint(1) NOT NULL comment '1每周，2单周，3双周',
  `weekday` tinyint(1) NOT NULL comment '周几',
  `from` tinyint(1) NOT NULL comment '起始第几节课',
  `to` tinyint(1) NOT NULL comment '起始第几节课',
  `addr` varchar(50) default '' comment '上课地点',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'kc','version_number','s:1:"1";','2013-01-15 10:00:00');

ALTER TABLE `ts_kc_opt` ADD `uid` INT( 11 )  UNSIGNED NOT NULL AFTER `id` ,
ADD INDEX ( `uid` );