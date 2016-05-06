/*
Navicat MySQL Data Transfer
Source Host     : localhost:3306
Source Database : sociax_2_0
Target Host     : localhost:3306
Target Database : sociax_2_0
Date: 2011-01-20 15:16:57
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for ts_event
-- ----------------------------
DROP TABLE IF EXISTS `ts_event`;
CREATE TABLE `ts_event` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `title` text NOT NULL,
  `contact` varchar(32) default '' comment '联系方式',
  `typeId` tinyint(1) NOT NULL comment ' 活动分类',
  `sTime` int(11) default NULL,
  `eTime` int(11) default NULL,
  `address` varchar(255) default '',
  `deadline` int(11) NOT NULL comment '截止报名',
  `joinCount` int(11) NOT NULL default '0',
  `limitCount` int(11) NOT NULL default '0' comment '名额限制',
  `coverId` int(11) NOT NULL default '0' comment '封面附件ts_attach',
  `logoId` int(11) NOT NULL default '0' comment '活动首页图片附件ts_attach',
  `isTop` tinyint(1) NOT NULL default '0' comment '置顶',
  `isHot` tinyint(1) NOT NULL default '0' comment '推荐',
  `isTicket` tinyint(1) NOT NULL default '0' comment '是否打开投票功能',
  `isDel` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0' comment '0 待审核',
  `sid` int(11) NOT NULL default '0' comment '院校',
  `cost` char(10) NOT NULL default '0',
  `costExplain` varchar(255) default '',
  `allow` tinyint(1) NOT NULL default '0' comment '报名是否需要审核',
  `note` float(2,1) NOT NULL default '0' comment '评分',
  `noteUser` int(11) NOT NULL default '0' comment '评分人数',
  `cTime` int(11) NOT NULL,
  `rTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_event`
ADD `attendCode` varchar(20) NOT NULL DEFAULT '' COMMENT '活动二维码';
ALTER TABLE `ts_event`
ADD `adminCode` varchar(20) NOT NULL DEFAULT '' COMMENT '管理员二维码';
ALTER TABLE `ts_event`
ADD `attachId` int(11) NOT NULL default '0' comment '附件';
-- ----------------------------
-- Table structure for ts_event_type
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_type`;
CREATE TABLE `ts_event_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_event_type
-- ----------------------------
INSERT INTO `ts_event_type` VALUES ('1', '音乐/演出');
INSERT INTO `ts_event_type` VALUES ('2', '展览');
INSERT INTO `ts_event_type` VALUES ('3', '电影');
INSERT INTO `ts_event_type` VALUES ('4', '讲座/沙龙');
INSERT INTO `ts_event_type` VALUES ('5', '戏剧/曲艺');
INSERT INTO `ts_event_type` VALUES ('8', '体育');
INSERT INTO `ts_event_type` VALUES ('9', '旅行');
INSERT INTO `ts_event_type` VALUES ('10', '公益');
INSERT INTO `ts_event_type` VALUES ('11', '其它');

-- ----------------------------
-- Table structure for ts_event_user
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_user`;
CREATE TABLE `ts_event_user` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL default '1' comment '0 参加待审核',
  `cTime` int(11) NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '照片',
  `realname` varchar(255) NOT NULL,
  `sex` tinyint(1) NOT NULL default '0' COMMENT '性别（0女1男）',
  `sid` int(11) NOT NULL default '0' comment '院校',
  `ticket` int(11) NOT NULL DEFAULT 0 COMMENT '投票数',
  `isHot` tinyint(1) NOT NULL default '0' comment '推荐',
  `stoped` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_news`;
CREATE TABLE `ts_event_news` (
  `id` int(10) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `isDel` tinyint(1) NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_img`;
CREATE TABLE `ts_event_img` (
  `id` mediumint(5) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `uid` mediumint(5) NOT NULL default '0',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '靓照',
  `title` varchar(255) NOT NULL DEFAULT '',
  `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_flash`;
CREATE TABLE `ts_event_flash` (
  `id` mediumint(5) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `uid` mediumint(5) NOT NULL default '0',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '靓照',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '视频',
  `title` varchar(255) NOT NULL DEFAULT '',
  `flashvar` varchar(255) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_orga`;
CREATE TABLE `ts_event_orga` (
  `id` int(10) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '组织单位',
  `content` text,
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_vote`;
CREATE TABLE `ts_event_vote` (
  `id` mediumint(5) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `mid` mediumint(5) NOT NULL default '0' COMMENT '投票用户',
  `pid` mediumint(5) NOT NULL default '0' COMMENT '选手',
  `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_note`;
CREATE TABLE `ts_event_note` (
  `id` mediumint(5) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `mid` mediumint(5) NOT NULL default '0' COMMENT '评分用户',
  `note` tinyint(1) NOT NULL default '0' COMMENT '分数',
  `cTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_school`;
CREATE TABLE IF NOT EXISTS `ts_event_school` (
  `id` mediumint(5) NOT NULL auto_increment,
  `eventId` int(11) unsigned NOT NULL,
  `sid` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0, 'event', 'limitpage', 's:2:"10";', '2011-01-20 15:19:10'),
    (0, 'event', 'canCreate', 's:1:"1";', '2011-01-20 15:19:10'),
    (0, 'event', 'credit', 's:3:"100";', '2011-01-20 15:19:10'),
    (0, 'event', 'credit_type', 's:10:"experience";', '2011-01-20 15:19:10'),
    (0, 'event', 'limittime', 's:2:"24";', '2011-01-20 15:19:10'),
    (0, 'event', 'createAudit', 's:1:"1";', '2011-01-20 15:19:10');

#模板数据
DELETE FROM `ts_template` WHERE `type` = 'event';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`)
VALUES
    ('event_create_weibo', '发起活动', '','我发起了一个活动：【{title}】{url}', 'zh', 'event', 'weibo', 0, 1290417734),
    ('event_share_weibo', '分享活动', '', '分享@{author} 的活动:【{title}】 {url}', 'zh',  'event', 'weibo', 0, 1290595552);

# 增加默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'event';
INSERT INTO `ts_credit_setting` (`id`,`name`, `alias`, `type`, `info`, `score`, `experience`)
VALUES
    ('', 'add_event', '发起活动', 'event', '{action}{sign}了{score}{typecn}', '10', '10'),
    ('', 'delete_event', '删除活动', 'event', '{action}{sign}了{score}{typecn}', '-10', '-10'),
    ('', 'join_event', '参加活动', 'event', '{action}{sign}了{score}{typecn}', '3', '2'),
    ('', 'cancel_join_event', '取消参加活动', 'event', '{action}{sign}了{score}{typecn}', '-3', '-2');

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'event','version_number','s:5:"36263";','2012-07-12 10:00:00');


ALTER TABLE `ts_event`
ADD `description` VARCHAR( 250 ) NOT NULL COMMENT '活动简介';

DROP TABLE IF EXISTS `ts_event_collection`;
 CREATE TABLE `ts_event_collection` (
`id` INT( 11 ) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` int(11) unsigned NOT NULL ,
`eid` int(11) unsigned NOT NULL ,
`time` int(11) unsigned NOT NULL
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

ALTER TABLE `ts_event_collection` ADD UNIQUE (`uid` ,`eid`);

ALTER TABLE `ts_user` ADD `email2` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `can_group` ;



DROP TABLE IF EXISTS `ts_course`;
CREATE TABLE `ts_course` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `title` text NOT NULL,
    `description` varchar(250) NOT NULL,
  `contact` varchar(32) default '' comment '联系方式',
  `typeId` tinyint(1) NOT NULL comment ' 活动分类',
  `sTime` int(11) default NULL,
  `eTime` int(11) default NULL,
  `address` varchar(255) default '',
  `teacher` varchar(255) default '',
  `deadline` int(11) NOT NULL comment '截止报名',
  `joinCount` int(11) NOT NULL default '0' comment '参加人数',
  `limitCount` int(11) NOT NULL default '0' comment '名额限制',
  `logoId` int(11) NOT NULL default '0' comment '活动首页图片附件ts_attach',
  `isTop` tinyint(1) NOT NULL default '0' comment '置顶',
  `isHot` tinyint(1) NOT NULL default '0' comment '推荐',
  `isDel` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0' comment '0驳回1待审核2通过',
  `sid` int(11) NOT NULL default '0' comment '院校',
  `cost` char(10) NOT NULL default '0',
  `costExplain` varchar(255) default '',
  `audit_uid` int(11) NOT NULL default '0',
  `allow` tinyint(1) NOT NULL default '0' comment '报名是否需要审核',
   `need_tel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '报名需电话信息',
  `credit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学分',
  `cTime` int(11) NOT NULL,
  `rTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_course_active`;
CREATE TABLE `ts_course_active` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `title` text NOT NULL,
    `description` varchar(250) NOT NULL,
  `contact` varchar(32) default '' comment '联系方式',
  `typeId` tinyint(1) NOT NULL comment ' 活动分类',
  `sTime` int(11) default NULL,
  `eTime` int(11) default NULL,
  `address` varchar(255) default '',
  `deadline` int(11) NOT NULL comment '截止报名',
  `joinCount` int(11) NOT NULL default '0' comment '参加人数',
  `limitCount` int(11) NOT NULL default '0' comment '名额限制',
  `logoId` int(11) NOT NULL default '0' comment '活动首页图片附件ts_attach',
  `isTop` tinyint(1) NOT NULL default '0' comment '置顶',
  `isHot` tinyint(1) NOT NULL default '0' comment '推荐',
  `isDel` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0' comment '0 待审核',
  `sid` int(11) NOT NULL default '0' comment '院校',
    `cost` char(10) NOT NULL DEFAULT '0',
  `costExplain` varchar(255) DEFAULT '',
 `audit_uid` int(11) NOT NULL default '0',
  `allow` tinyint(1) NOT NULL default '0' comment '报名是否需要审核',
   `need_tel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '报名需电话信息',
  `credit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学分',
  `cTime` int(11) NOT NULL,
  `rTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ts_course_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ts_course_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `courseId` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `studentId` int(11) DEFAULT '0' COMMENT '学生学号',
  `cTime` int(11) NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '照片',
  `realname` varchar(255) NOT NULL,
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别（0女1男）',
  `sid` int(11) NOT NULL DEFAULT '0' COMMENT '院校',
  `tel` varchar(20) NOT NULL DEFAULT '' COMMENT '电话',
  `credit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学分',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 参加待审核',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `ts_course_img` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `courseId` int(11) NOT NULL,
  `uid` mediumint(5) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '靓照',
  `title` varchar(255) NOT NULL DEFAULT '',
  `cTime` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `ts_course_active_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `courseId` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `studentId` int(11) DEFAULT '0' COMMENT '学生学号',
  `cTime` int(11) NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '照片',
  `realname` varchar(255) NOT NULL,
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别（0女1男）',
  `sid` int(11) NOT NULL DEFAULT '0' COMMENT '院校',
  `tel` varchar(20) NOT NULL DEFAULT '' COMMENT '电话',
  `credit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学分',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 参加待审核',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

ALTER TABLE `ts_course` DROP `cost` ,
DROP `costExplain` ;

ALTER TABLE `ts_user` ADD `course_credit` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程和课程活动总学分' AFTER `jy_year_note` ;
ALTER TABLE  `ts_course_user` CHANGE  `studentId`  `studentId` VARCHAR( 25 ) NULL DEFAULT  '0' COMMENT  '学生学号';
ALTER TABLE  `ts_course_active_user` CHANGE  `studentId`  `studentId` VARCHAR( 25 ) NULL DEFAULT  '0' COMMENT  '学生学号';


CREATE TABLE IF NOT EXISTS  `2012xyhui`.`ts_citys` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`city` VARCHAR( 15 )  NOT NULL ,
`short` VARCHAR( 2 )  NOT NULL ,
 PRIMARY KEY (`id`)
)  ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

ALTER TABLE  `ts_school` ADD  `cityId` INT( 11 ) NOT NULL DEFAULT '0' AFTER  `canRegister`;


ALTER TABLE  `ts_group` ADD  `is_init` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '初始化';

ALTER TABLE `ts_group_topic` ADD `isEvent` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `ts_group_topic` ADD `isRule` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `ts_group` ADD `contact` VARCHAR( 25 ) NOT NULL DEFAULT '';
ALTER TABLE `ts_group` ADD `telephone` VARCHAR( 15 ) NOT NULL NULL DEFAULT '';
ALTER TABLE `ts_group` ADD `email` VARCHAR( 30 ) NOT NULL NULL DEFAULT '';
ALTER TABLE `ts_group` ADD `category` TINYINT( 1 ) NOT NULL NULL DEFAULT '0' COMMENT  '1学生部门 2团支部 3学生社团' ;
ALTER TABLE `ts_group` ADD `sid1` INT( 11 ) NOT NULL DEFAULT '0' ;
ALTER TABLE `ts_group` ADD `year` CHAR( 2 ) NOT NULL DEFAULT '' ;


CREATE TABLE IF NOT EXISTS  `2012xyhui`.`ts_event_group` (
`gid` INT( 15 )  NOT NULL ,
`uid` INT( 15 )  NOT NULL
)  ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
ALTER TABLE `ts_event_group` ADD UNIQUE (`gid` ,`uid`);
ALTER TABLE `ts_event_group` ADD INDEX ( `uid` );

ALTER TABLE `ts_event` ADD `gid` INT( 15 ) NOT NULL DEFAULT '0' ;

ALTER TABLE  `ts_user` ADD  `can_announce` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '校内通知权限' AFTER  `can_group`;

ALTER TABLE  `ts_event_user` ADD  `lot` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '抽签过滤';

ALTER TABLE  `ts_event_user` ADD  `remark` varchar( 15 ) NOT NULL DEFAULT  '' COMMENT  '备注';

ALTER TABLE  `ts_event_user` ADD  `addCredit` smallint( 3 ) NOT NULL DEFAULT  '0' COMMENT  '加分';

ALTER TABLE  `ts_event_user` ADD  `addScore` smallint( 3 ) NOT NULL DEFAULT  '0' COMMENT  '附加积分';

ALTER TABLE `ts_event` CHANGE `title` `title` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;


DROP TABLE IF EXISTS `ts_event_add`;
CREATE TABLE IF NOT EXISTS  `ts_event_add` (
`uid` INT( 11 ) NOT NULL ,
`codelimit` SMALLINT( 3 ) NOT NULL COMMENT  '发起人给出活动签到权限人数',
PRIMARY KEY (`uid`)
)ENGINE=InnoDb  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `ts_event_code`;
CREATE TABLE IF NOT EXISTS  `ts_event_code` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`uid` int( 11 )  NOT NULL ,
`adminCode` varchar(20)  NOT NULL COMMENT  '活动二维码',
 PRIMARY KEY (`id`)
)  ENGINE=InnoDb  DEFAULT CHARSET=utf8 ;
ALTER TABLE `ts_event_code` ADD INDEX ( `uid` , `adminCode` ) ;

ALTER TABLE  `ts_event` ADD  `codelimit` smallint( 3 ) NOT NULL DEFAULT  '5' COMMENT  '活动签到权限人数';
ALTER TABLE  `ts_event` ADD  `pay` DECIMAL(10,2) unsigned NOT NULL default 0.00  COMMENT  '申请完结活动经费';
ALTER TABLE  `ts_citys` ADD  `pid` SMALLINT( 3 ) unsigned NOT NULL DEFAULT  '0';


CREATE TABLE IF NOT EXISTS  `2012xyhui`.`ts_province` (
`id` INT( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
`title` VARCHAR( 15 )  NOT NULL ,
 PRIMARY KEY (`id`)
)  ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

ALTER TABLE  `ts_event_flash` ADD  `show` TINYINT(1) unsigned NOT NULL DEFAULT  '1',ADD INDEX (`show`);

DROP TABLE IF EXISTS `ts_event_parameter`;
CREATE TABLE IF NOT EXISTS  `ts_event_parameter` (
`eventId` INT( 11 ) NOT NULL,
`parameter` text,
`defaultName` text,
 PRIMARY KEY (`eventId`)
)  ENGINE=InnoDb  DEFAULT CHARSET=utf8 ;

ALTER TABLE `ts_event_player` ADD `paramValue` text;
ALTER TABLE `ts_event_player` ADD `isRecomm` tinyint(1) NOT NULL default '0';
ALTER TABLE `ts_event_player` ADD `recommPid` INT( 11 ) unsigned NOT NULL default '0';

DROP TABLE IF EXISTS `ts_event_recomm`;
CREATE TABLE IF NOT EXISTS  `ts_event_recomm` (
`provId` INT( 11 ) NOT NULL,
`eventId` INT( 11 ) NOT NULL,
 PRIMARY KEY (`provId`,`eventId`),
 UNIQUE KEY `eventId` (`eventId`)
)  ENGINE=InnoDb  DEFAULT CHARSET=utf8 ;
ALTER TABLE ts_event_recomm DROP PRIMARY KEY;
ALTER TABLE `ts_event_recomm` ADD `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE `ts_event_img` ADD `upUid` INT( 11 ) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `ts_event_school` DROP `id`, ADD PRIMARY KEY(`eventId` ,`sid`);
ALTER TABLE `ts_event_school` ADD INDEX (`eventId`), ADD INDEX (`sid`);
ALTER TABLE `ts_event_school` ENGINE = InnoDB;
ALTER TABLE `ts_event` ADD `puRecomm` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `ts_event` ADD INDEX (`puRecomm`);


ALTER TABLE  `ts_newcomer_document` ADD  `sid` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `document_id`, ADD INDEX (`sid`);
ALTER TABLE  `ts_newcomer_document` ADD INDEX (`isrecom`),ADD INDEX (`is_active`);

DROP TABLE IF EXISTS `ts_newcomer_logo`;
CREATE TABLE IF NOT EXISTS  `ts_newcomer_logo` (
`sid` INT( 11 ) NOT NULL,
`url` varchar(255) NOT NULL DEFAULT '',
`logo` varchar(255) NOT NULL DEFAULT '' COMMENT '学校logo',
`website`varchar(255) NOT NULL DEFAULT '' COMMENT  '学校官网',
PRIMARY KEY (`sid`)
)  ENGINE=InnoDb  DEFAULT CHARSET=utf8 ;

ALTER TABLE `ts_event` ADD `endattach` INT( 11 ) unsigned NOT NULL default '0' COMMENT '完结附件';
ALTER TABLE `ts_event_orga` DROP `uTime`;
ALTER TABLE `ts_event_orga` DROP `id`;
ALTER TABLE `ts_event_orga` ADD PRIMARY KEY(`eventId`);

ALTER TABLE `ts_event` CHANGE `limitCount` `limitCount` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ts_event` CHANGE `joinCount` `joinCount` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ts_tj_event` ADD INDEX (`tj_sid`);
ALTER TABLE `ts_event` CHANGE `credit` `credit` DECIMAL(10,2) unsigned NOT NULL default '0';
ALTER TABLE `ts_event_user` CHANGE `credit` `credit` DECIMAL(10,2) unsigned NOT NULL default '0';
ALTER TABLE `ts_event_user` CHANGE `addCredit` `addCredit` DECIMAL(10,2) unsigned NOT NULL default '0';
ALTER TABLE `ts_tj_eday` CHANGE `credit` `credit` DECIMAL(10,2) unsigned NOT NULL default '0';
ALTER TABLE `ts_user` CHANGE `school_event_credit` `school_event_credit` DECIMAL(10,2) unsigned NOT NULL default '0';
ALTER TABLE `ts_tj_event` CHANGE `credit1` `credit1` DECIMAL(10,2) unsigned NOT NULL default '0',
CHANGE `credit2` `credit2` DECIMAL(10,2) unsigned NOT NULL default '0',CHANGE `credit3` `credit3` DECIMAL(10,2) unsigned NOT NULL default '0';
ALTER TABLE `ts_ec_apply` CHANGE `credit` `credit` DECIMAL(10,2) unsigned NOT NULL default '0';

ALTER TABLE `ts_school_web` ADD `max_credit` DECIMAL(10,2) unsigned NOT NULL default '10.00',
ADD `max_score` SMALLINT unsigned NOT NULL default '10';


DROP TABLE IF EXISTS `ts_feedback`;
CREATE TABLE IF NOT EXISTS  `ts_feedback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '反馈内容',
  `contact` varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

alter table ts_feedback add fid int unsigned not null default '0',
add rtime int unsigned not null default '0';
ALTER TABLE `ts_feedback` ADD INDEX ( `fid` );

ALTER TABLE `ts_ec_apply` ADD `year` varchar(10) NOT NULL DEFAULT '' after `uid`;
ALTER TABLE `ts_ec_apply` ADD INDEX ( `year` ) ;

ALTER TABLE `ts_event_img` ADD `w` smallint(5) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `ts_event_img` ADD `h` smallint(5) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `ts_ec_apply` ADD `stime` smallint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `ts_tj_gday` ADD `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID';
ALTER TABLE `ts_tj_gday` ADD `eid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID';
ALTER TABLE `ts_tj_gday` ADD `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间';
ALTER TABLE `ts_tj_gday` ADD `reason` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1发布活动 2部落公告 3部落帖子 4活动签到 5活动评论 6活动评分';
ALTER TABLE `ts_tj_gday` CHANGE `credit` `credit` DECIMAL(4, 1)  NOT NULL DEFAULT '0';

