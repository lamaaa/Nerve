<template>
    <section id="shSzStocksSection">
        <el-row>
            <el-table
            :data="shSzStocks"
            @sort-change="sort"
            border
            stripe>
            <el-table-column
                prop="code"
                label="代码">
                </el-table-column>
                <el-table-column
                prop="name"
                label="名称">
                </el-table-column>
                <el-table-column
                prop="current_price"
                sortable="custom"
                label="最新价">
                </el-table-column>
                <el-table-column
                prop="quote_change"
                sortable="custom"
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
            </el-table>
        </el-row>

        <el-row>
            <el-pagination
            layout="prev, pager, next"
            :total="total"
            :page-size="pageSize"
            background
            @current-change="handleCurrentChange"
            :current-page="currentPage">
            </el-pagination>
        </el-row>
    </section>
</template>

<style>
    #shSzStocksSection {
        background-color: #fff;
        height: 100%;
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;
        padding: 10px;
    }

    .el-row {
        margin-bottom: 20px;
    }
</style>

<script>
    export default {
        data() {
            return {
                shSzStocks: [],
                total: 0,
                pageSize: 0,
                currentPage: 0,
                intervalId: '',
                order: 'desc',
                criteria: 'current_price'
            }
        },
        created: function () {
            this.$nextTick(function () {
                this.handleCurrentChange();
            });
            this.intervalId = setInterval(() => {
                this.handleCurrentChange(this.currentPage);
            }, 1000 * 60);
        },
        methods: {
            handleCurrentChange (page = 1) {
                axios.get('api/stock?page=' + page + '&order=' + this.order + '&criteria=' + this.criteria)
                    .then((response) => {
                        if (response.status == 200 && response.data.data != null && response.data.data.length != 0
                        && response.data.data.stocks != null && response.data.data.stocks.length != 0) {
                            let data = response.data.data;
                            this.shSzStocks = data.stocks;
                            this.total = Number(data.total);
                            this.pageSize = Number(data.per_page);
                            this.currentPage = Number(data.current_page);
                            this.shSzStocks.forEach((stock) => {
                                stock['quote_change'] = String((Number(stock['quote_change']) * 100).toFixed(2)) + '%';
                                stock['current_price'] = Number(stock['current_price']).toFixed(2);
                                stock['today_opening'] = Number(stock['today_opening']).toFixed(2);
                                stock['yesterday_closing'] = Number(stock['yesterday_closing']).toFixed(2);
                                stock['today_highest_price'] = Number(stock['today_highest_price']).toFixed(2);
                                stock['today_lowest_price'] = Number(stock['today_lowest_price']).toFixed(2);
                                stock['total_account'] = Number(stock['total_account']).toFixed(2);
                            });
                        }
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                },
            sort(params) {
                this.order = params.order === 'ascending' ? 'asc' : 'desc';
                this.criteria = params.prop;
                this.handleCurrentChange(this.currentPage);
            }
        },
        beforeDestroy () {
            clearInterval(this.intervalId);
        },
    }
</script>