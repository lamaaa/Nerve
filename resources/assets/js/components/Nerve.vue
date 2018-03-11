<template>
    <div id="app">
        <el-row>
            <div style="background-color: #000000;">
                <el-col :span="4">
                    <MyNav></MyNav>
                </el-col>
            </div>
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
