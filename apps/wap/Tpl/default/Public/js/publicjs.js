$(document).ready(function () {
    dopicerror();
    $(".app-tan").click(function () {
        $('#myModal').modal('show');
    });

    $("#actionbar-back").click(function () {
        //判断跳转
        if (document.referrer.trim().indexOf("http://")) {
            Android.closeWeb();
        } else {
            history.back();
        }
    });

    ////去部落
    $(document).on("click", ".go-tribe", function () {
        var id = $(this).data("tribeid") + "";
        console.log(id);
        Android.openInternalPage("groupDetails", "", "", id, "", "", "");
    });
    //个人主页
    //$(document).on("click", ".to-user-home", function () {
    //    $(".to-user-home").click(function () {
    //    var id = $(this).data("userid") + "";
    //    alert(id);
    //    Android.openInternalPage("user_home", "", "", id, "", "", "");
    //});
$(".to-user-home").on("click", function () {
        var id = $(this).data("userid") + "";
        Android.openInternalPage("user_home", "", "", id, "", "", "");
})
    //去活动
    //$(document).on("click", ".to-event", function () {
    //    var id = $(this).data("eventid") + "";
    //    Android.openInternalPage("eventDetails", "", "", id, "", "", "");
    //});

    //publish-topic 发布活动
    //$(document).on("click", ".publish-topic", function () {
        $(".publish-topic").click(function () {
        var id = $(this).data("themeid") + "";
        try{
            Android.openInternalPage("weibo", "", "", id, "", "", "");
        }catch (e) {
            setTimeout(function () {
            Android.openInternalPage("weibo", "", "", id, "", "", "");
            },50);
        }
    });

//评论weibo
//    $(document).on("click", "#comment-topic", function () {
        $("#comment-topic").click(function () {
        var id = $(this).data("topicid") + "";
            try {
                Android.openInternalPage("weibo", "", "", id, "", "", "", "2", "");
            }catch (e){
                setTimeout(function () {
                    Android.openInternalPage("weibo", "", "" , id, "", "", "", "2", "");
                },50);
            }

    });

//评论pinglun
    $(document).on("click", ".comment-comment", function () {
        var id = $(this).data("commentid") + "";
        var weiboid = $(this).data("topicid") + "";
        var nickname  =$(this).data("nickname")+"";
        Android.openInternalPage("weibo", "", nickname, weiboid, "", "", "", "3", id);
    });
    //成长服务超市
    $("#market").click(function () {
        Android.openInternalPage("market", "", "", "", "", "", "");
    });
    //充值
    $(".recharger-card").click(function () {
        Android.openInternalPage("charge", "", "", "", "", "", "");
    });
    $("#top-back").click(function () {
        $('body,html').animate({scrollTop:0},0);
    });
});


//处理图片
function dopicerror() {
    $("img").bind("error", function () {
        this.src = "/apps/wap/Tpl/default/Public/images/img_default.png";
    });
}

function openselect(){
        $('#area-selection').modal('show');
}

function settitle (title,url,func){
    var serverurl = "";
    if(url!=""){
        serverurl =  "http://www.pocketuni.net/apps/wap/Tpl/default/Public/images/"+url;
    }

    try{
        Android.setActionBarMenuImage(title,serverurl ,func);
    }catch (e){
        setTimeout(function () {
            try{
                Android.setActionBarMenuImage(title,serverurl ,func);
            }catch (e){
                setTimeout(function () {
                    Android.setActionBarMenuImage(title,serverurl ,func);
                },50);
            }
        },50);
    }
}

function  setgohome (id){
        Android.openInternalPage("user_home", "", "", id, "", "", "");
}