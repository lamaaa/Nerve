<template>
    <el-container>
        <el-aside>
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
