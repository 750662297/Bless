$(document).ready(function(){
    //var str = '{"name":"商品名称","num":5,"old_price":999,"new_price":999,"unit":"点券","color":0,"icon_url":"images/mall-bg.png","content":["测试测试","测试啊"]}';
    //str = Base64.encode(str);
    //console.log(str);
    //showItemPopupTips($('body'), JSON.parse(Base64.decode(str)));
    // 选中第一个菜单
    autoScrollInfoBox.start();
}).on('mouseenter','.mall-item-need-popup-tips',function(){
    var obj = $(this).attr('data-tips');
    if(obj){
        obj = JSON.parse(Base64.decode(obj));
        if(typeof obj == 'object'){
            showItemPopupTips($(this),obj);
        }
    }
}).on('mouseleave','.mall-item-need-popup-tips',function(){
    $('.mall-item-popup-box').remove();
}).on('click','.mall-shopping-box',function(){
    var e = $(this);
    e.find('.mall-full-box').addClass('mall-full-box-hide');
    setTimeout(function(){
        e.remove();
    },500);
}).on('click','.mall-full-box',function(e){
    e.stopPropagation(); // 阻止冒泡到父元素
}).on('click','.mall-menu li',function(){
    if($(this).is(".li-selected")) return false;
    $(".mall-menu li").removeClass('li-selected');
    $(this).addClass('li-selected');
    var jumpAction = $(this).attr('jump-action');
    if(jumpAction)
    {
        $('.mall-goods').empty();
        MsgBox.loading('加载中，请稍后...');
        $.ajax({
            url:'action',
            type:'post',
            dataType:'json',
            data:{action:jumpAction},
            success:function(res){
                MsgBox.loading();
                if(res['return_code'] == 'success')
                {
                    handleServerData.init(res['data']);
                }else{
                    MsgBox.notice(res['return_msg']);
                }
            },
            error:function(x,i,e){
                MsgBox.loading();
                MsgBox.notice(e);
            }
        })
    }
}).on('click','.mall-msg-box',function(e){
    if($(this).is('mall-msg-box-button')){
        e.stopPropagation();
    }
});
var autoScrollInfoBox = {
    start:function(){
        if($(".mall-srcoll-info-box div").length > 0)
        {
            setInterval(function(){
                $(".mall-srcoll-info-box div").each(function(i,e){
                    var left = $(e).position().left - 1;
                    if(left < -$(e).width()) left = $(e).parent().width();
                    $(e).css({'left' : left + 'px'});
                });
            },50);
        }
    }
}
var handleServerData = {
    // 初始化
    init:function(res){
        switch(res['action'])
        {
            case 'mall':    // 商城
                this.__ShowMall(res['mall_type'], res['page'], res['all_page'], res['goods'], res['hot'], res['token']);
                break;
            case 'packet':  // 礼包
                this.__ShowPacket(res);
                break;
            case 'active_code':    // 激活码
                this.__ShowActiveCodePage(res);
                break;
            default:
                console.log('不支持的类型！' + res['action']);
                break;
        }
    },
    __ShowActiveCodePage:function(res)
    {
        //var ele_id = '__active_code_' + parseInt(Math.random() * 1000000);
        var html = '<div class="mall-title">激活码</div><div class="mall-cdk-box"><form action="#" onsubmit="return false"><input type="hidden" name="action" value="' + res['token'] +'"><br><br><br><br><br><br><br><br><div><input type="text" maxlength="60" placeholder="激活码" name="active_code"></div><div><input type="submit" value="提 交"></div></form></div>';
        $('.mall-goods').html(html);
        $('.mall-cdk-box form').off('submit').on('submit',function(){
            var element = $(this);
            if(element.find('input[type=submit]').attr('disabled'))
            {
                return;
            }
            if(!element.find('input[name="active_code"]').val()){
                MsgBox.notice('请填写激活码！');
                return;
            }
            element.find('input[type=submit]').attr('disabled', true);
            element.find('input[type=submit]').val('请稍后...');
            MsgBox.loading('加载中，请稍后...');
            $.ajax({
                url:'action',
                type:'post',
                dataType:'json',
                data:element.serializeArray(),
                success:function(res){
                    MsgBox.loading();
                    MsgBox.notice(res['return_msg']);
                    element.find('input[type=submit]').attr('disabled', false);
                    element.find('input[type=submit]').val('提 交');
                    if(res['return_code'] == 'success')
                    {
                        element.find('input[name="active_code"]').val('');
                        try
                        {
                            $('#user-cash').html(res['data']['user_cash']);
                        }
                        catch (e)
                        {
                            console.log(e);
                        }
                    }
                },
                error:function(x,i,e){
                    MsgBox.loading();
                    MsgBox.notice(e);
                    element.find('input[type=submit]').attr('disabled', false);
                    element.find('input[type=submit]').val('提 交');
                }
            })
        });
    },
    __ShowPacket:function(res){
        /**
         * res['type']可能的值：
         * 0：每日限购礼包，
         * 1：每周限购礼包，
         * 2：永久限购礼包（永久限购时，date_start和date_end有效，均为时间戳，时间戳转换：https://tool.lu/timestamp/），
         * 3：每日充值礼包，
         * 4：每周充值礼包，
         * 5：累计充值礼包（累计充值时，date_start和date_end有效，均为时间戳），
         * 6：直购礼包
         */
        if(res['type'] >= 0 && res['type'] <= 6)
        {
            var html = '';
            for(var i = 0; i < res['list'].length; i++)
            {
                html += this.__MakePacketList(res['type'],res['list'][i]['item']);
            }
            var title = '';
            switch(res['type'])
            {
                case 0:
                    title = '每日限购礼包';
                    break;
                case 1:
                    title = '每周限购礼包';
                    break;
                case 2:
                    title = '永久限购礼包';
                    break;
                case 3:
                    title = '每日充值礼包';
                    break;
                case 4:
                    title = '每周充值礼包';
                    break;
                case 5:
                    title = '累计充值礼包';
                    break;
                case 6:
                    title = '直购礼包';
                    break;
                default:
                    title = '未知';
                    break;
            }
            html = '<div class="mall-title">' + title + '</div><ul class="mall-list mall-packet-box">' + html + '</ul>';
            $('.mall-goods').html(html);
            if($('.mall-goods .mall-line-item').length > 0){
                $('.mall-goods .mall-line-item').each(function(i,e){
                    if($(e).find('.mall-line-tag').length > 0){
                        DateTime.setObjInterval($(e).find('.mall-line-tag'));
                    }
                });
                setTimeout(function(){
                    $('.mall-goods .mall-line-item').removeClass('mall-line-item-hide');
                },50);
            }
            $('.mall-goods .mall-packet-box .mall-line-item input[type=button]').off('click').on('click',function(){
                var confirm = $(this).attr('data-confirm'),token=$(this).attr('data-token');
                if(confirm == 1)    // 购买礼包
                {
                    // 有询问框
                    MsgBox.confirm('确定要花费<span style="color:red;">' + $(this).attr('data-price') + $(this).attr('data-unit') + '</span>购买<span style="color:#0f0;">[' + $(this).attr('data-name') + ']</span>礼包吗？',['购买','取消'],function(i){
                        if(i === 0){
                            handleServerData.BuyPacket(token);
                        }
                    });
                }else if(confirm == 2){ // 直购礼包
                    MsgBox.confirm('确定要充值<span style="color:red;">' + $(this).attr('data-price') + $(this).attr('data-unit') + '</span>购买<span style="color:#0f0;">[' + $(this).attr('data-name') + ']</span>礼包吗？',['充值','取消'],function(i){
                        if(i === 0){
                            handleServerData.BuyPacket(token);
                        }
                    });
                }else{
                    handleServerData.BuyPacket(token);
                }
            });
            
            
        }else{
            console.log('暂不支持解析的礼包类型！type=' + res['type']);
        }
    },
    BuyPacket:function(token){
        MsgBox.loading('正在处理中...');
        $.ajax({
            url:'action',
            type:'post',
            dataType:'json',
            data:{action:token},
            success:function(res){
                MsgBox.loading();
                if(res['return_code'] == 'success')
                {
                    MsgBox.notice(res['return_msg']);
                    try
                    {
                        $('#user-cash').html(res['data']['user_cash']);
                    }
                    catch (e)
                    {
                        console.log(e);
                    }
                }else{
                    MsgBox.notice(res['return_msg']);
                }
            },
            error:function(x,i,e){
                MsgBox.loading();
                MsgBox.notice(e);
            }
        })
    },
    __MakePacketList:function(type,base64_str)
    {
        var arr = JSON.parse(Base64.decode(base64_str)),html='';
        if(typeof arr == 'object')
        {
            //console.log(arr);
            var end_time = '',item_info = '',packet_type='',title_str = '',flag_str='',surplus_str = '',button_str='';


            if(arr['surplus_num'] != -1)    // -1表示不限制数量
            {
                surplus_str = '剩余' + arr['surplus_num'] + '份';
            }
            if(arr['end_times'] > 0){
                end_time = '<div class="mall-line-tag" data-seconds="' + arr['end_times'] + '" data-end-class="mall-line-tag-gray" data-sec-format="' + surplus_str + (surplus_str != '' ? '，' : '') + '{0}后结束" data-sec-end-tips="已结束">' + surplus_str + (surplus_str != '' ? '，' : '') + DateTime.sec_format(arr['end_times']) + '后结束</div>';
            }else if(surplus_str != ''){
                end_time = '<div class="mall-line-tag">' + surplus_str + '</div>';
            }
            for(var i = 0; i < arr['packet'].length; i++)
            {
                item_info += MakeItemIcon.makeOnce(arr['packet'][i]['item']);
            }
            // 判断开始
            /**
             * res['type']可能的值：
             * 0：每日限购礼包，
             * 1：每周限购礼包，
             * 2：永久限购礼包（永久限购时，date_start和date_end有效，均为时间戳，时间戳转换：https://tool.lu/timestamp/），
             * 3：每日充值礼包，
             * 4：每周充值礼包，
             * 5：累计充值礼包（累计充值时，date_start和date_end有效，均为时间戳），
             * 6：直购礼包
             */
            if(type == 0){
                packet_type = '<span style="color:yellow;">【' +  arr['name'] + '】</span>';
                title_str = '特价</span><span style="color:red;">' + arr['new_price'] + '</span><span>' + arr['unit'];
                flag_str = '<span class="mall-flag mall-flag-red deleteline">原价'+ arr['old_price'] + arr['unit'] + '</span>';
                if(arr['surplus_num'] == 0)
                {
                    button_str = '<input type="button" value="已领完" disabled>';
                }else{
                    button_str = '<input type="button" value="' + (arr['new_price'] > 0 ? '立即抢购' : '免费领取') + '" data-token="' + arr['token'] + '" data-confirm="' + (arr['new_price'] > 0 ? 1 : 0) + '" data-price="' + arr['new_price'] + '" data-unit="' + arr['unit'] + '" data-name="' + arr['name'] + '">';
                }
                
            }else if(type == 1){
                packet_type = '<span style="color:orange;">【' +  arr['name'] + '】</span>';
                title_str = '特价</span><span style="color:red;">' + arr['new_price'] + '</span><span>' + arr['unit'];
                flag_str = '<span class="mall-flag mall-flag-red deleteline">原价'+ arr['old_price'] + arr['unit'] + '</span>';
                if(arr['surplus_num'] == 0)
                {
                    button_str = '<input type="button" value="已领完" disabled>';
                }else{
                    button_str = '<input type="button" value="' + (arr['new_price'] > 0 ? '立即抢购' : '免费领取') + '" data-token="' + arr['token'] + '" data-confirm="' + (arr['new_price'] > 0 ? 1 : 0) + '" data-price="' + arr['new_price'] + '" data-unit="' + arr['unit'] + '" data-name="' + arr['name'] + '">';
                }
            }else if(type == 2){
                packet_type = '<span style="color:red;">【' +  arr['name'] + '】</span>';
                title_str = '特价</span><span style="color:red;">' + arr['new_price'] + '</span><span>' + arr['unit'];
                flag_str = '<span class="mall-flag mall-flag-red deleteline">原价'+ arr['old_price'] + arr['unit'] + '</span>';
                if(arr['surplus_num'] == 0)
                {
                    button_str = '<input type="button" value="已领完" disabled>';
                }else{
                    button_str = '<input type="button" value="' + (arr['new_price'] > 0 ? '立即抢购' : '免费领取') + '" data-token="' + arr['token'] + '" data-confirm="' + (arr['new_price'] > 0 ? 1 : 0) + '" data-price="' + arr['new_price'] + '" data-unit="' + arr['unit'] + '" data-name="' + arr['name'] + '">';
                }
            }else if(type == 3){
                packet_type = '<span style="color:yellow;">【' +  arr['name'] + '】</span>';
                title_str = '今日充值</span><span style="color:red;">' +  (arr['current_cash'] < arr['new_price'] ? arr['current_cash'] : arr['new_price']) + '/' +  arr['new_price'] + '</span><span>' + arr['unit'];
                if(arr['current_cash'] >= arr['new_price'])
                {
                    flag_str = '<span class="mall-flag mall-flag-green">完成</span>';
                }else{
                    flag_str = '<span class="mall-flag mall-flag-red">未完成</span>';
                }
                if(arr['surplus_num'] == 0)
                {
                    button_str = '<input type="button" value="已领完" disabled>';
                }else{
                    button_str = '<input type="button" value="立即领取" data-token="' + arr['token'] + '">';
                }
            }else if(type == 4){
                packet_type = '<span style="color:orange;">【' +  arr['name'] + '】</span>';
                title_str = '本周充值</span><span style="color:red;">' +  (arr['current_cash'] < arr['new_price'] ? arr['current_cash'] : arr['new_price']) + '/' +  arr['new_price'] + '</span><span>' + arr['unit'];
                if(arr['current_cash'] >= arr['new_price'])
                {
                    flag_str = '<span class="mall-flag mall-flag-green">完成</span>';
                }else{
                    flag_str = '<span class="mall-flag mall-flag-red">未完成</span>';
                }
                if(arr['surplus_num'] == 0)
                {
                    button_str = '<input type="button" value="已领完" disabled>';
                }else{
                    button_str = '<input type="button" value="立即领取" data-token="' + arr['token'] + '">';
                }
            }else if(type == 5){
                packet_type = '<span style="color:red;">【' +  arr['name'] + '】</span>';
                title_str = '累计充值</span><span style="color:red;">' +  (arr['current_cash'] < arr['new_price'] ? arr['current_cash'] : arr['new_price']) + '/' +  arr['new_price'] + '</span><span>' + arr['unit'];
                if(arr['current_cash'] >= arr['new_price'])
                {
                    flag_str = '<span class="mall-flag mall-flag-green">完成</span>';
                }else{
                    flag_str = '<span class="mall-flag mall-flag-red">未完成</span>';
                }
                if(arr['surplus_num'] == 0)
                {
                    button_str = '<input type="button" value="已领完" disabled>';
                }else{
                    button_str = '<input type="button" value="立即领取" data-token="' + arr['token'] + '">';
                }
            }else if(type == 6){
                packet_type = '<span style="color:cyan;">【' +  arr['name'] + '】</span>';
                flag_str = '<span class="mall-flag mall-flag-red">促销</span>';
                if(arr['surplus_num'] == 0)
                {
                    button_str = '<input type="button" value="已领完" disabled>';
                }else{
                    button_str = '<input type="button" value="立即购买" data-token="' + arr['token'] + '" data-confirm="2">';
                }
            }else{
                packet_type = '<span style="color:gray;">【' +  arr['name'] + '】</span>';
            }
            html = '<li class="line-bg mall-line-item mall-line-item-hide">' + end_time + '<div class="item-info"><h2>' + packet_type + '<span>' + title_str + '</span>' + flag_str + '</h2><div>' + item_info + '</div></div><div class="item-button">' + button_str + '</div></li>';

        }
        return html;
        
    },
    // ============== 处理各种数据
    /**
     * 
     * @param {*} t type 商城类型
     * @param {*} p page 页码
     * @param {*} a all_page 总页码
     * @param {*} g goods 商品数据
     */
    __ShowMall:function(t,p,a,g,h,tk){
        var html = '',hot_html = '';
        for(var i = 0; i < g.length; i++)
        {
            html += this.__MallMakeItem(g[i]['item']);
        }
        for(var i = 0; i < (8 - g.length); i++)
        {
            html += '<li class="mall-item mall-item-hide mall-item-invalid"></li>';
        }
        for(var i = 0; i < h.length; i++)
        {
            hot_html += this.__MallMakeItem(h[i]['item']);
        }
        for(var i = 0; i < (2 - h.length); i++)
        {
            hot_html += '<li class="mall-item mall-item-hide mall-item-invalid"></li>';
        }
        // 拼合html
        var all = '<div class="mall-title">推荐商品</div><ul class="mall-item-group mall-hot-goods">' + hot_html + '</ul><div class="mall-title">全部商品</div><ul class="mall-item-group mall-all-goods">' + html + '</ul><ul class="mall-page-box">' + this.__CeilPage(p,a,3,tk) + '</ul>';
        $('.mall-goods').html(all);
        setTimeout(function(){
            $('.mall-goods .mall-item').removeClass('mall-item-hide');
        },50);
        // 重新绑定事件
        $('.mall-goods .mall-page-box li').off('click').on('click',function(){
            $.ajax({
                url:'action',
                type:'post',
                dataType:'json',
                data:{action:$(this).attr('data-token'),page:$(this).attr('data-page')},
                success:function(res){
                    if(res['return_code'] == 'success')
                    {
                        handleServerData.init(res['data']);
                    }else{
                        MsgBox.notice(res['return_msg']);
                    }
                },
                error:function(x,i,e){
                    MsgBox.notice(e);
                }
            });
        });
    },
    __MallMakeItem:function(str){
        var arr = JSON.parse(Base64.decode(str));
        if(typeof arr == 'object')
        {
            var html = '<li class="mall-item mall-item-hide"><div><div class="mall-item-icon-box"><div class="mall-item-icon mall-item-need-popup-tips" data-tips="' + str + '"><img src="' + arr['icon_url'] + '" alt=""></div></div><div class="mall-item-info"><h2>' + arr['name'] + '</h2><p>原价：<span class="gray deleteline">' + arr['old_price'] + '</span>' + arr['unit'] + '</p><p>现价：<span class="yellow">' + arr['new_price'] + '</span>' + arr['unit'] + '</p></div><div class="mall-item-buy"><input type="button" value="购买" onclick="showMallBuyBox(\'' + str + '\')"></div></div></li>';
            return html;
        }
        return '';
    },
    __CeilPage:function(page,all_page,limit,token){
        var arr = new Array,bAddBefore,bAddAfter;
        arr.push({page:1,name:'首页',class:'start'})
        for(var i = 1; i <= all_page; i++)
        {
            //if(i == 1 || i == all_page) continue;
            if((i >= (page - limit) && i < page) || (i <= (page + limit) && i > page)) arr.push({page:i,name:i,class:''});
            else if(i == page) arr.push({page:i,name:i,class:'current'});
            else if(i > 1 && i < (page - limit) && !bAddBefore){ bAddBefore = true; arr.push({page:null,name:'...',class:'none'});}
            else if(i < all_page && i > (page - limit) && !bAddAfter){ bAddAfter = true; arr.push({page:null,name:'...',class:'none'});}
        }
        arr.push({page:all_page,name:'尾页',class:'end'});
        var html = '';
        for(var i = 0; i< arr.length; i++)
        {
            html += '<li class="' + arr[i]['class'] + '" data-page="' + arr[i]['page'] + '" data-token="' + token + '">' + arr[i]['name'] + '</li>';
        }
        return html;
    }

}
var MakeItemIcon = {
    /**
     * 制作一个物品图标
     * @param {*} str 物品的base64
     */
    makeOnce:function(str){
        var arr = JSON.parse(Base64.decode(str));
        if(typeof arr == 'object')
        {
            return '<div class="mall-item-icon mall-item-need-popup-tips" data-tips="' + str + '"><img src="' + arr['icon_url'] + '" alt=""></div>';
        }
    }
}
/**
 * 展示物品弹出冒泡卡片
 * @param {*} e 要展示到哪个元素
 * @param {*} o JSON对象：JSON.parse(Base64.decode(obj));
 */
