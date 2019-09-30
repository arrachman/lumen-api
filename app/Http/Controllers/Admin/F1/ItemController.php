<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ItemController extends Controller
{
    // table identity
    public $tableName = 'f1_item';
    public $primaryKey = 'bid';

    // parameter for select data
    public $select = 'bid, bkode, bnama, bnamaalias1, bnamaalias2, bnamaalias3, bnamaalias4, bnamaalias5, btipe, bjenis, bjenisdetail, bkategori, bkelasproduk, bretur, btag, bketerangan, bsatuan, bnilaisatuan, bsatuandefault, bnilaisatuandefault, bhpp, bcabang, blokasi, bdivisi, bsubdivisi, bdepartemen, bsubdepartemen, bgudang, bproyek, bsubitem, bsubitemdari, bbarcode, bsuplier, baktif, baktiftgl, bstokminimal, bstokmaksimal, breorder, bminorder, bjmlorderbeli, bjmlorderjual, bkategoriumur, bstatusmoving, bsifatharga, bpromo, bpromoberlaku, bkl, bkp, bpajakbeli, bpajakjual, bhargabeli, bhppaverage, bhargajual1, bhargajual2, bhargajual3, bhargajual4, bhargajual5, bdiskonjual1, bdiskonjual2, bdiskonjual3, bdiskonjual4, bdiskonjual5, bstok, bkomisi, bmarginminimal, brekpersediaan, brekpenjualan, brekreturpenjualan, brekdiskonpenjualan, brekhargapokok, brekreturpembelian, brekdiskonpembelian, brekkonsinyasi, bapanjang, balebar, batinggi, bavolume, baberat, bawarna, baoem, bamerk, baukuran, bamodel, bakelas, bserial, bbatch, bpengganti, bgambar, burutan, bcustom1, bcustom2, bcustom3, bcustom4, bcustom5, bcustom6, bcustom7, bcustom8, bcustom9, bcustom10, bcustom11, bcustom12, bcustom13, bcustom14, bcustom15, bcatatan, binputuser, binputtgl, bmodifikasiuser, bmodifikasitgl, bedithpp, bmobile, bassembly, bdownloaded, bjmllapangan, bsatuanlapangan, bsubkelas, bmaterial, bsection, bvendor, bdesigner, basset, bhargajual6, bhargajual7, bhargajual8, bhargajual9, bhargajual10, bdiskonjual6, bdiskonjual7, bdiskonjual8, bdiskonjual9, bdiskonjual10';
    public $selectFormatDate = 'baktiftgl, bpromoberlaku, binputtgl, bmodifikasitgl';

    // Parameter for post data
    public $validatorPost = [
           'bid'   => 'required|numeric|max:20',
           'bkode'   => 'required|max:100',
           'bnama'   => 'required|max:500',
           'bnamaalias1'   => 'max:100',
           'bnamaalias2'   => 'max:100',
           'bnamaalias3'   => 'max:100',
           'bnamaalias4'   => 'max:100',
           'bnamaalias5'   => 'max:100',
           'btipe'   => 'max:500',
           'bjenis'   => 'required|max:5',
           'bjenisdetail'   => 'required|numeric|max:11',
           'bkategori'   => 'max:50',
           'bkelasproduk'   => 'required|max:25',
           'bretur'   => 'required|numeric|max:11',
           'btag'   => 'max:25',
           'bketerangan'   => 'max:255',
           'bsatuan'   => 'required|max:25',
           'bnilaisatuan'   => 'numeric',
           'bsatuandefault'   => 'required|max:25',
           'bnilaisatuandefault'   => 'numeric',
           'bhpp'   => 'required|max:2',
           'bcabang'   => 'max:25',
           'blokasi'   => 'max:25',
           'bdivisi'   => 'max:25',
           'bsubdivisi'   => 'max:25',
           'bdepartemen'   => 'max:50',
           'bsubdepartemen'   => 'max:50',
           'bgudang'   => 'max:25',
           'bproyek'   => 'max:25',
           'bsubitem'   => 'required|numeric|max:2',
           'bsubitemdari'   => 'numeric|max:20',
           'bbarcode'   => 'max:100',
           'bsuplier'   => 'numeric|max:20 unsigned',
           'baktif'   => 'required|numeric|max:2 unsigned',
           'baktiftgl'   => 'required|date',
           'bstokminimal'   => 'required|numeric',
           'bstokmaksimal'   => 'required|numeric',
           'breorder'   => 'required|numeric',
           'bminorder'   => 'required|numeric',
           'bjmlorderbeli'   => 'required|numeric',
           'bjmlorderjual'   => 'required|numeric',
           'bkategoriumur'   => 'max:25',
           'bstatusmoving'   => 'max:2',
           'bsifatharga'   => 'max:2',
           'bpromo'   => 'required|numeric|max:2',
           'bpromoberlaku'   => 'required|date',
           'bkl'   => 'required|numeric|max:4',
           'bkp'   => 'required|numeric|max:4',
           'bpajakbeli'   => 'max:25',
           'bpajakjual'   => 'max:25',
           'bhargabeli'   => 'required|numeric',
           'bhppaverage'   => 'required|numeric',
           'bhargajual1'   => 'required|numeric',
           'bhargajual2'   => 'required|numeric',
           'bhargajual3'   => 'required|numeric',
           'bhargajual4'   => 'required|numeric',
           'bhargajual5'   => 'required|numeric',
           'bdiskonjual1'   => 'required|max:25',
           'bdiskonjual2'   => 'required|max:25',
           'bdiskonjual3'   => 'required|max:25',
           'bdiskonjual4'   => 'required|max:25',
           'bdiskonjual5'   => 'required|max:25',
           'bstok'   => 'required|numeric',
           'bkomisi'   => 'required|numeric',
           'bmarginminimal'   => 'numeric',
           'brekpersediaan'   => 'required|max:15',
           'brekpenjualan'   => 'required|max:15',
           'brekreturpenjualan'   => 'required|max:15',
           'brekdiskonpenjualan'   => 'required|max:15',
           'brekhargapokok'   => 'required|max:15',
           'brekreturpembelian'   => 'required|max:15',
           'brekdiskonpembelian'   => 'required|max:15',
           'brekkonsinyasi'   => 'required|max:15',
           'bapanjang'   => 'required|numeric',
           'balebar'   => 'required|numeric',
           'batinggi'   => 'required|numeric',
           'bavolume'   => 'required|numeric',
           'baberat'   => 'required|numeric',
           'bawarna'   => 'max:25',
           'baoem'   => 'max:25',
           'bamerk'   => 'max:100',
           'baukuran'   => 'max:25',
           'bamodel'   => 'max:25',
           'bakelas'   => 'max:10',
           'bserial'   => 'required|numeric|max:2',
           'bbatch'   => 'required|numeric|max:2',
           'bpengganti'   => 'numeric|max:20',
           'bgambar'   => 'max:250',
           'burutan'   => 'numeric|max:20',
           'bcustom1'   => 'max:100',
           'bcustom2'   => 'max:100',
           'bcustom3'   => 'max:100',
           'bcustom4'   => 'max:100',
           'bcustom5'   => 'max:100',
           'bcustom6'   => 'max:100',
           'bcustom7'   => 'max:100',
           'bcustom8'   => 'max:100',
           'bcustom9'   => 'max:100',
           'bcustom10'   => 'max:100',
           'bcustom11'   => 'required|numeric|max:11',
           'bcustom12'   => 'required|numeric',
           'bcustom13'   => 'required|numeric|max:11',
           'bcustom14'   => 'required|numeric',
           'bcustom15'   => 'required|numeric',
           'bcatatan'   => 'max:250',
           'binputuser'   => 'required|numeric|max:20',
           'binputtgl'   => 'required|date',
           'bedithpp'   => 'required|numeric|max:2',
           'bmobile'   => 'required|numeric|max:2',
           'bassembly'   => 'required|numeric|max:2',
           'bdownloaded'   => 'required|numeric|max:11',
           'bjmllapangan'   => 'required|numeric',
           'bsatuanlapangan'   => 'required|max:50',
           'bsubkelas'   => 'max:250',
           'bmaterial'   => 'max:250',
           'bsection'   => 'max:250',
           'bvendor'   => 'max:250',
           'bdesigner'   => 'max:250',
           'basset'   => 'required|numeric|max:2',
           'bhargajual6'   => 'required|numeric',
           'bhargajual7'   => 'required|numeric',
           'bhargajual8'   => 'required|numeric',
           'bhargajual9'   => 'required|numeric',
           'bhargajual10'   => 'required|numeric',
           'bdiskonjual6'   => 'required|max:25',
           'bdiskonjual7'   => 'required|max:25',
           'bdiskonjual8'   => 'required|max:25',
           'bdiskonjual9'   => 'required|max:25',
           'bdiskonjual10'   => 'required|max:25'];
    public $dataPost = 'bid, bkode, bnama, bnamaalias1, bnamaalias2, bnamaalias3, bnamaalias4, bnamaalias5, btipe, bjenis, bjenisdetail, bkategori, bkelasproduk, bretur, btag, bketerangan, bsatuan, bnilaisatuan, bsatuandefault, bnilaisatuandefault, bhpp, bcabang, blokasi, bdivisi, bsubdivisi, bdepartemen, bsubdepartemen, bgudang, bproyek, bsubitem, bsubitemdari, bbarcode, bsuplier, baktif, baktiftgl, bstokminimal, bstokmaksimal, breorder, bminorder, bjmlorderbeli, bjmlorderjual, bkategoriumur, bstatusmoving, bsifatharga, bpromo, bpromoberlaku, bkl, bkp, bpajakbeli, bpajakjual, bhargabeli, bhppaverage, bhargajual1, bhargajual2, bhargajual3, bhargajual4, bhargajual5, bdiskonjual1, bdiskonjual2, bdiskonjual3, bdiskonjual4, bdiskonjual5, bstok, bkomisi, bmarginminimal, brekpersediaan, brekpenjualan, brekreturpenjualan, brekdiskonpenjualan, brekhargapokok, brekreturpembelian, brekdiskonpembelian, brekkonsinyasi, bapanjang, balebar, batinggi, bavolume, baberat, bawarna, baoem, bamerk, baukuran, bamodel, bakelas, bserial, bbatch, bpengganti, bgambar, burutan, bcustom1, bcustom2, bcustom3, bcustom4, bcustom5, bcustom6, bcustom7, bcustom8, bcustom9, bcustom10, bcustom11, bcustom12, bcustom13, bcustom14, bcustom15, bcatatan, binputuser, binputtgl, bedithpp, bmobile, bassembly, bdownloaded, bjmllapangan, bsatuanlapangan, bsubkelas, bmaterial, bsection, bvendor, bdesigner, basset, bhargajual6, bhargajual7, bhargajual8, bhargajual9, bhargajual10, bdiskonjual6, bdiskonjual7, bdiskonjual8, bdiskonjual9, bdiskonjual10';

    // Parameter for update data
    public $validatorUpdate = [
           'bkode'   => 'required|max:100',
           'bnama'   => 'required|max:500',
           'bnamaalias1'   => 'max:100',
           'bnamaalias2'   => 'max:100',
           'bnamaalias3'   => 'max:100',
           'bnamaalias4'   => 'max:100',
           'bnamaalias5'   => 'max:100',
           'btipe'   => 'max:500',
           'bjenis'   => 'required|max:5',
           'bjenisdetail'   => 'required|numeric|max:11',
           'bkategori'   => 'max:50',
           'bkelasproduk'   => 'required|max:25',
           'bretur'   => 'required|numeric|max:11',
           'btag'   => 'max:25',
           'bketerangan'   => 'max:255',
           'bsatuan'   => 'required|max:25',
           'bnilaisatuan'   => 'numeric',
           'bsatuandefault'   => 'required|max:25',
           'bnilaisatuandefault'   => 'numeric',
           'bhpp'   => 'required|max:2',
           'bcabang'   => 'max:25',
           'blokasi'   => 'max:25',
           'bdivisi'   => 'max:25',
           'bsubdivisi'   => 'max:25',
           'bdepartemen'   => 'max:50',
           'bsubdepartemen'   => 'max:50',
           'bgudang'   => 'max:25',
           'bproyek'   => 'max:25',
           'bsubitem'   => 'required|numeric|max:2',
           'bsubitemdari'   => 'numeric|max:20',
           'bbarcode'   => 'max:100',
           'bsuplier'   => 'numeric|max:20 unsigned',
           'baktif'   => 'required|numeric|max:2 unsigned',
           'baktiftgl'   => 'required|date',
           'bstokminimal'   => 'required|numeric',
           'bstokmaksimal'   => 'required|numeric',
           'breorder'   => 'required|numeric',
           'bminorder'   => 'required|numeric',
           'bjmlorderbeli'   => 'required|numeric',
           'bjmlorderjual'   => 'required|numeric',
           'bkategoriumur'   => 'max:25',
           'bstatusmoving'   => 'max:2',
           'bsifatharga'   => 'max:2',
           'bpromo'   => 'required|numeric|max:2',
           'bpromoberlaku'   => 'required|date',
           'bkl'   => 'required|numeric|max:4',
           'bkp'   => 'required|numeric|max:4',
           'bpajakbeli'   => 'max:25',
           'bpajakjual'   => 'max:25',
           'bhargabeli'   => 'required|numeric',
           'bhppaverage'   => 'required|numeric',
           'bhargajual1'   => 'required|numeric',
           'bhargajual2'   => 'required|numeric',
           'bhargajual3'   => 'required|numeric',
           'bhargajual4'   => 'required|numeric',
           'bhargajual5'   => 'required|numeric',
           'bdiskonjual1'   => 'required|max:25',
           'bdiskonjual2'   => 'required|max:25',
           'bdiskonjual3'   => 'required|max:25',
           'bdiskonjual4'   => 'required|max:25',
           'bdiskonjual5'   => 'required|max:25',
           'bstok'   => 'required|numeric',
           'bkomisi'   => 'required|numeric',
           'bmarginminimal'   => 'numeric',
           'brekpersediaan'   => 'required|max:15',
           'brekpenjualan'   => 'required|max:15',
           'brekreturpenjualan'   => 'required|max:15',
           'brekdiskonpenjualan'   => 'required|max:15',
           'brekhargapokok'   => 'required|max:15',
           'brekreturpembelian'   => 'required|max:15',
           'brekdiskonpembelian'   => 'required|max:15',
           'brekkonsinyasi'   => 'required|max:15',
           'bapanjang'   => 'required|numeric',
           'balebar'   => 'required|numeric',
           'batinggi'   => 'required|numeric',
           'bavolume'   => 'required|numeric',
           'baberat'   => 'required|numeric',
           'bawarna'   => 'max:25',
           'baoem'   => 'max:25',
           'bamerk'   => 'max:100',
           'baukuran'   => 'max:25',
           'bamodel'   => 'max:25',
           'bakelas'   => 'max:10',
           'bserial'   => 'required|numeric|max:2',
           'bbatch'   => 'required|numeric|max:2',
           'bpengganti'   => 'numeric|max:20',
           'bgambar'   => 'max:250',
           'burutan'   => 'numeric|max:20',
           'bcustom1'   => 'max:100',
           'bcustom2'   => 'max:100',
           'bcustom3'   => 'max:100',
           'bcustom4'   => 'max:100',
           'bcustom5'   => 'max:100',
           'bcustom6'   => 'max:100',
           'bcustom7'   => 'max:100',
           'bcustom8'   => 'max:100',
           'bcustom9'   => 'max:100',
           'bcustom10'   => 'max:100',
           'bcustom11'   => 'required|numeric|max:11',
           'bcustom12'   => 'required|numeric',
           'bcustom13'   => 'required|numeric|max:11',
           'bcustom14'   => 'required|numeric',
           'bcustom15'   => 'required|numeric',
           'bcatatan'   => 'max:250',
           'bmodifikasiuser'   => 'required|numeric|max:20',
           'bmodifikasitgl'   => 'required|date',
           'bedithpp'   => 'required|numeric|max:2',
           'bmobile'   => 'required|numeric|max:2',
           'bassembly'   => 'required|numeric|max:2',
           'bdownloaded'   => 'required|numeric|max:11',
           'bjmllapangan'   => 'required|numeric',
           'bsatuanlapangan'   => 'required|max:50',
           'bsubkelas'   => 'max:250',
           'bmaterial'   => 'max:250',
           'bsection'   => 'max:250',
           'bvendor'   => 'max:250',
           'bdesigner'   => 'max:250',
           'basset'   => 'required|numeric|max:2',
           'bhargajual6'   => 'required|numeric',
           'bhargajual7'   => 'required|numeric',
           'bhargajual8'   => 'required|numeric',
           'bhargajual9'   => 'required|numeric',
           'bhargajual10'   => 'required|numeric',
           'bdiskonjual6'   => 'required|max:25',
           'bdiskonjual7'   => 'required|max:25',
           'bdiskonjual8'   => 'required|max:25',
           'bdiskonjual9'   => 'required|max:25',
           'bdiskonjual10'   => 'required|max:25'];
    public $dataUpdate = 'bkode, bnama, bnamaalias1, bnamaalias2, bnamaalias3, bnamaalias4, bnamaalias5, btipe, bjenis, bjenisdetail, bkategori, bkelasproduk, bretur, btag, bketerangan, bsatuan, bnilaisatuan, bsatuandefault, bnilaisatuandefault, bhpp, bcabang, blokasi, bdivisi, bsubdivisi, bdepartemen, bsubdepartemen, bgudang, bproyek, bsubitem, bsubitemdari, bbarcode, bsuplier, baktif, baktiftgl, bstokminimal, bstokmaksimal, breorder, bminorder, bjmlorderbeli, bjmlorderjual, bkategoriumur, bstatusmoving, bsifatharga, bpromo, bpromoberlaku, bkl, bkp, bpajakbeli, bpajakjual, bhargabeli, bhppaverage, bhargajual1, bhargajual2, bhargajual3, bhargajual4, bhargajual5, bdiskonjual1, bdiskonjual2, bdiskonjual3, bdiskonjual4, bdiskonjual5, bstok, bkomisi, bmarginminimal, brekpersediaan, brekpenjualan, brekreturpenjualan, brekdiskonpenjualan, brekhargapokok, brekreturpembelian, brekdiskonpembelian, brekkonsinyasi, bapanjang, balebar, batinggi, bavolume, baberat, bawarna, baoem, bamerk, baukuran, bamodel, bakelas, bserial, bbatch, bpengganti, bgambar, burutan, bcustom1, bcustom2, bcustom3, bcustom4, bcustom5, bcustom6, bcustom7, bcustom8, bcustom9, bcustom10, bcustom11, bcustom12, bcustom13, bcustom14, bcustom15, bcatatan, bmodifikasiuser, bmodifikasitgl, bedithpp, bmobile, bassembly, bdownloaded, bjmllapangan, bsatuanlapangan, bsubkelas, bmaterial, bsection, bvendor, bdesigner, basset, bhargajual6, bhargajual7, bhargajual8, bhargajual9, bhargajual10, bdiskonjual6, bdiskonjual7, bdiskonjual8, bdiskonjual9, bdiskonjual10';

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
