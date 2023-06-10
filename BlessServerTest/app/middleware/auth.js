const jwt = require('jsonwebtoken');

module.exports = () => {
  return async function auth(ctx, next) {
    const token = ctx.get('token'); // 获取请求头中的 token

    console.log("token:   ",token)
    try {
      const decoded = jwt.verify(token, ctx.app.config.jwt.secret); // 解密 token 并进行验证

      console.log(decoded)
      ctx.state.user = decoded;
      await next();
    } catch (err) {

        console.log("token error")
      ctx.status = 401; // 设置状态码为 401 Unauthorized
      ctx.body = { code:401,msg: "token unauthorized" };
    }
  };
};