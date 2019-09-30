<?php


function formatDate($data)
{
    return \Carbon\Carbon::parse($data)->format('d/m/Y H:m:s');
}

function strToArray($data)
{
    return explode(',', str_replace(' ', '', $data));
}

function setGroupBy($db, $groupBy)
{
    if(!isEmpty($groupBy))
    {
        if(gettype($groupBy) == "array")
        {
            foreach($groupBy as $field)
            {
                $db = $db->groupBy($field);
            }
        }
        else
        {
            $db = $db->groupBy($groupBy);
        }
    }

    return $db;
}

function setOrderBy($db, $orderBy)
{
    if(!isEmpty($orderBy))
    {
        $db = $db->orderByRaw(DB::raw($orderBy));
    }

    return $db;
}

function isEmpty($data)
{
    if(empty($data))
        return true;

    switch(gettype($data))
    {
        case 'NULL':return true;
        case 'object':
            if(count((array)$data) > 0)   
                return false;
            return true;
        case 'integer':
            if($data > 0)   
                return false;
            return true;
        case 'array':
            if(count($data) > 0)   
                return false;
            return true;
        case 'string': 
            if(strlen($data) > 0)
                return false;
            else            
                return true;
        default: return true;
    }
}

function notEmpty($data)
{
    return !isEmpty($data);
}

function FixQuotes($text = '')
{
    return str_replace("'", "''", $text);
}

function FixDouble($text = '')
{
    return str_replace(",", ".", $text); //Replace koma menjadi titik
}

function isContain($data = '', $cek = '')
{
    if(var_export(strrpos($data, $cek), true) == 'false')
        return false;

    if(strrpos($data, $cek) >= 0)
        return true;

    return false;
}

function isDate($param = '', $nama = 'param')
{
    $data = '{"'. $nama .'":"'. $param .'"}';
    $validator = \Validator::make((array)json_decode($data), [
        $nama              => 'required|date',
    ]);

    $res = json_decode("{}");
    $res->success = !$validator->fails();
    $res->info = ($validator->fails()) ? $validator->messages()->all()[0] : '';

    return $res;
}


//Penformatan - Format tanggal
function AsFormatTanggal($Tanggal = "", $FormatTanggal = "yyyy-MM-dd")
{
    // $StrTgl = $Tanggal.ToString($FormatTanggal);
    $DaftarBulan1 = "January~February~March~April~May~June~July~August~September~October~November~December~Jan~Feb~Mar~Apr~May~Jun~Jul~Aug~Sep~Oct~Nov~Dec";
    $DaftarBulan2 = "Januari~Februari~Maret~April~Mei~Juni~Juli~Agustus~September~Oktober~November~Desember~Jan~Feb~Mar~Apr~Mei~Jun~Jul~Agust~Sep~Okt~Nov~Des";
    $Dt1 = explode("~", $DaftarBulan1);
    $Dt2 = explode("~", $DaftarBulan2);

    for($i=0;$i<23;$i++)
        $StrTgl = str_replace($Dt1[$i], $Dt2[$i], $StrTgl);

    return $StrTgl;
}

function get_date($date = '')
{
    $date = explode('-', $date);

    $res = json_decode("{}");
    $res->year = $date[0];
    $res->month = $date[1];
    $res->day = $date[2];

    return $res;
}

function checkContains($columns, $data)
{
    $columns = str_replace(' ', '', $columns);
    $arr = explode(',', $columns);
    foreach($arr as $val)
    {
        if($val == $data)
            return true;
    }
    return false;
}


//  DataTable - Mengambil/mencari suatu nilai pada field tertentu. AsFilterDataTableX->AsDataTableDLookup
function AsDataTableDLookup($dt = '', $StrField = '', $StrFilter = '', $NilaiJikaEOF = '')
{
    //  Filter datanya dan kembalikan nilai datanya sesuai field yg diminta
    if (notEmpty($StrFilter))
    {
        //  Proses dg filter
        foreach($dt as $dc)
        {
            foreach($dc as $col => $val)
            {
                foreach($StrFilter as $arr)
                {
                    if(notEmpty($arr) && count($arr) == 2)
                        if(($col == $arr[0]) && ($val == $arr[1]))
                        {
                            return $dc->{$StrField};
                        }
                }
            }
        }
    }
    else
    {
        //  Proses tanpa filter
        if(notEmpty($dt))
            if(gettype($dt) == 'object')
            {
                if(!empty($dt->{$StrField}))
                    return $dt->{$StrField};
            }
            else
                return $dt[0]->{$StrField};
    }

    return $NilaiJikaEOF;
}

