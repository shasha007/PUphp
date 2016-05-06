/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Version : 50505
 Source Host           : localhost
 Source Database       : 2012xyhui

 Target Server Version : 50505
 File Encoding         : utf-8

 Date: 12/16/2015 18:02:01 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `ts_bank_op`
-- ----------------------------
DROP TABLE IF EXISTS `ts_bank_op`;
CREATE TABLE `ts_bank_op` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `optime` varchar(10) NOT NULL,
  `action` varchar(35) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
