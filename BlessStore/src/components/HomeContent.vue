<!-- <template>
    <div>
        <a-tabs v-model:activeKey="activeKey">
            <a-tab-pane key="1">
                <template #tab>
                    <span>
                        <shopping-filled />
                        商城
                    </span>
                </template>
                <HomeList />
            </a-tab-pane>
            <a-tab-pane key="2" :disabled="isDisabled">
                <template #tab>
                    <span>
                        <qq-square-filled />
                        GM工具
                    </span>
                </template>
                Tab 2
            </a-tab-pane>
        </a-tabs>
    </div>
</template>
<script setup >
import { defineComponent, ref } from 'vue';
import { ShoppingFilled, QqSquareFilled } from '@ant-design/icons-vue'

import HomeList from './HomeList.vue'
import Store from './TempStore.vue'


let isDisabled=true;

</script> -->

<template>
    <a-layout>
        <a-layout-header class="header" style="height: 50px;margin-bottom: 10px;">
            <div class="logo" />
        </a-layout-header>

        <a-layout>
            <a-layout-sider v-model:collapsed="collapsed" :trigger="null" collapsible>
                <div class="logo" />
                <a-menu v-model:selectedKeys="selectedKeys" theme="dark" mode="inline">
                    <a-menu-item key="1">
                        <shopping-filled />
                        <span>商城</span>
                    </a-menu-item>
                    <a-menu-item key="2" :disabled="isDisabled">
                        <qq-square-filled />
                        <span>GM工具</span>
                    </a-menu-item>
                </a-menu>
            </a-layout-sider>

            <a-layout>
                <a-layout-header style="background: #fff; padding: 0">
                    <menu-unfold-outlined v-if="collapsed" class="trigger" @click="() => (collapsed = !collapsed)" />
                    <menu-fold-outlined v-else class="trigger" @click="() => (collapsed = !collapsed)" />
                </a-layout-header>
                <a-layout-content :style="{ margin: '24px 16px', padding: '24px', background: '#fff', minHeight: '280px' }">
                    <HomeList />
                </a-layout-content>
            </a-layout>
        </a-layout>

        <a-layout-footer>Footer</a-layout-footer>
    </a-layout>
</template>
<script setup>
import { ShoppingFilled, QqSquareFilled ,MenuUnfoldOutlined, MenuFoldOutlined} from '@ant-design/icons-vue'
import HomeList from './HomeList.vue'

import { ref } from 'vue';

const selectedKeys = ref(['1']);
const collapsed = ref(false);
const isDisabled = ref(true);
</script>

<style>
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

#components-layout-demo-custom-trigger .logo {
    height: 32px;
    background: rgba(255, 255, 255, 0.3);
    margin: 16px;
}

.site-layout .site-layout-background {
    background: #fff;
}
</style>