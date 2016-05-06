// 加入社团
function joingroup(gid) {
    ui.box.load(U('group/Group/joinGroup')+'&gid='+gid,{title:'加入社团'});
}
// 删除社团
function delgroup(gid) {
    ui.box.load(U('group/Group/delGroupDialog')+'&gid='+gid,{title:'解散社团'});
}
function disband(gid) {
    ui.box.load(U('group/Group/disbandDialog')+'&gid='+gid,{title:'申请解散'});
}
function movegroup(gid) {
    ui.box.load(U('group/Group/moveGroupDialog')+'&gid='+gid,{title:'转让社团'});
}
// 退出社团
function quitgroup(gid) {
    ui.box.load(U('group/Group/quitGroupDialog')+'&gid='+gid,{title:'脱离社团'});
}
// 过滤html，字串检测长度
function checkPostContent(content)
{
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/<br>/g, "");
	content = content.replace(/<p>/g, "");
	content = content.replace(/<\/p>/g, "");
	return getLength(content);
}