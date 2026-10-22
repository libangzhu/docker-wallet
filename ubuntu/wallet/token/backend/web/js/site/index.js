/**
 * 首页
 * libingke
 * 2018/01/09
 */

$(function () {
    $('#table1-1').bootstrapTable({
        columns: [
            { field: 'f0', title: '类别' },
            { field: 'f1', title: '总用户数' },
            { field: 'f1', title: '推荐用户' },
            { field: 'f1', title: '实名用户' },
            { field: 'f1', title: '邮箱认证' },
            { field: 'f1', title: '手机认证' },
            { field: 'f1', title: 'PC端' },
            { field: 'f1', title: '移动端' }
        ],
        data: [
            { f0: '总数', f1: '-'},
            { f0: '今日增加', f1: '-'},
            { f0: '较昨日', f1: '-'}
        ]
    });

    $('#table1-2').bootstrapTable({
        columns: [
            { field: 'f0', title: '类别' },
            { field: 'f1', title: '法币成交量' },
            { field: 'f1', title: '币币成交量' },
            { field: 'f1', title: '法币交易次数' },
            { field: 'f1', title: '币币交易次数' },
            { field: 'f1', title: '法币交易手续费' },
            { field: 'f1', title: '币币交易手续费' }
        ],
        data: [
            { f0: '总数', f1: '-'},
            { f0: '今日增加', f1: '-'},
            { f0: '较昨日', f1: '-'}
        ]
    });

    $('#table2-1').bootstrapTable({
        columns: [
            { field: 'f0', title: '类别' },
            { field: 'f1', title: 'USDT充值' },
            { field: 'f1', title: 'BTC充币' },
            { field: 'f1', title: 'BCC充币' },
            { field: 'f1', title: 'BTH充币' },
            { field: 'f1', title: 'ETC充币' },
            { field: 'f1', title: 'ZEC充币' },
            { field: 'f1', title: 'LTC充币' },
            { field: 'f1', title: 'BTS充币' },
            { field: 'f1', title: 'SC充币' }
        ],
        data: [
            { f0: '总数', f1: '-'},
            { f0: '今日充币', f1: '-'},
            { f0: '总笔数', f1: '-'},
            { f0: '待复核', f1: '-'},
            { f0: '较昨日', f1: '-'}
        ]
    });

    $('#table2-2').bootstrapTable({
        columns: [
            { field: 'f0', title: '类别' },
            { field: 'f1', title: 'USDT提款' },
            { field: 'f1', title: 'BTC提币' },
            { field: 'f1', title: 'BCC提币' },
            { field: 'f1', title: 'BTH提币' },
            { field: 'f1', title: 'ETC提币' },
            { field: 'f1', title: 'ZEC提币' },
            { field: 'f1', title: 'LTC提币' },
            { field: 'f1', title: 'BTS提币' },
            { field: 'f1', title: 'SC提币' }
        ],
        data: [
            { f0: '总数', f1: '-'},
            { f0: '今日提币', f1: '-'},
            { f0: '总笔数', f1: '-'},
            { f0: '待处理', f1: '-'},
            { f0: '较昨日', f1: '-'}
        ]
    });

    $('#table3-1').bootstrapTable({
        columns: [
            { field: 'f0', title: '类别' },
            { field: 'f1', title: '推客人数' },
            { field: 'f1', title: '推客推广人数' },
            { field: 'f1', title: '注册用户' },
            { field: 'f1', title: '实名认证' },
            { field: 'f1', title: '手机认证' },
            { field: 'f1', title: '邮箱认证' },
            { field: 'f1', title: 'PC端' },
            { field: 'f1', title: '移动端' }
        ],
        data: [
            { f0: '总数', f1: '-'},
            { f0: '今日增加', f1: '-'},
            { f0: '较昨日', f1: '-'}
        ]
    });

    $('#table3-2').bootstrapTable({
        columns: [
            { field: 'f0', title: '类别' },
            { field: 'f1', title: '红包总金额' },
            { field: 'f1', title: '发红包数' },
            { field: 'f1', title: '发红包用户数' },
            { field: 'f1', title: '红包注册' },
            { field: 'f1', title: '实名认证' },
            { field: 'f1', title: '手机认证' },
            { field: 'f1', title: '邮箱认证' }
        ],
        data: [
            { f0: '总数', f1: '-'},
            { f0: '今日增加', f1: '-'},
            { f0: '较昨日', f1: '-'}
        ]
    });

    $('#table4-1').bootstrapTable({
        columns: [
            { field: 'f0', title: '排名' },
            { field: 'f1', title: '总注册用户' },
            { field: 'f1', title: '邮箱注册' },
            { field: 'f1', title: '手机注册' },
            { field: 'f1', title: '人脸识别' },
            { field: 'f1', title: '成交额USDT' },
            { field: 'f1', title: '成交额BTC' },
            { field: 'f1', title: 'BTY持有量' }
        ],
        data: [
            { f0: 'NO.1', f1: '-'},
            { f0: 'NO.2', f1: '-'},
            { f0: 'NO.3', f1: '-'},
            { f0: '...', f1: '-'}
        ]
    });

    $('#table4-2').bootstrapTable({
        columns: [
            { field: 'f0', title: '排名' },
            { field: 'f1', title: '充值' },
            { field: 'f1', title: '提款' },
            { field: 'f1', title: '法币交易' },
            { field: 'f1', title: '币币交易' },
            { field: 'f1', title: '充币' },
            { field: 'f1', title: '提币' }
        ],
        data: [
            { f0: 'NO.1', f1: '-'},
            { f0: 'NO.2', f1: '-'},
            { f0: 'NO.3', f1: '-'},
            { f0: '...', f1: '-'}
        ]
    });

    var myChart1 = echarts.init(document.getElementById('c1'));
    var myChart2 = echarts.init(document.getElementById('c2'));
    var myChart3 = echarts.init(document.getElementById('c3'));

    var option1 = {
        title: {
            text: '--',
            textStyle:{
                color:'#ba322b',
                fontStyle:'normal',
                fontWeight:'bold',
                //字体大小
                fontSize:18
            },
            x:'center'
        },
        color: ['#3398DB'],
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        grid: {
            left: '1%',
            right: '2%',
            bottom: '2%',
            containLabel: true
        },
        xAxis : [
            {
                type : 'category',
                data : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                axisTick: {
                    alignWithLabel: true
                }
            }
        ],
        yAxis : [
            {
                type : 'value'
            }
        ],
        series : [
            {
                name:'量',
                type:'bar',
                barWidth: '10%',
                data:[10, 52, 200, 334, 390, 330, 220]
            }
        ]
    };

    option1.title.text = '交易频率分布表（法币）';
    myChart1.setOption(option1);
    option1.title.text = '交易频率分布表（币币）';
    myChart2.setOption(option1);
    option1.title.text = '人气分布趋势图（PC\\手机）';
    myChart3.setOption(option1);
});

