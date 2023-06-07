'use strict';

// const dbService = require('../service/service')

const { Controller } = require('egg');

class HomeController extends Controller {
    async index() {
        const { ctx } = this;

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
                        username:userData.username,
                        charId:userData.char
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
        let result = await ctx.service.service.MinusUserCash(item.username, cash-item.price);
        if (result != true) {
            body.code = 400;
            body.msg = "购买失败，去找GM吧！"
            ctx.body = body;
            return;
        }

        const sendArr = [];
        let tmpArr = [];
        for (let i = 0; i < item.info.length; i++) {
            if (i !== 0 && i % 4 === 0) {
                sendArr.push(tmpArr);
                tmpArr = [];
            }
            tmpArr.push(item.info[i]);
        }
        if (tmpArr.length > 0) {
            sendArr.push(tmpArr);
        }

        sendArr.forEach(async send => {
            let str = '';
            const count = send.length;
            for (let i = 0; i < count; i++) {
                const it = send[i];
                str += `,0,${it.item_id},${it.num},${it.enchant},${it.grade}`;
            }
            for (let i = 0; i < (4 - count); i++) {
                str += ',0,0,0,0,0';
            }

            await ctx.service.service.BuyItem(item.charId,str);
        });

        let aResult = await ctx.service.service.InsertBuyHistory(item.username,item.charId,item.index,item.price,cash-item.price);
        if(aResult.rowsAffected !=0)
        {
            let data={
                cash:cash-item.price
            }

            body.code=200;
            body.msg="success";
            body.data=data;
        }
        else{
            body.code=400;
            body.msg="插入购买记录失败"
        }
        
        ctx.body=body;
    }
}

module.exports = HomeController;
