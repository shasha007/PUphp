/**
 * Created by huqiwen on 16/3/29.
 */

//设置密码
//如果点击的是确认密码则无法弹出输入框
var str = str2 = "";
var pw = document.getElementById("password");

function setPassword(obj) {
    if (obj.getAttribute("data-inputable")) {
        var kb = document.getElementById("keyboard");
        kb.style.bottom = 0;
        for (var i = 0; i < kb.getElementsByTagName("td").length; i++) {
            kb.getElementsByTagName("td")[i].addEventListener("touchstart", set);
        }
    }
}

function set() {
    var kb = document.getElementById("keyboard");
    if (!this.getAttribute("data-delete")) {
        str += this.innerText;
        if (str.length == 6) {
            kb.style.bottom = "-225px";
            pw.setAttribute("data-pw", str);
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

function check(str){
	$.post(
		URL,
		{str:str},
		function(res){
			if(res.code == 1){
				window.location.href=SUCCESS_URL;
			}else{
				alert('密码有误，请重试！');
			}
		},'json'
	);
}

