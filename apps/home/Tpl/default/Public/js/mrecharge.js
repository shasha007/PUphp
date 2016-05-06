$(function () {
    var H = $("#submit_ok");
    var E = $("#Money");
    var G = $("#otherMoney");
    $(".mradio").click(function () {
        E.html($(this).val());
        G.val("")
    });
    $("#otherMoney").click(function () {
        E.html("0")
    });
    var B = function () {
        var I = G.val();
        if (I == "") {
            E.html(0)
        } else {
            E.html(I)
        }
    };
    G.focus(function () {
        $("#money5").attr("checked", "checked");
        B()
    }).bind("keyup", function (J) {
        B();
    });
    H.click(function () {
        var I = E.html();
        if (isNaN(parseInt(I)) || I == "0") {
            alert("请选择或输入充值金额!");
            return false
        }
        $("#hidMoney").val(I);
        return true;
    })
});