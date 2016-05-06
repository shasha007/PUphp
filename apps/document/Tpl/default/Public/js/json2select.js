

;(function($) {
$.fn.json2select=function(json,dft,name,deep) {
	//参数初始化
	var _this=this,				//保存呼叫的对象
		name=name||"sel",		//如果未提供名字，则为默认为sel
		deep=deep||0,			//深度，默认为0，即生成的select的name=sel0
		dft=dft||[];			//默认值
	//换内容的时候删除旧的select
	$("[name="+name+deep+"]",_this).nextAll().remove();
	if (json[0]) {
		//新建一个select
		var slct=$("<select name='"+name+$("select",_this).length+"'></select>");
		//建立一个默认项，value为空，修改请保留为空
		$("<option value=''>请选择</option>").appendTo(slct);
		$.each(json,function(i,sd) {
			//添加项目，并用data将其子元素附加在这个option上以备后用。
			$("<option value='"+sd.a+"'>"+sd.t+"</option>").appendTo(slct).data("d",sd.d||[]);
		});
		//绑定这个select的change事件
		slct.change(function(e,dftflag) {
			//如果选的不是value为空的，则调用方法本身。如果已经初始化过了,即，不是由trigger触发的，而是手工点的，则不将dft传递进去。
			$(this).val() && _this.json2select($(":selected",this).data("d"),dftflag?dft.slice(1):[],name,$(this).attr("name").match(/\d+/)[0]);
			//设置初始值，并且触发change事件，传递true参数进去。
		}).appendTo(_this).val(dft[0]||'').trigger("change",[true]);
	}
	//返回jQuery对象
	return _this;
};

})(jQuery);