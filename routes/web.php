<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/aa', function () use ($router) {
    return 'abc';
});
$source = 'Color';
$param = 'show';
// $router->get('/',function () use ($router) {
//         return $router->app->version();
//     });
$router->get('/calculator', $source . "Controller@add");
$router->get('/f1/color', $source . "Controller@" . $param);

// TEMPLATES
$router->get('/checklists/templates', "TemplatesController@list_all_checklists_templates");
$router->post('/checklists/templates', "TemplatesController@create_checklists_template");
$router->get('/checklists/templates/{templateId}', "TemplatesController@get_checklists_template");
$router->patch('/checklists/templates/{templateId}', "TemplatesController@update_checklists_template");
$router->delete('/checklists/templates/{templateId}', "TemplatesController@delete_checklists_template");
$router->post('/checklists/templates/{templateId}/assigns', "TemplatesController@assign_bulk");

// CHECKLISTS
$router->get('/checklists', "ChecklistsController@get_list_checklists");
$router->get('/checklists/{checklistId}', "ChecklistsController@get_checklists");
$router->patch('/checklists/{checklistId}', "ChecklistsController@update_checklist");
$router->delete('/checklists/{checklistId}', "ChecklistsController@delete_checklist");
$router->post('/checklists', "ChecklistsController@create_checklists");

// ITEMS
$router->post('/checklists/complete', "ItemsController@complete_item");
$router->post('/checklists/incomplete', "ItemsController@incomplete_item");
$router->post('/checklists/{checklistId}/items/_bulk', "ItemsController@update_bulk_checklist");
$router->post('/checklists/{checklistId}/items', "ItemsController@create_checklist_item");
$router->get('/checklists/{checklistId}/items', "ItemsController@list_all_items_in_given_checklists");
$router->get('/checklists/{checklistId}/items/{itemId}', "ItemsController@get_checklist_item");
$router->get('/checklists/items/summaries', "ItemsController@summary_item");
$router->patch('/checklists/{checklistId}/items/{itemId}', "ItemsController@update_checklist_item");
$router->delete('/checklists/{checklistId}/items/{itemId}', "ItemsController@delete_checklist_item");
$router->get('/checklists/item/s', "ItemsController@getallitems");
