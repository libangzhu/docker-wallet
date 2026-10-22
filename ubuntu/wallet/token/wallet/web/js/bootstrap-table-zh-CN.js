/**
 * 自定义刷新提示
 */
var whenShowLoading=null;
var showMyLoading=function () {
    if (typeof(layer) === 'object' && whenShowLoading === null) {
        whenShowLoading = layer.load(2, {shade: false});
    }
    return true;
};
var hideMyLoading=function () {
    if (typeof(layer) === 'object' && whenShowLoading !== null) {
        layer.close(whenShowLoading);
        whenShowLoading = null;
    }
    return true;
};

/**
 * Bootstrap Table Chinese translation
 */
(function ($) {
    'use strict';

    $.fn.bootstrapTable.locales['zh-CN'] = {
        contentType: "application/x-www-form-urlencoded",
        striped: true,  //设置为 true 会有隔行变色效果
        cache: false,   //设置为 false 禁用 AJAX 数据缓存
        silent: true,   //静默刷新方式 refresh {silent: true}
        dataType: "json",   //服务器返回的数据类型
        queryParamsType:'', //queryParams {pageSize, pageNumber, searchText, sortName, sortOrder}
        undefinedText: '',  //当数据为 undefined 时显示的字符
        sidePagination: "server",   //服务器分页
        pagination: true,   //设置为 true 会在表格底部显示分页条
        paginationLoop: false,  //设置为 true 启用分页条无限循环的功能
        /*fixedColumns: true,
        fixedNumber: 2,*/
        paginationPreText: '上一页',
        paginationNextText: '下一页',
        ajaxOptions: function () {
            if (typeof(request_token) === 'string') {
                return {
                    headers: {"Authorization": 'Bearer ' + request_token}
                };
            } else {
                return {};
            }
        },
        formatLoadingMessage: function () {
            return '';
        },
        formatRecordsPerPage: function (pageNumber) {
            return '';//'每页显示 ' + pageNumber + ' 条记录';
        },
        formatShowingRows: function (pageFrom, pageTo, totalRows) {
            return '<span style="font-size: 11px">第 <b>'+pageFrom+'</b>-<b>'+pageTo+'</b> 条，共 <b>'+totalRows+'</b> 条数据. </span>';
            //return '显示第 ' + pageFrom + ' 到第 ' + pageTo + ' 条记录，总共 ' + totalRows + ' 条记录';
        },
        formatSearch: function () {
            return '搜索';
        },
        formatNoMatches: function () {
            return '<span style="color: #9e9e9e">没有找到匹配的记录</span>';
        },
        formatPaginationSwitch: function () {
            return '隐藏/显示分页';
        },
        formatRefresh: function () {
            return '刷新';
        },
        formatToggle: function () {
            return '切换';
        },
        formatColumns: function () {
            return '列';
        },
        formatExport: function () {
            return '导出';
        },
        formatClearFilters: function () {
            return '清空过滤';
        },
        onLoadSuccess: function () {
            return hideMyLoading();
        },
        onLoadError: function () {
            return hideMyLoading();
        },
        onRefresh: function () {
            return showMyLoading();
        },
        onPageChange: function () {
            return showMyLoading();
        }
    };

    $.extend($.fn.bootstrapTable.defaults, $.fn.bootstrapTable.locales['zh-CN']);

})(jQuery);