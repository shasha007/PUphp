DROP TABLE IF EXISTS `ts_grow_categroy`;
CREATE TABLE `ts_grow_categroy` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `name` varchar(255) NOT NULL ,
  `pid` int(11) unsigned NOT NULL default '0',
  `display_order` int unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY (`pid`),
  KEY (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_grow_information`;
CREATE TABLE `ts_grow_information` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `logo` varchar(255) NOT NULL default '',
  `attId` int unsigned NOT NULL default '0',
  `content` text,
  `ctime` int unsigned NOT NULL default '0',
  `praise` int unsigned default '0',
  `cid1` int(11) unsigned NOT NULL ,
  `cid2` int(11) unsigned NOT NULL ,
  `title` varchar(255) NOT NULL,
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY (`isDel`),
  KEY (`cid1`),
  KEY (`cid2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_grow_comment`;
CREATE TABLE `ts_grow_comment` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned  NOT NULL ,
  `ctime` int unsigned NOT NULL default '0',
  `content` text,
  `grow_id` int(11) unsigned NOT NULL ,
  PRIMARY KEY  (`id`),
  KEY (`uid`),
  KEY (`grow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_grow_collection`;
CREATE TABLE `ts_grow_collection` (
  `uid` int(11) unsigned  NOT NULL ,
  `ctime` int unsigned NOT NULL default '0',
  `grow_id` int(11) unsigned NOT NULL ,
  PRIMARY KEY  (`uid`,`grow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_grow_praise`;
CREATE TABLE `ts_grow_praise` (
  `uid` int(11) unsigned  NOT NULL ,
  `ctime` int unsigned NOT NULL default '0',
  `grow_id` int(11) unsigned NOT NULL ,
  PRIMARY KEY  (`uid`,`grow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

<!-- 游戏分数表-->
DROP TABLE IF EXISTS `ts_game_score`;
CREATE TABLE `ts_game_score` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned  NOT NULL ,
  `score` int unsigned NOT NULL default '0',
  `gid` int(11) unsigned NOT NULL ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid_gid` (`uid`,`gid`),
  KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_game_score_copy`;
CREATE TABLE `ts_game_score_copy` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned  NOT NULL ,
  `score` int unsigned NOT NULL default '0',
  `gid` int(11) unsigned NOT NULL ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid_gid` (`uid`,`gid`),
  KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_game_name`;
CREATE TABLE `ts_game_name` (
  `gid` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL ,
  PRIMARY KEY  (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into ts_game_name (`gid`,`name`) values (13,'该死的钉子'),(559,'天天上厕所'),(6,'情侣版2048'),(561,'爱就块一起吧'),(555,'星际摩托'),
(536,'我插！我插！！'),(528,'黄金矿工'),(556,'目标11'),(553,'砸到你咩咩叫'),(539,'划拳裁判长');

ALTER TABLE `ts_grow_information` ADD `rnum` INT( 11 ) UNSIGNED NOT NULL default '0' COMMENT '阅读数';