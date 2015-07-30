/*
 *
 *Plugin Name: Juser
 *URL: http://blog.jjonline.cn
 *Description: 晶晶的博客emlog会员系统插件
 *Author: Jea杨
 *Version: 1.0
 *
 */
$(function () {
	var insertNode = $('#menu_data'),juserSidebarNode = $('#juser');
	if(typeof insertNode.html() != 'undefined') {
		var menu = '<li class="sidebarsubmenu" id="menu_juser">'+juserSidebarNode.html()+'</li>';
		if(J.GetUrlQueryString('plugin') == 'juser') {
			menu = '<li class="sidebarsubmenu sidebarsubmenu1" id="menu_juser">'+juserSidebarNode.html()+'</li>';
		}
		juserSidebarNode.hide().remove();
		insertNode.after(menu);
	}	
});