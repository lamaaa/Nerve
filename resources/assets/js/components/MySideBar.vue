<template>
    <el-menu
    router
    class="el-menu-vertical-all-screen"
    background-color="#545c64"
    text-color="#fff"
    active-text-color="#ffd04b"
    >
        <el-submenu index="1">
            <template slot="title">
                <i class="el-icon-location"></i>
                <span>股市行情</span>
            </template>
            <el-menu-item index="sh-sz-stock">沪深股市</el-menu-item>
        </el-submenu>
        <el-submenu index="2">
            <template slot="title">
                <i class="el-icon-menu"></i>
                <span>股票预警</span>
            </template>
            <el-menu-item index="warning-config">预警设置</el-menu-item>
            <el-menu-item index="2-2">预警历史</el-menu-item>
        </el-submenu>
        <el-menu-item index="3">
            <i class="el-icon-document"></i>
            <span slot="title">导航三</span>
        </el-menu-item>
        <el-menu-item index="4">
            <i class="el-icon-setting"></i>
            <span slot="title">导航四</span>
        </el-menu-item>
    </el-menu>
</template>

<style>
    .el-menu-vertical-all-screen {
        height: calc(100% - 50px);
    }

</style>

<script>
    export default {
        methods: {
        }
    }
</script>