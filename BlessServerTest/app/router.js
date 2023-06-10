'use strict';
/**
 * @param {Egg.Application} app - egg application
 */
module.exports = app => {
    const { router, controller ,middleware} = app;

    const auth = middleware.auth();

    router.options('/api', ctx => {
        // 设置跨域响应头
        ctx.set('Access-Control-Allow-Origin', '*');
        ctx.set('Access-Control-Allow-Headers', 'X-Requested-With');
        ctx.set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        ctx.body = '';
    });

    router.post('/action',controller.home.postAction)
    router.get('/', auth,controller.home.index);
    router.post('/login', controller.home.login);
    router.post('/buy/item',auth, controller.home.buyItem)
};
