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
import { ref} from 'vue';
import jsonData from '../../public/mall.json'
import { buyItem } from '../api/protocol';
import { message } from 'ant-design-vue';
import { store} from "../store/index";

const data = ref([]);
data.value = jsonData;

const shopAction =(item)=>{
    console.log(item)

    let data ={
        username:store.username,
        charId:store.charId,
        index:item.serial,
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
            store.uCash = response.data.cash
            message.success(response.msg)
        }
    }).catch(function (error){
        console.log(error)
    })
}
</script>

