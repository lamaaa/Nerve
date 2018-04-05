<template>
    <section style="background-color: #fff; position: relative; overflow: auto; height: 100%; border-radius: 10px; padding: 10px; -webkit-border-radius: 10px; -moz-border-radius: 10px">
        <el-row style="margin: 20px 20px;">
            <el-col :span="8">
                <el-autocomplete
                v-model="stock"
                :fetch-suggestions="querySearchAsync"
                placeholder="请输入股票代码/名称"
                @select="handleSelect">
                    <template slot-scope="props">
                        <span style="margin-right: 20px;">{{ props.item.code }}</span>
                        <span>{{ props.item.name }}</span>
                    </template>
                </el-autocomplete>
                <el-button @click="handleAddStock">添加</el-button>
            </el-col>
            <el-col :offset="5" :span="6" style="margin-right: 5px;">
                <el-input
                v-model="stockQueryString"
                placeholder="请输入股票代码/名称"
                ></el-input>
            </el-col>
            <el-col :span="4" style="margin-right: 5px;">
                <el-button style="margin-left: 5px;" @click="searchStockQuote">搜索</el-button>
                <el-button type="primary" style="margin-left: 5px;" @click="resetStockQuotes">重置</el-button>
            </el-col>
        </el-row>
        <el-row style="margin-bottom: 20px;">
            <el-table
            :data="stockQuotes"
            stripe
            v-loading="loadingStockQuotes"
            >
                <el-table-column
                prop="code"
                label="代码">
                </el-table-column>
                <el-table-column
                prop="name"
                min-width="100"
                label="股票名称">
                </el-table-column>
                <el-table-column
                prop="current_price"
                sortable
                label="最新">
                </el-table-column>
                <el-table-column
                prop="quote_change"
                sortable
                min-width="100"
                label="涨跌幅">
                </el-table-column>
                <el-table-column
                prop="today_opening"
                sortable
                label="今开">
                </el-table-column>
                <el-table-column
                prop="yesterday_closing"
                sortable
                label="昨收">
                </el-table-column>
                <el-table-column
                prop="today_highest_price"
                sortable
                label="最高">
                </el-table-column>
                <el-table-column
                prop="today_lowest_price"
                sortable
                label="最低">
                </el-table-column>
                <el-table-column
                prop="total_volume"
                sortable
                min-width="120"
                label="成交数量">
                </el-table-column>
                <el-table-column
                prop="total_account"
                sortable
                min-width="160"
                label="成交金额">
                </el-table-column>
                <el-table-column
                prop="datetime"
                sortable
                min-width="140"
                label="时间">
                </el-table-column>
                <el-table-column
                fixed="right"
                label="操作"
                width="150">
                    <template slot-scope="scope">
                        <el-button type="text" @click="handleAddWarningConfig(scope.row)">添加预警</el-button>
                        <el-button type="text" @click="confirmDeleteStock(scope.row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-row>
        <el-dialog
        title="添加预警"
        :visible.sync="addWarningConfigDialogVisible"
        width="500px"
        center>
            <el-form
            :model="warningConfigForm"
            :rules="rules"
            ref="warningConfigForm"
            label-width="120px">
                <el-row style="margin-bottom: 30px;">
                    <el-col :offset="1" :span="8">
                        <h3 style="display: inline;">{{ warningConfigForm.stockName }}</h3>
                    </el-col>
                    <el-col :offset="2" :span="6">
                        <label>最新价</label>
                        <span>{{ warningConfigForm.currentPrice }}</span>
                    </el-col>
                    <el-col :offset="1" :span="6">
                        <label>涨跌幅</label>
                        <span>{{ warningConfigForm.quoteChange }}</span>
                    </el-col>
                </el-row>
                <el-form-item label="当日股价涨到" prop="riseValue">
                    <el-col :span="15">
                        <el-input
                        v-model="warningConfigForm.riseValue">
                            <template slot="append">元</template>
                        </el-input>
                    </el-col>
                    <el-col :offset="2" :span="7">
                        <el-switch
                        style="margin-top: 8px"
                        v-model="warningConfigForm.riseValueSwitch">
                        </el-switch>
                    </el-col>
                </el-form-item>
                <el-form-item label="当日股价跌到" prop="fallValue">
                    <el-col :span="15">
                        <el-input
                        v-model="warningConfigForm.fallValue">
                            <template slot="append">元</template>
                        </el-input>
                    </el-col>
                    <el-col :offset="2" :span="7">
                        <el-switch
                        style="margin-top: 8px"
                        v-model="warningConfigForm.fallValueSwitch">
                        </el-switch>
                    </el-col>
                </el-form-item>
                <el-form-item label="当日涨幅超过" prop="riseRate">
                    <el-col :span="15">
                        <el-input
                        v-model="warningConfigForm.riseRate">
                            <template slot="append">%</template>
                        </el-input>
                    </el-col>
                    <el-col :offset="2" :span="7">
                        <el-switch
                        style="margin-top: 8px"
                        v-model="warningConfigForm.riseRateSwitch">
                        </el-switch>
                    </el-col>
                </el-form-item>
                <el-form-item label="当日跌幅超过" prop="fallRate">
                    <el-col :span="15">
                        <el-input
                        v-model="warningConfigForm.fallRate">
                            <template slot="append">%</template>
                        </el-input>
                    </el-col>
                    <el-col :offset="2" :span="7">
                        <el-switch
                        style="margin-top: 8px"
                        v-model="warningConfigForm.fallRateSwitch">
                        </el-switch>
                    </el-col>
                </el-form-item>
                <el-form-item label="预警方式" prop="checkedNotificationTypes">
                    <el-checkbox-group
                    v-model="warningConfigForm.checkedNotificationTypes"
                    style="margin-top: 11px;">
                        <el-checkbox v-for="notificationType in notificationTypes" :label="notificationType['name']" :key="notificationType['name']">{{ notificationType['description'] }}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="addWarningConfigDialogVisible = false">取消</el-button>
                <el-button type="primary" @click="addWarningConfig()">确定</el-button>
            </span>
        </el-dialog>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                stocks: [],
                stockQueryString: '',
                stock: '',
                timeout: null,
                stockQuotes: [],
                rawStockQuotes: [],
                userId: null,
                loadingStockQuotes: true,
                intervalId: '',
                addWarningConfigDialogVisible: false,
                notificationTypes: [],
                warningConfigs: [],
                rules: {
                    checkedNotificationTypes: [
                        {required: true, message: '请至少选择一种预警方式', trigger: 'change'}
                    ]
                },
                warningConfigForm: {
                    stockId: '',
                    stockName: '',
                    currentPrice: '',
                    quoteChange: '',
                    riseValue: '',
                    fallValue: '',
                    riseRate: '',
                    fallRate: '',
                    riseValueSwitch: false,
                    fallValueSwitch: false,
                    riseRateSwitch: false,
                    fallRateSwitch: false,
                    checkedNotificationTypes: [],
                }
            }
        },
        methods: {
            resetStockQuotes() {
                this.stockQuotes = this.rawStockQuotes;
            },
            searchStockQuote() {
                let queryString = this.stockQueryString.trim();
                if (queryString === '') {
                    return false;
                }
                this.stockQuotes = this.rawStockQuotes.filter((stockQuote) => {
                    return stockQuote.code === queryString || stockQuote.name === queryString;
                });
            },
            addWarningConfig() {
                this.$refs['warningConfigForm'].validate((valid) => {
                    if (!valid) {
                        return false;
                    }
                });
                if (this.userId !== null) {
                    axios.post('/api/v1/users/' + this.userId + '/warning-configs/', this.warningConfigForm).then((response) => {
                        if (response.status === 204 && response.data !== null) {
                            let typeArray = [
                                {'name': 'riseValue', 'value': 1},
                                {'name': 'fallValue', 'value': 2},
                                {'name': 'riseRate', 'value': 3},
                                {'name': 'fallRate', 'value': 4},
                            ];
                            typeArray.forEach((type) => {
                                let warningConfig = this.warningConfigs.find((warningConfig) => {
                                    return warningConfig.type === type.value && warningConfig.stock_id === this.warningConfigForm.stockId;
                                });
                                if (warningConfig != null) {
                                    warningConfig.value = this.warningConfigForm[type.name];
                                    warningConfig.switch = this.warningConfigForm[type.name + 'Switch'] === true ? 1 : 0;
                                } else {
                                    this.warningConfigs.push({
                                        value: this.warningConfigForm[type.name],
                                        switch: this.warningConfigForm[type.name + 'Switch'] === true ? 1 : 0,
                                        status: 1,
                                        stock_id: this.warningConfigForm.stockId,
                                        stock_name: this.warningConfigForm.stockName,
                                        type: type.value
                                    });
                                }
                            });
                            this.addWarningConfigDialogVisible = false;
                            let changedNotificationTypes = [];
                            let stockQuote = this.stockQuotes.find((stockQuote) => {
                                return stockQuote.id === this.warningConfigForm.stockId;
                            });
                            this.warningConfigForm.checkedNotificationTypes.forEach((checkNotificationType) => {
                                switch (checkNotificationType) {
                                    case 'email':
                                        changedNotificationTypes.push({name: 'email', description: '邮箱'});
                                        break;
                                    case 'sms':
                                        changedNotificationTypes.push({name: 'sms', description: '短信'});
                                        break;
                                }
                            });
                            stockQuote.notificationTypes = changedNotificationTypes;
                            this.$message.success('添加成功!');
                        }
                    }).catch((error) => {
                        console.log(error);
                        this.$message.error('添加失败');
                    });
                } else {
                    this.getUserInfo(this.addWarningConfig);
                }
            },
            loadWarningConfigsData() {
                if (this.userId !== null) {
                    axios.get('/api/v1/users/' + this.userId + '/warning-configs').then((response) => {
                        if (response.status === 200 && response.data !== null && response.data.data !== null) {
                            this.warningConfigs = response.data.data;
                        }
                    }).catch((error) => {
                        console.log(error);
                    });
                } else {
                    this.getUserInfo(this.loadWarningConfigsData);
                }
            },
            setWarningConfig(row) {
                let typeArray = [
                    {'name': 'riseValue', 'value': 1},
                    {'name': 'fallValue', 'value': 2},
                    {'name': 'riseRate', 'value': 3},
                    {'name': 'fallRate', 'value': 4},
                ];
                typeArray.forEach((type) => {
                    let warningConfig = this.warningConfigs.find((warningConfig) => {
                        return warningConfig.type === type.value && warningConfig.stock_id === row.id;
                    });
                    if (warningConfig != null) {
                        this.warningConfigForm[type.name] = warningConfig.value;
                        this.warningConfigForm[type.name + 'Switch'] = warningConfig.switch === 1 ? true : false;
                        this.warningConfigForm.checkedNotificationTypes = [];
                        row.notificationTypes.forEach((notificationType) => {
                            this.warningConfigForm.checkedNotificationTypes.push(notificationType.name);
                        });
                    } else {
                        this.warningConfigForm[type.name] = '';
                        this.warningConfigForm[type.name + 'Switch'] = false;
                    }
                });
            },
            handleAddWarningConfig(row) {
                this.warningConfigForm.stockName = row.name;
                this.warningConfigForm.currentPrice = row.current_price;
                this.warningConfigForm.quoteChange = row.quote_change;
                this.warningConfigForm.stockId = row.id;

                this.setWarningConfig(row);
                this.addWarningConfigDialogVisible = true;
            },
            confirmDeleteStock(row) {
                this.$confirm('确定删除股票 ' + row.name + ' 吗？', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.deleteStock(row.id);
                }).catch(() => {

                });
            },
            deleteStock(id) {
                if (this.userId !== null) {
                    axios.delete('/api/v1/users/' + this.userId + '/stocks/' + id).then((response) => {
                        if (response.status === 204 && response.data !== null) {
                            this.$message.success('删除成功！');
                            this.stockQuotes = this.stockQuotes.filter((stockQuote) => {
                                return stockQuote.id !== id;
                            });
                            this.rawStockQuotes = this.stockQuotes;
                        }
                    }).catch((error) => {
                        console.log(error);
                        this.$message.error('删除失败！');
                    });
                } else {
                    this.getUserInfo(this.deleteStock);
                }
            },
            handleAddStock() {
                let stocks = this.stocks;
                let results = stocks.filter(this.createStockFilter(this.stock));
                if (results !== null && results.length === 1) {
                    let stock = results[0];
                    this.addStock(stock.code);
                } else {
                    return;
                }
            },
            querySearchAsync(queryString, cb) {
                let stocks = this.stocks;
                let results = [];
                if (queryString !== null && queryString.trim() !== "") {
                    results = queryString ? stocks.filter(this.createStockFilter(queryString)) : stocks;
                }

                cb(results);
            },
            createStockFilter(queryString) {
                return (stock) => {
                    return (stock.code.toLowerCase().indexOf(queryString.toLowerCase()) !== -1
                    || stock.name.toLowerCase().indexOf(queryString.toLowerCase()) !== -1);
                };
            },
            handleSelect(item) {
                this.addStock(this.stock);
                this.stock = '';
                console.log(item);
            },
            addStock(code) {
                if (this.userId !== null) {
                    let postData = {'code': code};
                    axios.post('/api/v1/users/' + this.userId + '/stocks', postData).then((response) => {
                        if (response.status === 204 && response.data !== null) {
                            this.$message.success('添加成功!');
                            this.loadStockQuotesData();
                        }
                    }).catch((error) => {
                        if (error !== null && error.response !== null && error.response.status === 409) {
                            this.$message.error('添加失败，重复添加！');
                        } else {
                            this.$message.error('添加失败！');
                        }
                        console.log(error);
                    });
                } else {
                    this.getUserInfo(this.addStock);
                }
            },
            loadStockData() {
                this.loadingStockQuotes = true;
                axios.get('/api/v1/stocks').then((response) => {
                    if (response.status === 200 && response.data != null
                    && response.data.data != null && response.data.data.length != null) {
                        this.stocks = response.data.data;
                        this.stocks.forEach((stock) => {
                            stock['value'] = stock['code'];
                        });
                    }
                }).catch((error) => {
                    console.log(error);
                    this.$message.error('服务器出错啦');
                });
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
            loadStockQuotesData() {
                this.loadingStockQuotes = true;
                if (this.userId !== null) {
                    axios.get('/api/v1/users/' + this.userId + '/stocks-quotes').then((response) => {
                        if (response.status === 200 && response.data != null
                        && response.data.data != null && response.data.data.length != null) {
                            this.stockQuotes = response.data.data;
                            this.stockQuotes.forEach((stockQuote) => {
                                stockQuote['quote_change'] = String((Number(stockQuote['quote_change']) * 100).toFixed(2)) + '%';
                                stockQuote['current_price'] = Number(stockQuote['current_price']).toFixed(2);
                                stockQuote['today_opening'] = Number(stockQuote['today_opening']).toFixed(2);
                                stockQuote['yesterday_closing'] = Number(stockQuote['yesterday_closing']).toFixed(2);
                                stockQuote['today_highest_price'] = Number(stockQuote['today_highest_price']).toFixed(2);
                                stockQuote['today_lowest_price'] = Number(stockQuote['today_lowest_price']).toFixed(2);
                                stockQuote['total_account'] = Number(stockQuote['total_account']).toFixed(2);
                            });
                            this.rawStockQuotes = this.stockQuotes;
                            this.loadingStockQuotes = false;
                        }
                        }).catch((error) => {
                            console.log(error);
                    });
                } else {
                    this.getUserInfo(this.loadStockQuotesData);
                }
            }
        },
        created: function () {
            this.$nextTick(function () {
                this.loadStockData();
                this.loadStockQuotesData();
                this.loadNotificationTypesData();
                this.loadWarningConfigsData();
            });
            this.intervalId = setInterval(() => {
                this.loadStockQuotesData();
            }, 1000 * 60);
        },
        beforeDestroy () {
            clearInterval(this.intervalId);
        }
    };
</script>