<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class BranchController extends Controller
{
    // table identity
    public $tableName = 'f1_branch';
    public $primaryKey = 'bkode';

    // parameter for select data
    public $select = 'bkode, bnama, balamat1, balamat2, bkota, bkodepos, bnotelp, bnofax, bcatatan, baktif, binputuser, binputtgl, bmodifikasiuser, bmodifikasitgl';
    public $selectFormatDate = 'binputtgl, bmodifikasitgl';

    // Parameter for post data
    public $validatorPost = [
            'bkode'           => 'required|max:25',
            'bnama'           => 'required|max:100',
            'balamat1'        => 'max:250',
            'balamat2'        => 'max:250',
            'bkota'           => 'max:25',
            'bkodepos'        => 'max:50',
            'bnotelp'         => 'max:50',
            'bnofax'          => 'max:50',
            'bcatatan'        => 'max:250',
            'baktif'          => 'required|numeric',
            'binputuser'      => 'required|numeric',
            'binputtgl'       => 'required|date'];
    public $dataPost = 'bkode, bnama, balamat1, balamat2, bkota, bkodepos, bnotelp, bnofax, bcatatan, baktif, binputuser, binputtgl';

    // Parameter for update data
    public $validatorUpdate = [
        'bnama'           => 'required|max:100',
        'balamat1'        => 'max:250',
        'balamat2'        => 'max:250',
        'bkota'           => 'max:25',
        'bkodepos'        => 'max:50',
        'bnotelp'         => 'max:50',
        'bnofax'          => 'max:50',
        'bcatatan'        => 'max:250',
        'baktif'          => 'required|numeric',
        'bmodifikasiuser' => 'required|numeric',
        'bmodifikasitgl'  => 'required|date'];
    public $dataUpdate = 'bnama, balamat1, balamat2, bkota, bkodepos, bnotelp, bnofax, bcatatan, baktif, bmodifikasiuser, bmodifikasitgl';

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