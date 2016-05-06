DROP TABLE IF EXISTS `ts_fundgroup_sponsor`;
CREATE TABLE `ts_fundgroup_sponsor` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `company` VARCHAR( 255 ) NOT NULL default '' comment '公司名',
    `type` VARCHAR( 255 ) NOT NULL default '' comment '投资类型',
    `money` VARCHAR( 255 ) NOT NULL default '' comment '投资金额',
    `stuff` VARCHAR( 255 ) NOT NULL default '' comment '所需材料',
    `win` VARCHAR( 255 ) NOT NULL default '' comment '投资收益要求',
    `month` VARCHAR( 255 ) NOT NULL default '' comment '投资期限',
    `content` text comment '详情须知',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '添加时间',
    `attachId` int(11) unsigned NOT NULL  default '0' comment '附件id',
    `is_activ` tinyint(1) unsigned NOT NULL default '0' comment '是否上架',
     PRIMARY KEY  (`id`),
     KEY `is_activ` (`is_activ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '投资基金企业';

DROP TABLE IF EXISTS `ts_fundgroup_sponsor_school`;
CREATE TABLE `ts_fundgroup_sponsor_school` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `sponsorId` int(11) unsigned  NOT NULL ,
    `china` tinyint(1) unsigned NOT NULL default '0' comment '全国',
    `provId` int(11) unsigned NOT NULL default '0' comment '全省',
    `cityId` int(11) unsigned NOT NULL default '0' comment '全市',
    `sid` int(11) unsigned NOT NULL default '0' comment '学校sid',
    PRIMARY KEY  (`id`),
    KEY `sponsorId` (`sponsorId`),
    KEY `china` (`china`),
    KEY `provId` (`provId`),
    KEY `cityId` (`cityId`),
    KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '投资基金企业显示于';

DROP TABLE IF EXISTS `ts_fundgroup_cyapply`;
CREATE TABLE `ts_fundgroup_cyapply` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `title` VARCHAR( 255 ) NOT NULL default '' comment '项目名称',
    `gid` int(11) unsigned NOT NULL  default '0' comment '申请部落',
    `uid` int(11) unsigned NOT NULL  default '0' comment '申请人',
    `mobile` VARCHAR( 255 ) NOT NULL default '' comment '负责人联系方式',
    `partner` text comment '合伙人姓名',
    `partnerContact` text comment '合伙人联系方式',
    `needMoney` DECIMAL(10,2)  unsigned  NOT NULL default '0' comment '需求资金',
    `getMoney` DECIMAL(10,2)  unsigned  NOT NULL default '0' comment '实际发放资金',
    `period` int(11) unsigned NOT NULL  default '0' comment '资金使用周期',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '添加时间',
    `attachId` int(11) unsigned NOT NULL  default '0' comment '附件id',
    `status` tinyint(1) NOT NULL default '0' comment '状态0待审核，1通过，2发放，-1驳回',
     PRIMARY KEY  (`id`),
     KEY `gid` (`gid`),
     KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '创业基金申请';

ALTER TABLE `ts_fundgroup_cyapply` ADD `rejectReason` VARCHAR( 255 ) NOT NULL default '' comment '驳回原因';

DROP TABLE IF EXISTS `ts_fundgroup_rw`;
CREATE TABLE `ts_fundgroup_rw` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `company` VARCHAR( 255 ) NOT NULL default '' comment '公司名',
    `title` VARCHAR( 255 ) NOT NULL default '' comment '任务名称',
    `needMoney` DECIMAL(10,2)  unsigned  NOT NULL default '0' comment '任务奖金',
    `stime` int(11) unsigned NOT NULL  default '0' comment '开始时间',
    `applyTime` int(11) unsigned NOT NULL  default '0' comment '截止申领日期',
    `content` text comment '任务详情',
    `attachId` int(11) unsigned NOT NULL default '0' comment '公司logo',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '添加时间',
    `isdel` tinyint(1) NOT NULL default '0' comment '是否删除',
     PRIMARY KEY  (`id`),
     KEY `isdel` (`isdel`),
     KEY `applyTime` (`applyTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '任务基金';

alter table ts_fundgroup_rw drop getMoney;

DROP TABLE IF EXISTS `ts_fundgroup_rw_school`;
CREATE TABLE `ts_fundgroup_rw_school` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `rwId` int(11) unsigned  NOT NULL ,
    `china` tinyint(1) unsigned NOT NULL default '0' comment '全国',
    `provId` int(11) unsigned NOT NULL default '0' comment '全省',
    `cityId` int(11) unsigned NOT NULL default '0' comment '全市',
    `sid` int(11) unsigned NOT NULL default '0' comment '学校sid',
    PRIMARY KEY  (`id`),
    KEY `rwId` (`rwId`),
    KEY `china` (`china`),
    KEY `provId` (`provId`),
    KEY `cityId` (`cityId`),
    KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '任务基金显示于';

DROP TABLE IF EXISTS `ts_fundgroup_rwapply`;
CREATE TABLE `ts_fundgroup_rwapply` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `rwId` int(11) unsigned NOT NULL  default '0' comment '任务基金Id',
    `gid` int(11) unsigned NOT NULL  default '0' comment '申请部落',
    `uid` int(11) unsigned NOT NULL  default '0' comment '申请人',
    `mobile` VARCHAR( 255 ) NOT NULL default '' comment '负责人联系方式',
    `partner` text comment '参与人姓名',
    `partnerContact` text comment '参与人联系方式',
    `getMoney` DECIMAL(10,2)  unsigned  NOT NULL default '0' comment '实际发放资金',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '添加时间',
    `attachId` int(11) unsigned NOT NULL  default '0' comment '附件id',
    `status` tinyint(1) NOT NULL default '0' comment '状态0待审核，1通过，2发放，-1驳回',
     PRIMARY KEY  (`id`),
     KEY `rwId` (`rwId`),
     KEY `gid` (`gid`),
     KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '任务基金申请';
ALTER TABLE `ts_fundgroup_rwapply` ADD `rejectReason` VARCHAR( 255 ) NOT NULL default '' comment '驳回原因';