function ValidasiMatauangCOA($DtMain, $MainCurrencyField = '', $MainArrayField = '', $DtDetail, $DetailArrayField = '', $PemisahArray = "~", $MainArrayFieldMessage = "",  $DetailArrayFieldMessage = "", $DetailUrutanField = "urutan")
{
    $ErrMessage = ""; $DtCoa = new \StdClass; $DtValidasi = new \StdClass;
    $Filter = ""; $Sql = ""; $CurrField = ""; $CurrFieldMessage = ""; $Norek = "";
    $valNorek = ""; $valNama = ""; $valMatauang = ""; $valUrutan = "";

    //  'SET FIELD UTAMA ===================================================
    $vMain = explode($PemisahArray, $MainArrayField);

    //  'SET FIELD MESSAGE UTAMA
    if (notEmpty($MainArrayFieldMessage))
        $vMainMessage = explode($PemisahArray, $MainArrayFieldMessage);
    else
        $vMainMessage = $vMain;

    //  'VALIDASI JML FIELD UTAMA DAN FIELD MESSAGE UTAMA
    if (count($vMain) != count($vMainMessage))
    {
        $ErrMessage = "Invalid MainArrayFieldMessage."; goto selesai;
    }    
    //  'END OF SET FIELD UTAMA ============================================

    //  'SET FIELD DETAIL ==================================================
    $vDetail = explode($PemisahArray, $DetailArrayField);

    //  'SET FIELD MESSAGE DETAIL
    if (strlen($DetailArrayFieldMessage) != 0)
        $vDetailMessage = explode($PemisahArray, $DetailArrayFieldMessage);
    else
        $vDetailMessage = $vDetail;

    //  'VALIDASI JML FIELD DETAIL DAN FIELD MESSAGE DETAIL
    if (count($vDetail) != count($vDetailMessage))
    {
        $ErrMessage = "Invalid DetailArrayFieldMessage."; goto selesai;
    }
    //  'END OF SET FIELD DETAIL ===========================================


    //  'AMBIL MATAUANG FUNGSIONAL =========================================
    $dtMatauang = \DB::select("SELECT skode, snilai FROM f0_setting WHERE smodule = 0 AND sgrup = 'accounting' AND (skode = 'MataUangFungsional' OR skode = 'Kurs')");
    $uangFungsional = AsDataTableDLookup($dtMatauang, "snilai", [['skode', 'MataUangFungsional']], "Not found");
    if ($uangFungsional == "Not found")
    {
        $ErrMessage = "Setting Functional Currency not found."; goto selesai;
    }

    $kursFungsional = AsDataTableDLookup($dtMatauang, "snilai", [['skode', 'Kurs']], "Not found");
    if ($kursFungsional == "Not found")
    {
        $ErrMessage = "Setting Exchange Rate Functional Currency not found."; goto selesai;
    }
    //  'END OF AMBIL MATAUANG FUNGSIONAL ==================================


    //  'VALIDASI MATAUANG COA =============================================
    if (notEmpty($DtMain))
    {
        //  'SET MATAUANG UTAMA
        $uangUtama = AsDataTableDLookup($DtMain, $MainCurrencyField, "", "Not Found");

        // 'VALIDASI DATA UTAMA ----------------------------------
        if (notEmpty($MainArrayField) && notEmpty($vMain))
        {
            // 'PERULANGAN SEBANYAK FIELD UTAMA
            foreach($vMain as $val)
            {
                // 'SET NOREK
                $Norek = $DtMain->{$val};

                // 'SET FILTER COA
                $Filter = (isEmpty($Filter))? "": $Filter . " OR ";
                $Filter .= " cnomor = '" . $Norek . "' ";

                // 'VALIDASI KE DATABASE (f1_coa)
                // 'AMBIL NOREK YANG MEMILIKI MATAUANG <> MATAUANG FUNGSIONAL DAN <> MATAUANG UTAMA
                $Sql = "SELECT cnomor, cnama, cmatauang FROM f1_coa ";
                $Sql .= " WHERE cmatauang <> '" . $uangFungsional . "' AND cmatauang <> '" . $uangUtama . "' ";
                $Sql .= " AND (" . $Filter . ") ";
                $DtCoa = \DB::select($Sql);

                // 'JIKA TERDAPAT DATA, MAKA TAMPILKAN ALERT
                if (notEmpty($DtCoa))
                {
                    // 'AMBIL NOREK, NAMA DAN MATAUANG DARI f1_coa
                    $valNorek = $DtCoa[0]->cnomor;
                    $valNama = $DtCoa[0]->cnama;
                    $valMatauang = $DtCoa[0]->cmatauang;
                    // 'ErrMessage = "Main Transaction : Invalid COA Currency for column " . CurrFieldMessage . " on " . valNorek . " - " . valNama . " (" . valMatauang . ")." : GoTo selesai
                    $ErrMessage = "Main Transaction : Invalid COA Currency on " . $valNorek . " - " . $valNama . " (" . $valMatauang . ")."; goto selesai;
                }

                // 'CLEAR FILTER
                $Filter = "";
            }
        }
        // 'END OF VALIDASI DATA UTAMA ---------------------------

        // 'VALIDASI DATA DETAIL ---------------------------------
        if (notEmpty($DtDetail) && notEmpty($DetailArrayField) && notEmpty($vDetail))
        {
            // 'PERULANGAN SEBANYAK FIELD DETAIL
            foreach($vDetail as $i => $val)
            {
                // 'SET FIELD DAN FIELD MESSAGE
                $CurrField = $val; $CurrFieldMessage = $vDetailMessage[$i];

                // 'PERULANGAN SEBANYAK ROW DATA DETAIL
                foreach($DtDetail as $dr)
                {
                    // 'SET NOREK
                    $Norek = $dr->{$CurrField};

                    // 'SET FILTER COA
                    $Filter = (isEmpty($Filter))? "": $Filter . " OR ";
                    $Filter .= " cnomor = '" . $Norek . "' ";
                }

                // 'VALIDASI KE DATABASE (f1_coa)
                // 'AMBIL NOREK YANG MEMILIKI MATAUANG <> MATAUANG FUNGSIONAL DAN <> MATAUANG UTAMA
                $Sql = "SELECT cnomor, cnama, cmatauang FROM f1_coa ";
                $Sql .= " WHERE cmatauang <> '" . $uangFungsional . "' AND cmatauang <> '" . $uangUtama . "' ";
                $Sql .= " AND (" . $Filter . ") ";
                $DtCoa = \DB::select($Sql);

                // 'JIKA TERDAPAT DATA, MAKA TAMPILKAN ALERT
                if (notEmpty($DtCoa))
                {
                    // 'AMBIL NOREK, NAMA DAN MATAUANG DARI f1_coa
                    $valNorek = $DtCoa[0]->cnomor;
                    $valNama = $DtCoa[0]->cnama;
                    $valMatauang = $DtCoa[0]->cmatauang;
                    // 'AMBIL URUTAN DARI DATA DETAIL
                    $valUrutan = AsDataTableDLookup($DtDetail, $DetailUrutanField, [[$CurrField, $valNorek]]);
                    // 'ErrMessage = "Detail Row - " . valUrutan . " : Invalid COA Currency for column " . CurrFieldMessage . " on " . valNorek . " - " . valNama . " (" . valMatauang . ")." : GoTo selesai
                    $ErrMessage = "Detail Row - " . $valUrutan . " : Invalid COA Currency on " . $valNorek . " - " . $valNama . " (" . $valMatauang . ").";
                }

                // 'CLEAR Variabel
                $Filter = "";
            }

        }
        // 'END OF VALIDASI DATA DETAIL --------------------------
    }
    else
    {
        $ErrMessage = "Main transaction not found."; goto selesai;
    }
    //  'END OF VALIDASI MATAUANG COA ======================================

selesai:
    return $ErrMessage;
}


