<template>
    <section style="background-color: #fff; position: relative; overflow: auto; height: 100%; border-radius: 10px; padding: 10px; -webkit-border-radius: 10px; -moz-border-radius: 10px">
        <div style="width: 60%; margin: 60px auto;">
            <el-form
            :data="userInfoForm">
                <el-form-item label="用户名" label-width="80px">
                    <el-input v-model="userInfoForm.username"></el-input>
                </el-form-item>
                <el-form-item label="邮箱" label-width="80px">
                    <el-input v-model="userInfoForm.email"></el-input>
                </el-form-item>
                <el-form-item label="手机号" label-width="80px">
                    <el-input v-model="userInfoForm.phone"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-col :offset="4" :span="10">
                        <el-button type="primary" @click="onSubmit()">修改</el-button>
                    </el-col>
                </el-form-item>
            </el-form>
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                userInfoForm: {
                    username: '',
                    email: '',
                    phone: '',
                    userId: '',
                }
            }
        },
        methods: {
            onSubmit() {
                axios.put('/api/v1/users', this.userInfoForm).then((response) => {
                    if (response.status === 204) {
                        this.$message.success('修改成功！');
                    }
                }).catch((error) => {
                    this.$message.error('修改失败！');
                    console.log(error);
                });
            },
            getUserInfo() {
                axios.get('api/v1/users/info').then((response) => {
                    if (response.status === 200 && response.data !== null
                        && response.data.data !== null) {
                        let data = response.data.data;
                        this.userInfoForm.username = data.username;
                        this.userInfoForm.email = data.email;
                        this.userInfoForm.phone = data.phone;
                        this.userInfoForm.userId = data.id;
                    }
                }).catch((error) => {
                    console.log(error);
                });
            },
        },
        created: function () {
            this.$nextTick(function () {
                this.getUserInfo();
            })
        }
    }
</script>