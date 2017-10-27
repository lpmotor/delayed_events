<?php namespace delayed_events\events;

use delayed_events\DelayedEvents;

abstract class AbstractEvent
{
    const STATUS_NEW = 0;
    const STATUS_IN_PROGRESS = 1; // todo для жирных задач
    const STATUS_IS_COMPLETED = 2;
    const STATUS_IS_SKIPPED = 3;
    const STATUS_NOT_EXECUTED = 4;
    const STATUS_HAS_ERROR = 5;

    protected $id;
    protected $parent_id;
    protected $name;
    protected $delay = 0;
    protected $data = [];
    protected $status_id;
    protected $d_created;
    protected $d_status_change;
    protected $micro_time_start;
    protected $log = [];

    /**
     * AbstractEvent constructor.
     */
    public final function __construct() {
        $this->initFields();
    }

    public final function initFields() {
        $className = explode('\\', get_class($this));
        $this->name = preg_replace(('/event$/i'), '', array_pop($className), -1);
        $this->setNewStatus(self::STATUS_NEW);
        $this->d_created = $this->d_status_change;
    }

    public final function setFieldsValue($data) {
        foreach ($data as $fieldName => $fieldValue) {
            if (property_exists($this, $fieldName)) {
                if (in_array($fieldName, ['name'])) {
                    continue;
                }

                if (in_array($fieldName, ['data', 'log'])) {
                    if (!is_array($fieldValue) && !is_object($fieldValue)) {
                        $fieldValue = json_decode($fieldValue, true);
                        $fieldValue = is_array($fieldValue) ? $fieldValue : [];
                    }
                }

                $this->$fieldName = $fieldValue;
            }
        }
    }

    public final function getId() {
        return $this->id;
    }

    public final function setId($id) {
        $this->id = $id;
    }

    public final function getParentId() {
        return $this->parent_id;
    }

    public final function setParentId($parentId) {
        $this->parent_id = $parentId;
    }

    public final function getName() {
        return $this->name;
    }

    public final function getDelay() {
        return $this->delay;
    }

    public final function getData() {
        return is_null($this->data) ? [] : $this->data;
    }

    public final function getStatusId() {
        return $this->status_id;
    }

    public final function getDCreated() {
        return $this->d_created;
    }

    public final function getDStatusChange() {
        return $this->d_status_change;
    }

    public final function getLog() {
        return $this->log;
    }

    public final function addLog($log) {
        $this->log[] = $log;
    }

    public final function getDExecute($ops) {
        $customDelay = 0;
        if (isset($ops['delay'])) {
            $customDelay = (integer) $ops['delay'];
        }
        return date('Y-m-d H:i:s', strtotime($this->getDCreated()) + $this->getDelay() + $customDelay);
    }

    public final function getMicroTimeStart() {
        return $this->micro_time_start;
    }


    public final function setMicroTimeStart() {
        $this->micro_time_start = microtime(true);
    }

    public final function getDuration() {
        return is_null($this->micro_time_start) ? 0 : round((microtime(true) - $this->micro_time_start), 2);
    }

    public final function setNewStatus($statusId) {
        $this->d_status_change = DelayedEvents::getInstance()->getCurDate();
        $this->status_id = $statusId;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkConditions() {
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function beforeExecute() {
        return true;
    }

    /**
     * @return bool
     */
    abstract public function execute();

    /**
     * @return bool
     * @throws \Exception
     */
    public final function save($ops = []) {
        if (is_null($this->getId())) {
            $sql = "
                INSERT INTO delayed_events (parent_id, name, d_created, d_status_change, d_execute, duration, status_id, data, log)
                VALUES(:PARENT_ID, :NAME, :D_CREATED, :D_STATUS_CHANGE, :D_EXECUTE, :DURATION, :STATUS_ID, :DATA, :LOG)
            ";

            $STH = DelayedEvents::getInstance()->getDBH()->prepare($sql);
            $STH->bindValue(':PARENT_ID', $this->getParentId(), \PDO::PARAM_INT);
            $STH->bindValue(':NAME', $this->getName(), \PDO::PARAM_STR);
            $STH->bindValue(':D_CREATED', $this->getDCreated());
            $STH->bindValue(':D_STATUS_CHANGE', $this->getDStatusChange());
            $STH->bindValue(':D_EXECUTE', $this->getDExecute($ops));
            $STH->bindValue(':DURATION', $this->getDuration());
            $STH->bindValue(':STATUS_ID', $this->getStatusId(), \PDO::PARAM_INT);
            $STH->bindValue(':DATA', json_encode($this->getData()), \PDO::PARAM_STR);
            $STH->bindValue(':LOG', json_encode($this->getLog()), \PDO::PARAM_STR);
            $result = $STH->execute();

            $this->id = DelayedEvents::getInstance()->getDBH()->lastInsertId();
        } else {
            $sql = "
                UPDATE delayed_events
                SET parent_id = :PARENT_ID, d_status_change = :D_STATUS_CHANGE, d_execute = :D_EXECUTE, duration = :DURATION, status_id = :STATUS_ID, data = :DATA, log = :LOG
                WHERE id = :ID
            ";

            $STH = DelayedEvents::getInstance()->getDBH()->prepare($sql);
            $STH->bindValue(':PARENT_ID', $this->getParentId(), \PDO::PARAM_INT);
            $STH->bindValue(':D_STATUS_CHANGE', $this->getDStatusChange());
            $STH->bindValue(':D_EXECUTE', $this->getDExecute($ops));
            $STH->bindValue(':DURATION', $this->getDuration());
            $STH->bindValue(':STATUS_ID', $this->getStatusId(), \PDO::PARAM_INT);
            $STH->bindValue(':DATA', json_encode($this->getData()), \PDO::PARAM_STR);
            $STH->bindValue(':LOG', json_encode($this->getLog()), \PDO::PARAM_STR);
            $STH->bindValue(':ID', $this->getId());
            $result = $STH->execute();
        }

        return $result;
    }
}