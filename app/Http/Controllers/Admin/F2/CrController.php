<?php

namespace App\Http\Controllers\Admin\F2;

use App\Helpers\helpfix as Res;
use App\Helpers\nomor;
use App\Helpers\ModelsDB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Helpers\Global_help;
use Validator;
use DB;

class CrController extends Controller
{

    public function insert(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            "userid"    => "required|numeric|min:0|max:255",
            "isUpdate"  => "required|numeric|min:0|max:1",
            "target"    => "required",
            "utama"     => "required",
            "detail"    => "required",
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }
        
        $utama = json_decode($req->utama);
        $validator = Validator::make((array)$utama, [
           'crid'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'crcabang'   => 'required|max:25',
           'crlokasi'   => 'required|max:25',
           'crsumber'   => 'required|max:10',
           'crautonotransaksi'   => 'required|numeric|min:-127|max:127',
           'crnotransaksi'   => 'required|max:50',
           'crtgl'   => 'required|date',
           'crkodepa'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'crkontak'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'crkontakperson'   => 'max:100',
           'crnorek'   => 'required|max:25',
           'cruraian'   => 'max:250',
           'crcatatan'   => 'max:250',
           'crmatauang'   => 'required|max:25',
           'crkurs'   => 'required|numeric',
           'crjumlah'   => 'required|numeric',
           'crjumlahvalas'   => 'required|numeric',
           'crjumlahbayar'   => 'required|numeric',
           'crjumlahbayarvalas'   => 'required|numeric',
           'crstatusbayar'   => 'required|numeric|min:-127|max:127',
           'crtgllunas'   => 'date',
           'crstatus'   => 'required|numeric|min:-127|max:127',
           'crstatussebelumnya'   => 'required|numeric|min:-127|max:127',
           'crjmlrevisi'   => 'required|numeric|min:-2147483647|max:2147483647',
           'crcetakanke'   => 'required|numeric|min:-127|max:127',
           'crisclose'   => 'required|numeric|min:-2147483647|max:2147483647',
           'crinputuser'   => 'numeric|min:-9223372036854775808|max:9223372036854775808',
           'crinputtgl'   => 'required|date',
           'crmodifikasiuser'   => 'numeric|min:-9223372036854775808|max:9223372036854775808',
           'crmodifikasitgl'   => 'required|date',
           'crposting'   => 'required|numeric|min:-127|max:127',
           'crpostingtgl'   => 'required|date',
           'crcustomtext1'   => 'max:250',
           'crcustomtext2'   => 'max:250',
           'crcustomtext3'   => 'max:250',
           'crcustomtext4'   => 'max:250',
           'crcustomtext5'   => 'max:250',
           'crcustomint1'   => 'required|numeric|min:-2147483647|max:2147483647',
           'crcustomint2'   => 'required|numeric|min:-2147483647|max:2147483647',
           'crcustomint3'   => 'required|numeric|min:-2147483647|max:2147483647',
           'crcustomdbl1'   => 'required|numeric',
           'crcustomdbl2'   => 'required|numeric',
           'crcustomdbl3'   => 'required|numeric',
           'crcustomdate1'   => 'required|date',
           'crcustomdate2'   => 'required|date',
           'crcustomdate3'   => 'required|date']);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $detail = json_decode($req->detail); $msgDetail = [];
        foreach($detail as $no => $val)
        {
            $validator = Validator::make((array)$val, [
              'idcrdetail'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
              'idcr'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
              'norek'   => 'required|max:25',
              'matauang'   => 'required|max:25',
              'kurs'   => 'required|numeric',
              'jumlah'   => 'required|numeric',
              'jumlahvalas'   => 'required|numeric',
              'catatan'   => 'max:250',
              'costcenter'   => 'max:25',
              'divisi'   => 'max:25',
              'subdivisi'   => 'max:25',
              'proyek'   => 'max:25',
              'urutan'   => 'required|numeric|min:-2147483647|max:2147483647',
              'isclose'   => 'required|numeric|min:-2147483647|max:2147483647',
              'customtext1'   => 'max:250',
              'customtext2'   => 'max:250',
              'customtext3'   => 'max:250',
              'customdbl1'   => 'required|numeric',
              'customdbl2'   => 'required|numeric',
              'customdbl3'   => 'required|numeric',
              'customdate1'   => 'required|date',
              'customdate2'   => 'required|date',
              'customdate3'   => 'required|date']);

            if ($validator->fails())
            {
                $msg = new \StdClass;
                $msg->row = ($no + 1);
                $msg->message = $validator->messages()->all();
                array_push($msgDetail, $msg);
            }
        }

        if (notEmpty($msgDetail)) { $res->fail($msgDetail, 409); goto selesai; }

        $userid      = $req->userid;
        $isUpdate    = $req->isUpdate;
        $res->target = $req->target;
        
        // "*** Start Transaction ***"  
        DB::beginTransaction();

