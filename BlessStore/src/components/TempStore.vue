<template>
    <el-container>
        <el-header>
            <el-menu :default-active="activeIndex" class="el-menu-demo" mode="horizontal" background-color="#545c64"
                text-color="#fff" active-text-color="#ffd04b" @select="handleSelect">
                <el-menu-item index="1">
                    商城
                </el-menu-item>
                <el-menu-item index="2" :disabled="isDisabled">
                    <i class="el-icon-house"></i>
                    GM工具
                </el-menu-item>
            </el-menu>
        </el-header>

        <el-main>Main</el-main>
        <el-footer>Footer</el-footer>
    </el-container>
</template>
  
<script lang="js" setup>
import { ref } from 'vue'
import HomeList from "./HomeList.vue";
const activeIndex = ref('1')
const isDisabled = ref(true)


const handleSelect = (key, keyPath) => {
    console.log(key, keyPath)
}
</script>
  