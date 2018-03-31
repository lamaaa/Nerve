<template>
    <section id="warningConfigSection">
        <el-table
        border
        :data="warningConfigs"
        :span-method="warningConfigSpanMethod"
        v-loading="loadingWarningConfigs">
            <el-table-column
            prop="stock_code"
            label="股票代码">
            </el-table-column>
            <el-table-column
            prop="stock_name"
            label="股票名称">
            </el-table-column>
            <el-table-column
            min-width="230"
            label="预警描述">
                <template slot-scope="scope">
                    <el-col :offset="2" :span="20">
                        <el-input
                        size="10"
                        @change="handleValueChanged(scope.row)"
                        v-model="scope.row.value">
                            <template slot="prepend">{{ scope.row.description }}</template>
                            <template slot="append">时提醒我</template>
                        </el-input>
                    </el-col>
                </template>
            </el-table-column>
            <el-table-column
            min-width="150"
            label="预警方式">
                <template slot-scope="scope">
                    <el-col :offset="2" :span="20">
                        <el-checkbox-group
                        @change="handleCheckedNotificationTypesChanged(scope.row)"
                        v-model="scope.row.notification_types">
                            <el-checkbox v-for="notificationType in notificationTypes" :label="notificationType['name']" :key="notificationType['name']">
                                {{ notificationType['description'] }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </el-col>
                </template>
            </el-table-column>
            <el-table-column
                min-width="110"
            label="操作">
                <template slot-scope="scope">
                    <el-col :offset="2" :span="6">
                        <el-switch
                        @change="handleSwitchChanged(scope.row)"
                        style="margin-top: 9px;"
                        v-model="scope.row.switch">
                        </el-switch>
                    </el-col>
                    <el-col :offset="2" :span="8">
                        <el-button type="text" @click="confirmDeleteWarningConfig(scope.row)">删除</el-button>
                    </el-col>
                </template>
            </el-table-column>
        </el-table>
    </section>
</template>

<style>
    #warningConfigSection {
        background-color: #fff;
        height: 100%;
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;
        padding: 10px;
    }
</style>

<script>
    export default {
        data() {
            return {
                warningConfigs: [],
                userId: null,
                loadingWarningConfigs: true,
                notificationTypes: [],
                warningConfigsNumberArray: [],
                testIndex: 0,
                tableSpan: [],
            }
        },
        methods: {
            confirmDeleteWarningConfig(row) {
                this.$confirm('确定删除预警吗？', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.deleteWarningConfig(row.id);
                }).catch(() => {

                });
            },
            deleteWarningConfig(id) {
                axios.delete('/api/v1/warning-configs/' + id).then((response) => {
                    if (response.status === 204) {
                        this.$message.success('删除成功！');
                        this.loadWarningConfigsData();
                    }
                }).catch((error) => {
                    this.$message.error('删除失败！');
                    console.log(error);
                });
            },
            loadWarningConfigsData() {
                this.loadingWarningConfigs = true;
                if (this.userId !== null) {
                    axios.get('/api/v1/users/' + this.userId + '/warning-configs').then((response) => {
                        if (response.status === 200 && response.data !== null && response.data.data !== null) {
                            this.warningConfigs = response.data.data;
                            this.warningConfigs.forEach((warningConfig) => {
                                warningConfig.switch = warningConfig.switch === 1 ? true : false;
                                warningConfig.checkedNotificationTypes = [];
                                switch (warningConfig.type) {
                                    case 1:
                                        warningConfig.description = '当日股价涨到';
                                        break;
                                    case 2:
                                        warningConfig.description = '当日股价跌到';
                                        break;
                                    case 3:
                                        warningConfig.description = '当日涨幅超过';
                                        break;
                                    case 4:
                                        warningConfig.description = '当日跌幅超过';
                                        break;
                                }
                            });
                            this.assembleTableSpan();
                            this.loadingWarningConfigs = false;
                        }
                    }).catch((error) => {
                        console.log(error);
                    });
                } else {
                    this.getUserInfo(this.loadWarningConfigsData);
                }
            },
            assembleTableSpan() {
                let warningConfigStockIds = [];
                this.warningConfigsNumberArray = [];
                // 首先获取所有股票ID
                this.warningConfigs.forEach((warningConfig) => {
                    let stockId = warningConfig.stock_id;
                    if (warningConfigStockIds.indexOf(stockId) === -1) {
                        warningConfigStockIds.push(stockId);
                    }
                });

                // 分别获取每只股票预警的数量
                let key = 0;
                warningConfigStockIds.forEach((warningConfigStockId) => {
                    let thisWarningConfigNumber = this.warningConfigs.filter((warningConfig) => {
                        return warningConfig.stock_id === warningConfigStockId
                    }).length;
                    this.warningConfigsNumberArray.push({spanRow: key, spanNum: [thisWarningConfigNumber, 1]});
                    key = this.warningConfigsNumberArray[this.warningConfigsNumberArray.length - 1].spanRow + thisWarningConfigNumber;
                });
            },
            warningConfigSpanMethod(data) {
                let rowIndex = data.rowIndex;
                let columnIndex = data.columnIndex;
                if (this.warningConfigsNumberArray.length !== 0) {
                    if (columnIndex === 0 || columnIndex === 1 || columnIndex === 3) {
                        let warningConfigsNumber = this.warningConfigsNumberArray.find((warningConfigsNumber) => {
                            return warningConfigsNumber.spanRow === rowIndex;
                            });
                        if (warningConfigsNumber != null) {
                            return warningConfigsNumber.spanNum;
                        } else {
                            return [0, 0];
                        }
                    }
                }
            },
            loadNotificationTypesData() {
                axios.get('/api/v1/notification-types').then((response) => {
                    if (response.status === 200 && response.data !== null && response.data.data !== null
                        && response.data.data.length >= 0) {
                        this.notificationTypes = response.data.data;
                    }
                }).catch((error) => {
                    console.log(error);
                    this.$message.error('服务器出错啦');
                });
            },
            handleSwitchChanged(row) {
                this.handleValueChanged(row);
            },
            handleValueChanged(row) {
                let putData = {
                    'id': row.id,
                    'value': row.value,
                    'switch': row.switch
                }
                axios.put('/api/v1/warning-configs', putData).then((response) => {
                    if (response.status === 204) {
                        this.$message.success('修改成功！');
                    }
                }).catch((error) => {
                    console.log(error);
                });
            },
            handleCheckedNotificationTypesChanged(row) {
                if (this.userId !== null) {
                    if (row.notification_types.length === 0) {
                        this.$message.error('请至少选择一种预警方式噢');
                        return false;
                    }
                    let putData = {notification_types: row.notification_types};
                    axios.put('/api/v1/users/' + this.userId + '/stocks/' + row.stock_id + '/notification-types', putData).then((response) => {
                        if (response.status === 204 && response.data !== null) {
                            this.$message.success('修改成功！');
                        }
                    }).catch((error) => {
                        console.log(error);
                        this.$message.error('修改失败！');
                    });
                } else {
                    this.getUserInfo(this.handleCheckedNotificationTypesChanged);
                }
            },
            getUserInfo(nextAction) {
                axios.get('api/v1/users/info').then((response) => {
                    if (response.status === 200 && response.data !== null
                        && response.data.data !== null) {
                        let data = response.data.data;
                        this.userId = data.id;
                        nextAction();
                    }
                }).catch((error) => {
                    console.log(error);
                });
            },
        },
        created: function () {
            this.$nextTick(function () {
                this.loadWarningConfigsData();
                this.loadNotificationTypesData();
            });
        }
    }
</script>