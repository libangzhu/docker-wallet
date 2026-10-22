<?php

use backend\assets\site\IndexAsset;

$jsAsset = $this->getAssetManager()->getBundle(\backend\assets\AppAsset::className())->baseUrl;
$this->registerJsFile($jsAsset.'/global/plugins/echarts/echarts-all.js');
IndexAsset::register($this);
?>
<div style="max-width: 1440px">
    <h3><b>一、基本数据：</b></h3>

    <div>
        <div style="float: left">
            <h4>(一) <b>用户数</b></h4>
        </div>
        <!--<div style="float: right">
            <button class="btn btn-default" type="button" name="refresh" title="刷新" onclick="">
                <i class="glyphicon glyphicon-refresh"></i>
            </button>
        </div>-->
    </div>
    <table id="table1-1" class="table table-striped table-bordered"></table>

    <div>
        <div style="float: left">
            <h4>(二) <b>交易量</b></h4>
        </div>
    </div>
    <table id="table1-2" class="table table-striped table-bordered"></table>

    <h3><b>二、财务数据：</b></h3>

    <div>
        <div style="float: left">
            <h4>(一) <b>充币</b></h4>
        </div>
    </div>
    <table id="table2-1" class="table table-striped table-bordered"></table>

    <div>
        <div style="float: left">
            <h4>(二) <b>提币</b></h4>
        </div>
    </div>
    <table id="table2-2" class="table table-striped table-bordered"></table>

    <h3><b>三、推广数据：</b></h3>

    <div>
        <div style="float: left">
            <h4>(一) <b>推客</b></h4>
        </div>
    </div>
    <table id="table3-1" class="table table-striped table-bordered"></table>

    <div>
        <div style="float: left">
            <h4>(二) <b>红包</b></h4>
        </div>
    </div>
    <table id="table3-2" class="table table-striped table-bordered"></table>

    <h3><b>四、运营数据：</b></h3>
    <br>
    <div class="layui-row">
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
            <div id="c1" style="width: 420px; height: 350px;margin: 0 auto"></div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
            <div id="c2" style="width: 420px; height: 350px;margin: 0 auto"></div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
            <div id="c3" style="width: 420px; height: 350px;margin: 0 auto"></div>
        </div>
    </div>

    <div>
        <div style="float: left">
            <h4>(一) <b>基本情况</b></h4>
        </div>
    </div>
    <table id="table4-1" class="table table-striped table-bordered"></table>

    <div>
        <div style="float: left">
            <h4>(二) <b>交易相关</b></h4>
        </div>
    </div>
    <table id="table4-2" class="table table-striped table-bordered"></table>

    <!--<div class="layui-row">
        <div class="layui-col-md9">
            9/12
        </div>
        <div class="layui-col-md3">
            3/12
        </div>
    </div>

    不同表现：
    <div class="layui-row">
        <div class="layui-col-xs6 layui-col-sm6 layui-col-md4">
            移动：6/12 | 平板：6/12 | 桌面：4/12
        </div>
        <div class="layui-col-xs6 layui-col-sm6 layui-col-md4">
            移动：6/12 | 平板：6/12 | 桌面：4/12
        </div>
        <div class="layui-col-xs4 layui-col-sm12 layui-col-md4">
            移动：4/12 | 平板：12/12 | 桌面：4/12
        </div>
        <div class="layui-col-xs4 layui-col-sm7 layui-col-md8">
            移动：4/12 | 平板：7/12 | 桌面：8/12
        </div>
        <div class="layui-col-xs4 layui-col-sm5 layui-col-md4">
            移动：4/12 | 平板：5/12 | 桌面：4/12
        </div>
    </div>-->
</div>