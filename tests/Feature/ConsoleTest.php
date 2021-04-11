<?php

namespace Tests\Feature;

use App\Console\Kernel;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_console_schedule()
    {
        $kernel = new Kernel($this->app, new Dispatcher());
        $result = $kernel->call("schedule:run");
        $this->assertEquals(0, $result);
    }
}
