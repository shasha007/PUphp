<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// event数据
	"DROP TABLE IF EXISTS `{$db_prefix}kc`;",
        "DROP TABLE IF EXISTS `{$db_prefix}kc_opt;",
        "DROP TABLE IF EXISTS `{$db_prefix}kc_user;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'kc'",
);

foreach ($sql as $v)
	M('')->execute($v);