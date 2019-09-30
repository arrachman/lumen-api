<?php
namespace App\Helpers;

use DB;
use Validator;

class Global_help  
{
    static function validator($reqData, $data)
    {
        $validator = Validator::make($reqData, $data);

        if ($validator->fails()) 
            return [false, $validator->messages()->all(), 409];
        
        return [true];
    }

    static function checkDataById($table, $primary_key, $value)
    {        
        return DB::table($table)->where($primary_key, $value)->first();
    }

    static function UpdateData($table, $primary_key, $value, $data)
    {   
        return DB::table($table)->where($primary_key, $value)->update($data);
    }

    static function DeleteData($table, $primary_key, $value)
    {        
        return DB::table($table)->where($primary_key, $value)->delete();
    }
    
    static function insertData($table, $value)
    {        
        return DB::table($table)->insert($value);
    }
    
    static function insertDataResIndex($table, $value)
    {        
        $result =  DB::table($table)->insertGetId($value);
        $response = ['succes' => true, 'id' => $result];
        return $response;
    }

    static function validatorGet($data)
    {
        $validator = Validator::make((array)json_decode($data, false), [
            'target'              => 'required',
            'pageNumber'          => 'required|numeric|min:1',
            'pageLimit'           => 'required|numeric|min:1|max:50',
        ]);

        if ($validator->fails()) 
            return [false, $validator->messages()->all(), 409];
        
        return [true];
    }
    
    static function hakAkses($ModuleId = 2, $MenuId = 1, $IndeksAkses = 0, $UserId = 1)
    {    
        $arrNamaAkses = ["Insert", "Update/Draft", "Delete", "GetData", "Approved1", "Approved2", "Approved3", "Approved4", "Approved", "Close/Unclose", "Journal", "History", "Setting Grid"];
    
        //CEK HAK AKSES =======================================
        $sql = "SELECT ur.userid, ur.role, rm.rmmoduleid, rm.rmmenuid, rm.rmrole, rm.rmakses, rm.rmfavourite FROM f0_user_role ur JOIN f0_role_menu rm ON ur.role = rm.rmrole WHERE ur.userid = '" . FixDouble($UserId) . "' AND rm.rmmoduleid = '" . FixDouble($ModuleId) . "' AND rmmenuid = '" . FixDouble($MenuId) . "'";
        $dt =  DB::select($sql);
        
        if(count($dt) > 0)
        {
            // JIKA AKSES <> 1 MAKA ALERT TIDAK MEMPUNYAI HAK AKSES
            if(substr($dt[0]->rmakses, $IndeksAkses-1, $IndeksAkses) == '0')
                return "This role doesn't have permission to '" . $arrNamaAkses[$IndeksAkses] . "' this menu.";
        }
        else
        {
            return "This role doesn't have permission to '" . $arrNamaAkses[$IndeksAkses] . "' this menu.";
        }

        return '';
    }

    
    static function M2_Accounting_PeriodeCheck($tglAwal = '', $tglAkhir = '')
    {
        $success = 0; $errmessage = ""; $filter = "";
          
        //  CEK TIPE DATA =============================================
        $cekTipeData = isDate($tglAwal, 'tglAwal');
        if (!$cekTipeData->succes) 
        {
            $errmessage = $cekTipeData->info;goto selesai;
        }
        // else
            // $tglAwal = AsFormatTanggal($tglAwal);

        $cekTipeData = isDate($tglAkhir, 'tglAkhir');
        if (!$cekTipeData->succes) 
        {
            $errmessage = $cekTipeData->info;goto selesai;
        }
//         Else
//             tglAkhir = AsFormatTanggal(tglAkhir)
//         End If
        //  END OF CEK TIPE DATA ======================================

        //  BUAT FILTER ===============================================
        $awal = get_date($tglAwal);
        $akhir = get_date($tglAkhir);
        //  jika tahun berbeda
        if ($awal->year != $akhir->year) 
        {
            $filter = "((aptahun = '" . $awal->year . "' AND apbulan >= '" . $awal->month . "') or (aptahun > '" . $awal->year . "' AND aptahun < '" . $akhir->year . "') or (aptahun = '" . $akhir->year . "' AND apbulan <= '" . $akhir->month . "'))";
            //  jika tahun sama
        }        
        elseif ($awal->year == $akhir->year) 
        {
            //  jika bulan sama
            if ($awal->month == $akhir->month) 
                $filter = "((aptahun = '" . $awal->year . "') AND (apbulan = '" . $awal->month . "'))";
            //  jika bulan beda
            else
                $filter = "((aptahun = '" . $awal->year . "') AND (apbulan BETWEEN '" . $awal->month . "' AND '" . $akhir->month . "'))";
        }
        //  END OF BUAT FILTER ========================================


        // 'CEK PERIODE AKUNTANSI SUDAH TUTUP/BELUM
        $dt = DB::select("SELECT aptahun, apbulan FROM f2_accounting_period WHERE " . $filter . " AND aptutupperiode = '1'");
        if (count($dt) > 0) 
        {
            $success = 0; 
            $errmessage = "Accounting Periode : Year = '" . $dt[0][0] . "', Month = '" . $dt[0][1] . "' has closed." ; 
            goto selesai;
        }

        $success = 1;
        selesai:
        return json_decode('{"success":"'. $success .'", "errmessage":"' . $errmessage . '"}');
    }
}

