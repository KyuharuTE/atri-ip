import pb from "./pb/index.js"
import {
    Buffer
} from 'buffer'
import crypto from 'crypto'
import {
    gzip as _gzip
} from 'zlib'
import {
    promisify
} from 'util'

const gzip = promisify(_gzip)


const RandomUInt = () => crypto.randomBytes(4).readUInt32BE()

export const Elem = async (
    e,
    content
) => {
    try {
        let packet = buildBasePbContent(e.isGroup ? e.group_id : e.user_id, e.isGroup)
        const parsed = typeof content === 'object' ? content : JSON.parse(content)
        const elements = Array.isArray(parsed) ? parsed : [parsed]

        packet = {
            ...packet,
            "3": {
                "1": {
                    "2": elements
                }
            },
            "4": RandomUInt(),
            "5": RandomUInt()
        }

        const data = pb.encode(parseJsonToMap(packet))
        const req = await e.bot.sendApi('send_packet', {
            cmd: 'MessageSvc.PbSendMsg',
            data: Buffer.from(data).toString("hex")
        })
        const resp = pb.decode(req.data)
        e.reply(JSON.stringify(resp, null, '  '))
    } catch (error) {
        console.error(`sendMessage failed: ${error.message}`, error)
        throw error;
    }
}

export const Raw = async (
    e,
    cmd,
    content
) => {
    try {
        const data = pb.encode(parseJsonToMap(content))
        const req = await e.bot.sendApi('send_packet', {
            cmd: cmd,
            data: Buffer.from(data).toString("hex")
        })
        const resp = pb.decode(req.data)
        e.reply(JSON.stringify(resp, null, '  '))
    } catch (error) {
        console.error(`sendMessage failed: ${error.message}`, error)
        throw error;
    }
}

export const getResId = async (
    napcat,
    content,
    group
) => {
    try {
        const data = {
            "2": {
                "1": "MultiMsg",
                "2": {
                    "1": [{
                        "3": {
                            "1": {
                                "2": typeof content === 'object' ? content : JSON.parse(content)
                            }
                        }
                    }]
                }
            }
        }
        const protoBytes = pb.encode(parseJsonToMap(data))
        const compressedData = await gzip(protoBytes)

        const target = BigInt(group)

        const json = {
            "2": {
                "1": 3,
                "2": {
                    "2": `${group}`
                },
                "3": target,
                "4": `hex->${bytesToHex(compressedData)}`
            },
            "15": {
                "1": 4,
                "2": 2,
                "3": 9,
                "4": 0
            }
        }

        const final = pb.encode(parseJsonToMap(json))
        const req = await napcat.send_packet({
            cmd: 'trpc.group.long_msg_interface.MsgService.SsoSendLongMsg',
            data: Buffer.from(final).toString("hex")
        })
        const resp = pb.decode(req)
        const resid = resp["2"]["3"].toString()
        return resid
    } catch (error) {
        console.error(`sendMessage failed: ${error.message}`, error)
        throw error;
    }
}

function buildBasePbContent(id, isGroupMsg) {
    const base = {
        "1": {
            [isGroupMsg ? "2" : "1"]: isGroupMsg ? {
                "1": id
            } : {
                "2": id
            }
        },
        "2": {
            "1": 1,
            "2": 0,
            "3": 0
        },
        "3": {
            "1": {
                "2": []
            }
        }
    }
    return base
}

function parseJsonToMap(json, path = []) {
    const result = {}
    if (Array.isArray(json)) {
        return json.map((item, index) => parseJsonToMap(item, path.concat(index + 1)))
    } else if (typeof json === "object" && json !== null) {
        for (const key in json) {
            const numKey = Number(key)
            if (Number.isNaN(numKey)) {
                throw new Error(`Key is not a valid integer: ${key}`)
            }
            const currentPath = path.concat(key)
            const value = json[key]

            if (typeof value === "object" && value !== null) {
                if (Array.isArray(value)) {
                    result[numKey] = value.map((item, idx) =>
                        parseJsonToMap(item, currentPath.concat(String(idx + 1)))
                    )
                } else {
                    result[numKey] = parseJsonToMap(value, currentPath)
                }
            } else {
                if (typeof value === "string") {
                    if (value.startsWith("hex->")) {
                        const hexStr = value.slice("hex->".length)
                        if (isHexString(hexStr)) {
                            result[numKey] = hexStringToByteArray(hexStr)
                        } else {
                            result[numKey] = value
                        }
                    } else if (
                        currentPath.slice(-2).join(",") === "5,2" &&
                        isHexString(value)
                    ) {
                        result[numKey] = hexStringToByteArray(value)
                    } else {
                        result[numKey] = value
                    }
                } else {
                    result[numKey] = value
                }
            }
        }
    } else {
        return json
    }
    return result
}

function isHexString(s) {
    return s.length % 2 === 0 && /^[0-9a-fA-F]+$/.test(s)
}

function hexStringToByteArray(s) {
    return Buffer.from(s, "hex")
}

function bytesToHex(bytes) {
    return Array.from(bytes)
        .map(byte => byte.toString(16).padStart(2, '0'))
        .join('');
}