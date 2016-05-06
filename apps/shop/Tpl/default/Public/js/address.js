var divId = ['id','city','shipAddress','shipZip','shipName','shipMobile','shipTel'];
function checkAdd(){
    if(addNum<4){
        editAdd('');
    }
    else{
        ui.error( '对不起,最多只能录入4个收货地址,请修改其中某个收货地址' );
    }
}
function editAdd(id){
    if(id!=''){
        $.each(list,function(i,item){
            if(id==item.address_id){
                B={"id":item.address_id,"city":item.city,"shipAddress":""+item.shipAddress+"","shipZip":""+item.shipZip+"","shipName":""+item.shipName+"","shipMobile":""+item.shipMobile+"","shipTel":""+item.shipTel+""}
            }
        });
    }
    $.each(divId,function(i,item){
        if(id==''){
            $('#'+item).val('');
        }else{
            $('#'+item).val(B[item]);
        }
    });
    $("#listDiv").hide();
    $("#addButt").hide();
    $("#addDiv").show();
}
function cancelAdd(){
    $("#listDiv").show();
    $("#addButt").show();
    $("#addDiv").hide();
}
function delAdd(id){
    if(confirm('您确定要删除此配送地址吗？')){
        $.post(U('shop/Myshop/delAdd'),{id:id},function(txt){
            if( txt == 1 ){
                $('#list'+id).remove();
                addNum-=1;
            }else{
                ui.error('删除失败');
            }
        });
    }
}