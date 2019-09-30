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

    static function UpdateDataFilter($table, $filter, $data)
    {   
        return DB::table($table)->where($filter)->update($data);
    }

    static function UpdateDataFilterRaw($table, $filter, $data)
    {   
        return DB::table($table)->whereRaw($filter)->update($data);
    }

    static function DeleteData($table, $primary_key, $value)
    {        
        return DB::table($table)->where($primary_key, $value)->delete();
    }

    static function DeleteDataFilter($table, $filter)
    {        
        return DB::table($table)->where($filter)->delete();
    }
    
    static function insertData($table, $value)
    {        
        return DB::table($table)->insert($value);
    }
    
    static function insertDataResIndex($table, $value)
    {        

        $result =  DB::table($table)->insertGetId($value);
        $response = ['success' => true, 'id' => $result];
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
        if (!$cekTipeData->success) 
        {
            $errmessage = $cekTipeData->info;goto selesai;
        }
        // else
            // $tglAwal = AsFormatTanggal($tglAwal);

        $cekTipeData = isDate($tglAkhir, 'tglAkhir');
        if (!$cekTipeData->success) 
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

    public $http = "";
    public $url = "";

    public function getData($where = '', $groupBy = '', $orderBy = '', $pageLimit = 1, $pageNumber = 20)
    {
        // Set Variabel
        $this->where = $where;
        $this->groupBy = $groupBy;
        $this->orderBy = $orderBy;
        $this->pageLimit = $pageLimit;
        $this->pageNumber = $pageNumber;

        // Deklarasi Tabel
        DB::enableQueryLog();
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
                // dd(json_decode($temp, true));
        $REQUEST_URI = '?';
        if(!isEmpty($this->where))
        {
            if(isset($this->where['id']))
                if(isset($this->where['id']['is']))
                {
                    $db = $db->where('id', $this->where['id']['is']);
                    $REQUEST_URI .= '&filter[id][is]=' . $this->where['id']['is'];
                }
            if(isset($this->where['checklist_id']))
                if(isset($this->where['checklist_id']['is']))
                {
                    $db = $db->where('checklist_id', $this->where['checklist_id']['is']);
                    $REQUEST_URI .= '&filter[checklist_id][is]=' . $this->where['checklist_id']['is'];
                }
            if(isset($this->where['created_by']))
                if(isset($this->where['created_by']['is']))
                {
                    $db = $db->where('created_by', $this->where['created_by']['is']);
                    $REQUEST_URI .= '&filter[created_by][is]=' . $this->where['created_by']['is'];
                }
            if(isset($this->where['assignee_id']))
                if(isset($this->where['assignee_id']['is']))
                {
                    $db = $db->where('assignee_id', $this->where['assignee_id']['is']);
                    $REQUEST_URI .= '&filter[assignee_id][is]=' . $this->where['assignee_id']['is'];
                }
            if(isset($this->where['due']))
                if(isset($this->where['due']['between']))
                {
                    $db = $db->whereBetween('due', explode(',', $this->where['due']['between']));
                    $REQUEST_URI .= '&filter[due][between]=' . $this->where['due']['between'];
                }

            // if($this->where->created_by)
        }
        
        // Group By
        $db = setGroupBy($db, $this->groupBy);

        // Order By
        $REQUEST_URI .= '&sort=' . $this->orderBy;
        $db = setOrderBy($db, $this->orderBy);
        
        // Paginate
        $REQUEST_URI .= '&page[limit]=' . $this->pageLimit;
        $REQUEST_URI .= '&page[offset]=' . $this->pageNumber;
        $REQUEST_URI = str_replace('?&', '?', $REQUEST_URI);
        $result = $db->paginate($this->pageLimit, [], 'page', ($this->pageNumber+$this->pageLimit)/$this->pageLimit);
        // dd(DB::getQueryLog()); // Show results of log

        $returnData = $result->toArray(); 
        if(sizeof($returnData['data']) == 0)
        {
            $res = [];
            $res['meta'] = [];
            $res['meta']['count'] = 0;
            $res['meta']['total'] = 0;
            $res['data'] = new \StdClass;
            $res['links'] = new \StdClass;
            $res['links']->first = null;
            $res['links']->last = null;
            $res['links']->next = null;
            $res['links']->prev = null;
            return $res;
        }
        $res = [];
        $res['meta'] = [];
        $res['meta']['count'] = (int)$returnData['per_page'];
        $res['meta']['total'] = $returnData['total'];

        $val = [];
        foreach($returnData['data'] as $data)
        {
            $attr = new \StdClass;
            $attr->type = $this->type;
            $attr->id = $data->id;
            unset($data->id);
            $attr->attributes = $data;
            $attr->links = new \StdClass;
            if($this->table == 'Item_attributes')
                $attr->links->self = $this->http . $this->url . $data->checklist_id;
            else
                $attr->links->self = $this->http . $this->url . $attr->id;
            $val[] = $attr;
        }

        $res['data'] = $val;


        $res['links'] = new \StdClass;
        $res['links']->first = null;
        $res['links']->last = null;
        $res['links']->next = null;
        $res['links']->prev = null;
        if(0 != $this->pageNumber)
        {
            $offsetFirst = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=0', $REQUEST_URI);
            $res['links']->first = $this->http . $this->url . $offsetFirst;
        }
        if($returnData['current_page'] < $returnData['last_page'])
        {
            $offsetLast = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=' . (((int)$returnData['last_page']-1) * (int)$this->pageLimit), $REQUEST_URI);
            $res['links']->last = $this->http . $this->url . $offsetLast;
        }
        if($returnData['next_page_url'] != null)
        {
            $next_page_url = explode('page=', $returnData['next_page_url'])[1];
            $offsetNext = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=' . (((int)$next_page_url-1) * (int)$this->pageLimit), $REQUEST_URI);
            $res['links']->next = $this->http . $this->url . $offsetNext;
        }
        if($returnData['prev_page_url'] != null)
        {
            $prev_page_url = explode('page=', $returnData['prev_page_url'])[1];
            $offsetPrev = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=' . (((int)$prev_page_url-1) * (int)$this->pageLimit), $REQUEST_URI);
            $res['links']->prev = $this->http . $this->url . $offsetPrev;
        }

        if($returnData['current_page'] > $returnData['last_page'])
        {
            $result = $db->paginate($this->pageLimit, [], 'page', ($this->pageNumber+$this->pageLimit)/$this->pageLimit);
            $returnData = $result->toArray(); 
            
            $res = [];
            $res['meta'] = [];
            $res['meta']['count'] = $returnData['per_page'];
            $res['meta']['total'] = $returnData['total'];


            $val = [];
            foreach($returnData['data'] as $data)
            {
                $attr = new \StdClass;
                $attr->type = $this->type;
                $attr->id = $data->id;
                unset($data->id);
                $attr->attributes = $data;
                $attr->links = new \StdClass;
                if($this->table == 'Item_attributes')
                    $attr->links->self = $this->http . $this->url . $data->checklist_id;
                else
                    $attr->links->self = $this->http . $this->url . $attr->id;
                $val[] = $attr;
            }

            $res['data'] = $val;

            $res['links'] = new \StdClass;
            $res['links']->first = null;
            $res['links']->last = null;
            $res['links']->next = null;
            $res['links']->prev = null;
            if(0 != $this->pageNumber)
            {
                $offsetFirst = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=0', $REQUEST_URI);
                $res['links']->first = $this->http . $this->url . $offsetFirst;
            }
            if($returnData['current_page'] < $returnData['last_page'])
            {
                $offsetLast = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=' . (((int)$returnData['last_page']-1) * (int)$this->pageLimit), $REQUEST_URI);
                $res['links']->last = $this->http . $this->url . $offsetLast;
            }
            if($returnData['next_page_url'] != null)
            {
                $next_page_url = explode('page=', $returnData['next_page_url'])[1];
                $offsetNext = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=' . (((int)$next_page_url-1) * (int)$this->pageLimit), $REQUEST_URI);
                $res['links']->next = $this->http . $this->url . $offsetNext;
            }
            if($returnData['prev_page_url'] != null)
            {
                $prev_page_url = explode('page=', $returnData['prev_page_url'])[1];
                $offsetPrev = str_replace('page[offset]=' . $this->pageNumber, 'page[offset]=' . (((int)$prev_page_url-1) * (int)$this->pageLimit), $REQUEST_URI);
                $res['links']->prev = $this->http . $this->url . $offsetPrev;
            }
        }

        
        $this->result = $res;
        return $res; 
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

        $val = [];
        $res = [];
        foreach($result as $data)
        {
            $attr = new \StdClass;
            $attr->type = $this->type;
            $attr->id = $data->id;
            unset($data->id);
            $attr->attributes = $data;
            $attr->links = new \StdClass;
            if($this->table == 'Item_attributes')
                $attr->links->self = $this->http . $this->url . $data->checklist_id;
            else
                $attr->links->self = $this->http . $this->url . $attr->id;
            $val[] = $attr;
        }

        $res['data'] = $val;

        return $res;
    }


    public function getDataById_itemDetail($primary_key, $value)
    {
        // Deklarasi Tabel
        $db = DB::table($this->table);

        // Select Data
        if(!isEmpty($this->select))
            $db = $db->select(DB::raw($this->select));

        // Filter
        $db = $db->where($primary_key, $value);
        
        $result = $db->get();

        $val = [];
        $res = [];
        foreach($result as $data)
        {
            $attr = new \StdClass;
            $attr->type = $this->type;
            $attr->id = $data->id;
            unset($data->id);
            $attr->attributes = $data;
            $attr->links = new \StdClass;
            if($this->table == 'Item_attributes')
                $attr->links->self = $this->http . $this->url . $data->checklist_id . "/items/" . $attr->id;
            else
                $attr->links->self = $this->http . $this->url . $attr->id;
            $val[] = $attr;
        }

        $res['data'] = $val;

        return $res;
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
    public $success = false;
    public $msguser = '';

    public function __construct($param) 
    {
        $this->func = $param;
    }

    function success()
    {
        $this->code = 201;
        $this->success = true;
    }
    
    function done()
    {
        if($this->success)
        {
            $res = [
                'success'    => true,
                'data'      => $this->data,
                'target'    => $this->target
            ];
        }
        else
        {
            $res = [
                'success'    => $this->success,
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
    
    function lumen()
    {
        return response()->json($this->data, $this->code);
    }
    
    function lumenDelete()
    {
        return response()->json("", 204);
    }

    function fail($message, $code = 409)
    {
        $this->msguser = $message;
        $this->code = $code;

        
        $res['meta'] = [];
        $res['meta']['count'] = 0;
        $res['meta']['total'] = 0;
        $res['data'] = $message;
        $res['links'] = new \StdClass;
        $res['links']->first = null;
        $res['links']->last = null;
        $res['links']->next = null;
        $res['links']->prev = null;
        
        return response()->json($res, $this->code);
    }

    function line($line, $command)
    {
        $this->line = $line;
        $this->desc = $command;
    }
}