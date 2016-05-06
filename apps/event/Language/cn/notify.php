<?php

return array(
    'event_add' => array(
        'title' => '{actor}发起了一个活动【' . $title . '】',
        'body' => '&nbsp;&nbsp;&nbsp;&nbsp;只允许TA关注的人来参加。<br /> <div class="quote"><p><span class="quoteR">' . $content . '</span></p></div><br /><a href="' . $url . '" target="_blank">去看看</a>',
    ),
    'event_reactiv' => array(
        'title' => '您发起的活动【' . $title . '】已重新激活。 请在一天内重新编辑截止报名时间',
        'other' => '<a href="' . U('event/Backend/index', array('id' => $event_id, 'uid' => $event_uid)) . '" target="_blank">去看看</a>',
    ),
    'event_audit' => array(
        'title' => '您发起的活动【' . $title . '】通过了审核',
        'other' => '<a href="' . U('event/Front/index', array('id' => $eventId)) . '" target="_blank">去看看</a>',
    ),
    'event_course_audit' => array(
        'title' => '您发起的课程【' . $title . '】通过了审核',
        'other' => '<a href="' . U('event/LessonMember/detail', array('id' => $courseId)) . '" target="_blank">去看看</a>',
    ),
    'event_course_active_audit' => array(
        'title' => '您发起的课程活动【' . $title . '】通过了审核',
        'other' => '<a href="' . U('event/LessonActiveMember/detail', array('id' => $courseId)) . '" target="_blank">去看看</a>',
    ),
    'event_delaudit' => array(
        'title' => '您发起的活动【' . $title . '】被驳回',
        'other' => '原因,' . $reason,
    ),
    'event_delplayer' => array(
        'title' => '您上传的选手资料【' . $title . '】被拒绝',
        'other' => '原因,' . $reason,
    ),
    'event_course_delaudit' => array(
        'title' => '您发起的课程【' . $title . '】被驳回',
        'other' => '原因,' . $reason,
    ),
    'event_course_del' => array(
        'title' => '您发起的课程【' . $title . '】被删除',
    ),
    'event_course_active_del' => array(
        'title' => '您发起的课程活动【' . $title . '】被删除',
    ),
    'event_course_active_delaudit' => array(
        'title' => '您发起的课程活动【' . $title . '】被驳回',
        'other' => '原因,' . $reason,
    ),
    'event_finishback' => array(
        'title' => '您申请完结的活动【' . $title . '】被驳回' . '<br/><a href="' . U('event/Author/finish', array('id' => $eventId)) . '" target="_blank">去看看</a>',
        'other' => '原因,' . $reason,
    ),
    'event_del' => array(
        'title' => '您发起的活动【' . $title . '】被删除',
    ),
    'event_group_init' => array(
        'title' => '您的部落【' . $title . '】等待您的初始化',
        'other' => '<a href="' . U('event/GroupManage/index', array('gid' => $group_id)) . '" target="_blank">去看看</a>',
    ),
    'event_group_delaudit' => array(
        'title' => '您的部落【' . $title . '】申请校方认证，被驳回',
        'other' => '<a href="' . U('group/Manage/validate', array('gid' => $group_id)) . '" target="_blank">去看看</a>',
    ),
    'event_group_nodisband' => array(
        'title' => '您的部落【' . $title . '】申请解散，被驳回',
        'other' => '原因:' . $reason,
    ),
    'event_group_disband' => array(
        'title' => '您的部落【' . $title . '】已被管理员解散',
    ),
    'event_group_topic_reply' => array(
        'title' => '{actor} 回复了您的帖子【' . $title . '】',
        'body' => '回复内容：“' . $content . '”',
        'other' => '<a href="' . U('event/GroupTopic/topic', array('gid' => $gid, 'tid' => $tid)) . '" target="_blank">去看看</a>',
    ),
    'event_group_topic_top'  => array(
		'title' => '{actor} 已将您的帖子【' . $title . '】置顶',
		'other' => '<a href="' . U('event/GroupTopic/topic',array('gid'=>$gid, 'tid'=>$tid)).'" target="_blank">去看看</a>',
	),
    'event_pass' => array(
        'title' => $title,
    ),
    'event_reject' => array(
        'title' => $title,
        'other' => '原因,' . $reason,
    ),
        'event_group_member_out' => array(
        'title' => '您已被管理员剔除【' . $title . '】部落',
    ),
        'event_group_member_no' => array(
        'title' => '您申请加入的部落【' . $title . '】被管理员拒绝',
    ),
    'event_group_member' => array(
        'title' => '您已成功加入【' . $title . '】部落',
        'other' => '<a href="' . U('event/GroupTopic/index', array('gid' => $group_id)) . '" target="_blank">去看看</a>',
    ),
    'event_finish_error' => array(
        'title' => '您参加的活动【' . $title . '】 经老师审核，已完结但不发放学分和积分',
    ),
    'event_warning' => array(
        'title' => '诚信系统 - 警告',
        'body' => '您报名但未签到的活动已满' . $absent . '次。请注意,满'.$times.'次将禁止参加所有活动'.$day.'天'
    ),
    'event_stop' => array(
        'title' => '诚信系统 - 惩罚',
        'body' => '您报名但未签到的活动已满' . $absent . '次。将禁止参加所有活动'.$day.'天！'
    ),
    'event_ec_pass' => array(
        'title' => $creditName.'申请，通过审核',
        'body' => '您申请'.$creditName.' "' . $title . '" 已通过审核，发放'.$credit.$creditName,
    ),
    'event_ec_reject' => array(
        'title' => $creditName.'申请，被驳回',
        'body' => '您申请'.$creditName.' "' . $title . '" 被驳回',
        'other' => '原因,' . $reason,
    ),
    'event_g_ann' => array(
        'title' => "校园部落【{$gName}】通知公告",
        'body' => $content,
    ),
);
?>