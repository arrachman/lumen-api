<?php

namespace App\Http\Controllers\Admin\F0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use DB;
use StdClass;

class GeneratorController extends Controller
{
    
    // GENERATOR API TRANSACTION LARAVEL
    public function ApiCrudTransaction(Request $req, Route $route)
    {
        $sourceTransaction = file_get_contents('sourceTransaction.txt');

        $desc = DB::select("describe $req->table");
        $descDetail = DB::select("describe {$req->table}_detail");

        $CONTROLLER_NAME = ucfirst(str_replace('f'.$req->noFix.'_', "", $req->table));
        $SUMBER = explode("_", $req->table)[1];
        $PRIMARY_KEY_UTAMA = '';
        $PRIMARY_KEY_DETAIL = '';
        $SELECT_DATA = '';
        $SELECT_DATA_FORMAT_DATE = '';
        $VALIDATOR_UTAMA = '';
        $VALIDATOR_DETAIL = '';
        $DATA_POST = '';
        $VALIDATOR_UPDATE = '';
        $DATA_UPDATE = '';

        foreach($desc as $field)
        {
            if($field->Key == 'PRI')
            {
                $PRIMARY_KEY_UTAMA = $field->Field;
            } 
            
            if(!$this->isCustom($field->Field))
                $SELECT_DATA .= $field->Field . ", ";

            
            if($field->Type == 'timestamp' || $field->Type == 'date')
            {
                $SELECT_DATA_FORMAT_DATE .= $field->Field . ", ";
            } 
            
            
            $validator = $this->setValidator($field->Type, $field->Null, (!isContain($field->Type, 'unsigned')) ? true : false);
            
            if($validator != '')
                $VALIDATOR_UTAMA .= "           '$field->Field'   => '" . $validator . "',\n";

            if(!isContain($field->Field, 'modifikasiuser') && !isContain($field->Field, 'modifikasitgl'))
            {
                $DATA_POST .= "$field->Field, ";
            }
            
            if(!isContain($field->Field, 'inputuser') && !isContain($field->Field, 'inputtgl') && ($field->Key != 'PRI'))
            {
                if($validator != '')
                    $VALIDATOR_UPDATE .= "           '$field->Field'   => '" . $validator . "',\n";

                $DATA_UPDATE .= "$field->Field, ";
            }

        }

        
        foreach($descDetail as $field)
        {
            if($field->Key == 'PRI')
            {
                $PRIMARY_KEY_DETAIL = $field->Field;
            } 

            $validator = $this->setValidator($field->Type, $field->Null, (!isContain($field->Type, 'unsigned')) ? true : false);
            
            if($validator != '')
                $VALIDATOR_DETAIL .= "              '$field->Field'   => '" . $validator . "',\n";
        }

        $SELECT_DATA = substr($SELECT_DATA, 0, strlen($SELECT_DATA)-2);

        if(strlen($SELECT_DATA_FORMAT_DATE) > 0)
            $SELECT_DATA_FORMAT_DATE = substr($SELECT_DATA_FORMAT_DATE, 0, strlen($SELECT_DATA_FORMAT_DATE)-2);

        $VALIDATOR_UTAMA = substr($VALIDATOR_UTAMA, 0, strlen($VALIDATOR_UTAMA)-2);
        $VALIDATOR_DETAIL = substr($VALIDATOR_DETAIL, 0, strlen($VALIDATOR_DETAIL)-2);

        $DATA_POST = substr($DATA_POST, 0, strlen($DATA_POST)-2);

        $VALIDATOR_UPDATE = substr($VALIDATOR_UPDATE, 0, strlen($VALIDATOR_UPDATE)-2);

        $DATA_UPDATE = substr($DATA_UPDATE, 0, strlen($DATA_UPDATE)-2);

        $code = $sourceTransaction;
        $code = str_replace('@', '$', $code);
        $code = str_replace('#0CONTROLLER_NAME', $CONTROLLER_NAME, $code);
        $code = str_replace('#1TABLE_NAME', $req->table, $code);
        $code = str_replace('#2PRIMARY_KEY_UTAMA', $PRIMARY_KEY_UTAMA, $code);
        $code = str_replace('#2PRIMARY_KEY_DETAIL', $PRIMARY_KEY_DETAIL, $code);
        $code = str_replace('#3SELECT_DATA', $SELECT_DATA, $code);
        $code = str_replace('#4SELECT_DATA_FORMAT_DATE', $SELECT_DATA_FORMAT_DATE, $code);
        $code = str_replace('#5VALIDATOR_UTAMA', $VALIDATOR_UTAMA, $code);
        $code = str_replace('#5VALIDATOR_DETAIL', $VALIDATOR_DETAIL, $code);
        $code = str_replace('#6DATA_POST', $DATA_POST, $code);
        $code = str_replace('#7VALIDATOR_UPDATE', $VALIDATOR_UPDATE, $code);
        $code = str_replace('#8DATA_UPDATE', $DATA_UPDATE, $code);
        $code = str_replace('#9SUMBER', $SUMBER, $code);

        return $code;
    }

