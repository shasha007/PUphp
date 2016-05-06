/**
 * Created by huqiwen on 16/3/29.
 */
$(function () {
    $(".h-puj div").click(function () {
        $(".h-puj div").siblings().removeClass("h-puj-on");
        $(this).addClass("h-puj-on")
    });
});