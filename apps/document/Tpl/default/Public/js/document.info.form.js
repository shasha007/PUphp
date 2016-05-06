document_info = function(){};
document_info.prototype = {
	$input_tags:'',
	init:function()
	{
		this.$input_tags = $('input[name="tags"]');
	},
	text_length:function(o, length)
	{
		$o = $(o);
		if (getLength($o.val()) > length) {
			$('#document_' + $o.attr('name') + '_tips').html('不能超过' + length + '个字');
		} else {
			$('#document_' + $o.attr('name') + '_tips').html('');
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
		if (getLength(v_form.name.value) == 0) {
			ui.error("文档名称不能为空");
			v_form.name.focus();
			return false;
		} else if (getLength(v_form.name.value) > 20) {
			ui.error("文档名称不能超过20个字");
			v_form.name.focus();
			return false;
		} else if (v_form.school0.value <= 0) {
			ui.error("请选择学校");
			v_form.school0.focus();
			return false;
		} else if (v_form.cid0.value <= 0) {
			ui.error("请选择文档分类");
			v_form.cid0.focus();
			return false;
		} else if (getLength(v_form.intro.value) > 60) {
			ui.error("文档简介不能超过60个字");
			v_form.intro.focus();
			return false;
		} else if (getLength(v_form.verify.value) == 0) {
			ui.error("请输入验证码！");
			v_form.verify.focus();
			return false;
		}
		return true;
	}
};
document_info = new document_info();