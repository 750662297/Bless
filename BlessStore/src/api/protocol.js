import request from "./request"
import requestParam from "./requestParam"

//协议列表

export function login(params) {
    return request({
        url: "/login",
        method: "post",
        data: requestParam(params)
    })
}

export function logout(params) {
    return request({
        url: "/logout",
        method: "post",
        data: requestParam(params)
    })
}

export function queryList(params) {
    return request({
        url: "/query/list",
        method: "get",
        data: requestParam(params)
    })
}

export function Test(params) {
    return request({
        url: "/",
        method: "get",
        data: requestParam(params)
    })
}