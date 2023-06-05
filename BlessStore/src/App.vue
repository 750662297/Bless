<script setup>
// This starter template is using Vue 3 <script setup> SFCs
// Check out https://vuejs.org/api/sfc-script-setup.html#script-setup
import Title from "./components/HomeTitle.vue"
import HomeContent from './components/HomeContent.vue';

</script>

<template>
	<div class="app">
		<Title />
		<HomeContent />
	</div>
</template>

<style>
#app {
	font-family: Avenir, Helvetica, Arial, sans-serif;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
	/* text-align: left; */
	color: #2c3e50;
	margin-top: 20px;
	margin-left: 30px;
	margin-right: 30px;
	height: 100%;
}
</style>
