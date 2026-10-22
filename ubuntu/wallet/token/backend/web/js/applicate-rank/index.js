var table1 = layui.table;
var table2 = layui.table;
var table3 = layui.table;
var form = layui.form;

var layer_add;
table1.render({
    elem: '#table1',
    where: {type: 1},
    url: '/admin/applicate-rank/index',
    cols: [[
        {field: 'id', title: '应用id'},
        {field: 'name', title: '应用名称'},
        {field: 'sort', title: '排序',edit: 'text'},
        {title: '操作', templet: '#operTpl'},
    ]]
});
table2.render({
    elem: '#table2',
    where: {type: 2},
    url: '/admin/applicate-rank/index',
    cols: [[
        {field: 'id', title: '应用id'},
        {field: 'name', title: '应用名称'},
        {field: 'sort', title: '排序',edit: 'text'},
        {title: '操作', templet: '#operTpl'},
    ]]
});

table3.render({
    elem: '#table3',
    url: '/admin/applicate-rank/search-index',
    cols: [[
        {field: 'name', title: '热门搜索名称'},
        {field: 'sort', title: '排序',edit: 'text'},
        {title: '操作', templet: '#operSearchTpl'},
    ]]
});
form.render();

layui.table.on('tool(table)', function (obj) {
    var data = obj.data;
    var event = obj.event;
    if (event == 'delete') {
        var ldelete = layer.confirm('确定要删除?', {
            icon: 3,
            title: '删除'
        }, function (index) {
            $.get('/admin/applicate-rank/delete', {id: data.id,type:data.type}, function (rev) {
                layer.msg(rev.msg);
                if (rev.code == 0) {
                    obj.del();
                    layer.close(ldelete);
                }
            });
        });
    }else if(event == 'delete_search'){
        var ldelete = layer.confirm('确定要删除?', {
            icon: 3,
            title: '删除'
        }, function (index) {
            $.get('/admin/applicate-rank/search-delete', {name: data.name}, function (rev) {
                layer.msg(rev.msg);
                if (rev.code == 0) {
                    obj.del();
                    layer.close(ldelete);
                }
            });
        });
    }
});
//添加图层
$("#add1").click(function () {
    layer_add = layer.open({
        type: 1,
        title: '版本信息',
        content: $('.add'), //这里content是一个普通的String
        scrollbar: false,
        area: ['700px','300px'],
        cancel: function (index, layero) {
            after_close_add(false);
        },
        success: function (index, layero) {
            $('input[name="type"]').val(1);
        }
    });
});

$("#add2").click(function () {
    layer_add = layer.open({
        type: 1,
        title: '版本信息',
        content: $('.add'), //这里content是一个普通的String
        scrollbar: false,
        area: ['700px','300px'],
        cancel: function (index, layero) {
            after_close_add(false);
        },
        success: function (index, layero) {
            $('input[name="type"]').val(2);
        }
    });
});

$("#add3").click(function () {
    layer_add = layer.open({
        type: 1,
        title: '版本信息',
        content: $('.add_search'), //这里content是一个普通的String
        scrollbar: false,
        area: ['700px','200px'],
        cancel: function (index, layero) {
            $('.add_search').attr('style', 'display:none;padding: 5px;');
            $('input[name="name"]').val('');
            form.render();
        },
    });
});
form.on('submit(formDemo)', function (data) {
    $.post('/admin/applicate-rank/add', data.field, function (rev) {
        layer.msg(rev.msg);
        if (rev.code == 0) {
            layer.close(layer_add);
            after_close_add();
        }
    });
    return false;
});

form.on('submit(formSearch)', function (data) {
    $.post('/admin/applicate-rank/add-search', data.field, function (rev) {
        layer.msg(rev.msg);
        if (rev.code == 0) {
            layer.close(layer_add);
            $('.add_search').attr('style', 'display:none;padding: 5px;');
            $('input[name="name"]').val('');
            table3.reload('table3', {});
            form.render();
        }
    });
    return false;
});

function after_close_add(reflush = true) {
    if (reflush) {
        table1.reload('table1', {});
        table2.reload('table2', {});
    }
    $('.add').attr('style', 'display:none;padding: 5px;');
    $('input[name="id"]').val('');
    $('select[name="applicate_id"]').val(0);
    form.render();
}

//监听单元格编辑
layui.table.on('edit(table)', function(obj){
    var value = obj.value; //得到修改后的值
    var data = obj.data;    //得到所在行所有键值
    if(data.type){
        var params =  {id:data.id,type:data.type,sort:value};
        var url = '/admin/applicate-rank/set-sort';
    }else{
        var params = {name:data.name,sort:value};
        var url = '/admin/applicate-rank/set-search-sort';
    }
    $.get(url, params, function(rev) {
        layer.msg(rev.msg);
        if (0 == rev.code) {
            if(data.type){
                layui.table.reload('table'+ data.type,{});
            }else{
                layui.table.reload('table3',{});
            }
        }else{

        }
    });
});


