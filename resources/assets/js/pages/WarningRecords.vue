<template>
    <section style="background-color: #fff; position: relative; overflow: auto; height: 100%; border-radius: 10px; padding: 20px; -webkit-border-radius: 10px; -moz-border-radius: 10px">
        <el-row style="margin: 20px 0;">
            <el-col :span="6" style="margin-right: 5px;">
                <el-input
                v-model="stockQueryString"
                placeholder="请输入股票代码/名称"
                ></el-input>
            </el-col>
            <el-col :span="4" style="margin-right: 5px;">
                <el-button style="margin-left: 5px;" @click="searchWarningRecords" >搜索</el-button>
                <el-button type="primary" style="margin-left: 5px;" @click="resetWarningRecords">重置</el-button>
            </el-col>
        </el-row>
        <el-row style="margin-bottom: 20px;">
            <el-table
            :data="warningRecords"
            border>
                <el-table-column
                type="index">
                </el-table-column>
                <el-table-column
                prop="stock_code"
                label="代码">
                </el-table-column>
                <el-table-column
                prop="stock_name"
                label="名称">
                </el-table-column>
                <el-table-column
                prop="stock_price"
                sortable
                label="价格">
                    <template slot-scope="scope">
                        <span v-if="scope.row.stock_quote_change === '0.00%'">{{ scope.row.stock_price }}</span>
                        <span v-else-if="scope.row.stock_quote_change.substring(0, 1) === '-'" style="color: #67C23A">{{ scope.row.stock_price }}</span>
                        <span v-else style="color: red">{{ scope.row.stock_price }}</span>
                    </template>
                </el-table-column>
                <el-table-column
                prop="stock_quote_change"
                sortable
                label="涨跌幅">
                    <template slot-scope="scope">
                        <span v-if="scope.row.stock_quote_change === '0.00%'">{{ scope.row.stock_quote_change }}</span>
                        <span v-else-if="scope.row.stock_quote_change.substring(0, 1) === '-'" style="color: #67C23A">{{ scope.row.stock_quote_change }}</span>
                        <span v-else style="color: red">{{ scope.row.stock_quote_change }}</span>
                    </template>
                </el-table-column>
                <el-table-column
                prop="notification_types"
                min-width="100px"
                label="预警方式">
                    <template slot-scope="scope">
                        <el-col :span="notification_type === '微信公众号' ? 16 : 6" v-for="notification_type in scope.row.notification_types.trim().split(' ')" v-bind:key="notification_type">
                            <el-tag :type="notification_type === '微信公众号' ? 'success' : 'primary'">{{ notification_type }}</el-tag>
                        </el-col>
                    </template>
                </el-table-column>
                <el-table-column
                prop="warning_setting"
                min-width="100px"
                label="预警设置">
                </el-table-column>
                <el-table-column
                prop="created_at"
                sortable
                min-width="120px"
                label="预警时间">
                </el-table-column>
            </el-table>
        </el-row>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                warningRecords: [],
                rawWarningRecords: [],
                stockQueryString: '',
            }
        },
        methods: {
            loadWarningRecordsData () {
                axios.get('api/v1/warning-records').then((response) => {
                    if (response.status === 200 && response.data != null && response.data.data != null) {
                        this.warningRecords = response.data.data;
                        this.rawWarningRecords = this.warningRecords;
                        this.warningRecords.forEach((warningRecord) => {
                            warningRecord.stock_quote_change = (Number(warningRecord.stock_quote_change) * 100).toFixed(2) + "%";
                        });
                    }
                }).catch((error) => {
                    this.$message.error('服务器出错啦');
                    console.log(error);
                });
            },
            searchWarningRecords() {
                this.warningRecords = this.warningRecords.filter((warningRecord) => {
                    let queryString = this.stockQueryString.trim();
                    if (queryString === '') {
                        return false;
                    }
                    return warningRecord.stock_name === queryString || warningRecord.stock_code === queryString;
                });
            },
            resetWarningRecords() {
                this.warningRecords = this.rawWarningRecords;
            },
        },
        created: function () {
            this.$nextTick(function () {
                this.loadWarningRecordsData();
            });
        }
    }
</script>