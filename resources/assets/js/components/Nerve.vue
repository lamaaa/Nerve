<template>
    <div id="app">
        <el-row>
            <el-col :span="3">
                <MyNav></MyNav>
            </el-col>
            <el-col :span="21">
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
