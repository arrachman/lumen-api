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
    }
    
    public function create_checklists_template(Request $req)
    {
        $res = new Res('');
        
        // Create check lists
        $param = json_decode($req->getContent(), true);
        unset($param['data']['attributes']['items']);
        $param['data']['attributes']['checklist']['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
        $checklist = Global_help::insertDataResIndex('checklist_attributes', $param['data']['checklist']['attributes']);

        
        if($checklist['success'])
        {

            foreach($param['data']['attributes']['items'] as $items)
            {
                $param = json_decode($req->getContent(), true);
                $items['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
                $items['checklist_id'] = $checklist['id'];
                $result = Global_help::insertDataResIndex('Item_attributes', $param['data']['attribute']);
                if($result['success'])
                {
                    $db = new ModelsDB('Item_attributes');
                    $db->http = "http://localhost:8000";
                    $db->url = "/checklists/";
                    $db->type = "checklists";
                    return $db->getDataById('id', $result['id']);
                }
            }

            $param = json_decode($req->getContent(), true);
            $param['data']['attributes']['checklist'] = $checklist['id'];
            $param['data']['attributes']['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
            $result = Global_help::insertDataResIndex('template_attributes', $param['data']['attributes']);
    
            if($result['success'])
            {
                $db = new ModelsDB('template_attributes');
                $db->http = "http://localhost:8000";
                $db->url = "/checklists/templates/";
                $db->type = "templates";
                return $db->getDataById('id', $result['id']);
            }
        }

        $res->success();
        return $res->done();
    }
    
    public function get_checklists_template(Request $req)
    {
    }
    
    public function update_checklists_template(Request $req)
    {
    }
    
    public function delete_checklists_template(Request $req)
    {
    }
    
    public function assign_bulk(Request $req)
    {
    }
    
}