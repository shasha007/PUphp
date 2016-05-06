//设置银行卡账号格式
function setAccount(){
	var data=document.getElementById("bankCard");
	var account=data.getAttribute("data-bank");
	for(var i=1;i<13;i++){
		var point=document.createElement("i");
		if(i%4==0){
			point.style.marginRight="7px";
		}
		data.appendChild(point);
	}
	var num=document.createElement("em");
	num.innerHTML=account.substr(12);
	data.appendChild(num);
}
//设定选中还款金额
function repaySum(){
	var repaysum=0;
	var div=document.getElementsByClassName("w-repayGeneral");
	console.log(div)
	var sRepay=document.getElementById("shouldRepay");
	repaysum+=parseInt(div[1].getElementsByClassName("w-repaySum")[0].innerText.substr(2));
	sRepay.innerText=repaysum;
	for(var i=1;i<div.length;i++){
		div[i].addEventListener("click",function(){
			if(this.getElementsByTagName("img")[0].getAttribute("src")=="img/sel01.png"){
				this.getElementsByTagName("img")[0].setAttribute("src","img/sel1.png");
				repaysum-=parseInt(this.getElementsByClassName("w-repaySum")[0].innerText.substr(2));
			}
			else{
				this.getElementsByTagName("img")[0].setAttribute("src","img/sel01.png");
				repaysum+=parseInt(this.getElementsByClassName("w-repaySum")[0].innerText.substr(2));
			}
			sRepay.innerText=repaysum;
		});
	}
}
window.onload=function(){
	//setAccount();
	repaySum();
}
