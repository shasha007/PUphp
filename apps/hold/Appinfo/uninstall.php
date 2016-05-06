<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_hold_info`;",
	"DROP TABLE IF EXISTS `ts_hold_user`;",
	"DROP TABLE IF EXISTS `ts_hold_user_img`;",
	"DROP TABLE IF EXISTS `ts_hold_user_flash`;",
	"DROP TABLE IF EXISTS `ts_hold_vote`;",

	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'hold'",
	"DELETE FROM `{$db_prefix}comment` WHERE `type` = 'hold';",
);

foreach ($sql as $v)
	M('')->execute($v);