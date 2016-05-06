<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_coupon`;",
	"DROP TABLE IF EXISTS `ts_coupon_category`;",
	"DROP TABLE IF EXISTS `ts_coupon_school`;",

	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'coupon'",	
	"DELETE FROM `{$db_prefix}comment` WHERE `type` = 'coupon';",
);

foreach ($sql as $v)
	M('')->execute($v);