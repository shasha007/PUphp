DROP TABLE IF EXISTS `ts_makefriends_user`;
CREATE TABLE `ts_makefriends_user` (
  `uid` int(11) unsigned NOT NULL,
  `nickname` VARCHAR( 255 ) NOT NULL default '',
  `headPhotoId` int(11) unsigned NOT NULL default '0' comment '头像ID',
  `cTime` int(11) unsigned NOT NULL,
  `popularity` int(11) unsigned NOT NULL default '0' comment '人气值',
  `contribution` int(11) unsigned NOT NULL default '0' comment '贡献值',
  `backAllCount` int(11) unsigned NOT NULL default '0' comment '总评论数',
  `praiseAllCount` int(11) unsigned NOT NULL default '0' comment '总赞数',
   KEY `popularity` (`popularity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '交友用户';

DROP TABLE IF EXISTS `ts_makefriends_usergx`;
CREATE TABLE `ts_makefriends_usergx` (
  `uid` int(11) unsigned NOT NULL,
  `type` VARCHAR( 255 ) NOT NULL default '',
  `toid` int unsigned NOT NULL default '0',
  `total` smallint NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '交友用户贡献值流水';

DROP TABLE IF EXISTS `ts_makefriends_photo`;
CREATE TABLE `ts_makefriends_photo` (
  `photoId` int(11) unsigned  NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `isDel` tinyint(1) unsigned NOT NULL default '0',
  `content` VARCHAR( 255 ) NOT NULL default '',
  `cTime` int(11) unsigned NOT NULL,
  `praiseCount` int(11) unsigned NOT NULL default '0' comment '喜欢数',
  `weekCount` int unsigned NOT NULL default '0' comment '周喜欢数',
  `backCount` int(11) unsigned NOT NULL default '0' comment '评论数',
   PRIMARY KEY  (`photoId`),
   KEY `isDel` (`isDel`),
   KEY `weekCount` (`weekCount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '交友照片';

DROP TABLE IF EXISTS `ts_makefriends_comment`;
CREATE TABLE `ts_makefriends_comment` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `photoId` int(11) unsigned NOT NULL default '0' comment '照片Id',
  `content` VARCHAR( 255 ) NOT NULL default '',
  `cTime` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
   PRIMARY KEY  (`id`),
   KEY `photoId` (`photoId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '照片评论';

DROP TABLE IF EXISTS `ts_makefriends_attention`;
CREATE TABLE `ts_makefriends_attention` (
  `uid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL,
  `cTime` int(11) unsigned NOT NULL,
   KEY `uid` (`uid`),
   KEY `tid` (`tid`),
   KEY `cTime` (`cTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '交友关注';

DROP TABLE IF EXISTS `ts_makefriends_gift`;
CREATE TABLE `ts_makefriends_gift` (
  `uid` int(11) unsigned NOT NULL,
  `toid` int(11) unsigned NOT NULL,
  `giftCode` tinyint(1) unsigned NOT NULL,
  `giftNum` smallint unsigned NOT NULL comment '数量',
  `price` DECIMAL(10,2) unsigned NOT NULL comment '单价',
  `day` date NOT NULL,
   KEY `uid` (`uid`),
   KEY `toid` (`toid`),
   KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '送礼流水';

DROP TABLE IF EXISTS `ts_makefriends_giftcount`;
CREATE TABLE `ts_makefriends_giftcount` (
  `uid` int(11) unsigned NOT NULL,
  `toid` int(11) unsigned NOT NULL,
  `total` DECIMAL(10,2) unsigned NOT NULL,
   PRIMARY KEY  (`uid`,`toid`),
   KEY `uid` (`uid`),
   KEY `toid` (`toid`),
   KEY `total` (`total`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '送礼统计';

DROP TABLE IF EXISTS `ts_makefriends_praise`;
CREATE TABLE `ts_makefriends_praise` (
  `uid` int(11) unsigned NOT NULL,
  `photoId` int(11) unsigned NOT NULL comment '照片id',
  `day` date NOT NULL,
  PRIMARY KEY  (`uid`,`photoId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '照片喜欢';

alter table ts_makefriends_photo add `newComment` tinyint(1) unsigned NOT NULL default '0',
add key `uid` (`uid`);
alter table ts_makefriends_attention add `newPhoto` tinyint(1) unsigned NOT NULL default '0';
alter table ts_makefriends_gift add `newGift` tinyint(1) unsigned NOT NULL default '1';

alter table ts_makefriends_photo add `w` smallint unsigned NOT NULL default '0',
add `h` smallint unsigned NOT NULL default '0';
alter table ts_makefriends_user add `w` smallint unsigned NOT NULL default '0' after `headPhotoId`,
add `h` smallint unsigned NOT NULL default '0' after `w`;

ALTER TABLE ts_makefriends_user add `weekRq` int NOT NULL default '0';
ALTER TABLE ts_makefriends_user DROP INDEX popularity;
ALTER TABLE `ts_makefriends_user` ADD INDEX ( `weekRq` );
ALTER TABLE `ts_makefriends_user` ADD INDEX ( `cTime` );
ALTER TABLE `ts_makefriends_user` CHANGE `popularity` `popularity` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '人气值';
ALTER TABLE `ts_makefriends_user` CHANGE  `backAllCount` `backAllCount` int(11) unsigned NOT NULL default '0' comment '总评论数';
ALTER TABLE `ts_makefriends_user` CHANGE `praiseAllCount` `praiseAllCount` int(11) unsigned NOT NULL default '0' comment '总赞数';
ALTER TABLE `ts_makefriends_user` ADD PRIMARY KEY(`uid`);
ALTER TABLE ts_makefriends_gift add `buyNum` smallint unsigned NOT NULL default 0 comment '付费数量' after `giftNum`;
ALTER TABLE ts_makefriends_photo add `sid` int unsigned NOT NULL default 0 after `uid`;
ALTER TABLE ts_makefriends_gift add `sid` int unsigned NOT NULL default 0 after `uid`;

ALTER TABLE `ts_makefriends_gift` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `ts_makefriends_usergx` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `ts_makefriends_attention` ADD PRIMARY KEY ( `uid` , `tid` ) ;

TRUNCATE TABLE `ts_makefriends_user`;
TRUNCATE TABLE `ts_makefriends_usergx`;
TRUNCATE TABLE `ts_makefriends_photo`;
TRUNCATE TABLE `ts_makefriends_comment`;
TRUNCATE TABLE `ts_makefriends_attention`;
TRUNCATE TABLE `ts_makefriends_gift`;
TRUNCATE TABLE `ts_makefriends_giftcount`;
TRUNCATE TABLE `ts_makefriends_praise`;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES (0, 'makefriends', 'initrq', 's:10:"2015-03-27";', '2015-03-27 15:19:10');
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES (0, 'makefriends_photo', 'initWeekPraise', 's:10:"2015-10-27";', '2015-10-27 15:19:10');
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES (0, 'makefriends_photo', 'initMonthPraise', 's:10:"2015-10-27";', '2015-10-27 15:19:10');

ALTER TABLE ts_makefriends_user add `monthRq` int NOT NULL default '0';
ALTER TABLE ts_makefriends_user add `cRq` int NOT NULL default '0' comment '自定义人气';
ALTER TABLE ts_makefriends_photo add `monthCount` int NOT NULL default '0';
DROP TABLE IF EXISTS `ts_makefriends_brecord`;
CREATE TABLE `ts_makefriends_brecord` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `photoId` int(11) unsigned NOT NULL comment '照片id',
  `cTime` int(11) unsigned NOT NULL,
   PRIMARY KEY  (`id`),
   KEY `cTime` (`cTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '照片浏览记录';

ALTER TABLE ts_makefriends_user add `cityId` int NOT NULL default '0' comment '城市id';

/*TA SHOW关注与平台合并*/
alter table ts_weibo_follow add `newPhoto` tinyint(1) unsigned NOT NULL default '0';
alter table ts_weibo_follow add `from` tinyint(1) unsigned NOT NULL default '0';
ALTER TABLE `ts_weibo_follow` CHANGE `from` `froms`tinyint(1) unsigned NOT NULL default '0';

/*添加回复*/
alter table ts_makefriends_comment add `rid` int(10) unsigned NOT NULL default '0';

/*擂台*/
DROP TABLE IF EXISTS `ts_makefriends_arena`;
CREATE TABLE `ts_makefriends_arena` (
  `arenaId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `photoId` int(10) unsigned DEFAULT NULL,
  `declaration` varchar(255) DEFAULT '',
  `cTime` int(10) unsigned DEFAULT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  `commentCount` int(10) unsigned DEFAULT 0,
  `startTime` int(10) unsigned DEFAULT '0',
  `arenaResult` tinyint(1) unsigned DEFAULT '0',
  `voteCount` int(10) unsigned DEFAULT '0' COMMENT '得票数',
  `pid` int(10) unsigned DEFAULT '0',
  `arenaStatus` tinyint(1) unsigned DEFAULT '0',
   PRIMARY KEY (`arenaId`)，
   KEY `cTime` (`cTime`),
   KEY `pid` (`pid`),
   KEY `arenaStatus` (`arenaStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '擂台';


DROP TABLE IF EXISTS `ts_makefriends_arenacomment`;
CREATE TABLE `ts_makefriends_arenacomment` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `arenaId` int(11) unsigned NOT NULL default '0' comment '擂台Id',
  `content` VARCHAR( 255 ) NOT NULL default '',
  `cTime` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL default '0'，
   PRIMARY KEY  (`id`),
   KEY `arenaId` (`arenaId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '擂台评论';

DROP TABLE IF EXISTS `ts_makefriends_vote`;
CREATE TABLE `ts_makefriends_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT NULL,
  `arenaId` int(10) unsigned DEFAULT '0',
  `cTime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cTime` (`cTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '投票';

alter table ts_makefriends_arena add `isDel` int(10) unsigned NOT NULL default '0'; 
/*是否时自动匹配*/
alter table ts_makefriends_arena add `is_auto` int(10) unsigned NOT NULL default '0';

-- 秀场
CREATE TABLE `ts_makefriends_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL COMMENT '活动标题',
  `stime` datetime NOT NULL COMMENT '活动开始时间',
  `etime` datetime NOT NULL COMMENT '活动结束时间',
  `rule` text COMMENT '活动规则',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `att_id` int(11) DEFAULT NULL COMMENT '活动封面ID',
  `is_del` tinyint(4) DEFAULT '0' COMMENT '1删除0未删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='Ta秀秀场活动表'