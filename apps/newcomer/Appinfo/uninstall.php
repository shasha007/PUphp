<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_newcomer_document`;",
	"DROP TABLE IF EXISTS `ts_newcomer_document_category`;",
	"DROP TABLE IF EXISTS `ts_newcomer_category`;",

	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'newcomer'",
	"DELETE FROM `{$db_prefix}user_set` WHERE `fieldkey` = 'newcomer_school' OR `fieldkey` = 'newcomer_school' OR `fieldkey` = 'newcomer_school'",
);
$sql = array();
foreach ($sql as $v)
	M('')->execute($v);