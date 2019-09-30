<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ClassController extends Controller
{
    // table identity
    public $tableName = 'f1_class';
    public $primaryKey = 'ckode';

    // parameter for select data
    public $select = 'ckode, cnama, ccatatan, caktif, cinputuser, cinputtgl, cmodifikasiuser, cmodifikasitgl, cindexbarcode';
    public $selectFormatDate = 'cinputtgl, cmodifikasitgl, ccustomdate1, ccustomdate2, ccustomdate3';

    // Parameter for post data
    public $validatorPost = [
           'ckode'   => 'required|max:25',
           'cnama'   => 'required|max:100',
           'ccatatan'   => 'max:250',
           'caktif'   => 'required|numeric|min:-127|max:127',
           'cinputuser'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'cinputtgl'   => 'required|date',
           'ccustomtext1'   => 'max:250',
           'ccustomtext2'   => 'max:250',
           'ccustomtext3'   => 'max:250',
           'ccustomtext4'   => 'max:250',
           'ccustomtext5'   => 'max:250',
           'ccustomint1'   => 'required|numeric|min:-2147483647|max:2147483647',
           'ccustomint2'   => 'required|numeric|min:-2147483647|max:2147483647',
           'ccustomint3'   => 'required|numeric|min:-2147483647|max:2147483647',
           'ccustomdbl1'   => 'required|numeric',
           'ccustomdbl2'   => 'required|numeric',
           'ccustomdbl3'   => 'required|numeric',
           'ccustomdate1'   => 'required|date',
           'ccustomdate2'   => 'required|date',
           'ccustomdate3'   => 'required|date',
           'cindexbarcode'   => 'required|max:250'];
    public $dataPost = 'ckode, cnama, ccatatan, caktif, cinputuser, cinputtgl, ccustomtext1, ccustomtext2, ccustomtext3, ccustomtext4, ccustomtext5, ccustomint1, ccustomint2, ccustomint3, ccustomdbl1, ccustomdbl2, ccustomdbl3, ccustomdate1, ccustomdate2, ccustomdate3, cindexbarcode';

    // Parameter for update data
    public $validatorUpdate = [
           'cnama'   => 'required|max:100',
           'ccatatan'   => 'max:250',
           'caktif'   => 'required|numeric|min:-127|max:127',
           'cmodifikasiuser'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'cmodifikasitgl'   => 'required|date',
           'ccustomtext1'   => 'max:250',
           'ccustomtext2'   => 'max:250',
           'ccustomtext3'   => 'max:250',
           'ccustomtext4'   => 'max:250',
           'ccustomtext5'   => 'max:250',
           'ccustomint1'   => 'required|numeric|min:-2147483647|max:2147483647',
           'ccustomint2'   => 'required|numeric|min:-2147483647|max:2147483647',
           'ccustomint3'   => 'required|numeric|min:-2147483647|max:2147483647',
           'ccustomdbl1'   => 'required|numeric',
           'ccustomdbl2'   => 'required|numeric',
           'ccustomdbl3'   => 'required|numeric',
           'ccustomdate1'   => 'required|date',
           'ccustomdate2'   => 'required|date',
           'ccustomdate3'   => 'required|date',
           'cindexbarcode'   => 'required|max:250'];
    public $dataUpdate = 'cnama, ccatatan, caktif, cmodifikasiuser, cmodifikasitgl, ccustomtext1, ccustomtext2, ccustomtext3, ccustomtext4, ccustomtext5, ccustomint1, ccustomint2, ccustomint3, ccustomdbl1, ccustomdbl2, ccustomdbl3, ccustomdate1, ccustomdate2, ccustomdate3, cindexbarcode';

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
