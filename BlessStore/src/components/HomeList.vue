<template>
    <a-list :grid="{ gutter: 24, xs: 1, sm: 2, md: 4, lg: 4, xl: 5, xxl: 7, xxxl: 9 }" :data-source="data">
        <template #renderItem="{ item }">
            <a-list-item>

                <a-card hoverable>
                    <template #actions>
                        <a-button style="border: 0px;background-color: transparent;" @click="shopAction(item)">
                            <plus-outlined key="shop"/>
                        </a-button>
                        
                        <a-button style="border: 0px;background-color: transparent;" disabled>
                            <shopping-cart-outlined  key="addToList"/>
                        </a-button>
                        <a-button style="border: 0px;background-color: transparent;" disabled>
                            <ellipsis-outlined key="ellipsis"/>
                        </a-button>
                        
                    </template>
                    <a-card-meta >
                        <template #title>
                            <a-tag color="blue">{{ item.item.new_price}}</a-tag>
                            <!-- <span></span> -->
                        </template>
                        <template #avatar>
                            <img alt="example" :src="item.item.icon_url" />
                        </template>
                        <template #description>{{ item.item.name }}</template>
                    </a-card-meta>
                </a-card>

                <!-- <a-card :title="item.title">Card content</a-card> -->
            </a-list-item>
        </template>
    </a-list>
</template>
<script setup>
import {EllipsisOutlined,ShoppingCartOutlined, PlusOutlined} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { ref} from 'vue';
import { userInfoStore } from "../store/store";

import jsonData from '../../public/mall.json'
import { buyItem } from '../api/protocol';


const userInfo = userInfoStore();

//绑定vuex及ref区
const data = ref([]);
data.value = jsonData;
const isShowList = ref(userInfo.token ? true : false); //骨架屏和list的切换


const shopAction =(item)=>{
    console.log(item)

    let data ={
        username:userInfo.username,
        charId:userInfo.charId,
        info:item.item.info,
        price:item.item.new_price
    }

    buyItem(data).then(function (response){
        if(response.code !=200)
        {
            message.error(response.msg)
        }
        else{
            window.localStorage.setItem("uCash",response.data.cash)
            userInfo.updateUCash(response.data.cash)
            message.success(response.msg)
        }
    }).catch(function (error){
        console.log(error)
    })
}
</script>

