<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class SearchController extends Controller
{
    public $master = ''; 
    
    public function __construct() 
    {
        $this->master = new MasterController();
    }

    public function coa(Request $req, Route $route)
    {
        $table      = 'f1_coa';
        $select     = '*';
        $selectDate = 'cinputtgl, cmodifikasitgl';
        $title      = 'COA';
        $header     = 
            [
                ['cnomor', 'Nomor', 120],
                ['cnama', 'Nama', 200],
                ['cnamaalias1', 'Nama', 230],
                ['cmatauang', 'Mata Uang', -1],
            ];
        return $this->master->showSearch($req, $route, $table, $select, $selectDate, $title, $header);    
    }

    public function contact(Request $req, Route $route)
    {
        $table      = 'f1_contact';
        $select     = '*';
        $selectDate = 'kaktiftgl, ktglkontrak, ktgllahir, ktglnikah, kinputtgl, kmodifikasitgl, kcustomdate1, kcustomdate2, kcustomdate3';
        $title      = 'Kontak';
        $header     = 
            [
                ['kkode', 'Kode', 120],
                ['knama', 'Nama', 200],
                ['kkategorinama', 'Kategori', 120],
                ['k1notelp1', 'No HP', 120, 1],
                ['k1alamat1', 'Alamat', 200],
                ['k1kota', 'Kota', -1],
            ];
        return $this->master->showSearch($req, $route, $table, $select, $selectDate, $title, $header);    
    }

    public function currency(Request $req, Route $route)
    {
        $table      = 'f1_currency';
        $select     = '*';
        $selectDate = 'cinputtgl, cmodifikasitgl';
        $title      = 'Mata Uang';
        $header     = 
            [
                ['ckode', 'Kode', 120],
                ['cnama', 'Nama', -1],
            ];
        return $this->master->showSearch($req, $route, $table, $select, $selectDate, $title, $header);    
    }

    public function item(Request $req, Route $route)
    {
        $table      = 'f1_item';
        $select     = '*';
        $selectDate = '';
        $title      = 'Mata Uang';
        $header     = 
            [
                ['bkode', 'Kode Barang', 120],
                ['bnama', 'Nama Barang', 120],
                ['bstok', 'Stok', 120, 2],
                ['bstokbooking', 'Stok Booking', 120, 2],
                ['bsatuan', 'Satuan', 120],
                ['bhargajual1', 'Harga Jual', 120, 2, 'currency'],
            ];
        return $this->master->showSearch($req, $route, $table, $select, $selectDate, $title, $header);    
    }
}