        //  CEK HAK AKSES STATUS ============================
        $vAkses = 0; $msgAkses = "";
        //  MODUL DAN MENU HARUS DISESUAIKAN
        $vModuleId = 2; $vMenuId = 3;
        switch($utama->crstatus)
        {
            case 0: $vAkses = 0; break;
            case 1: $vAkses = 0; break;
            case 2: $vAkses = 8; break;
            case 3: $vAkses = 0; break;
            case 4: $vAkses = 0; break;
            case 5: $vAkses = 0; break;
            case 6: $vAkses = 0; break;
            case 7: $vAkses = 0; break;
            case 8: $vAkses = 4; break;
            case 9: $vAkses = 5; break;
            case 10: $vAkses = 6; break;
            case 11: $vAkses = 7; break;
            case 12: $vAkses = 0; break;
        }

        $cekAkses = Global_help::hakAkses($vModuleId, $vMenuId, $vAkses, $userid);
        if(strlen($cekAkses) > 0) { $res->fail($cekAkses); DB::rollBack(); goto selesai;}
        //  END OF CEK HAK AKSES STATUS =====================

        //  CEK PERIODE AKUNTANSI ==================================
        $rsCekPeriode = Global_help::M2_Accounting_PeriodeCheck($utama->crtgl, $utama->crtgl);
        if(!$rsCekPeriode->success) { $res->fail($rsCekPeriode->errmessage); DB::rollBack(); goto selesai; }
        //  END OF CEK PERIODE AKUNTANSI ===========================

        // "CEK MATAUANG COA =======================================
        $rsCekCoa = ValidasiMatauangCOA($utama, "crmatauang", "crnorek", $detail, "norek");
        if(notEmpty($rsCekCoa)) 
        {
            $res->fail($rsCekCoa); DB::rollBack(); goto selesai;
        }
        // "END OF CEK MATAUANG COA ================================

        // "CEK COA WAJIB COST CENTER ==============================
        if ($utama->crstatus == 2)
        {
            foreach($detail as $val)
            {
                $strRekCostCenter = (empty($strRekCostCenter))? "": "$strRekCostCenter OR ";
                $strRekCostCenter .= " cnomor = '$val->norek' ";
            }
            $cekCoaCostCenter = ValidasiCoaRequiredCostCenter($strRekCostCenter, $detail);
            if (notEmpty($cekCoaCostCenter)){ $res->fail($cekCoaCostCenter); DB::rollBack(); goto selesai; }
        }
        // "END OF CEK COA WAJIB COST CENTER =======================

        // "HITUNG TOTAL BERDASARKAN DATA DETAIL ===================
        $utama->crjumlah = AsDataTableDSum($detail, "jumlah");
        $utama->crjumlahvalas = AsDataTableDSum($detail, "jumlahvalas");
        // "END OF HITUNG TOTAL BERDASARKAN DATA DETAIL ============

        $res->data = new \StdClass;
        
