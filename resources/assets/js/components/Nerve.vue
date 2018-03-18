<template>
    <el-container>
        <el-aside
            width="15%">
            <myNav></myNav>
        </el-aside>
        <el-main>
            <router-view></router-view>
        </el-main>
    </el-container>
</template>

<script>
    import myNav from './MyNav.vue'
    export default {
        components: {
            myNav
        }
    }
</script>