function M0_DeleteNotransaksi($cabang, $lokasi, $kodetabel, $tgl, $notransaksi, $sumber = "", $smodule = 0, $matauang = "")
{
    $mukodenotransaksi = "";
    $awalan = ""; $withLokasi = "1";
    $sqlambil = ""; $sql = "";
    $success = 0; $jmldigit = 0; $noberikutnya = 0;
    $errmessage = ""; $rsSetting = "";
    $sgrup = ($smodule == 0) ? "accounting" : "options";

    //  AMBIL SETTING, PAKAI CABANG ATAU TIDAK
    $rsSetting = F_getSetting($smodule, $sgrup, $sumber . "NoTransactionCabang");
    $withCabang = (notEmpty($rsSetting)) ? $rsSetting : 1;
    if ($withCabang != 1) $cabang = "--";

    // 'AMBIL SETTING, PAKAI LOKASI ATAU TIDAK
    $rsSetting = F_getSetting($smodule, $sgrup, $sumber . "NoTransactionLokasi");
    $withLokasi = (notEmpty($rsSetting)) ? $rsSetting : 1;
    if ($withLokasi != 1) $lokasi = "--";

    // 'AMBIL SETTING, PAKAI BULAN ATAU TIDAK
    $rsSetting = F_getSetting($smodule, $sgrup, $sumber . "NoTransactionPeriode");
    $resetBulan = (notEmpty($rsSetting)) ? $rsSetting : 1;

    if ($withLokasi == 1)
    {
        // 'AMBIL KODE TRANSAKSI LOKASI
        $sqlambil = "SELECT lkodetransaksi FROM f1_location WHERE lkode = '" . $lokasi . "'";
        $dt = DB::select($sqlambil);
        if (notEmpty($dt))
            $lokasi = $dt[0]->lkodetransaksi;
        else
        {
            $errmessage = "Could not find Transaction Code for '" . $lokasi . "' location."; goto selesai;
        }
    }

    // 'AMBIL KODE NO TRANSAKSI MATAUANG
    $sqlambil = "SELECT c.ckodenotransaksi FROM f1_currency c WHERE c.ckode = '" . $matauang . "'";
    $dt = DB::select($sqlambil);
    if (notEmpty($dt))
        $mukodenotransaksi = $dt[0]->ckodenotransaksi;

    // 'FORMAT TGL
    if ($resetBulan != 1)
    {
        $getdate = get_date($tgl);
        $tgl = $getdate->year . '-01-' . $getdate->day;
    }

    // 'AMBIL NOMORBERIKUTNYA DARI F0_NOMOR_NEXT BERDASARKAN :
    // 'KODETABEL, LOKASI, TAHUN DAN BULAN TRANSAKSI
    $sqlambil = "  SELECT noberikutnya FROM f0_nomor_next";
    $sqlambil .= " WHERE kodetabel = '" . $kodetabel . $mukodenotransaksi . "'";
    $sqlambil .= " AND lokasi = '" . $lokasi . "'";
    $sqlambil .= " AND cabang = '" . $cabang . "'";
    $sqlambil .= " AND tahun = RIGHT(YEAR('" . $tgl . "'), 2)";
    $sqlambil .= " AND bulan = MONTH('" . $tgl . "')";
    $dt = DB::select($sqlambil);
    if (notEmpty($dt))
        $noberikutnya = (int)$dt[0]->noberikutnya;

    // 'AMBIL JMLDIGIT DARI F0_NOMOR BERDASARKAN KODETABEL
    $sqlambil = "  SELECT jmldigit FROM f0_nomor";
    $sqlambil .= " WHERE kodetabel = '" . $kodetabel . "'";
    $dt = DB::select($sqlambil);
    if (notEmpty($dt))
        $jmldigit = (int)$dt[0]->jmldigit;

    // 'JIKA URUTAN NO.TRANSAKSI = NOMORBERIKUTNYA - 1 MAKA UPDATE F0_NOMOR_NEXT
    // if ( strlen($notransaksi)-$jmldigit == strlen($noberikutnya) - 1)
    // {
        $sql = "  UPDATE f0_nomor_next SET noberikutnya = noberikutnya - 1";
        $sql .= " WHERE kodetabel = '" . $kodetabel . $mukodenotransaksi . "'";
        $sql .= " AND lokasi = '" . $lokasi . "'";
        $sql .= " AND cabang = '" . $cabang . "'";
        $sql .= " AND tahun = RIGHT(YEAR('" . $tgl . "'), 2)";
        $sql .= " AND bulan = MONTH('" . $tgl . "')";
    // }

    $success = 1;

selesai:
    $res = new \StdClass;
    $res->success = $success;
    $res->errmessage = $errmessage;
    $res->notransaksi = $notransaksi;
    $res->sql = $sql;
    return $res;
}

