<?php

namespace App\Http\Controllers\Admin\F0;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

use App\Helpers\helpfix  as Res;
use App\Helpers\ModelsDB;
use App\Helpers\Global_help;
use App\Http\Controllers\Controller;
use App\User;
use App\Transformers\UserTransformer;
use Auth;

define('TableName', 'f0_user');

class AuthController extends Controller
{
    // table identity
    public $tableName = 'f0_user';
    public $primaryKey = 'userid';

    public function register(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());
        
        $res->line(1, Validator); 
        $valid = [
          'ukode'             => 'required|max:25',
          'unama'             => 'required|max:100',
          'uaktif'            => 'required|numeric',
          'ulevel'            => 'required|numeric',
          'ubahasa'           => 'required|max:25',
          'uaktivitasproduksi'=> 'required|numeric'];
        $result = Global_help::validator($req->all(), $valid);
        if(!$result[0])
            return $res->fail($result[1], $result[2]);

        $res->line(2, SetParamTarget); 
        if(!isEmpty($req->param))
        {
            $param = json_decode($req->param);
            $res->target = $param->target; 
        }

        $res->line(3, CheckData); 
        $result = Global_help::checkDataById($this->tableName, $this->primaryKey, $req{$this->primaryKey});
        if(!isEmpty($result))
            return $res->fail(DataAlreadyExists . " '" . $req{$this->primaryKey} . "'");

        $res->line(4, InsertData); 
        $dataPost = [];
        $this->dataPost = strToArray('ukode, upassword, unama, uaktif, ulevel, ubahasa, uaktivitasproduksi, utoken');
        for($i=0;$i<sizeof($this->dataPost);$i++)
        {
            $column = $this->dataPost[$i];
            $value  = $req->{$column};
            
            if($column == 'upassword')
            $value = bcrypt($value);
            if($column == 'utoken')
            $value = bcrypt(\Carbon\Carbon::now());
            if(isEmpty($value))
              $value = '';
            
            $dataPost{$column} = $value;
        }
        $result = Global_help::insertDataResIndex($this->tableName, $dataPost);
        if($result['succes'])
            $res->data = Global_help::checkDataById($this->tableName, $this->primaryKey, $result['id']);

        $res->succes();
        return $res->done();
    }

    public function login(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());
        
        if(!Auth::attempt(['ukode'=>$req->username, 'password'=>$req->password]))
            return $res->fail('Your credential is wrong', 401);
       
        $res->line(2, SetParamTarget); 
        if(!isEmpty($req->param))
        {
            $param = json_decode($req->param);
            $res->target = $param->target; 
        }
        
        $res->line(2, 'Create token'); 
        $result = Global_help::UpdateData(TableName, 'ukode', $req->username, ['utoken' => bcrypt(\Carbon\Carbon::now())]);

        if(!$result)
            return $res->fail(UpdateFailed);

        $db = new ModelsDB('f0_user');
        $db->select = 'utoken';
        $res->data = $db->getDataById('ukode', $req->username);

        $res->succes();
        return $res->done();
    }

    public function logout(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());
        
        $res->line(2, SetParamTarget); 
        if(!isEmpty($req->param))
        {
            $param = json_decode($req->param);
            $res->target = $param->target; 
        }

        $authorization = $req->headers->all()['authorization'][0];
        $authorization = str_replace('Bearer ', '', $authorization);
        $res->data = Global_help::UpdateData(TableName, 'utoken', $authorization, ['utoken' => '']);

        $res->succes();
        return $res->done();
    }
}
