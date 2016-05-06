ALTER TABLE `ts_user`
ADD `school_event_score` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '校方活动积分',
ADD `sid1` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '院',
ADD `sid2` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '系',
ADD `sid3` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '班';

ALTER TABLE `ts_event`
ADD `is_school_event` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '校方活动学校id',
ADD `show_in_xyh` tinyint(1) NOT NULL DEFAULT '1' COMMENT '在校邮汇显示',
ADD `score` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '活动积分';

ALTER TABLE `ts_user`
ADD `school_event_score_used` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '用掉的校方活动积分',
ADD `event_level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1学生10校11院12系13班';

ALTER TABLE `ts_user`
ADD `can_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '校方活动权限修改',
ADD `can_event` tinyint(1) NOT NULL DEFAULT '0' COMMENT '校方活动初级审核权限',
ADD `can_event2` tinyint(1) NOT NULL DEFAULT '0' COMMENT '校方活动终极审核权限',
ADD `can_gift` tinyint(1) NOT NULL DEFAULT '0' COMMENT '校方活动兑换权限',
ADD `can_add_event` tinyint(1) NOT NULL DEFAULT '0' COMMENT '校方活动发布权限';
ADD `can_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '校方活动打印权限';

ALTER TABLE `ts_event_user`
ADD `hasScore` tinyint(1) NOT NULL DEFAULT '0' COMMENT '积分是否已发放';

ALTER TABLE `ts_user`
ADD `event_role_info` varchar(20) NOT NULL DEFAULT '' COMMENT '校方活动权限备注信息';

ALTER TABLE `ts_event`
ADD `audit_uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核人',
ADD `school_audit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1初级审核通过，2终极审核通过, 3提交完结,4完结驳回,5结束';

ALTER TABLE `ts_event`
ADD `need_tel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '报名需电话信息';

ALTER TABLE `ts_event_user`
ADD `tel` varchar(20) NOT NULL DEFAULT '' COMMENT '电话';

ALTER TABLE `ts_event`
ADD `credit` INT( 11 ) unsigned NOT NULL DEFAULT '0' COMMENT '学分';

ALTER TABLE `ts_user`
ADD `school_event_credit` INT( 11 ) unsigned NOT NULL DEFAULT '0' COMMENT '实践学分';

ALTER TABLE `ts_event`
ADD `print_img` varchar(255) NOT NULL DEFAULT '' COMMENT '纪念册照片',
ADD `print_text` varchar(255) NOT NULL DEFAULT '' COMMENT '总结';
ALTER TABLE `ts_event`
ADD `fTime` int(11) COMMENT '申请完结时间';

ALTER TABLE `ts_event_user`
ADD `credit` INT( 11 ) unsigned NOT NULL DEFAULT '0' COMMENT '学分',
ADD `score` INT( 11 ) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
ADD `fTime` int(11) DEFAULT '0' COMMENT '发放积分时间',
ADD `usid` int(11) DEFAULT '0' COMMENT '校邮汇用户的学校id';



