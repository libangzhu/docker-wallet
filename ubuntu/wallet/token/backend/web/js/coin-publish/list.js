var table = layui.table;

table.render({
    elem: "#table1",
    url: '/admin/coin-publish/list',
    limit: 10,
    page: 1,
    loading: true,
    cols: [[
        {field: 'id', title: 'ID'},
        {field: 'company_name', title: '公司名称'},
        {field: 'coin_name_zh', title: '币种中文名'},
        {field: 'coin_name_en', title: '币种英文名'},
        {field: 'address', title: '热钱包地址'},
        {
            field: 'amount', title: '热钱包数量', templet: function (d) {
                return d.amount / 1e8;
            }
        },
        {field: 'auto_check_limit', title: '自动审核上限值', edit:  'text'},
        {
            title: '自动审核',
            field: 'auto_check',
            templet: '#autoCheckTpl',
            width: 100
        },
        {toolbar: '#toolbar', title: '操作'}
    ]],
});
table.on('tool(table1)', function (obj) {
    var event = obj.event;
    var data = obj.data;
    if ('update' == event) {
        $.get('/admin/coin-publish/add', {id: data.id}, function (str) {
            var index = layer.open({
                type: 1,
                title: '编辑',
                id: 'add-one',
                skin: 'layui-layer-lan',
                area: ['434px', '330px'],
                content: str,
                btn: ['确认', '取消'],
                btn1: function () {
                    var company_name = $('#addData input[name="company_name"]').val();
                    var coin_name_zh = $('#addData input[name="coin_name_zh"]').val();
                    var coin_name_en = $('#addData input[name="coin_name_en"]').val();
                    if (company_name !== "" && company_name !== "" && coin_name_en !== "") {
                        $.post('/admin/coin-publish/add', $("#addData").serialize(), function (rev) {
                            layer.msg(rev.msg);
                            if (rev.code == 0) {
                                layer.close(index);
                                table.reload("table1", {});
                            }
                        });
                    } else {
                        layer.msg("请输入完整！");
                    }
                    return false;
                }
            });
            layui.form.render();
        });
    } else if ('del' == event) {
        layer.confirm('确定删除此币种监控吗?', {title: '删除币种'}, function (index) {
            $.get('/admin/coin-publish/del', {id: data.id}, function (rev) {
                if (rev.code == 0) {
                    obj.del();
                    layer.close(index);
                }else{
                    layer.msg(rev.msg);
                }
            });
        });
    }
});
$('#add').click(function () {
    //打开弹窗
    $.get('/admin/coin-publish/add', {}, function (str) {
        var index = layer.open({
            type: 1,
            title: '添加币种监控',
            id: 'add-one',
            skin: 'layui-layer-lan',
            area: ['434px', '330px'],
            content: str,
            btn: ['确认', '取消'],
            btn1: function () {
                var company_name = $('#addData input[name="company_name"]').val();
                var coin_name_zh = $('#addData input[name="coin_name_zh"]').val();
                var coin_name_en = $('#addData input[name="coin_name_en"]').val();
                if (company_name !== "" && company_name !== "" && coin_name_en !== "") {
                    $.post('/admin/coin-publish/add', $("#addData").serialize(), function (rev) {
                        layer.msg(rev.msg);
                        if (rev.code == 0) {
                            layer.close(index);
                            table.reload("table1", {});
                        }
                    });
                } else {
                    layer.msg("请输入完整！");
                }
                return false;
            }
        });
        layui.form.render();
    });

    return false;
});
var form = layui.form;
form.render();
form.on('switch(autoCheckDemo)', function(obj){
    var enable = 1;
    if(obj.elem.checked){
        enable = 1
    }else{
        enable = 0;
    }
    $.get('/admin/coin-publish/set-auto-check', {id:this.value,enable:enable}, function(rev) {
        layer.msg(rev.msg);
        if (0 == rev.code) {
        }else{

        }
    });
});

//监听单元格编辑
table.on('edit(table1)', function(obj){
    var value = obj.value; //得到修改后的值
    var data = obj.data;    //得到所在行所有键值
    $.get('/admin/coin-publish/set-limit', {id:data.id,auto_check_limit:value}, function(rev) {
        layer.msg(rev.msg);
        if (0 == rev.code) {
            table.reload('table1',{});
        }else{

        }
    });
});