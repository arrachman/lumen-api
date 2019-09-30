<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class AreaController extends Controller
{
    // table identity
    public $tableName = 'f1_area';
    public $primaryKey = 'akode';

    // parameter for select data
    public $select = 'akode, anama, acatatan, aaktif, ainputuser, ainputtgl, amodifikasiuser, amodifikasitgl';
    public $selectFormatDate = 'ainputtgl, amodifikasitgl';

    // Parameter for post data
    public $validatorPost = [
           'akode'   => 'required|max:25',
           'anama'   => 'required|max:100',
           'acatatan'   => 'max:250',
           'aaktif'   => 'required|numeric|max:2',
           'ainputuser'   => 'required|numeric|max:20',
           'ainputtgl'   => 'required|date'];
    public $dataPost = 'akode, anama, acatatan, aaktif, ainputuser, ainputtgl';

    // Parameter for update data
    public $validatorUpdate = [
           'anama'   => 'required|max:100',
           'acatatan'   => 'max:250',
           'aaktif'   => 'required|numeric|max:2',
           'amodifikasiuser'   => 'required|numeric|max:20',
           'amodifikasitgl'   => 'required|date'];
    public $dataUpdate = 'anama, acatatan, aaktif, amodifikasiuser, amodifikasitgl';

    // Master 
    public $master = ''; 
    
    public function __construct() 
    {
        $this->master = new MasterController();
        $this->master->tableName        = $this->tableName;
        $this->master->primaryKey       = $this->primaryKey;
        $this->master->select           = $this->select;
        $this->master->selectFormatDate = $this->selectFormatDate;
        $this->master->validatorPost    = $this->validatorPost;
        $this->master->dataPost         = $this->dataPost;
        $this->master->validatorUpdate  = $this->validatorUpdate;
        $this->master->dataUpdate       = $this->dataUpdate;
    }

    public function show(Request $req, Route $route)
    {
        return $this->master->show($req, $route);
    }
    
    public function showById(Request $req, Route $route)
    {
        return $this->master->showById($req, $route);
    }
    
    public function insert(Request $req, Route $route)
    {
        return $this->master->insert($req, $route);
    }
    
    public function update(Request $req, Route $route)
    {
        return $this->master->update($req, $route);
    }
    
    public function delete(Request $req, Route $route)
    {
        return $this->master->delete($req, $route);
    }

}
