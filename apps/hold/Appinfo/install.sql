DROP TABLE IF EXISTS `ts_hold_info`;
CREATE TABLE `ts_hold_info` (
  `id` int(10) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `isDel` tinyint(1) NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_hold_user`;
CREATE TABLE `ts_hold_user` (
  `id` mediumint(5) NOT NULL auto_increment,
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '照片',
  `num` varchar(10) NOT NULL DEFAULT '' COMMENT '编号',
  `note` int(11) NOT NULL DEFAULT 0 COMMENT '成绩',
  `realname` varchar(255) NOT NULL,
  `sex` tinyint(1) NOT NULL default '0' COMMENT '性别（0女1男）',
  `school` varchar(255) NOT NULL DEFAULT '' COMMENT '学校',
  `college` varchar(255) NOT NULL DEFAULT '' COMMENT '学院',
  `comment` varchar(255) NOT NULL DEFAULT '' COMMENT '评委点评',
  `ticket` int(11) NOT NULL DEFAULT 0 COMMENT '投票数',
  `isDel` tinyint(1) NOT NULL default '0',
  `stoped` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_hold_user_img`;
CREATE TABLE `ts_hold_user_img` (
  `id` mediumint(5) NOT NULL auto_increment,
  `uid` mediumint(5) NOT NULL default '0',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '靓照',
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_hold_user_flash`;
CREATE TABLE `ts_hold_user_flash` (
  `id` mediumint(5) NOT NULL auto_increment,
  `uid` mediumint(5) NOT NULL default '0',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '靓照',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '视频',
  `title` varchar(255) NOT NULL DEFAULT '',
  `flashvar` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_hold_vote`;
CREATE TABLE `ts_hold_vote` (
  `id` mediumint(5) NOT NULL auto_increment,
  `mid` mediumint(5) NOT NULL default '0' COMMENT '投票用户',
  `pid` mediumint(5) NOT NULL default '0' COMMENT '选手',
  `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'hold','version_number','s:1:"1";','2012-10-29 00:00:00');