        if (!$isUpdate)
        {
            if ($utama->crautonotransaksi)
            {
                // "GENERATE NOTRANSAKSI =========================================
                $rsNotransaksi = nomor::M0_Notransaksi($utama->crcabang, $utama->crlokasi, $utama->crsumber, $utama->crtgl, $utama->crsumber, 2);
                // cek success generate notransaksi
                if ($rsNotransaksi->success == 1)
                {
                    $notransaksi = $rsNotransaksi->notransaksi;
                    // "tambah query update m0_nomor_next
                    DB::insert($rsNotransaksi->sql);
                }
                else
                {
                    $res->fail($rsNotransaksi->errmessage); DB::rollBack(); goto selesai;
                }
                // "END OF GENERATE NOTRANSAKSI ==================================
            }
            else
                $notransaksi = $utama->crnotransaksi;
            

            // "CEK NO TRANSAKSI ======================
            $dtCekNo = DB::select("SELECT COUNT(crid) as jml FROM f2_cr WHERE crnotransaksi='$notransaksi'");
            $cekNo = $dtCekNo[0]->jml;
            if ($cekNo > 0)
            {
                $res->fail("No. : '$notransaksi' - has been used."); DB::rollBack(); goto selesai;
            }
            // "END OF CEK NO TRANSAKSI ===============

            $get_col = ""; $get_val = "";
            $utama->crnotransaksi = $notransaksi;
            $colNotContains = "crid";
            $colDate = 'crtgl, crtgllunas, crinputtgl, crmodifikasitgl, crpostingtgl, crcustomdate1, crcustomdate2, crcustomdate3';
            foreach($utama as $col => $val)
            {
                if(!checkContains($colNotContains, $col))
                {
                    $get_col .= $col . ", ";

                    $get_val .= "'". FixQuotes($val) ."', ";
                }
            }
            $get_col = substr($get_col, 0, strlen($get_col)-2);
            $get_val = substr($get_val, 0, strlen($get_val)-2);

            $sql = "INSERT INTO f2_cr (" . $get_col . ") VALUES (". $get_val .")";
            DB::insert($sql);

            // Sql disesuaikan sendiri, untuk parameternya disesuaikan sendiri.
            $sql = "select crid as transaksi from f2_cr where crnotransaksi='" . $notransaksi . "' AND crinputuser= '" . $userid . "' order by crmodifikasitgl desc limit 1";
            $dt2 = DB::select($sql);

            if(notEmpty($dt2)) 
                $idtransaksi = $dt2[0]->transaksi;
            else
            { 
                $res->fail("Main transaction data not found."); DB::rollBack(); goto selesai;
            }
        }
        else
        {
            $idtransaksi = $utama->crid;
            $notransaksi = $utama->crnotransaksi;

            // 'JIKA UPDATE CEK JML ROW PADA DATABASE
            $dtupdate = DB::select("SELECT COUNT(crid) as jml, crnotransaksi FROM f2_cr WHERE crid='" . $idtransaksi . "' AND crstatus NOT IN(2,3,4,7)");
            $rowUpdate = $dtupdate[0]->jml;

            if (notEmpty($rowUpdate))
            {
                if ($utama->crautonotransaksi == 1 && $notransaksi == "Auto")
                {
                    // 'GENERATE NOTRANSAKSI =========================================
                    $rsNotransaksi = nomor::M0_Notransaksi($utama->crcabang, $utama->crlokasi, $utama->crsumber, $utama->crtgl, $utama->crsumber, 2);
                    // cek success generate notransaksi
                    if ($rsNotransaksi->success == 1)
                    {
                        $notransaksi = $rsNotransaksi->notransaksi;
                        // 'tambah query update m0_nomor_next
                        DB::insert($rsNotransaksi->sql);
                    }
                    else
                    {
                        $res->fail($rsNotransaksi->errmessage); DB::rollBack(); goto selesai;
                    }
                    // 'END OF GENERATE NOTRANSAKSI ==================================
                }

                // 'CEK NO TRANSAKSI ======================
                if ($notransaksi != $dtupdate[0]->crnotransaksi)
                {
                    $dtCekNo = DB::select("SELECT COUNT(crid) as jml FROM f2_cr WHERE crnotransaksi='$notransaksi'");
                    $cekNo = $dtCekNo[0]->jml;
                    if (notEmpty($cekNo))
                    {
                        $res->fail("No. : '$notransaksi' - has been used."."SELECT COUNT(crid) as jml FROM f2_cr WHERE crnotransaksi='$notransaksi'"); DB::rollBack(); goto selesai;
                    }
                }
                // 'END OF CEK NO TRANSAKSI ===============

                // 'SIMPAN HISTORY ========================
                insertHistory('f2_cr', 'crid', 'idcr', $notransaksi, $idtransaksi);
                // 'END OF SIMPAN HISTORY ==================
                
                $get_val = '';
                $utama->crnotransaksi = $notransaksi;
                $colNotContains = 'crid';
                $colDate = 'crtgl, crtgllunas, crinputtgl, crmodifikasitgl, crpostingtgl, crcustomdate1, crcustomdate2, crcustomdate3';
                foreach($utama as $col => $val)
                {
                    if(!checkContains($colNotContains, $col))
                    {
                        $get_val .= $col . " = '". FixQuotes($val) ."', ";
                    }
                }
                $get_val = substr($get_val, 0, strlen($get_val)-2);

                $sql = "UPDATE f2_cr SET $get_val WHERE crid = '$utama->crid'";
                DB::update($sql);
            }
            else
            {
                $res->fail("Can't update No. : '$notransaksi' - it has been approved."); DB::rollBack(); goto selesai;
            }
        }

        $res->data->idtransaksi = $idtransaksi;
        $res->data->notransaksi = $notransaksi;

        // 'Hapus detail ketika update
        if ($isUpdate)
        {
            $sql = "DELETE FROM f2_cr_Detail where idcr = '$idtransaksi'";
            DB::delete($sql);
        }

        // 'Proses detail
        $colNotContains = "idcrdetail, idcr";
        $get_col = ''; $get_val = '';
        foreach($detail as $i => $arr)
        {
            $get_val .= '(';
            $arr->idcr = $idtransaksi;
            foreach($arr as $col => $val)
            {
                if(!checkContains($colNotContains, $col))
                {
                    if($i == 0)
                        $get_col .= $col . ", ";

                    $get_val .= "'". FixQuotes($val) ."', ";
                }
				else
				{
					if($col == 'idcr')
					{				
						if($i == 0)
							$get_col .= $col . ", ";

						$get_val .= "'$idtransaksi', ";
					}						
				}
            }
            if($i == 0)
                $get_col = substr($get_col, 0, strlen($get_col)-2);
            $get_val = substr($get_val, 0, strlen($get_val)-2) . "), ";
        }
        $get_val = substr($get_val, 0, strlen($get_val)-2);
        $sql = "INSERT INTO f2_cr_Detail($get_col) VALUES $get_val"; 
        DB::insert($sql);

