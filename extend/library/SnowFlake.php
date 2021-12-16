<?php

namespace library;

class SnowFlake
{
    const EPOCH_OFFSET = 1483200000000;
    const SIGN_BITS = 1;
    const TIMESTAMP_BITS = 41;
    const DATACENTER_BITS = 5;
    const MACHINE_ID_BITS = 5;
    const SEQUENCE_BITS = 12;

    /**
     * @var mixed
     */
    protected $datacenter_id;

    /**
     * @var mixed
     */
    protected $machine_id;

    /**
     * @var null|int
     */
    protected $lastTimestamp = null;

    /**
     * @var int
     */
    protected $sequence = 1;
    protected $signLeftShift = self::TIMESTAMP_BITS + self::DATACENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
    protected $timestampLeftShift = self::DATACENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
    protected $dataCenterLeftShift = self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
    protected $machineLeftShift = self::SEQUENCE_BITS;
    protected $maxSequenceId = -1 ^ (-1 << self::SEQUENCE_BITS);
    protected $maxMachineId = -1 ^ (-1 << self::MACHINE_ID_BITS);
    protected $maxDataCenterId = -1 ^ (-1 << self::DATACENTER_BITS);

    /**
     * Constructor to set required paremeters
     *
     * @param mixed 数据中心的唯一ID(如果使用多个数据中心,需要设置此ID用以区分)
     * @param mixed 机器的唯一ID (如果使用多台机器,需要设置此ID用以区分)
     * @throws \Exception
     */
    public function __construct($dataCenter_id, $machine_id)
    {
        if ($dataCenter_id > $this->maxDataCenterId) {
            throw new \Exception('dataCenter id should between 0 and ' . $this->maxDataCenterId);
        }
        if ($machine_id > $this->maxMachineId) {
            throw new \Exception('machine id should between 0 and ' . $this->maxMachineId);
        }
        $this->datacenter_id = $dataCenter_id;
        $this->machine_id = $machine_id;
    }

    /**
     * 使用雪花算法生成一个唯一ID
     * @return string
     * @throws \Exception
     */
    public function generateID()
    {
        $sign = 0; // default 0
        $timestamp = $this->getUnixTimestamp();
        if ($timestamp < $this->lastTimestamp) {
            throw new \Exception('"Clock moved backwards!');
        }
        if ($timestamp == $this->lastTimestamp) { //与上次时间戳相等，需要生成序列号
            $sequence = ++$this->sequence;
            if ($sequence == $this->maxSequenceId) { //如果序列号超限，则需要重新获取时间
                $timestamp = $this->getUnixTimestamp();
                while ($timestamp <= $this->lastTimestamp) {
                    $timestamp = $this->getUnixTimestamp();
                }
                $this->sequence = 0;
                $sequence = ++$this->sequence;
            }
        } else {
            $this->sequence = 0;
            $sequence = ++$this->sequence;
        }
        $this->lastTimestamp = $timestamp;
        $time = (int)($timestamp - self::EPOCH_OFFSET);
        $id = ($sign << $this->signLeftShift) | ($time << $this->timestampLeftShift) | ($this->datacenter_id << $this->dataCenterLeftShift) | ($this->machine_id << $this->machineLeftShift) | $sequence;
        return (string)$id;
    }

    /**
     * 获取去当前时间戳
     *
     * @return int  毫秒级别的时间戳
     */
    private function getUnixTimestamp()
    {
        return floor(microtime(true) * 1000);
    }
}