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

                                <a-button style="border: 0px;background-color: transparent;" @click="ShowItemModal(item)">
                                    <ellipsis-outlined key="ellipsis" />
                                </a-button>
                            </template>
                            <a-card-meta>
                                <template #title>
                                    <a-tag color="blue">{{ item.price }}</a-tag>
                                    <a-tag color="pink" v-if="item.tag">{{ item.tag }}</a-tag>
                                    <!-- <span></span> -->
                                </template>
                                <template #avatar>
                                    <img alt="example" :src="item.iconUrl" />
                                </template>
                                <template #description>{{ item.showName }}</template>
                            </a-card-meta>
                        </a-card>

                        <a-modal class="UpdateItemInfo" v-model:visible="isShowUpdateItem" :mask="false" title="更新商品信息" width="400px" ok-text="提交" v-model="modalItem"
                            cancel-text="取消" @ok="updateItemInfo()">
                            <div class="ItemInfoArea" style="margin-left: 50px;">
                                <a-input style="width: 250px;" v-model:value="modalItem.name" placeholder="商品名称" />
                                <a-input style="width: 250px;margin-top: 10px;" v-model:value="modalItem.price" placeholder="商品价格" />
                                <a-input style="width: 250px;margin-top: 10px;" v-model:value="modalItem.num" placeholder="商品数量" />
                                <a-input style="width: 250px;margin-top: 10px;" v-model:value="modalItem.desc" placeholder="商品描述" />
                                <a-input style="width: 250px;margin-top: 10px;" v-model:value="modalItem.tag"
                                    placeholder="商品标签，比如装备，武器，材料等" />
                            </div>
                        </a-modal>
                        <!-- <a-card :title="item.title">Card content</a-card> -->
                    </a-list-item>
                </template>
            </a-list>

            <a-pagination v-model:current="currentPage" v-model:page-size="pageSize" :total="totalCount"
                :hideOnSinglePage="true" :pageSizeOptions="pageSizeOptions" :show-total="total => `共有${total}个符合条件的商品`" />
        </div>

        <div class="skeletonArea" v-else>
            <a-skeleton active :title="false" :avatar="false" size="large" :paragraph="{ rows: 11, width: ['70%'] }" />

            <a-skeleton-button :active="false" size="large" shape="default" :block="true" />
        </div>
    </div>
</template>
<script setup>
import { EllipsisOutlined, ShoppingCartOutlined, PlusOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { getCurrentInstance, ref, watchEffect } from 'vue';
import { userInfoStore } from "../store/store";

import { buyItem, queryList ,updateItem } from '../api/protocol';
import { func } from 'vue-types';

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
const isShowUpdateItem = ref(false)

//修改信息
const modalItem=ref(null)

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

            listData.value=[]
            for (let info of response.data.infoList) {
                info.showName = info.name + '*' + info.num;
                listData.value.push(info)
            }
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

    if(userInfo.token ==null)
    {
        return;
    }

    getItemList()
})

const ShowItemModal = (item) => {
    modalItem.value=item
    isShowUpdateItem.value = true;
}
const updateItemInfo = () => {
    let data ={
        name:modalItem.value.name,
        price:modalItem.value.price,
        num:modalItem.value.num,
        tag:modalItem.value.tag,
        desc:modalItem.value.desc,
        itemId:modalItem.value.itemId
    }

    updateItem(data).then(function (response){
        if(response.code!=200)
        {
            message.error(response.msg)
            return;
        }
        listData.value.forEach(item =>{
            if(item.itemId == response.data.info.itemId)
            {
                item=response.data.info
            }
        })
        message.success(response.msg)
    }).catch(function (error){
        console.log(error)
    })

    isShowUpdateItem.value = false;
}

const shopAction = (item) => {
    console.log(item)

    let data = {
        username: userInfo.username,
        charId: userInfo.charId,
        price: item.price,
        info:[{
            num: item.num,
            itemId:item.itemId
        }]
        
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

<style scoped>

</style>