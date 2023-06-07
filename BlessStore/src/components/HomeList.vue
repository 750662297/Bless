<template>
    <a-list :grid="{ gutter: 24, xs: 1, sm: 2, md: 4, lg: 4, xl: 5, xxl: 7, xxxl: 9 }" :data-source="data">
        <template #renderItem="{ item }">
            <a-list-item>

                <a-card hoverable>
                    <template #cover>
                        <img alt="example" src="https://gw.alipayobjects.com/zos/rmsportal/JiqGstEfoWAOHiTxclqi.png" />
                    </template>
                    <template #actions>
                        <a-button style="border: 0px;background-color: transparent;" @click="shopAction">
                            <plus-outlined key="shop"/>
                        </a-button>
                        
                        <a-button style="border: 0px;background-color: transparent;" disabled>
                            <shopping-cart-outlined  key="addToList"/>
                        </a-button>
                        <a-button style="border: 0px;background-color: transparent;" disabled>
                            <ellipsis-outlined key="ellipsis"/>
                        </a-button>
                        
                    </template>
                    <!-- <a-card-meta :title="item.title"></a-card-meta> -->
                    <a-card-meta>
                        <template #description>{{ item.title }}</template>
                    </a-card-meta>
                </a-card>

                <!-- <a-card :title="item.title">Card content</a-card> -->
            </a-list-item>
        </template>
    </a-list>
</template>
<script setup>
import {EllipsisOutlined,ShoppingCartOutlined, PlusOutlined} from '@ant-design/icons-vue';
import { ref } from 'vue';
import data from '../../public/mall.json'

const data = ref([{
    title: 'Title 1',
}, {
    title: 'Title 2',
}, {
    title: 'Title 3',
}, {
    title: 'Title 4',
}, {
    title: 'Title 5',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}, {
    title: 'Title 6',
}]);


const shopAction =()=>{

}
</script>

