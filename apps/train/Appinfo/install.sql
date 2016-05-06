DROP TABLE IF EXISTS `ts_train`;
CREATE TABLE `ts_train` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL,
  `title` varchar(50) NOT NULL default '',
  `description` VARCHAR( 500 ) NOT NULL default '' COMMENT '培训简介',
  `address` varchar(255) default '',
  `cost` int(11) unsigned  NOT NULL default '0',
  `contact` varchar(32) default '' comment '联系方式',
  `sTime` int(11) unsigned default NULL,
  `eTime` int(11) unsigned default NULL,
  `deadline` int(11) unsigned NOT NULL comment '截止报名',
  `catId` smallint(11) unsigned NOT NULL default '0' comment '分类',
  `orgId` smallint(11) unsigned NOT NULL default '0' comment '机构',
  `provinceId` smallint(11) unsigned NOT NULL default '0' comment '省份',
  `cityId` smallint(11) unsigned NOT NULL default '0' comment '城市',
  `isTop` tinyint(1) unsigned NOT NULL default '0' comment '置顶',
  `isDel` tinyint(1) unsigned NOT NULL default '0',
  `cTime` int(11) unsigned NOT NULL,
  `rTime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_train_cat`;
CREATE TABLE `ts_train_cat` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_train_org`;
CREATE TABLE `ts_train_org` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `logoId`smallint(11) unsigned NOT NULL default '0' comment '机构logo',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_train_area`;
CREATE TABLE `ts_train_area` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `pid`int(11) unsigned NOT NULL default '0' comment '父id',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE  `ts_train` ADD INDEX (  `catId` );
ALTER TABLE  `ts_train` ADD INDEX (  `provinceId` );
ALTER TABLE  `ts_train` ADD INDEX (  `cityId` );


DROP TABLE IF EXISTS `ts_train_collect`;
CREATE TABLE `ts_train_collect` (
  `id` int(11) NOT NULL auto_increment,
  `courseId` int(11) unsigned NOT NULL,
  `uid`  int(11) unsigned NOT NULL,
 KEY `uid` (`uid`),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDb  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE  `ts_train_org` CHANGE  `logoId`  `logo` VARCHAR( 255 ) NOT NULL DEFAULT  '' COMMENT  '机构logo';
ALTER TABLE  `ts_train` CHANGE  `cost`  `cost` VARCHAR( 255 ) NOT NULL DEFAULT  '';
ALTER TABLE `ts_train` DROP `isDel`;
ALTER TABLE `ts_train_collect` ADD INDEX ( `courseId` ) ;
ALTER TABLE `ts_train_org` ADD `link` VARCHAR( 255 ) NOT NULL DEFAULT '';

ALTER TABLE `ts_train`  DROP `sTime`,DROP `eTime`,DROP `deadline`,ADD `kDate` VARCHAR( 255 ) NOT NULL DEFAULT '',ADD `dauer` VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE `ts_train` ADD `click` int(8) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `ts_train` CHANGE `description` `description` TEXT ;
ALTER TABLE `ts_train_org` ADD `description` TEXT ;
ALTER TABLE `ts_train` ADD `collect` int(8) unsigned NOT NULL DEFAULT 0;

DROP TABLE IF EXISTS `ts_train_collect`;
CREATE TABLE `ts_train_collect` (
  `courseId` int(11) unsigned NOT NULL,
  `uid`  int(11) unsigned NOT NULL,
  PRIMARY KEY (`courseId` ,`uid`)
) ENGINE=InnoDb  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ts_train` ADD `eday` DATE NOT NULL DEFAULT '0000-00-00' comment '签约到期时间';
ALTER TABLE `ts_train` ADD INDEX ( `eday` );
ALTER TABLE `ts_train_org` ADD `eday` DATE NOT NULL DEFAULT '0000-00-00' comment '签约到期时间';

ALTER TABLE `ts_train` ADD `display_order` smallint(6) unsigned NOT NULL default '0',
ADD INDEX ( `display_order` );
update ts_train set `display_order`=`orgId`;