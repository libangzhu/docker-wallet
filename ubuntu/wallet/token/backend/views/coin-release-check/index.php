<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-05 18:40:23
 * @authors rlgy <rlgyzhcn@qq.com>
 */

use backend\assets\coinReleaseCheck\IndexAsset;

IndexAsset::register($this);
?>
<style type="text/css">
    .layui-form-label {
        width: 60px;
        padding: 0 0 0 0;
        line-height: 40px;
        text-align: center;
    }

    .layui-inline {
        margin-bottom: 0;
        margin-right: 10px;
    }
</style>

<div class="layui-row">
    <h4>审核</h4>
    <div class="layui-row">
        <form class="layui-form" lay-filter="form_check">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="coin">
                            <option value="">全部</option>
                            <?php if (is_array($coins)): ?>
                                <?php foreach ($coins as $key => $value): ?>
                                    <option value="<?= strtoupper($value); ?>"><?= strtoupper($value); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">开始时间</label>
                    <div class="layui-input-inline">
                        <input type="text" name="start_time" class="layui-input" placeholder="选择时间范围" id="date_check_start">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">结束时间</label>
                    <div class="layui-input-inline">
                        <input type="text" name="end_time" class="layui-input" placeholder="选择时间范围" id="date_check_end">
                    </div>
                </div>
                <div class="layui-inline">
                    <div class="layui-input-inline" style="margin-left: 5px;">
                        <input type="text" name="mobile" class="layui-input" placeholder="手机" autocomplete="off">
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-filter="submit_check" lay-submit>搜索</button>
                    <button class="layui-btn layui-btn-sm layui-btn-primary" type="reset">重置筛选</button>
                </div>
            </div>
        </form>
    </div>
    <div class="layui-row">
        <table class="layui-table" id="table_check" lay-filter="table_check"></table>
    </div>
</div>

<div class="layui-row">
    <h4>查询</h4>
    <div class="layui-row">
        <form class="layui-form" lay-filter="form_search">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="status">
                            <option value="">全部</option>
                            <option value="1">未完成</option>
                            <option value="2">被撤提</option>
                            <option value="3">失败</option>
                            <option value="4">完成</option>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="coin">
                            <option value="">全部</option>
                            <?php if (is_array($coins)): ?>
                                <?php foreach ($coins as $key => $value): ?>
                                    <option value="<?= strtoupper($value); ?>"><?= strtoupper($value); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">初审员</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="check_first_uid">
                            <option value="">全部</option>
                            <?php if (!empty($check_firsts)): ?>
                                <?php foreach ($check_firsts as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $value ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">复审员</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="check_second_uid">
                            <option value="">全部</option>
                            <?php if (!empty($check_seconds)): ?>
                                <?php foreach ($check_seconds as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $value ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <br>
                <div class="layui-inline">
                    <label class="layui-form-label">开始时间</label>
                    <div class="layui-input-inline">
                        <input type="text" name="start_time" class="layui-input" placeholder="选择时间范围" id="date_search_start">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">结束时间</label>
                    <div class="layui-input-inline">
                        <input type="text" name="end_time" class="layui-input" placeholder="选择时间范围" id="date_search_end">
                    </div>
                </div>
                <div class="layui-inline">
                    <div class="layui-input-inline" style="margin-left: 5px;">
                        <input type="text" name="mobile" class="layui-input" placeholder="手机" autocomplete="off">
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit lay-filter="submit_search">搜索</button>
                    <button class="layui-btn layui-btn-sm layui-btn-primary" type="reset">重置筛选</button>
                    <button class="layui-btn layui-btn-sm layui-btn-danger">导出</button>
                </div>
            </div>
        </form>
    </div>
    <div class="layui-row">
        <table class="layui-table" id="table_search" lay-filter="table_search"></table>
    </div>
</div>

<script type="text/html" id="toolbar_check">

    <!-- 复核通过无法撤体 -->

    {{# if(d.status!=0){ }}
    <!-- 撤提之后无法进行任何操作 -->
    <button class="layui-btn layui-btn-xs layui-btn-disabled">初审</button>
    <button class="layui-btn layui-btn-xs layui-btn-disabled">复核</button>
    <button class="layui-btn layui-btn-xs layui-btn-disabled">撤提</button>
    {{# }else{ }}
    <!-- 复核之后无法在初审 -->
    {{# if(1==d.check_second_status){ }}
    {{# if(1==d.check_first_status){ }}
    <button class="layui-btn layui-btn-xs" lay-event="check_first">初审</button>
    {{# }else if(2==d.check_first_status && 2!=d.check_seconf_status){ }}
    <button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="check_first">初审(已通过)</button>
    {{# }else if(3==d.check_first_status){ }}
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="check_first">初审(不通过)</button>
    {{# } }}
    {{# }else{ }}
    <button class="layui-btn layui-btn-xs layui-btn-disabled">初审</button>
    {{# } }}
    <!-- 初审通过才可复核 -->
    {{# if(2==d.check_first_status){ }}
    {{# if(1==d.check_second_status){ }}
    <button class="layui-btn layui-btn-xs" lay-event="check_second">复核</button>
    {{# }else if(2==d.check_second_status){ }}
    <button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="check_second">复核(已通过)</button>
    {{# }else if(3==d.check_second_status){ }}
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="check_second">复核(不通过)</button>
    {{# } }}
    {{# }else{ }}
    <button class="layui-btn layui-btn-xs layui-btn-disabled">复核</button>
    {{# } }}
    {{# } }}
    {{# if(2==d.check_second_status){ }}
    <button class="layui-btn layui-btn-xs layui-btn-disabled">撤提</button>
    {{# }else{ }}
    <button class="layui-btn layui-btn-xs layui-btn" lay-event="extract_cancel">撤提</button>
    {{# } }}
</script>

<script type="text/html" id="search_status">
    {{# if(1==d.status){ }}
    未完成
    {{# }else if(2==d.status){ }}
    被撤提
    {{# }else if(3==d.status){ }}
    失败
    {{# }else if(4==d.status){ }}
    完成
    {{# }else if(5==d.status){ }}
    审核中
    {{# }else if(6==d.status){ }}
    审核失败
    {{# }else if(7==d.status){ }}
    审核异常
    {{# } }}
</script>