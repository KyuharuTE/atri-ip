window.onload = function () {
	const isLogin = window.localStorage.getItem("token") != null

	let isLoginDisplay = ''
	if (!isLogin) {
		isLoginDisplay = `<mdui-list-item href="/user/register">注册</mdui-list-item>
					<mdui-list-item href="/user/login">登录</mdui-list-item>`
	} else {
		isLoginDisplay = `<mdui-list-item href="/user/logout">退出</mdui-list-item>
					<mdui-list-item href="/user">控制台</mdui-list-item>
					<mdui-list-item href="/user/until/long_msg">获取自定义 LongMSG</mdui-list-item>`
	}

	var drawerHtml = `
        <mdui-navigation-drawer
				modal
				close-on-esc
				close-on-overlay-click
				contained
			>
				<mdui-list>
					<mdui-list-item href="/">主页</mdui-list-item>
					<mdui-list-subheader>用户</mdui-list-subheader>
					${isLoginDisplay}
					<mdui-list-subheader>探针</mdui-list-subheader>
					<mdui-list-item href="/ip">探针列表</mdui-list-item>
					<mdui-list-subheader>杂项</mdui-list-subheader>
					<mdui-list-item href="/about">关于</mdui-list-item>
					<mdui-list-item href="https://qm.qq.com/cgi-bin/qm/qr?k=BA-FnRO983Dy94sRXp_zTsOFCyIvhBAk&jump_from=webapi&authKey=Wm1eUNnT95yKDFKzEanUgBkvqydX1X8/lbfuW1AYJztsRAMn+OYL51QUVYswsnlu">QQ Group</mdui-list-item>
					<mdui-list-item href="https://t.me/fricktools">TG Group</mdui-list-item>
					<mdui-list-item href="https://t.me/fricktool">TG Channel</mdui-list-item>
				</mdui-list>
			</mdui-navigation-drawer>

			<mdui-top-app-bar style="position: relative">
				<mdui-button-icon
					id="open-navigation"
					icon="menu--outlined"
				></mdui-button-icon>
				<mdui-top-app-bar-title>iP 探针</mdui-top-app-bar-title>
				<div style="flex-grow: 1"></div>
			</mdui-top-app-bar>
    `;

	var appContainer = document.querySelector("mdui-layout")
	if (appContainer) {
		appContainer.insertAdjacentHTML('afterbegin', drawerHtml);
	} else {
		console.error("未找到元素");
	}

	document.getElementById("open-navigation").onclick = function () {
		var drawer = document.querySelector("mdui-navigation-drawer");
		if (drawer) {
			drawer.open = true;
		} else {
			console.error("未找到 mdui-navigation-drawer 元素");
		}
	};

	mdui.setColorScheme('#0061a4');
};