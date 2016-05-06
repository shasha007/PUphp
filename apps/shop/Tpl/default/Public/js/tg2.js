function payment2(rest){
    var pay = $('#pay').val();
    if(pay>rest){
        alert('您的账号余额不够，请前往充值');
    }else{
        var id = $('#order_id').val();
        $.post(U('shop/Myshop/doTgPay'),{id:id},function(txt){
            var json =$.parseJSON(txt);
            if( json.status == 1 ){
                location.replace(U('shop/Myshop/success2')+'&id='+id)
            }else{
                ui.error( json.info );
            }
        });
    }
}