// FUNGSI UNTUK VALIDASI AKUN WAJIB COSTCENTER ATAU TIDAK
function ValidasiCoaRequiredCostCenter($strFilter = '', $dtdetail = '')
{
    $hasil = "";

    // 'CEK COA WAJIB COST CENTER ==============================
    $dtCekCC = \DB::select("SELECT cnomor, cnama FROM f1_coa WHERE ccostcenter = 1 AND (" . $strFilter . ")");
    if (notEmpty($dtCekCC))
        foreach($dtCekCC as $dr1)
        {
            $dtDetailCC = AsDataTableDLookup($dtdetail, "urutan", [['norek', $dr1->cnomor], ['costcenter', '']], "Not found");
            if (notEmpty($dtDetailCC))
                $hasil = "Row " . $dtDetailCC . " : " . $dr1->cnomor . " " . $dr1->cnama . " - cost center can't be empty."; goto selesai;
        }
    
    // 'END OF CEK COA WAJIB COST CENTER =======================

selesai:
    return $hasil;
}


//  DataTable - Menghitung jml nilai (sum) pada suatu datatable
function AsDataTableDSum($dt, $StrField)
{
    $sum = 0;
    foreach($dt as $val)
        if(!empty($val->{$StrField}))
            $sum += $val->{$StrField}; 

    return $sum;
}

