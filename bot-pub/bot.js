import { NCWebsocket, Structs } from 'node-napcat-ts'
import { getResId } from './PacketHelper.js';
import config from './config.js';

export class Bot {
    constructor() {
        this.napcat = new NCWebsocket({
            baseUrl: config.BOT_WEB_SOCKET_URL,
            throwPromise: true,
            reconnection: {
                enable: true,
                attempts: 10,
                delay: 5000
            }
        }, true)

        this.napcat.connect()
        this.setupMessageHandlers()
    }

    setupMessageHandlers() {
        this.napcat.on('message.group', async (e) => {
            try {
                await this.handleGroupMessage(e);
            } catch (error) {
                console.error('Error handling group message:', error);
                try {
                    await this.napcat.send_group_msg({
                        group_id: e.group_id,
                        message: Structs.text('处理消息时发生错误')
                    });
                } catch (sendError) {
                    console.error('Error sending error message:', sendError);
                }
            }
        });
    }

    async handleGroupMessage(e) {
        const message = e.message[0];
        if (!message || message.type !== "text") return;

        const text = message.data.text;
        if (text.startsWith('#resid')) {
            await this.handleResIdCommand(e, text);
        } else if (text.startsWith('#ark')) {
            await this.handleArkCommand(e);
        }
    }

    async handleResIdCommand(e, text) {
        const content = text.replace('#resid', '');
        const res = await getResId(this.napcat, content, e.group_id);
        if (!res) {
            throw new Error('Failed to get resId');
        }
        await this.napcat.send_group_msg({
            group_id: e.group_id,
            message: Structs.text(JSON.stringify(res))
        });
    }

    async handleArkCommand(e) {
        const ark = await this.napcat.get_mini_app_ark({
            type: "bili",
            title: "拾雪的一天",
            desc: "vlog记录一天的生活",
            picUrl: "https://thirdqq.qlogo.cn/g?b=oidb&k=09ElpZZZUTHFhoIlvs0lFg&kti=ZyBvjxHhVOI&s=640",
            jumpUrl: "https://www.bilibili.com/video/BV1GJ411x7h7/"
        });
        if (!ark || !ark.data) {
            throw new Error('Failed to get ark data');
        }
        await this.napcat.send_group_msg({
            group_id: e.group_id,
            message: Structs.text(JSON.stringify(ark.data))
        });
    }

    async botGetResId(content) {
        try {
            const res = await getResId(this.napcat, content, 337096807);
            if (!res) {
                throw new Error('Failed to get resId');
            }
            return res;
        } catch (error) {
            console.error('Error in botGetResId:', error);
            throw error;
        }
    }

    async botGetArk(title, desc, url) {
        try {
            const ark = await this.napcat.get_mini_app_ark({
                type: "bili",
                title,
                desc,
                picUrl: url,
                jumpUrl: "https://www.bilibili.com/video/BV1GJ411x7h7/"
            });
            if (!ark || !ark.data) {
                throw new Error('Failed to get ark data');
            }
            return ark.data;
        } catch (error) {
            console.error('Error in botGetArk:', error);
            throw error;
        }
    }
}