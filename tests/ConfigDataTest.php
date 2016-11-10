<?php

use Edujugon\LaravelExtraFeatures\ConfigData;

class ConfigDataTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function no_valid_key()
    {
        $value = (new ConfigData())->getValue('whatever');

        $this->assertEquals(null,$value);
    }
}