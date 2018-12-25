<?php
/**
 * Created by PhpStorm.
 * User: dudj
 * Date: 2017/11/14
 * Time: 11:13
 * twitter的雪花算法 保持数字串唯一性
 * 使用了微秒+机器码
    $work = new IdWork(5,5);
    $work->nextId();
 */
/**
 * SnowFlake ID生成器
 *基于Twitter Snowflake生成跨越多个唯一的ID
 *数据中心和数据库没有重复。
 *
 *
 *雪花布局
 *
 * 1符号位 -  0为正，1为负
 * 41位 - 自纪元以来的毫秒数
 * 5位 - 数据中心ID
 * 5位 - 机器ID
 * 12位 - 序列号
 *
 *总共64位整数/字符串
 */
namespace Think;
class SnowFlake
{
    /**
     *从Unix纪元偏移
     * Unix时代：1970年1月1日00:00:00
     *时代抵消：2000年1月1日00:00:00
     */
    CONST EPOCH_OFFSET = 1480166465631;
    /**
     * @var mixed
     */
    private $datacenter_id;

    /**
     * @var mixed
     */
    private $machine_id;

    /**
     * @var null|int
     */
    private $lastTime = null;

    /**
     * @var int
     */
    private $sequence  = 1;
    /**
     * Constructor to set required paremeters
     *
     * @param mixed $datacenter_id    Unique ID for datacenter (if multiple locations are used)
     * @param mixed $machine_id       Unique ID for machine (if multiple machines are used)
     */
    public function __construct($datacenter_id, $machine_id)
    {
        $this->datacenter_id = $datacenter_id;
        $this->machine_id = $machine_id;
    }
    /**
     * Generate an unique ID based on SnowFlake
     *
     * @return string  Unique ID
     */
    public function generateID() {
        $sign = 1; // default 0
        $time = (int)($this->getUnixTimestamp() - self::EPOCH_OFFSET);
        $sequence = $this->getNextSequence($time);
        $this->lastTime = $time;
        $id = ($sign << 1) | ($time << 41) | ($this->datacenter_id << 5) | ($this->machine_id << 5) | ($sequence << 12) ;
        return (string)$id;
    }
    /**
     * Get UNIX timestamp in microseconds
     *
     * @return int  Timestamp in microseconds
     */
    private function getUnixTimestamp()
    {
        return floor(microtime(true) * 1000);
    }
    /**
     * Get the next sequence number if $time
     * was already used
     *
     * @param  int $time    (micro)timestamp from EPOCH_OFFSET
     * @return int          Sequence number
     */
    private function getNextSequence($time)
    {
        if($time === $this->lastTime) {
            return ++$this->sequence;
        }
        return $this->sequence;
    }
}