    // GENERATOR UI REACT JS
    public function ui(Request $req, Route $route)
    {
        $desc = DB::select("describe $req->table");

        $SOURCE = str_replace('f'.$req->noFix.'_', "", $req->table);
        $PRIMARY_KEY = '';
        $DATA_COLUMN = '';
        $DATA_FORMAT_DATE = '';
        $INPUT_FORM = '';
        $INPUT_FILTER = '';
        $PARAM_FILTER_GLOBAL = '';
        $SORT = '';

        $DATA_COLUMN .= "{ name: 'no', label: 'No', initialValue: '', width: '80', hidden: false },\n";

        foreach($desc as $field)
        {
            if($field->Key == 'PRI')
            {
                $PRIMARY_KEY = $field->Field;
            } 
            
            $initialValue = ($field->Type == 'timestamp' || $field->Type == 'date') ? 'defaultDate' : "''";
            $label = ucfirst(substr($field->Field, 1, strlen($field->Field)));

            if(isContain($field->Field, 'modifikasiuser') || isContain($field->Field, 'modifikasitgl') || isContain($field->Field, 'inputuser') || isContain($field->Field, 'inputtgl'))
                $DATA_COLUMN .= "        { name: '". $field->Field ."', label: '". $field->Field ."', initialValue: $initialValue, width: 'auto', hidden: true },\n";
            elseif($this->isCustom($field->Field))
                $DATA_COLUMN .= "        { name: '". $field->Field ."', label: '". $field->Field ."', initialValue: $initialValue, width: 'auto', hidden: true },\n";
            else
            {
                $DATA_COLUMN .= "        { name: '". $field->Field ."', label: '". $label ."', initialValue: $initialValue, width: 'auto', hidden: false },\n";
        
                if(!isContain($field->Field, 'modifikasiuser') && !isContain($field->Field, 'modifikasitgl') && !isContain($field->Field, 'inputuser') && !isContain($field->Field, 'inputtgl'))
                    $INPUT_FORM .= "        { id: '". $field->Field ."', type: 'text', width: '200%', label: '". $label ."', placeholder: '". $label ."'},\n";
            }
            
            if($field->Type == 'timestamp' || $field->Type == 'date')
            {
                $DATA_FORMAT_DATE .= "'" . $field->Field . "', ";
            } 

            $PARAM_FILTER_GLOBAL .= $field->Field . ' LIKE "%${val}%" OR ';
        }

        $DATA_COLUMN = substr($DATA_COLUMN, 0, strlen($DATA_COLUMN)-1);

        if(strlen($DATA_FORMAT_DATE) > 0)
            $DATA_FORMAT_DATE = substr($DATA_FORMAT_DATE, 0, strlen($DATA_FORMAT_DATE)-2);

        if(strlen($INPUT_FORM) > 0)
            $INPUT_FORM = substr($INPUT_FORM, 8, strlen($INPUT_FORM)-10);

        $INPUT_FILTER = $INPUT_FORM;

        if(strlen($PARAM_FILTER_GLOBAL) > 0)
            $PARAM_FILTER_GLOBAL = substr($PARAM_FILTER_GLOBAL, 0, strlen($PARAM_FILTER_GLOBAL)-4);

        $SORT = $PRIMARY_KEY;

        $code = $this->ThemeUI;
        $code = str_replace('@', '$', $code);
        $code = str_replace('#1_SOURCE', $SOURCE, $code);
        $code = str_replace('#2_PRIMARY_KEY', $PRIMARY_KEY, $code);
        $code = str_replace('#3_DATA_COLUMN', $DATA_COLUMN, $code);
        $code = str_replace('#4_DATA_FORMAT_DATE', $DATA_FORMAT_DATE, $code);
        $code = str_replace('#5_INPUT_FORM', $INPUT_FORM, $code);
        $code = str_replace('#6_INPUT_FILTER', $INPUT_FILTER, $code);
        $code = str_replace('#7_PARAM_FILTER_GLOBAL', $PARAM_FILTER_GLOBAL, $code);
        $code = str_replace('#8_SORT', $SORT, $code);

        return $code;
    }
    