// 'FUNGSI UNTUK AMBIL SETTING
function F_getSetting($sModule, $sGrup, $sKode)
{
    $sql = "SELECT snilai FROM f0_setting WHERE smodule = '" . $sModule . "' AND sgrup = '" . $sGrup . "' AND skode = '" . $sKode . "'";
    $dtSetting = DB::select($sql);
    if(notEmpty($dtSetting))
        if (notEmpty($dtSetting[0]->snilai))
            return $dtSetting[0]->snilai;

    return '';
}

function insertHistory($table, $primaryKey, $primaryKeyDetail, $notransaksi, $idtransaksi)
{
    DB::insert("INSERT INTO {$table}_history(SELECT 0, fix.* FROM {$table} fix WHERE fix.{$primaryKey} = $idtransaksi)");
    DB::insert("INSERT INTO {$table}_detail_history (SELECT 0, '" . DB::getPdo()->lastInsertId() . "', fix.* FROM {$table}_detail fix WHERE fix.{$primaryKeyDetail} = $idtransaksi )");
}

// Route
function routeGetSearch($source)
{
    return Route::get('search/' . $source, "SearchController@" . $source);
}

function routeGet($source, $param)
{
    return Route::get(strtolower($source), $source . "Controller@" . $param);
}

function routePost($source, $param)
{
    return Route::post(strtolower($source) . '/{post}', $source . "Controller@" . $param);
}

function routePut($source, $param)
{
    return Route::put(strtolower($source) . '/{post}', $source . "Controller@" . $param);
}

function routeGetById($source, $param)
{
    return Route::get(strtolower($source) . '/' . $param . '/{post}', $source . "Controller@" . $param);
}

function routeDelete($source, $param)
{
    return Route::delete(strtolower($source) . '/{post}', $source . "Controller@" . $param);
}

function routeGetDelete($source, $param)
{
    return Route::get(strtolower($source) . '/{post}', $source . "Controller@" . $param);
}