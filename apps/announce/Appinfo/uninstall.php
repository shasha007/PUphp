<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_announce`;",
	"DROP TABLE IF EXISTS `ts_announce_category`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'announce'",	
);

foreach ($sql as $v)
	M('')->execute($v);