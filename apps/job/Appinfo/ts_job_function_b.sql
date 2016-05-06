/*
Navicat MySQL Data Transfer

Source Server         : myproject
Source Server Version : 50170
Source Host           : localhost:3306
Source Database       : 2012xyhui

Target Server Type    : MYSQL
Target Server Version : 50170
File Encoding         : 65001

Date: 2015-03-24 09:36:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_job_function_b`
-- ----------------------------
DROP TABLE IF EXISTS `ts_job_function_b`;
CREATE TABLE `ts_job_function_b` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `functionname` varchar(255) NOT NULL DEFAULT '' COMMENT 'ְλ',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='ְλB';

-- ----------------------------
-- Records of ts_job_function_b
-- ----------------------------
INSERT INTO `ts_job_function_b` VALUES ('1', '销售管理', '1');
INSERT INTO `ts_job_function_b` VALUES ('2', '销售人员', '1');
INSERT INTO `ts_job_function_b` VALUES ('3', '销售行政/商务', '1');
INSERT INTO `ts_job_function_b` VALUES ('4', '客户服务/技术支持', '1');
INSERT INTO `ts_job_function_b` VALUES ('5', '公务员/事业单位/科研', '2');
INSERT INTO `ts_job_function_b` VALUES ('6', '毕业生/实习生/培训生', '2');
INSERT INTO `ts_job_function_b` VALUES ('7', '农/林/牧/渔业', '2');
INSERT INTO `ts_job_function_b` VALUES ('8', '餐饮/娱乐', '3');
INSERT INTO `ts_job_function_b` VALUES ('9', '酒店/旅游', '3');
INSERT INTO `ts_job_function_b` VALUES ('10', '百货/连锁/零售服务', '3');
INSERT INTO `ts_job_function_b` VALUES ('11', '交通运输服务', '3');
INSERT INTO `ts_job_function_b` VALUES ('12', '咨询/顾问', '4');
INSERT INTO `ts_job_function_b` VALUES ('13', '教育/培训', '4');
INSERT INTO `ts_job_function_b` VALUES ('14', '翻译', '4');
INSERT INTO `ts_job_function_b` VALUES ('15', '计算机软件/系统集成', '5');
INSERT INTO `ts_job_function_b` VALUES ('16', '计算机硬件', '5');
INSERT INTO `ts_job_function_b` VALUES ('17', '互联网/移动互联网/电子商务/网游', '5');
INSERT INTO `ts_job_function_b` VALUES ('18', 'IT管理/品管/IT支持', '5');
INSERT INTO `ts_job_function_b` VALUES ('19', '电子/电器/半导体/仪器/仪表', '5');
INSERT INTO `ts_job_function_b` VALUES ('20', '金融/证券/期货/投资', '5');
INSERT INTO `ts_job_function_b` VALUES ('21', '银行', '5');
INSERT INTO `ts_job_function_b` VALUES ('22', '财务/审计/税务', '5');
INSERT INTO `ts_job_function_b` VALUES ('23', '保险', '5');
INSERT INTO `ts_job_function_b` VALUES ('24', '汽车/工程/机械', '5');
INSERT INTO `ts_job_function_b` VALUES ('25', '汽车销售与服务', '5');
INSERT INTO `ts_job_function_b` VALUES ('26', '工程/机械', '5');
INSERT INTO `ts_job_function_b` VALUES ('27', '生产/营运/采购/物流', '5');
INSERT INTO `ts_job_function_b` VALUES ('28', '质量管理/安全防护', '5');
INSERT INTO `ts_job_function_b` VALUES ('29', '服装/纺织/皮革', '5');
INSERT INTO `ts_job_function_b` VALUES ('30', '物流/仓储', '5');
INSERT INTO `ts_job_function_b` VALUES ('31', '采购/贸易', '5');
INSERT INTO `ts_job_function_b` VALUES ('32', '市场/营销', '6');
INSERT INTO `ts_job_function_b` VALUES ('33', '公关/媒介', '6');
INSERT INTO `ts_job_function_b` VALUES ('34', '公关/媒介', '6');
INSERT INTO `ts_job_function_b` VALUES ('35', '广告/会展', '6');
INSERT INTO `ts_job_function_b` VALUES ('36', '写作/报刊/出版/印刷', '6');
INSERT INTO `ts_job_function_b` VALUES ('37', '影视/媒体', '6');
INSERT INTO `ts_job_function_b` VALUES ('38', '艺术/设计/创意', '6');
INSERT INTO `ts_job_function_b` VALUES ('39', '生物/制药/医疗/能源/环保', '6');
INSERT INTO `ts_job_function_b` VALUES ('40', '化工', '6');
INSERT INTO `ts_job_function_b` VALUES ('41', '医院/医疗/护理', '6');
INSERT INTO `ts_job_function_b` VALUES ('42', '电气/能源/矿产/地质勘查', '6');
INSERT INTO `ts_job_function_b` VALUES ('43', '环境科学/环保', '6');
INSERT INTO `ts_job_function_b` VALUES ('44', '建筑装潢/市政建设', '8');
INSERT INTO `ts_job_function_b` VALUES ('45', '房地产', '8');
INSERT INTO `ts_job_function_b` VALUES ('46', '物业管理', '8');
INSERT INTO `ts_job_function_b` VALUES ('47', '人力资源', '9');
INSERT INTO `ts_job_function_b` VALUES ('48', '高级管理', '9');
INSERT INTO `ts_job_function_b` VALUES ('49', '行政/后勤/文秘', '9');
INSERT INTO `ts_job_function_b` VALUES ('50', '项目管理', '9');
