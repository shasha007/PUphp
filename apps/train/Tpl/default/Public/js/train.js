function gbcount(o,max){
    var msg = $(o).val();
    var rest = max - msg.length;
    if(rest < 0){
        rest = 0;
        $('#remain').html(0);
        $(o).val(msg.substring(0,max));
        alert('不能超过'+max+'个字!');
    }
    $('#remain').html(rest);
}

function check(){
    var title      = $( '#title' ).val();
    var cost    = $( '#cost' ).val();
    var province    = $( '#province' ).val();
    var city    = $( '#city' ).val();
    var address    = $( '#address' ).val();
    var org    = $( '#org' ).val();
    var cat    = $( '#cat' ).val();
    var textarea  = $('#textarea').val();
    var contact  = $('#contact').val();
    if(!title || getLength(title.replace(/\s+/g,"")) == 0){
        ui.error("课程名称不能为空");
        return false;
    }
    if( title.length>30 ) {
        ui.error( '课程名称最大30个字符' );
        return false;
    }
    if(!textarea){
        ui.error("课程介绍不能为空");
        return false;
    }
    if(!cost){
        ui.error("学习费用不能为空");
        return false;
    }
    if(province<1){
        ui.error("所在地区省份不能为空");
        return false;
    }
    if(city<1){
        ui.error("所在地区城市不能为空");
        return false;
    }
    if(!address){
        ui.error("授课地点不能为空");
        return false;
    }
    if(org<1){
        ui.error("机构不能为空");
        return false;
    }
    if(cat<1){
        ui.error("分类不能为空");
        return false;
    }
    if( !contact ) {
        ui.error( '联系方式不能为空' );
        return false;
    }
    return true;
}