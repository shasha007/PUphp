--
-- 表的结构 `ts_newcomer_document`
--

DROP TABLE IF EXISTS `ts_newcomer_document`;
CREATE TABLE IF NOT EXISTS `ts_newcomer_document` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `author_id` int(11) DEFAULT NULL,
  `last_editor_id` int(11) DEFAULT NULL,
  `isrecom` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `category0` smallint(6) unsigned NOT NULL default '0',
  `icon` varchar(255) DEFAULT NULL,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',
  `readCount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`document_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=10;

INSERT INTO `ts_newcomer_document` (`document_id`, `title`, `content`, `author_id`, `last_editor_id`, `is_active`, `ctime`, `mtime`, `readCount`, `display_order`, `category0`, `isrecom`) VALUES
(1, '报道须知', '请修改为报道须知的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 1, 1, 1),
(2, '交通指南', '请修改为交通指南的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 2, 1, 1),
(3, '户籍知识', '请修改为户籍知识的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 3, 1, 1),
(4, '医疗卫生', '请修改为医疗卫生的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 4, 1, 1),
(5, '就餐指南', '请修改为就餐指南的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 5, 1, 1),
(6, '住宿指南', '请修改为住宿指南的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 6, 1, 1),
(7, '帮困助学', '请修改为帮困助学的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 7, 1, 1),
(8, '财政管理', '请修改为财政管理的内容, 勿删除', 1, 1, 1, 1343710201, 1343710201, 1, 8, 1, 1);


DROP TABLE IF EXISTS `ts_newcomer_document_category`;
CREATE TABLE IF NOT EXISTS `ts_newcomer_document_category` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `pid` int(5) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50;
INSERT INTO `ts_newcomer_document_category` (`id`, `title`, `type`, `pid`, `module`) VALUES
(1, '新生导航', 1, 0, ''),
(2, '大学生活', 1, 0, ''),
(3, '校园风采', 1, 0, '');

--
-- 表的结构 `ts_newcomer_category`
--
DROP TABLE IF EXISTS `ts_newcomer_category`;
CREATE TABLE IF NOT EXISTS `ts_newcomer_category` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `pid` int(5) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50;

--
-- 转存表中的数据 `ts_newcomer_category`
--

INSERT INTO `ts_newcomer_category` (`id`, `title`, `type`, `pid`, `module`) VALUES
(1, '苏州大学', 1, 0, ''),
(2, '苏州科技学院', 1, 0, ''),
(3, '苏州市职业大学', 1, 0, ''),
(4, '苏州工业职业技术学院', 1, 0, ''),
(5, '艺术学院', 1, 1, ''),
(6, '物理科学与技术学院', 1, 1, ''),
(7, '能源学院', 1, 1, ''),
(8, '医学部', 1, 1, ''),
(9, '纳米科学技术学院', 1, 1, ''),
(10, '城市轨道交通学院', 1, 1, ''),
(11, '社会学院', 1, 1, ''),
(12, '沙钢钢铁学院', 1, 1, ''),
(13, '外国语学院', 1, 1, ''),
(14, '机电工程学院', 1, 1, ''),
(15, '政治与公共管理学院', 1, 1, ''),
(16, '电子信息学院', 1, 1, ''),
(17, '文学院', 1, 1, ''),
(18, '体育学院', 1, 1, ''),
(19, '材料与化学化工学部', 1, 1, ''),
(20, '计算机科学与技术学院', 1, 1, ''),
(21, '金螳螂建筑与城市环境学院', 1, 1, ''),
(22, '凤凰传媒学院', 1, 1, ''),
(23, '数学科学学院', 1, 1, ''),
(24, '教育学院', 1, 1, ''),
(25, '纺织与服装工程学院', 1, 1, ''),
(26, '东吴商学院', 1, 1, ''),
(27, '王健法学院', 1, 1, ''),
(28, '建筑与城市规划学院', 1, 2, ''),
(29, '环境科学与工程学院', 1, 2, ''),
(30, '资源环境与城乡规划管理', 1, 2, ''),
(31, '电子与信息工程学院', 1, 2, ''),
(32, '经济与管理学院', 1, 2, ''),
(33, '人文学院', 1, 2, ''),
(34, '教育与公共管理学院', 1, 2, ''),
(35, '数理学院', 1, 2, ''),
(36, '化学与生物工程学院', 1, 2, ''),
(37, '传媒与视觉艺术学院', 1, 2, ''),
(38, '外国语学院', 1, 2, ''),
(39, '音乐学院', 1, 2, ''),
(40, '机电工程系', 1, 2, '');


#模板数据
DELETE FROM `ts_user_set` WHERE `fieldkey` = 'newcomer_school' OR `fieldkey` = 'newcomer_school' OR `fieldkey` = 'newcomer_school';
INSERT INTO `ts_user_set` (`fieldkey`, `fieldname`, `status`, `module`, `showspace`) VALUES
('newcomer_school', '学校', 1, 'intro', 1),
('newcomer_department', '院系', 1, 'intro', 1),
('newcomer_studentno', '学号', 1, 'intro', 1);

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'newcomer','version_number','s:1:"1";','2012-08-1 00:00:00');