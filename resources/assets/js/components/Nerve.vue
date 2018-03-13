<template>
    <div id="app">
        <el-row>
            <el-col :span="4">
                <MyNav></MyNav>
            </el-col>
            <el-col :span="20">
                <router-view></router-view>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import MyNav from './MyNav.vue'
    export default {
        components: {
            MyNav
        }
    }
</script>
