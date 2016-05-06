/*
Navicat MySQL Data Transfer

Source Server         : xyh
Source Server Version : 50520
Source Host           : localhost:3306
Source Database       : 2012xyhui

Target Server Type    : MYSQL
Target Server Version : 50520
File Encoding         : 65001

Date: 2015-09-10 13:11:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_wx_vote`
-- ----------------------------
DROP TABLE IF EXISTS `ts_wx_vote`;
CREATE TABLE `ts_wx_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eid` int(10) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  `ctime` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '投票时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ts_wx_vote
-- ----------------------------
