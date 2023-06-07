'use strict';

// const dbService = require('../service/service')

const { Controller } = require('egg');

class HomeController extends Controller {
    async index() {
        const { ctx } = this;

        ctx.body = "hi, egg";
    }

    async login() {
        const { ctx,app } = this;
        const userData = ctx.request.body;
        console.log(userData)

        let body = {
            "code": 0,
            "msg": "",
            "data": {

            }
        }

        const result = await ctx.service.service.queryUserFromDB1(userData.username, userData.password);
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

        console.log(resultQueryChar)
        if(userData.char!="")
        {
            let isSuccess=false;
            isSuccess = resultQueryChar.recordset.forEach(obj => {
                if(obj.DB_ID == userData.char)
                {
                    console.log("login success")
                    // 登录成功，设置token等内容

                    const token = app.jwt.sign({
                        userName:userData.username,
                    },app.config.jwt.secret,{
                        expiresIn:60*60*24
                    })

                    let data ={
                        token:token
                    }

                    body.code=200;
                    body.msg="success";
                    body.data=data;
                    return true;
                }
            });
            
            if(isSuccess==false)
            {
                body.code = 405;
                body.msg = "此账号下无此角色，请重新选择"
            }
            ctx.body = body;

            return;
        }

        const data = {
            username: userData.username,
            charList: resultQueryChar.recordset
        }

        body.code = 200;
        body.msg = "success";
        body.data = data;
        ctx.body = body;
    }
}

module.exports = HomeController;
