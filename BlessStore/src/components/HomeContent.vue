<template>
    <div>
        <a-layout>
            <!-- 标题区域 -->
            <a-layout-header style="background-color: #545c64;">

                <div class="logo" style="float:left" />

                <div class="userInfoArea" style="float: right;">
                    <div v-if="userInfo.token ? true : false" style="margin-right: -50px;">
                        <span style="color:darkorange;">{{ userInfo.username }}</span>
                        <a-button class="custom-button-login" type="text" style="color: #1890ff;">{{ userInfo.uCash
                        }}</a-button>
                        <a-button style="margin-top: 16px;margin-right: 10px;" @click="Logout">登出</a-button>
                    </div>
                    <div class="loginBtn" v-else style="margin-right: -50px;">
                        <a-button class="custom-button-login" type="text" style="color: #1890ff;"
                            @click="handleClick">login</a-button>
                    </div>
                </div>
                <div style="clear: both;"></div>
            </a-layout-header>

            <!-- 下半Content区域 -->
            <a-layout-content style="margin-top: 20px;">
                <a-layout>
                    <a-layout-sider v-model:collapsed="userInfo.collapsed" :trigger="null" collapsible>

                        <a-menu v-model:selectedKeys="selectedKeys" theme="dark" mode="inline">
                            <a-menu-item key="1">
                                <shop-outlined />
                                <span>{{ storeText }}</span>
                            </a-menu-item>
                            <a-menu-item key="2" :disabled="isDisabled">
                                <qq-outlined />
                                <span>{{ GMTool }}</span>
                            </a-menu-item>
                        </a-menu>
                    </a-layout-sider>

                    <!-- Content区域 -->
                    <a-layout>
                        <a-layout-header style="background: #fff; padding: 0">
                            <menu-unfold-outlined v-if="userInfo.collapsed" class="trigger" @click="changeCollapsed()" />
                            <menu-fold-outlined v-else class="trigger" @click="(changeCollapsed())" />

                            <div class="searchArea" style="float: right;margin-top: 20px;margin-right: 20px;">
                                <a-input-search v-model:value="searchName" placeholder="搜索" style="width: 200px"
                                    @search="onSearch(searchName)" />
                            </div>
                        </a-layout-header>
                        <a-layout-content
                            :style="{ margin: '24px 16px', padding: '24px', background: '#fff', minHeight: '280px' }">


                            <HomeList />

                        </a-layout-content>
                    </a-layout>
                </a-layout>

            </a-layout-content>

            <a-layout-footer style="margin-bottom: 20px;text-align: center">
                {{ footerText }}
            </a-layout-footer>


            <!-- 登录框 -->
            <a-modal v-model:visible="visible" title="登录" width="400px" ok-text="登录" cancel-text="取消" @ok="handleLogin"
                @cancel="handleCancel">
                <div class="loginModalContent" style="margin-left: 50px;">
                    <a-input style="width: 250px;" v-model:value="username" placeholder="输入用户名">
                        <template #prefix>
                            <user-outlined type="user" />
                        </template>
                    </a-input>
                    <a-input-password style="margin-top: 20px;width: 250px;" v-model:value="password" placeholder="输入密码" />

                    <a-select v-if="charVisible" style="margin-top:20px;width: 250px" v-model:value="charSelect"
                        placeholder="选择角色" @change="handleChange">

                        <a-select-option v-for="option in options" :key="option.DB_ID" :value="option.DB_ID">
                            {{ option.Player_Name }}
                        </a-select-option>
                    </a-select>
                </div>
            </a-modal>
        </a-layout>
    </div>
</template>
<script lang="js" setup>
import { MenuUnfoldOutlined, MenuFoldOutlined, ShopOutlined, QqOutlined, UserOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue'
import { ref } from 'vue';
import { userInfoStore } from "../store/store";

import HomeList from './HomeList.vue'
import { login} from '../api/protocol'

//vue变量
const userInfo = userInfoStore();

//变量定义区
const selectedKeys = ref(['1']);
const isDisabled = ref(true);
const visible = ref(false);
const username = ref("");
const password = ref("");
const options = ref([]);
const charVisible = ref(false);
const charSelect = ref("");
const searchName =ref("")
//文字显示变量定义 
let storeText = ref("商城");
let GMTool = ref("GM工具");
let footerText = ref("开发版本，仅作测试用");

const onSearch = (value)=>{
    userInfo.updateSearchName(value)
}
const handleChange = (value) => {
    
}

const changeCollapsed = () => {
    userInfo.updateCollapsed();
}
//函数区域
const handleClick = () => {

    charVisible.value = false
    visible.value = true
}

const handleCancel = () => {
    username.value = "";
    password.value = "";
    options.value = [];
    charVisible.value = false;
    charSelect.value = "";
}

const handleLogin = () => {
    let data = {
        "username": username.value,
        "password": password.value,
        "char": charSelect.value
    }

    login(data).then(function (response) {

        if (response.code != 200 && response.code != 201) {
            if (response.code != 405) {
                charVisible.value = false;
            }

            message.error(response.msg)
        }
        else {
            console.log(response.data)

            if (response.code == 200) {
                //登录步骤1
                charVisible.value = true;
                options.value = response.data.charList;
            }
            else if (response.code == 201) {
                window.localStorage.setItem("token", response.data.token)
                window.localStorage.setItem("uCash", response.data.uCash);
                window.localStorage.setItem("username", response.data.username);
                window.localStorage.setItem("charId", response.data.charId);

                userInfo.updateToken(response.data.token);
                userInfo.updateUCash(response.data.uCash);
                userInfo.updateUserName(response.data.username);
                userInfo.updateCharId(response.data.charId);

                visible.value = false;

                message.success(response.msg)
            }

        }

    }).catch(function (error) {
        console.log(error)
    })
}

const Logout = () => {
    window.localStorage.clear();
    userInfo.clearAll();
}
</script>


<style scoped>
.logo {
    width: 100px;
    height: 32px;
    background: rgba(255, 255, 255, 0.3);

    margin-top: 16px;
    margin-left: -20px;
}

#components-layout-demo-custom-trigger .trigger {
    font-size: 18px;
    line-height: 64px;
    padding: 0 24px;
    cursor: pointer;
    transition: color 0.3s;
}

#components-layout-demo-custom-trigger .trigger:hover {
    color: #1890ff;
}

.site-layout .site-layout-background {
    background: #fff;
}

.loginBtn .ant-btn.custom-button-login {
    color: red !important;
}
</style>