        // 'INSERT MSMQ JURNAL =================================================================
        $sumber = "cr"; $mdlid = 0; $mnid = 0; $jnsaktivitas = 0;
        if ($utama->crstatus == 2)
        {
            // 'BUAT ID UNIQUE
            $mjid = 'Security.MD5Cal)'.$notransaksi; //RandomId.Generate(15)

            // 'MSMQ TABEL
            $VALUES = "'" . $mjid . "', '" . $sumber . "', '" . $idtransaksi . "', '" . 0 . "', " . "''" . ", NOW(), '1971-01-01 00:00:00', '" . $userid . "'";
            $sql = "INSERT INTO f0_Msmq_Journal(mjid, mjsumber, mjidtransaksi, mjprogress, mjpesan, mjtglantrian, mjtglselesai, mjuserid) VALUES (". $VALUES .")";
            // DB::insert($sql);

            // 'MSMQ ANTRIAN
            $PostingJurnal = F_getSetting(0, "accounting", "AutoPosting");
            if ($PostingJurnal == 1)
            {
                // $hasilMsmq = SendMsmq($dirMsmq, "J", $mjid, $sumber, $idtransaksi, $userid);
                // if (notEmpty($hasilMsmq))
                // {
                //     $res->fail($hasilMsmq); DB::rollBack(); goto selesai;
                // }
            }
        }
        // 'END OF INSERT MSMQ JURNAL ==========================================================


        // 'INSERT USER LOG ====================================================================
        // 'ambil moduleid dan menuid dari m0_nomor
        $dtnomor = DB::select("SELECT moduleid, menuid FROM f0_nomor WHERE kodetabel='" . $sumber . "'");
        if (notEmpty($dtnomor)) 
        {
            $mdlid = $dtnomor[0]->moduleid; 
            $mnid = $dtnomor[0]->menuid;
        }
        else
        {
            $res->fail("Can't find '" . $sumber . "' in M0_Nomor."); DB::rollBack(); goto selesai;
        }

        // 'jika update jnsaktivitas = 14, jika insert : jnsaktivitas = 13
        if ($isUpdate) $jnsaktivitas = 14; else $jnsaktivitas = 13;

        $VALUES = $userid . ", " . $mdlid . ", " . $mnid . ", " . $jnsaktivitas . ", '" . $notransaksi . "', NOW(), " . 0 ;
        $sql = "INSERT INTO f0_Userlog (uluserid, ulidmodule, ulidmenu, uljenisaktivitas, ulaktivitas, ultgl, ulkodepa) VALUES(". $VALUES .")";
        DB::insert($sql);
        // 'END OF INSERT USER LOG =============================================================

        DB::commit();
        $res->succes();

