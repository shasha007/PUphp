DROP TABLE IF EXISTS `ts_question`;
CREATE TABLE `ts_question` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL COMMENT '发布人',
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问卷分类',
  `num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '答题人数',
  `top` tinyint(1) NOT NULL default '0' COMMENT '置顶',
  `recom` tinyint(1) NOT NULL default '0' COMMENT '推荐',
  `lock` tinyint(1) NOT NULL default '0' COMMENT '停止答卷',
  `status` tinyint(1) NOT NULL default '0',
  `deadline` int(11) default NULL COMMENT '结束时间',
  `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_question_opt`;
CREATE TABLE `ts_question_opt` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `qid` int(11) unsigned NOT NULL COMMENT '问卷ID',
  `num` int(11) unsigned NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '问卷选项';

DROP TABLE IF EXISTS `ts_question_category`;
CREATE TABLE `ts_question_category` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL default '1',
  `pid` mediumint(5) NOT NULL default '0',
  `module` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_question_answer`;
CREATE TABLE `ts_question_answer` (
  `id` mediumint(5) NOT NULL auto_increment,
  `qid` int(11) unsigned NOT NULL COMMENT '问卷ID',
  `uid` int(11) unsigned NOT NULL COMMENT '答题人',
  `num` int(11) unsigned NOT NULL,
  `answer` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'question','version_number','s:1:"1";','2012-11-08 00:00:00');