<?php
/**
 * Created By Sublime Test 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

use backend\assets\coinPublishRule\ListAsset;

ListAsset::register($this);
?>

<div class="layui-row">
    <div class="layui-col-md6">
        <table class="layui-table">
            <thead>
            <th>币种</th>
            <th>热钱包数量</th>
            <th>当月解冻总量</th>
            <th>热钱包地址</th>
            </thead>
            <tbody>
            <tr>
                <td><?= $coin_name ?></td>
                <td><?= $amount / 1e8 ?></td>
                <td><?= $release_amount / 1e8 ?></td>
                <td><?= $address ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="layui-row">
    <div class="layui-col-md8">
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">手机号码</label>
                <div class="layui-input-inline">
                    <input class="layui-input" name="mobile">
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" id="search">搜索</button>
            </div>
    </div>
</div>
<div class="layui-row">
    <table class="layui-hide" id="table1" lay-filter="table1"></table>
</div>

<script type="text/html" id="toolBarTop">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" lay-event="add">创建锁仓规则</button>
    </div>
</script>
<script type="text/html" id="toolbar">
    <button class="layui-btn layui-btn-xs" lay-event="update">编辑</button>
    <a class="layui-btn layui-btn-xs" href="/admin/coin-release-member/list?rid={{d.id}}">详情</a>
    <button class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</button>
</script>
<script type="text/html" id="form_type">
    {{# if (1 == d.type) { }}
    按锁仓总量释放
    {{# } else if (2 == d.type) { }}
    按锁仓余量释放
    {{# } }}
</script>
<script type="text/html" id="form_release_time">
    {{# if (d.repeat == 0) { }}
    {{d.release_time}}
    {{# } else if (d.repeat == 1) { }}
    每年 {{d.release_time}}
    {{# } else if (d.repeat == 2) { }}
    每月 {{d.release_time}}
    {{# } else if (d.repeat == 3) { }}
    每天 {{d.release_time}}
    {{# } }}
</script>