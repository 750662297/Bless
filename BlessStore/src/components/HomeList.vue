<template>
    <div>
        <div class="listArea" v-if="isShowList">
            <a-list :grid="{ gutter: 24, xs: 1, sm: 2, md: 4, lg: 4, xl: 5, xxl: 7, xxxl: 9 }" :data-source="listData">
                <template #renderItem="{ item }">
                    <a-list-item>

                        <a-card hoverable>
                            <template #actions>
                                <a-button style="border: 0px;background-color: transparent;" @click="shopAction(item)">
                                    <plus-outlined key="shop" />
                                </a-button>

                                <a-button style="border: 0px;background-color: transparent;" disabled>
                                    <shopping-cart-outlined key="addToList" />
                                </a-button>
                                <a-button style="border: 0px;background-color: transparent;" disabled>
                                    <ellipsis-outlined key="ellipsis" />
                                </a-button>

                            </template>
                            <a-card-meta>
                                <template #title>
                                    <a-tag color="blue">{{ item.price }}</a-tag>
                                    <!-- <span></span> -->
                                </template>
                                <template #avatar>
                                    <img alt="example" :src="item.iconUrl" />
                                </template>
                                <template #description>{{ item.name }}</template>
                            </a-card-meta>
                        </a-card>

                        <!-- <a-card :title="item.title">Card content</a-card> -->
                    </a-list-item>
                </template>
            </a-list>

            <a-pagination v-model:current="currentPage" v-model:page-size="pageSize" :total="totalCount"
                :hideOnSinglePage="true" :pageSizeOptions="pageSizeOptions" :show-total="total => `共有${total}个符合条件的商品`" />
        </div>

        <div class="skeletonArea" v-else>
            <a-skeleton active :title="false" :avatar="false" size="large"  :paragraph="{ rows: 11, width: ['70%'] }"/>

            <a-skeleton-button :active="false" size="large" shape="default" :block="true"/>
        </div>
    </div>
</template>
<script setup>
import { EllipsisOutlined, ShoppingCartOutlined, PlusOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { getCurrentInstance, ref, watchEffect } from 'vue';
import { userInfoStore } from "../store/store";

import { buyItem, queryList } from '../api/protocol';


const userInfo = userInfoStore();
const { ctx } = getCurrentInstance();

//绑定vuex及ref区
const listData = ref([]);
const currentPage = ref(1);
const pageSize = ref(15);
const tag = ref('');
const totalCount = ref(0);
const totalPage = ref(0);
const pageSizeOptions = ['15', '30', '50', '100']

const isShowList = ref(false); //骨架屏和list的切换



// watchEffect =()=>{
//     if(userInfo.token)
//     {

//     }
// }

const getItemList = () => {
    let data = {
        page: currentPage.value,
        pageSize: pageSize.value,
        name: userInfo.searchName,
        tag: ''
    }

    queryList(data).then(function (response) {
        if (response.code != 200) {
            message.error(response.msg)
        }
        else {
            listData.value = response.data.infoList;
            currentPage.value = response.data.currentPage
            totalCount.value = response.data.totalCount
            totalPage.value = response.data.totalPage

            console.log(response)
        }
    }).catch(function (error) {
        console.log(error)
    })
}

watchEffect(() => {
    isShowList.value = (userInfo.token ? true : false)

    if (userInfo.token) {
        getItemList()
    }
})

const shopAction = (item) => {
    console.log(item)

    let data = {
        username: userInfo.username,
        charId: userInfo.charId,
        price: item.price,
        num: item.num
    }

    buyItem(data).then(function (response) {
        if (response.code != 200) {
            message.error(response.msg)
        }
        else {
            window.localStorage.setItem("uCash", response.data.cash)
            userInfo.updateUCash(response.data.cash)
            message.success(response.msg)
        }
    }).catch(function (error) {
        console.log(error)
    })
}
</script>

