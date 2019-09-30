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

class ItemsController extends Controller
{
    public function getallitems(Request $req)
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
        // if ($validator->fails()) 
        //     return $res->fails($validator->messages()->all());
 
        $db = new ModelsDB('Item_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/items/";
        $db->type = "items";
        $res->data = $db->getData($req->filter, '', $req->sort, $req->page['limit'], $req->page['offset']);

        $res->success();
        return $res->lumen();
    }

    public function create_checklist_item(Request $req)
    {
        $res = new Res('');

        $param = json_decode($req->getContent(), true);
        $param['data']['attribute']['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
        $param['data']['attribute']['checklist_id'] = $req->checklistId;
        $result = Global_help::insertDataResIndex('Item_attributes', $param['data']['attribute']);
        if($result['success'])
        {
            $db = new ModelsDB('Item_attributes');
            $db->http = "http://localhost:8000";
            $db->url = "/checklists/";
            $db->type = "checklists";
            return $db->getDataById('id', $result['id']);
        }

        $res->success();
        return $res->done();
    }

    public function complete_item(Request $req)
    {
        $res = new Res('');
        
        $param = json_decode($req->getContent(), true);

        
        $data = "";
        foreach($param['data'] as $val)
        {
            $data .= "," . $val['item_id'];
        }
        if(strlen($data) > 0)
        {
            $data = substr($data, 1, strlen($data));
            $filter = "id IN (" . $data .  ")";
            $param = [];
            $param['is_completed'] = 1;
            $param['updated_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
            $result = Global_help::updateDataFilterRaw('Item_attributes', $filter, $param);
            
            
            $db = DB::table('Item_attributes');
            $db->select(DB::raw('id, id as item_id, is_completed, checklist_id'));
            $db->whereRaw($filter);
            $data2 =$db->get();
            $res = new \StdClass;
            $res->data = [];
            foreach($data2 as $data)
            {
                $data->is_completed = boolval($data->is_completed);
                $res->data[] = $data;
            }

            return response()->json($res, 200);
        }

        $res->success();
        return $res->done();
    }

    public function incomplete_item(Request $req)
    {
        $res = new Res('');
        
        $param = json_decode($req->getContent(), true);

        
        $data = "";
        foreach($param['data'] as $val)
        {
            $data .= "," . $val['item_id'];
        }
        if(strlen($data) > 0)
        {
            $data = substr($data, 1, strlen($data));
            $filter = "id IN (" . $data .  ")";
            $param = [];
            $param['is_completed'] = 0;
            $param['updated_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
            $result = Global_help::updateDataFilterRaw('Item_attributes', $filter, $param);
            
            
            $db = DB::table('Item_attributes');
            $db->select(DB::raw('id, id as item_id, is_completed, checklist_id'));
            $db->whereRaw($filter);
            $data2 =$db->get();
            $res = new \StdClass;
            $res->data = [];
            foreach($data2 as $data)
            {
                $data->is_completed = boolval($data->is_completed);
                $res->data[] = $data;
            }

            return response()->json($res, 200);
        }

        $res->success();
        return $res->done();
    }

    public function update_bulk_checklist(Request $req)
    {
        $res = new Res('');
        
        $param = json_decode($req->getContent(), true);

        
        $data = [];
        foreach($param['data'] as $val)
        {
            // $data .= "," . $val['item_id'];

            $filter = [];
            $filter['id'] = $val['id'];
            $filter['checklist_id'] = $req->checklistId;
            $val['attributes']['updated_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
            $result = Global_help::updateDataFilter('Item_attributes', $filter, $val['attributes']);
            $res2 = new \StdClass;
            $res2->id = $val['id'];
            $res2->action = 'update';
            if($result)
                $res2->status = 200;
            else
                $res2->status = 403;
                
            $data[] = $res2;
        }

        $res = new \StdClass;
        $res->data = $data;
        return response()->json($res, 200);
        return $res;
    }
    
    public function get_checklist_item(Request $req)
    {
        $res = new Res('');

        $res->line(1, 'Validator'); 

        $validator = Validator::make((array)$req->page, [
            'limit' => 'required|numeric|min:10',
            'offset' => 'required|numeric|min:0']);
 
        $db = new ModelsDB('Item_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "checklists";
        $db->include = "items";
        $req2 = $req->all();
        // $req2['filter'] = [];
        $req2['filter']['id'] = [];
        $req2['filter']['id']['is'] = $req->itemId;
        $req2['filter']['checklist_id'] = [];
        $req2['filter']['checklist_id']['is'] = $req->checklistId;
        $param['data']['attribute']['checklist_id'] = $req->checklistId;
        if ($validator->fails()) 
        $res->data = $db->getData($req2['filter'], '', $req->sort, 1, 0);
        else
        $res->data = $db->getData($req2['filter'], '', $req->sort, $req->page['limit'], $req->page['offset']);

        $res->success();
        return $res->lumen();
    }

    public function summary_item(Request $req)
    {
        $res = new Res('');

        $res->line(1, 'Validator'); 

        $db = new ModelsDB('Item_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "checklists";
        $db->include = "items";
        $req2 = $req->all();
        // $req2['filter'] = [];
        $req2['filter']['id'] = [];
        $req2['filter']['id']['is'] = $req->itemId;
        $req2['filter']['created_at'] = [];
        $req2['filter']['created_at']['is'] = $req->checklistId;
        $param['data']['attribute']['checklist_id'] = $req->checklistId;

        $date = '"' . $req->date . '"';
        $sql = 'select count(1) today from `Item_attributes`
        WHERE created_at = ' . $date .'
        UNION ALL
        select count(1) past_due from `Item_attributes`
        WHERE due > ' . $date .'
        UNION ALL
        select count(1) this_week from `Item_attributes`
        WHERE created_at BETWEEN ' . $date .' AND ' . $date .' + interval 1 week 
        UNION ALL
        select count(1) past_week from `Item_attributes`
        WHERE created_at BETWEEN ' . $date .' - interval 1 week AND ' . $date .'
        UNION ALL
        select count(1) this_month from `Item_attributes`
        WHERE created_at BETWEEN ' . $date .' AND ' . $date .' + interval 1 month 
        UNION ALL
        select count(1) past_month from `Item_attributes`
        WHERE created_at BETWEEN ' . $date .' - interval 1 month AND ' . $date .'
        UNION ALL
        select count(1) total from `Item_attributes`
        WHERE created_at BETWEEN ' . $date .' - interval 1 month AND ' . $date .' + interval 1 month ';
        // return $sql;
        $db = DB::select($sql);
        $res = new \StdClass;
        $res->data = new \StdClass;
        $res->data->today = $db[0]->today;
        $res->data->past_due = $db[1]->today;
        $res->data->this_week = $db[2]->today;
        $res->data->past_week = $db[3]->today;
        $res->data->this_month = $db[4]->today;
        $res->data->past_month = $db[5]->today;
        $res->data->total = $db[6]->today;

        return response()->json($res, 200);
        $res->success();
        return $res->lumen();
    }

    public function list_all_items_in_given_checklists(Request $req)
    {
        $res = new Res('');

        $res->line(1, 'Validator'); 
        $validator = Validator::make((array)$req->all(), [
            'page' => 'required']);

        $validator = Validator::make((array)$req->page, [
            'limit' => 'required|numeric|min:10',
            'offset' => 'required|numeric|min:0']);
 
        $db = new ModelsDB('checklist_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "checklists";
        $db->include = "items";
        $req2 = $req->all();
        // $req2['filter'] = [];
        $req2['filter']['id'] = [];
        $req2['filter']['id']['is'] = $req->checklistId;
        if ($validator->fails()) 
        $res->data = $db->getData($req2['filter'], '', $req->sort, 1, 0);
        else
        $res->data = $db->getData($req2['filter'], '', $req->sort, $req->page['limit'], $req->page['offset']);

        $res->data['data'][0]->attributes->items = [];
        
        
        $filter = [];
        // $req2['filter'] = [];
        $filter['checklist_id'] = [];
        $filter['checklist_id']['is'] = $req->checklistId;
        $db = new ModelsDB('Item_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "checklists";
        $data = $db->getDataById_itemDetail('checklist_id', $req->checklistId);
        foreach($data['data'] as $data)
        {
            $res->data['data'][0]->attributes->items[] = $data->attributes;
        }

        $res->success();
        return $res->lumen();
    }

    public function update_checklist_item(Request $req)
    {
        $res = new Res('');
        
        $result = Global_help::checkDataById('Item_attributes', 'id', $req->checklistId);
        if(isEmpty($result))
            return $res->fail(`DataNotFound`);

        $param = json_decode($req->getContent(), true);
        $param['data']['attribute']['updated_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $req->itemId;
        $filter['checklist_id'] = [];
        $filter['checklist_id']['is'] = $req->checklistId;
        $result = Global_help::updateDataFilter('Item_attributes', $filter, $param['data']['attribute']);
        if(!$result)
            return $res->fail(`UpdateFailed`);
            
        $db = new ModelsDB('Item_attributes');
        $db->http = "http://localhost:8000";
        $db->url = "/checklists/";
        $db->type = "items";
        return $db->getDataById_itemDetail('id', $req->itemId);
    }

    public function delete_checklist_item(Request $req)
    {
        $res = new Res('');

        $filter = [];
        $filter['id'] = [];
        $filter['id']['is'] = $req->itemId;
        $filter['checklist_id'] = [];
        $filter['checklist_id']['is'] = $req->checklistId;
        $result = Global_help::DeleteDataFilter('Item_attributes', $filter);
            
        return $res->lumenDelete();
    }
}