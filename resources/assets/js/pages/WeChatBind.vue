<template>
    <section style="background-color: #fff; position: relative; overflow: auto; height: 100%; border-radius: 10px; padding: 10px; -webkit-border-radius: 10px; -moz-border-radius: 10px">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%)">
            <img :src="qrCodeUrl" alt="" class="image">
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                qrCodeUrl: '',
                intervalId: '',
            }
        },
        methods: {
            getUserQrCodeUrl() {
                axios.get('api/v1/users/wechat-qrcode-url').then((response) => {
                    if (response.status === 200 && response.data != null && response.data.data != null) {
                        let timestamp = Date.parse(new Date());
                        this.qrCodeUrl = response.data.data + '?' + timestamp;
                    }
                }).catch((error) => {
                    console.log(error);
                });
            }
        },
        created: function () {
            this.$nextTick(function () {
                this.getUserQrCodeUrl();
            });
            this.intervalId = setInterval(() => {
                this.getUserQrCodeUrl();
            }, 1000 * 150);
        },
        beforeDestroy () {
            clearInterval(this.intervalId);
        }
    }
</script>