    // GENERATOR API LARAVEL
    public function show(Request $req, Route $route)
    {
        $desc = DB::select("describe $req->table");

        $CONTROLLER_NAME = ucfirst(str_replace('f'.$req->noFix.'_', "", $req->table));
        $PRIMARY_KEY = '';
        $SELECT_DATA = '';
        $SELECT_DATA_FORMAT_DATE = '';
        $VALIDATOR_POST = '';
        $DATA_POST = '';
        $VALIDATOR_UPDATE = '';
        $DATA_UPDATE = '';

        foreach($desc as $field)
        {
            if($field->Key == 'PRI')
            {
                $PRIMARY_KEY = $field->Field;
            } 
            
            if(!$this->isCustom($field->Field))
                $SELECT_DATA .= $field->Field . ", ";

            
            if($field->Type == 'timestamp' || $field->Type == 'date')
            {
                $SELECT_DATA_FORMAT_DATE .= $field->Field . ", ";
            } 
            
            
            $validator = $this->setValidator($field->Type, $field->Null, (!isContain($field->Type, 'unsigned')) ? true : false);
            
            if(!isContain($field->Field, 'modifikasiuser') && !isContain($field->Field, 'modifikasitgl'))
            {
                if($validator != '')
                    $VALIDATOR_POST .= "           '$field->Field'   => '" . $validator . "',\n";

                $DATA_POST .= "$field->Field, ";
            }
            
            if(!isContain($field->Field, 'inputuser') && !isContain($field->Field, 'inputtgl') && ($field->Key != 'PRI'))
            {
                if($validator != '')
                    $VALIDATOR_UPDATE .= "           '$field->Field'   => '" . $validator . "',\n";

                $DATA_UPDATE .= "$field->Field, ";
            }

        }

        $SELECT_DATA = substr($SELECT_DATA, 0, strlen($SELECT_DATA)-2);

        if(strlen($SELECT_DATA_FORMAT_DATE) > 0)
            $SELECT_DATA_FORMAT_DATE = substr($SELECT_DATA_FORMAT_DATE, 0, strlen($SELECT_DATA_FORMAT_DATE)-2);

        $VALIDATOR_POST = substr($VALIDATOR_POST, 0, strlen($VALIDATOR_POST)-2);

        $DATA_POST = substr($DATA_POST, 0, strlen($DATA_POST)-2);

        $VALIDATOR_UPDATE = substr($VALIDATOR_UPDATE, 0, strlen($VALIDATOR_UPDATE)-2);

        $DATA_UPDATE = substr($DATA_UPDATE, 0, strlen($DATA_UPDATE)-2);

        $code = $this->source;
        $code = str_replace('@', '$', $code);
        $code = str_replace('#0CONTROLLER_NAME', $CONTROLLER_NAME, $code);
        $code = str_replace('#1TABLE_NAME', $req->table, $code);
        $code = str_replace('#2PRIMARY_KEY', $PRIMARY_KEY, $code);
        $code = str_replace('#3SELECT_DATA', $SELECT_DATA, $code);
        $code = str_replace('#4SELECT_DATA_FORMAT_DATE', $SELECT_DATA_FORMAT_DATE, $code);
        $code = str_replace('#5VALIDATOR_POST', $VALIDATOR_POST, $code);
        $code = str_replace('#6DATA_POST', $DATA_POST, $code);
        $code = str_replace('#7VALIDATOR_UPDATE', $VALIDATOR_UPDATE, $code);
        $code = str_replace('#8DATA_UPDATE', $DATA_UPDATE, $code);

        return $code;
    }

