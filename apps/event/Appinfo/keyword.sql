/*
Navicat MySQL Data Transfer

Source Server         : myproject
Source Server Version : 50170
Source Host           : localhost:3306
Source Database       : 2012xyhui

Target Server Type    : MYSQL
Target Server Version : 50170
File Encoding         : 65001

Date: 2015-06-05 14:24:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_event_keyword`
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_keyword`;
CREATE TABLE `ts_event_keyword` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT '',
  `num` int(10) unsigned DEFAULT '1',
  `ctime` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_event_keyword
-- ----------------------------
