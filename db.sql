# Host: 120.24.47.229  (Version 5.7.10)
# Date: 2017-06-17 17:14:57
# Generator: MySQL-Front 5.4  (Build 1.33)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "yf_additional"
#


#
# Structure for table "yf_app"
#

CREATE TABLE `yf_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(32) NOT NULL DEFAULT '' COMMENT '获取接入APPID',
  `appkey` varchar(255) NOT NULL DEFAULT '' COMMENT 'KEY',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '应用名称',
  `body` varchar(255) NOT NULL DEFAULT '' COMMENT '应用描述',
  `private_key` text NOT NULL COMMENT '私有KEY',
  `public_key` text NOT NULL COMMENT '公有KEY',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='开放应用信息';

#
# Structure for table "yf_area"
#

CREATE TABLE `yf_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '级别（省份-》城市-》区（镇）',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称（省份。城市，区/镇）',
  `pycode` char(20) DEFAULT NULL COMMENT '拼音码',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2503 DEFAULT CHARSET=utf8 COMMENT='中国区域（省份，城市，区域）';




#
# Structure for table "yf_category"
#

CREATE TABLE `yf_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '类别名称',
  `pycate` char(20) DEFAULT NULL COMMENT '拼音类名',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='类别';

#
# Structure for table "yf_merchant"
#

CREATE TABLE `yf_merchant` (
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `mid` varchar(32) NOT NULL DEFAULT '' COMMENT '平台商户ID',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '=1 普通商户 =2子商户',
  `categoryid` int(11) NOT NULL DEFAULT '0' COMMENT '经营类别ID',
  `name` char(60) NOT NULL COMMENT '商户名称',
  `shopimg` varchar(32) DEFAULT NULL COMMENT '店铺招牌图片',
  `body` varchar(255) DEFAULT NULL COMMENT '商户描述',
  `numberno` varchar(30) DEFAULT '' COMMENT '营业执照号',
  `picid` varchar(32) DEFAULT '0' COMMENT '营业执照复印件（图片ID）',
  `cardno` varchar(30) DEFAULT '' COMMENT '身份证号码',
  `cardid1` varchar(32) DEFAULT '0' COMMENT '身份证正面（图片ID)',
  `cardid2` varchar(32) DEFAULT '0' COMMENT '身份证反面（图片ID)',
  `provinceid` int(11) NOT NULL DEFAULT '0' COMMENT '省份ID',
  `cityid` int(11) NOT NULL DEFAULT '0' COMMENT '城市ID',
  `address` varchar(128) DEFAULT '' COMMENT '详细地址',
  `bank_name` varchar(30) DEFAULT '' COMMENT '开户银行',
  `bank_user` char(20) DEFAULT '' COMMENT '开户名称',
  `bank_no` char(20) DEFAULT '' COMMENT '银行帐号',
  `username` char(20) DEFAULT '' COMMENT '联系人姓名',
  `mobile` varchar(20) DEFAULT '' COMMENT '手机号码',
  `email` varchar(30) DEFAULT '' COMMENT '常用邮箱',
  `isdel` tinyint(4) NOT NULL DEFAULT '0' COMMENT '=0未删除 =1已删除',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '=0未审核 ，=1通过审核 ，=2未通过',
  `other_picid` varchar(32) DEFAULT NULL COMMENT '其它图片ID(如食品许可证）',
  `auditing_time` datetime DEFAULT NULL COMMENT '审核时间',
  `auditing_msg` varchar(255) DEFAULT NULL COMMENT '备注信息，审核时的信息',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  UNIQUE KEY `id_UNIQUE` (`mid`),
  KEY `mid_index` (`mid`),
  KEY `uid_index` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商户信息';

#
# Structure for table "yf_payconfig"
#

CREATE TABLE `yf_payconfig` (
  `mid` varchar(32) NOT NULL DEFAULT '' COMMENT '平台商户ID',
  `wx_mch_id` varchar(32) NOT NULL COMMENT '微信平台分配的子商户号',
  `wx_mcd_key` varchar(32) DEFAULT NULL COMMENT '微信平台分配的商户KEY',
  `wx_appid` varchar(32) DEFAULT NULL COMMENT '微信平台子商户公众账号ID',
  `wx_appid_key` varchar(32) DEFAULT NULL COMMENT '公众号AppSecret',
  `wx_token` varchar(32) DEFAULT NULL COMMENT 'token',
  `wx_aes_key` varchar(32) DEFAULT NULL COMMENT 'EncodingAESKey，安全模式下请一定',
  `wx_cert_path` varchar(32) DEFAULT NULL COMMENT 'SSL',
  `wx_key_path` varchar(32) DEFAULT NULL COMMENT 'KEY',
  `wx_openid` varchar(128) DEFAULT NULL COMMENT '(微信openid)二维码收款单通知',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '=1 普通商户 =2子商户',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  UNIQUE KEY `id_UNIQUE` (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='支付配置文件';

#
# Structure for table "yf_payorder"
#

CREATE TABLE `yf_payorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID（收银用户）',
  `mid` varchar(32) NOT NULL DEFAULT '' COMMENT '平台商户ID',
  `wx_mch_id` varchar(32) NOT NULL COMMENT '微信支付分配的子商户号',
  `wx_appid` varchar(32) DEFAULT NULL COMMENT '微信分配的子商户公众账号ID',
  `device_info` varchar(32) DEFAULT NULL COMMENT '微信支付分配的终端设备号',
  `body` varchar(128) NOT NULL COMMENT '商品描述',
  `detail` text COMMENT '商品详情',
  `attach` varchar(200) DEFAULT NULL COMMENT '附加数据内容，原样返回',
  `out_trade_no` char(32) NOT NULL COMMENT '消费商户订单号',
  `transaction_id` char(32) DEFAULT NULL COMMENT '微信支付订单号',
  `total_fee` int(11) NOT NULL DEFAULT '0' COMMENT '订单总金额，单位为分',
  `callback_total_fee` int(11) DEFAULT '0' COMMENT '记录异步完成回调的订单总金额，单位为分',
  `fee_type` char(16) DEFAULT NULL COMMENT '货币类型',
  `poundage_total_fee` double(11,6) DEFAULT NULL COMMENT '单笔手续费',
  `settlement_total_fee` double(11,6) DEFAULT '0.000000' COMMENT '单笔扣除手续费后的金额',
  `cash_fee` int(11) DEFAULT '0' COMMENT '现金支付金额订单现金支付金额',
  `cash_fee_type` char(16) DEFAULT NULL COMMENT '货币类型，符合ISO 4217标准的三位字母代码，默认人民币：CNY',
  `spbill_create_ip` char(30) NOT NULL COMMENT '终端IP',
  `time_start` datetime DEFAULT NULL COMMENT '订单生成时间(请求交易时间)',
  `time_expire` datetime DEFAULT NULL COMMENT '订单失效时间(交易结束时间)',
  `goods_tag` varchar(32) DEFAULT NULL COMMENT '商品标记，代金券或立减优惠功能的参数',
  `trade_type` char(16) NOT NULL COMMENT '交易类型JSAPI、NATIVE、APP',
  `product_id` varchar(32) DEFAULT NULL COMMENT '此id为二维码中包含的商品ID，商户自行定义。',
  `limit_pay` varchar(32) DEFAULT NULL COMMENT '指定支付方式,=no_credit--指定不能使用信用卡支付',
  `openid` varchar(128) DEFAULT NULL COMMENT '用户标识,trade_type=JSAPI，此参数必传，用户在主商户appid下的唯一标识',
  `bank_type` char(30) DEFAULT NULL COMMENT '银行类型，采用字符串类型的银行标识',
  `return_count` int(11) NOT NULL DEFAULT '0' COMMENT '回调次数',
  `return_code` char(30) DEFAULT NULL COMMENT '返回状态码,此字段是通信标识SUCCESS/FAIL',
  `result_code` char(30) DEFAULT NULL COMMENT '交易业务结果SUCCESS/FAIL',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '订单状态: =0未支付 ,=1 完成, =2关闭',
  `is_subscribe` char(1) DEFAULT NULL COMMENT '用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效',
  `sub_is_subscribe` char(1) DEFAULT NULL COMMENT '用户是否关注子公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效',
  `coupon_fee` int(11) DEFAULT NULL COMMENT '代金券或立减优惠金额',
  `coupon_count` int(11) DEFAULT NULL COMMENT '代金券或立减优惠使用数量',
  `coupon_id_var` char(20) DEFAULT NULL COMMENT '代金券或立减优惠ID, $n为下标，从1开始编号',
  `coupon_fee__var` int(11) DEFAULT NULL COMMENT '单个代金券或立减优惠支付金额, $n为下标，从1开始编号',
  `time_end` datetime DEFAULT NULL COMMENT '支付完成时间',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `mid_index` (`mid`),
  KEY `uid_index` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=329 DEFAULT CHARSET=utf8 COMMENT='支付交易表';

