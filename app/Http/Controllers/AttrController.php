<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Helpers\helpfix as Res;
use App\Helpers\ModelsDB;
use App\Helpers\Global_help;

class AttrController extends Controller
{
    public function getchecklistsitems(Request $req)
    {
        $res = new Res('');
        
        $res->line(1, `Validator`); 
        $result = Global_help::validator($req->all(), []);
        if(!$result[0])
            return $res->fail($result[1], $result[2]);

        $res->line(2, `SetParamTarget`); 
        if(!isEmpty($req->param))
        {
            $param = json_decode($req->param);
            $res->target = $param->target; 
        }

        $res->line(3, `CheckData`); 
        $result = Global_help::checkDataById('Item_attributes', 'created_at', '2019-07-25 09:34:02');
        if(!isEmpty($result))
            return $res->fail('DataAlreadyExists' . " '" . '2019-07-25 09:34:02' . "'");

        $res->line(4, `InsertData`); 
        $param = json_decode($req->getContent(), true);
        $param['data']['attribute']['created_at'] = \Carbon\Carbon::parse('')->format('Y-m-d H:m:s');
        $result = Global_help::insertData('Item_attributes', $param['data']['attribute']);
        if($result)
            $res->data = Global_help::checkDataById('Item_attributes', 'created_at', '2019-07-25 09:34:02');

        $res->succes();
        return $res->done();
    }

}