var showItemPopupTips = function(e,o){
    var color = '',num = '',old_price = '',new_price = '', attach_name = '', content = '';
    if(o['color'] == 0) color = 'white';
    else if(o['color'] == 1) color = 'green';
    else if(o['color'] == 2) color = 'blue';
    else if(o['color'] == 3) color = 'yellow';
    else if(o['color'] == 4) color = 'orange';
    else if(o['color'] == 5) color = 'red';
    else if(o['color'] == 6) color = 'violet';
    else if(o['color'] == 7) color = 'pink';
    if(o['num']) num = '<p>数量：' + o['num'] + '个</p>';
    if(o['old_price']) old_price = '<p>原价：<span class="gray deleteline">' + o['old_price'] + '</span>' + o['unit'] + '</p>';
    if(o['new_price']) new_price = '<p>现价：<span class="yellow">' + o['new_price'] + '</span>' + o['unit'] + '</p>';
    if(o['attach_name']){
        for(var i = 0; i < o['attach_name'].length; i++)
        {
            attach_name += '<p>' + o['attach_name'][i]['key'] + "：" + o['attach_name'][i]['value'] + '</p>';
        }
    }
    if(o['content']){
        for(var i = 0; i < o['content'].length; i++)
        {
            content += (content != '' ? '<hr>' : '') + '<div class="mall-item-popup-content">' + o['content'][i] + '</div>';
        }
    }
    //console.log(o);
    var html = '<div class="mall-item-popup-box mall-item-popup-box-hide mall-item-popup-' + color + '"><div class="mall-item-popup-info"><div class="item-icon"><div class="mall-item-icon"><img src="' + o['icon_url'] + '" alt=""></div></div><div class="item-info"><h2>' + o['name'] + '</h2>' + num + old_price + new_price + attach_name + '</div></div>' + content + '</div>';
    $('.mall-item-popup-box').remove();
    $('body').append(html);
    var left = e.offset().left + e.outerWidth();
    var top = e.offset().top;
    /*left = (left + $('.mall-item-popup-box').outerWidth() > $('body').width() ? e.offset().left - $('.mall-item-popup-box').outerWidth() : left);
    top = (top + $('.mall-item-popup-box').outerHeight() > $('body').height() ? e.offset().top - $('.mall-item-popup-box').outerHeight() : top);
    left = (left < e.offset().left + e.outerWidth() ? e.offset().left - $('.mall-item-popup-box').outerWidth() : left);
    left = (left < 0 ? 0 : left);
    top = (top < 0 ? 0 : top);*/
    // 判断左边+宽度是否超过了body，如果左边够的话移到左边，不然还在右边
    if(left + $('.mall-item-popup-box').width() > $('body').width() && e.offset().left - $('.mall-item-popup-box').width() >= 0)
    {
        left = e.offset().left - $('.mall-item-popup-box').outerWidth();
    }
    // 判断顶边+高度是否超过了body，是的话靠底边，但如果顶边超出后以顶边为准
    if(top + $('.mall-item-popup-box').height() > $('body').height())
    {
        top = $('body').height() - $('.mall-item-popup-box').height();
        top = (top < 0 ? 0 : top);
    }
    $('.mall-item-popup-box').css({
        'top':top+'px',
        'left':left+'px'
    });
    $('.mall-item-popup-box').removeClass('mall-item-popup-box-hide');
}


