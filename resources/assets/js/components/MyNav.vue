<template>
    <aside style="height: 1700px; border-right: solid 1px #e6e6e6; background-color: #fff">
    <el-menu
    default-active="2"
    class="el-menu-vertial-demo"
    @open="handleOpen"
    @close="handleClose"
    >
        <el-submenu index="1">
            <template slot="title">
                <i class="el-icon-location"></i>
                <span>股市行情</span>
            </template>
            <el-menu-item index="1-1">沪深股市</el-menu-item>
            <el-menu-item index="1-2">选项2</el-menu-item>
        </el-submenu>
        <el-submenu index="2">
            <template slot="title">
                <i class="el-icon-menu"></i>
                <span>股票预警</span>
            </template>
            <el-menu-item index="2-1">预警设置</el-menu-item>
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
    </aside>
</template>
<script>
    export default {
        methods: {
            handleOpen(key, keyPath) {
                console.log(key, keyPath);
            },
            handleClose(key, keyPath) {
                console.log(key, keyPath);
            }
        }
    }
</script>