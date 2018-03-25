<template>
    <section style="background-color: #fff; position: relative; overflow: auto; height: 100%; border-radius: 10px; padding: 10px; -webkit-border-radius: 10px; -moz-border-radius: 10px">
        <el-row style="margin: 20px 20px;">
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
                label="最新价">
                </el-table-column>
                <el-table-column
                prop="quote_change"
                label="涨跌幅">
                </el-table-column>
                <el-table-column
                prop="today_opening"
                label="今开">
                </el-table-column>
                <el-table-column
                prop="yesterday_closing"
                label="昨收">
                </el-table-column>
                <el-table-column
                prop="today_highest_price"
                label="最高价">
                </el-table-column>
                <el-table-column
                prop="today_lowest_price"
                label="最低价">
                </el-table-column>
                <el-table-column
                prop="total_volume"
                min-width="120"
                label="成交数量">
                </el-table-column>
                <el-table-column
                prop="total_account"
                min-width="160"
                label="成交金额">
                </el-table-column>
                <el-table-column
                prop="datetime"
                min-width="140"
                label="时间">
                </el-table-column>
                <el-table-column
                fixed="right"
                label="操作"
                width="150">
                    <template slot-scope="scope">
                        <el-button type="text">添加预警</el-button>
                        <el-button type="text" @click="confirmDeleteStock(scope.row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-row>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                stocks: [],
                stock: '',
                timeout: null,
                stockQuotes: [],
                userId: null,
                loadingStockQuotes: true,
                intervalId: '',
            }
        },
        methods: {
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
                            this.loadStockQuotesData();
                        }
                    }).catch((error) => {
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
                if (queryString != null && queryString.trim() !== "") {
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