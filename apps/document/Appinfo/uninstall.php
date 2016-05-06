<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_wenku`;",
	"DROP TABLE IF EXISTS `ts_wenku_category`;",
	"DROP TABLE IF EXISTS `ts_wenku_download`;",

	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'document'",	
	"DELETE FROM `{$db_prefix}comment` WHERE `type` = 'document';",
);

foreach ($sql as $v)
	M('')->execute($v);