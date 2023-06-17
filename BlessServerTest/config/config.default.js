/* eslint valid-jsdoc: "off" */

'use strict';

/**
 * @param {Egg.EggAppInfo} appInfo app info
 */
module.exports = appInfo => {
  /**
   * built-in config
   * @type {Egg.EggAppConfig}
   **/
  const config = exports = {};

  // use for cookie sign key, should change to your own and keep security
  config.keys = appInfo.name + '_1686058643617_5803';

  // add your middleware config here
  config.middleware = [];

  config.jwt = {
	secret:'xczl785'
  }

  config.static = {
    prefix:'/',
    dir: process.cwd() + '/public'
  }

  config.rundir = process.cwd() + '/run'
  // add your user config here
  const userConfig = {
    // myAppName: 'egg',
  };

  const security= { // 关闭 Egg 安全选项（仅为示例）
    csrf: false,
    csp: false,
  };
  const cors={
    origin: '*', // 允许的请求源，* 表示允许任意来源发起的请求
    allowMethods: 'GET,HEAD,PUT,POST,DELETE,PATCH', // 允许的 HTTP 请求方法
  };

  const mssql = {
	clients: {
	  globalDBCreate: {
		user: 'sa',
		password: 'BlessUnleashed',
		server: '192.168.200.100',
		port:1433,
		database: 'GlobalDB_Create',
	  },
	  gamedbPC: {
		user: 'sa',
		password: 'BlessUnleashed',
		server: '192.168.200.100',
		port:1433,
		database: 'create_gamedb_pc',
	  },
	},
  };

  const cluster = {
    listen: {
        port: 8887,
        hostname: '',
      },
  }

  return {
    ...config,
    ...userConfig,
    security,
    cors,
	mssql,
    cluster
  };
};

// exports.security = {
//     csrf: {
//       enable: false,
//     },
//     domainWhiteList: ['http://127.0.0.1:3001'], // 允许访问的跨域域名列表
//   };
  
//   exports.cors = {
//     origin: '*', // 设置允许跨域的域名，也可以设置为true表示全部允许
//     allowMethods: 'GET,HEAD,PUT,POST,DELETE,PATCH',
//   };