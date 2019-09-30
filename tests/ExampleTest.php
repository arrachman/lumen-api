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
    public function test_chechklists()
    {
        // $response  = $this->get('/');
        $response = $this->call('GET', '/checklists/items?filter[created_by][is]=556396&sort=-due&page[limit]=10&page[offset]=0');
        $res = json_decode($response->content());
        // Check mapping result
        $this->assertTrue(property_exists($res, 'meta'));
        $this->assertTrue(property_exists($res->meta, 'count'));
        $this->assertTrue(property_exists($res->meta, 'total'));
        $this->assertTrue(property_exists($res, 'data'));
        if(isset($res->meta->total))
        {
            if($res->meta->total > 0)
            {
                $this->assertTrue(property_exists($res->data[0], 'type'));
                $this->assertTrue(property_exists($res->data[0], 'id'));
                $this->assertTrue(property_exists($res->data[0], 'attributes'));
                $this->assertTrue(property_exists($res->data[0], 'links'));
            }
        }
        $this->assertTrue(property_exists($res, 'links'));
        $this->assertTrue(property_exists($res->links, 'first'));
        $this->assertTrue(property_exists($res->links, 'last'));
        $this->assertTrue(property_exists($res->links, 'next'));
        $this->assertTrue(property_exists($res->links, 'prev'));
        
        // Validasi Limit
        $response = $this->call('GET', '/checklists/items?filter[created_by][is]=556396&sort=-due&page[limit]=0&page[offset]=10');
        $res = json_decode($response->content());
        if($res->meta->total == 0)
            $this->assertEquals($res->data[0], "The limit must be at least 10.");
        
        // Validasi Offset
        $response = $this->call('GET', '/checklists/items?filter[created_by][is]=556396&sort=-due&page[limit]=10&page[offset]=-10');
        $res = json_decode($response->content());
        if($res->meta->total == 0)
            $this->assertEquals($res->data[0], "The offset must be at least 0.");
        // $this->assertEquals(5, $response->content());
        // $response = $this->call('GET', '/calculator?a=8&b=3');
        // $this->assertEquals(11, $response->content());
        // $this->assertEquals(
        //     $this->app->version(), $this->response->getContent()
        // );
    }
}
