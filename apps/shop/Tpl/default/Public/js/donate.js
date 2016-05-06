function area(type){
    ui.box.load(U('shop/Donate/citySchool')+'&type='+type,{
        title:'请选择'
    });
}

function check(){
    if(!$( 'input[name="title"]' ).val()){
        ui.error("物品名称不能为空");
        return false;
    }
    if($( 'input[name="title"]' ).val().length>30){
        ui.error("物品名称要在30字内");
        return false;
    }
    if(!$( 'input[name="price"]:checked' ).val()){
        ui.error("价格不能为空");
        return false;
    }
    if(!$('input[name="id"]').val() && !$( 'input[name="upfile"]' ).val()){
        ui.error("请上传缩略图");
        return false;
    }
    if(!$( '#catId' ).val()){
        ui.error("类型不能为空");
        return false;
    }
    if(!$( 'input[name="contact"]' ).val()){
        ui.error("联系人不能为空");
        return false;
    }
    if(!$( 'input[name="mobile"]' ).val()){
        ui.error("联系电话不能为空");
        return false;
    }
    return true;
}

function payment(rest){
    var pay = $('.shopT').html()*100;
    if(pay>rest){
        alert('您的账号余额不够，请前往充值');
    }else{
        if(confirm('确定付款!')){
            var id = $('#tgid').val();
            $.post(U('shop/Donate/payment'),{
                id:id
            },function(txt){
                var json =$.parseJSON(txt);
                if( json.status == 1 ){
                    location.replace(U('shop/Donate/success'))
                }else{
                    ui.error( json.info );
                }
            });
        }
    }
}
