-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2015 年 01 月 28 日 09:28
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
-- 表的结构 `ts_job_industry`
--

CREATE TABLE IF NOT EXISTS `ts_job_industry` (
  `industry_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '行业ID',
  `industry_pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `industryname` varchar(255) NOT NULL DEFAULT '' COMMENT '行业名',
  PRIMARY KEY (`industry_id`),
  KEY `industry_pid` (`industry_pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='行业' AUTO_INCREMENT=173 ;

--
-- 转存表中的数据 `ts_job_industry`
--

INSERT INTO `ts_job_industry` (`industry_id`, `industry_pid`, `industryname`) VALUES
(1, 0, '互联网·游戏·软件'),
(2, 1, '互联网/移动互联网/电子商务'),
(3, 1, '计算机软件'),
(4, 1, 'IT服务/系统集成'),
(5, 1, '网络游戏'),
(6, 0, '电子·通信·硬件'),
(7, 6, '电子技术/半导体/集成电路'),
(8, 6, '通信(设备/运营/增值)'),
(9, 0, '房地产·建筑·物业'),
(10, 9, '房地产开发/建筑/建材/工程'),
(11, 9, '规划/设计/装潢'),
(12, 9, '房地产服务(物业管理/地产经纪)'),
(13, 6, '计算机硬件/网络设备'),
(14, 0, '金融'),
(15, 14, '银行'),
(16, 14, '保险'),
(17, 14, '基金/证券/期货/投资'),
(18, 14, '会计/审计'),
(19, 14, '信托/担保/拍卖/典当'),
(20, 0, '消费品'),
(21, 20, '食品/饮料/烟酒/日化'),
(22, 20, '百货/批发/零售'),
(23, 20, '服装服饰/纺织/皮革'),
(24, 20, '家具/家电'),
(25, 20, '办公用品及设备'),
(26, 20, '奢侈品/收藏品'),
(27, 20, '工艺品/珠宝/玩具'),
(28, 0, '汽车·机械·制造'),
(29, 28, '汽车/摩托车\r\n汽车/摩托车'),
(30, 28, '机械制造/机电/重工'),
(31, 28, '印刷/包装/造纸'),
(32, 28, '原材料及加工'),
(33, 28, '仪器/仪表/工业自动化/电气'),
(34, 0, '服务·外包·中介'),
(35, 34, '专业服务(咨询/财会/法律/翻译等)'),
(36, 34, '中介服务'),
(37, 34, '外包服务'),
(38, 34, '检测/认证'),
(39, 34, '旅游/酒店/餐饮服务/生活服务'),
(40, 34, '娱乐/休闲/体育'),
(41, 34, '租赁服务'),
(42, 0, '广告·传媒·教育·文化'),
(43, 42, '广告/公关/市场推广/会展'),
(44, 42, '影视/媒体/艺术/文化/出版'),
(45, 42, '教育/培训/学术/科研/院校'),
(46, 0, '交通·贸易·物流'),
(47, 46, '交通/物流/运输'),
(48, 46, '贸易/进出口'),
(49, 46, '航空/航天'),
(50, 0, '制药·医疗'),
(51, 50, '制药/生物工程'),
(52, 50, '医疗/保健/美容/卫生服务'),
(53, 50, '医疗设备/器械'),
(54, 0, '能源·化工·环保'),
(55, 54, '能源(电力/水利)'),
(56, 54, '石油/石化/化工'),
(57, 54, '采掘/冶炼/矿产'),
(58, 54, '环保'),
(59, 54, '新能源'),
(60, 0, '政府·农林牧渔\r\n'),
(61, 60, '政府/公共事业/非营利机构'),
(62, 60, '农/林/牧/渔');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
