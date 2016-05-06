<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_show_info`;",
	"DROP TABLE IF EXISTS `ts_show_user`;",
	"DROP TABLE IF EXISTS `ts_show_user_img`;",
	"DROP TABLE IF EXISTS `ts_show_user_flash`;",
	"DROP TABLE IF EXISTS `ts_show_vote`;",

	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'show'",	
	"DELETE FROM `{$db_prefix}comment` WHERE `type` = 'show';",
);

foreach ($sql as $v)
	M('')->execute($v);