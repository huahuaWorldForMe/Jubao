/**
 * Created by diannao on 2018/3/21.
 */
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); // 匹配目标参数
    var result = window.location.search.substr(1).match(reg); // 对querystring匹配目标参数
    if (result != null) {
        return decodeURIComponent(result[2]);
    } else {
        return null;
    }
}
function getOpenid(){
    var storage=null;
    if(window.localStorage) {              //判断浏览器是否支持localStorage
        storage = window.localStorage;
        openid=storage.getItem('openid');

    }
    return openid;
}
function is_login_success(){
    var storage=null;
    var is_login=0;
    if(window.localStorage) {              //判断浏览器是否支持localStorage
        storage = window.localStorage;
        is_login=storage.getItem('is_login');

    }
    return parseInt(is_login)===1;
}