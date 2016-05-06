/**
 * Created by huqiwen on 16/3/29.
 */
//设置密码
//如果点击的是确认密码则无法弹出输入框
var str = str2 = "";
var pw = document.getElementById("password");
//show高度
var showH = $('.h-PU-show').height();
//密码盘高度
var pswH = $('#keyboard').height();
function setPassword(obj) {
    if (obj.getAttribute("data-inputable")) {
        var kb = document.getElementById("keyboard");
        $('.h-PU-show').css("top",(window.screen.height-showH-pswH)/2+"px");
        kb.style.bottom = 0;
        for (var i = 0; i < kb.getElementsByTagName("td").length; i++) {
            kb.getElementsByTagName("td")[i].addEventListener("touchstart", set);
        }
    }
}

function set() {
    var kb = document.getElementById("keyboard");
    //var show = document.getElementByClassName("h-PU-show");
    if (!this.getAttribute("data-delete")) {
        str += this.innerText;
        if (str.length == 6) {
            kb.style.bottom = "-225px";
            $('.h-PU-show').css("top","20%");
			check(str);
        }
        for (var i = 0; i < str.length; i++) {
            pw.getElementsByTagName("li")[i].innerText = "•";
        }

    }
    else {
        str = str.substring(0, str.length - 1);
        pw.getElementsByTagName("li")[str.length].innerText = "";
    }
}
$(function () {
	var amount = $('#amount').html();
    $('.h-withdraw-input').keyup(function () {
		if(parseFloat($(this).val()) > parseFloat(amount)){
			$('.h-tixian').attr({"disabled":true,"id":"h-disabled-disnone"});
			alert('你的钱包没有这么多钱呀！');
			return false;
		}
        if($(this).val()==''){
            $('.h-tixian').attr({"disabled":true,"id":"h-disabled-disnone"});
        }else{
            $('.h-tixian').attr({"disabled":false,"id":""});
        }
    });
    $('.h-tixian').click(function () {
		if(parseFloat($('.h-withdraw-input').val()) <= 0){
			alert('提现金额大于0');
			return false;
		}
        $('.h-withdraw-show-num').html($('.h-withdraw-input').val());
        $('.h-PU-show').show();
        $('.h-back-cover').show();
    });
    $('.h-withdraw-all').click(function () {
		if(parseFloat($('.h-withdraw-number').html()) <= 0){
			alert('提现金额大于0');
			return false;
		}
        $('.h-withdraw-show-num').html($('.h-withdraw-number').html());
		$('input.h-withdraw-input').val($('.h-withdraw-number').html());
        $('.h-PU-show').show();
        $('.h-back-cover').show();
    });
})

function CloseShow(){
    str = str2 = "";
    $('.h-PU-show').hide();
    $('.h-back-cover').hide();
    pw.setAttribute("data-pw", "");
    $('#password li').html('');
    $('#keyboard').css('bottom','-225px')
}

function withdraw(obj){
    obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
    obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字而不是
    obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
    obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
    obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
}

function check(str){
	$.post(
		URL,
		{str:str,amount:$('.h-withdraw-input').val()},
		function(res){
			if(res.code == 1){
				console.log(res);
				window.location.href = SUCCESS_URL;
			}else{
				alert('密码有误，请重试！');
			}
		},'json'
	);
}