DROP TABLE IF EXISTS `ts_wenku`;

CREATE TABLE IF NOT EXISTS `ts_wenku` (
  `id` int(11) NOT NULL auto_increment,
  `attachId` int(11) default NULL,
  `schoolid` smallint(6) unsigned NOT NULL default '0',
  `cid0` smallint(6) unsigned NOT NULL default '0',
  `cid1` smallint(6) unsigned NOT NULL default '0',
  `userId` int(11) default NULL,
  `status` tinyint(2) unsigned NOT NULL default '0',
  `rate` tinyint(2) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `cTime` int(11) unsigned default NULL,
  `mTime` int(11) unsigned default NULL,
  `rTime` int(11) unsigned default NULL,
  `intro` text,
  `commentCount` int(11) unsigned default '0',
  `readCount` int(11) unsigned default '0',
  `downloadCount` int(11) unsigned default '0',
  `savepath` varchar(255) default NULL,
  `cover` varchar(255) default NULL,
  `size` int(11) NOT NULL default '0',
  `extension` varchar(255) default NULL,
  `credit` int(11) NOT NULL default '0',
  `privacy` int(1) NOT NULL default '1',
  `tranStatus` tinyint(1) NOT NULL default '0',
  `tags` text,
  `order` int(11) NOT NULL default '0',
  `isrecom` tinyint(1) NOT NULL default '0',
  `isDel` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_wenku_category`;
CREATE TABLE IF NOT EXISTS `ts_wenku_category` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL default '1',
  `pid` mediumint(5) NOT NULL default '0',
  `display_order` smallint(6) unsigned NOT NULL default '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

DROP TABLE IF EXISTS `ts_wenku_download`;
CREATE TABLE IF NOT EXISTS `ts_wenku_download` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) default NULL,
  `ownerid` int(11) default NULL,
  `docid` int(11) default NULL,
  `price` int(11) default NULL,
  `dTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_wenku_school`;
CREATE TABLE IF NOT EXISTS `ts_wenku_school` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL default '1',
  `pid` mediumint(5) NOT NULL default '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

INSERT INTO `ts_wenku_school` (`id`, `title`, `type`, `pid`, `module`) VALUES
(1, '苏州大学', 1, 0, ''),
(2, '苏州科技学院', 1, 0, ''),
(3, '苏州市职业大学', 1, 0, ''),
(4, '苏州工业职业技术学院', 1, 0, '');

--
-- 转存表中的数据 `ts_wenku_category`
--

INSERT INTO `ts_wenku_category` (`id`, `title`, `type`, `pid`, `module`) VALUES
(1, '生活休闲', 1, 0, ''),
(2, '办公文档', 1, 0, ''),
(3, '行业资料', 1, 0, ''),
(4, '学位论文', 1, 0, ''),
(5, '人力资源', 1, 0, ''),
(6, '资格考试', 1, 0, ''),
(7, '计算机', 1, 0, '');


-- --------------------------------------------------------

# 增加doc的默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'document';
INSERT INTO `ts_credit_setting`
VALUES
	('', 'add_wenku_document', '上传课件', 'photo', '{action}{sign}了{score}{typecn}', '2', '2'),
	('', 'delete_wenku_document', '删除课件', 'photo', '{action}{sign}了{score}{typecn}', '-2', '-2');

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'document','version_number','s:1:"1";','2012-08-01 00:00:00');

DROP TABLE `ts_wenku_school`;