<?php namespace delayed_events\events;

class TestEvent extends AbstractEvent
{
    protected $delay = 1200;

    public function checkConditions() {
        return true;
    }

    public function execute() {
        return true;
    }
}