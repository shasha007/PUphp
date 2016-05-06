function post_vote(id,pid,name){
    if(confirm('您确定要把票投给：'+name+'?')){
    $.post(U('/Front/vote'),{id:id,pid:pid},function(txt){
        json = eval('('+txt+')');
        if( json.status == 1 ){
            ui.success( json.info );
            setTimeout(function(){location.reload();},1500);
        }else{
            ui.error( json.info );
        }
    });
    }
}