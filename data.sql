CREATE TABLE `xx_email_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '模板标题',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '邮件标题',
  `body` text  DEFAULT NULL COMMENT '邮件正文',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '开关状态 1：开启 2：关闭',
  `receivers` varchar(1000) NOT NULL DEFAULT '' COMMENT '接收者多个用英文逗号隔开',
  `cc` varchar(1000) NOT NULL DEFAULT '' COMMENT '抄送者多个用英文逗号隔开',
  `last_send_time` int(11) DEFAULT NULL COMMENT '最后一次发送时间',
  `template_class` varchar(255) NOT NULL DEFAULT '' COMMENT '发送的模板的类',
  `cron_day` varchar(255) DEFAULT NULL COMMENT '定时发送时间 单位格式：天',
  `cron_hour` varchar(255) DEFAULT NULL COMMENT '定时发送时间 单位格式：时',
  `cron_minute` varchar(255) DEFAULT NULL COMMENT '定时发送时间 单位格式：分',
  `is_html` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否以html格式发送默认0否 1是',
  `attempt_num` tinyint(1) NOT NULL DEFAULT '0' COMMENT '重试次数',
  `success_num` int(10) NOT NULL DEFAULT '0' COMMENT '成功数量',
  `fail_num` int(10) NOT NULL DEFAULT '0' COMMENT '失败数量',
  `total_num` int(10) NOT NULL DEFAULT '0' COMMENT '发送总数量',
  `updated_user` varchar(50) DEFAULT NULL COMMENT '更新者',
  `created_user` varchar(50) DEFAULT NULL COMMENT '创建者',
  `created_at` int(10) DEFAULT '0' COMMENT '添加时间',
  `updated_at` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `CLASS_INDEX` (`template_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件模板';

CREATE TABLE `xx_email_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板ID',
  `sender` varchar(64) NOT NULL DEFAULT '' COMMENT '发送者',
  `receiver` varchar(1000) NOT NULL COMMENT '接收者',
  `cc` varchar(1000) NOT NULL DEFAULT '' COMMENT '抄送者多个用英文逗号隔开',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1：成功 2：失败',
  `send_time` int(11) DEFAULT NULL COMMENT '发送时间',
  `cron_time` bigint(20) NOT NULL COMMENT '定时发送时间 单位格式根据时间格式2019052115',
  `send_template` varchar(255) NOT NULL COMMENT '发送的模板',
  `is_html` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否以html格式发送默认0否 1是',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '邮件标题',
  `body` text  DEFAULT NULL COMMENT '邮件正文',
  `runtime` decimal(5,4) NOT NULL DEFAULT 0 COMMENT '发送邮件耗时单位毫秒',
  `created_at` int(10) DEFAULT 0 COMMENT '添加时间',
  `attempt_num` tinyint(1) DEFAULT '0' COMMENT '重试次数',
  `attachment` varchar(1000) DEFAULT NULL COMMENT '附件多个用逗号隔开',
  `flag` tinyint(1) DEFAULT 0 COMMENT '开关默认不打开 0是关闭 1是打开',
  PRIMARY KEY (`id`),
  KEY `MULTI_INDEX_1` (`template_id`,`cron_time`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件模板';

CREATE TABLE `xx_email_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_class` varchar(255) NOT NULL COMMENT '发送的模板的类',
  `reason` varchar(256) NOT NULL DEFAULT '' COMMENT '失败的原因',
  `created_at` int(10) DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件错误信息';