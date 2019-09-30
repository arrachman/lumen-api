<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class CountryController extends Controller
{
    // table identity
    public $tableName = 'f1_country';
    public $primaryKey = 'ckode';

    // parameter for select data
    public $select = 'ckode, cnama, ccatatan, caktif, cinputuser, cinputtgl, cmodifikasiuser, cmodifikasitgl';
    public $selectFormatDate = 'cinputtgl, cmodifikasitgl';

    // Parameter for post data
    public $validatorPost = [
           'ckode'   => 'required|max:25',
           'cnama'   => 'required|max:50',
           'ccatatan'   => 'max:250',
           'caktif'   => 'required|numeric|max:2',
           'cinputuser'   => 'required|numeric|max:20',
           'cinputtgl'   => 'required|date'];
    public $dataPost = 'ckode, cnama, ccatatan, caktif, cinputuser, cinputtgl';

    // Parameter for update data
    public $validatorUpdate = [
           'cnama'   => 'required|max:50',
           'ccatatan'   => 'max:250',
           'caktif'   => 'required|numeric|max:2',
           'cmodifikasiuser'   => 'required|numeric|max:20',
           'cmodifikasitgl'   => 'required|date'];
    public $dataUpdate = 'cnama, ccatatan, caktif, cmodifikasiuser, cmodifikasitgl';

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
