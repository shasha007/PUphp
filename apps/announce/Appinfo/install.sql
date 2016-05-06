DROP TABLE IF EXISTS `ts_announce`;
CREATE TABLE `ts_announce` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL COMMENT '发布人',
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `sid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学校',
  `sid1` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '院系',
  `cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
  `isDel` tinyint(1) NOT NULL default '0',
  `readCount` int(11) unsigned NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_announce_category`;
CREATE TABLE `ts_announce_category` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `pid` mediumint(5) NOT NULL default '0',
  `display_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ts_announce_category` (`id`,`title`) VALUES
(1,'校内通知1'),
(2,'校内通知2'),
(3,'校内通知3');

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'announce','version_number','s:1:"1";','2012-11-08 00:00:00');

DROP TABLE IF EXISTS `ts_notice`;
CREATE TABLE `ts_notice` (
`id` int(10) unsigned NOT NULL auto_increment,
`uid` int(11) unsigned NOT NULL COMMENT '发布人',
`front` varchar(255) NOT NULL DEFAULT ''  COMMENT '副标题',
`title` varchar(255) NOT NULL DEFAULT '',
 `content` text,
`cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
`isDel` tinyint(1) NOT NULL default '0',
 `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_notice` CHANGE `cTime` `cTime` DATE NOT NULL;
ALTER TABLE `ts_notice` ADD `sys` tinyint(1) NOT NULL default '0' COMMENT '0无限制1安卓2ios';
ALTER TABLE `ts_notice` ADD INDEX ( `cid` ),ADD INDEX ( `isDel` ),ADD INDEX ( `sys` );