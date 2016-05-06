/*
Navicat MySQL Data Transfer

Source Server         : myproject
Source Server Version : 50170
Source Host           : localhost:3306
Source Database       : 2012xyhui

Target Server Type    : MYSQL
Target Server Version : 50170
File Encoding         : 65001

Date: 2015-03-24 09:36:17
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_job_function_a`
-- ----------------------------
DROP TABLE IF EXISTS `ts_job_function_a`;
CREATE TABLE `ts_job_function_a` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `functionname` varchar(255) NOT NULL DEFAULT '' COMMENT 'ְλ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='ְλA';

-- ----------------------------
-- Records of ts_job_function_a
-- ----------------------------
INSERT INTO `ts_job_function_a` VALUES ('1', '销售/客服/技术支持');
INSERT INTO `ts_job_function_a` VALUES ('2', '公务员/学生/其他');
INSERT INTO `ts_job_function_a` VALUES ('3', '服务业');
INSERT INTO `ts_job_function_a` VALUES ('4', '咨询/法律/教育/翻译');
INSERT INTO `ts_job_function_a` VALUES ('5', '计算机/互联网/通信/电子');
INSERT INTO `ts_job_function_a` VALUES ('6', '广告/市场/媒体/艺术/设计');
INSERT INTO `ts_job_function_a` VALUES ('7', '造纸研发');
INSERT INTO `ts_job_function_a` VALUES ('8', '房产/建筑建设/物业');
INSERT INTO `ts_job_function_a` VALUES ('9', '人事/行政/高级管理');
