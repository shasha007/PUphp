<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_question`;",
	"DROP TABLE IF EXISTS `ts_question_category`;",
	"DROP TABLE IF EXISTS `ts_question_answer`;",

	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'question'",	
);

foreach ($sql as $v)
	M('')->execute($v);