DROP TABLE IF EXISTS `ts_coupon`;
CREATE TABLE `ts_coupon` (
  `id` int(10) NOT NULL auto_increment,
  `path` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL default '',
  `content` text,
  `is_hot` tinyint(1) NOT NULL DEFAULT '0',
  `cid` smallint(6) unsigned NOT NULL default '0',
  `sid` smallint(6) unsigned NOT NULL default '0',
  `isDel` tinyint(1) NOT NULL default '0',
  `cTime` int(11) unsigned default NULL,
  `uTime` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_coupon_category`;
CREATE TABLE `ts_coupon_category` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `pid` mediumint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ts_coupon_category` (`id`,`title`) VALUES
(1,'餐饮'),
(2,'住宿'),
(3,'旅游'),
(4,'娱乐');

DROP TABLE IF EXISTS `ts_coupon_school`;
CREATE TABLE `ts_coupon_school` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `pid` mediumint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'coupon','version_number','s:1:"1";','2012-10-22 00:00:00');

ALTER TABLE `ts_coupon` ADD `readCount` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';