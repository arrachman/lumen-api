<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Helpers\helpfix as Res;
use App\Helpers\ModelsDB;
use App\Helpers\Global_help;
use Validator;

class ChecklistsController extends Controller
{
    public function create_checklists(Request $req)
    {
        $res = new Res('');
        

        // DB::enableQueryLog();
        // dd(DB::getQueryLog()); // Show results of log

        $param = json_decode($req->getContent(), true);
        unset($param['data']['attributes']['items']);
        $param['data']['attributes']['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
        $result = Global_help::insertDataResIndex('checklist_attributes', $param['data']['attributes']);
        if($result['success'])
        {
            $db = new ModelsDB('checklist_attributes');
            $db->http = "http://localhost:8000";
            $db->url = "/checklists/";
            $db->type = "checklists";
            return $db->getDataById('id', $result['id']);
        }

        $res->success();
        return $res->done();
    }

    public function get_list_checklists(Request $req)
    {
        $res = new Res('');

        $res->line(1, 'Validator'); 
        $validator = Validator::make((array)$req->all(), [
            'page' => 'required']);
        if ($validator->fails()) 
            return $res->fails($validator->messages()->all());

        $validator = Validator::make((array)$req->page, [
            'limit' => 'required|numeric|min:10',
            'offset' => 'required|numeric|min:0']);
        if ($validator->fails()) 
            return $res->fails($validator->messages()->all());
 
        $db = new ModelsDB('checklist_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "checklists";
        $db->include = "items";
        $res->data = $db->getData($req->filter, '', $req->sort, $req->page['limit'], $req->page['offset']);

        $res->success();
        return $res->lumen();
    }

    public function get_checklists(Request $req)
    {
        $res = new Res('');

        $res->line(1, 'Validator'); 
        $validator = Validator::make((array)$req->all(), []);
        if ($validator->fails()) 
            return $res->fails($validator->messages()->all());

        $validator = Validator::make((array)$req->page, [
            'limit' => 'required|numeric|min:10',
            'offset' => 'required|numeric|min:0']);

        $db = new ModelsDB('checklist_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "checklists";
        $db->include = "items";
        $req2 = $req->all();
        $req2['filter'] = [];
        $req2['filter']['id'] = [];
        $req2['filter']['id']['is'] = $req->checklistId;

        if ($validator->fails()) 
        {
            // return $res->fails($validator->messages()->all());
 
            $res->data = $db->getData($req2['filter'], '', $req->sort, 10, 0);
        }
        else
            $res->data = $db->getData($req2['filter'], '', $req->sort, $req->page['limit'], $req->page['offset']);

        $res->success();
        return $res->lumen();
    }

    public function delete_checklist(Request $req)
    {
        $res = new Res('');

        if(!isEmpty($req->param))
        {
            $param = json_decode($req->param);
            $res->target = $param->target; 
        }

        // $result = Global_help::checkDataById('checklist_attributes', 'id', $req->checklistId);
        // if(isEmpty($result))
        //     return $res->fail(`DataNotFound`);

        $result = Global_help::deleteData('checklist_attributes', 'id', $req->checklistId);
            
        if(!$result)
        return $res->lumenDelete();

        $res->success();
        return $res->lumenDelete();
    }
    
    public function update_checklist(Request $req)
    {
        $res = new Res('');
        
        $result = Global_help::checkDataById('checklist_attributes', 'id', $req->checklistId);
        if(isEmpty($result))
            return $res->fail(`DataNotFound`);

        $param = json_decode($req->getContent(), true);
        $param['data']['attributes']['updated_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
        $result = Global_help::updateData('checklist_attributes', 'id', $req->checklistId, $param['data']['attributes']);

        if(!$result)
            return $res->fail(`UpdateFailed`);
            
        $db = new ModelsDB('checklist_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "checklists";
        return $db->getDataById('id', $req->checklistId);
        
        $res->success();
        return $res->done();
    }

    
}