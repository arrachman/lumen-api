<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CaseATest extends TestCase
{
    public function test_CaseA()
    {
        $header = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        
        // Create Checklist
        $url = '/checklists';
        $json = '{
            "data": {
              "attributes": {
                "object_domain": "contact",
                "object_id": "1",
                "due": "2019-01-25T07:50:14",
                "urgency": 1,
                "description": "Need to verify this guy house.",
                "items": [
                  "Visit his house",
                  "Capture a photo",
                  "Meet him on the house"
                ],
                "task_id": "123"
              }
            }
          }';
        $response = $this->call('POST', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $checklistId = $res->data[0]->id;

        // Get List of Checklists
        $url = '/checklists?include=items&filter[id][is]=' . $checklistId . '&sort=id&page[limit]=10&page[offset]=0';
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->id, $checklistId);

        // Get Checklists
        $url = '/checklists/' . $checklistId . '?include=items&sort=id&page[limit]=10&page[offset]=0';
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->id, $checklistId);

        // Update checklist item
        $url = '/checklists/' . $checklistId;
        $json = '{
            "data": {
              "type": "checklists",
              "id": 1,
              "attributes": {
                "object_domain": "contact",
                "object_id": "1",
                "description": "Need1 to verify this guy house.",
                "is_completed": false,
                "completed_at": null,
                "created_at": "2018-01-25T07:50:14"
              },
              "links": {
                "self": "https://dev-kong.command-api.kw.com/checklists/50127"
              }
            }
          }';
        $response = $this->call('PATCH', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->id, $checklistId);

        // Create checklist item
        $url = '/checklists/' . $checklistId . '/items';
        $json = '{"data": {"attribute": {
                                "description": "Need to verify this guy house.",
                                "due": "2019-01-19 18:34:51",
                                "urgency": "2",
                                "assignee_id": 123
                              }
                            }
                          }';
        $response = $this->call('POST', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $itemId = $res->data[0]->id;
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->attributes->checklist_id, $checklistId);

        // Get checklist item
        $url = '/checklists/' . $checklistId . '/items/'  . $itemId . '?sort=-id&page[limit]=10&page[offset]=0';
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->attributes->checklist_id, $checklistId);
        // -- Link self
        $url = $res->data[0]->links->self;
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->id, $checklistId);

        // List all items in given checklists
        $url = '/checklists/' . $checklistId . '/items';
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->id, $checklistId);
        $this->assertEquals($res->data[0]->attributes->items[0]->checklist_id, $checklistId);
        // -- Link self
        $url = $res->data[0]->links->self;
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'checklists');
        $this->assertEquals($res->data[0]->id, $checklistId);

        // Get all items
        $url = '/checklists/item/s?filter[due][between]=2019-01-04T17:00:00.000,2019-10-11T16:59:59.5959&filter[created_by][is]=556396&sort=id&page[limit]=10&page[offset]=0';
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'items');
        $this->assertEquals($res->data[0]->attributes->created_by, 556396);

        // Summary item
        $url = '/checklists/items/summaries?date=2019-09-30T00:00:00&object_domain=&tz';
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertTrue(true);

        // Incomplete Item(s)
        $url = '/checklists/incomplete';
        $json = '{
            "data": [
              {
                "item_id": '. $itemId .'
              }
            ]
          }';
        $response = $this->call('POST', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->is_completed, false);
        $this->assertEquals($res->data[0]->item_id, $itemId);
        $this->assertEquals($res->data[0]->checklist_id, $checklistId);

        // Complete Item(s)
        $url = '/checklists/complete';
        $json = '{
            "data": [
              {
                "item_id": '. $itemId .'
              }
            ]
          }';
        $response = $this->call('POST', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->is_completed, true);
        $this->assertEquals($res->data[0]->item_id, $itemId);
        $this->assertEquals($res->data[0]->checklist_id, $checklistId);

        // Update bulk checklist
        $url = '/checklists/' . $checklistId . '/items/_bulk';
        $json = '{
            "data": [
              {
                "id": "' . $itemId . '",
                "action": "update",
                "attributes": {
                  "description": "oke oke",
                  "due": "2019-01-19 18:34:51",
                  "urgency": "2"
                }
              }
            ]
          }';
        $response = $this->call('POST', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->status, 200);
        $this->assertEquals($res->data[0]->action, 'update');

        // Update checklist item
        $url = '/checklists/' . $checklistId . '/items/'  . $itemId;
        $json = '{
            "data": {
              "attribute": {
                "description": "Need to verify this guy house 2.",
                "due": "2019-01-19 18:34:51",
                "urgency": "2",
                "assignee_id": 123
              }
            }
          }';
        $response = $this->call('PATCH', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'items');
        $this->assertEquals($res->data[0]->id, $itemId);

        // Delete Checklist item
        $url = '/checklists/' . $checklistId . '/items/'  . $itemId ;
        $json = '';
        $response = $this->call('DELETE', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertTrue(true);

        // Delete Checklist
        $url = '/checklists/' . $checklistId;
        $json = '';
        $response = $this->call('DELETE', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertTrue(true);

        // Create checklist template
        $url = '/checklists/templates';
        $json = '{
            "data": {
              "attributes": {
                "name": "foo template",
                "checklist": {
                  "description": "my checklist",
                  "due_interval": 3,
                  "due_unit": "hour"
                },
                "items": [
                  {
                    "description": "my foo item",
                    "urgency": 2,
                    "due_interval": 40,
                    "due_unit": "minute"
                  },
                  {
                    "description": "my bar item",
                    "urgency": 3,
                    "due_interval": 30,
                    "due_unit": "minute"
                  }
                ]
              }
            }
          }';
        $response = $this->call('POST', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $templateId = $res->data[0]->id;
        $this->assertEquals($res->data[0]->type, 'templates');
        $this->assertEquals($res->data[0]->attributes->checklist->description, 'my checklist');
        $this->assertEquals($res->data[0]->attributes->items[0]->description, 'my foo item');

        // List all checklists templates
        $url = '/checklists/templates?filter[id][is]=' . $templateId . '&sort=id&fields&page[limit]=2&page[offset]=0';
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'templates');
        $this->assertEquals($res->data[0]->id, $templateId);

        // Get checklist template
        $url = '/checklists/templates/' . $templateId;
        $json = '';
        $response = $this->call('GET', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'templates');
        $this->assertEquals($res->data[0]->id, $templateId);

        // Update Checklist Template
        $url = '/checklists/templates/' . $templateId;
        $json = '{
            "data": {
              "name": "foo template 3",
              "checklist": {
                "description": "my checklist2",
                "due_interval": 3,
                "due_unit": "hour"
              },
              "items": [
                {
                  "description": "my foo item2",
                  "urgency": 2,
                  "due_interval": 40,
                  "due_unit": "minute"
                },
                {
                  "description": "my bar item",
                  "urgency": 3,
                  "due_interval": 30,
                  "due_unit": "minute"
                }
              ]
            }
          }';
        $response = $this->call('PATCH', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'templates');
        $this->assertEquals($res->data[0]->id, $templateId);
        $this->assertEquals($res->data[0]->attributes->checklist->description, 'my checklist2');
        $this->assertEquals($res->data[0]->attributes->items[0]->description, 'my foo item2');

        // Assign a checklist template by given templateId to a domain
        $url = '/checklists/templates/' . $templateId . '/assigns';
        $json = '{
            "data": [
              {
                "attributes": {
                  "object_id": 1,
                  "object_domain": "deals"
                }
              },
              {
                "attributes": {
                  "object_id": 2,
                  "object_domain": "deals"
                }
              },
              {
                "attributes": {
                  "object_id": 3,
                  "object_domain": "deals"
                }
              }
            ]
          }';
        $response = $this->call('POST', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertEquals($res->data[0]->type, 'templates');
        $this->assertEquals($res->data[0]->id, $templateId);
        $this->assertEquals($res->data[0]->attributes->checklist->description, 'my checklist2');
        $this->assertEquals($res->data[0]->attributes->items[0]->description, 'my foo item2');

        // Delete Checklist
        $url = '/checklists/templates/' . $templateId;
        $json = '';
        $response = $this->call('DELETE', $url,[],[],[],$header, $json);
        $res = json_decode($response->content());
        $this->assertTrue(true);
    }
    
}
