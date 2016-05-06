<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// event数据
	"DROP TABLE IF EXISTS `{$db_prefix}event`;",
        "DROP TABLE IF EXISTS `{$db_prefix}event_type;",
        "DROP TABLE IF EXISTS `{$db_prefix}event_user;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_news;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_img;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_flash;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_orga;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_vote;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_note;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_school;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'event'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `type` = 'event';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'event';",
);

foreach ($sql as $v)
	M('')->execute($v);