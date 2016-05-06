<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_appstore_document`;",
	"DROP TABLE IF EXISTS `ts_appstore_app`;",	
	"DROP TABLE IF EXISTS `ts_appstore_category`;",
	"DROP TABLE IF EXISTS `ts_appstore_banner`;",
	"DROP TABLE IF EXISTS `ts_appstore_link`;",		
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'appstore'",
	"DELETE FROM `{$db_prefix}comment` WHERE `type` = 'appstore'",

);

foreach ($sql as $v)
	M('')->execute($v);