/**
 * 要展示购买的商品界面
 * @param {*} o JSON对象：JSON.parse(Base64.decode(obj));
 */
var showMallBuyBox = function(item_base64_str){
    var o = JSON.parse(Base64.decode(item_base64_str));
    var num = '',old_price = '',new_price = '', attach_name = '';
    if(o['num']) num = '<p>数量：' + o['num'] + '个</p>';
    if(o['old_price']) old_price = '<p>原价：<span class="gray deleteline">' + o['old_price'] + '</span>' + o['unit'] + '</p>';
    if(o['new_price']) new_price = '<p>现价：<span class="yellow">' + o['new_price'] + '</span>' + o['unit'] + '</p>';
    if(o['attach_name']){
        for(var i = 0; i < o['attach_name'].length; i++)
        {
            attach_name += '<p>' + o['attach_name'][i]['key'] + "：" + o['attach_name'][i]['value'] + '</p>';
        }
    }
    var html = '<div class="mall-shopping-box"><div class="mall-full-box mall-full-box-hide"><div class="mall-buy-confirm"><div class="mall-item-icon mall-item-need-popup-tips" data-tips="' + item_base64_str + '"><img src="' + o['icon_url'] + '" alt=""></div><h2>' + o['name'] + '</h2>' + num + old_price + new_price + attach_name + '<br><input class="button-big" type="button" value="立即购买" data-token="' + o['token'] + '"></div></div></div>';
    $('.mall-shopping-box').remove();
    $('body').append(html);
    setTimeout(function(){
        $('.mall-shopping-box .mall-full-box').removeClass('mall-full-box-hide');
    },50);
    $('.mall-shopping-box .button-big').off('click').on('click',function(){
        var token = $(this).attr('data-token');
        $('.mall-shopping-box').click();
        if(token)
        {
            setTimeout(function(){
                MallBuyGoods(token);
            },500);
        }
    });
}
/**
 * 购买商品
 * @param {*} token 
 */
