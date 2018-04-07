<template>
    <section style="background-color: #fff; position: relative; overflow: auto; height: 100%; border-radius: 10px; padding: 10px; -webkit-border-radius: 10px; -moz-border-radius: 10px">
        <div style="width: 60%; margin: 60px auto;">
            <el-form
            :data="passwordChangeForm">
                <el-form-item label="旧密码" label-width="100px">
                    <el-input type="password" v-model="passwordChangeForm.oldPassword"></el-input>
                </el-form-item>
                <el-form-item label="新密码" label-width="100px">
                    <el-input type="password" v-model="passwordChangeForm.password"></el-input>
                </el-form-item>
                <el-form-item label="确认新密码" label-width="100px">
                    <el-input type="password" v-model="passwordChangeForm.password_confirmation"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-col :offset="4" :span="10">
                        <el-button type="primary" @click="onSubmit()">重置密码</el-button>
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
                passwordChangeForm: {
                    password: '',
                    password_confirmation: '',
                    oldPassword: '',
                }
            }
        },
        methods: {
            onSubmit() {
                axios.put('api/v1/users/password', this.passwordChangeForm).then((response) => {
                    if (response.status === 204) {
                        this.$message.success('修改成功！');
                    }
                }).catch((error) => {
                    if (error.response.status === 403) {
                        this.$message.error('密码错误！');
                    } else {
                        this.$message.error('修改失败！');
                        console.log(error);
                    }
                });
            },
        }
    }
</script>