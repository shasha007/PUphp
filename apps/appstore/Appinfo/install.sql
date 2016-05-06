--
-- 表的结构 `ts_appstore_document`
--

DROP TABLE IF EXISTS `ts_appstore_document`;
CREATE TABLE IF NOT EXISTS `ts_appstore_document` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `author_id` int(11) DEFAULT NULL,
  `last_editor_id` int(11) DEFAULT NULL,
  `platform` smallint(6) unsigned NOT NULL default '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `category` tinyint(1) NOT NULL DEFAULT '0',
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',  
  `readCount` int(11) NOT NULL DEFAULT '0',
  `commentCount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`document_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_appstore_app`;
CREATE TABLE IF NOT EXISTS `ts_appstore_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `appfile` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `content` text,
  `img` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `price` tinyint(1) NOT NULL DEFAULT '0',
  `category` tinyint(1) NOT NULL DEFAULT '0',
  `platform` smallint(6) unsigned NOT NULL default '0',
  `cid0` smallint(6) unsigned NOT NULL default '0',
  `cid1` smallint(6) unsigned NOT NULL default '0',
  `url` varchar(1024) DEFAULT NULL,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `rtime` int(11) DEFAULT NULL,
  `isrecom` tinyint(1) NOT NULL default '0',
  `readCount` int(11) NOT NULL DEFAULT '0',
  `commentCount` int(11) NOT NULL DEFAULT '0',
  `downloadCount` int(11) unsigned default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 表的结构 `ts_appstore_category`
--
DROP TABLE IF EXISTS `ts_appstore_category`;
CREATE TABLE IF NOT EXISTS `ts_appstore_category` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `pid` int(5) NOT NULL DEFAULT '0',
  `display_order` smallint(6) unsigned NOT NULL default '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50;

--
-- 转存表中的数据 `ts_newcomer_category`
--

INSERT INTO `ts_appstore_category` (`id`, `title`, `type`, `pid`, `module`) VALUES
(3, 'iPhone', 2, 0, ''),
(4, 'Android', 2, 0, ''),
(1, '应用', 1, 0, ''),
(2, '游戏', 1, 0, ''),
(20, '系统工具', 1, 1, ''),
(21, '网络浏览', 1, 1, ''),
(22, '即时通讯', 1, 1, ''),
(23, '摄影图像', 1, 1, ''),
(24, '安全杀毒', 1, 1, ''),
(25, '新闻资讯', 1, 1, ''),
(26, '通话增强', 1, 1, ''),
(27, '短信增强', 1, 1, ''),
(28, '便捷生活', 1, 1, ''),
(29, '角色扮演', 1, 2, ''),
(30, '飞行射击', 1, 2, ''),
(31, '体育竞技', 1, 2, ''),
(32, '益智休闲', 1, 2, ''),
(33, '策略棋牌', 1, 2, ''),
(34, '赛车游戏', 1, 2, ''),
(35, '动作游戏', 1, 2, ''),
(36, '养成经营', 1, 2, '');


DROP TABLE IF EXISTS `ts_appstore_banner`;
CREATE TABLE IF NOT EXISTS `ts_appstore_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `platform` smallint(6) unsigned NOT NULL default '0',
  `url` varchar(1024) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_appstore_link`;
CREATE TABLE IF NOT EXISTS `ts_appstore_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `url` varchar(1024) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int(11) DEFAULT '0',
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'appstore','version_number','s:1:"1";','2012-08-1 00:00:00');