var MallBuyGoods = function(token){
    $.ajax({
        url:'action',type:'post',dataType:'json',data:{action:token},
        success:function(res){
            if(res['return_code'] == 'item') // 显示ITEM
            {
                MsgBox.prize(res['return_msg']);
            }else if(res['return_code'] == 'success')
            {
                MsgBox.notice(res['return_msg']);
                try
                {
                    $('#user-cash').html(res['data']['user_cash']);
                }
                catch (e)
                {
                    console.log(e);
                }
                
            }else{
                MsgBox.notice(res['return_msg']);
            }
        },
        error:function(x,i,e){
            MsgBox.notice(e);
        }
    })
}
/**
 * 显示登录框
 */
var showLoginBox = function(str){
    var html = '<div class="mall-user-login-box"><div><div class="mall-user-login-inline-box mall-user-login-inline-box-hide"><form action="#" onsubmit="return false"><input type="hidden" name="action" value="'+str+'"><div class="mall-login-input"><p><input type="text" name="username" id="username" placeholder="账号" maxlength="30"></p></div><div class="mall-login-input mall-login-password"><p><input type="password" name="password" id="password" placeholder="密码" maxlength="30"></p></div><div class="mall-login-input"><p><input type="submit" value="登 录"></p></div></form></div></div></div>';
    $('body').append(html);
    setTimeout(function(){
        $('.mall-user-login-inline-box').removeClass('mall-user-login-inline-box-hide');
        $('.mall-user-login-inline-box input[name=username]').focus();
    },50);
    $('.mall-user-login-inline-box form').off('submit').on('submit',function(){
        var username = $(this).find('input[name=username]').val();
        var password = $(this).find('input[name=password]').val();
        var obj = $(this);
        if(obj.find('input[type=submit]').attr('disabled')) return false;
        if(!username){
            MsgBox.notice('请输入账号！');
            return false;
        }
        if(!password){
            MsgBox.notice('请输入密码！');
            return false;
        }
        if(!/^[A-Za-z0-9_]{1,30}$/.test(username))
        {
            MsgBox.notice('账号仅限英文字母、数字和下划线，长度1~30个字符。');
            return false;
        }
        if(!/^[A-Za-z0-9_]{1,30}$/.test(password))
        {
            MsgBox.notice('密码仅限英文字母、数字和下划线，长度1~30个字符。');
            return false;
        }
        if(obj.find('.mall-login-select-char').length > 0)
        {
            if(!obj.find('select[name=char]').val())
            {
                MsgBox.notice('请选择登录角色。');
                return false;
            }
        }
        var submitData = obj.serializeArray();
        obj.find('input[type=submit]').attr('disabled', true);
        obj.find('input[type=submit]').val('登录中...');
        $('.mall-user-login-box .mall-login-select-char').remove(); 
        $.ajax({
            url:'action',
            type:'post',
            dataType:'json',
            data:submitData,
            success:function(res){
                if(res['return_code'] == 'select'){
                    var html = '<option value="">请选择角色</option>';
                    for(var i = 0; i < res['data'].length; i++)
                    {
                        html += '<option value="' + res['data'][i]['usn'] + '">' + res['data'][i]['name'] + '</option>';
                    }
                    html = '<div class="mall-login-input mall-login-select-char"><p><select name="char">' + html + '</select></p></div>';
                    $('.mall-user-login-box .mall-login-select-char').remove();    // 移除旧的
                    $('.mall-user-login-box .mall-login-password').append(html);
                    obj.find('input[type=submit]').attr('disabled', false);
                    obj.find('input[type=submit]').val('继 续');
                }else if(res['return_code'] == 'success'){
                    MsgBox.notice(res['return_msg'],function(){
                        location.reload();
                    },500);
                    obj.find('input[type=submit]').val('登录成功，请稍后...');
                }else{
                    MsgBox.notice(res['return_msg']);
                    obj.find('input[type=submit]').attr('disabled', false);
                    obj.find('input[type=submit]').val('登 录');
                }
                
            },error:function(x,i,e){
                MsgBox.notice(e);
                obj.find('input[type=submit]').attr('disabled', false);
                obj.find('input[type=submit]').val('登 录');
            }
        });
        return false;
    });
}
var UserLogout = {
    Logout:function(token)
    {
        MsgBox.confirm('要退出登录吗？', ['退出登录', '取消'],function(i){
            if(i == 0)
            {
                $.ajax({
                    url:'action',
                    type:'post',
                    dataType:'json',
                    data:{action:token},
                    success:function(res){
                        if(res['return_code'] == 'success'){
                            MsgBox.notice(res['return_msg'],function(){
                                location.reload();
                            },500);
                        }else{
                            MsgBox.notice(res['return_msg']);
                        }
                    },error:function(x,i,e){
                        MsgBox.notice(e);
                    }
                });
            }
        });
    }
}

