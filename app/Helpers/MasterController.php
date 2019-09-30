<?php

namespace App\Http\Controllers;

use App\Helpers\helpfix as Res;
use App\Helpers\ModelsDB;
use App\Helpers\Global_help;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Validator;
use Auth;

class MasterController extends Controller
{
    // table identity
    public $tableName = '';
    public $primaryKey = '';

    // parameter for select data
    public $select = '';
    public $selectFormatDate = '';

    // Parameter for post data
    public $validatorPost = '';
    public $dataPost = '';

    // Parameter for update data
    public $validatorUpdate = '';
    public $dataUpdate = '';

    public function showSearch(Request $req, Route $route, String $table, String $select, String $selectDate, String $title, Array $header)
    {
        $res = new Res($route->getActionName());

        $res->line(1, Validator); 
        $result = Global_help::validatorGet($req->param);
        if(!$result[0])
            return $res->fail($result[1], $result[2]);

        $res->line(2, SetParamTarget); 
        $param = json_decode($req->param);
        $res->target = $param->target; 

        $res->line(3, GetData); 
        $filter = '';
        if($param->filter != '')
            $filter = $param->filter;

        if($param->filterSearch != '')
        {
            $filter2 = '';

            if($param->column == 'All')
            {
                for($i=0;$i<sizeof($header);$i++)
                {
                    if($filter != '') $filter .= ' OR ';
                    $filter .= $this->caseOperator($param->operator, $header[$i][0], $param->filterSearch);
                }
            }
            else
            {
                $filter .= $this->caseOperator($param->operator, $param->column, $param->filterSearch);
            }

            if($filter2 != '' && $filter != '') $filter = '(' . $filter . ') AND (' . $filter2 . ')';
        }

        $db = new ModelsDB($table);
        $db->select           = $select;
        $db->selectFormatDate = $selectDate;
        $data = $db->getData($filter, $param->groupBy, $param->orderBy, $param->pageLimit, $param->pageNumber);
        
        $res->line(4, 'Set Data Title, Header, Data'); 
        $res->data = new \StdClass;
        $res->data->title = $title;
        $res->data->header = $header;
        $res->data->data = $data;

        $res->succes();
        return $res->done();
    }

    function caseOperator($operator, $column, $value)
    {
        switch($operator)
        {
            case 'contains': return $column . " LIKE '%" . $value . "%'";
            case 'begin with': return $column . " LIKE '" . $value . "%'";
            case 'end with': return $column . " LIKE '%" . $value . "'";
            default: return $column . " " . $operator . " '" . $value . "'";
        }
    }

    public function show(Request $req)
    {
        $res = new Res('');

        $res->line(1, 'Validator'); 
        $result = Global_help::validatorGet($req->param);
        if(!$result[0])
            return $res->fail($result[1], $result[2]);

        $res->line(2, 'SetParamTarget'); 
        $param = json_decode($req->param);
        $res->target = $param->target; 

        $res->line(3, 'GetData'); 
        $db = new ModelsDB($this->tableName);
        $db->select           = $this->select;
        $db->selectFormatDate = $this->selectFormatDate;
        $res->data = $db->getData($param->filter, $param->groupBy, $param->orderBy, $param->pageLimit, $param->pageNumber);

        $res->succes();
        return $res->done();
    }
    
    public function showById(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $res->line(2, SetParamTarget); 
        if(!isEmpty($req->param))
        {
            $param = json_decode($req->param);
            $res->target = $param->target; 
        }

        $res->line(3, GetData); 
        $db = new ModelsDB($this->tableName);
        $db->selectFormatDate = $this->selectFormatDate;
        $res->data = $db->getDataById($this->primaryKey, $req->post);

        if(isEmpty($res->data))
            return $res->fail(DataNotFound);
            
        $res->succes();
        return $res->done();
    }
    
    public function insert(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());
        
        $res->line(1, Validator); 
        $result = Global_help::validator($req->all(), $this->validatorPost);
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
        $this->dataPost = strToArray($this->dataPost);
        for($i=0;$i<sizeof($this->dataPost);$i++)
        {
            $value = $req->{$this->dataPost[$i]};
            
            if(isEmpty($value))
                $value = '';
            
            $dataPost{$this->dataPost[$i]} = $value;
        }
        $result = Global_help::insertData($this->tableName, $dataPost);
        if($result)
            $res->data = Global_help::checkDataById($this->tableName, $this->primaryKey, $req{$this->primaryKey});

        $res->succes();
        return $res->done();
    }
    
    public function update(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());
        
        $res->line(1, Validator); 
        $result = Global_help::validator($req->all(), $this->validatorUpdate);
        if(!$result[0])
            return $res->fail($result[1], $result[2]);

        $res->line(3, CheckData); 
        $result = Global_help::checkDataById($this->tableName, $this->primaryKey, $req->post);
        if(isEmpty($result))
            return $res->fail(DataNotFound);

        $res->line(3, UpdateData); 
        $dataUpdate = [];
        $this->dataUpdate = strToArray($this->dataUpdate);
        for($i=0;$i<sizeof($this->dataUpdate);$i++)
        {
            $value = $req->{$this->dataUpdate[$i]};
            
            if(isEmpty($value))
                $value = '';
            
            $dataUpdate{$this->dataUpdate[$i]} = $value;
        }
        // $dataUpdate{$this->dataUpdate[$i]} = "'" . $req->{$this->dataUpdate[$i]} . "'";
        $result = Global_help::updateData($this->tableName, $this->primaryKey, $req->post, $dataUpdate);

        if(!$result)
            return $res->fail(UpdateFailed);
            
        $res->data = Global_help::checkDataById($this->tableName, $this->primaryKey, $req->post);
        
        $res->succes();
        return $res->done();
    }
    
    public function delete(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $res->line(2, SetParamTarget); 
        if(!isEmpty($req->param))
        {
            $param = json_decode($req->param);
            $res->target = $param->target; 
        }

        $res->line(3, CheckData); 
        $result = Global_help::checkDataById($this->tableName, $this->primaryKey, $req->post);
        if(isEmpty($result))
            return $res->fail(DataNotFound);

        $res->line(3, DeleteData); 
        $result = Global_help::deleteData($this->tableName, $this->primaryKey, $req->post);
            
        if(!$result)
            return $res->fail(DeleteFailed);

        $res->succes();
        return $res->done();
    }
}