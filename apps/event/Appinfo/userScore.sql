DROP TABLE IF EXISTS `ts_user_scoreout`;
CREATE TABLE `ts_user_scoreout` (
`uid`  int unsigned NOT NULL,
`score_out` int unsigned NOT NULL,
`ctime`  int(11) NULL DEFAULT NULL
) ENGINE=INNODB DEFAULT CHARSET=utf8 comment '活动积分消费记录';

DROP TABLE IF EXISTS `ts_user_score`;
CREATE TABLE `ts_user_score` (
`uid`  int unsigned NOT NULL,
`sid` int unsigned NOT NULL,
`score` int unsigned NOT NULL,
`ctime`  int(11) NULL DEFAULT NULL,
PRIMARY KEY  (`uid`),
 KEY  (`sid`),
 KEY  (`score`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 comment '剩余活动积分';

Alter table ts_user_score partition by hash(uid) partitions 10;

ALTER TABLE `ts_user` DROP `school_event_score`;
ALTER TABLE `ts_user` DROP `school_event_score_used`;
