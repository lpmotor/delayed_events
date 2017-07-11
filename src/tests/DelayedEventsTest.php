<?php

use delayed_events\DelayedEvents;

class DelayedEventsTest extends PHPUnit_Framework_TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }

    public function testGetInstance() {
        $delayedEvents = DelayedEvents::getInstance();

        $this->assertEquals(get_class($delayedEvents), 'delayed_events\DelayedEvents');
    }

    public function testGetDBH() {
        /*
        $DBH = DelayedEvents::getInstance()->getDBH();

        $this->assertEquals(get_class($DBH), '\PDO');
        */
    }

    public function testGetEventListForProcessing() {
    }

    public function testProcessEventList() {
    }

    public function testProcessEvent() {
        /*
        $event = new \delayed_events\events\TestEvent();

        DelayedEvents::getInstance()->processEvent($event);

        $this->assertNotEquals($event->getMicroTimeStart(), null);
        */
    }

    public function testAddEvent() {
    }

    public function testGetClassName() {
        $this->assertEquals(DelayedEvents::getInstance()->getClassName("test123"), 'Test123Event');
        $this->assertEquals(DelayedEvents::getInstance()->getClassName("TesT123"), 'TesT123Event');
    }

    public function testGetClassPath() {
        DelayedEvents::getInstance()->eventsPath = null;
        $result = DelayedEvents::getInstance()->getClassPath("test123");
        $this->assertNotFalse(strpos($result, 'Test123Event.php'));

        $result = DelayedEvents::getInstance()->getClassPath("TesT123");
        $this->assertNotFalse(strpos($result, 'TesT123Event.php'));

        DelayedEvents::getInstance()->eventsPath = DIR_ROOT;
        $result = DelayedEvents::getInstance()->getClassPath("test123");
        $this->assertEquals($result, DIR_ROOT . 'Test123Event.php');
    }

    public function testGetEventObject() {
        DelayedEvents::getInstance()->eventsPath = null;
        $event = DelayedEvents::getInstance()->getEventObject('321123test123321');
        $this->assertEquals(get_class($event), 'delayed_events\events\MockEvent');

        $event = DelayedEvents::getInstance()->getEventObject('test');
        $this->assertTrue(is_object($event));
        $this->assertEquals(get_class($event), 'events\items\TestEvent');
    }
}