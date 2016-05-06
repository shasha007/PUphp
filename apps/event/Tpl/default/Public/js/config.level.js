function newLevel() {
    if($('#newLevel').val()){
        ui.error('请先保存新添加的');
        return;
    }
    var content = '<tr><td>级别名称：<input id="levelName" type="text" value="" size="8"></td>';
    var i = 1;
    while (i <= typeCnt) {
        content += '<td>默认学分：<input id="credit' + i + '" type="text" value="0" size="5" onkeyup="clearNoNum(this)" ></td>';
        i++;
    }
    content += '<td ><input id="newLevel" class="btn_a" value="保存" onclick="addLevel(this);" type="button">'
            + '<input class="btn_a" value="取消" onclick="cancelNewLevel(this);" type="button"></td>'
            + '<td><font id="status"></font></td></tr>';
    $('#mark').before(content);
}
function cancelNewLevel(obj) {
    $(obj).parent().parent().remove();
}
function addLevel(obj) {
    var levelName = $(obj).parent().parent().find('#levelName').val();
    if(getLength(levelName.replace(/\s+/g,"")) == 0){
	 alert("级别名称不能为空");
	 return false;
    }
    if(levelName.length >5) {
      alert("级别名称不能超过5个字");
      return false;
    }
    var i = 1;
    var credit = '';
    while (i <= typeCnt) {
        credit += $(obj).parent().parent().find('#credit' + i).val() + '|';
        i++;
    }
    $.ajax({type: "POST",url: _URL_+"&act=addLevel",timeout: 20000,data: "title=" + levelName + "&credit=" + credit,
        beforeSend: function () {
            $(obj).attr('disabled', true);
        },
        success: function (response) {
            $(obj).attr('disabled', false);
            var json = $.parseJSON(response);
            if (json.status == 1) {
                ui.success(json.info);
                window.location.reload();
            } else {
                ui.error(json.info);
            }
        },
        error: function () {
            $(obj).attr('disabled',false);
            ui.error('网络繁忙稍后再试');
        }
    });
}
function toEdit(obj) {
    $(obj).parent().parent().find('td').each(function (i) {
        $(this).find('input').attr('disabled', false);
    });
    $(obj).parent().parent().css("background", "#ffffcc");
    $(obj).parent().parent().find("input").removeAttr("readonly");
    $(obj).parent().parent().find("select").removeAttr("disabled");
    var content = '<td><input type="button" class="btn_a" onclick="editRecord(this)" value="保存"><input onclick="unEdit(this)" type="button" class="btn_a" value="取消"></td>';
    $(obj).parent().after(content);
    $(obj).parent().remove();
}
function editRecord(obj){
    var id         = $(obj).parent().parent().attr('id');
    var levelName = $(obj).parent().parent().find('#title').val();
    if(getLength(levelName.replace(/\s+/g,"")) == 0){
	 alert("级别名称不能为空");
	 return false;
    }
    if(levelName.length >5) {
      alert("级别名称不能超过5个字");
      return false;
    }
    var i = 0;
    var credit = '';
    while (i < typeCnt) {
        credit += $(obj).parent().parent().find('#credit_' + i).val() + '|';
        i++;
    }
    $.ajax({type: "POST",url: _URL_+"&act=editLevel",timeout: 20000,data: "id=" + id +"&title=" + levelName + "&credit=" + credit,
        beforeSend: function () {
            $(obj).attr('disabled', true);
        },
        success: function (response) {
            var json = $.parseJSON(response);
            if (json.status == 1) {
                $(obj).parent().parent().find('#title').attr('disabled',true);
                $(obj).parent().parent().find('#title').attr('value',json.info);

                $.each(json.data, function(i, item){
                    $(obj).parent().parent().find('#credit_'+i).attr('disabled',true);
                    $(obj).parent().parent().find('#credit_'+i).attr('value',item);
                });
                var content = '<td><input class="btn_a" value="修改" onclick="toEdit(this)" type="button"><input class="btn_a" value="删除" onclick="toDelete(this)" type="button"></td>';
                $(obj).parent().after(content);
                $(obj).parent().parent().css("background","#ffffff"); 
                $(obj).parent().remove();
                $(obj).attr('disabled', false);
            } else {
                ui.error(json.info);
            }
        },
        error: function () {
            $(obj).attr('disabled',false);
            ui.error('网络繁忙稍后再试');
        }
    });
}
function unEdit(obj){
    var id = $(obj).parent().parent().attr('id');
$.ajax({type: "POST",url: _URL_+'&act=getLevelById',timeout:20000,data:"id="+id,
    beforeSend:function(){$(obj).attr('disabled',true);},
    success: function(response){
        $(obj).attr('disabled',false);
        var json = $.parseJSON(response);
        if(json.status == 1){
            $(obj).parent().parent().find('#title').attr('disabled',true);
            $(obj).parent().parent().find('#title').attr('value',json.info);

            $.each(json.data, function(i, item){
                $(obj).parent().parent().find('#credit_'+i).attr('disabled',true);
                $(obj).parent().parent().find('#credit_'+i).attr('value',item);
            });
            var content = '<td><input class="btn_a" value="修改" onclick="toEdit(this)" type="button"><input class="btn_a" value="删除" onclick="toDelete(this)" type="button"></td>';
            $(obj).parent().after(content);
            $(obj).parent().parent().css("background","#ffffff"); 
            $(obj).parent().remove();
        }else {
            ui.error(json.info);
        }	                		
    },
    error: function () {
        $(obj).attr('disabled',false);
        ui.error('网络繁忙稍后再试');
    }
});
}
function toDelete(obj){
    if(confirm('删除后记录将不能恢复，但不影响现有活动，确认删除吗？')){
        deleteOne(obj);
    }
}
function deleteOne(obj){
    var id = $(obj).parent().parent().attr('id');
    if(id != null){
    $.ajax({type: "POST",url: _URL_+"&act=delLevel",timeout: 20000,data:"id="+id,
        beforeSend:function(){$(obj).attr('disabled',true);},
        success: function(response){
            $(obj).attr('disabled',false);
            var json = $.parseJSON(response);
            if(json.status == 1){
                $('#'+id).remove();
            }else{
                ui.error(json.info);
            }
        },
        error:function(){
            $(obj).attr('disabled',false);
            ui.error('网络繁忙稍后再试');
        }
    });
    }
}
function activeLevel(obj){
    $.ajax({type: "POST",url: _URL_+"&act=activeLevel",timeout: 20000,
        beforeSend:function(){$(obj).attr('disabled',true);},
        success: function(response){
            $(obj).attr('disabled',false);
            var json = $.parseJSON(response);
            if(json.status == 1){
                $('#activ').html(json.info);
            }else{
                ui.error(json.info);
            }
        },
        error:function(){
            $(obj).attr('disabled',false);
            ui.error('网络繁忙稍后再试');
        }
    });
}