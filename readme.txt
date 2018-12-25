状态码
10000 分组必须
10001 添加分组唯一
10002 修改分组唯一
10003 添加图文回复的关键字不能重复
10004 修改图文回复的关键字不能重复

my_sequence 序列表
简介以及使用
DROP TABLE
IF EXISTS my_sequence;

-- 建my_sequence表，指定seq列为无符号大整型，可支持无符号值：0(default)到18446744073709551615（0到2^64–1）。
CREATE TABLE my_sequence (
	NAME VARCHAR (50) NOT NULL,
	current_value BIGINT UNSIGNED NOT NULL DEFAULT 0,
	increment INT NOT NULL DEFAULT 1,
	PRIMARY KEY (NAME) -- 不允许重复seq的存在。
) ENGINE = INNODB;
必须执行下面的SQL语句
执行的时候有可能会报错：ERROR 1418 (HY000): This function has none of DETERMINISTIC, NO SQL, or READS SQL DATA in its declaration and binary logging is enabled (you *might* want to use the less safe log_bin_trust_function_creators variable)
信任子程序的创建者，禁止创建、修改子程序时对SUPER权限的要求
log_bin_trust_routine_creators全局系统变量为1。
1.在客户端上执行SET GLOBAL log_bin_trust_function_creators = 1;
2.MySQL启动时，加上--log-bin-trust-function-creators选贤，参数设置为1
3.在MySQL配置文件my.ini或my.cnf中的[mysqld]段上加log-bin-trust-function-creators=1
/*
	用于获取序列当前值(v_seq_name 参数值 代表序列名称)
*/
DROP FUNCTION
IF EXISTS currval;
create function currval(seq_name VARCHAR(50))
	returns integer
begin
    declare value integer;
    set value = 0;
    select current_value into value from my_sequence where upper(NAME) = upper(seq_name);
   return value;
end;
/*
	用于获取序列下一个值(v_seq_name 参数值 代表序列名称)
*/
DROP FUNCTION
IF EXISTS nextval;
create function nextval(seq_name VARCHAR(50))
    returns integer
begin
    update my_sequence set current_value = current_value + increment  where upper(NAME) = upper(seq_name);
    return currval(seq_name);
end;

/*
	设置某一个序列的值
*/
DROP FUNCTION
IF EXISTS setval;

CREATE FUNCTION setval (
	seq_name VARCHAR (50),VALUE BIGINT
) RETURNS BIGINT
BEGIN
	UPDATE my_sequence
	SET current_value = VALUE
WHERE
	upper(NAME) = upper(seq_name) ; RETURN currval (seq_name) ;
END ;