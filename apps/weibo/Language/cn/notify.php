<?php
return  array(
	'weibo_reply'   => array(
		'title' => '{actor} 回复了你的'.( ($reply_type=='weibo')?'话题':'评论' ),
		'body'  => $content,
		'other' =>'<a href="'.U('home/space/detail',array('id'=>$weibo_id)).'" target="_blank">去看看</a>',
	),
	'weibo_follow'   => array(
		'title' => '{actor} 已加你为好友',
		'other' =>'<span class="right" id="follow_list_'.$from.'"><script>document.write(followState(\''.getFollowState($receive, $from).'\',\'dolistfollow\','.$from.'))</script></span><a href="'.U('home/space/index',array('uid'=>$from)).'" target="_blank">去TA空间</a>'
		/**'other' =>'<a href="'.U('home/space/index',array('uid'=>$from)).'" target="_blank">去TA空间</a></title>'**/
	),
	'weibo_atme'   => array(
		'title' => '{actor} 在话题中提到了你',
		'body'  =>'<a href="'.U('home/space/detail',array('id'=>$weibo_id)).'" target="_blank">'.$content.'</a>',
	),
	'weibo_heart' =>array(
		'title' => '{actor} 点赞了你的话题',
		'body' => '<a href="'.U('home/space/detail',array('id'=>$weibo_id)).'" target="_blank">'.$content.'</a>',
	),
	'weibo_comment'=>array(
		'title' => '{actor} 评论了你的话题',
		'body' => '<a href="'.U('home/space/detail',array('id'=>$weibo_id)).'" target="_blank">'.$content.'</a>',
	),

);