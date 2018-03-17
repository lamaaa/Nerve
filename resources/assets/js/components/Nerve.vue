<template>
    <div id="app">
        <el-row>
            <el-col :span="3">
                <myNav></myNav>
            </el-col>
            <el-col :span="21">
                <router-view></router-view>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import myNav from './MyNav.vue'
    export default {
        components: {
            myNav
        }
    }
</script>
