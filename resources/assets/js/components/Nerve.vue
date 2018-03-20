<template>
    <el-container>
        <el-aside
        width="15%">
            <mySideBar></mySideBar>
        </el-aside>
        <el-main>
            <router-view></router-view>
        </el-main>
    </el-container>
</template>

<style>
    .el-main {
        height: calc(100% - 50px);
        padding: 10px;
    }
</style>

<script>
    import mySideBar from './MySideBar.vue';

    export default {
        components: {
            mySideBar,
        }
    }
</script>
