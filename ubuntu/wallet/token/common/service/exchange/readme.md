- 交易所服务层
从redis数据库读取行情数据

使用crontab定时更新数据库行情

1.支持交易对的保存方法：使用redis的set保存交易对
2.交易对行情的保存方法：使用redis的hashmap保存行情
    - 键名：$prefix_$tag_$aim(eg: $prefix_byc_usd)
    - 行情信息：
        [
           low=>'',//最低价
           high=>'',//最高价
           'last'=>'',//当前价格
        ]