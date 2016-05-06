/*
Navicat MySQL Data Transfer

Source Server         : xyh
Source Server Version : 50520
Source Host           : localhost:3306
Source Database       : 2012xyhui

Target Server Type    : MYSQL
Target Server Version : 50520
File Encoding         : 65001

Date: 2015-09-10 13:11:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_wx_event`
-- ----------------------------
DROP TABLE IF EXISTS `ts_wx_event`;
CREATE TABLE `ts_wx_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报名活动的用户id',
  `realname` varchar(200) CHARACTER SET utf8 DEFAULT '' COMMENT '用户真实姓名',
  `school` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `vote_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '获得的投票数',
  `pic` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户上传的照片',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ts_wx_event
-- ----------------------------
INSERT INTO `ts_wx_event` VALUES ('2', '71077', 'ddd', '苏州大学', '0', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('3', '71064', 'yyf', '苏州大学', '9', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('4', '71065', 'yaoyunf', '苏州大学', '8', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('5', '71066', 'yaoyunf', '苏州大学', '9', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('6', '71067', 'yyf', '苏州大学', '8', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('7', '71068', 'yaoyunf', '苏州大学', '8', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('8', '71069', 'yyy', '苏州大学', '8', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('9', '71070', 'lll', '苏州大学', '6', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('10', '71071', 'aaa', '苏州大学', '6', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('11', '71072', 'hhh', '苏州大学', '6', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('12', '71073', 'ggg', '苏州大学', '6', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('13', '71074', 'rrr', '苏州大学', '6', 'webuploader/image/2015091011153214658f6985acf53d3f90528f2d8a6af7.jpg');
INSERT INTO `ts_wx_event` VALUES ('14', '71075', 'www', '苏州大学', '7', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('15', '71077', 'ddd', '苏州大学', '6', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
INSERT INTO `ts_wx_event` VALUES ('16', '71078', 'qqqq', '苏州大学', '6', 'webuploader/image/20150909101749119497285048fefc03o.jpg');
