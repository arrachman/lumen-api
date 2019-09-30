<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class BankController extends Controller
{
    // table identity
    public $tableName = 'f1_bank';
    public $primaryKey = 'bkode';

    // parameter for select data
    public $select = 'bkode, bnama, balamat, bkota, bnotelp, bnofax, bcatatan, baktif, binputuser, binputtgl, bmodifikasiuser, bmodifikasitgl';
    public $selectFormatDate = 'binputtgl, bmodifikasitgl, bcustomdate1, bcustomdate2, bcustomdate3';

    // Parameter for post data
    public $validatorPost = [
           'bkode'   => 'required|max:25',
           'bnama'   => 'required|max:100',
           'balamat'   => 'max:250',
           'bkota'   => 'max:25',
           'bnotelp'   => 'max:50',
           'bnofax'   => 'max:50',
           'bcatatan'   => 'max:250',
           'baktif'   => 'required|numeric|max:2',
           'binputuser'   => 'required|numeric|max:20',
           'binputtgl'   => 'required|date',
           'bcustomtext1'   => 'max:250',
           'bcustomtext2'   => 'max:250',
           'bcustomtext3'   => 'max:250',
           'bcustomtext4'   => 'max:250',
           'bcustomtext5'   => 'max:250',
           'bcustomint1'   => 'required|numeric|max:11',
           'bcustomint2'   => 'required|numeric|max:11',
           'bcustomint3'   => 'required|numeric|max:11',
           'bcustomdbl1'   => 'required|numeric',
           'bcustomdbl2'   => 'required|numeric',
           'bcustomdbl3'   => 'required|numeric',
           'bcustomdate1'   => 'required|date',
           'bcustomdate2'   => 'required|date',
           'bcustomdate3'   => 'required|date'];
    public $dataPost = 'bkode, bnama, balamat, bkota, bnotelp, bnofax, bcatatan, baktif, binputuser, binputtgl, bcustomtext1, bcustomtext2, bcustomtext3, bcustomtext4, bcustomtext5, bcustomint1, bcustomint2, bcustomint3, bcustomdbl1, bcustomdbl2, bcustomdbl3, bcustomdate1, bcustomdate2, bcustomdate3';

    // Parameter for update data
    public $validatorUpdate = [
           'bnama'   => 'required|max:100',
           'balamat'   => 'max:250',
           'bkota'   => 'max:25',
           'bnotelp'   => 'max:50',
           'bnofax'   => 'max:50',
           'bcatatan'   => 'max:250',
           'baktif'   => 'required|numeric|max:2',
           'bmodifikasiuser'   => 'required|numeric|max:20',
           'bmodifikasitgl'   => 'required|date',
           'bcustomtext1'   => 'max:250',
           'bcustomtext2'   => 'max:250',
           'bcustomtext3'   => 'max:250',
           'bcustomtext4'   => 'max:250',
           'bcustomtext5'   => 'max:250',
           'bcustomint1'   => 'required|numeric|max:11',
           'bcustomint2'   => 'required|numeric|max:11',
           'bcustomint3'   => 'required|numeric|max:11',
           'bcustomdbl1'   => 'required|numeric',
           'bcustomdbl2'   => 'required|numeric',
           'bcustomdbl3'   => 'required|numeric',
           'bcustomdate1'   => 'required|date',
           'bcustomdate2'   => 'required|date',
           'bcustomdate3'   => 'required|date'];
    public $dataUpdate = 'bnama, balamat, bkota, bnotelp, bnofax, bcatatan, baktif, bmodifikasiuser, bmodifikasitgl, bcustomtext1, bcustomtext2, bcustomtext3, bcustomtext4, bcustomtext5, bcustomint1, bcustomint2, bcustomint3, bcustomdbl1, bcustomdbl2, bcustomdbl3, bcustomdate1, bcustomdate2, bcustomdate3';

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
