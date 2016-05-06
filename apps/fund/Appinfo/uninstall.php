<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'fund'",
);

foreach ($sql as $v)
	M('')->execute($v);