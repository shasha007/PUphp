DROP TABLE IF EXISTS `ts_bank_card`;
CREATE TABLE `ts_bank_card` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT '学生唯一邮箱',
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `school_id` int(11) NOT NULL COMMENT '学校id',
  `realname` varchar(20) NOT NULL COMMENT '学生真实姓名',
  `mobile` varchar(100) NOT NULL COMMENT '学生手机号',
  `d_mobile` varchar(20) NOT NULL COMMENT '学生家长手机号',
  `m_mobile` varchar(20) COMMENT '学生家长手机号',
  `ctf_id` varchar(20) NOT NULL COMMENT '仅支持身份证号',
  `address` varchar(100) NOT NULL COMMENT '联系地址',
  `post_code` int(11) NOT NULL COMMENT '邮编',
  `email_bill` varchar(60) NOT NULL COMMENT '发送账单的邮箱',
  `total_line` decimal(10,2) DEFAULT '0.00' COMMENT '总额度',
  `surplus_line` decimal(10,2) DEFAULT '0.00' COMMENT '剩余额度',
  `channel`varchar(60) NOT NULL DEFAULT '0' COMMENT '办卡渠道',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '办卡状态0未处理1处理中2办卡成功3办卡失败',
  `is_mali` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否恶意欠款0否1是',
  `allow_finance` tinyint(4) NOT NULL DEFAULT 1 COMMENT '是否允许借贷0不允许1允许',
  `allow_risk` tinyint(4) NOT NULL DEFAULT 0 COMMENT '风控是否通过0拒绝1通过',
  `free_allow_risk` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1000免息风控是否通过0拒绝1通过',
  `card_no` varchar(100) DEFAULT NULL COMMENT '银行卡号，办理成功后自动填入',
  `card_account` varchar(100) DEFAULT NULL COMMENT '银行卡户名，办理成功后自动填入',
  `ctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '办卡时间',
  `suctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '办卡成功时间',
  `referrer` varchar(50) NOT NULL DEFAULT '' COMMENT '推荐人',
  `area` varchar(20) NOT NULL DEFAULT '' COMMENT '区号',
  `imgs` text COMMENT '附件图片',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报名申请办卡表';
ALTER table `ts_bank_card` ADD `f_mark` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 非一千元免息 1一千元免息';
ALTER table `ts_bank_card` ADD `check` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户修改手机 0不用修改  1需要修改';
ALTER table `ts_bank_card` ADD `back_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '风控驳回原因';
ALTER table `ts_bank_card` ADD `isReg` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是不是注册用户 1 是 0不是的';
ALTER table `ts_bank_card` ADD `isYear` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是不是待毕业生 1 是 0不是的';

DROP TABLE IF EXISTS `ts_bank_applycard_order`;
CREATE TABLE `ts_bank_applycard_order` (
  `order_id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `bank_card_id` int(11) NOT NULL COMMENT '对应bank_card表id',
  `stime` datetime NOT NULL COMMENT '办卡开始时间',
  `etime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '办卡结束时间',
  `bank_order_id` varchar(30) DEFAULT '' COMMENT '银行订单号',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态 0未处理1处理中2处理结束3返回数据错误',
  `bank_code` int(11) NOT NULL DEFAULT 0 COMMENT '银行办卡请求返回码',
  `bank_msg` text DEFAULT '' COMMENT '银行办卡请求返回信息',
  `error_applyid` int DEFAULT 0 COMMENT '银行办卡返回信息中有问题的数据',
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='申请办卡订单表';

DROP TABLE IF EXISTS `ts_bank_applycard_order_log`;
CREATE TABLE `ts_bank_applycard_order_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) NOT NULL,
  `post_data` text NOT NULL COMMENT '发送数据',
  `rev_data` text DEFAULT '' COMMENT '接收数据',
  `ctime` datetime NOT NULL COMMENT '创建时间',
  `rev_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '接收数据时间',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='银行办卡日志表';

DROP TABLE IF EXISTS `ts_bank_cron`;
CREATE TABLE `ts_bank_cron` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `stime` datetime NOT NULL COMMENT '开始时间',
  `etime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '结束时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态 0未处理1处理中2处理结束',
  `type` varchar(60) NOT NULL DEFAULT 0 COMMENT '类型：applycard办卡',
  `path` varchar(100) DEFAULT '' COMMENT '上传回执xml文件目录',
  `filename` varchar(60) DEFAULT '' COMMENT 'xml文件名',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='银行任务表,处理回执文件';

