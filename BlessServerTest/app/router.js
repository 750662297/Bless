'use strict';
/**
 * @param {Egg.Application} app - egg application
 */
module.exports = app => {
    const { router, controller } = app;

    router.options('/api', ctx => {
        // 设置跨域响应头
        ctx.set('Access-Control-Allow-Origin', '*');
        ctx.set('Access-Control-Allow-Headers', 'X-Requested-With');
        ctx.set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        ctx.body = '';
    });

    router.get('/', controller.home.index);
    router.post('/login', controller.home.login);
};
