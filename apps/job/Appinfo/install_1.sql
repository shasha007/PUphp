DROP TABLE IF EXISTS `ts_job_enterprise`;
CREATE TABLE `ts_job_enterprise` (
  `eid` int(11) unsigned NOT NULL auto_increment comment '企业ID',
  `ename` VARCHAR( 255 ) NOT NULL default '' comment '用户名',
  `password` VARCHAR( 255 ) NOT NULL default '' comment '密码',
  `is_activation` tinyint(1) unsigned NOT NULL default '0' comment '是否激活',
   PRIMARY KEY  (`eid`),
   KEY is_activation (is_activation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '企业用户表';

DROP TABLE IF EXISTS `ts_job_account`;
CREATE TABLE `ts_job_account` (
  `eid` int(11) unsigned NOT NULL  comment '企业ID',
  `account` decimal(10,2) NOT NULL default '0' comment '账户余额'
   PRIMARY KEY  (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '账户余额';

DROP TABLE IF EXISTS `ts_job_enterprise_info`;
CREATE TABLE `ts_job_enterprise_info` (
  `eid` int(11) unsigned NOT NULL  comment '企业ID',
  `type` tinyint(1) unsigned NOT NULL default '0' comment '公司类型',
  `industrycategory_a` int(11) unsigned NOT NULL default '0' comment '行业类别1',
  `industrycategory_b` int(11) unsigned NOT NULL default '0' comment '行业类别2',
  `scale` VARCHAR( 255 ) NOT NULL default '' comment '公司规模',
  `address` VARCHAR( 255 ) NOT NULL default '' comment '公司地址',
  `homepage` VARCHAR( 255 ) NOT NULL default '' comment '公司主页',
  `contact` VARCHAR( 255 ) NOT NULL default '' comment '联系人',
  `city` int(11) unsigned NOT NULL comment '城市',
  `telephone_areacode` int(11) unsigned NOT NULL comment '电话区号',
  `telephone` int(11) unsigned NOT NULL  default '0' comment '电话号',
  `logo_pic` int(11) unsigned NOT NULL  default '0' comment 'LOGOid',
  `contact_email` VARCHAR( 255 ) NOT NULL default '' comment '联系人邮箱',
  `iphone` int(11) unsigned NOT NULL default '0' comment '联系电话',
  `getresume_email` VARCHAR( 255 ) NOT NULL default '' comment '接受简历邮箱',
  `fullname` VARCHAR( 255 ) NOT NULL default '' comment '公司全名',
  `email_vcode` VARCHAR( 255 ) NOT NULL default '' comment '邮箱验证码',
  `lastlogintime` int(11) unsigned NOT NULL default '0' comment '最后登陆时间',
  `ctime` int(11) unsigned NOT NULL comment '注册时间',
  `blicense` int(11) unsigned NOT NULL default '0' comment '营业执照id',
  `introduction` VARCHAR( 255 ) NOT NULL default '' comment '简介',
  `founder_name` VARCHAR( 255 ) NOT NULL default '' comment '创始人',
  `founder_pic` int(11) unsigned NOT NULL comment '创始人照片',
  `founder_info` VARCHAR( 255 ) NOT NULL default '' comment '创始人信息',
  `is_black` tinyint(1) unsigned NOT NULL  default '0' comment '黑名单 0正常 1拉黑'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '公司基本信息';


DROP TABLE IF EXISTS `ts_job_account_water`;
CREATE TABLE `ts_job_account_water` (
 `water_id` int(11) unsigned NOT NULL auto_increment comment '流水ID',
  `watertype` tinyint(1) unsigned NOT NULL comment '流水类型',
  `account` decimal(10,2) NOT NULL default '0' comment '金额',
  `ctime` int(11) unsigned NOT NULL  default '0' comment '时间',
  `eid` int(11) unsigned NOT NULL  default '0' comment '公司Id',
  PRIMARY KEY (water_id),
  KEY (eid)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '账户消费情况(流水)';

DROP TABLE IF EXISTS `ts_job_publish_posts`;
CREATE TABLE `ts_job_publish_posts` (
 `postid` int(11) unsigned NOT NULL auto_increment comment '发布ID',
 `positionname` VARCHAR( 255 ) NOT NULL default '' comment '职位名称',
 `positionclass` tinyint(1) NOT NULL default '0' comment '职位分类',
 `number` tinyint(1) unsigned NOT NULL  default '0' comment  '招聘人数',
 `workarea` int(11) unsigned NOT NULL  default '0' comment '工作区域',
 `endtime` int(11) unsigned NOT NULL  default '0' comment '结束日期',
 `positiontype` tinyint(1) unsigned NOT NULL default '0' comment '职位类型 0全职',
 `salary` VARCHAR( 255 ) NOT NULL default '' comment '薪资',
 `education` tinyint(1) unsigned NOT NULL default '0' comment '学历',
 `sex` tinyint(1) unsigned NOT NULL default '0' comment '性别',
 `jobdescripition` VARCHAR( 255 ) NOT NULL default '' comment '岗位描述',
 `state` tinyint(1) unsigned NOT NULL default '0' comment '状态',
 `ctime` int(11) unsigned NOT NULL  default '0' comment '时间',
 `isDel` tinyint(1) unsigned NOT NULL default '0' comment '是否删除',
  PRIMARY KEY (postid),
  KEY isDel (isDel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '发布岗位';

DROP TABLE IF EXISTS `ts_job_position_collection`;
CREATE TABLE `ts_job_position_collection` (
 `uid` int(11) unsigned NOT NULL  default '0' comment '收藏人',
 `postid` int(11) unsigned NOT NULL  default '0' comment '岗位id',
 `ctime` int(11) unsigned NOT NULL  default '0' comment '时间',
  PRIMARY KEY (uid,postid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '职位收藏';

DROP TABLE IF EXISTS `ts_job_attention`;
CREATE TABLE `ts_job_attention` (
 `uid` int(11) unsigned NOT NULL  default '0' comment '用户id',
 `eid` int(11) unsigned NOT NULL  default '0' comment '公司id',
 `ctime` int(11) unsigned NOT NULL  default '0' comment '关注时间',
 PRIMARY KEY (uid,eid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '关注公司';

DROP TABLE IF EXISTS `ts_job_resume_browsing`;
CREATE TABLE `ts_job_resume_browsing` (
 `resume_browsing_id` int(11) unsigned NOT NULL auto_increment,
 `eid` int(11) unsigned NOT NULL  default '0' comment '公司ID',
 `rid` int(11) unsigned NOT NULL  default '0' comment '简历ID',
 `btime` int(11) unsigned NOT NULL  default '0' comment '浏览时间',
 `is_download` tinyint(1) unsigned NOT NULL  default '0' comment '是否下载 0未 1下载',
 `dtime` int(11) unsigned NOT NULL  default '0' comment '下载时间',
  PRIMARY KEY (resume_browsing_id),
  KEY is_download (is_download)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '简历浏览';

DROP TABLE IF EXISTS `ts_job_application`;
CREATE TABLE `ts_job_application` (
 `application_id` int(11) unsigned NOT NULL auto_increment,
 `uid` int(11) unsigned NOT NULL  default '0' comment '用户ID',
 `postid` int(11) unsigned NOT NULL  default '0' comment '职位ID',
 `state` tinyint(1) unsigned NOT NULL  default '0' comment '状态',
 `ctime` int(11) unsigned NOT NULL  default '0' comment '申请时间',
  PRIMARY KEY (application_id),
  KEY state (state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '职位申请';

DROP TABLE IF EXISTS `ts_job_talentpool`;
CREATE TABLE `ts_job_talentpool` (
 `application_id` int(11) unsigned NOT NULL auto_increment,
 `uid` int(11) unsigned NOT NULL  default '0' comment '用户ID',
 `eid` int(11) unsigned NOT NULL  default '0' comment '公司ID',
 `ctime` int(11) unsigned NOT NULL  default '0' comment '添加时间',
 `isDel` tinyint(1) unsigned NOT NULL  default '0' comment '是否删除',
  PRIMARY KEY (application_id),
  KEY isDel (isDel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '公司人才库';


DROP TABLE IF EXISTS `ts_job_label`;
CREATE TABLE `ts_job_label` (
 `label_id` int(11) unsigned NOT NULL auto_increment,
 `uid` int(11) unsigned NOT NULL  default '0' comment '学生用户ID',
 `label` int(11) unsigned NOT NULL  default '0' comment '标签ID',
 PRIMARY KEY (label_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '学生标签';

DROP TABLE IF EXISTS `ts_job_product_pic`;
CREATE TABLE `ts_job_product_pic` (
 `product_pic_id` int(11) unsigned NOT NULL auto_increment comment 'ID',
 `pic` int(11) unsigned NOT NULL  default '0' comment '产品照片id',
 `eid` int(11) unsigned NOT NULL  default '0' comment '公司id',
  PRIMARY key(product_pic_id),
  KEY (eid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '公司产品照片';


DROP TABLE IF EXISTS `ts_job_recommendcompany`;
CREATE TABLE `ts_job_recommendcompany` (
 `recommend_id` int(11) unsigned NOT NULL auto_increment comment 'ID',
 `eid` int(11) unsigned NOT NULL  default '0' comment '公司id',
 `endtime` int(11) unsigned NOT NULL  default '0' comment '到期时间',
  PRIMARY KEY (recommend_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '推荐企业';



DROP TABLE IF EXISTS `ts_job_report`;
CREATE TABLE `ts_job_report` (
 `id` int(11) unsigned NOT NULL auto_increment comment 'ID',
 `uid` int(11) unsigned NOT NULL  default '0' comment '用户ID',
 `postid` int(11) unsigned NOT NULL  default '0' comment '招聘ID',
 `type` int(11) unsigned NOT NULL  default '0' comment '类型',
 `reason` VARCHAR( 255 ) NOT NULL default '' comment '原因',
 `ctime` int(11) unsigned NOT NULL  default '0' comment '时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '招聘举报';


DROP TABLE IF EXISTS `ts_job_localpolicy`;
CREATE TABLE `ts_job_localpolicy` (
 `id` int(11) unsigned NOT NULL auto_increment comment 'ID',
 `title` VARCHAR( 255 ) NOT NULL default '' comment '标题',
 `content` VARCHAR( 255 ) NOT NULL default '' comment '内容',
 `ctime` int(11) unsigned NOT NULL  default '0' comment '时间',
 `area` int(11) unsigned NOT NULL  default '0' comment '地区',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '地方政策表';


ALTER TABLE `ts_job_recommendcompany` ADD `type` tinyint(1) unsigned NOT NULL default '0' comment '推荐类型 0自己 1政府';
ALTER TABLE `ts_job_enterprise_info` ADD `is_gover` tinyint(1) unsigned NOT NULL default '0' comment '推荐类型 0无 1政府添加';
ALTER TABLE `ts_job_publish_posts` ADD `is_gover` tinyint(1) unsigned NOT NULL default '0' comment '推荐类型 0无 1政府添加';
ALTER TABLE `ts_job_localpolicy` ADD `author` varchar(50) NOT NULL default '';
ALTER TABLE `ts_job_publish_posts` ADD `eid` int unsigned NOT NULL default '0';

DROP TABLE IF EXISTS `ts_job_postid_sid`;
CREATE TABLE `ts_job_postid_sid` (
    `postid` int(11) unsigned  NOT NULL ,
    `sid` int(11)  NOT NULL default '0' comment '学校sid -100代表所有学校'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '招聘学校绑定';

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'job','version_number','s:1:"1";','2015-03-01 10:00:00');

ALTER TABLE `ts_job_localpolicy` ADD `author` VARCHAR( 255 ) NOT NULL default '' comment '发布人';