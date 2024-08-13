<?php namespace delayed_events;

use delayed_events\events\AbstractEvent;
use delayed_events\events\MockEvent;
use Throwable;

class DelayedEvents
{
    private static $instance;

    public $dbHost = "127.0.0.1";
    public $dbName = "test";
    public $dbUser = "test";
    public $dbPassword = "*";
    public $eventsPath;

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new DelayedEvents();
        }

        return self::$instance;
    }

    public function getCurDate()
    {
        return date('Y-m-d H:i:s', time() - (int)date('Z'));
    }

    public function getDBH()
    {
        $DBH = new \PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPassword);
        $DBH->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $DBH;
    }

    /**
     * получить список событий
     *
     * @param int $limit
     *
     * @return array
     */
    public function getEventListForProcessing($limit = 100)
    {
        $sql = "
            SELECT *
            FROM delayed_events
            WHERE status_id = 0
              AND d_execute <= :CURRENT_DATE
            LIMIT :LIMIT
        ";

        $STH = $this->getDBH()->prepare($sql);
        $STH->bindValue(':CURRENT_DATE', $this->getCurDate(), \PDO::PARAM_STR);
        $STH->bindValue(':LIMIT', $limit, \PDO::PARAM_INT);
        $STH->setFetchMode(\PDO::FETCH_OBJ);
        $STH->execute();

        return $STH->fetchAll();
    }

    /**
     * обработать список
     *
     * @param array $eventList
     */
    public function processEventList(&$eventList)
    {
        foreach ($eventList as $eventData) {
            $event = $this->getEventObject($eventData->name, $eventData);
            $this->processEvent($event);
        }
    }

    /**
     * @param AbstractEvent $event
     *
     * @return bool
     */
    public function processEvent(&$event)
    {
        $result = false;

        try {
            $event->setMicroTimeStart();

            if ($event->checkConditions()) {
                $event->beforeExecute();
                if ($event->execute()) {
                    $event->setNewStatus($event::STATUS_IS_COMPLETED);
                    $result = true;
                } else {
                    $event->setNewStatus($event::STATUS_NOT_EXECUTED);
                }
            } else {
                $event->setNewStatus($event::STATUS_IS_SKIPPED);
            }
        } catch (Throwable $e) {
            $event->setNewStatus($event::STATUS_HAS_ERROR);
            $event->addLog(['code' => $e->getCode(), 'message' => $e->getMessage(), 'trace' => traceToJson($e->getTrace())]);
        }

        $event->save();

        return $result;
    }

    /**
     * добавление события на обработку
     *
     * @param string $name
     * @param array $data
     *
     * @return bool|AbstractEvent
     */
    public function addEvent($name, $data = [])
    {
        $result = false;

        /**
         * @var AbstractEvent $event
         */
        $event = $this->getEventObject($name, $data);

        if ($event !== false) {
            $event->save();
            $result = $event;
        }

        return $result;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getClassName($name)
    {
        $name = ucfirst($name);

        return "events\\{$name}Event";
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getClassPath($name)
    {

        $name = ucfirst($name);

        if (is_null($this->eventsPath)) {
            $path = __DIR__ . DS . "events" . DS . "{$name}Event.php";
        } else {
            $path = $this->eventsPath . "{$name}Event.php";
        }

        return $path;
    }

    /**
     * @param string $name
     * @param bool|array $data
     *
     * @return bool|AbstractEvent
     */
    public function getEventObject($name, $data = false)
    {
        $classPath = $this->getClassPath($name);
        $className = $this->getClassName($name);

        if (file_exists($classPath)) {
            /**
             * @var AbstractEvent $event
             */
            $event = new $className();
        } else {
            $event = new MockEvent();
        }

        if (false !== $data) {
            $event->setFieldsValue($data);
        }

        return $event;
    }
}
