DROP TABLE IF EXISTS `ts_forum`;
CREATE TABLE `ts_forum` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `sid` int(11) unsigned NOT NULL comment '学校id',
  `content` VARCHAR( 255 ) NOT NULL default '',
  `tid` int(11) unsigned NOT NULL default '0' comment '帖子id',
  `rid` int(11) unsigned NOT NULL default '0' comment '评论id',
  `backCount` int(11) unsigned NOT NULL default '0' comment '评论数',
  `isDel` tinyint(1) unsigned NOT NULL default '0',
  `cTime` int(11) unsigned NOT NULL,
  `readCount` int(11) unsigned NOT NULL default '0' comment '阅读数',
   `praiseCount` int(11) unsigned NOT NULL default '0' comment '赞数',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_forum_notice`;
CREATE TABLE `ts_forum_notice` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL default '0' comment '帖子id',
  `rid` int(11) unsigned NOT NULL default '0' comment '评论id',
  `isRead` tinyint(1) unsigned NOT NULL default '0' comment '0未读,1已读',
  `rTime` date  comment '读取时间',
  `cTime` date ,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_forum_notice` ADD `hid` int unsigned NOT NULL DEFAULT '0' AFTER  `rid`;

DROP TABLE IF EXISTS `ts_forum_praise`;
CREATE TABLE `ts_forum_praise` (
  `uid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL comment '帖子id',
  `ctime` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_forum` ADD `readCount` int(11) unsigned NOT NULL default '0' comment '阅读数',
ADD `praiseCount` int(11) unsigned NOT NULL default '0' comment '赞数',
ADD `photoId` int(11) unsigned NOT NULL default '0' comment '帖子图片id';

