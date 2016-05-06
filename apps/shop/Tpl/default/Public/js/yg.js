$(document).ready(function(){
    var totalSpan = $('.shopT');
    var menger = $('#txtNum').val();
    $('.moenyCount').html(menger+".00");
    if(!$("#voucher").attr("checked")){
        $('#zjId').val('0');
    }
    if($('#zjId').val()>0){
        totalSpan.html(menger-1);
    }else{
        totalSpan.html(menger);
    }
    var mengerDiv = $('#payNum');
    mengerDiv.html(menger);
    $("#voucher").click(function(){
        if($("#voucher").attr("checked")){
            $.post(U('shop/Yg/voucher'),{id:$('#productId').val()},function(txt){
                var json =$.parseJSON(txt);
                if( json.status == 1 ){
                    ui.success('使用代金券，总价减1PU币');
                    $('#zjId').val(json.info);
                    totalSpan.html($('#txtNum').val()-1);
                }else{
                    ui.error( json.info );
                    $("#voucher").removeAttr("checked");
                }
            });
        }else{
            $('#zjId').val('0');
            totalSpan.html($('#txtNum').val());
        }
    });
    $("#txtNum").keyup(function(){
        $(this).val($(this).val().replace(/[^\d]/g,""));
        var F=$('#restNum').html();//剩余次数
        var PRICE=$('#price').val();
        var money;
        if(($(this).val()-0)>F){
            $('#ygerr').show().html("\u6570\u91cf\u4e0d\u80fd\u5927\u4e8e"+F+"!");
            $(this).val(F);
            money = F*PRICE;
            $('.moenyCount').html(money+".00")
        }else{
            $('#ygerr').hide();
            money = $(this).val()*PRICE;
            $('.moenyCount').html(money+".00")
        }
        if($(this).val()<1||$(this).val()==""){
            $('#ygerr').show().html("\u6570\u91cf\u5fc5\u987b\u5927\u4e8e1!");
            $(this).val("1");
            money = PRICE;
            $('.moenyCount').html(PRICE+".00");
        }
        if($('#zjId').val()>0){
            money -= 1;
        }
        totalSpan.html(money);
        mengerDiv.html($(this).val());
    });
    $("#jia").click(function(){
        var F=$('#restNum').html();//剩余次数
        var I=$('#txtNum');
        var PRICE=$('#price').val();
        if(I.val()*1>=F*1){
            $('#ygerr').show().html("\u6570\u91cf\u4e0d\u80fd\u5927\u4e8e"+F*PRICE+"!");
            return false
        }else{
            $('#ygerr').hide();
            I.val(I.val()*1+1);
            $('.moenyCount').html(I.val()*PRICE+".00");
            tp = I.val()*PRICE;
            if($('#zjId').val()>0){
                tp -= 1;
            }
            totalSpan.html(tp);
            mengerDiv.html(I.val());
        }
    });
    $("#jian").click(function(){
        B=this;
        var H=$('#txtNum');
        var PRICE=$('#price').val();
        if(H.val()==1){
            $('#ygerr').show().html("\u6570\u91cf\u5fc5\u987b\u5927\u4e8e1!");
            return false
        }else{
            $('#ygerr').hide();
            H.val(H.val()-1);
            $('.moenyCount').html(H.val()*PRICE+".00")
            tp = H.val()*PRICE;
            if($('#zjId').val()>0){
                tp -= 1;
            }
            totalSpan.html(tp);
            mengerDiv.html(H.val());
        }
    });
});
function payment(rest){
    var pay = $('.shopT').html()*100;
    if(pay>rest){
        alert('您的账号余额不够，请前往充值');
    }else{
        var id = $('#ygid').val();
        var num = $('#txtNum').val();
        var voucher = 0;
        if($('#zjId').val()>0){
            voucher = $('#zjId').val();
        }
        $.post(U('shop/Yg/payment'),{id:id,num:num,voucher:voucher},function(txt){
            var json =$.parseJSON(txt);
            if( json.status == 1 ){
                location.replace(U('shop/Yg/success'))
            }else{
                ui.error( json.info );
            }
        });
    }
}