$(function () {
    $(".to-user-home").click(function(){
        var userid = $(this).data("userid")+"";
        Android.openInternalPage("user_home", "", "",userid, "","","" );
    });
});
