<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// event数据
	"DROP TABLE IF EXISTS `{$db_prefix}exercise`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'exercise'",
);

foreach ($sql as $v)
	M('')->execute($v);