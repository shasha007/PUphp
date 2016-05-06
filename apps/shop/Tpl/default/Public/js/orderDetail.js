$(function(){
    var I="";
    //确认收货地址
    $("#setOrderAddress").click(function(){
        $("input[name=address_id]").each(function(){
            if($(this).attr("checked")){
                I=$(this).val()
            }
        });
        if(I==""){
            alert("请选择收货地址");
            return false
        }
        setDisabled();
        var oid = $("#oid").val();
        $.post(U('shop/Myshop/setOrderAddress'),{aid:I,oid:oid},function(txt){
            var json =$.parseJSON(txt);
            if( json.status == 1 ){
                ui.success(json.info);
                setTimeout(function(){location.reload();},1000);
            }else{
                ui.error( json.info );
                unDisabled();
            }
        });
    });

    $("#recevier").click(function(){
        var oid = $("#oid").val();
        $.post(U('shop/Myshop/setOrderOver'),{oid:oid},function(txt){
            if( txt == 1 ){
                ui.success('确认收货成功');
                setTimeout(function(){location.reload();},1000);
            }else{
                ui.error('确认收货失败');
                unDisabled();
            }
        });
    });
});

function setDisabled(){
    $("#setOrderAddress").attr("disabled","disabled").css("cursor","wait")
}
function unDisabled(){
    $("#setOrderAddress").removeAttr("disabled").css("cursor","pointer")
}
function addAdd(){
    var oid = $("#oid").val();
    ui.box.load(U('shop/Myshop/addAdd')+'&oid='+oid,{title:'添加地址'});
}