-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2015 年 01 月 23 日 02:27
-- 服务器版本: 5.1.70-community
-- PHP 版本: 5.2.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `2012xyhui`
--

-- --------------------------------------------------------

--
-- 表的结构 `ts_hat_province`
--

CREATE TABLE IF NOT EXISTS `ts_hat_province` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `provinceID` varchar(6) DEFAULT NULL,
  `province` varchar(40) DEFAULT NULL,
  `order` varchar(255) NOT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='省份' AUTO_INCREMENT=35 ;

--
-- 转存表中的数据 `ts_hat_province`
--

INSERT INTO `ts_hat_province` (`id`, `provinceID`, `province`, `order`) VALUES
(1, '110000', '北京市', 'BJS'),
(2, '120000', '天津市', 'TJS'),
(3, '130000', '河北省', 'HBS'),
(4, '140000', '山西省', 'SXS'),
(5, '150000', '内蒙古自治区', 'NMGZZQ'),
(6, '210000', '辽宁省', 'LNS'),
(7, '220000', '吉林省', 'JLS'),
(8, '230000', '黑龙江省', 'HLJS'),
(9, '310000', '上海市', 'SHS'),
(10, '320000', '江苏省', 'JSS'),
(11, '330000', '浙江省', 'ZJS'),
(12, '340000', '安徽省', 'AHS'),
(13, '350000', '福建省', 'FJS'),
(14, '360000', '江西省', 'JXS'),
(15, '370000', '山东省', 'SDS'),
(16, '410000', '河南省', 'HNS'),
(17, '420000', '湖北省', 'HBS'),
(18, '430000', '湖南省', 'HNS'),
(19, '440000', '广东省', 'GDS'),
(20, '450000', '广西壮族自治区', 'GXZZZZQ'),
(21, '460000', '海南省', 'HNS'),
(22, '500000', '重庆市', 'ZQS'),
(23, '510000', '四川省', 'SCS'),
(24, '520000', '贵州省', 'GZS'),
(25, '530000', '云南省', 'YNS'),
(26, '540000', '西藏自治区', 'XCZZQ'),
(27, '610000', '陕西省', 'SXS'),
(28, '620000', '甘肃省', 'GSS'),
(29, '630000', '青海省', 'QHS'),
(30, '640000', '宁夏回族自治区', 'NXHZZZQ'),
(31, '650000', '新疆维吾尔自治区', 'XJWWEZZQ'),
(32, '710000', '台湾省', 'TWS'),
(33, '810000', '香港特别行政区', 'XGTBXZQ'),
(34, '820000', '澳门特别行政区', 'AMTBXZQ');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
