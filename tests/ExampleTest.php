<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        // $response  = $this->get('/');
        $response = $this->call('GET', '/calculator?a=2&b=3');
        $this->assertEquals(5, $response->content());
        $response = $this->call('GET', '/calculator?a=8&b=3');
        $this->assertEquals(11, $response->content());
        // $this->assertEquals(
        //     $this->app->version(), $this->response->getContent()
        // );
    }
}