#
# Structure for table "yf_picture"
#

CREATE TABLE `yf_picture` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '递增',
  `mid` varchar(32) NOT NULL DEFAULT '' COMMENT '商户ID',
  `title` varchar(30) DEFAULT NULL COMMENT '图片名称',
  `picno` varchar(32) NOT NULL DEFAULT '' COMMENT '图片ID串',
  `file` varchar(40) NOT NULL DEFAULT '' COMMENT '图片存储路径',
  `isdel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 =0未册 =1删除',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COMMENT='图片库';

#
# Structure for table "yf_qrcode"
#

CREATE TABLE `yf_qrcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` varchar(32) NOT NULL DEFAULT '0' COMMENT '商户ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '商品名称',
  `total_fee` int(11) NOT NULL DEFAULT '0' COMMENT '金额',
  `isdel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 =0未册 =1删除',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='二维码参数值';

#
# Structure for table "yf_question"
#

CREATE TABLE `yf_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '递增',
  `mobile` varchar(12) DEFAULT NULL COMMENT '手机号码',
  `contents` varchar(255) NOT NULL DEFAULT '' COMMENT '内容描述',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='返馈问题';

#
# Structure for table "yf_settlement"
#

CREATE TABLE `yf_settlement` (
  `mid` varchar(32) NOT NULL DEFAULT '' COMMENT '平台商户ID',
  `wx_mch_id` varchar(32) NOT NULL COMMENT '微信支付分配的子商户号',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '交易笔数',
  `total_fee` int(11) NOT NULL DEFAULT '0' COMMENT '结算总金额，单位为分',
  `settlement_total_fee` decimal(11,6) DEFAULT '0.000000' COMMENT '扣除手续费后的汇总金额',
  `poundage_total_fee` decimal(11,6) DEFAULT '0.000000' COMMENT '手续费总额',
  `settlement_date` date DEFAULT NULL COMMENT '结算日期',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  KEY `mid_index` (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='支付结算表（按日）';

#
# Structure for table "yf_user"
#

CREATE TABLE `yf_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` varchar(32) DEFAULT NULL COMMENT '商户订单号',
  `puid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `loginname` char(30) NOT NULL DEFAULT '' COMMENT '帐号名称（手机）',
  `password` char(32) NOT NULL COMMENT '密码',
  `encrypted` char(20) DEFAULT '' COMMENT '密码加密串',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '=0 禁止；=1启用',
  `isdel` varchar(45) NOT NULL DEFAULT '0' COMMENT '状态 =0未删, -1已删',
  `isadmin` varchar(45) NOT NULL DEFAULT '0' COMMENT '是否商户管理者=1是，否则=0不是',
  `logincount` int(11) NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `lastlogin_time` datetime DEFAULT NULL COMMENT '最后登陆时间',
  `ip` char(20) DEFAULT '' COMMENT 'IP地址',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1257 DEFAULT CHARSET=utf8 COMMENT='用户表';

#
# Structure for table "yf_wxuser"
#

CREATE TABLE `yf_wxuser` (
  `mid` varchar(32) NOT NULL DEFAULT '' COMMENT '商户ID',
  `openid` varchar(32) NOT NULL DEFAULT '' COMMENT 'openid',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `isdel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '=1删除',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  KEY `mid_index` (`mid`) COMMENT 'mid_index'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信用户信息';
