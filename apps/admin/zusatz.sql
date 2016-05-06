ALTER TABLE `ts_school`
ADD `email` varchar( 20 ) NOT NULL DEFAULT '' COMMENT '默认邮箱后缀';
UPDATE `2012xyhui`.`ts_school` SET `email` = '@mysuda.com' WHERE `ts_school`.`id` =1;
UPDATE `2012xyhui`.`ts_school` SET `email` = '@myusts.com' WHERE `ts_school`.`id` =2;
UPDATE `2012xyhui`.`ts_school` SET `email` = '@myjssvc.com' WHERE `ts_school`.`id` =3;
UPDATE `2012xyhui`.`ts_school` SET `email` = '@mysiit.com' WHERE `ts_school`.`id` =4;

DROP TABLE IF EXISTS `ts_user_mobile`;
CREATE TABLE `ts_user_mobile` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `code` char(4) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '3',
  `cTime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_user`
ADD `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码';

ALTER TABLE `ts_user`
ADD `share_mobile` tinyint(1) NOT NULL DEFAULT '0' COMMENT '公开手机号码';

ALTER TABLE `ts_school`
ADD `canRegister` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可以注册账号';

ALTER TABLE `ts_user`
ADD `year` varchar(6) NOT NULL DEFAULT '' COMMENT '年级',
ADD `major` varchar(10) NOT NULL DEFAULT '' COMMENT '专业';

UPDATE `ts_user` SET `event_level` = 20 WHERE `event_level` = 1;

ALTER TABLE `ts_school` ADD UNIQUE (`title` ,`pid`);

DROP TABLE IF EXISTS `ts_user_privacy`;
CREATE TABLE IF NOT EXISTS `ts_user_privacy` (
  `uid` int(11) NOT NULL,
  `key` varchar(120) NOT NULL,
  `value` varchar(120) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `ts_user_privacy` ADD UNIQUE (`uid` ,`key`);

DROP TABLE IF EXISTS `ts_school_prov`;
CREATE TABLE `ts_school_prov` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `2012xyhui`.`ts_school_prov` (`title`)
VALUES
('北京'), ('上海'),('黑龙江'),('吉林'),('辽宁'),('天津'),('安徽'),('江苏'),('浙江'),('陕西'),('湖北'),('广东'),
('湖南'), ('甘肃'),('四川'),('山东'),('福建'),('河南'),('重庆'),('云南'),('河北'),('江西'),('山西'),('贵州'),
('广西'), ('内蒙古'),('宁夏'),('青海'),('新疆'),('海南'),('西藏'),('香港'),('澳门'),('台湾');

ALTER TABLE `ts_user` CHANGE `major` `major` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '专业';

INSERT INTO `ts_citys` (`city` ,`short`)VALUES ('苏州', 'S'), ('南京', 'N'), ('无锡', 'W'), ('淮安', 'H');
UPDATE `ts_school` SET `cityId` = '1' WHERE `id` IN (1,2,3,4,61,62,283,393,402,472,473,507,517);
UPDATE `ts_school` SET `cityId` = '2' WHERE `id` IN (480,505,524);
UPDATE `ts_school` SET `cityId` = '3' WHERE `id` IN (525,526);
UPDATE `ts_school` SET `cityId` = '4' WHERE `id` IN (527,528,529,530,531);

DROP TABLE IF EXISTS `ts_login_count`;
CREATE TABLE `ts_login_count` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_user` ADD `is_valid` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否验证过手机或邮箱' AFTER `is_init`;
ALTER TABLE `ts_user_mobile` CHANGE `mobile` `mobile` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号码';

UPDATE `ts_school` SET `display_order` = '1' WHERE `id` =532;
UPDATE `ts_school` SET `display_order` = '2' WHERE `id` =533;
UPDATE `ts_school` SET `display_order` = '3' WHERE `id` =534;
UPDATE `ts_school` SET `display_order` = '4' WHERE `id` =535;
UPDATE `ts_school` SET `display_order` = '5' WHERE `id` =543;
UPDATE `ts_school` SET `display_order` = '6' WHERE `id` =536;
UPDATE `ts_school` SET `display_order` = '7' WHERE `id` =537;
UPDATE `ts_school` SET `display_order` = '8' WHERE `id` =545;
UPDATE `ts_school` SET `display_order` = '9' WHERE `id` =540;
UPDATE `ts_school` SET `display_order` = '10' WHERE `id` =541;
UPDATE `ts_school` SET `display_order` = '11' WHERE `id` =539;
UPDATE `ts_school` SET `display_order` = '12' WHERE `id` =538;
UPDATE `ts_school` SET `display_order` = '13' WHERE `id` =546;
UPDATE `ts_school` SET `display_order` = '14' WHERE `id` =542;
UPDATE `ts_school` SET `display_order` = '15' WHERE `id` =544;
UPDATE `ts_school` SET `display_order` = '16' WHERE `id` =547;
UPDATE `ts_school` SET `display_order` = '17' WHERE `id` =549;
UPDATE `ts_school` SET `display_order` = '18' WHERE `id` =548;

