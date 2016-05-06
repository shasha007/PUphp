-- phpMyAdmin SQL Dump
-- version 3.3.4
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2011 年 06 月 28 日 11:13
-- 服务器版本: 5.0.22
-- PHP 版本: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `sociax_2_0`
--

-- --------------------------------------------------------

--
-- 表的结构 `ts_group`
--

DROP TABLE IF EXISTS `ts_group`;
CREATE TABLE IF NOT EXISTS `ts_group` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL,
  `intro` text NOT NULL,
  `logo` varchar(255) NOT NULL default 'default.gif',
  `announce` text NOT NULL,
  `cid0` smallint(6) unsigned NOT NULL,
  `cid1` smallint(6) unsigned NOT NULL,
  `school` int(11) unsigned NOT NULL default 0,
  `membercount` smallint(6) unsigned NOT NULL default '0',
  `threadcount` smallint(6) unsigned NOT NULL default '0',
  `type` enum('open','limit','close') NOT NULL,
  `need_invite` tinyint(1) NOT NULL default '2',
  `need_verify` tinyint(4) NOT NULL,
  `actor_level` tinyint(4) NOT NULL,
  `brower_level` tinyint(4) NOT NULL default '-1',
  `openWeibo` tinyint(1) NOT NULL default '1',
  `openBlog` tinyint(1) NOT NULL default '1',
  `openUploadFile` tinyint(1) NOT NULL default '1',
  `whoUploadFile` tinyint(1) NOT NULL default '1',
  `whoDownloadFile` tinyint(1) NOT NULL default '2',
  `openAlbum` tinyint(1) NOT NULL default '1',
  `whoCreateAlbum` tinyint(1) NOT NULL default '1',
  `whoUploadPic` tinyint(1) NOT NULL default '0',
  `anno` tinyint(1) NOT NULL default '0',
  `ipshow` tinyint(1) NOT NULL default '0',
  `invitepriv` tinyint(1) NOT NULL default '0',
  `createalbumpriv` tinyint(1) NOT NULL default '1',
  `uploadpicpriv` tinyint(1) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `mtime` int(11) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  `isrecom` tinyint(1) NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

alter table `ts_group` add prov_id int(11) unsigned NOT NULL default '1';
-- --------------------------------------------------------

--
-- 表的结构 `ts_group_album`
--

