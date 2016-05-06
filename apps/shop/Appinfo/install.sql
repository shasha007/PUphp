DROP TABLE IF EXISTS `ts_shop_product`;
CREATE TABLE IF NOT EXISTS `ts_shop_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `need_attended` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '总共需要人数',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `over_times` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `audit` int(10) unsigned NOT NULL DEFAULT '0',
  `isDel` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_shop_product_opt`;
CREATE TABLE IF NOT EXISTS `ts_shop_product_opt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `content` text,
  `imgs` text,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_shop_yg`;
CREATE TABLE IF NOT EXISTS `ts_shop_yg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL COMMENT '商品编码',
  `need_attended` smallint(10) unsigned NOT NULL DEFAULT '0' COMMENT '总共需要人数',
  `has_attended` smallint(10) unsigned NOT NULL DEFAULT '0' COMMENT '目前参与人数',
  `win` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '获奖人',
  `times` smallint(6) unsigned NOT NULL DEFAULT '1' COMMENT '第几期',
  `over_date` datetime NOT NULL COMMENT '商品结束时间',
  `codeRNO` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '中奖云购编号',
  `codeState` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '商品状态1正在进行中3已经开奖',
  `order_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ctime` int(10) unsigned NOT NULL,
  `eday` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `win` (`win`),
  KEY `codeState` (`codeState`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_shop_rno`;