DROP TABLE IF EXISTS `ts_school_web`;
CREATE TABLE `ts_school_web` (
  `id` int(11) NOT NULL auto_increment,
  `sid` INT( 11 ) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL,
  `path` varchar(255) NOT NULL default '' comment '首页图片',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_school_web`
ADD `cxjg` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '警告条件',
ADD `cxjy` tinyint(1) unsigned NOT NULL DEFAULT '5' COMMENT '禁言条件',
ADD `cxday` tinyint(1) unsigned NOT NULL DEFAULT '7' COMMENT '禁言天数';

ALTER TABLE `ts_school_web`
ADD `print_title` varchar(255) NOT NULL default '',
ADD `print_content` text;

ALTER TABLE `ts_event_print`
ADD `is_orga` tinyint(1) NOT NULL default 0 COMMENT '0纪念册1官方证书';

ALTER TABLE `ts_school_web`
ADD `print_day` tinyint(1) unsigned NOT NULL default '0',
ADD `print_address` varchar(255) NOT NULL default '' comment '领取地点';

DROP TABLE IF EXISTS `ts_jf`;
CREATE TABLE `ts_jf` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL,
  `path` varchar(255) NOT NULL default '' comment '封面',
  `number` int(11) NOT NULL default '0' comment '数量',
  `content` text,
  `cost` int(11) NOT NULL comment '所需积分',
  `sid` int(11) NOT NULL default '0' comment '学校id',
  `isTop` tinyint(1) NOT NULL default '0' comment '置顶',
  `isHot` tinyint(1) NOT NULL default '0' comment '推荐',
  `isDel` tinyint(1) NOT NULL default '0',
  `cTime` int(11) NOT NULL,
  `uTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_jfdh`;
CREATE TABLE `ts_jfdh` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL comment '兑换者',
  `jfid` int(11) NOT NULL comment '兑换物品',
  `number` int(11) NOT NULL comment '数量',
  `cost` int(11) NOT NULL comment '物品价格',
  `code` varchar(11) NOT NULL comment '编号',
  `sid` int(11) NOT NULL default '0' comment '学校id',
  `isGet` tinyint(1) NOT NULL default '0' comment '是否已领取',
  `cTime` int(11) NOT NULL,
  `uTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_print`;
CREATE TABLE `ts_event_print` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL comment '用户',
  `title` varchar(255) NOT NULL,
  `content` text,
  `eids` text comment '活动ids',
  `sid` int(11) NOT NULL default '0' comment '学校id',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_school_orga`;
CREATE TABLE `ts_school_orga` (
  `id` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL comment '学校id',
  `title` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_fav`;
CREATE TABLE `ts_event_fav` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `fav` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `ts_event_fav` ADD UNIQUE (`uid`);

ALTER TABLE `ts_user`
ADD `realname` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `uname`;
UPDATE `ts_user` SET `realname` = `uname`;

UPDATE `2012xyhui`.`ts_event_type` SET `name` = '文体艺术' WHERE `ts_event_type`.`id` =1;
UPDATE `2012xyhui`.`ts_event_type` SET `name` = '学术创新' WHERE `ts_event_type`.`id` =2;
UPDATE `2012xyhui`.`ts_event_type` SET `name` = '实习创业' WHERE `ts_event_type`.`id` =3;
UPDATE `2012xyhui`.`ts_event` SET `typeId` = '1' WHERE `typeId` =8;
UPDATE `2012xyhui`.`ts_event` SET `typeId` = '2' WHERE `typeId` =9;
UPDATE `2012xyhui`.`ts_event_type` SET `name` = '身心发展' WHERE `ts_event_type`.`id` =8;
UPDATE `2012xyhui`.`ts_event_type` SET `name` = '社会工作' WHERE `ts_event_type`.`id` =9;
UPDATE `2012xyhui`.`ts_event_type` SET `name` = '志愿服务' WHERE `ts_event_type`.`id` =10;
INSERT INTO `2012xyhui`.`ts_event_type` (
`id` ,
`name`
)
VALUES (
'4', '道德修养'
), (
'5', '技能培训'
);

ALTER TABLE `ts_school_orga`
ADD `cat` tinyint(1) NOT NULL default 1;

ALTER TABLE `ts_user`
ADD `cs_orga` INT( 11 ) unsigned NOT NULL DEFAULT '0' COMMENT '初级审核人归属组织';

ALTER TABLE `ts_user`
ADD `can_group` tinyint(1) NOT NULL DEFAULT '0' COMMENT '社团校方认证权限';

ALTER TABLE `ts_event_type` ADD `banner` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'banner数量';
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '3' WHERE `ts_event_type`.`id` =1;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '2' WHERE `ts_event_type`.`id` =2;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '3' WHERE `ts_event_type`.`id` =3;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '3' WHERE `ts_event_type`.`id` =8;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '2' WHERE `ts_event_type`.`id` =9;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '2' WHERE `ts_event_type`.`id` =10;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '2' WHERE `ts_event_type`.`id` =11;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '3' WHERE `ts_event_type`.`id` =4;
UPDATE `2012xyhui`.`ts_event_type` SET `banner` = '3' WHERE `ts_event_type`.`id` =5;

ALTER TABLE `ts_event`
ADD `default_banner` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '默认banner';

DROP TABLE IF EXISTS `ts_event_player`;
CREATE TABLE IF NOT EXISTS `ts_event_player` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `cTime` int(11) NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '照片',
  `realname` varchar(255) NOT NULL,
  `ticket` int(11) NOT NULL DEFAULT '0' COMMENT '投票数',
  `isHot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '推荐',
  `stoped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

ALTER TABLE `ts_user`
ADD `can_prov_event` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发起全省活动权限';
ALTER TABLE `ts_event`
ADD `is_prov_event` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否全省活动';
ALTER TABLE `ts_user`
ADD `can_prov_audit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '推荐优秀活动审核权限';

ALTER TABLE `ts_user` CHANGE `cs_orga` `cs_orga` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '初级审核人归属组织';
UPDATE `2012xyhui`.`ts_school` SET `domain` = 'suda' WHERE `ts_school`.`id` =1;
UPDATE `2012xyhui`.`ts_school` SET `domain` = 'test' WHERE `ts_school`.`id` =473;
UPDATE `2012xyhui`.`ts_school` SET `domain` = 'jyrc' WHERE `ts_school`.`id` =505;
UPDATE `2012xyhui`.`ts_school` SET `domain` = 'njut' WHERE `ts_school`.`id` =480;

ALTER TABLE `ts_weibo_follow` DROP INDEX `uid_fid`;
ALTER TABLE `ts_weibo_follow` ADD UNIQUE `uid_fid` (`uid` ,`fid` ,`type`);
UPDATE `ts_event` SET `default_banner` = '2' WHERE `typeId` =8 AND `default_banner` =3;
UPDATE `ts_event_type` SET `banner` = '2' WHERE `ts_event_type`.`id` =8;

ALTER TABLE `ts_user`
DROP `can_prov_audit`,
ADD `can_prov_news` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发布新闻',
ADD `can_prov_work` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发布作业';

DROP TABLE IF EXISTS `ts_school_news`;
CREATE TABLE `ts_school_news` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL COMMENT '发布人',
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `sid` int(11) unsigned NOT NULL COMMENT '学校',
  `isDel` tinyint(1) NOT NULL default '0',
  `readCount` int(11) unsigned NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_school_work`;
CREATE TABLE `ts_school_work` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL COMMENT '发布人',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` VARCHAR( 250 ) NOT NULL DEFAULT '' COMMENT '简介',
  `sid` int(11) unsigned NOT NULL COMMENT '学校',
  `isDel` tinyint(1) NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  `eTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_school_workback`;
CREATE TABLE `ts_school_workback` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `wid` int(11) unsigned NOT NULL COMMENT '作业id',
  `uid` int(11) unsigned NOT NULL COMMENT '作业人',
  `autor` int(11) unsigned NOT NULL COMMENT '评分人',
  `sid` int(11) unsigned NOT NULL COMMENT '学校',
  `note` tinyint(1) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1' COMMENT '0草稿，1提交未处理2已评分',
  `content` text,
  `attach` text NOT NULL DEFAULT '',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `ts_school_workback` ADD UNIQUE (`wid` ,`uid`);
DROP TABLE IF EXISTS `ts_school_work_attach`;
CREATE TABLE IF NOT EXISTS `ts_school_work_attach` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `attachId` int(11) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `filesize` int(10) NOT NULL DEFAULT '0',
  `filetype` varchar(10) NOT NULL,
  `fileurl` varchar(255) NOT NULL,
  `ctime` int(11) NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

ALTER TABLE `ts_user`
ADD `jy_year_note` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '学期总评分',
ADD INDEX ( `jy_year_note` ) ;

DROP TABLE IF EXISTS `ts_school_year_note`;
CREATE TABLE IF NOT EXISTS `ts_school_year_note` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `note` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

ALTER TABLE `ts_user`
ADD `is_guided` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '引导页';

DROP TABLE IF EXISTS `ts_sj`;
CREATE TABLE `ts_sj` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL COMMENT '申请人',
  `sid` int(11) unsigned NOT NULL COMMENT '学校',
  `sid1` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `title2` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `content` text NOT NULL DEFAULT '',
  `zusatz` text NOT NULL DEFAULT '',
  `reason` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL default '1' COMMENT '1待初核2初审驳回3初审通过，待终审4终审驳回5上线',
  `type` tinyint(1) NOT NULL,
  `attach` text NOT NULL DEFAULT '',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_sj_img`;
CREATE TABLE `ts_sj_img` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sjid` int(11) unsigned NOT NULL,
  `attachId` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL default '1' COMMENT '0普通1封面',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_flash`;
CREATE TABLE `ts_flash` (
  `id` mediumint(5) NOT NULL auto_increment,
  `uid` mediumint(5) NOT NULL default '0',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '靓照',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '视频',
  `title` varchar(255) NOT NULL DEFAULT '',
  `flashvar` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_sj_flash`;
CREATE TABLE `ts_sj_flash` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sjid` int(11) unsigned NOT NULL,
  `flashId` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `ts_credit_user` ADD UNIQUE (`uid`);
ALTER TABLE `ts_weibo_follow_group_link` ADD INDEX ( `follow_id` );

ALTER TABLE `ts_sj` ADD `ticket` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ts_sj` ADD INDEX ( `ticket` );
ALTER TABLE `ts_sj` CHANGE `sid1` `sid1` VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE `ts_event_school` ADD UNIQUE (`eventId` ,`sid`);
ALTER TABLE `ts_user` ADD INDEX ( `email2` );
ALTER TABLE `ts_user` ADD INDEX ( `mobile` );
ALTER TABLE `ts_user` ADD INDEX ( `uname` );
ALTER TABLE `ts_user` ADD INDEX ( `realname` );

DROP TABLE IF EXISTS `ts_sj_vote`;
CREATE TABLE `ts_sj_vote` (
  `id` mediumint(5) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `mid` mediumint(5) NOT NULL default '0' COMMENT '投票用户',
  `pid` mediumint(5) NOT NULL default '0' COMMENT '选手',
  `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_test`;
CREATE TABLE `ts_test` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_event_player`
ADD `school` varchar(50) NOT NULL default '' AFTER `realname`,
ADD `content` text AFTER `school`,
ADD INDEX ( `eventId` );

ALTER TABLE `ts_event`
ADD `maxVote` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '每人最多可投几票';

ALTER TABLE `ts_event`
ADD `audit_uid2` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '终审人';

DROP TABLE IF EXISTS `ts_tj_eday`;
CREATE TABLE `ts_tj_eday` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `sid` int(10) unsigned NOT NULL default '0',
  `credit` smallint(6) unsigned NOT NULL default '0',
  `day` date NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid_day` (`uid`,`day`),
  KEY `uid` (`uid`),
  KEY `day` (`day`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '活动统计日结';

DROP TABLE IF EXISTS `ts_tj_event`;
CREATE TABLE `ts_tj_event` (
  `tj_uid` int(10) unsigned NOT NULL,
  `tj_sid` int(10) unsigned NOT NULL default '0',
  `credit1` MEDIUMINT(8) unsigned NOT NULL default '0',
  `credit2` MEDIUMINT(8) unsigned NOT NULL default '0',
  `credit3` MEDIUMINT(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tj_uid`),
  KEY `credit1` (`credit1`),
  KEY `credit2` (`credit2`),
  KEY `credit3` (`credit3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT = '活动统计';

-- ALTER TABLE `ts_tj_eday` ADD `credit1` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit2` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit3` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit4` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit5` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit8` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit9` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit10` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
-- ALTER TABLE `ts_tj_eday` ADD `credit11` SMALLINT UNSIGNED NOT NULL DEFAULT '0';

DROP TABLE IF EXISTS `ts_event_csorga`;
CREATE TABLE `ts_event_csorga` (
  `uid` int(10) unsigned NOT NULL default '0',
  `orga` INT( 11 ) NOT NULL DEFAULT '0',
  PRIMARY KEY  ( `uid` , `orga` ),
  KEY `uid` (`uid`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '初审人归属组织';

ALTER TABLE `ts_user` DROP `cs_orga`;

ALTER TABLE `ts_event`
ADD `player_upload` tinyint(1) unsigned NOT NULL default '0';

ALTER TABLE `ts_event_player`
ADD `uid` int(11) unsigned NOT NULL default '0' AFTER `eventId`;
ALTER TABLE `ts_event_player`
ADD `sid` int(11) unsigned NOT NULL default '0' AFTER `uid`;
ALTER TABLE `ts_event_player`
ADD `status` tinyint(1) unsigned NOT NULL default '1';
ALTER TABLE `ts_event_player` ADD INDEX ( `status` );

ALTER TABLE `ts_school_web`
ADD `cradit_name` varchar(50) NOT NULL default '实践学分';

ALTER TABLE `ts_event`
ADD `repeated_vote` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '可重复投票';
ALTER TABLE `ts_event`
ADD `allTicket` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '所有票投完才生效';
ALTER TABLE `ts_event_vote`
ADD `status` tinyint(1) unsigned NOT NULL DEFAULT '1';

ALTER TABLE `ts_event_player` ADD `commentCount` int(11) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `ts_user`
ADD `can_credit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '学分认定审核';

DROP TABLE IF EXISTS `ts_ec_type`;
CREATE TABLE `ts_ec_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sid` INT(10) unsigned NOT NULL DEFAULT '0',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `audit` tinyint(1) unsigned NOT NULL default '0',
  `description` text,
  `need_text` tinyint(1) unsigned NOT NULL default '0',
  `img` tinyint(1) unsigned NOT NULL default '0',
  `attach` tinyint(1) unsigned NOT NULL default '0',
  `isDel` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  ( `id`),
  KEY `sid` (`sid`),
  KEY `isDel` (`isDel`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '学分认定类别';

DROP TABLE IF EXISTS `ts_ec_apply`;
CREATE TABLE `ts_ec_apply` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` INT(10) unsigned NOT NULL DEFAULT '0',
  `sid` INT(10) unsigned NOT NULL DEFAULT '0',
  `sid1` INT(10) unsigned NOT NULL DEFAULT '0',
  `uid` INT(10) unsigned NOT NULL DEFAULT '0',
  `realname` varchar(20) NOT NULL DEFAULT '',
  `audit` INT(10) unsigned NOT NULL DEFAULT '0',
  `finish` INT(10) unsigned NOT NULL DEFAULT '0',
  `credit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` text,
  `imgs` text,
  `attachs` text,
  `cTime` int(11) unsigned default NULL,
  `rTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `audit` (`audit`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '学分认定申请';

DROP TABLE IF EXISTS `ts_ec_utype`;
CREATE TABLE `ts_ec_utype` (
  `uid` int(10) unsigned NOT NULL default '0',
  `sid` INT(10) unsigned NOT NULL DEFAULT '0',
  `type` INT( 11 ) NOT NULL DEFAULT '0',
  PRIMARY KEY  ( `uid` , `type` ),
  KEY `uid` (`uid`),
  KEY `sid` (`sid`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '学分认定人类别';

DROP TABLE IF EXISTS `ts_group_announce`;
CREATE TABLE `ts_group_announce` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `gid` INT(10) unsigned NOT NULL DEFAULT '0',
  `uid` INT(10) unsigned NOT NULL DEFAULT '0',
  `realname` varchar(100) NOT NULL DEFAULT '',
  `content` varchar(100) NOT NULL DEFAULT '',
  `ctime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '部落通知公告';

DROP TABLE IF EXISTS `ts_group_feed`;
CREATE TABLE `ts_group_feed` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `gid` INT(10) unsigned NOT NULL DEFAULT '0',
  `uid` INT(10) unsigned NOT NULL DEFAULT '0',
  `content` text,
  `ctime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '部落动态';

ALTER TABLE `ts_event`
ADD `startline` int(11) unsigned NOT NULL DEFAULT '0' AFTER `address` COMMENT '报名开始时间';

DROP TABLE IF EXISTS `ts_tj_gday`;
CREATE TABLE `ts_tj_gday` (
  `gid` int(10) unsigned NOT NULL,
  `credit` smallint(6) NOT NULL default '0',
  `day` date NOT NULL,
  PRIMARY KEY `gid_day` (`gid`,`day`),
  KEY `gid` (`gid`),
  KEY `day` (`day`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '部落统计日结';

ALTER TABLE `ts_group` ADD `activ_num` MEDIUMINT NOT NULL DEFAULT '0' COMMENT  '部落活跃度',ADD INDEX (`activ_num`);

ALTER TABLE `ts_group_member` ENGINE = InnoDB;
ALTER TABLE `ts_group` ENGINE = InnoDB;
ALTER TABLE `ts_group_member` ADD INDEX (`level`);
ALTER TABLE `ts_group` ADD INDEX (`vStern`);

ALTER TABLE `ts_event` ADD `free_attend` tinyint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `ts_school_web` CHANGE `title` `title` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `ts_school_web` CHANGE `print_day` `print_day` tinyint(1) unsigned NOT NULL DEFAULT '7';

ALTER TABLE `ts_ec_utype` ADD `realname` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `ts_sj` ENGINE = InnoDB;
ALTER TABLE `ts_sj_img` ENGINE = InnoDB;
ALTER TABLE `ts_sj_flash` ENGINE = InnoDB;
ALTER TABLE `ts_sj` ADD `year` smallint UNSIGNED NOT NULL DEFAULT '2013';
ALTER TABLE `ts_sj` ADD INDEX ( `year` );

DROP TABLE IF EXISTS `ts_sj_vote_2014`;
CREATE TABLE `ts_sj_vote_2014` (
  `id` mediumint(5) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `mid` mediumint(5) NOT NULL default '0',
  `pid` mediumint(5) NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8;

ALTER TABLE `ts_sj_vote_2014` ADD INDEX ( `mid` );
ALTER TABLE `ts_sj_vote_2014` ADD INDEX ( `eventId` );
/* 0未满，1已满，2处理中，3已处理 */
ALTER TABLE `ts_sj_vote_2014` ADD status tinyint unsigned NOT NULL default '0';
ALTER TABLE `ts_sj_vote_2014` ADD INDEX ( `status` );

ALTER TABLE `ts_event_note` ADD INDEX ( `eventId` );
ALTER TABLE `ts_event` ADD INDEX ( `isDel` );
ALTER TABLE `ts_event` ADD INDEX ( `status` );
ALTER TABLE `ts_event` ADD INDEX ( `is_school_event` );
ALTER TABLE `ts_event` ADD INDEX ( `uid` );
ALTER TABLE `ts_event` ADD INDEX ( `is_prov_event` );
ALTER TABLE `ts_event` ADD INDEX ( `adminCode` );

ALTER TABLE `ts_sj` ADD INDEX ( `type` );
ALTER TABLE `ts_sj` ADD INDEX ( `status` );

ALTER TABLE `ts_announce` ADD INDEX ( `sid` );
ALTER TABLE `ts_announce` ADD INDEX ( `cid` );

DROP TABLE IF EXISTS `ts_ec_applygd`;
CREATE TABLE `ts_ec_applygd` (
  `apply_id` int(10) unsigned NOT NULL,
  `uid` int unsigned NOT NULL,
  `sid` int unsigned NOT NULL,
  `gd` tinyint(1) unsigned NOT NULL,
  `opt` text,
  `has_zs` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `zs_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`apply_id`),
  KEY `uid` (`uid`),
  KEY `has_zs` (`has_zs`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '学分认定申请工大附加';

ALTER TABLE ts_tj_gday DROP PRIMARY KEY;
ALTER TABLE `ts_tj_gday` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `ts_tj_gday` ADD `sid` INT UNSIGNED NOT NULL DEFAULT '0' after `gid`;

CREATE TABLE `ts_tj_event1` (
  `tj_uid` int(10) unsigned NOT NULL,
  `tj_sid` int(10) unsigned NOT NULL DEFAULT '0',
  `credit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`tj_uid`),
  KEY `credit` (`credit`),
  KEY `tj_sid` (`tj_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `ts_tj_event2` (
  `tj_uid` int(10) unsigned NOT NULL,
  `tj_sid` int(10) unsigned NOT NULL DEFAULT '0',
  `credit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`tj_uid`),
  KEY `credit` (`credit`),
  KEY `tj_sid` (`tj_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `ts_tj_event3` (
  `tj_uid` int(10) unsigned NOT NULL,
  `tj_sid` int(10) unsigned NOT NULL DEFAULT '0',
  `credit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`tj_uid`),
  KEY `credit` (`credit`),
  KEY `tj_sid` (`tj_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_event_img` ADD INDEX ( `eventId` );
ALTER TABLE `ts_event_img` ADD INDEX ( `uid` );
ALTER TABLE `ts_school_web` ADD `gift_address` varchar(255) NOT NULL default '';

-- `ts_event` id,uid,title,typeId,sTime,eTime,startline,deadline,joinCount,limitCount,isTop,isHot,isDel,status,sid,cTime,rTime,is_school_event
-- show_in_xyh

CREATE TABLE `ts_event_info` (
  `eventId` int unsigned NOT NULL,
  `contact` varchar(32) NOT NULL COMMENT '联系方式',
  `address` varchar(255) DEFAULT '',
  `cost` char(10) NOT NULL DEFAULT '0',
  `costExplain` varchar(255) DEFAULT '',
`description` varchar(250) NOT NULL COMMENT '活动简介',
`attachId` int(11) NOT NULL DEFAULT '0' COMMENT '附件',
  PRIMARY KEY (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ts_event_join` (
  `eventId` int unsigned NOT NULL,
  `allow` tinyint(1) NOT NULL DEFAULT '0' COMMENT '报名是否需要审核',
  `need_tel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '报名需电话信息',
`player_upload` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '报名活动';
-- 签到
`free_attend` tinyint(3) unsigned NOT NULL DEFAULT '0',

CREATE TABLE `ts_event_voteset` (
  `eventId` int unsigned NOT NULL,
  `isTicket` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否打开投票功能',
  `maxVote` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '每人最多可以投几票',
  `repeated_vote` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '可否重复投票',
  `allTicket` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '票必须全部投完',
  PRIMARY KEY (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '投票设定';

CREATE TABLE `ts_event_note` (
  `eventId` int unsigned NOT NULL,
  `note` float(2,1) NOT NULL DEFAULT '0.0' COMMENT '评分',
  `noteUser` int(11) NOT NULL DEFAULT '0' COMMENT '评分人数',
  PRIMARY KEY (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '评分';

DROP TABLE IF EXISTS `ts_event_finish`;
CREATE TABLE `ts_event_finish` (
  `eventId` int unsigned NOT NULL,
  `print_img` varchar(255) NOT NULL DEFAULT '' COMMENT '纪念册照片',
  `print_text` varchar(255) NOT NULL DEFAULT '' COMMENT '总结',
  `endattach` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '完结附件',
  `pay` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '活动经费',
  PRIMARY KEY (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '完结活动';

ALTER TABLE `ts_event_user` ADD INDEX ( `usid` );
ALTER TABLE `ts_group_feed` ADD `img` varchar(255) NOT NULL DEFAULT '';

DROP TABLE IF EXISTS `ts_event_voteyear`;
CREATE TABLE `ts_event_voteyear` (
  `eid` int unsigned NOT NULL,
  `allowYear` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '允许投票年级';

ALTER TABLE `ts_event_voteyear` ADD `allowSid1` varchar(255) NOT NULL DEFAULT '';

DROP TABLE IF EXISTS `ts_tj_gday`;
CREATE TABLE IF NOT EXISTS `ts_tj_gday` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `credit` decimal(4,1) NOT NULL DEFAULT '0.0',
  `day` date NOT NULL,
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `reason` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1发布活动 2部落公告 3部落帖子 4活动完结 5删除公告帖子',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `reason` (`reason`),
  KEY `day` (`day`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='部落统计日结';

ALTER TABLE ts_group CHANGE `activ_num` `activ_num` decimal(8,1) NOT NULL DEFAULT '0.0';

ALTER TABLE `ts_event_type` AUTO_INCREMENT =101;
ALTER TABLE `ts_event_type` ADD `pid` INT UNSIGNED NOT NULL DEFAULT '0',ADD `sid` INT UNSIGNED NOT NULL DEFAULT '0',
ADD `isdel` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ts_event_type` ADD INDEX ( `sid` );

DROP TABLE IF EXISTS `ts_etype_init`;
CREATE TABLE IF NOT EXISTS `ts_etype_init` (
  `sid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='活动分类自定义生效';

DROP TABLE IF EXISTS `ts_ec_folder`;
CREATE TABLE IF NOT EXISTS `ts_ec_folder` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sid` int unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `pid` int unsigned NOT NULL DEFAULT '0',
  `is_folder` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordernum` int unsigned NOT NULL DEFAULT '0',
  `desc` varchar(255) NOT NULL DEFAULT '',
  `years` varchar(255) NOT NULL DEFAULT '' COMMENT '适用年级12,13,',
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isRelease` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sid_pid` (`sid`,`pid`),
  KEY `ordernum` (`ordernum`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='学分申请自定义';

DROP TABLE IF EXISTS `ts_ec_input`;
CREATE TABLE IF NOT EXISTS `ts_ec_input` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sid` int unsigned NOT NULL,
  `fileId` int unsigned NOT NULL DEFAULT '0' COMMENT 'ts_ec_folder ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1input|2textarea3|select|4attach',
  `inputOrder` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `must` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填',
  `desc` varchar(255) NOT NULL DEFAULT '',
  `opt` text COMMENT '候选项',
  PRIMARY KEY (`id`),
  KEY `fileId` (fileId),
  KEY `inputOrder` (inputOrder)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='表单每一条';

DROP TABLE IF EXISTS `ts_ec_auditor`;
CREATE TABLE IF NOT EXISTS `ts_ec_auditor` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `sid1` int(10) unsigned NOT NULL DEFAULT '0',
  `realname` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学分申请审核人';

DROP TABLE IF EXISTS `ts_ec_identify`;
CREATE TABLE `ts_ec_identify` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fileId` INT(10) unsigned NOT NULL,
  `uid` INT(10) unsigned NOT NULL,
  `realname` varchar(20) NOT NULL,
  `sid` INT(10) unsigned NOT NULL,
  `sid1` INT(10) unsigned NOT NULL DEFAULT '0',
  `year` varchar(10) NOT NULL DEFAULT '',
  `stime` smallint unsigned NOT NULL DEFAULT '0' COMMENT '实践或获奖时间',
  `audit` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '提交时选择的审核人',
  `finish` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '最终审核人',
  `credit` DECIMAL(10,2) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0待审核，1审核通过',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `opt` text COMMENT '表单提交内容',
  `cTime` int(11) unsigned default '0',
  `rTime` int(11) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `audit` (`audit`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT = '学分认定申请';

CREATE TABLE `ts_event_move` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` INT( 11 ) UNSIGNED NOT NULL COMMENT ' 活动分类',
  `is_school_event` int(11) NOT NULL DEFAULT '0' COMMENT '校方活动学校id',
  PRIMARY KEY (`id`),
  KEY `is_school_event` (`is_school_event`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ts_event` CHANGE `typeId` `typeId` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT ' 活动分类';

ALTER TABLE `ts_school_web` ADD `ecName` varchar(255) NOT NULL default '学时';
ALTER TABLE `ts_school_web` ADD `ecRat` varchar(255) NOT NULL default '1';

CREATE TABLE `ts_event_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '标签名称',
  `sid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学校id',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0是未删除，1是已删除',
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `isdel` (`isdel`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '活动标签';

  CREATE TABLE `ts_event_tagcheck` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动id',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签id',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `eid` (`eid`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT '活动标签选择';
﻿/*
DROP TABLE IF EXISTS `ts_event_school2`;
CREATE TABLE `ts_event_school2` (
    `tsid` int(11) unsigned  NOT NULL auto_increment,
    `eventId` int(11) unsigned  NOT NULL ,
    `china` tinyint(1) unsigned NOT NULL default '0' comment '全国',
    `provId` int(11) unsigned NOT NULL default '0' comment '全省',
    `cityId` int(11) unsigned NOT NULL default '0' comment '全市',
    `sid` int(11) unsigned NOT NULL default '0' comment '学校sid',
    PRIMARY KEY  (`tsid`),
    KEY `eventId` (`eventId`),
    KEY `china` (`china`),
    KEY `provId` (`provId`),
    KEY `cityId` (`cityId`),
    KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '活动显示于';
*/
create table ts_event_school2 like ts_event_school;
INSERT INTO ts_event_school2 SELECT * FROM ts_event_school;
alter table ts_event_school2 ADD `china` tinyint(1) unsigned NOT NULL default '0' comment '全国', ADD `provId` int(11) unsigned NOT NULL default '0' comment '全省'
, ADD `cityId` int(11) unsigned NOT NULL default '0' comment '全市', CHANGE `sid`  `sid` int(11) unsigned NOT NULL default '0' comment '学校sid', ADD INDEX ( `china` ),
ADD INDEX ( `provId` ),ADD INDEX ( `cityId` );
alter table ts_event_school2 DROP PRIMARY KEY;
alter table ts_event_school2 ADD `tsid` int(11) unsigned  NOT NULL auto_increment PRIMARY KEY FIRST;
alter table ts_event_school2 ADD INDEX ( `eventId` );
alter table ts_event_school2 ADD INDEX ( `sid` );

CREATE TABLE `ts_kc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` INT( 11 ) UNSIGNED NOT NULL COMMENT ' 活动分类',
  `is_school_event` int(11) NOT NULL DEFAULT '0' COMMENT '校方活动学校id',
  PRIMARY KEY (`id`),
  KEY `is_school_event` (`is_school_event`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- begin 活动默认学分
CREATE TABLE `ts_event_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '活动级别';

CREATE TABLE `ts_event_credit` (
  `typeId` INT( 11 ) UNSIGNED NOT NULL COMMENT ' 活动分类ID',
  `levelId` INT( 11 ) UNSIGNED NOT NULL COMMENT ' 活动级别ID',
  `credit` DECIMAL(10,2) unsigned NOT NULL default '0',
  PRIMARY KEY `typeId_levelId` (`typeId`,`levelId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '活动默认学分';

CREATE TABLE IF NOT EXISTS `ts_event_auto_level` (
  `sid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0禁用，1启用',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='活动默认学分生效';

ALTER TABLE `ts_event` ADD `levelId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动级别';
-- end 活动默认学分
