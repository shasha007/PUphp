DROP TABLE IF EXISTS `ts_temporary_tribe`;
CREATE TABLE `ts_temporary_tribe` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`uid`  int(11) NOT NULL COMMENT '作者ID' ,
`sid`  int(11) NOT NULL COMMENT '学校ID',
`sid1`  int(11) NOT NULL COMMENT '学院ID',
`year`  int(11) NOT NULL COMMENT '年级',
`cid`  int(11) NOT NULL COMMENT '分类ID',
`name`  varchar(255) NOT NULL ,
`img`  varchar(255) NOT NULL COMMENT '部落头像',
`explain`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '介绍' ,
`ctime`  int(11) NULL DEFAULT NULL COMMENT '创建时间',
`status`  tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '0待审核1审核通过2审核不通过',
`department`  tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '1学生部门2团支部3学生社团',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

alter table ts_temporary_tribe add audit int(11) NOT NULL DEFAULT 0 COMMENT '审核人ID';
alter table ts_temporary_tribe add atime int(11) NOT NULL DEFAULT 0 COMMENT '审核时间';
alter table ts_temporary_tribe add reason varchar(255) NOT NULL DEFAULT 0 COMMENT '驳回原因';

//部落积分表
DROP TABLE IF EXISTS `ts_group_score`;
CREATE TABLE `ts_group_score` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`uid`  int(11) NOT NULL COMMENT '作者ID' ,
`sid`  int(11) NOT NULL COMMENT '学校ID',
`cid`  int(11) NOT NULL COMMENT '分类ID',
`score` int(11) NOT NULL DEFAULT 0 COMMENT '部落积分',
`reason` varchar(255) NOT NULL COMMENT '加分原因',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;