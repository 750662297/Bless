<template>
    <div>
        <a-tabs v-model:activeKey="activeKey">
            <a-tab-pane key="1">
                <template #tab>
                    <span>
                        <shopping-filled />
                        商城
                    </span>
                </template>
                <!-- <HomeList /> -->
                <Store/>
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

</script>