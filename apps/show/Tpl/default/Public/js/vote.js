function post_vote(pid,name){
    if(confirm('您确定要把今天的票投给：'+name+'?')){
    $.post(U('show/player/vote'),{pid:pid},function(txt){
        if(txt){
            if( -1 == txt ){
                ui.error( "你今天已经投过 ["+name+"] 一次了!" );
            }else if( -3 == txt ){
                ui.error( "请先登录!" );
                setTimeout(function(){window.location.href=U('index/index');},1500);
            }else if( -2 == txt ){
                ui.error( "投票失败，选手不存在或已删除!" );
            }else if( -4 == txt ){
                ui.error( "投票失败，该选手已终止投票!" );
            }else{
                ui.success( "投票成功!" );
                setTimeout(function(){location.reload();},1500);
            }
        }
    });
    }
}