ALTER TABLE `ts_user_count`
ADD `following` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `follower` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `weibo` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `atme` `atme` MEDIUMINT( 6 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `comment` `comment` MEDIUMINT( 6 ) UNSIGNED NOT NULL DEFAULT '0';


ALTER TABLE `ts_group` ADD INDEX ( `school` );
ALTER TABLE `ts_event_user` ADD INDEX ( `eventId` );

ALTER TABLE `ts_user_count` ENGINE = InnoDB;
ALTER TABLE `ts_user_online` ENGINE = InnoDB;
ALTER TABLE `ts_login_record` ENGINE = InnoDB;
ALTER TABLE `ts_login_count` ENGINE = InnoDB;
ALTER TABLE `ts_login` ENGINE = InnoDB;
delete FROM `ts_weibo_follow` WHERE uid=1;
delete FROM `ts_weibo_follow` WHERE fid=1;

ALTER TABLE `ts_user`
  DROP `province`,
  DROP `city`,
  DROP `location`;

DROP TABLE IF EXISTS `ts_money`;
CREATE TABLE `ts_money` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `money` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_money_in`;
CREATE TABLE IF NOT EXISTS `ts_money_in` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `typeName` varchar(10) NOT NULL DEFAULT '财付通',
  `logMoney` int(11) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_money_trade`;
CREATE TABLE IF NOT EXISTS `ts_money_trade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `out_trade_no` char(14) NOT NULL DEFAULT '',
  `money` int(11) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_ymq`;
CREATE TABLE `ts_ymq` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `sid` int(11) unsigned NOT NULL,
  `school` varchar(50) NOT NULL,
  `n1` varchar(50) NOT NULL DEFAULT '',
  `n2` varchar(50) NOT NULL DEFAULT '',
  `n3` varchar(50) NOT NULL DEFAULT '',
  `n4` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `school` (`school`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_event_user` ENGINE = InnoDB;
ALTER TABLE `ts_event_player` ENGINE = InnoDB;
ALTER TABLE `ts_event_user` ADD INDEX ( `uid` );
ALTER TABLE `ts_login` ADD INDEX ( `oauth_token` , `oauth_token_secret` ) ;
ALTER TABLE `ts_credit_user` ENGINE = InnoDB;

UPDATE `ts_school` SET `display_order` = '1' WHERE `id` =24;
UPDATE `ts_school` SET `display_order` = '2' WHERE `id` =28;
UPDATE `ts_school` SET `display_order` = '3' WHERE `id` =32;
UPDATE `ts_school` SET `display_order` = '4' WHERE `id` =35;
UPDATE `ts_school` SET `display_order` = '5' WHERE `id` =662;
UPDATE `ts_school` SET `display_order` = '6' WHERE `id` =29;
UPDATE `ts_school` SET `display_order` = '7' WHERE `id` =33;
UPDATE `ts_school` SET `display_order` = '8' WHERE `id` =36;
UPDATE `ts_school` SET `display_order` = '9' WHERE `id` =661;
UPDATE `ts_school` SET `display_order` = '10' WHERE `id` =30;
UPDATE `ts_school` SET `display_order` = '11' WHERE `id` =34;
UPDATE `ts_school` SET `display_order` = '12' WHERE `id` =37;
UPDATE `ts_school` SET `display_order` = '13' WHERE `id` =754;
UPDATE `ts_school` SET `display_order` = '14' WHERE `id` =660;
UPDATE `ts_school` SET `display_order` = '15' WHERE `id` =750;
UPDATE `ts_school` SET `display_order` = '16' WHERE `id` =31;
UPDATE `ts_school` SET `display_order` = '17' WHERE `id` =753;

DROP TABLE IF EXISTS `ts_money_out`;
CREATE TABLE IF NOT EXISTS `ts_money_out` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `out_uid` int(11) unsigned NOT NULL,
  `out_title` varchar(255) NOT NULL DEFAULT '',
  `out_money` int(11) unsigned NOT NULL,
  `out_url` varchar(255) NOT NULL DEFAULT '',
  `out_ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `out_uid` (`out_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_y_user`;
CREATE TABLE IF NOT EXISTS `ts_y_user` (
  `y_uid` int(11) unsigned NOT NULL,
  `y_times` tinyint(1) NOT NULL default '1',
  `day` date NOT NULL,
  PRIMARY KEY (`y_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_y_tj`;
CREATE TABLE IF NOT EXISTS `ts_y_tj` (
  `day` date NOT NULL,
  `times` int(11) unsigned NOT NULL default '0',
  `moneyIn` int(11) unsigned NOT NULL default '0',
  `moneyOut` int(11) unsigned NOT NULL default '0',
  `free_times` int(11) unsigned NOT NULL default '0',
  `free_moneyOut` int(11) unsigned NOT NULL default '0',
  `one_times` int(11) unsigned NOT NULL default '0',
  `one_moneyOut` int(11) unsigned NOT NULL default '0',
  `two_times` int(11) unsigned NOT NULL default '0',
  `two_moneyOut` int(11) unsigned NOT NULL default '0',
  `five_times` int(11) unsigned NOT NULL default '0',
  `five_moneyOut` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (`day`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `ts_y_tj` ADD `users` mediumint unsigned NOT NULL DEFAULT '0',
ADD `free_users` mediumint unsigned NOT NULL DEFAULT '0',
ADD `one_users` mediumint unsigned NOT NULL DEFAULT '0',
ADD `two_users` mediumint unsigned NOT NULL DEFAULT '0',
ADD `five_users` mediumint unsigned NOT NULL DEFAULT '0';

DROP TABLE IF EXISTS `ts_y_lucky`;
CREATE TABLE IF NOT EXISTS `ts_y_lucky` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lucky_uid` int(11) unsigned NOT NULL,
  `y_times` tinyint(1) NOT NULL default '1',
  `type` tinyint(1) NOT NULL default '1',
  `product` int(11) unsigned NOT NULL,
  `day` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_ad`;
CREATE TABLE IF NOT EXISTS `ts_ad` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL ,
   `title` varchar(255) NOT NULL  DEFAULT '',
   `type` tinyint(1) NOT NULL  DEFAULT '0',
  `place` smallint(2) unsigned NOT NULL  DEFAULT '0',
  `areaId` varchar(255)  NOT NULL  DEFAULT '',
  `sid` varchar(255)  NOT NULL  DEFAULT '',
  `year` varchar(255)  NOT NULL  DEFAULT '',
  `coverId` int(11) unsigned NOT NULL  DEFAULT '0',
  `url` varchar(255)  NOT NULL  DEFAULT '',
  `cTime` int(11) unsigned NOT NULL,
  `sTime` int(11) unsigned NOT NULL,
  `eTime` int(11) unsigned NOT NULL,
  `price` double(10,2) unsigned NOT NULL  DEFAULT '0.00',
  `fund` double(10,2) unsigned NOT NULL  DEFAULT '0.00' COMMENT '广告资金库',
  `level` smallint(6) unsigned NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `place` (`place`),
  KEY `sTime` (`sTime`),
  KEY `eTime` (`eTime`),
  KEY `is_del` (`is_del`),
  KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `ts_ad` CHANGE `sid` `sid` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

DROP TABLE IF EXISTS `ts_ad_line`;
CREATE TABLE IF NOT EXISTS `ts_ad_line` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`adId` int(11) unsigned NOT NULL ,
`areaId` int(255) unsigned  NOT NULL,
 `sid` int(11)  unsigned NOT NULL,
 `year` varchar(20)  NOT NULL  DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `adId` (`adId`),
  KEY `areaId` (`areaId`),
  KEY `sid` (`sid`),
  KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_ad_click`;
CREATE TABLE IF NOT EXISTS `ts_ad_click` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`uid` int(11) unsigned NOT NULL ,
`adId` int(11) unsigned NOT NULL ,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_adId` (`uid`,`adId`),
  KEY `uid` (`uid`),
  KEY `adId` (`adId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_event_cx`;
CREATE TABLE IF NOT EXISTS `ts_event_cx` (
 `uid` int(11) unsigned NOT NULL ,
 `total` smallint(6) NOT NULL default '0' COMMENT '总次数',
 `attend` smallint(6) NOT NULL default '0' COMMENT '签到次数',
 `absent` tinyint(1) NOT NULL default '0' COMMENT '临界次数3警告5禁止',
 `status` tinyint(1) NOT NULL default '0' COMMENT '0无1警告2禁止',
 `sday` DATE NOT NULL DEFAULT '0000-00-00',
 `eday` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_money_trade` CHANGE `out_trade_no` `out_trade_no` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

DROP TABLE IF EXISTS `ts_event_cron`;
CREATE TABLE IF NOT EXISTS `ts_event_cron` (
 `event_id` int(11) unsigned NOT NULL ,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT '定时运行诚信';

ALTER TABLE `ts_school_web` DROP `id`,ADD PRIMARY KEY(`sid`);

DROP TABLE IF EXISTS `ts_partner`;
CREATE TABLE IF NOT EXISTS `ts_partner` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `mAppName` varchar(20)  NOT NULL  DEFAULT '',
 `mIconUrl` varchar(255)  NOT NULL  DEFAULT '',
 `mDownloadUrl` varchar(255)  NOT NULL  DEFAULT '',
 `mPkgName` varchar(50)  NOT NULL  DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2012xyhui`.`ts_partner` (
`mAppName` ,
`mIconUrl` ,
`mDownloadUrl` ,
`mPkgName`
)
VALUES
('139邮箱', 'http://pocketuni.net/data/partner/mobile_app_mail.png', 'http://file1.popspace.com/apk/10/064/442/ab3c1b6d-17d1-45a4-8102-dc5d8df89b53.apk', 'cn.cj.pe'
),(
'139出行', 'http://pocketuni.net/data/partner/mobile_app_cx.png', 'http://wap.139sz.cn/cx/download.php?id=1', 'com.android.suzhoumap'
),(
'无线智慧城', 'http://pocketuni.net/data/partner/mobile_app_city.png', 'http://wap.139sz.cn/g3club/3gfly/?a=app&m=down&id=12912', 'com.jscity'
),(
'苏州生活', 'http://pocketuni.net/data/partner/mobile_app_life.png', 'http://wap.139sz.cn/MobileSZSH_V1.apk', 'com.xwtech.szlife'
),(
'139分享', 'http://pocketuni.net/data/partner/mobile_app_share.png', 'http://wap.139sz.cn/g3club/3gfly/?a=app&m=down&id=75654', 'com.diypda.g3downmarket'
),(
'农家乐', 'http://pocketuni.net/data/partner/mobile_app_njl.png', 'http://wap.139sz.cn/g3club/3gfly/?a=app&m=down&id=65105', 'com.szmobile.sznjl'
),(
'139答应', 'http://pocketuni.net/data/partner/mobile_app_dy.png', 'http://www.apk.anzhi.com/data1/apk/201309/16/com.cplatform.xqw_14035600.apk', 'com.cplatform.xqw'
);

DROP TABLE IF EXISTS `ts_tg_login`;
CREATE TABLE IF NOT EXISTS `ts_tg_login` (
 `uid` int(11) unsigned NOT NULL ,
 `day` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`uid`,`day`),
  KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE ts_message_content DROP INDEX list_id;
ALTER TABLE ts_message_content DROP INDEX list_id_2;
ALTER TABLE `ts_message_content` ADD INDEX ( `list_id` );
ALTER TABLE `ts_message_content` ADD INDEX ( `from_uid` );
ALTER TABLE `ts_message_content` ENGINE = InnoDB;

ALTER TABLE ts_message_member DROP INDEX ctime;
ALTER TABLE ts_message_member DROP INDEX list_ctime;
ALTER TABLE `ts_message_member` ADD INDEX ( `member_uid` );
ALTER TABLE `ts_message_member` ENGINE = InnoDB;

ALTER TABLE `ts_message_list` ENGINE = InnoDB;

ALTER TABLE `ts_user` CHANGE `uid` `uid` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `ts_money_in` ADD `desc` VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE `ts_money_in` ADD INDEX ( `typeName` );

DROP TABLE IF EXISTS `ts_money_ccb`;
CREATE TABLE `ts_money_ccb` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `pay` DECIMAL(10,2) unsigned NOT NULL,
  `gift` DECIMAL(10,2) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_school` CHANGE `display_order` `display_order` VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE `ts_school` ADD INDEX ( `display_order` );

DROP TABLE IF EXISTS `ts_weibo_time`;
CREATE TABLE `ts_weibo_time` (
  `uid` int(11) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_partner` ADD `type` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE  `ts_ad` CHANGE  `type`  `type` TINYINT( 3 ) NOT NULL DEFAULT  '0'

ALTER TABLE `ts_weibo` ADD `heart` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ts_weibo_favorite` ENGINE = InnoDB;
DROP TABLE IF EXISTS `ts_weibo_heart`;
CREATE TABLE IF NOT EXISTS `ts_weibo_heart` (
  `uid` int(11) NOT NULL,
  `weibo_id` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`weibo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_user` ADD INDEX ( `sid1` );
ALTER TABLE `ts_user` ENGINE = InnoDB;
ALTER TABLE `ts_user_profile` ENGINE = InnoDB;

DROP TABLE IF EXISTS `ts_weibo_time`;
CREATE TABLE `ts_weibo_time` (
  `uid` int(11) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `ts_user`
ADD `clientType` tinyint(1) NOT NULL DEFAULT '0' COMMENT '客户端类型1为android,2为ios';

DROP TABLE IF EXISTS `ts_ly_lucky`;
CREATE TABLE IF NOT EXISTS `ts_ly_lucky` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_ly_day`;
CREATE TABLE IF NOT EXISTS `ts_ly_day` (
  `uid` int(11) unsigned NOT NULL,
  `day` date NOT NULL,
  PRIMARY KEY (`uid`,`day`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE ts_user_profile ADD INDEX ( `uid` );
ALTER TABLE ts_event_vote ADD INDEX ( `eventId` );
ALTER TABLE ts_event_vote ADD INDEX ( `mid` );
ALTER TABLE ts_user ADD INDEX ( `is_valid` );
ALTER TABLE ts_user_mobile ADD INDEX ( `uid` );
ALTER TABLE ts_weibo ADD INDEX ( `isdel` );
ALTER TABLE `ts_event_vote` ENGINE = InnoDB;

ALTER TABLE `ts_photo_album` CHANGE `privacy_data` `privacy_data` VARCHAR( 50 ) NOT NULL DEFAULT '';
ALTER TABLE `ts_photo_album` ENGINE = InnoDB;

ALTER TABLE `ts_photo_album` CHANGE `coverImageId` `coverImageId` INT( 11 ) unsigned NOT NULL DEFAULT '0',
CHANGE `coverImagePath` `coverImagePath` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `ts_user` ADD `sign` varchar(100) NOT NULL DEFAULT '' COMMENT '签名';

DROP TABLE IF EXISTS `ts_cron_jpush`;
CREATE TABLE IF NOT EXISTS `ts_cron_jpush` (
  `uid` int(11) unsigned NOT NULL,
  `day` date NOT NULL,
  PRIMARY KEY (`uid`,`day`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_lucky_product`;
CREATE TABLE IF NOT EXISTS `ts_lucky_product` (
  `id` int unsigned NOT NULL auto_increment,
  `name` varchar(100)  NOT NULL  DEFAULT '',
  `description` varchar(255)  NOT NULL  DEFAULT '',
  `content` text,
  `num` mediumint unsigned NOT NULL,
  `usedNum` mediumint unsigned NOT NULL DEFAULT '0',
  `type` tinyint unsigned NOT NULL,
  `ygid` int unsigned NOT NULL DEFAULT '0',
  `pay` DECIMAL(10,2) unsigned NOT NULL DEFAULT '0',
  `stime` int unsigned  NOT NULL DEFAULT '0',
  `etime` int unsigned  NOT NULL DEFAULT '3123456789' COMMENT '代金券使用有效期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_lucky_yy`;
CREATE TABLE IF NOT EXISTS `ts_lucky_yy` (
  `pid` int unsigned NOT NULL,
  `prozent` mediumint unsigned NOT NULL,
  `cost0` tinyint unsigned NOT NULL DEFAULT '0',
  `cost1` tinyint unsigned NOT NULL DEFAULT '0',
  `cost2` tinyint unsigned NOT NULL DEFAULT '0',
  `cost5` tinyint unsigned NOT NULL DEFAULT '0',
  `ctime` int unsigned  NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `cost0` (`cost0`),
  KEY `cost1` (`cost1`),
  KEY `cost2` (`cost2`),
  KEY `cost5` (`cost5`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_lucky_zj`;
CREATE TABLE IF NOT EXISTS `ts_lucky_zj` (
  `id` int unsigned NOT NULL auto_increment,
  `uid` int unsigned NOT NULL,
  `pid` int unsigned NOT NULL,
  `order_id` int unsigned NOT NULL DEFAULT '0',
  `type` tinyint unsigned NOT NULL,
  `name` varchar(100)  NOT NULL  DEFAULT '',
  `zjfrom` varchar(100)  NOT NULL  DEFAULT '',
  `used` tinyint unsigned NOT NULL DEFAULT '0',
  `ctime` int unsigned  NOT NULL,
  `utime` int unsigned  NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`),
  KEY `type` (`type`),
  KEY `used` (`used`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `ts_message_member` ADD INDEX ( `list_ctime` );

DROP TABLE IF EXISTS `ts_jpush`;
CREATE TABLE IF NOT EXISTS `ts_jpush` (
  `id` int unsigned NOT NULL auto_increment,
  `type` tinyint unsigned NOT NULL,
  `to_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  UNIQUE KEY `type_to_id` (`type`,`to_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;




DROP TABLE IF EXISTS `ts_ad_record`;
CREATE TABLE IF NOT EXISTS `ts_ad_record` (
`id` int unsigned NOT NULL auto_increment,
`uid` int(11) unsigned NOT NULL ,
`sid` int(11) unsigned NOT NULL ,
`adId` int(11) unsigned NOT NULL ,
`ctime` int(11) unsigned  NOT NULL,
 `from` tinyint(1)  NOT NULL  DEFAULT '0' COMMENT '1android,2ios,3网站',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `ts_ad` ADD  `count` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `level` ;
ALTER TABLE  `ts_ad` ADD  `jump` tinyint UNSIGNED NOT NULL DEFAULT  '0'  COMMENT '客户端广告跳转';

DROP TABLE IF EXISTS `ts_user_si`;
CREATE TABLE IF NOT EXISTS `ts_user_si` (
`uid` int unsigned NOT NULL ,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_user_gz`;
CREATE TABLE IF NOT EXISTS `ts_user_gz` (
`uid` int unsigned NOT NULL ,
`gzid` int unsigned NOT NULL ,
  PRIMARY KEY (`uid`,`gzid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_user_avatar`;
CREATE TABLE IF NOT EXISTS `ts_user_avatar` (
`uid` int unsigned NOT NULL ,
`tag` tinyint unsigned NOT NULL default '0' ,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_ymq`;
CREATE TABLE `ts_ymq` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `sid` int(11) unsigned NOT NULL,
  `school` varchar(50) NOT NULL,
  `n1` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2012xyhui`.`ts_ymq` (`sid` ,`school` ,`n1`)
VALUES ('526', '江南大学', 'D2'), ('636', '扬州大学', 'B2'), ('626', '常州大学', 'E1'), ('594', '南京农业大学', 'E2'), ('597', '南京审计学院', 'D1')
, ('586', '南京师范大学', 'A1'), ('480', '南京工业大学', 'B1'), ('1', '苏州大学', 'A2'), ('528', '淮阴师范学院', 'C1'), ('551', '江苏大学', 'C2');

ALTER TABLE `ts_money` ADD `ctime` int(11) unsigned NOT NULL DEFAULT '0',ADD INDEX ( `ctime` );

DROP TABLE IF EXISTS `ts_cron_credit`;
CREATE TABLE IF NOT EXISTS `ts_cron_credit` (
 `eid` int(11) unsigned NOT NULL ,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT '定时计算学分积分';

DROP TABLE IF EXISTS `ts_cron_gd`;
CREATE TABLE `ts_cron_gd` (
  `uid` int unsigned NOT NULL,
  `credit` DECIMAL(10,2) unsigned NOT NULL,
  `ctime` int unsigned NOT NULL,
  PRIMARY KEY  (`uid`,`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_denounce`;
CREATE TABLE `ts_denounce` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `from` varchar(255) NOT NULL COMMENT '目前存入各个应用的名称，比如blog,weibo，说明举报的是不同应用下的内容',
  `aid` int unsigned NOT NULL COMMENT '记录内容表的主键ID',
  `state` tinyint(4) NOT NULL DEFAULT 0 COMMENT '记录状态，0，默认，表示刚举报；1，表示已删除；2，表示已经通过可以正常显示；',
  `uid` int unsigned NOT NULL COMMENT '记录举报人的UID',
  `fuid` int unsigned NOT NULL COMMENT '记录被举报人UID',
  `reason` text NOT NULL COMMENT '举报理由',
  `content` text NOT NULL,
  `ctime` int unsigned NOT NULL COMMENT '记录举报的时间',
  PRIMARY KEY (`id`),
  KEY (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_cron_cxlist`;
CREATE TABLE `ts_cron_cxlist` (
  `uid` int unsigned NOT NULL,
  `eventId` int unsigned NOT NULL,
  `isAttend` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `day` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY  (`uid`,`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_order_7881`;
CREATE TABLE `ts_order_7881` (
  `order_id` varchar(50) NOT NULL,
  `uid` int unsigned NOT NULL,
  `price` DECIMAL(10,2) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `account` varchar(50) NOT NULL,
  `number` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `ctime` int unsigned NOT NULL,
  `ptime` int unsigned NOT NULL default '0',
  `rtime` int unsigned NOT NULL,
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_user_follow`;
CREATE TABLE `ts_user_follow` (
  `uid` int unsigned NOT NULL,
  `follow_id` int unsigned NOT NULL,
  `is_friend` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `group_id` int unsigned NOT NULL DEFAULT 0,
  `ctime` int unsigned NOT NULL,
  `rtime` int unsigned NOT NULL DEFAULT 0,
  KEY  (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_user_fans`;
CREATE TABLE `ts_user_fans` (
  `uid` int unsigned NOT NULL,
  `fan_id` int unsigned NOT NULL,
  `is_friend` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `ctime` int unsigned NOT NULL,
  `rtime` int unsigned NOT NULL DEFAULT 0,
  KEY  (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE ts_weibo_follow DROP INDEX uid_fid;
ALTER TABLE `ts_weibo_follow` CHANGE `follow_id` `ctime` INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `ts_weibo_follow` ADD `group_id` INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ts_weibo_follow DROP PRIMARY KEY;
ALTER TABLE `ts_weibo_follow` DROP INDEX `uid`;
ALTER TABLE `ts_weibo_follow` DROP INDEX `fid`;
ALTER TABLE `ts_weibo_follow` ADD INDEX ( `uid` );
ALTER TABLE `ts_weibo_follow` ADD INDEX ( `fid` );

Alter table ts_weibo_follow partition by hash(uid) partitions 10;

DROP TABLE IF EXISTS `ts_y_record`;
CREATE TABLE `ts_y_record` (
  `uid` int unsigned NOT NULL,
  `sid` int unsigned NOT NULL DEFAULT 0,
  `sid1` int unsigned NOT NULL DEFAULT 0,
  `year` int unsigned NOT NULL DEFAULT 0,
  `cost` smallint unsigned NOT NULL DEFAULT 0,
  `win` smallint unsigned NOT NULL DEFAULT 0,
  `day` date NOT NULL,
  KEY  (`uid`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `ts_lucky_product` ADD  `rtime` INT UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE `ts_lucky_product` ADD INDEX ( `ygid` );
ALTER TABLE  `ts_lucky_yy` ADD  `canRepeat` tinyint(1) UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `ts_lucky_yy` ADD  `dayNum` mediumint UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE ts_lucky_zj DROP INDEX `type`;
ALTER TABLE ts_lucky_zj DROP INDEX `used`;
ALTER TABLE ts_lucky_zj ADD  `day` date NOT NULL;

ALTER TABLE  `ts_weibo` ADD  `sid` INT UNSIGNED NOT NULL DEFAULT  '0' after uid;
ALTER TABLE `ts_weibo` ADD INDEX ( `sid` );
ALTER TABLE ts_weibo DROP INDEX `type`;
ALTER TABLE ts_weibo DROP INDEX `transpond`;
ALTER TABLE ts_weibo DROP INDEX `uid_2`;
ALTER TABLE `ts_weibo` ADD INDEX ( `uid` );

DROP TABLE IF EXISTS `ts_app_error`;
CREATE TABLE `ts_app_error` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0',
  `from` varchar(50) NOT NULL DEFAULT '' comment 'android,ios',
  `handy` varchar(255) NOT NULL DEFAULT '' comment '手机型号',
  `system` varchar(255) NOT NULL DEFAULT '' comment '系统版本',
  `pixels` varchar(255) NOT NULL DEFAULT '' comment '屏幕分辨率大小',
  `pu_version` varchar(255) NOT NULL DEFAULT '' comment 'PU版本号',
  `log_file` varchar(255) NOT NULL DEFAULT '' comment '错误日志文件',
  `ctime` int unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY  (`from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ts_tj_eventcount` (
  `mon` char(7) NOT NULL,
  `sid` int unsigned NOT NULL,
  `year` varchar(10) NOT NULL default '',
  `event_count` mediumint unsigned NOT NULL default '0',
  `finish_count` mediumint unsigned NOT NULL default '0',
  `join_count` mediumint unsigned NOT NULL default '0',
  `attent_count` mediumint unsigned NOT NULL default '0',
  KEY  (`mon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_user_reg`;
CREATE TABLE `ts_user_reg` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' comment '0未处理，1通过，2驳回',
  `sid` int unsigned NOT NULL DEFAULT '0',
  `number` varchar(255) NOT NULL DEFAULT '' comment '学号',
  `realname` varchar(50) NOT NULL DEFAULT '',
  `yuanxi` int unsigned NOT NULL DEFAULT '0',
  `major` varchar(255) NOT NULL DEFAULT '' comment '专业',
  `year` varchar(10) NOT NULL DEFAULT '' comment '年级',
  `email` varchar(255) NOT NULL DEFAULT '' comment '密保邮箱',
  `zj_file` varchar(255) NOT NULL DEFAULT '' comment '学生证',
  `audit` int unsigned NOT NULL DEFAULT '0' comment '处理人',
  `uid` int unsigned NOT NULL DEFAULT '0' comment '建立后用户ID',
  `mail_send` tinyint(1) unsigned NOT NULL DEFAULT '0' comment '0未发送，1已发送',
  `ctime` int unsigned NOT NULL DEFAULT '0',
  `rtime` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '用户注册';

DROP TABLE IF EXISTS `ts_attach_reg`;
CREATE TABLE IF NOT EXISTS `ts_attach_reg` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `attach_type` varchar(50) NOT NULL DEFAULT 'register',
  `userId` int unsigned DEFAULT '0',
  `uploadTime` int unsigned DEFAULT NULL,
  `isDel` tinyint(1) unsigned DEFAULT '0',
  `savepath` varchar(255) DEFAULT '',
  `savename` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '用户学生证';

ALTER TABLE `ts_user` CHANGE `is_active` `is_active` TINYINT(1) NOT NULL DEFAULT '1';

DROP TABLE IF EXISTS `ts_user_gag`;
CREATE TABLE IF NOT EXISTS `ts_user_gag` (
  `uid` int unsigned DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' comment '1吐泡泡',
  `ctime` int unsigned DEFAULT '0',
  PRIMARY KEY (`uid`,`type`),
  KEY  (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '用户禁言';

DROP TABLE IF EXISTS `ts_user_tg`;
CREATE TABLE IF NOT EXISTS `ts_user_tg` (
  `uid` int unsigned DEFAULT '0',
  `sid` int unsigned DEFAULT '0',
  `school` varchar(255) DEFAULT '',
  `realname` varchar(255) DEFAULT '',
  `email` varchar(255) DEFAULT '',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '测试人员';


ALTER TABLE  `ts_school` ADD  `tj_year` tinyint(1) UNSIGNED NOT NULL DEFAULT  '4';

DROP TABLE IF EXISTS `ts_tj_rh`;
CREATE TABLE IF NOT EXISTS `ts_tj_rh` (
  `day` date NOT NULL,
  `all_user` int unsigned NOT NULL,
  `tj_user` int unsigned NOT NULL,
  `all_init` int unsigned NOT NULL,
  `tj_init` int unsigned NOT NULL,
  `all_dlogin` int unsigned NOT NULL,
  `tj_dlogin` int unsigned NOT NULL,
  `all_mlogin` int unsigned NOT NULL,
  `tj_mlogin` int unsigned NOT NULL,
  PRIMARY KEY (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '日统计';

ALTER TABLE  `ts_user_reg` ADD  `mobile` varchar(20) NOT NULL DEFAULT '';
ALTER TABLE  `ts_user_reg` ADD  `sms_send` tinyint(1) unsigned NOT NULL DEFAULT '0' comment '0未发送，1已发送';
ALTER TABLE  `ts_user_reg` ADD  `password` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE  `ts_user_reg` ADD  `reason` varchar(250) NOT NULL DEFAULT '';

DROP TABLE IF EXISTS `ts_nv`;
CREATE TABLE IF NOT EXISTS `ts_nv` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `eid` int unsigned NOT NULL,
  `pid` int unsigned NOT NULL,
  `realname` varchar(255) NOT NULL default '',
  `ziel` int unsigned NOT NULL,
  `has` int unsigned NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '女';

ALTER TABLE `ts_citys` CHANGE `short` `short` varchar(5) NOT NULL default '';
ALTER TABLE `ts_citys` ADD INDEX ( `short` );
ALTER TABLE `ts_province` ADD `short` varchar(5) NOT NULL default '';
ALTER TABLE `ts_province` ADD INDEX ( `short` );

ALTER TABLE  `ts_school` ADD  `provinceId` int(11) unsigned NOT NULL DEFAULT '0' comment '省ID';
ALTER TABLE `ts_user` ADD `from_reg` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '导入还是注册用户';
ALTER TABLE `ts_school` ADD `isCjdV2` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否第二课堂成绩单自定义';

DROP TABLE IF EXISTS `ts_user_a`;
CREATE TABLE IF NOT EXISTS `ts_user_a` (
  `uid` int(11) unsigned NOT NULL,
  `class` varchar(100) NOT NULL default '' comment '班级',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '用户信息';

/*添加奖品奖券需审核*/
ALTER TABLE  `ts_lucky_product` ADD  `status` tinyint UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE `ts_lucky_product` ADD INDEX ( `status` );
update ts_lucky_product set status=1;

ALTER TABLE `ts_user_reg` ADD INDEX ( `uid` );

CREATE TABLE IF NOT EXISTS `ts_cardapi_error` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `err_msg` varchar(255) NOT NULL default '',
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment 'cardApi调用错误信息';