DROP TABLE IF EXISTS `ts_group_album`;
CREATE TABLE IF NOT EXISTS `ts_group_album` (
  `id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `userId` int(11) default NULL,
  `name` varchar(255) default NULL,
  `info` text,
  `cTime` int(11) unsigned default NULL,
  `mTime` int(11) unsigned default NULL,
  `coverImageId` int(11) NOT NULL,
  `coverImagePath` varchar(255) default NULL,
  `photoCount` int(11) default '0',
  `status` tinyint(2) unsigned NOT NULL default '1',
  `share` tinyint(1) NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `uid` (`userId`),
  KEY `cTime` (`cTime`),
  KEY `mTime` (`mTime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ts_group_album`
--


-- --------------------------------------------------------

--
-- 表的结构 `ts_group_attachment`
--

DROP TABLE IF EXISTS `ts_group_attachment`;
CREATE TABLE IF NOT EXISTS `ts_group_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `attachId` int(11) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `note` text NOT NULL,
  `filesize` int(10) NOT NULL DEFAULT '0',
  `filetype` varchar(10) NOT NULL,
  `fileurl` varchar(255) NOT NULL,
  `totaldowns` mediumint(6) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL,
  `mtime` varchar(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `gid_2` (`gid`,`attachId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_category`
--

DROP TABLE IF EXISTS `ts_group_category`;
CREATE TABLE IF NOT EXISTS `ts_group_category` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL default '1',
  `pid` mediumint(5) NOT NULL default '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- 转存表中的数据 `ts_group_category`
--

INSERT INTO `ts_group_category` (`id`, `title`, `type`, `pid`, `module`) VALUES
(1, '同事朋友', 1, 0, ''),
(2, '行业交流', 1, 0, ''),
(3, '兴趣爱好', 1, 0, ''),
(4, '游戏', 1, 0, ''),
(5, '生活休闲', 1, 0, ''),
(6, '学习考试', 1, 0, ''),
(7, '品牌产品', 1, 0, ''),
(8, '粉丝', 1, 0, '');
-- --------------------------------------------------------

--
-- 表的结构 `ts_group_invite_verify`
--

DROP TABLE IF EXISTS `ts_group_invite_verify`;
CREATE TABLE IF NOT EXISTS `ts_group_invite_verify` (
  `invite_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `is_used` int(11) NOT NULL default '0',
  PRIMARY KEY  (`invite_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_log`
--

DROP TABLE IF EXISTS `ts_group_log`;
CREATE TABLE IF NOT EXISTS `ts_group_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `type` varchar(10) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_member`
--

DROP TABLE IF EXISTS `ts_group_member`;
CREATE TABLE IF NOT EXISTS `ts_group_member` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL default '0',
  `name` char(10) NOT NULL,
  `reason` text NOT NULL,
  `status` tinyint(1) default '1',
  `level` tinyint(2) unsigned default '1',
  `ctime` int(11) NOT NULL default '0',
  `mtime` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gid` (`gid`,`uid`),
  KEY `mid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

ALTER TABLE  `ts_group_member` ADD  `remark` VARCHAR( 15 ) NOT NULL COMMENT  '备注' ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_photo`
--

DROP TABLE IF EXISTS `ts_group_photo`;
CREATE TABLE IF NOT EXISTS `ts_group_photo` (
  `id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `attachId` int(11) NOT NULL,
  `albumId` int(11) NOT NULL,
  `userId` int(11) default NULL,
  `status` tinyint(2) unsigned NOT NULL default '1',
  `name` varchar(255) NOT NULL,
  `cTime` int(11) unsigned default NULL,
  `mTime` int(11) unsigned default NULL,
  `info` text,
  `commentCount` int(11) unsigned default '0',
  `readCount` int(11) unsigned default '0',
  `savepath` varchar(255) NOT NULL,
  `size` int(11) NOT NULL default '0',
  `tags` text,
  `order` int(11) NOT NULL,
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`,`albumId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_post`
--

DROP TABLE IF EXISTS `ts_group_post`;
CREATE TABLE IF NOT EXISTS `ts_group_post` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL,
  `content` text NOT NULL,
  `ip` char(16) NOT NULL,
  `istopic` tinyint(1) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `quote` int(11) unsigned NOT NULL default '0',
  `is_del` varchar(1) NOT NULL default '0',
  `attach` text,
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`,`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_tag`
--

DROP TABLE IF EXISTS `ts_group_tag`;
CREATE TABLE IF NOT EXISTS `ts_group_tag` (
  `group_tag_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY  (`group_tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_topic`
--

DROP TABLE IF EXISTS `ts_group_topic`;
CREATE TABLE IF NOT EXISTS `ts_group_topic` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `name` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `cid` int(11) unsigned NOT NULL,
  `viewcount` smallint(6) unsigned NOT NULL default '0',
  `replycount` smallint(6) unsigned NOT NULL default '0',
  `dist` tinyint(1) NOT NULL default '0',
  `top` tinyint(1) NOT NULL default '0',
  `lock` tinyint(1) NOT NULL default '0',
  `addtime` int(11) NOT NULL default '0',
  `replytime` int(11) NOT NULL default '0',
  `mtime` int(11) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `isrecom` tinyint(1) NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  `attach` text,
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`),
  KEY `gid_2` (`gid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_topic_category`
--

DROP TABLE IF EXISTS `ts_group_topic_category`;
CREATE TABLE IF NOT EXISTS `ts_group_topic_category` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_topic_collect`
--

DROP TABLE IF EXISTS `ts_group_topic_collect`;
CREATE TABLE IF NOT EXISTS `ts_group_topic_collect` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `tid` int(11) unsigned NOT NULL default '0',
  `mid` int(11) unsigned NOT NULL default '0',
  `addtime` int(11) unsigned NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tid` (`tid`,`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_user_count`
--

DROP TABLE IF EXISTS `ts_group_user_count`;
CREATE TABLE IF NOT EXISTS `ts_group_user_count` (
  `uid` int(11) NOT NULL,
  `atme` mediumint(6) NOT NULL,
  `comment` mediumint(6) NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo`
--

DROP TABLE IF EXISTS `ts_group_weibo`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo` (
  `weibo_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) NOT NULL,
  `from` tinyint(1) NOT NULL,
  `from_data` text,
  `comment` mediumint(8) NOT NULL,
  `transpond_id` int(11) NOT NULL default '0',
  `transpond` mediumint(8) NOT NULL,
  `type` varchar(255) default '0',
  `type_data` text,
  `isdel` tinyint(1) NOT NULL,
  PRIMARY KEY  (`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo_atme`
--

DROP TABLE IF EXISTS `ts_group_weibo_atme`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo_atme` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `weibo_id` int(11) NOT NULL,
  UNIQUE KEY `uid` (`uid`,`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo_comment`
--

DROP TABLE IF EXISTS `ts_group_weibo_comment`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo_comment` (
  `comment_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `reply_comment_id` int(11) NOT NULL,
  `reply_uid` int(11) NOT NULL,
  `weibo_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) NOT NULL,
  `isdel` tinyint(1) NOT NULL,
  PRIMARY KEY  (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo_topic`
--

DROP TABLE IF EXISTS `ts_group_weibo_topic`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo_topic` (
  `topic_id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `count` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


DROP TABLE IF EXISTS `ts_group_ditu_list`;
CREATE TABLE IF NOT EXISTS `ts_group_ditu_list` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `school` int(11) unsigned NOT NULL default 0,
  `title` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `sort` int(11) unsigned NOT NULL default 0,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `ts_group_ditu`;
CREATE TABLE IF NOT EXISTS `ts_group_ditu` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `listid` int(11) unsigned NOT NULL default 0,
  `title` varchar(255) NOT NULL,
  `lat` varchar(255) NOT NULL,
  `lng` varchar(255) NOT NULL,
  `sort` int(11) unsigned NOT NULL default 0,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
	(0, 'group', 'close_invite', 's:1:\"2\";', '2011-06-28 11:46:49'),
	(0, 'group', 'createAudit', 's:1:\"1\";', '2011-06-28 11:46:47'),
	(0, 'group', 'createGroup', 's:1:\"1\";', '2011-06-28 11:46:47'),
	(0, 'group', 'createMaxGroup', 's:1:\"3\";', '2011-06-28 11:46:47'),
	(0, 'group', 'creditType', 's:10:\"experience\";', '2011-06-28 11:46:47'),
	(0, 'group', 'editSubmit', 's:1:\"1\";', '2011-06-28 11:46:49'),
	(0, 'group', 'hotTags', 's:0:\"\";', '2011-06-28 11:46:47'),
	(0, 'group', 'joinMaxGroup', 's:2:\"10\";', '2011-06-28 11:46:47'),
	(0, 'group', 'openBlog', 's:1:\"1\";', '2011-06-28 11:46:49'),
	(0, 'group', 'openUploadFile', 's:1:\"1\";', '2011-06-28 11:46:49'),
	(0, 'group', 'open_invite', 's:1:\"0\";', '2011-06-28 11:46:49'),
	(0, 'group', 'discussion', 's:1:\"1\";', '2011-06-28 11:46:49'),
	(0, 'group', 'uploadFile', 's:1:\"1\";', '2011-06-28 11:46:47'),
	(0, 'group', 'simpleFileSize', 's:1:\"2\";', '2011-06-28 11:46:47'),
	(0, 'group', 'spaceSize', 's:2:\"10\";', '2011-06-28 11:46:47'),
	(0, 'group', 'uploadFileType', 's:59:\"jpg|gif|png|jpeg|bmp|zip|rar|doc|xls|ppt|docx|xlsx|pptx|pdf\";', '2011-06-28 11:46:47'),
	(0, 'group', 'userCredit', 's:3:\"100\";', '2011-06-28 11:46:47'),
	(0, 'group', 'whoDownloadFile', 's:1:\"3\";', '2011-06-28 11:46:49'),
	(0, 'group', 'whoUploadFile', 's:1:\"3\";', '2011-06-28 11:46:49');

#模板数据
DELETE FROM `ts_template` WHERE `type` = 'group';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`)
VALUES
    ('group_share_weibo', '分享社团', '','我在@{author} 的社团 【{name}】 里玩得很嗨， {url} 推荐大家也来看看哦~', 'zh', 'group', 'weibo', 0, 1307590430),
	('group_post_share_weibo', '分享帖子', '','分享@{author} 的帖子:【{title}】 {url}', 'zh', 'group', 'weibo', 0, 1307415524),
	('group_post_create_weibo', '发布帖子', '','我发起了一份帖子:【{title}】 {url}', 'zh', 'group', 'weibo', 0, 1307417128);

	#积分配置
	DELETE FROM `ts_credit_setting` WHERE `type` = 'group';
	INSERT INTO `ts_credit_setting` (`name`, `alias`, `type`, `info`, `score`)
	VALUES
		('add_group', '创建社团', 'group', '{action}{sign}了{score}{typecn}', '5'),
		('delete_group', '解散群租', 'group', '{action}{sign}了{score}{typecn}', '-5'),
		('join_group', '加入社团', 'group', '{action}{sign}了{score}{typecn}', '2'),
		('quit_group', '退出社团', 'group', '{action}{sign}了{score}{typecn}', '-2'),
		('group_add_topic', '发表帖子', 'group', '{action}{sign}了{score}{typecn}', '5'),
		('group_reply_topic', '回复帖子', 'group', '{action}{sign}了{score}{typecn}', '2'),
		('group_delete_topic', '删除帖子', 'group', '{action}{sign}了{score}{typecn}', '-5'),
		('group_upload_file', '上传文件', 'group', '{action}{sign}了{score}{typecn}', '5'),
		('group_download_file', '下载文件', 'group', '{action}{sign}了{score}{typecn}', '2'),
		('group_delete_file', '删除文件', 'group', '{action}{sign}了{score}{typecn}', '-5');

INSERT INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'group','version_number','s:5:"28172";','2012-02-14 10:00:00');

DROP TABLE IF EXISTS `ts_group_validate`;
CREATE TABLE IF NOT EXISTS `ts_group_validate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL COMMENT '审核人uid',
  `reason` varchar(255) NOT NULL,
  `cover_id` int(11) unsigned NOT NULL,
  `reject` varchar(255) NOT NULL COMMENT '驳回原因',
  `atime` int(11) unsigned NOT NULL COMMENT '申请时间',
  `vtime` int(11) unsigned NOT NULL COMMENT '审核时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0驳回1申请中2通过',
  PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

ALTER TABLE `ts_group_validate` ADD `sid` INT( 11 ) UNSIGNED NOT NULL AFTER `gid`;
ALTER TABLE `ts_group` ADD `vStatus` tinyint(1) NOT NULL default '0' COMMENT '校方认证状态0未认证1认证';
ALTER TABLE `ts_group` ADD `vStern` tinyint(1) NOT NULL default '0' COMMENT '0123颗星';
ALTER TABLE `ts_group` ADD `disband` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `vStern` ;