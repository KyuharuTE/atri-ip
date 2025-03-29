## 如何部署 BOT 后端
1. 安装依赖
```bash
pnpm i
```
2. 配置 `confg.js`
```js
class Config {
    constructor() {
        this.API_SERVER_PORT = 22233; // API 服务端口
        
        this.BOT_GET_GROUP_ID = 0; // 群 ID（机器人所在的任意群）
        this.BOT_WEB_SOCKET_URL = 'ws://'; // NapCat WebSocket 服务地址
    }
}

module.exports = new Config();
```
3. 启动服务
```bash
node server.js
```