/**
 * Created by diannao on 2018/3/16.
 */
var gallery = mui('.mui-slider');
gallery.slider({
    interval:3000//自动轮播周期，若为0则不自动播放，默认为0；
});

var incomeVue = new Vue({
    el: '#finacial-model',
    data: {
        todayIncome: 0,
        totalIncome: 0,
        todayExpend: 0,
        totalExpend: 0
    },
    watch: {
        number: function (newValue, oldValue) {
            var vm = this

            function animate() {
                if (TWEEN.update()) {
                    requestAnimationFrame(animate)
                }
            }

            new TWEEN.Tween({tweeningNumber: oldValue})
                .easing(TWEEN.Easing.Quadratic.Out)
                .to({tweeningNumber: newValue}, 1000)
                .onUpdate(function () {
                    vm.todayIncome = this.tweeningNumber.toFixed(0)
                })
                .start()

            animate()
        }
    }, methods: {
        updateView(data){
            this.todayIncome = data.today_income;
            this.totalIncome = data.total_income;
            this.todayExpend = data.todayExpend;
            this.totalExpend = data.total_expend;
        }
    }
});
function bodyLoaded() {
    mui.ajax('https://www.miaokemc.com/api/indexjj/today_money', {
        data: {},
        dataType: 'json',//服务器返回json格式数据
        type: 'get',//HTTP请求类型
        timeout: 10000,//超时时间设置为10秒；
//                    headers:{'Content-Type':'application/json'},
        success: function (data) {
            //服务器返回响应，根据响应结果，分析是否登录成功；
            console.log(data)
            if (data.status === 1) {
                incomeVue.updateView(data.data);

            } else {
                if (page === 0) {
                    mui.confirm(data.message, "提示", ["确定"], function () {
                        window.location.reload();
                    })
                } else {
                    mui.toast(data.message);
                }
            }

        },
        error: function (xhr, type, errorThrown) {
            //异常处理；
            console.log(type);
        }
    });
};
document.getElementById("record-wrap").addEventListener('tap', function () {
    mui.openWindow({
        id: 'record',
        url: 'record_list.html'
    });
});
document.getElementById("account-wrap").addEventListener("click", function () {
    mui.openWindow({
        id: 'account',
        url: 'account.html'
    });
});

