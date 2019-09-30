<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Helpers\helpfix as Res;
use App\Helpers\ModelsDB;
use App\Helpers\Global_help;
use Validator;
use DB;

class TemplatesController extends Controller
{
    public function list_all_checklists_templates(Request $req)
    {
        $res = new Res('');
 
        $db = new ModelsDB('template_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/templates/";
        $db->type = "templates";
        $res->data = $db->getData($req->filter, '', $req->sort, $req->page['limit'], $req->page['offset']);

        foreach($res->data['data'] as $no => $template)
        {
            $db = new ModelsDB('checklist_attributes');
            $db->select = 'id, description, due_interval, due_unit';
            $db->type = 'templates';
            $checklist = $res->data['data'][$no]->attributes->checklist;
            $res->data['data'][$no]->attributes->checklist = $db->getDataById('id', $res->data['data'][$no]->attributes->checklist);
            $res->data['data'][$no]->attributes->checklist = $res->data['data'][$no]->attributes->checklist['data'][0]->attributes;
            
            $db = new ModelsDB('Item_attributes');
            $db->select = 'id, description, urgency, due_interval, due_unit, checklist_id';
            $db->type = 'items';
            $res->data['data'][$no]->attributes->items = $db->getDataById('checklist_id', $checklist);
            $data = [];
            foreach($res->data['data'][$no]->attributes->items['data'] as $items)
            {
                unset($items->attributes->checklist_id);
                $data[] = $items->attributes;
    
            }
            $res->data['data'][$no]->attributes->items = $data;
        }
        
        return $res->lumen();
    }
    
    public function create_checklists_template(Request $req)
    {
        $res = new Res('');
        
        // Create checklists
        $param = json_decode($req->getContent(), true);
        $param['data']['attributes']['checklist']['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
        $checklist = Global_help::insertDataResIndex('checklist_attributes', $param['data']['attributes']['checklist']);

        if($checklist['success'])
        {
            // Create itemlist
            foreach($param['data']['attributes']['items'] as $items)
            {
                $param = json_decode($req->getContent(), true);
                $items['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
                $items['checklist_id'] = $checklist['id'];
                Global_help::insertDataResIndex('Item_attributes', $items);
            }

            $param = json_decode($req->getContent(), true);
            unset($param['data']['attributes']['items']);
            unset($param['data']['attributes']['checklist']);
            $param['data']['attributes']['checklist'] = $checklist['id'];
            // $param['data']['attributes']['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
            $result = Global_help::insertDataResIndex('template_attributes', $param['data']['attributes']);
    
            if($result['success'])
            {
                $db = new ModelsDB('template_attributes');
                $db->http = "http://localhost:8000";
                $db->url = "/checklists/templates/";
                $db->type = "templates";
                $req2 = $req->all();
                $req2['filter'] = [];
                $req2['filter']['id'] = [];
                $req2['filter']['id']['is'] = $result['id'];
                $res->data = $db->getDataById('id', $result['id']);
                unset($res->data['data'][0]->links);
        
                $db = new ModelsDB('checklist_attributes');
                $db->select = 'id, description, due_interval, due_unit';
                $db->type = 'templates';
                $checklist = $res->data['data'][0]->attributes->checklist;
                $res->data['data'][0]->attributes->checklist = $db->getDataById('id', $res->data['data'][0]->attributes->checklist);
                $res->data['data'][0]->attributes->checklist = $res->data['data'][0]->attributes->checklist['data'][0]->attributes;
                
                $db = new ModelsDB('Item_attributes');
                $db->select = 'id, description, urgency, due_interval, due_unit, checklist_id';
                $db->type = 'items';
                $res->data['data'][0]->attributes->items = $db->getDataById('checklist_id', $checklist);
                $data = [];
                foreach($res->data['data'][0]->attributes->items['data'] as $items)
                {
                    unset($items->attributes->checklist_id);
                    $data[] = $items->attributes;
        
                }
                $res->data['data'][0]->attributes->items = $data;
                return $res->lumen();
            }
        }

        $res->success();
        return $res->done();
    }
    
    public function get_checklists_template(Request $req)
    {
        $res = new Res('');

        $db = new ModelsDB('template_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/templates/";
        $db->type = "templates";
        $req2 = $req->all();
        $req2['filter'] = [];
        $req2['filter']['id'] = [];
        $req2['filter']['id']['is'] = $req->templateId;
        $res->data = $db->getDataById('id', $req->templateId);
        unset($res->data['data'][0]->links);

        $db = new ModelsDB('checklist_attributes');
        $db->select = 'id, description, due_interval, due_unit';
        $db->type = 'templates';
        $checklist = $res->data['data'][0]->attributes->checklist;
        $res->data['data'][0]->attributes->checklist = $db->getDataById('id', $res->data['data'][0]->attributes->checklist);
        $res->data['data'][0]->attributes->checklist = $res->data['data'][0]->attributes->checklist['data'][0]->attributes;
        
        $db = new ModelsDB('Item_attributes');
        $db->select = 'id, description, urgency, due_interval, due_unit, checklist_id';
        $db->type = 'items';
        $res->data['data'][0]->attributes->items = $db->getDataById('checklist_id', $checklist);
        $data = [];
        foreach($res->data['data'][0]->attributes->items['data'] as $items)
        {
            unset($items->attributes->checklist_id);
            $data[] = $items->attributes;

        }
        $res->data['data'][0]->attributes->items = $data;
        
        return $res->lumen();
    }
    
    public function update_checklists_template(Request $req)
    {
        $res = new Res('');

        $result = Global_help::checkDataById('template_attributes', 'id', $req->templateId);
        if(isEmpty($result))
            return $res->fail(`DataNotFound`);

        $param = json_decode($req->getContent(), true);
        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $req->templateId;
        $setData = $param['data'];
        unset($setData['checklist']);
        unset($setData['items']);
        $result = Global_help::updateDataFilter('template_attributes', $filter, $setData);

        $db = new ModelsDB('template_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/templates/";
        $db->type = "templates";
        $template_attributes = $db->getDataById('id', $req->templateId);

        
        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $template_attributes['data'][0]->attributes->checklist;
        $result = Global_help::updateDataFilter('checklist_attributes', $filter, $param['data']['checklist']);
        
        // Delete itemlist
        $filter = [];
        $filter['checklist_id'] = [];
        $filter['checklist_id']['is'] = $template_attributes['data'][0]->attributes->checklist;
        $result = Global_help::DeleteDataFilter('Item_attributes', $filter);

        // Create itemlist
        foreach($param['data']['items'] as $items)
        {
            $param = json_decode($req->getContent(), true);
            $items['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
            $items['checklist_id'] = $template_attributes['data'][0]->attributes->checklist;
            Global_help::insertDataResIndex('Item_attributes', $items);
        }

        $param = json_decode($req->getContent(), true);
        unset($param['data']['items']);
        unset($param['data']['checklist']);
        $param['data']['checklist'] = $template_attributes['data'][0]->attributes->checklist;

        $db = new ModelsDB('template_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/templates/";
        $db->type = "templates";
        $req2 = $req->all();
        $req2['filter'] = [];
        $req2['filter']['id'] = [];
        $req2['filter']['id']['is'] = $req->templateId;
        $res->data = $db->getDataById('id', $req->templateId);
        unset($res->data['data'][0]->links);

        $db = new ModelsDB('checklist_attributes');
        $db->select = 'id, description, due_interval, due_unit';
        $db->type = 'templates';
        $checklist = $res->data['data'][0]->attributes->checklist;
        $res->data['data'][0]->attributes->checklist = $db->getDataById('id', $res->data['data'][0]->attributes->checklist);
        $res->data['data'][0]->attributes->checklist = $res->data['data'][0]->attributes->checklist['data'][0]->attributes;
        
        $db = new ModelsDB('Item_attributes');
        $db->select = 'id, description, urgency, due_interval, due_unit, checklist_id';
        $db->type = 'items';
        $res->data['data'][0]->attributes->items = $db->getDataById('checklist_id', $checklist);
        $data = [];
        foreach($res->data['data'][0]->attributes->items['data'] as $items)
        {
            unset($items->attributes->checklist_id);
            $data[] = $items->attributes;

        }
        $res->data['data'][0]->attributes->items = $data;
        return $res->lumen();
    }
    
    public function delete_checklists_template(Request $req)
    {
        $res = new Res('');

        $db = new ModelsDB('template_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/templates/";
        $db->type = "templates";
        $template_attributes = $db->getDataById('id', $req->templateId);

        $filter = [];
        $filter['checklist_id'] = [];
        $filter['checklist_id']['is'] = $template_attributes['data'][0]->attributes->checklist;
        $result = Global_help::DeleteDataFilter('Item_attributes', $filter);

        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $template_attributes['data'][0]->attributes->checklist;
        $result = Global_help::DeleteDataFilter('checklist_attributes', $filter);

        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $req->templateId;
        $result = Global_help::DeleteDataFilter('template_attributes', $filter);
            
        return $res->lumenDelete();
    }
    
    public function assign_bulk(Request $req)
    {
        
        $res = new Res('');

        $result = Global_help::checkDataById('template_attributes', 'id', $req->templateId);
        if(isEmpty($result))
            return $res->fail(`DataNotFound`);

        $param = json_decode($req->getContent(), true);

        $db = new ModelsDB('template_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/templates/";
        $db->type = "templates";
        $template_attributes = $db->getDataById('id', $req->templateId);
        
        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $template_attributes['data'][0]->attributes->checklist;
        $result = Global_help::updateDataFilter('checklist_attributes', $filter, $param['data'][0]['attributes']);
        
        $db = new ModelsDB('template_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/templates/";
        $db->type = "templates";
        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $req->templateId;
        $res->data = $db->getData($filter, '', '', 10, 0);

        foreach($res->data['data'] as $no => $template)
        {
            $db = new ModelsDB('checklist_attributes');
            $db->select = 'id, description, due_interval, due_unit';
            $db->type = 'templates';
            $checklist = $res->data['data'][$no]->attributes->checklist;
            $res->data['data'][$no]->attributes->checklist = $db->getDataById('id', $res->data['data'][$no]->attributes->checklist);
            $res->data['data'][$no]->attributes->checklist = $res->data['data'][$no]->attributes->checklist['data'][0]->attributes;
            
            $db = new ModelsDB('Item_attributes');
            $db->select = 'id, description, urgency, due_interval, due_unit, checklist_id';
            $db->type = 'items';
            $res->data['data'][$no]->attributes->items = $db->getDataById('checklist_id', $checklist);
            $data = [];
            foreach($res->data['data'][$no]->attributes->items['data'] as $items)
            {
                unset($items->attributes->checklist_id);
                $data[] = $items->attributes;
    
            }
            $res->data['data'][$no]->attributes->items = $data;
        }
        
        return $res->lumen();
    }
    
}