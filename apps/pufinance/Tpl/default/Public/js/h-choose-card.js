/**
 * Created by huqiwen on 16/3/29.
 */
$(function () {
    $(".h-choose-card section").click(function () {
        $(".h-is-choose").hide();
		var item = $(this).data('item');
        $(this).children(".h-is-choose").show();
		if(!!item){
			window.location.href=URL+'&id='+item;
		}
    });
});