DROP TABLE IF EXISTS `ts_bank_lend_order`;
CREATE TABLE `ts_bank_lend_order` (
  `order_id` int(11) unsigned NOT NULL auto_increment,
  `finance_id` bigint(20) unsigned NOT NULL COMMENT 'ts_bank_finance的id',
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `stime` datetime NOT NULL COMMENT '放款开始时间',
  `etime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '放款结束时间',
  `bank_order_id` varchar(30) DEFAULT '' COMMENT '银行订单号',
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '订单状态 0未处理1处理中2处理结束',
  `bank_code` int(11) NOT NULL DEFAULT 0 COMMENT '银行放款请求返回码',
  `bank_msg` text DEFAULT '' COMMENT '银行放款请求返回信息',
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU放款表';

DROP TABLE IF EXISTS `ts_bank_lend_order_log`;
CREATE TABLE `ts_bank_lend_order_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) NOT NULL,
  `post_data` text NOT NULL COMMENT '发送数据',
  `rev_data` text DEFAULT '' COMMENT '接收数据',
  `ctime` datetime NOT NULL COMMENT '创建时间',
  `rev_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '接收数据时间',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU放款日志表';

DROP TABLE IF EXISTS `ts_bank_deduct_order`;
CREATE TABLE `ts_bank_deduct_order` (
  `order_id` int(11) unsigned NOT NULL auto_increment,
  `finance_detail_id` bigint(20) unsigned NOT NULL COMMENT 'ts_bank_finance_detail的id',
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `stime` datetime NOT NULL COMMENT '扣款开始时间',
  `etime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '扣款结束时间',
  `bank_order_id` varchar(30) DEFAULT '' COMMENT '银行订单号',
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '订单状态 0未处理1处理中2处理结束',
  `bank_code` int(11) NOT NULL DEFAULT 0 COMMENT '银行扣款请求返回码',
  `bank_msg` text DEFAULT '' COMMENT '银行扣款请求返回信息',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU扣款表';

