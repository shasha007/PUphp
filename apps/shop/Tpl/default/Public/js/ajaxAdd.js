var divId = ['city','shipAddress','shipZip','shipName','shipMobile'];
var divName = ['所在地区','街道地址','邮政编码','收货人','手机号码'];
function setAdd(){
    var postData={};
    $.each(divId,function(i,item){
        var va = $('#'+item).val();
        if(!va){
            ui.error(divName[i]+' 不能为空');
            postData = false;
            return false;
        }
        postData[item] = va;
    });
    if(postData==false){
        return;
    }
    postData['shipTel'] = $('#shipTel').val();
    postData['oid'] = $('#oid').val();
    setDisabled();
    $.post(U('shop/Myshop/ajaxAddress'),postData,function(txt){
        var json =$.parseJSON(txt);
        if( json.status == 1 ){
            ui.success(json.info);
            setTimeout(function(){location.reload();},1000);
        }else{
            ui.error( json.info );
            unDisabled();
        }
    });
}
function setDisabled(){
    $("#setOrderAddress").attr("disabled","disabled").css("cursor","wait")
}
function unDisabled(){
    $("#setOrderAddress").removeAttr("disabled").css("cursor","pointer")
}