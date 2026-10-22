/**
 * @author rlgyzhcn@qq.com
 */
var table = layui.table;
var form = layui.form;
var layer = layui.layer;
form.render();
var tableIns = table.render({
    elem: "#table1",
    url: '/admin/application-category/index',
    limit: 10,
    page: 1,
    loading: true,
    cols: [[
        {
            field: 'name', title: '名称', templet: function (data) {
                var name = JSON.parse(data.name);
                console.log(typeof (data.name), name.zh);
                return name.zh
            }
        },
        {field: 'coin_name',title: '钱包'},
        {field: 'icon_url', title: '图标', templet: '#iconTpl'},
        {field: 'isrecommend', title: '首页推荐', templet: '#recommendTpl', width: 100},
        {field: 'enable', title: '状态', templet: '#enableTpl', width: 100},
        {field: 'id', title: '操作', templet: '#operationTpl'}
    ]],
});
form.on('submit(form1)', function (data) {
    table.reload("table1", {
        where: data.field,
        page: {curr: 1},
    });
    return false;
});
//监听单元格事件
table.on('tool(table1)', function (obj) {
    var data = obj.data;
    if ('del' === obj.event) {
        var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
            $.get('/admin/application-category/delete', {id: data.id}, function (rev) {
                layer.msg(rev.msg);
                if (0 == rev.code) {
                    table.reload('table1', {
                        page: {curr: 1}
                    });
                }
            });
        });
    } else if (obj.event == 'edit') {
        $.get('/admin/application-category/edit', {id: data.id}, function (str) {
            var editIndex = layer.open({
                type: 1,
                title: '编辑: ' + data.name,
                area: '625px',
                content: str,
                btn: ['保存', '取消'],
                btn1: function () {
                    // console.log();
                    $.post('/admin/application-category/edit', $("#application-category-edit").serialize(), function (rev) {
                        layer.msg(rev.msg);
                        if (rev.code == 0) {
                            table.reload("table1", {
                                where: data.field,
                            });
                            layer.close(editIndex);
                        }
                    });
                }
            });
        });
    }
});
form.on('switch(recommendDemo)', function (obj) {
    //layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);return;
    if (obj.elem.checked) {
        $.get('/admin/applicate-recommend/add', {id: this.value, type: 1}, function (rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
            } else {

            }
        });
    } else {
        $.get('/admin/applicate-recommend/delete', {id: this.value, type: 1}, function (rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
            } else {

            }
        });
    }
});

form.on('switch(enableDemo)', function (obj) {
    var enable = 1;
    if (obj.elem.checked) {
        enable = 1
    } else {
        enable = 0;
    }
    $.get('/admin/application-category/set-enable', {id: this.value, enable: enable}, function (rev) {
        layer.msg(rev.msg);
        if (0 == rev.code) {
        } else {

        }
    });
});