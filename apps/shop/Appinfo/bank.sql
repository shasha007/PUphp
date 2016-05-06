DROP TABLE IF EXISTS `ts_bank_card`;
CREATE TABLE `ts_bank_card` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `email` varchar(60) NOT NULL COMMENT '学生唯一邮箱',
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `school_id` int(11) NOT NULL COMMENT '学校id',
  `realname` varchar(20) NOT NULL COMMENT '学生真实姓名',
  `mobile` varchar(20) NOT NULL COMMENT '学生手机号',
  `d_mobile` varchar(20) NOT NULL COMMENT '学生家长手机号',
  `m_mobile` varchar(20) NOT NULL COMMENT '学生家长手机号',
  `ctf_id` varchar(20) NOT NULL COMMENT '仅支持身份证号',
  `address` varchar(100) NOT NULL COMMENT '联系地址',
  `mark` tinyint(4) NOT NULL DEFAULT 0 COMMENT '借款类型 1 一千免息2非一千免息',
  `post_code` int(11) NOT NULL COMMENT '邮编',
  `email_bill` varchar(60) NOT NULL COMMENT '发送账单的邮箱',
  `total_line` decimal(10,2) DEFAULT '0.00' COMMENT '总额度',
  `surplus_line` decimal(10,2) DEFAULT '0.00' COMMENT '剩余额度',
  `reason` text NOT NULL COMMENT '申请理由',
  `back_reason` text NOT NULL DEFAULT '' COMMENT '驳回原因',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '办卡状态0未处理1处理中2办卡成功3办卡失败',
  `is_mali` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否恶意欠款0否1是',
  `allow_finance` tinyint(4) NOT NULL DEFAULT 1 COMMENT '是否允许借贷0不允许1允许',
  `allow_risk` tinyint(4) NOT NULL DEFAULT 0 COMMENT '风控是否通过0拒绝1通过',
  `card_no` varchar(60) DEFAULT NULL COMMENT '银行卡号，办理成功后自动填入',
  `recommend` varchar(20) NOT NULL DEFAULT '' COMMENT '推荐人姓名',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报名申请办卡表';



CREATE TABLE `ts_bank_applycard_order` (
  `order_id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `stime` datetime NOT NULL COMMENT '办卡开始时间',
  `etime` datetime DEFAULT '' COMMENT '办卡结束时间',
  `bank_order_id` varchar(30) DEFAULT '' COMMENT '银行订单号',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态 0未处理1处理中2处理结束',
  `bank_code` int(11) NOT NULL DEFAULT 0 COMMENT '银行办卡请求返回码',
  `bank_msg` text DEFAULT '' COMMENT '银行办卡请求返回信息',
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='申请办卡订单表';

CREATE TABLE `ts_bank_applycard_order_card` (
  `order_id` bigint(20) NOT NULL COMMENT '对应bank_applycard_order表order_id',
  `bank_card_id` int(11) NOT NULL COMMENT '对应bank_card表id 单条或多条数据',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态 0未处理1处理中2处理结束',
  `update_time` date NOT NULL COMMENT '更新时间',
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='申请办卡订单详细信息表';

CREATE TABLE `ts_bank_applycard_order_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) NOT NULL,
  `post_data` text NOT NULL COMMENT '发送数据',
  `rev_data` text DEFAULT '' COMMENT '接收数据',
  `ctime` datetime NOT NULL COMMENT '创建时间',
  `rev_time` datetime NOT NULL COMMENT '接收数据时间',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='银行办卡日志表';

CREATE TABLE `ts_bank_cron` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) NOT NULL,
  `stime` datetime NOT NULL COMMENT '开始时间',
  `etime` datetime DEFAULT '' COMMENT '结束时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态 0未处理1处理中2处理结束',
  `type` varchar(60) NOT NULL DEFAULT 0 COMMENT '类型：applycard办卡',
  `path` varchar(100) DEFAULT '' COMMENT '上传回执xml文件目录',
  `filename` varchar(60) DEFAULT '' COMMENT 'xml文件名',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='银行任务表,处理回执文件';

