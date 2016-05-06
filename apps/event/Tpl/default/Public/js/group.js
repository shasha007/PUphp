//关注状态
function followState(type,target,uid){
	target = target || 'dofollow';
	uid    = uid    || _UID_;
	if(type=='havefollow'){
		html = '<div class="btn_relation"><span>已关注&nbsp;&nbsp;|&nbsp;&nbsp;</span><a href="javascript:void(0);" onclick="'+target+'(\'unflollow\',\''+target+'\','+uid+')">取消</a></div>';
	}else if(type=='eachfollow'){
		html = '<div class="btn_relation btn_relation2"><span>互相关注&nbsp;&nbsp;|&nbsp;&nbsp;</span><a href="javascript:void(0);" onclick="'+target+'(\'unflollow\',\''+target+'\','+uid+')">取消</a></div>';
	}else{
		if(uid != 0){
		html = '<a class="add_atn" href="javascript:void(0);" onclick="'+target+'(\'dofollow\',\''+target+'\','+uid+')">加关注</a>';
		}
		if(uid == 0){
		html = '';
		}
	}
	return html;
}


group_info = function(){};
group_info.prototype = {
	$input_tags:'',
	init:function()
	{
		this.$input_tags = $('input[name="tags"]');
	},
	text_length:function(o, length)
	{
		$o = $(o);
		if (getLength($o.val()) > length) {
			$('#group_' + $o.attr('name') + '_tips').html('不能超过' + length + '个字');
		} else {
			$('#group_' + $o.attr('name') + '_tips').html('');
		}
	},
	add_tag:function(e)
	{
		var tag = $(e).html().replace(/\s/g, '');
		var tags = this.$input_tags.val();
		if (tags.indexOf(tag) == -1) {
			this.$input_tags.val((tags?(tags.replace(/,$/g, '') + ','):'') + tag);
			this.tag_num();
		}
	},
	tag_num:function()
	{
		var tags	= this.$input_tags.val().split(',');
		var tag_num = tags.length;
		var $tag_change = $('#tags_change');
		var i;
		var _tag_num;
		for (i = 0, _tag_num = 0; i < tag_num; i++) {
			if (tags[i] != '') {
				_tag_num++;
			}
		}
		if (_tag_num > 5) {
			$tag_change.html('添加标签最多可设置五个');
			this.$input_tags.focus();
		} else {
			$tag_change.html('');
		}
		return _tag_num;
	},
	change_verify:function()
	{
	    var date = new Date();
	    var ttime = date.getTime();
	    var url = U('home/Public/verify');
	    $('#verifyimg').attr('src',url+'&'+ttime);
	},
	check_form:function(v_form)
	{
		if (getLength(v_form.intro.value) == 0) {
			ui.error("校园部落简介不能为空");
			v_form.intro.focus();
			return false;
		} else if (getLength(v_form.intro.value) > 300) {
			ui.error("校园部落简介不能超过300个字");
			v_form.intro.focus();
			return false;
		} else if (this.tag_num() >5) {
			ui.error("标签个数请不能超过五个");
			return false;
		}
		return true;
	}
};
group_info = new group_info();


// 加入部落
function joingroup(gid) {
    ui.box.load(U('event/GroupTopic/joinGroup')+'&gid='+gid,{title:'加入校园部落'});
}

// 退出部落
function quitgroup(gid) {
    ui.box.load(U('event/GroupTopic/quitGroupDialog')+'&gid='+gid,{title:'退出校园部落'});
}