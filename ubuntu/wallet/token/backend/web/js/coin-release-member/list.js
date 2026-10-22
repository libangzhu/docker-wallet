var table = layui.table;
var upload = layui.upload;

table.render({
    elem: "#table1",
    url: '/admin/coin-release-member/list',
    page: 1,
    limit: 10,
    cols: [[
        {field: 'id', title: '用户id'},
        {field: 'mobile', title: '手机号'},
        {field: 'coin', title: '币种'},
        {
            field: 'amount', title: '总量', templet: function (d) {
                return d.amount / 1e8;
            }
        },
        {
            field: 'release', title: '活动数量', templet: function (d) {
                return d.release / 1e8;
            }
        },
        {
            field: 'freeze', title: '已冻结', templet: function (d) {
                return d.freeze / 1e8;
            }
        },
    ]]
});

upload.render({
    elem: "#implode",
    url: "/admin/coin-release-member/implode",
    accept: 'file',
    exts: 'csv',
    done: function (res) {
        layer.closeAll('loading');
        var data = res.data;
        var tr_data = '';
        for(var i=0;i<data.length;i++){
           tr_data += '<tr><td>'+data[i][1]+'</td><td>'+data[i][2]+'</td><td>'+data[i][3]+'</td></tr>';
        }
        if(res.code == 0){
            var count_html = '<p style="color:#0000ff">共导入<b style="color:red">'+res.count+'</b>条记录:'+'</p>';
            var html = count_html + '<table class="layui-table">' +
                            '<colgroup>\n' +
                                '<col width="150">' +
                                '<col width="150">' +
                                '<col width="200">' +
                            '</colgroup>' +
                            '<thead>' +
                                '<tr>' +
                                    '<th>手机号</th>' +
                                    '<th>币种</th>' +
                                    '<th>数量</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' +
                                      tr_data +
                            '</tbody>' +
                       '</table>';
            layer.open({
                title: '导入数据预览',
                content: html,
                area: ['1000px', '800px'],
                scrollbar:true,
                btn: ['确认导入', '取消']
                ,btn1: function(index, layero){
                    //按钮【按钮一】的回调
                    $.get('/admin/coin-release-member/data-update', {}, function (rev) {
                        layer.msg(rev.msg);
                        if (0 == rev.code) {
                            layer.close(index);
                            table.reload('table1', {
                                page: {
                                    curr: 1
                                }
                            });
                        }
                    });
                }
                ,btn2: function(index, layero){
                    //按钮【按钮二】的回调
                    //return false 开启该代码可禁止点击该按钮关闭
                }
                ,cancel: function(){

                }
            });
        }else{
            layer.msg(res.msg);
        }


    },
    before: function (obj) { //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
        layer.load(); //上传loading
    }
});

$("#add").click(function () {
    $.get('/admin/coin-release-member/set', {}, function (rev) {
        var index = layer.open({
            type: 1,
            title: "添加人员",
            id: 'add_one',
            skin: 'layui-skin-lan',
            content: rev,
            btn: ['保存', '取消'],
            btn1: function () {
                $.post('/admin/coin-release-member/set', $("#form_data").serialize(), function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        layer.close(index);
                        table.reload('table1', {
                            page: {
                                curr: 1
                            }
                        });
                    }
                });
            }
        });
    });
});