CREATE TABLE `ts_bank_lend_order` (
  `order_id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `stime` datetime NOT NULL COMMENT '放款开始时间',
  `etime` datetime DEFAULT '' COMMENT '放款结束时间',
  `bank_order_id` varchar(30) DEFAULT '' COMMENT '银行订单号',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态 0未处理1处理中2处理结束',
  `bank_code` int(11) NOT NULL DEFAULT 0 COMMENT '银行放款请求返回码',
  `bank_msg` text DEFAULT '' COMMENT '银行放款请求返回信息',
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU放款表';

CREATE TABLE `ts_bank_lend_order_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) NOT NULL,
  `post_data` text NOT NULL COMMENT '发送数据',
  `rev_data` text DEFAULT '' COMMENT '接收数据',
  `ctime` datetime NOT NULL COMMENT '创建时间',
  `rev_time` datetime NOT NULL COMMENT '接收数据时间',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU放款日志表';

CREATE TABLE `ts_bank_deduct_order` (
  `order_id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `stime` datetime NOT NULL COMMENT '扣款开始时间',
  `etime` datetime DEFAULT '' COMMENT '扣款结束时间',
  `bank_order_id` varchar(30) DEFAULT '' COMMENT '银行订单号',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态 0未处理1处理中2处理结束',
  `bank_code` int(11) NOT NULL DEFAULT 0 COMMENT '银行扣款请求返回码',
  `bank_msg` text DEFAULT '' COMMENT '银行扣款请求返回信息',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU扣款表';

CREATE TABLE `ts_bank_deduct_order_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) NOT NULL,
  `post_data` text NOT NULL COMMENT '发送数据',
  `rev_data` text DEFAULT '' COMMENT '接收数据',
  `ctime` datetime NOT NULL COMMENT '创建时间',
  `rev_time` datetime NOT NULL COMMENT '接收数据时间',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU扣款日志表';

CREATE TABLE `ts_bank_finance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `bank_card_id` bigint(20) NOT NULL COMMENT 'bank_card表的id',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '还款本金',
  `interest` decimal(10,2) DEFAULT '0.00' COMMENT '还款利息',
  `rate` decimal(10,2) DEFAULT '0.00' COMMENT '利率',
  `reason` text NOT NULL COMMENT '申请理由',
  `stime` date NOT NULL COMMENT '扣款开始时间',
  `etime` date DEFAULT '0000-00-00' COMMENT '扣款结束时间',
  `ctime` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `staging` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '还款期数',
  `mark` tinyint(4) NOT NULL DEFAULT '0' COMMENT '借款类型 1 一千免息2非一千免息',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '还款状态 0未还款 1还款中（放款成功后更新1） 2还款结束 98开始处理1000元免息',
  `lockTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '放款锁定时间戳',
  `is_kx` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '康欣是否导出',
  PRIMARY KEY (`id`),
  KEY `lockTime` (`lockTime`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU分期表';

CREATE TABLE `ts_bank_finance_detail` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `bank_finance_id` bigint(20) NOT NULL COMMENT '分期id，对应bank_finance的id',
  `order_id` bigint(20) DEFAULT 0 COMMENT '还款订单号对应bank_deduct_order的order_id，每月还款结束时添加',
  `uid` int(11) NOT NULL COMMENT '学生uid',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '每月还款金额 本金+利息',
  `stime` date NOT NULL COMMENT '扣款开始时间',
  `etime` date DEFAULT '' COMMENT '扣款结束时间',
  `ctime` datetime DEFAULT '' COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '还款状态 1还款中2还款结束',
  `bank_code` int(11) NOT NULL DEFAULT 0 COMMENT '银行扣款请求返回码',
  `bank_msg` text DEFAULT '' COMMENT '银行扣款请求返回信息',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU分期详细表，每月还款额';

CREATE TABLE `ts_bank_rate` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '还款本金',
  `rate` decimal(10,2) DEFAULT '0.00' COMMENT '利率',
  `staging` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '还款期数',
  `isDel` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态 1删除',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PU利率表';