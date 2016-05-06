<?php
if (!defined('SITE_PATH')) exit();


$sql = array(
	// document数据
	"DROP TABLE IF EXISTS `ts_check_in`;",
	"DROP TABLE IF EXISTS `ts_check_in_total`;",
	"DROP TABLE IF EXISTS `ts_check_in_type`;",
);

foreach ($sql as $v)
	M('')->execute($v);