class ModelsDB  
{
    public $table = '';
    public $select = '*';
    public $selectFormatDate = '';
    public $where = '';
    public $groupBy = '';
    public $orderBy = '';
    public $pageLimit = '';
    public $pageNumber = '';
    public $arrJoin = [];
    public $arrLeftJoin = [];
    public $result = '';

    public function __construct($table = '')
    {
        $this->table = $table;
    }
    
    public function join($tableJoin = '', $column = '', $operator = '', $value = '')
    {
        array_push($this->arrJoin, [$tableJoin, $column, $operator, $value]);
    }
    
    public function leftJoin($tableJoin = '', $column = '', $operator = '', $value = '')
    {
        array_push($this->arrLeftJoin, [$tableJoin, $column, $operator, $value]);
    }
    
    public function select($data = '')
    {
        $this->select = $data;
    }
    
    public function selectFormatDate($data = '')
    {
        $this->selectFormatDate = $data;
    }

    public function getData($where = '', $groupBy = '', $orderBy = '', $pageLimit = 1, $pageNumber = 20)
    {
        // Set Variabel
        $this->where = $where;
        $this->groupBy = $groupBy;
        $this->orderBy = $orderBy;
        $this->pageLimit = $pageLimit;
        $this->pageNumber = $pageNumber;

        // Deklarasi Tabel
        $db = DB::table($this->table);

        foreach($this->arrJoin as $val)
        {
            $db = $db->join($val[0], $val[1], $val[2], $val[3]);
        }

        foreach($this->arrLeftJoin as $val)
        {
            $db = $db->leftJoin($val[0], $val[1], $val[2], $val[3]);
        }

        // Select Data
        if(!isEmpty($this->select))
        {
            $db = $db->select(DB::raw($this->select));
        }

        // Filter
        if(!isEmpty($this->where))
            $db = $db->whereRaw($this->where);
        
        // Group By
        $db = setGroupBy($db, $this->groupBy);

        // Order By
        $db = setOrderBy($db, $this->orderBy);
        
        // Paginate
        $result = $db->paginate($this->pageLimit, ['*'], 'page', $this->pageNumber);
        $returnData = $result->toArray(); 
        if($returnData['current_page'] > $returnData['last_page'])
        {
            $result = $db->paginate($this->pageLimit, ['*'], 'page', $returnData['last_page']);
            $returnData = $result->toArray(); 
        }

        // Manipulasi Format Date 
        if($this->selectFormatDate != '')
        {
            $formatDate = str_replace(' ', '', $this->selectFormatDate);
            $formatDate = explode(',', $formatDate);

            foreach($returnData['data'] as $field)
                foreach($formatDate as $format)
                    if(!empty($field->{$format}))
                        $field->{$format} = formatDate($field->{$format});
        }
        
        $this->result = $returnData;
        return $returnData; 
    }

    public function getDataById($primary_key, $value)
    {
        // Deklarasi Tabel
        $db = DB::table($this->table);

        // Select Data
        if(!isEmpty($this->select))
            $db = $db->select(DB::raw($this->select));

        // Filter
        $db = $db->where($primary_key, $value);
        
        $result = $db->get();

        // Manipulasi Format Date 
        if(!isEmpty($this->selectFormatDate))
        {
            $formatDate = str_replace(' ', '', $this->selectFormatDate);
            $formatDate = explode(',', $formatDate);
    
            foreach($result as $field)
                foreach($formatDate as $format)
                    $field->{$format} = formatDate($field->{$format});
        }
        return $result;
    }


}

class Helpfix  
{
    public $data = '';
    public $target = '';
    public $line = 0;
    public $source = 'Area';
    public $func = 'index';
    public $desc = '';
    public $code = 404;
    public $succes = false;
    public $msguser = '';

    public function __construct($param) 
    {
        $this->func = $param;
    }

    function succes()
    {
        $this->code = 201;
        $this->succes = true;
    }
    
    function done()
    {
        if($this->succes)
        {
            $res = [
                'succes'    => true,
                'data'      => $this->data,
                'target'    => $this->target
            ];
        }
        else
        {
            $res = [
                'succes'    => $this->succes,
                'data'      => $this->data,
                'target'    => $this->target,
                'code'      => $this->code,
                'msguser'   => $this->msguser,
                'msgdev'    => [
                    'line'      => $this->line,
                    'function'  => $this->func,
                    'desc'      => $this->desc,
                ],
            ];
        }

        return response()->json($res, $this->code);
    }

    function fail($message, $code = 409)
    {
        $this->msguser = $message;
        $this->code = $code;

        return $this->done();
    }

    function line($line, $command)
    {
        $this->line = $line;
        $this->desc = $command;
    }
}