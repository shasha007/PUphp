function repayStyle(){
	var index=0;
	var li=document.getElementsByClassName("w-payType")[0].getElementsByTagName("li");
	for(var i=0;i<li.length;i++){
		li[i].addEventListener("touchstart",select);
		li[i].index=i;
	}
	function select(){
		li[index].setAttribute("data-checked",false);
		li[index].getElementsByTagName("div")[0].getElementsByTagName("img")[0].style.display="none"
		index=this.index;
		this.setAttribute("data-checked",true);
		this.getElementsByTagName("div")[0].getElementsByTagName("img")[0].style.display="block"
	}
}
window.onload=function(){
	repayStyle();
}