        selesai:
        return $res->done();
    }
    
    public function updateStatus(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            'idtransaksi'   => 'required|numeric|min:0',
            'nilaistatus'   => 'required|numeric|min:0|max:12',
            'userid'        => 'required|numeric|min:0|max:255',
            'target'        => 'required',
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $res->target = $req->target;
        $nilaiStatus = (int)$req->nilaistatus;
        $idtransaksi = $req->idtransaksi;
        $userid = $req->userid;
        
        // 'JIKA NUMERIC MAKA NILAISTATUS = PARAM NILAI STATUS YG DIINPUT
        // 'JIKA TIDAK MAKA NILAISTATUS = UNCLOSE
        if (gettype($nilaiStatus) == 'integer') 
        {
            // 'JIKA NILAI STATUS < 0 ATAU NILAI STATUS > 12 MAKA NILAISTATUS TIDAK VALID
            if ($nilaiStatus < 0 || $nilaiStatus > 12)
            {
                // $res->fail("Invalid transaction status value."); goto selesai;
            }
        }
        else
        {
            if (ucfirst($nilaiStatus) === "unclose")
                $nilaiStatus = "unclose";
            else
            {
                $res->fail("Invalid transaction status value."); goto selesai;
            }
        }

        // '*** Start Transaction ***'
        DB::beginTransaction();

        // 'PERSIAPAN INSERT USER LOG ==========================================================
        $sumber = "cr"; $tglTransaksi = "";
        $mdlid = 0; $mnid = 0; $jnsaktivitas = 0; $statusTransaksi = 0;
        // 'ambil moduleid, menuid dari m0_nomor dan tgl, notransaksi, status dari transaksi
        $dtdetail = DB::select("SELECT moduleid, menuid, 0 as statusTransaksi FROM f0_nomor WHERE kodetabel='" . $sumber . "' UNION SELECT crtgl, crnotransaksi, crstatus FROM f2_cr WHERE crid='" . $idtransaksi . "'");

        if (count($dtdetail) > 1)
        {
            $mdlid = $dtdetail[0]->moduleid;
            $mnid = $dtdetail[0]->menuid; 
            $tglTransaksi = $dtdetail[1]->moduleid;
            $notransaksi = $dtdetail[1]->menuid;
            $statusTransaksi = $dtdetail[1]->statusTransaksi;

            if($nilaiStatus == $statusTransaksi)
            {
                $res->fail("Transaction '$notransaksi' status has been drafted."); DB::rollBack(); goto selesai;                
            }
        }
        else
        {
            $res->fail("#1. Transaction data not found."); DB::rollBack(); goto selesai;
        }
        // 'END OF PERSIAPAN INSERT USER LOG ===================================================

        // 'JIKA UNCLOSE MAKA SET NILAI STATUS = STATUSSEBELUMNYA, JNSAKTIVITAS = 17. ELSE JNSAKTIVITAS = NILAISTATUS
        if ($nilaiStatus === "unclose")
        {
            $nilaiStatus = "crstatussebelumnya"; $jnsaktivitas = 17;
            // 'CEK STATUS TRANSAKSI, JIKA <> 7 MAKA TIDAK BISA UNCLOSE
            if ($statusTransaksi != 7)
            {
                $res->fail("Transaction has not closed, it can't be unclose."); DB::rollBack(); goto selesai;
            }
        }
        else
            $jnsaktivitas = $nilaiStatus;

        // 'SET ISDELETE = TRUE JIKA STATUS TRANSAKSI = 2/3/4/7 DAN JNS AKTIVITAS <> 7(CLOSE) & 17(UNCLOSE)
        $isDelete = (($statusTransaksi == 2 || $statusTransaksi == 3 || $statusTransaksi == 4 || $statusTransaksi == 7) && $jnsaktivitas != 7 && $jnsaktivitas != 17); 

        // 'CEK PERIODE AKUNTANSI ==============================================================
        $rsCekPeriode = Global_help::M2_Accounting_PeriodeCheck($tglTransaksi, $tglTransaksi);
        if(!$rsCekPeriode->success) { $res->fail($rsCekPeriode->errmessage); DB::rollBack(); goto selesai; }
        // 'END OF CEK PERIODE AKUNTANSI =======================================================

        // 'SIMPAN HISTORY ========================
        insertHistory('f2_cr', 'crid', 'idcr', $notransaksi, $idtransaksi);
        // 'END OF SIMPAN HISTORY ==================

        if ($isDelete)
        {
            // 'DELETE JURNAL
            $sql = "DELETE FROM f2_Transaction_Journal WHERE tsumber = 'cr' AND tidtransaksi = '" . $idtransaksi . "' AND tnotransaksi = '" . $notransaksi . "'";
            DB::delete($sql);
        }

        // 'update status utama
        $sql = "UPDATE f2_cr SET crstatus = " . $nilaiStatus . ", crmodifikasiuser='" . $userid . "', crmodifikasitgl = NOW(), crposting = 0, crpostingtgl = '1971-01-01 00:00:00', crjmlrevisi = crjmlrevisi + 1 WHERE crid = '" . $idtransaksi . "'";
        DB::update($sql);

        // 'INSERT USER LOG ====================================================================
        $VALUES = $userid . ", " . $mdlid . ", " . $mnid . ", " . $jnsaktivitas . ", '" . $notransaksi . "', NOW(), 0";
        $sql = "INSERT INTO f0_Userlog (uluserid, ulidmodule, ulidmenu, uljenisaktivitas, ulaktivitas, ultgl, ulkodepa) VALUES(". $VALUES .")";
        DB::insert($sql);
        // 'END OF INSERT USER LOG =============================================================

        DB::commit();
        $res->succes();

        selesai:
        return $res->done();
    }

    public function delete(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            'idtransaksi'   => 'required|numeric|min:0',
            'userid'        => 'required|numeric|min:0|max:255',
            'target'        => 'required',
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $idtransaksi = (int)$req->idtransaksi;
        $userid = $req->userid;
        $res->target = $req->target;

        // '*** Start Transaction ***'
        DB::beginTransaction();

        // 'PERSIAPAN INSERT USER LOG ==========================================================
        $sumber = "cr"; $notransaksi = ""; $mdlid = 0; $mnid = 0; $jnsaktivitas = 0;
        // 'ambil moduleid dan menuid dari m0_nomor
        $dtnomor = DB::select("SELECT moduleid, menuid FROM f0_nomor WHERE kodetabel='" . $sumber . "' UNION SELECT crid, crnotransaksi FROM f2_cr WHERE crid='" . $idtransaksi . "'");
        if (notEmpty($dtnomor))
        {
            $mdlid = $dtnomor[0]->moduleid;
            $mnid = $dtnomor[0]->menuid; 
            $notransaksi = $dtnomor[1]->menuid;
        }
        else
        {
            $res->fail("#1. Transaction data not found."); DB::rollBack(); goto selesai;
        }

        // 'hapus : jnsaktivitas = 12
        $jnsaktivitas = 12;
        // 'END OF PERSIAPAN INSERT USER LOG ===================================================

        
        // 'PERSIAPAN UPDATE NOMOR BERIKUTNYA ==================================================
        // Dim cabang As String = "", lokasi As String = "", autonotransaksi As Integer = 0, tgl As String = ""
        $sql = "  SELECT crcabang, crlokasi, crsumber, crautonotransaksi, crnotransaksi, crtgl, crstatus";
        $sql .= " FROM f2_cr";
        $sql .= " WHERE crid = '" . $idtransaksi . "'";
        $dtNomorNext = DB::select($sql);
        if (notEmpty($dtNomorNext))
        {
            $notransaksi = $dtNomorNext[0]->crnotransaksi;
            $status = $dtNomorNext[0]->crstatus;

            if($status == 2 || $status == 3 || $status == 4 || $status == 7)
            {
                $res->fail("Can't delete No. : '$notransaksi' - it has been approved."); DB::rollBack(); goto selesai;
            }
            $cabang = $dtNomorNext[0]->crcabang;
            $lokasi = $dtNomorNext[0]->crlokasi; 
            $sumber = $dtNomorNext[0]->crsumber;
            $autonotransaksi = (int)$dtNomorNext[0]->crautonotransaksi;
            $tgl = $dtNomorNext[0]->crtgl;
        }
        else
        {
            $res->fail("#2. Transaction data not found."); DB::rollBack(); goto selesai;
        }
        // 'END OF PERSIAPAN UPDATE NOMOR BERIKUTNYA ===========================================

        // 'DELETE JURNAL
        $sql = "DELETE FROM f2_Transaction_Journal WHERE tsumber = 'cr' AND tidtransaksi = '" . $idtransaksi . "' AND tnotransaksi = '" . $notransaksi . "'";
        DB::delete($sql);

        // 'DELETE DETAIL
        $sql = "DELETE FROM f2_cr_Detail WHERE idcr = '" . $idtransaksi . "'";
        DB::delete($sql);

        // 'DELETE UTAMA
        $sql = "DELETE FROM f2_cr WHERE crid = '" . $idtransaksi . "'";
        DB::delete($sql);

        // 'UPDATE NOMOR BERIKUTNYA ============================================================
        // 'JIKA AUTO NO. TRANSAKSI
        if ($autonotransaksi == 1)
        {
            $rsNomorNext = M0_DeleteNotransaksi($cabang, $lokasi, $sumber, $tgl, $notransaksi, $sumber, 2);
            // 'Cek success M0_DeleteNotransaksi
            if ($rsNomorNext->success == 1)
            {
                // 'tambah query update m0_nomor_next
                DB::insert($rsNomorNext->sql);
            }
            else
            {
                $res->fail($rsNomorNext->errmessage); DB::rollBack(); goto selesai;
            }
        }
        // 'END OF UPDATE NOMOR BERIKUTNYA =====================================================

        // 'INSERT USER LOG ====================================================================
        $VALUES = $userid . ", " . $mdlid . ", " . $mnid . ", " . $jnsaktivitas . ", '" . $notransaksi . "', NOW(), 0";
        $sql = "INSERT INTO f0_Userlog (uluserid, ulidmodule, ulidmenu, uljenisaktivitas, ulaktivitas, ultgl, ulkodepa) VALUES(". $VALUES .")";
        DB::insert($sql);
        // 'END OF INSERT USER LOG =============================================================

        DB::commit();
        $res->succes();

        selesai:
        return $res->done();
    }

    public function getDataById(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            'idtransaksi'   => 'required|numeric|min:0',
            'target'        => 'required',
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $idtransaksi = (int)$req->idtransaksi;
        $target = $req->target;
        $filter = "WHERE crid = $idtransaksi";
        
        $sql = "
            SELECT 
                `cr`.* ,`br`.`bnama` AS `crcabangnama`,`lc`.`lnama` AS `crlokasinama`,`c`.`kkode` AS `crkontakkode`,
                `c`.`knama` AS `crkontaknama`,`coa`.`cnama` AS `crnoreknama`,`st1`.`nama` AS `crstatusnama`,`st2`.`nama` AS `crstatussebelumnyanama`,
                `u1`.`unama` AS `crinputusernama`,`u2`.`unama` AS `crmodifikasiusernama`,`crd`.*,
                `coa1`.`cnama` AS `noreknama`,`cc`.`ccnama` AS `costcenternama`,`d`.`dnama` AS `divisinama`,`sd`.`sdnama` AS `subdivisinama`,
                `p`.`pnama` AS `proyeknama` 
            from `f2_cr` `cr` 
            join `f2_cr_detail` `crd` on `cr`.`crid` = `crd`.`idcr` 
            left join `f1_branch` `br` on `cr`.`crcabang` = `br`.`bkode` 
            left join `f1_location` `lc` on `cr`.`crlokasi` = `lc`.`lkode` 
            left join `f1_contact` `c` on `cr`.`crkontak` = `c`.`kid` 
            left join `f1_coa` `coa` on `cr`.`crnorek` = `coa`.`cnomor` 
            left join `f0_status` `st1` on `cr`.`crstatus` = `st1`.`kode` 
            left join `f0_status` `st2` on `cr`.`crstatussebelumnya` = `st2`.`kode` 
            left join `f0_user` `u1` on `cr`.`crinputuser` = `u1`.`userid` 
            left join `f0_user` `u2` on `cr`.`crmodifikasiuser` = `u2`.`userid` 
            left join `f1_coa` `coa1` on `crd`.`norek` = `coa1`.`cnomor` 
            left join `f1_cost_center` `cc` on `crd`.`costcenter` = `cc`.`cckode` 
            left join `f1_division` `d` on `crd`.`divisi` = `d`.`dkode` 
            left join `f1_subdivision` `sd` on `crd`.`subdivisi` = `sd`.`sdkode` 
            left join `f1_project` `p` on `crd`.`proyek` = `p`.`pkode` ";
        $sql .= $filter;

        $dt = DB::select($sql);

        $get_val = '';
        $utama = new \StdClass; $arrDetail = [];
        foreach($dt as $i => $arr)
        {
            $detail = new \StdClass; 

            foreach($arr as $col => $val)
            {
                // SET DATA UTAMA
                if($i == 0)
                    $utama->{$col} = $val;

                // SET DATA DETAIL
                $detail->{$col} = $val;
            }

            array_push($arrDetail, $detail);
        }

        $res->data = [$utama, $arrDetail];

       
        
        $res->succes();
        selesai:
        return $res->done();
    }
    
    public function search(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            'pageNumber'  => 'required|numeric|min:0',
            'pageLimit'   => 'required|numeric|min:0',
            'target'   => 'required',
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $filter  = (empty($req->filter))  ? "" : $req->filter;
        $groupBy = (empty($req->groupBy)) ? "" : $req->groupBy;
        $orderBy = (empty($req->orderBy)) ? "" : $req->orderBy;
        $res->target = $req->target;
        
        $db = new ModelsDB('f2_cr AS cr');
        $db->leftJoin('f1_branch AS br', 'cr.crcabang', '=' ,'br.bkode');
        $db->leftJoin('f1_location AS lc', 'cr.crlokasi', '=' ,'lc.lkode');
        $db->leftJoin('f1_contact AS c', 'cr.crkontak', '=' ,'c.kid');
        $db->leftJoin('f1_coa AS coa', 'cr.crnorek', '=' ,'coa.cnomor');
        $db->leftJoin('f0_status AS st1', 'cr.crstatus', '=' ,'st1.kode');
        $db->leftJoin('f0_status AS st2', 'cr.crstatussebelumnya', '=' ,'st2.kode');
        $db->leftJoin('f0_user AS u1', 'cr.crinputuser', '=' ,'u1.userid');
        $db->leftJoin('f0_user AS u2', 'cr.crmodifikasiuser', '=' ,'u2.userid');
        $db->select('cr.*, br.bnama AS crcabangnama, lc.lnama AS crlokasinama,
                c.kkode AS crkontakkode, c.knama AS crkontaknama, coa.cnama AS crnoreknama, st1.nama AS crstatusnama,
                st2.nama AS crstatussebelumnyanama, u1.unama AS crinputusernama, u2.unama AS crmodifikasiusernama');
        $db->selectFormatDate('crinputtgl, crmodifikasitgl');
        $db->getData($filter, $groupBy, $orderBy, $req->pageLimit, $req->pageNumber);

        $res->data = $db->result;

        $res->succes();
        selesai:
        return $res->done();
    }
    
    public function terkait(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            'target'        => 'required',
            'idtransaksi'   => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $res->target = $req->target;
        $dt = new \StdClass;
        // $dt = DB::select($sql);
        if(notEmpty($dt))
        {
            foreach($dt as $arr)
            {
                $detail = new \StdClass; 
    
                foreach($arr as $col => $val)
                {
                    // SET DATA DETAIL
                    $detail->{$col} = $val;
                }
    
                array_push($res->data, $detail);
            }
        }
        else
        {
            $res->data = "Related cr data not found."; 
        }

        $res->succes();
        selesai:
        return $res->done();
    }
    
    public function searchHistory(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            'pageNumber'  => 'required|numeric|min:0',
            'pageLimit'   => 'required|numeric|min:0',
            'target'   => 'required',
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $filter  = (empty($req->filter))  ? "" : $req->filter;
        $groupBy = (empty($req->groupBy)) ? "" : $req->groupBy;
        $orderBy = (empty($req->orderBy)) ? "" : $req->orderBy;
        $res->target = $req->target;

        $db = new ModelsDB('f2_cr_history AS cr');
        $db->leftJoin('f1_branch AS br', 'cr.crcabang', '=' ,'br.bkode');
        $db->leftJoin('f1_location AS lc', 'cr.crlokasi', '=' ,'lc.lkode');
        $db->leftJoin('f1_contact AS c', 'cr.crkontak', '=' ,'c.kid');
        $db->leftJoin('f1_coa AS coa', 'cr.crnorek', '=' ,'coa.cnomor');
        $db->leftJoin('f0_status AS st1', 'cr.crstatus', '=' ,'st1.kode');
        $db->leftJoin('f0_status AS st2', 'cr.crstatussebelumnya', '=' ,'st2.kode');
        $db->leftJoin('f0_user AS u1', 'cr.crinputuser', '=' ,'u1.userid');
        $db->leftJoin('f0_user AS u2', 'cr.crmodifikasiuser', '=' ,'u2.userid');
        $db->select('`cr`.*,`br`.`bnama` AS `crcabangnama`,`lc`.`lnama` AS `crlokasinama`,`c`.`kkode` AS `crkontakkode`,
        `c`.`knama` AS `crkontaknama`,`coa`.`cnama` AS `crnoreknama`,`st1`.`nama` AS `crstatusnama`,`st2`.`nama` AS `crstatussebelumnyanama`,
        `u1`.`unama` AS `crinputusernama`,`u2`.`unama` AS `crmodifikasiusernama`');
        $db->selectFormatDate('crinputtgl, crmodifikasitgl');
        $db->getData($filter, $groupBy, $orderBy, $req->pageLimit, $req->pageNumber);

        $res->data = $db->result;

        $res->succes();
        selesai:
        return $res->done();
    }

    public function getDataByIdHistory(Request $req, Route $route)
    {
        $res = new Res($route->getActionName());

        $validator = Validator::make($req->all(), [
            'idtransaksi'   => 'required|numeric|min:0',
            'target'        => 'required',
        ]);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $idtransaksi = (int)$req->idtransaksi;
        $target = $req->target;
        $filter = "WHERE cridhistory = $idtransaksi";
        
        $sql = "
            SELECT  `cr`.*,`br`.`bnama` AS `crcabangnama`,`lc`.`lnama` AS `crlokasinama`,`c`.`kkode` AS `crkontakkode`,
                `c`.`knama` AS `crkontaknama`,`coa`.`cnama` AS `crnoreknama`,`st1`.`nama` AS `crstatusnama`,`st2`.`nama` AS `crstatussebelumnyanama`,
                `u1`.`unama` AS `crinputusernama`,`u2`.`unama` AS `crmodifikasiusernama`,`crd`.`idhistorydetail` AS `idhistorydetail`,
                `crd`.*,
                `coa1`.`cnama` AS `noreknama`,`cc`.`ccnama` AS `costcenternama`,`d`.`dnama` AS `divisinama`,`sd`.`sdnama` AS `subdivisinama`,
                `p`.`pnama` AS `proyeknama` 
            from `f2_cr_history` `cr` 
            join `f2_cr_detail_history` `crd` on `cr`.`cridhistory` = `crd`.`idhistory` 
            left join `f1_branch` `br` on `cr`.`crcabang` = `br`.`bkode` 
            left join `f1_location` `lc` on `cr`.`crlokasi` = `lc`.`lkode` 
            left join `f1_contact` `c` on `cr`.`crkontak` = `c`.`kid` 
            left join `f1_coa` `coa` on `cr`.`crnorek` = `coa`.`cnomor` 
            left join `f0_status` `st1` on `cr`.`crstatus` = `st1`.`kode` 
            left join `f0_status` `st2` on `cr`.`crstatussebelumnya` = `st2`.`kode` 
            left join `f0_user` `u1` on `cr`.`crinputuser` = `u1`.`userid` 
            left join `f0_user` `u2` on `cr`.`crmodifikasiuser` = `u2`.`userid` 
            left join `f1_coa` `coa1` on `crd`.`norek` = `coa1`.`cnomor` 
            left join `f1_cost_center` `cc` on `crd`.`costcenter` = `cc`.`cckode` 
            left join `f1_division` `d` on `crd`.`divisi` = `d`.`dkode` 
            left join `f1_subdivision` `sd` on `crd`.`subdivisi` = `sd`.`sdkode` 
            left join `f1_project` `p` on `crd`.`proyek` = `p`.`pkode` ";
        $sql .= $filter;

        $dt = DB::select($sql);

        $get_val = '';
        $utama = new \StdClass; $arrDetail = [];
        foreach($dt as $i => $arr)
        {
            $detail = new \StdClass; 

            foreach($arr as $col => $val)
            {
                // SET DATA UTAMA
                if($i == 0)
                    $utama->{$col} = $val;

                // SET DATA DETAIL
                $detail->{$col} = $val;
            }

            array_push($arrDetail, $detail);
        }

        $res->data = [$utama, $arrDetail];

        $res->succes();
        selesai:
        return $res->done();
    }
}