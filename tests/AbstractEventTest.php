<?php

use \events\items\TestEvent;
use \events\items\AbstractEvent;

class AbstractEventTest extends PHPUnit_Framework_TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }

    /*
    public function testSetFieldsValue() {
        $testData = [
            'id' => 123,
            'event_id' => hex2bin(genUUID()),
            'name' => 'test123321test',
            'delay' => 456465,
            'data' => null,
            'status_id' => 4,
            'd_created' => DelayedEvents::getInstance()->getCurDate(),
            'd_status_change' => $this->rocket->date->getCurDateShifted(),
        ];

        $event = new TestEvent();
        $event->setFieldsValue($testData);

        $this->assertNotEquals($event->getName(), $testData['name']);
        $this->assertEquals($event->getName(), 'Test');

        $this->assertEquals($event->getId(), $testData['id']);
        $this->assertEquals($event->getEventId(), $testData['event_id']);
        $this->assertEquals($event->getDelay(), $testData['delay']);
        $this->assertEquals($event->getData(), $testData['data']);
        $this->assertEquals($event->getStatusId(), $testData['status_id']);
        $this->assertEquals($event->getDCreated(), $testData['d_created']);
        $this->assertEquals($event->getDStatusChange(), $testData['d_status_change']);
    }

    public function testInitFields() {
        $event = new TestEvent();

        $this->assertEquals($event->getName(), 'Test');
        $this->assertFalse(is_null($event->getDCreated()));
        $this->assertEquals($event->getDCreated(), $event->getDStatusChange());
        $this->assertEquals($event->getStatusId(), AbstractEvent::STATUS_NEW);
    }

    public function testGetDExecute() {
        $event = new TestEvent();
        $dExecute = date('Y-m-d H:i:s', $this->rocket->date->dateToTimestamp($event->getDCreated()) + 60 * 20 - date('Z'));

        $this->assertEquals($event->getDExecute(), $dExecute);
    }
    */
}