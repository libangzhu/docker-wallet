var table1 = layui.table;
var table2 = layui.table;
var form = layui.form;
var upload = layui.upload;
var type = [
    {code: 0, name: ''},
    {code: 1, name: '安卓稳定版'},
    {code: 2, name: '安卓公测版'},
    {code: 3, name: '安卓内侧版'},
    {code: 4, name: 'IOS稳定版'},
    {code: 5, name: 'IOS公测版'},
    {code: 6, name: 'IOS内侧版'}
];
var vstatus = [
    {code: 0, name: ''},
    {code: 1, name: '上线'},
    {code: 2, name: '推荐更新'},
    {code: 3, name: '下线'},
    {code: 4, name: '强制更新'},
];
var layer_add;
table1.render({
    elem: '#table1',
    page: 1,
    limit: 10,
    where: {type: [1, 2, 3],platform_id: $("select[name='platform_id']").val()},
    url: '/admin/app/list',
    cols: [[
        {field: 'id', title: '编号'},
        {field: 'version', title: '版本号'},
        {field: 'type', title: '类型', templet: '<div>{{type[d.type].name}}</div>'},
        {field: 'status', title: '状态', templet: '<div>{{vstatus[d.status].name}}</div>'},
        {
            field: 'download_url',
            title: '下载链接',
            width: 900,
            templet: '<div><a href="{{d.download_url}}">{{d.download_url}}</a> </div>'
        },
        {field: 'updated_at', title: '更新时间'},
        {title: '操作', templet: '#operTpl'},
    ]]
});
table2.render({
    elem: '#table2',
    page: 1,
    limit: 10,
    where: {type: [4, 5, 6],platform_id: $("select[name='platform_id']").val()},
    url: '/admin/app/list',
    cols: [[
        {field: 'id', title: '编号'},
        {field: 'version', title: '版本号'},
        {field: 'type', title: '类型', templet: '<div>{{type[d.type].name}}</div>'},
        {field: 'status', title: '状态', templet: '<div>{{vstatus[d.status].name}}</div>'},
        {
            field: 'download_url',
            title: '下载链接',
            width: 900,
            templet: '<div><a href="{{d.download_url}}">{{d.download_url}}</a> </div>'
        },
        {field: 'updated_at', title: '更新时间'},
        {title: '操作', templet: '#operTpl'},
    ]]
});
form.render();
var index_upload = 0;
upload.render({
    elem: '#upload1',
    url: '/admin/oss/upload',
    accept: 'file',
    field: 'file',
    data: {_csrf: $('input[name="_csrf"]').val(), image_type: 'app'},
    before: function (obj) {
       index_upload = layer.load(1);
    },
    done: function (res) {
        layer.close(index_upload);
        layer.msg(res.msg);
        if (0 == res.code) {
            $('input[name="download_url"]').val(res.data.src);
        }
    }
});
layui.table.on('tool(table)', function (obj) {
    var data = obj.data;
    var event = obj.event;
    if (event == 'delete') {
        var ldelete = layer.confirm('确定要删除' + type[data.type].name + data.version + '?', {
            icon: 3,
            title: '删除'
        }, function (index) {
            $.get('/admin/app/delete', {id: data.id}, function (rev) {
                layer.msg(rev.msg);
                if (rev.code == 0) {
                    obj.del();
                    layer.close(ldelete);
                }
            });
        });
    } else if (event == 'detail') {
        before_show(data);
        //禁用提交按钮
        $("form button").css('display', 'none');
        layer_add = layer.open({
            type: 1,
            title: '版本信息',
            content: $('.add'), //这里content是一个普通的String
            scrollbar: false,
            area: '700px',
            cancel: function (index, layero) {
                after_close_add(false);
                $("form button").css('display', '');
            }
        });
    } else if (event == 'edit') {
        before_show(data);

        layer_add = layer.open({
            type: 1,
            title: '版本信息',
            content: $('.add'), //这里content是一个普通的String
            scrollbar: false,
            area: '700px',
            cancel: function (index, layero) {
                after_close_add(false);
            }
        });
    }
});
//添加图层
$(".btn_add").click(function () {
    layer_add = layer.open({
        type: 1,
        title: '版本信息',
        content: $('.add'), //这里content是一个普通的String
        scrollbar: false,
        area: '700px',
        cancel: function (index, layero) {
            after_close_add(false);
        }
    });
});
form.on('submit(formDemo)', function (data) {
    var platform_obj = {platform_id: $("select[name='platform_id']").val()};
    data.field = Object.assign(data.field,platform_obj);
    $.post('/admin/app/add', data.field, function (rev) {
        layer.msg(rev.msg);
        if (rev.code == 0) {
            layer.close(layer_add);
            after_close_add();
        }
    });
    return false;
});

function after_close_add(reflush = true) {
    if (reflush) {
        table1.reload('table1', {});
        table2.reload('table2', {});
    }
    $('.add').attr('style', 'display:none');
    $('input[name="id"]').val('');
    $('select[name="type"]').val(0);
    $('select[name="status"]').val(0);
    $('input[name="version"]').val('');
    $('input[name="version_code"]').val('');
    $('input[name="download_url"]').val('');
    $('textarea[name="log"]').val('');
    form.render();
}

function before_show(data) {
    $('input[name="id"]').val(data.id);
    $('select[name="type"]').val(data.type);
    $('select[name="status"]').val(data.status);
    $('input[name="version"]').val(data.version);
    $('input[name="version_code"]').val(data.version_code);
    $('input[name="download_url"]').val(data.download_url);
    if (data.log) {
        $('textarea[name="log"]').val(JSON.parse(data.log).join("\n"));
    }
    form.render();
}

form.on('select(platform_change)', function(data){
    table1.reload('table1', {where:{platform_id:data.value}});
    table2.reload('table2', {where:{platform_id:data.value}});
});