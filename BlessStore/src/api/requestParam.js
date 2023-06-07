import qs from 'qs'
/**
 * request参数封装
 * @param params 参数对象
 * @param requesType request类型
 * @param addDefaultParams 是否添加默认参数
 * @param contentType 数据格式
 *  json: 'application/json; charset=utf-8'
 *  form: 'application/x-www-form-urlencoded; charset=utf-8'
 */
export default function (params, requestType = 'post', addDefaultParams = false, contentType = 'json') {
    var defaults = {
        't': new Date().getTime()
    }
    
    params = requestType === 'post' ?
        (contentType === 'json' ? JSON.stringify(params) : qs.stringify(params)) :
        params;

    return params
}