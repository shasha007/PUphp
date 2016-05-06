DROP TABLE IF EXISTS `ts_pocket_category`;
CREATE TABLE `ts_pocket_category` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `name` varchar(255) NOT NULL ,
  `pid` int(11) unsigned NOT NULL default '0',
  `display_order` int unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY (`pid`),
  KEY (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER table `ts_user` ADD `from_reg` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 导入用户 1注册用户';


DROP TABLE IF EXISTS `ts_pocket_goods`;
CREATE TABLE `ts_pocket_goods` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `pic` varchar(255) NOT NULL default '',
  `cid` int(11) unsigned NOT NULL ,
  `name` varchar(255) NOT NULL,
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY (`isDel`),
  KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER table `ts_pocket_goods` ADD `price` decimal(10,2) unsigned NOT NULL DEFAULT '0';
ALTER table `ts_pocket_goods` ADD `ordernum` int(11) unsigned NOT NULL DEFAULT '0';
ALTER table `ts_pocket_goods` ADD `profitId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '利率ID';
ALTER table `ts_pocket_goods` ADD `market` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '市场价';
ALTER table `ts_pocket_goods` ADD `lowest` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '最低月供';
ALTER table `ts_pocket_goods` ADD `num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分期数';
ALTER table `ts_pocket_goods` ADD `lowestShoufu` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '最低首付';
ALTER table `ts_pocket_goods` ADD `isHot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是热卖商品';
ALTER table `ts_pocket_goods` ADD `isPu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是PU卡专属商品';
ALTER table `ts_pocket_goods` ADD `stock` int(11) unsigned NOT NULL DEFAULT '999999' COMMENT '库存';

ALTER table `ts_bank_contract` ADD `ptm` varchar(255) COMMENT '提款协议';
ALTER table `ts_bank_card` ADD `back_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '驳回原因';

DROP TABLE IF EXISTS `ts_staging_goods`;
CREATE TABLE `ts_staging_goods` (
  `id` int(11) unsigned  NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL ,
  `price` decimal(10,2) unsigned NOT NULL,
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY (`gid`),
  KEY (`isDel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_pocket_goods_opt`;
CREATE TABLE `ts_pocket_goods_opt` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned  NOT NULL ,
  `content` text,
  `imgs` text,
  `color` text,
  `desc` text,
  PRIMARY KEY  (`id`),
  KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_pocket_address`;
CREATE TABLE `ts_pocket_address` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned  NOT NULL default '0' ,
  `name` varchar(50) NOT NULL default '',
  `identity` varchar(50) NOT NULL default '' COMMENT '身份证号',
  `address` varchar(255) NOT NULL default '',
  `zipCode` varchar(50) NOT NULL default '' COMMENT '邮编',
  `tel` varchar(50) NOT NULL default '',
  `used` tinyint(1) unsigned  NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_pocket_order`;
CREATE TABLE `ts_pocket_order` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned  NOT NULL ,
  `addressId` int(11) unsigned  NOT NULL ,
  `uid` int(11) unsigned  NOT NULL ,
  `ctime` int(11) unsigned  NOT NULL ,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0待审核 1审核通过,待发货 2审核失败 3还款中 4订单结束 5用户删除',
  PRIMARY KEY  (`id`),
  KEY (`uid`),
  KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER table `ts_pocket_order` ADD `reason` varchar(255) NOT NULL DEFAULT '' COMMENT '驳回原因';
ALTER table `ts_pocket_order` ADD `color` varchar(255) NOT NULL DEFAULT '' COMMENT '商品颜色';
ALTER table `ts_pocket_order` ADD `staging` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '分期数';
ALTER table `ts_pocket_order` ADD `stagPrice` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '每期还款金额';
ALTER table `ts_pocket_order` ADD `shoufu` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '首付';

DROP TABLE IF EXISTS `ts_pocket_pu_order`;
CREATE TABLE `ts_pocket_pu_order` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned  NOT NULL ,
  `addressId` int(11) unsigned  NOT NULL ,
  `uid` int(11) unsigned  NOT NULL ,
  `color` varchar(255) NOT NULL DEFAULT '' COMMENT '商品颜色',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
  `ctime` int(11) unsigned  NOT NULL ,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 审核通过,待发货 1待收货 2已收货 3用户删除',
  PRIMARY KEY  (`id`),
  KEY (`uid`),
  KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_pocket_price`;
CREATE TABLE `ts_pocket_price` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned  NOT NULL default '0' ,
  `price` int(11) unsigned NOT NULL default '0',
  `reasonId` int(11) unsigned NOT NULL default '1' COMMENT '借款原因',
  `staging` int(11) unsigned NOT NULL default '1' COMMENT '分期数',
  `stagPrice` int(11) unsigned NOT NULL default '0' COMMENT '每期还款金额',
  `addressId` int(11) unsigned  NOT NULL ,
  `ctime` int(11) unsigned  NOT NULL ,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0待审核 1审核通过,待放款 2审核失败 3还款中 4结束 5用户删除',
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `reason` varchar(255) NOT NULL default '' COMMENT '驳回原因',
  PRIMARY KEY  (`id`),
  KEY (`uid`),
  KEY (`status`),
  KEY (`isDel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER table `ts_pocket_price` modify `stagPrice` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '每期还款金额';


DROP TABLE IF EXISTS `ts_pocket_reason`;
CREATE TABLE `ts_pocket_reason` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY (`isDel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*--利润空间*/
DROP TABLE IF EXISTS `ts_pocket_profit`;
CREATE TABLE `ts_pocket_profit` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `interest` text COMMENT '每期利率',
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY (`isDel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER table `ts_pocket_profit` modify `interest` text;

<!--广告图-->

DROP TABLE IF EXISTS `ts_pocket_logo`;
CREATE TABLE `ts_pocket_logo` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `pic` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `isShow` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '前台是否显示 1 显示',
  `ordernum` int(11) unsigned NOT NULL DEFAULT '0',
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY (`isShow`),
  KEY (`ordernum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

<!--商品库-->
DROP TABLE IF EXISTS `ts_shop_depot`;
CREATE TABLE `ts_shop_depot` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 删除',
  PRIMARY KEY  (`id`),
  KEY (`isDel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


<!--新表-->
DROP TABLE IF EXISTS `ts_price_order`;
CREATE TABLE `ts_price_order` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_num` varchar(50) NOT NULL default '' COMMENT '订单号',
  `u_id` int(11) unsigned  NOT NULL default '0',
  `ctime` int(11) unsigned  NOT NULL ,
  `price` decimal(10,2) unsigned NOT NULL default '0',
  `staging` int(11) unsigned NOT NULL default '1' COMMENT '分期数',
  `reason` varchar(255) NOT NULL default '' COMMENT '申请原因',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0待审核 1审核通过,待放款 2审核失败',
  PRIMARY KEY  (`id`),
  KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_price_order_detail`;
CREATE TABLE `ts_price_order_detail` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) unsigned  NOT NULL default '0',
  `profitId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '利率ID';
  `stagPrice` decimal(10,2) unsigned NOT NULL default '0' COMMENT '每期还款数',
  `numMonth` int(11) unsigned NOT NULL default '0' COMMENT '还需还款月数',
  `reason` varchar(255) NOT NULL default '' COMMENT '驳回申请原因',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '3还款中 4还款结束 5还款失败',
  PRIMARY KEY  (`id`),
  KEY (`order_id`),
  KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_pocket_user`;
CREATE TABLE `ts_pocket_user` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned  NOT NULL default '0' ,
  `name` varchar(50) NOT NULL default '',
  `identity` varchar(50) NOT NULL default '' COMMENT '身份证号',
  `address` varchar(255) NOT NULL default '',
  `zipCode` varchar(50) NOT NULL default '' COMMENT '邮编',
  `tel` varchar(50) NOT NULL default '',
  `pTel` varchar(50) NOT NULL default '' COMMENT '父母电话',
  `used` tinyint(1) unsigned  NOT NULL default '0',
  `isDel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 删除',
  PRIMARY KEY  (`id`),
  KEY (`used`),
  KEY (`uid`),
  KEY (`isDel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;