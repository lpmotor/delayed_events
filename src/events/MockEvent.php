<?php namespace delayed_events\events;

class MockEvent extends AbstractEvent
{
    /**
     * @throws \Exception
     */
    public function checkConditions() {
        throw new \Exception("Обработчик события не найден!", 4150);
    }

    public function execute() {
        return false;
    }
}