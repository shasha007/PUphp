DROP TABLE IF EXISTS `ts_ptx_block`;
CREATE TABLE `ts_ptx_block` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `release` tinyint(1) unsigned NOT NULL default '0' comment '是否已发布',
  `ctime` int unsigned NOT NULL default '0',
  `rtime` int unsigned NOT NULL default '0',
  `isdel` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY (`isdel`),
  KEY (`rtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_ptx_list`;
CREATE TABLE `ts_ptx_list` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `img` varchar(255) NOT NULL default '0',
  `content` text,
  `block_id` int(11) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `ordernum` tinyint(1) unsigned NOT NULL default '0',
  `isbig` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY (`id`),
  KEY (`block_id`),
  KEY (`ordernum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ts_ptx_block` ADD `rnum` INT( 11 ) UNSIGNED NOT NULL default '0' COMMENT '阅读数';
alter table `ts_ptx_block` drop `rnum`;
ALTER TABLE `ts_ptx_list` ADD `rnum` INT( 11 ) UNSIGNED NOT NULL default '0' COMMENT '阅读数';

