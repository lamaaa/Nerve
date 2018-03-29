<template>
    <el-container>
        <el-aside
        width="15%">
            <mySideBar></mySideBar>
        </el-aside>
        <el-main
        style="height: calc(100% - 50px); padding: 10px;">
            <router-view></router-view>
        </el-main>
    </el-container>
</template>

<style>
</style>

<script>
    import mySideBar from './MySideBar.vue';

    export default {
        components: {
            mySideBar,
        }
    }
</script>
