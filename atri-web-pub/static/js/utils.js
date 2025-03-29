class AfritUtils {
    constructor() {
        this.token = localStorage.getItem('token');
        this.API_BASE = '/api';
        this.API_ENDPOINTS = {
            user: {
                login: '/user/login.php',
                register: '/user/reg.php',
                info: '/user/getUserInfo.php',
                sign: '/user/sign.php'
            },
            ip: {
                list: '/ip/getIpz.php',
                add: '/ip/addIpz.php',
                delete: '/ip/delIpz.php',
                details: '/ip/getIp.php'
            },
            until: {
                longMsg: '/until/getLongMsg.php',
                arkCard: '/until/getArkCard.php',
                prices: '/until/getPrices.php'
            }
        };
        this.IP_LOCATION_API = 'https://api.vore.top/api/IPdata';
    }

    async apiRequest(endpoint, options = {}) {
        try {
            const url = new URL(this.API_BASE + endpoint, window.location.origin);
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json'
                }
            };

            if (options.method === 'GET' || !options.method) {
                if (this.token) {
                    url.searchParams.append('token', this.token);
                }

                const { method, params, ...otherParams } = options;

                if (params && typeof params === 'object') {
                    for (const [key, value] of Object.entries(params)) {
                        if (value !== undefined && value !== null) {
                            url.searchParams.append(key, value);
                        }
                    }
                }

                for (const [key, value] of Object.entries(otherParams)) {
                    if (value !== undefined && value !== null) {
                        url.searchParams.append(key, value);
                    }
                }
            } else {
                if (this.token) {
                    defaultOptions.headers['Authorization'] = `Bearer ${this.token}`;
                }
                Object.assign(defaultOptions, options);
            }

            const response = await fetch(url, defaultOptions);
            const data = await response.json();

            if (data.code != 200) {
                this.showMessage(data.message || data.msg);
            }

            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            this.showMessage('请求失败，请稍后重试');
            return null;
        }
    }

    showMessage(message, duration = 2000) {
        mdui.snackbar({
            message,
            autoCloseDelay: duration
        });
    }

    handleTokenExpired() {
        localStorage.removeItem('token');
        this.showMessage('登录已失效，请重新登录');
        setTimeout(() => {
            window.location.href = '/user/login';
        }, 1500);
    }

    checkAuth() {
        if (!this.token) {
            window.location.href = '/user/login';
            return false;
        }
        return true;
    }

    async batchGetIpLocations(ips) {
        try {
            // 使用Promise.all并发请求
            const promises = ips.map(ip =>
                fetch(`${this.IP_LOCATION_API}?ip=${ip}`)
                    .then(res => res.json())
                    .then(data => ({
                        ip,
                        location: data.code === 200 ? data.adcode.o : '未知'
                    }))
                    .catch(() => ({
                        ip,
                        location: '未知'
                    }))
            );

            return await Promise.all(promises);
        } catch (error) {
            console.error('Batch IP location query failed:', error);
            return ips.map(ip => ({
                ip,
                location: '未知'
            }));
        }
    }

    generatePB(id) {
        return JSON.stringify({
            1: {
                1: "ip.afrit.cn",
                12: {
                    14: {
                        1: "iP 抓捕",
                        2: "专为 QQ PB 打造的 iP 探针, 完全免费, 最新支持一键转 LongMSG 无需 ONO 内测",
                        3: `https://${window.location.host}/get.php?id=${id}`
                    }
                }
            }
        });
    }

    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showMessage('复制成功');
            return true;
        } catch (error) {
            console.error('Copy failed:', error);
            this.showMessage('复制失败，请手动复制');
            return false;
        }
    }

    showDialog(options) {
        return mdui.dialog({
            closeOnOverlayClick: true,
            stackedActions: true,
            ...options
        });
    }
}

window.afritUtils = new AfritUtils();