var MsgBox = {
    __MakeHtml : function(str, type, btn,call,outtimes)
    {
        // 生成消息框HTML
        var cls = '',btnHtml = '';
        if(0 == type)
        {
            cls = 'mall-msg-box-none'; // 可点击空白区域关闭
        }
        else if(1 == type)
        {
            cls = 'mall-msg-box-button'; // 不可点击空白区域关闭，有按钮
            if(typeof btn == 'object')
            {
                for(var i = 0; i < btn.length; i++)
                {
                    btnHtml += '<input type="button" value="' + btn[i] + '">';
                }
                if(btnHtml)
                {
                    btnHtml = '<div class="mall-msg-box-btn">' + btnHtml + '</div>';
                }
            }
        }else if(2 == type)
        {
            cls = 'mall-msg-box-prize'; // 可点击空白区域关闭
            // 解析物品
            var arr = JSON.parse(Base64.decode(str));
            if(typeof arr == 'object')
            {
                str = '';
                for(var i = 0; i < arr.length; i++)
                {
                    str += MakeItemIcon.makeOnce(arr[i]);
                }
            }
        }else if(3 == type)
        {
            cls = 'mall-msg-box-button mall-msg-box-loading'; // 不可点击空白区域关闭
            if(str == null)
            {
                $('.mall-msg-box-loading').parent().parent().remove();
                return null;
            }
        }else{
            return null;
        }
        var element_idx = '__msg_box_' + parseInt((Math.random() * 1000000));
        var html = '<div id="' + element_idx + '" class="mall-user-login-box mall-msg-box-bg"><div><div class="mall-msg-box mall-msg-box-hide ' + cls + '">' + str + btnHtml + '</div></div></div>';
        $('body').append(html);
        setTimeout(function(){
            $('#' + element_idx + ' .mall-msg-box').removeClass('mall-msg-box-hide');
        },50);
        $('#' + element_idx).focus(); // 可以设置元素焦点？
        if(outtimes > 0)
        {
            setTimeout(function(){
                $('#' + element_idx + ' .mall-msg-box').addClass('mall-msg-box-hide');
                setTimeout(function(){
                    $('#' + element_idx).remove();
                    if(typeof call === 'function') call();
                },500);
            }, outtimes + 500);
        }
        $('#' + element_idx).off('click').on('click',function(){
            if($('#' + element_idx + ' .mall-msg-box').is('.mall-msg-box-button')){
                return false;
            }
            $('#' + element_idx + ' .mall-msg-box').addClass('mall-msg-box-hide');
            setTimeout(function(){
                $('#' + element_idx).remove();
                if(typeof call === 'function') call();
            },500);
        }).off('keydown').on('keydown',function(e){
            console.log(e.which);
        });
        $('#' + element_idx + ' .mall-msg-box-btn input[type=button]').off('click').on('click',function(){
            $('#' + element_idx + ' .mall-msg-box').addClass('mall-msg-box-hide');
            var idx = $(this).index();
            setTimeout(function(){
                $('#' + element_idx).remove();
                if(typeof call === 'function')
                {
                    call(idx);
                }
            },500);
        });

    },
    notice: function(str,call,outtimes)
    {
        // 通知类，点击空白关闭
        this.__MakeHtml(str, 0,null, call, outtimes);
    },
    confirm:function(str,btn,call)
    {
        // 询问类，不可点击空白关闭
        this.__MakeHtml(str, 1, btn, call);
    },
    prize: function(str)
    {
        // 通知类，点击空白关闭
        this.__MakeHtml(str, 2);
    },
    loading:function(str)
    {
        // 加载类，点击空白不可关闭
        this.__MakeHtml(str, 3);
    }
}