    // GENERATOR POSTMAN
    public function postman(Request $req, Route $route)
    {
        $content = file_get_contents('ERPFix.postman_collection.json');
        header ('Content-Type: application/octet-stream');
        $data = json_decode($content);

        // Reset modul
        $data->item[2]->item = [];
        
        $source = explode(",", $req->table);    
        foreach ($source as $value) 
        {
            $fieldPrimaryKey = '';
            $code = str_replace('f'.$req->noFix.'_', "", $value); 
            $set = json_decode($content)->item[2]->item[3];
            $set->name = ucfirst($code);
            
            $desc = DB::select("describe $value");
            
            $set->item[0]->request->body->formdata = [];
            $varUpdate = '';
            foreach($desc as $field)
            {
                $var = new StdClass;
                $var->key = $field->Field;
                
                // 1. Insert form data
                if($field->Key == 'PRI')
                {
                    $fieldPrimaryKey = $field->Field;
                    $var->value = $field->Field ."123";
                }
                elseif($field->Type == 'timestamp' || $field->Type == 'date')
                    $var->value = "{{timestamp}}";
                elseif(isContain($field->Type, 'int') || isContain($field->Type, 'double') || isContain($field->Type, 'bigint') || isContain($field->Type, 'tinyint'))
                    $var->value = "0";
                else
                    $var->value = "$field->Field 123";
                $var->type = "text";


                // 2. Insert form data
                if(!isContain($field->Field, 'modifikasiuser') && !isContain($field->Field, 'modifikasitgl'))
                    array_push($set->item[0]->request->body->formdata, $var);
                    
                // 1. Update form data
                if(!isContain($field->Field, 'inputuser') && !isContain($field->Field, 'inputtgl') && ($field->Key != 'PRI'))
                    if($field->Type == 'timestamp' || $field->Type == 'date')
                        $varUpdate .=  '"'. $field->Field .'":"{{timestamp}}",';
                    elseif(isContain($field->Type, 'int') || isContain($field->Type, 'double') || isContain($field->Type, 'bigint') || isContain($field->Type, 'tinyint'))
                        $varUpdate .= '"'. $field->Field .'":"0",';
                    else
                        $varUpdate .= '"'. $field->Field .'":"'. $field->Field .' 456",';
                    
            }
            // 2. Update form data
            $varUpdate = '{'. substr($varUpdate, 0, strlen($varUpdate)-1) . '}';
            $set->item[1]->request->body->raw = $varUpdate;

            // Insert set url
            $set->item[0]->request->url->raw = "{{url}}/f".$req->noFix."/". $code ."/insert?param={\"target\":\"insertData\"}";
            $set->item[0]->request->url->path[0] = 'f'.$req->noFix;
            $set->item[0]->request->url->path[1] = $code;
            
            // Update set url
            $set->item[1]->request->url->raw = "{{url}}/f".$req->noFix."/". $code ."/".$fieldPrimaryKey ."123?param={\"target\":\"updateData\"}";
            $set->item[1]->request->url->path[0] = 'f'.$req->noFix;
            $set->item[1]->request->url->path[1] = $code;
            $set->item[1]->request->url->path[2] = $fieldPrimaryKey ."123";
            $set->item[1]->request->url->query[0]->value = $fieldPrimaryKey ."{\"target\":\"updateData\"}";

            // Show set url
            $set->item[2]->request->url->raw = "{{url}}/f".$req->noFix."/". $code ."?param={\n    \"target\":\"show\",\n    \"pageNumber\":1,\n    \"pageLimit\":3,\n\t\"filter\":\"\",\n\t\"orderBy\":\"\",\n\t\"groupBy\": \"\"}";
            $set->item[2]->request->url->path[0] = 'f'.$req->noFix;
            $set->item[2]->request->url->path[1] = $code;

            // ShowById set url
            $set->item[3]->request->url->raw = "{{url}}/f".$req->noFix."/". $code ."/showById/".$fieldPrimaryKey ."123?param={\"target\":\"showById\"}";
            $set->item[3]->request->url->path[0] = 'f'.$req->noFix;
            $set->item[3]->request->url->path[1] = $code;
            $set->item[3]->request->url->path[2] = "showById";
            $set->item[3]->request->url->path[3] = $fieldPrimaryKey ."123";

            // Delete set url
            $set->item[4]->request->url->raw = "{{url}}/f".$req->noFix."/". $code ."/".$fieldPrimaryKey ."123?param={\"target\":\"delete\"}";
            $set->item[4]->request->url->path[0] = 'f'.$req->noFix;
            $set->item[4]->request->url->path[1] = $code;
            $set->item[4]->request->url->path[2] = $fieldPrimaryKey ."123";

            array_push($data->item[2]->item, $set);
        }

        // dd($data->item[2]->item);
        $data = json_encode($data);
        file_put_contents('ERPFix.postman_collection.json', $data);

        return `true`;
    }
    
