'use strict';

// const dbService = require('../service/service')

const { Controller } = require('egg');

const fs = require('fs');
const path = require('path')

class HomeController extends Controller {
    async index() {
        const { ctx } = this;

        // const filePath = path.resolve(__dirname, '../public/data.json');
        // const jsonData = JSON.parse(fs.readFileSync(filePath, 'utf-8'));

        // for (const element of jsonData) {
        //     let url;
        //     let showName;
        //     let result =await ctx.service.service.InsertTemp(element.name,element.item_id,50000,element.n,url,showName);

        //     console.log(result)
        // }


        const request = ctx.request.body;

        const page = 1;
        const pageSize = 10;
        const offset = (page - 1) * pageSize;
        const name = '突击'
        // const name = null
        const tag = request.tag || null;
        // const name = request.name;

        let reCount = await this.ctx.service.service.getTotalCount(name, tag);
        let result = await ctx.service.service.queryItemList(pageSize, offset, tag, name)

        let data = {
            code: 200,
            msg: 'success',
            data:
            {
                infoList: result.recordset,
                totalCount: reCount.recordset[0].totalRowCount
            }
        }
        console.log(result)


        ctx.body = "hi, egg";
    }

    async login() {
        const { ctx, app } = this;
        const userData = ctx.request.body;
        console.log(userData)

        let body = {
            "code": 0,
            "msg": "",
            "data": {

            }
        }

        const result = await ctx.service.service.queryUserFromDB1(userData.username, userData.password);
        console.log(result)
        if (result.rowsAffected == 0) {
            //登陆失败
            body.code = 400;
            body.msg = "账号或密码错误"
            ctx.body = body;
            return;
        }

        const resultQueryChar = await ctx.service.service.queryCharByUserName(userData.username);
        if (resultQueryChar.rowsAffected == 0) {
            body.code = 400;
            body.msg = "此账号下无角色，请先在游戏中创建角色再登录！"
            ctx.body = body;

            return;
        }

        if (userData.char != "") {
            let isSuccess = false;
            isSuccess = resultQueryChar.recordset.forEach(obj => {
                if (obj.DB_ID == userData.char) {
                    console.log("login success")
                    // 登录成功，设置token等内容

                    const token = app.jwt.sign({
                        userName: userData.username,
                    }, app.config.jwt.secret, {
                        expiresIn: 60 * 60 * 24
                    })

                    let data = {
                        token: token,
                        uCash: result.recordset[0].uCash,
                        username: userData.username,
                        charId: userData.char
                    }

                    body.code = 201;
                    body.msg = "success";
                    body.data = data;
                    return true;
                }
            });

            if (isSuccess == false) {
                body.code = 405;
                body.msg = "此账号下无此角色，请重新选择"
            }
            ctx.body = body;

            return;
        }

        const data = {
            username: userData.username,
            charList: resultQueryChar.recordset,
        }

        body.code = 200;
        body.msg = "success";
        body.data = data;
        ctx.body = body;
    }

    async buyItem() {
        const { ctx, app } = this;
        const item = ctx.request.body;
        console.log(item);

        let body = {
            code: 0,
            msg: '',
            data: {

            }
        }

        let cash = await ctx.service.service.getUserMoney(item.username);
        //资金不足
        if (cash < item.price) {
            body.code = 400;
            body.msg = "资金不足，无法购买，去找GM吧！"
            ctx.body = body;
            return;
        }
        //购买失败
        let result = await ctx.service.service.MinusUserCash(item.username, cash - item.price);
        if (result != true) {
            body.code = 400;
            body.msg = "购买失败，去找GM吧！"
            ctx.body = body;
            return;
        }

        console.log("163")
        console.log(item)
        const sendArr = [];
        let tmpArr = [];
        for (let i = 0; i < item.info.length; i++) {
            if (i !== 0 && i % 4 === 0) {
                sendArr.push(tmpArr);
                tmpArr = [];
            }

            console.log("173")
            tmpArr.push(item.info[i]);
        }
        if (tmpArr.length > 0) {
            console.log("174")
            sendArr.push(tmpArr);
        }

        console.log("176")
        sendArr.forEach(async send => {
            let str = '';
            const count = send.length;
            for (let i = 0; i < count; i++) {
                const it = send[i];
                str += `,0,${it.itemId},${it.num},0,0`;
            }
            for (let i = 0; i < (4 - count); i++) {
                str += ',0,0,0,0,0';
            }

            console.log("send Arr")
            await ctx.service.service.BuyItem(item.charId, str);
        });


        console.log("buy before InsertBuyHistory")
        // let aResult = await ctx.service.service.InsertBuyHistory(item.username, item.charId, item.price, cash - item.price);
        // console.log(aResult)
        // if (aResult.rowsAffected != 0) {
        //     let data = {
        //         cash: cash - item.price
        //     }
        //     body.code = 200;
        //     body.msg = "success";
        //     body.data = data;
        // }
        // else {
        //     body.code = 200;
        //     body.msg = "插入购买记录失败"
        // }


            let data = {
                cash: cash - item.price
            }
            body.code = 200;
            body.msg = "success";
            body.data = data;
        ctx.body = body;
    }

    async postAction() {

        const { ctx, app } = this;
        const item = ctx.request.body;

        const username = item.username;
        const password = item.password;
        const ip = item.ip;
        const mac = item.mac;
        const server = item.server;
        const step = item.step;


        console.log(item)
        this.ctx.body = "hi,post"
    }

    async queryList() {
        const { ctx } = this;
        const request = ctx.request.body;

        const page = parseInt(request.page) || 1;
        const pageSize = parseInt(request.pageSize) || 10;
        const offset = (page - 1) * pageSize;

        const tag = request.tag || null;
        const name = request.name || null;
        let reCount = await this.ctx.service.service.getTotalCount(name, tag);
        let result = await ctx.service.service.queryItemList(pageSize, offset, tag, name)

        let body = {
            code: 200,
            msg: 'success',
            data:
            {
                infoList: result.recordset,
                currentPage: page,
                pageSize: pageSize,
                totalCount: reCount.recordset[0].totalRowCount,
                totalPage: 0
            }
        }

        ctx.body = body;
    }


    //更新商品信息
    async updateItemInfo()
    {
        const {ctx}=this;
        const request = ctx.request.body;

        console.log(request)
        const num = request.num || 1;
        const price = request.price || 50000;
        const tag = request.tag || null;
        const desc = request.desc || null;
        const name = request.name || request.oldName;
        const itemId = request.itemId;

        let result = await ctx.service.service.updateItemInfo(itemId,name,price,num,tag,desc);
        console.log(result)

        if(result.rowsAffected !=1)
        {
            let body={
                code:400,
                msg:'修改失败'
            }
            ctx.body=body;
            return;
        }

        let re = await ctx.service.service.getItemInfo(itemId);
        let data={
            code:200,
            msg:'sucess',
            data:{
                info:re.recordset[0]
            }
        }

        ctx.body=data;
    }
}

module.exports = HomeController;
