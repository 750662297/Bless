<template>
    <div>
        <div class="logo" />
        <a-menu v-model:selectedKeys="selectedKeys" theme="dark" mode="horizontal" :style="{ lineHeight: '64px',background: '#545c64' }">
            <a-menu-item key="1">nav 1</a-menu-item>
            <a-menu-item key="2" :disabled="isDisabled">nav 2</a-menu-item>
        </a-menu>
    </div>
</template>

<script setup>
import { ref } from 'vue';

let isDisabled = true;
const selectedKeys = ref(['1']);

</script>