    public $log = '';

    function console($data = '')
    {
        if($data != '')
        {
            $this->log .= $data . "\n";
        }
    }

    function isCustom($data = '')
    {
        if(strrpos($data, "customtext", 1))
            return true;

        if(strrpos($data, "customint", 1))
            return true;

        if(strrpos($data, "customdate", 1))
            return true;

        if(strrpos($data, "customdbl", 1))
        return true;

        return false;
    }

    public $validator = '';

    function setValidator($type = '', $null = '', $signed = true)
    {
        $this->validator = '';
        
        if($null == 'NO')
            $this->setVar('required');

        if(isContain($type, 'integer') || isContain($type, 'int') || isContain($type, 'double') || isContain($type, 'bigint') || isContain($type, 'tinyint'))
        {
            $this->setVar('numeric');
        }
        elseif(isContain($type, 'date') || isContain($type, 'timestamp'))
        {
            $this->setVar('date');
        }
            
        if(isContain($type, 'varchar'))
        {
            $val = str_replace('varchar(', '', $type);
            $val = str_replace(')', '', $val);
            $this->setVar('max:' . $val);
        }
        elseif(isContain($type, 'char'))
        {
            $val = str_replace('char(', '', $type);
            $val = str_replace(')', '', $val);
            $this->setVar('max:' . $val);
        }
        elseif(isContain($type, 'tinyint')) // 127
        {
            if($signed)
            {
                $this->setVar('min:-127');
                $this->setVar('max:127');
            }
            else
            {
                $this->setVar('min:0');
                $this->setVar('max:255');
            }
        }
        elseif(isContain($type, 'smallint')) 
        {
            if($signed)
            {
                $this->setVar('min:-32767'); 
                $this->setVar('max:32767'); // 32.767
            }
            else
            {
                $this->setVar('min:0');
                $this->setVar('max:65535'); // 65.535
            }
        }
        elseif(isContain($type, 'mediumint')) // 8.388.608
        {
            if($signed)
            {
                $this->setVar('min:-8388608'); 
                $this->setVar('max:8388608'); // 8.388.608
            }
            else
            {
                $this->setVar('min:0');
                $this->setVar('max:16777215'); // 16,777,215
            }
        }
        elseif(isContain($type, 'bigint')) // 9.223.372.036.854.775.808
        {
            if($signed)
            {
                $this->setVar('min:-9223372036854775808'); 
                $this->setVar('max:9223372036854775808'); // 9.223.372.036.854.775.808
            }
            else
            {
                $this->setVar('min:0');
                $this->setVar('max:18446744073709551615'); // 18,446,744,073,709,551,615
            }
        }
        elseif(isContain($type, 'int') || isContain($type, 'integer')) 
        {
            if($signed)
            {
                $this->setVar('min:-2147483647'); 
                $this->setVar('max:2147483647'); // 2.147.483.647
            }
            else
            {
                $this->setVar('min:0');
                $this->setVar('max:4294967295'); //  4,294,967,295
            }
        }

        return $this->validator;
    }

