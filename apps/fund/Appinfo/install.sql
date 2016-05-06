DROP TABLE IF EXISTS `ts_fund_sponsor`;
CREATE TABLE `ts_fund_sponsor` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `company` VARCHAR( 255 ) NOT NULL default '' comment '公司名',
    `putFund` DECIMAL(10,2)  unsigned  NOT NULL default '0' comment '投放金额',
    `actualFund` decimal(10,2)  unsigned  NOT NULL default '0' comment '实际到帐金额',
    `putTime` int(11) unsigned NOT NULL  default '0' comment '投放时间',
    `actualTime` int(11) unsigned NOT NULL  default '0' comment '到帐时间',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '合作开始时间',
    `endTime` int(11) unsigned NOT NULL  default '0' comment '合作结束时间',
    `attachId` int(11) unsigned NOT NULL  default '0' comment '附件id',
     PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '基金赞助';

DROP TABLE IF EXISTS `ts_fund_event`;
CREATE TABLE `ts_fund_event` (
    `eventId` int(11) unsigned  NOT NULL auto_increment,
    `company` VARCHAR( 255 ) NOT NULL default '' comment '申请公司名',
    `eventName` VARCHAR( 255 ) NOT NULL default '' comment '活动名称',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '活动开始时间',
    `endTime` int(11) unsigned NOT NULL default '0' comment '活动结束时间',
    `byTime` int(11) unsigned NOT NULL default '0' comment '申办截止时间',
    `logo` int(11) unsigned NOT NULL default '0' comment '活动logo',
     PRIMARY KEY  (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '申办活动';

DROP TABLE IF EXISTS `ts_fund_event_school`;
CREATE TABLE `ts_fund_event_school` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `fundEvnetId` int(11) unsigned  NOT NULL ,
    `china` tinyint(1) unsigned NOT NULL default '0' comment '全国',
    `provId` int(11) unsigned NOT NULL default '0' comment '全省',
    `cityId` int(11) unsigned NOT NULL default '0' comment '全市',
    `sid` int(11) unsigned NOT NULL default '0' comment '学校sid',
    PRIMARY KEY  (`id`),
    KEY `fundEvnetId` (`fundEvnetId`),
    KEY `china` (`china`),
    KEY `provId` (`provId`),
    KEY `cityId` (`cityId`),
    KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '申办活动显示于';

DROP TABLE IF EXISTS `ts_fund_applyfund`;
CREATE TABLE `ts_fund_applyfund` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `eventId` int(11) unsigned NOT NULL default '0' comment '活动id',
    `uid` int(11) unsigned NOT NULL default '0' comment '申请人',
    `sid` int(11) unsigned NOT NULL default '0' comment '学校',
    `position` VARCHAR( 255 ) NOT NULL default '' comment '职位',
    `telephone` VARCHAR( 255 ) NOT NULL default '' comment '电话',
    `qq` VARCHAR( 255 ) NOT NULL default '' comment 'qq',
    `alipayAccount` VARCHAR( 255 ) NOT NULL default '' comment '支付宝账号',
    `responsibleInfo` VARCHAR( 255 ) NOT NULL default '' comment '部落外联负责人姓名及电话',
    `range` tinyint(1) unsigned NOT NULL default '0' comment '活动范围1校级2院级3团支部4其他',
    `eRegistration` int(11) unsigned NOT NULL default '0' comment '预计报名',
    `eSign` int(11) unsigned NOT NULL default '0' comment '预计签到',
    `fund` DECIMAL(10,2)  unsigned  NOT NULL default '0' comment '申请金额',
    `audltFund` DECIMAL(10,2)  unsigned  NOT NULL default '0' comment '核审金额',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '申请时间',
    `state` tinyint(1) unsigned NOT NULL  default '0' comment '申请状态0待审核1通过2驳回',
    `loanState` tinyint(1) unsigned NOT NULL  default '0' comment '放款状态0未发放1发放',
    `rejectReason` VARCHAR( 255 ) NOT NULL default '' comment '驳回原因',
     PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '申请基金';
ALTER TABLE `ts_fund_applyfund` CHANGE `state` `state` tinyint(1) NOT NULL default '0' comment '申请状态0待审核1通过-1驳回';
ALTER TABLE `ts_fund_applyfund` ADD INDEX ( `state` );
update ts_fund_applyfund set state=-1 where state=2;

DROP TABLE IF EXISTS `ts_fund_fundwater`;
CREATE TABLE `ts_fund_fundwater` (
    `fund` DECIMAL(10,2)  unsigned  NOT NULL  default '0' comment '金额',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '时间',
    `eventId`  int(11) unsigned NOT NULL  default '0' comment '活动id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '基金流水';

DROP TABLE IF EXISTS `ts_fund_applyevent`;
CREATE TABLE `ts_fund_applyevent` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `eventId` int(11) unsigned  NOT NULL  default '0' comment'活动id',
    `gid` int(11) unsigned  NOT NULL  default '0' comment '部落',
    `sid` int(11) unsigned  NOT NULL  default '0' comment '学校',
    `uid` int(11) unsigned NOT NULL  default '0' comment '申请人',
    `telephone` VARCHAR(255) NOT NULL  default '' comment '电话',
    `qq` VARCHAR(255) NOT NULL  default ''  comment 'QQ',
    `alipayAccount` VARCHAR(255) NOT NULL  default ''  comment '支付宝账号',
    `contact` VARCHAR(255)  NOT NULL  default '' comment '联系人',
    `amount` DECIMAL(10,2)  unsigned  NOT NULL  default '0' comment '经费',
    `amount2` DECIMAL(10,2)  unsigned  NOT NULL  default '0' comment '核准经费',
    `amount3` DECIMAL(10,2)  unsigned  NOT NULL  default '0' comment '实际发放',
    `state` tinyint(1) NOT NULL  default '0' comment '申请状态0待审核-1驳回1通过',
    `rejectReason` VARCHAR(255)  NOT NULL  default '0' comment '驳回原因',
    `attachId` int(11) unsigned NOT NULL  default '0' comment '附件id',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '申请时间',
    `eid` int(11) unsigned  NOT NULL  default '0' comment'审核通过后正式活动id',
    `finished` tinyint(1) NOT NULL  default '0' comment '完结状态0未完1已完结',
     PRIMARY KEY(`id`),
    UNIQUE KEY `eid_gid` (`eventId`,`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '申办活动';

alter table ts_fund_applyevent DROP INDEX eid_gid;

ALTER TABLE `ts_fund_applyfund` ADD `sendTime` int(11) unsigned NOT NULL default '0' comment '发放时间';
ALTER TABLE `ts_fund_event` ADD `isDel` tinyint(11) unsigned NOT NULL default '0' comment '是否删除';
Alter table `ts_fund_event_school` modify `sid`int(11) NOT NULL default '0' comment '学校sid 0代表所有学校';
ALTER TABLE `ts_fund_event` ADD `descript` longtext comment '活动描述';

DROP TABLE IF EXISTS `ts_fund_fundlog`;
CREATE TABLE `ts_fund_fundlog` (
    `id` int(11) unsigned  NOT NULL auto_increment,
    `fundId` int(11) unsigned NOT NULL default '0' comment '申请id',
    `state` tinyint(1) unsigned NOT NULL  default '0' comment '申请状态0待审核1通过2发放',
    `cTime` int(11) unsigned NOT NULL  default '0' comment '操作时间',
     PRIMARY KEY(`id`),
     KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '申请基金日志';


