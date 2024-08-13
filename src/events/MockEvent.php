<?php namespace delayed_events\events;

use Exception;

class MockEvent extends AbstractEvent
{
    /**
     * @throws Exception
     */
    public function checkConditions()
    {
        throw new Exception("The event handler was not found!", 4150);
    }

    public function execute()
    {
        return false;
    }
}