    function setVar($data = '')
    {
        if($data != '')
        {
            if($this->validator != '')
                $this->validator .= '|';

            $this->validator .= $data;
        }
    }

    public $ThemeUI = 
"import React from 'react';
import CRUD from './data/CRUD';
import { defaultDate } from '../../service';

class CRUD_Master extends React.Component 
{
  render() 
  {
    const params = {
      source: '#1_SOURCE',
      primaryKey: '#2_PRIMARY_KEY',
    
      dataColumn: [
        #3_DATA_COLUMN
      ],

      dataFormatDate: [#4_DATA_FORMAT_DATE],
    
      inputForm: [
        #5_INPUT_FORM
      ],
    
      inputFilter: [
        #6_INPUT_FILTER
      ],
    
      // Pagging
      paramFilterGlobal: (val) => `#7_PARAM_FILTER_GLOBAL`,
      sort: `#8_SORT`,
      limit: 4,
    }

    return (<CRUD params={params} />);
  }
}

export default CRUD_Master;
";


public $source =
"<?php

namespace App\Http\Controllers\Admin\F1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class #0CONTROLLER_NAMEController extends Controller
{
    // table identity
    public @tableName = '#1TABLE_NAME';
    public @primaryKey = '#2PRIMARY_KEY';

    // parameter for select data
    public @select = '#3SELECT_DATA';
    public @selectFormatDate = '#4SELECT_DATA_FORMAT_DATE';

    // Parameter for post data
    public @validatorPost = [
#5VALIDATOR_POST];
    public @dataPost = '#6DATA_POST';

    // Parameter for update data
    public @validatorUpdate = [
#7VALIDATOR_UPDATE];
    public @dataUpdate = '#8DATA_UPDATE';

    // Master 
    public @master = ''; 
    
    public function __construct() 
    {
        @this->master = new MasterController();
        @this->master->tableName        = @this->tableName;
        @this->master->primaryKey       = @this->primaryKey;
        @this->master->select           = @this->select;
        @this->master->selectFormatDate = @this->selectFormatDate;
        @this->master->validatorPost    = @this->validatorPost;
        @this->master->dataPost         = @this->dataPost;
        @this->master->validatorUpdate  = @this->validatorUpdate;
        @this->master->dataUpdate       = @this->dataUpdate;
    }

    public function show(Request @req, Route @route)
    {
        return @this->master->show(@req, @route);
    }
    
    public function showById(Request @req, Route @route)
    {
        return @this->master->showById(@req, @route);
    }
    
    public function insert(Request @req, Route @route)
    {
        return @this->master->insert(@req, @route);
    }
    
    public function update(Request @req, Route @route)
    {
        return @this->master->update(@req, @route);
    }
    
    public function delete(Request @req, Route @route)
    {
        return @this->master->delete(@req, @route);
    }

}
"; 
}