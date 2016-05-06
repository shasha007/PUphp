$(document).ready(function(){
    var totalSpan = $('.shopT');
    var menger = $('#txtNum').val();
    var PRICE=$('#price').val();
    var tmoney = menger*PRICE;
    $('.moenyCount').html(tmoney+".00");
    totalSpan.html(tmoney);
    var mengerDiv = $('#payNum');
    mengerDiv.html(menger);
    $("#txtNum").keyup(function(){
        $(this).val($(this).val().replace(/[^\d]/g,""));
        var money = $(this).val()*PRICE;
        $('.moenyCount').html(money+".00")
        if($(this).val()<1||$(this).val()==""){
            $(this).val("1");
            money = PRICE;
            $('.moenyCount').html(PRICE+".00");
        }
        totalSpan.html(money)
        mengerDiv.html($(this).val());
    });
    $("#jia").click(function(){
        var I=$('#txtNum');
        I.val(I.val()*1+1);
        $('.moenyCount').html(I.val()*PRICE+".00")
        totalSpan.html(I.val()*PRICE)
        mengerDiv.html(I.val());
    });
    $("#jian").click(function(){
        var H=$('#txtNum');
        if(H.val()==1){
            return false
        }else{
            H.val(H.val()-1);
            $('.moenyCount').html(H.val()*PRICE+".00")
            totalSpan.html(H.val()*PRICE)
            mengerDiv.html(H.val());
        }
    });
});
function payment(rest){
    var pay = $('.shopT').html()*100;
    if(pay>rest){
        alert('您的账号余额不够，请前往充值');
    }else{
        if(confirm('定金恕不退还，开团后只需支付余额。确定付款？')){
            var id = $('#tgid').val();
            var num = $('#txtNum').val();
            $.post(U('shop/Tg/payment'),{id:id,num:num},function(txt){
                var json =$.parseJSON(txt);
                if( json.status == 1 ){
                    location.replace(U('shop/Tg/success'))
                }else{
                    ui.error( json.info );
                }
            });
        }
    }
}