var DateTime = {
    sec_format:function(seconds)
    {
        var day,hour,minute,second,str = '';
        day = Math.floor(seconds / (60 * 60 * 24));
        hour = Math.floor((seconds % (60 * 60 * 24)) / (60 * 60));
        minute = Math.floor((seconds % (60 * 60)) / 60);
        second = Math.floor(seconds % 60);
        if(day > 0) str = day + '天';
        str += hour + '时' + minute + '分' + second + '秒';
        return str;
    },
    setObjInterval:function(e,disabled_ele)
    {
        var sec = parseInt(e.attr('data-seconds'));
        if(sec)
        {
            var hTimer = setInterval(function(){
                sec --;
                if(sec < 0)
                {
                    if(e.attr('data-end-class')) e.addClass(e.attr('data-end-class'));
                    if(e.attr('data-sec-end-tips'))
                    {
                        e.html(e.attr('data-sec-end-tips'));
                    }else{
                        e.html('已结束');
                    }
                    clearInterval(hTimer);
                    return;
                }
                if(e.attr('data-sec-format'))
                {
                    var str = e.attr('data-sec-format').format(DateTime.sec_format(sec));
                }else{
                    var str = DateTime.sec_format(sec);
                }
                e.html(str);
            },1000);
        }
    }
}

String.prototype.format = function(){
    if(arguments.length==0){
        return this;
    }
        for(var s=this, i=0; i<arguments.length; i++){
        s = s.replace(new RegExp("\\{"+i+"\\}","g"), arguments[i]);
    }
    return s;
}