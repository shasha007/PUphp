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
-- Table structure for ts_event
-- ----------------------------
DROP TABLE IF EXISTS `ts_exercise`;
CREATE TABLE `ts_exercise` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `select` text default '' comment '答题选项',
  `answer` text NOT NULL comment '答案',
  `type` tinyint(1) NOT NULL comment '1选择2判断3填空',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'exercise','version_number','s:1:"1";','2013-03-15 10:00:00');