CREATE TABLE IF NOT EXISTS `ts_shop_rno` (
  `rno_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `ygid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rno_id`),
  KEY `ygid` (`ygid`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8  AUTO_INCREMENT=10000001;

DROP TABLE IF EXISTS `ts_consume`;
CREATE TABLE IF NOT EXISTS `ts_consume` (
  `consume_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `logMoney` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总共消费',
  `buyNum` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '购买个数',
  `buyTime` int(10) unsigned NOT NULL,
  `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1一元梦想2众志成城3旅游',
  `product_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`consume_id`),
  KEY `uid` (`uid`),
  KEY `type` (`type`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_buylist`;
CREATE TABLE IF NOT EXISTS `ts_buylist` (
  `buy_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `buyNum` smallint(6) unsigned NOT NULL,
  `buyTime` int(10) unsigned NOT NULL,
  `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1一元梦想2众志成城3旅游',
  `buyIp` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`buy_id`),
  KEY `uid` (`uid`),
  KEY `product_id` (`product_id`),
  KEY `type` (`type`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_order`;
CREATE TABLE IF NOT EXISTS `ts_order` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `order_state` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态0未提交收货地址1等待发货2已发货等待用户收货3等待开团4等待付尾款10交易完成11交易取消',
  `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1一元梦想2众志成城3旅游',
  `buyNum` smallint(6) unsigned NOT NULL DEFAULT '1',
  `vorMoney` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '定金',
  `totalMoney` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '尾款',
  `cday` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `uid` (`uid`),
  KEY `order_state` (`order_state`),
  KEY `cday` (`cday`),
  KEY `type` (`type`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

ALTER TABLE  `ts_order` ADD  `comment` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '0未评1已评';


DROP TABLE IF EXISTS `ts_order_log`;
CREATE TABLE IF NOT EXISTS `ts_order_log` (
  `order_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `oplog` varchar(255) NOT NULL DEFAULT '',
  `opuser` varchar(10) NOT NULL DEFAULT '',
  `optime` datetime NOT NULL,
  PRIMARY KEY (`order_log_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_address`;
CREATE TABLE IF NOT EXISTS `ts_address` (
  `address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `city` varchar(150) NOT NULL DEFAULT '' COMMENT '城市',
  `shipAddress` varchar(150) NOT NULL DEFAULT '' COMMENT '街道地址',
  `shipZip` varchar(20) NOT NULL DEFAULT '',
  `shipName` varchar(10) NOT NULL DEFAULT '' COMMENT '收件人',
  `shipMobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `shipTel` varchar(20) NOT NULL DEFAULT '' COMMENT '固定电话',
  `default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认收货地址',
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`address_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_order_address`;
CREATE TABLE IF NOT EXISTS `ts_order_address` (
  `order_id` int(10) unsigned NOT NULL,
  `city` varchar(150) NOT NULL DEFAULT '' COMMENT '城市',
  `shipAddress` varchar(150) NOT NULL DEFAULT '' COMMENT '街道地址',
  `shipZip` varchar(20) NOT NULL DEFAULT '',
  `shipName` varchar(10) NOT NULL DEFAULT '' COMMENT '收件人',
  `shipMobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `shipTel` varchar(20) NOT NULL DEFAULT '' COMMENT '固定电话',
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_order_transport`;
CREATE TABLE IF NOT EXISTS `ts_order_transport` (
  `order_id` int(10) unsigned NOT NULL,
  `transport_num` varchar(30) NOT NULL DEFAULT '' COMMENT '快递单号',
  `transport_name` varchar(15) NOT NULL DEFAULT '' COMMENT '快递名字',
  `transport_time` datetime NOT NULL COMMENT '发货时间',
  `transport_mark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'shop','version_number','s:1:"1";','2013-10-29 10:00:00');

DROP TABLE IF EXISTS `ts_shop_tg`;
CREATE TABLE IF NOT EXISTS `ts_shop_tg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `pay` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '定金',
  `sprice` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '起拍价',
  `eprice` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '最低价',
  `eprice_attended` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '最低价需要人数',
  `has_attended` MEDIUMINT( 6 ) unsigned NOT NULL DEFAULT '0' COMMENT '参加人数',
  `dec` MEDIUMINT( 6 ) unsigned NOT NULL DEFAULT '0' COMMENT '每增加1人降价多少',
  `cprice` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '当前价格',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `uid` int(10) unsigned NOT NULL,
  `audit` int(10) unsigned NOT NULL DEFAULT '0',
  `codeState` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '商品状态1正在进行中2待审核3已经开奖',
  `isDel` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
  `eday` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `eday` (`eday`),
  KEY `codeState` (`codeState`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

ALTER TABLE `ts_shop_tg`
  DROP `name`,
  DROP `price`,
  DROP `pic`,
  DROP `isDel`;
ALTER TABLE `ts_shop_tg` ADD `tg_times` smallint(6) unsigned  NOT NULL DEFAULT '1' AFTER `id`,
ADD `tgprod_id` INT( 10 ) unsigned NOT NULL AFTER `id` ,
ADD INDEX ( `tgprod_id` );

DROP TABLE IF EXISTS `ts_shop_tgprod`;
CREATE TABLE IF NOT EXISTS `ts_shop_tgprod` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `canActiv` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '可否重新激活',
  `times` smallint(6) unsigned NOT NULL DEFAULT '1' COMMENT '第几期',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_shop_tg_opt`;
CREATE TABLE IF NOT EXISTS `ts_shop_tg_opt` (
  `tg_id` int(10) unsigned NOT NULL,
  `content` text,
  `imgs` text,
  UNIQUE KEY `tg_id` (`tg_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_shop_yd`;
CREATE TABLE IF NOT EXISTS `ts_shop_yd` (
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8;

ALTER TABLE `ts_shop_yg` ADD UNIQUE (
`product_id` ,
`times`
);


CREATE TABLE IF NOT EXISTS `ts_shop_yg_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ygId` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `content` text,
  `cTime` int(12) DEFAULT NULL,
`isDel` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `ts_shop_tg_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tgId` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `content` text,
  `cTime` int(12) DEFAULT NULL,
`isDel` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `ts_donate_product`;
CREATE TABLE IF NOT EXISTS `ts_donate_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
 `price` decimal(10,0) unsigned NOT NULL DEFAULT '0',
`catId` tinyint(2) unsigned NOT NULL default '0',
  `pic` varchar(255) NOT NULL DEFAULT '',
`provinceId` int(11) unsigned NOT NULL default '0',
`cityId` int(11) unsigned NOT NULL default '0',
`sid` int(11) unsigned NOT NULL default '0',
`sid1` int(11) unsigned NOT NULL default '0',
 `contact` varchar(255) NOT NULL DEFAULT '',
 `mobile` varchar(255) NOT NULL DEFAULT '',
 `buyer` int(11) unsigned NOT NULL default '0' COMMENT '买家',
 `buytime` int(11) unsigned NOT NULL default '0' COMMENT '购买时间',
`isDel` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `ts_donate_product_opt`;
CREATE TABLE IF NOT EXISTS `ts_donate_product_opt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `content` text,
  `imgs` text,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_donate_love_fund`;
CREATE TABLE IF NOT EXISTS `ts_donate_love_fund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `uid` int(11) unsigned  NOT NULL  COMMENT '捐物者',
 `fund` decimal(11,0) unsigned NOT NULL  COMMENT '捐款金额',
 `buyer` int(11) unsigned NOT NULL  COMMENT '买家',
 `buytime` int(11) unsigned NOT NULL COMMENT '购买时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE  `ts_donate_product` ADD  `cTime` int(11) unsigned NOT NULL DEFAULT  '0' AFTER  `buytime`;
ALTER TABLE  `ts_donate_product` ADD  `status` TINYINT(1) unsigned NOT NULL DEFAULT  '0' COMMENT  '0待审核1驳回2通过' AFTER  `cTime`;


DROP TABLE IF EXISTS `ts_donate_love_all_fund`;
CREATE TABLE IF NOT EXISTS `ts_donate_love_all_fund` (
 `type` int(10) unsigned NOT NULL ,
 `allfund` decimal(11,0) unsigned NOT NULL   default '0' COMMENT '捐款总金额'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO  `2012xyhui`.`ts_donate_love_all_fund` (
`type` ,
`allfund`
)
VALUES (
'1',  '0'
), (
'2',  '0'
);



DROP TABLE IF EXISTS `ts_donate_cat`;
CREATE TABLE `ts_donate_cat` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE  `ts_donate_product` ADD  `groupId` int(8) unsigned NOT NULL DEFAULT  '0',
ADD `groupName` varchar(32) NOT NULL DEFAULT '';