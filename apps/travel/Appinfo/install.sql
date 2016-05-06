DROP TABLE IF EXISTS `ts_travel_cat`;
CREATE TABLE `ts_travel_cat` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_travel_area`;
CREATE TABLE `ts_travel_area` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ts_travel`;
CREATE TABLE `ts_travel` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` VARCHAR( 250 ) NOT NULL COMMENT '活动简介',
  `cost` int(10) unsigned  NOT NULL default '0',
 `costExplain` varchar(255) default '',
 `cost2` int(10) unsigned  NOT NULL default '0',
 `costExplain2` varchar(255) default '',
  `contact` varchar(32) default '' comment '联系方式',
  `catId` smallint(11) unsigned NOT NULL default '0' comment '分类',
  `areaId` smallint(11) unsigned NOT NULL default '0' comment '地区',
  `sTime` int(11) unsigned default NULL,
  `eTime` int(11) unsigned default NULL,
  `deadline` int(11) unsigned NOT NULL comment '截止报名',
  `address` varchar(255) default '',
  `joinCount` int(11) unsigned NOT NULL default '0',
  `limitCount` int(11) unsigned NOT NULL default '0' comment '名额限制',
  `coverId` int(11) unsigned NOT NULL default '0' comment '封面附件ts_attach',
  `isTop` tinyint(1) unsigned NOT NULL default '0' comment '置顶',
  `isDel` tinyint(1) unsigned NOT NULL default '0',
  `cTime` int(11) unsigned NOT NULL,
  `rTime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_travel_news`;
CREATE TABLE `ts_travel_news` (
  `id` int(10) NOT NULL auto_increment,
  `travelId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `isDel` tinyint(1) NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_travel_user`;
CREATE TABLE `ts_travel_user` (
  `id` int(11) NOT NULL auto_increment,
  `travelId` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `cTime` int(11) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `tel` varchar(25) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL default '0' COMMENT '支付金额类型',
  `sex` tinyint(1) NOT NULL default '0' COMMENT '性别（0女1男）',
  `sid` int(11) NOT NULL default '0' comment '学校',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ts_travel_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `travelId` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `content` text,
  `cTime` int(12) DEFAULT NULL,
`isDel` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'travel','version_number','s:1:"1";','2012-08-1 00:00:00');

ALTER TABLE `ts_travel_user` ADD INDEX ( `uid` );
ALTER TABLE `ts_travel_user` ADD INDEX ( `travelId` );
ALTER TABLE `ts_travel_news` ADD INDEX ( `travelId` );

DROP TABLE IF EXISTS `ts_travel_partner`;
CREATE TABLE IF NOT EXISTS `ts_travel_partner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL default '',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `display_order` int(10) unsigned NOT NULL default '0',
  `has_pic` tinyint(1) unsigned NOT NULL default '0',
  `ctime` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL default '0',
  `wap_url` varchar(255) NOT NULL DEFAULT '',
  `apk_name` varchar(255) NOT NULL DEFAULT '',
  `apk_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `display_order` (`display_order`),
  KEY `has_pic` (`has_pic`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;