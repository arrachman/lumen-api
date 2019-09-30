<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class CityController extends Controller
{
    // table identity
    public $tableName = 'f1_city';
    public $primaryKey = 'ckode';

    // parameter for select data
    public $select = 'ckode, cnama, cpropinsi, ccatatan, caktif, cinputuser, cinputtgl, cmodifikasiuser, cmodifikasitgl, cnegara';
    public $selectFormatDate = 'cinputtgl, cmodifikasitgl';

    // Parameter for post data
    public $validatorPost = [
           'ckode'   => 'required|max:25',
           'cnama'   => 'required|max:100',
           'cpropinsi'   => 'max:25',
           'ccatatan'   => 'max:250',
           'caktif'   => 'required|numeric|max:2',
           'cinputuser'   => 'required|numeric|max:20',
           'cinputtgl'   => 'required|date',
           'cnegara'   => 'max:25'];
    public $dataPost = 'ckode, cnama, cpropinsi, ccatatan, caktif, cinputuser, cinputtgl, cnegara';

    // Parameter for update data
    public $validatorUpdate = [
           'cnama'   => 'required|max:100',
           'cpropinsi'   => 'max:25',
           'ccatatan'   => 'max:250',
           'caktif'   => 'required|numeric|max:2',
           'cmodifikasiuser'   => 'numeric|max:20',
           'cmodifikasitgl'   => 'required|date',
           'cnegara'   => 'max:25'];
    public $dataUpdate = 'cnama, cpropinsi, ccatatan, caktif, cmodifikasiuser, cmodifikasitgl, cnegara';

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