DROP TABLE IF EXISTS `ts_bank_deduct_order_log`;
CREATE TABLE `ts_bank_deduct_order_log` (
  `order_id` bigint(20) NOT NULL COMMENT '对应ts_bank_deduct_order的id',
  `post_data` text NOT NULL COMMENT '发送数据',
  `rev_data` text DEFAULT '' COMMENT '接收数据',
  `ctime` datetime NOT NULL COMMENT '创建时间',
  `rev_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '接收数据时间',
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU扣款日志表';

DROP TABLE IF EXISTS `ts_bank_finance`;
CREATE TABLE `ts_bank_finance` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `bank_card_id` bigint(20) NOT NULL COMMENT 'bank_card表的id',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '还款本金',
  `interest` decimal(10,2) DEFAULT '0.00' COMMENT '还款利息',
  `rate` decimal(10,2) DEFAULT '0.00' COMMENT '利率',
  `reason` text NOT NULL COMMENT '申请理由',
  `stime` date NOT NULL COMMENT '扣款开始时间',
  `etime` date DEFAULT '0000-00-00 00:00:00' COMMENT '扣款结束时间',
  `ctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `staging` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '还款期数',
  `mark` tinyint(4) NOT NULL DEFAULT 0 COMMENT '借款类型 1 一千免息2非一千免息',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '还款状态 0未还款 1还款中（放款成功后更新1） 2还款结束 40允许借款 44订单作废 98开始处理1000元免息',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU分期表';

DROP TABLE IF EXISTS `ts_bank_finance_detail`;
CREATE TABLE `ts_bank_finance_detail` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `bank_finance_id` bigint(20) NOT NULL COMMENT '分期id，对应bank_finance的id',
  `bank_card_id` bigint(20) NOT NULL COMMENT 'bank_card表的id',
  `order_id` bigint(20) DEFAULT 0 COMMENT '还款订单号对应bank_deduct_order的order_id，每月还款结束时添加',
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '每月还款金额 本金+利息',
  `surp_money` decimal(10,2) DEFAULT '0.00' COMMENT '每月剩余还款金额 本金+利息',
  `stime` date DEFAULT '0000-00-00' COMMENT '扣款开始时间',
  `etime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '扣款结束时间',
  `ctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '还款状态 0未还款 1还款中 2还款结束 3还款失败 4余额不足 997开始发送查询扣款结果请求 998开始发送扣款请求  999银行处理扣款中',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU分期详细表，每月还款额';

DROP TABLE IF EXISTS `ts_bank_rate`;
CREATE TABLE `ts_bank_rate` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '还款本金',
  `rate` decimal(10,2) DEFAULT '0.00' COMMENT '利率',
  `staging` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '还款期数',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU利率表';

DROP TABLE IF EXISTS `ts_bank_user_info`;
CREATE TABLE `ts_bank_user_info` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `bank_id` int(11) NOT NULL COMMENT '对应bank_card表ID',
  `nation` varchar(20) NOT NULL COMMENT '民族',
  `education` tinyint(4) NOT NULL DEFAULT 1 COMMENT '学历 1专科  2本科',
  `qq` varchar(20) NOT NULL DEFAULT '' COMMENT 'QQ账号',
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `sid1` int(11) NOT NULL COMMENT '院系id',
  `dc_mobile` varchar(20) COMMENT '父亲单位手机号',
  `mc_mobile` varchar(20) COMMENT '母亲单位手机号',
  `d_name` varchar(20) NOT NULL COMMENT '父亲姓名',
  `m_name` varchar(20) COMMENT '母亲姓名',
  `d_company` varchar(60) NOT NULL DEFAULT '' COMMENT '父亲单位名称',
  `m_company` varchar(60) NOT NULL DEFAULT '' COMMENT '母亲单位名称',
  `classmate` varchar(20) NOT NULL COMMENT '同学姓名',
  `class_mobile` varchar(20) COMMENT '同学手机号',
  `home` varchar(100) NOT NULL COMMENT '家庭地址',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='办卡用户信息表';
ALTER table `ts_bank_user_info` ADD `class_address` varchar(100) NOT NULL COMMENT '同学联系地址';

DROP TABLE IF EXISTS `ts_bank_contract`;
CREATE TABLE `ts_bank_contract` (
  `contract_id` varchar(50) NOT NULL ,
  `finance_id` int(11) unsigned  COMMENT '分期ID',
  `ctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `uid` int(11) unsigned NOT NULL COMMENT '学生uid',
  `ptm` varchar(255) COMMENT '提款协议',
  `pfm` varchar(255) COMMENT '服务协议',
  PRIMARY KEY  (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU合同编号表';

DROP TABLE IF EXISTS `ts_bank_credit`;
CREATE TABLE `ts_bank_credit` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `contract` varchar(50) NOT NULL COMMENT '授信合同编号' ,
  `ctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `uid` int(11) unsigned NOT NULL COMMENT '学生uid',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU授信合同编号表';


DROP TABLE IF EXISTS `ts_bank_channel`;
CREATE TABLE `ts_bank_channel` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `real` varchar(255) NOT NULL  COMMENT '真实值',

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='口袋金渠道表';

DROP TABLE IF EXISTS `ts_bank_school`;
CREATE TABLE `ts_bank_school` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `sid` int(11) unsigned NOT NULL COMMENT '学校id',
  `cate` tinyint(4) NOT NULL DEFAULT 1 COMMENT '类型 1 一千免息',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='口袋金免风控学校表';

ALTER table `ts_bank_finance` ADD `lockTime` int(11) unsigned NOT NULL default 0 COMMENT '放款锁定时间戳';
ALTER table `ts_bank_finance` ADD INDEX ( `lockTime` ) ;
ALTER table `ts_bank_finance` ADD INDEX ( `status` ) ;


DROP TABLE IF EXISTS `ts_bank_ddct`;
CREATE TABLE `ts_bank_ddct` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned DEFAULT '0' COMMENT '学校id',
  `card_no` varchar(100) NOT NULL DEFAULT ''  COMMENT '银行卡号，办理成功后自动填入',
  `card_account` varchar(100) NOT NULL DEFAULT '' COMMENT '银行卡户名，办理成功后自动填入',
  `isddct` varchar(10) NOT NULL DEFAULT '' COMMENT '是否签代扣协议',
  `ctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='银行返回数据暂存表';