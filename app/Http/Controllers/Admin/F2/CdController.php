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

class CdController extends Controller
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
           'cdid'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'cdcabang'   => 'required|max:25',
           'cdlokasi'   => 'required|max:25',
           'cdsumber'   => 'required|max:10',
           'cdautonotransaksi'   => 'required|numeric|min:-127|max:127',
           'cdnotransaksi'   => 'required|max:50',
           'cdtgl'   => 'required|date',
           'cdkodepa'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'cdkontak'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
           'cdkontakperson'   => 'max:100',
           'cdnorek'   => 'required|max:25',
           'cduraian'   => 'max:250',
           'cdcatatan'   => 'max:250',
           'cdmatauang'   => 'required|max:25',
           'cdkurs'   => 'required|numeric',
           'cdjumlah'   => 'required|numeric',
           'cdjumlahvalas'   => 'required|numeric',
           'cdjumlahbayar'   => 'required|numeric',
           'cdjumlahbayarvalas'   => 'required|numeric',
           'cdstatusbayar'   => 'required|numeric|min:-127|max:127',
           'cdtgllunas'   => 'date',
           'cdstatus'   => 'required|numeric|min:-127|max:127',
           'cdstatussebelumnya'   => 'required|numeric|min:-127|max:127',
           'cdjmlrevisi'   => 'required|numeric|min:-2147483647|max:2147483647',
           'cdcetakanke'   => 'required|numeric|min:-127|max:127',
           'cdisclose'   => 'required|numeric|min:-2147483647|max:2147483647',
           'cdinputuser'   => 'numeric|min:-9223372036854775808|max:9223372036854775808',
           'cdinputtgl'   => 'required|date',
           'cdmodifikasiuser'   => 'numeric|min:-9223372036854775808|max:9223372036854775808',
           'cdmodifikasitgl'   => 'required|date',
           'cdposting'   => 'required|numeric|min:-127|max:127',
           'cdpostingtgl'   => 'required|date',
           'cdcustomtext1'   => 'max:250',
           'cdcustomtext2'   => 'max:250',
           'cdcustomtext3'   => 'max:250',
           'cdcustomtext4'   => 'max:250',
           'cdcustomtext5'   => 'max:250',
           'cdcustomint1'   => 'required|numeric|min:-2147483647|max:2147483647',
           'cdcustomint2'   => 'required|numeric|min:-2147483647|max:2147483647',
           'cdcustomint3'   => 'required|numeric|min:-2147483647|max:2147483647',
           'cdcustomdbl1'   => 'required|numeric',
           'cdcustomdbl2'   => 'required|numeric',
           'cdcustomdbl3'   => 'required|numeric',
           'cdcustomdate1'   => 'required|date',
           'cdcustomdate2'   => 'required|date',
           'cdcustomdate3'   => 'required|date']);
        if ($validator->fails()) { $res->fail($validator->messages()->all(), 409); goto selesai; }

        $detail = json_decode($req->detail); $msgDetail = [];
        foreach($detail as $no => $val)
        {
            $validator = Validator::make((array)$val, [
              'idcddetail'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
              'idcd'   => 'required|numeric|min:-9223372036854775808|max:9223372036854775808',
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
        switch($utama->cdstatus)
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
        $rsCekPeriode = Global_help::M2_Accounting_PeriodeCheck($utama->cdtgl, $utama->cdtgl);
        if(!$rsCekPeriode->success) { $res->fail($rsCekPeriode->errmessage); DB::rollBack(); goto selesai; }
        //  END OF CEK PERIODE AKUNTANSI ===========================

        // "CEK MATAUANG COA =======================================
        $rsCekCoa = ValidasiMatauangCOA($utama, "cdmatauang", "cdnorek", $detail, "norek");
        if(notEmpty($rsCekCoa)) 
        {
            $res->fail($rsCekCoa); DB::rollBack(); goto selesai;
        }
        // "END OF CEK MATAUANG COA ================================

        // "CEK COA WAJIB COST CENTER ==============================
        if ($utama->cdstatus == 2)
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
        $utama->cdjumlah = AsDataTableDSum($detail, "jumlah");
        $utama->cdjumlahvalas = AsDataTableDSum($detail, "jumlahvalas");
        // "END OF HITUNG TOTAL BERDASARKAN DATA DETAIL ============

        $res->data = new \StdClass;
        
        if (!$isUpdate)
        {
            if ($utama->cdautonotransaksi)
            {
                // "GENERATE NOTRANSAKSI =========================================
                $rsNotransaksi = nomor::M0_Notransaksi($utama->cdcabang, $utama->cdlokasi, $utama->cdsumber, $utama->cdtgl, $utama->cdsumber, 2);
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
                $notransaksi = $utama->cdnotransaksi;
            

            // "CEK NO TRANSAKSI ======================
            $dtCekNo = DB::select("SELECT COUNT(cdid) as jml FROM f2_cd WHERE cdnotransaksi='$notransaksi'");
            $cekNo = $dtCekNo[0]->jml;
            if ($cekNo > 0)
            {
                $res->fail("No. : '$notransaksi' - has been used."); DB::rollBack(); goto selesai;
            }
            // "END OF CEK NO TRANSAKSI ===============

            $get_col = ""; $get_val = "";
            $utama->cdnotransaksi = $notransaksi;
            $colNotContains = "cdid";
            $colDate = 'cdtgl, cdtgllunas, cdinputtgl, cdmodifikasitgl, cdpostingtgl, cdcustomdate1, cdcustomdate2, cdcustomdate3';
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

            $sql = "INSERT INTO f2_cd (" . $get_col . ") VALUES (". $get_val .")";
            DB::insert($sql);

            // Sql disesuaikan sendiri, untuk parameternya disesuaikan sendiri.
            $sql = "select cdid as transaksi from f2_cd where cdnotransaksi='" . $notransaksi . "' AND cdinputuser= '" . $userid . "' order by cdmodifikasitgl desc limit 1";
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
            $idtransaksi = $utama->cdid;
            $notransaksi = $utama->cdnotransaksi;

            // 'JIKA UPDATE CEK JML ROW PADA DATABASE
            $dtupdate = DB::select("SELECT COUNT(cdid) as jml, cdnotransaksi FROM f2_cd WHERE cdid='" . $idtransaksi . "' AND cdstatus NOT IN(2,3,4,7)");
            $rowUpdate = $dtupdate[0]->jml;

            if (notEmpty($rowUpdate))
            {
                if ($utama->cdautonotransaksi == 1 && $notransaksi == "Auto")
                {
                    // 'GENERATE NOTRANSAKSI =========================================
                    $rsNotransaksi = nomor::M0_Notransaksi($utama->cdcabang, $utama->cdlokasi, $utama->cdsumber, $utama->cdtgl, $utama->cdsumber, 2);
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
                if ($notransaksi != $dtupdate[0]->cdnotransaksi)
                {
                    $dtCekNo = DB::select("SELECT COUNT(cdid) as jml FROM f2_cd WHERE cdnotransaksi='$notransaksi'");
                    $cekNo = $dtCekNo[0]->jml;
                    if (notEmpty($cekNo))
                    {
                        $res->fail("No. : '$notransaksi' - has been used."."SELECT COUNT(cdid) as jml FROM f2_cd WHERE cdnotransaksi='$notransaksi'"); DB::rollBack(); goto selesai;
                    }
                }
                // 'END OF CEK NO TRANSAKSI ===============

                // 'SIMPAN HISTORY ========================
                insertHistory('f2_cd', 'cdid', 'idcd', $notransaksi, $idtransaksi);
                // 'END OF SIMPAN HISTORY ==================
                
                $get_val = '';
                $utama->cdnotransaksi = $notransaksi;
                $colNotContains = 'cdid';
                $colDate = 'cdtgl, cdtgllunas, cdinputtgl, cdmodifikasitgl, cdpostingtgl, cdcustomdate1, cdcustomdate2, cdcustomdate3';
                foreach($utama as $col => $val)
                {
                    if(!checkContains($colNotContains, $col))
                    {
                        $get_val .= $col . " = '". FixQuotes($val) ."', ";
                    }
                }
                $get_val = substr($get_val, 0, strlen($get_val)-2);

                $sql = "UPDATE f2_cd SET $get_val WHERE cdid = '$utama->cdid'";
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
            $sql = "DELETE FROM f2_cd_Detail where idcd = '$idtransaksi'";
            DB::delete($sql);
        }

        // 'Proses detail
        $colNotContains = "idcddetail, idcd";
        $get_col = ''; $get_val = '';
        foreach($detail as $i => $arr)
        {
            $get_val .= '(';
            $arr->idcd = $idtransaksi;
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
					if($col == 'idcd')
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
        $sql = "INSERT INTO f2_cd_Detail($get_col) VALUES $get_val"; 
        DB::insert($sql);

        // 'INSERT MSMQ JURNAL =================================================================
        $sumber = "cd"; $mdlid = 0; $mnid = 0; $jnsaktivitas = 0;
        if ($utama->cdstatus == 2)
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
        $sumber = "cd"; $tglTransaksi = "";
        $mdlid = 0; $mnid = 0; $jnsaktivitas = 0; $statusTransaksi = 0;
        // 'ambil moduleid, menuid dari m0_nomor dan tgl, notransaksi, status dari transaksi
        $dtdetail = DB::select("SELECT moduleid, menuid, 0 as statusTransaksi FROM f0_nomor WHERE kodetabel='" . $sumber . "' UNION SELECT cdtgl, cdnotransaksi, cdstatus FROM f2_cd WHERE cdid='" . $idtransaksi . "'");

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
            $nilaiStatus = "cdstatussebelumnya"; $jnsaktivitas = 17;
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
        insertHistory('f2_cd', 'cdid', 'idcd', $notransaksi, $idtransaksi);
        // 'END OF SIMPAN HISTORY ==================

        if ($isDelete)
        {
            // 'DELETE JURNAL
            $sql = "DELETE FROM f2_Transaction_Journal WHERE tsumber = 'cd' AND tidtransaksi = '" . $idtransaksi . "' AND tnotransaksi = '" . $notransaksi . "'";
            DB::delete($sql);
        }

        // 'update status utama
        $sql = "UPDATE f2_cd SET cdstatus = " . $nilaiStatus . ", cdmodifikasiuser='" . $userid . "', cdmodifikasitgl = NOW(), cdposting = 0, cdpostingtgl = '1971-01-01 00:00:00', cdjmlrevisi = cdjmlrevisi + 1 WHERE cdid = '" . $idtransaksi . "'";
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
        $sumber = "cd"; $notransaksi = ""; $mdlid = 0; $mnid = 0; $jnsaktivitas = 0;
        // 'ambil moduleid dan menuid dari m0_nomor
        $dtnomor = DB::select("SELECT moduleid, menuid FROM f0_nomor WHERE kodetabel='" . $sumber . "' UNION SELECT cdid, cdnotransaksi FROM f2_cd WHERE cdid='" . $idtransaksi . "'");
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
        $sql = "  SELECT cdcabang, cdlokasi, cdsumber, cdautonotransaksi, cdnotransaksi, cdtgl, cdstatus";
        $sql .= " FROM f2_cd";
        $sql .= " WHERE cdid = '" . $idtransaksi . "'";
        $dtNomorNext = DB::select($sql);
        if (notEmpty($dtNomorNext))
        {
            $notransaksi = $dtNomorNext[0]->cdnotransaksi;
            $status = $dtNomorNext[0]->cdstatus;

            if($status == 2 || $status == 3 || $status == 4 || $status == 7)
            {
                $res->fail("Can't delete No. : '$notransaksi' - it has been approved."); DB::rollBack(); goto selesai;
            }
            $cabang = $dtNomorNext[0]->cdcabang;
            $lokasi = $dtNomorNext[0]->cdlokasi; 
            $sumber = $dtNomorNext[0]->cdsumber;
            $autonotransaksi = (int)$dtNomorNext[0]->cdautonotransaksi;
            $tgl = $dtNomorNext[0]->cdtgl;
        }
        else
        {
            $res->fail("#2. Transaction data not found."); DB::rollBack(); goto selesai;
        }
        // 'END OF PERSIAPAN UPDATE NOMOR BERIKUTNYA ===========================================

        // 'DELETE JURNAL
        $sql = "DELETE FROM f2_Transaction_Journal WHERE tsumber = 'cd' AND tidtransaksi = '" . $idtransaksi . "' AND tnotransaksi = '" . $notransaksi . "'";
        DB::delete($sql);

        // 'DELETE DETAIL
        $sql = "DELETE FROM f2_cd_Detail WHERE idcd = '" . $idtransaksi . "'";
        DB::delete($sql);

        // 'DELETE UTAMA
        $sql = "DELETE FROM f2_cd WHERE cdid = '" . $idtransaksi . "'";
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
        $filter = "WHERE cdid = $idtransaksi";
        
        $sql = "
            SELECT 
                `cd`.* ,`br`.`bnama` AS `cdcabangnama`,`lc`.`lnama` AS `cdlokasinama`,`c`.`kkode` AS `cdkontakkode`,
                `c`.`knama` AS `cdkontaknama`,`coa`.`cnama` AS `cdnoreknama`,`st1`.`nama` AS `cdstatusnama`,`st2`.`nama` AS `cdstatussebelumnyanama`,
                `u1`.`unama` AS `cdinputusernama`,`u2`.`unama` AS `cdmodifikasiusernama`,`cdd`.*,
                `coa1`.`cnama` AS `noreknama`,`cc`.`ccnama` AS `costcenternama`,`d`.`dnama` AS `divisinama`,`sd`.`sdnama` AS `subdivisinama`,
                `p`.`pnama` AS `proyeknama` 
            from `f2_cd` `cd` 
            join `f2_cd_detail` `cdd` on `cd`.`cdid` = `cdd`.`idcd` 
            left join `f1_branch` `br` on `cd`.`cdcabang` = `br`.`bkode` 
            left join `f1_location` `lc` on `cd`.`cdlokasi` = `lc`.`lkode` 
            left join `f1_contact` `c` on `cd`.`cdkontak` = `c`.`kid` 
            left join `f1_coa` `coa` on `cd`.`cdnorek` = `coa`.`cnomor` 
            left join `f0_status` `st1` on `cd`.`cdstatus` = `st1`.`kode` 
            left join `f0_status` `st2` on `cd`.`cdstatussebelumnya` = `st2`.`kode` 
            left join `f0_user` `u1` on `cd`.`cdinputuser` = `u1`.`userid` 
            left join `f0_user` `u2` on `cd`.`cdmodifikasiuser` = `u2`.`userid` 
            left join `f1_coa` `coa1` on `cdd`.`norek` = `coa1`.`cnomor` 
            left join `f1_cost_center` `cc` on `cdd`.`costcenter` = `cc`.`cckode` 
            left join `f1_division` `d` on `cdd`.`divisi` = `d`.`dkode` 
            left join `f1_subdivision` `sd` on `cdd`.`subdivisi` = `sd`.`sdkode` 
            left join `f1_project` `p` on `cdd`.`proyek` = `p`.`pkode` ";
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
        
        $db = new ModelsDB('f2_cd AS cd');
        $db->leftJoin('f1_branch AS br', 'cd.cdcabang', '=' ,'br.bkode');
        $db->leftJoin('f1_location AS lc', 'cd.cdlokasi', '=' ,'lc.lkode');
        $db->leftJoin('f1_contact AS c', 'cd.cdkontak', '=' ,'c.kid');
        $db->leftJoin('f1_coa AS coa', 'cd.cdnorek', '=' ,'coa.cnomor');
        $db->leftJoin('f0_status AS st1', 'cd.cdstatus', '=' ,'st1.kode');
        $db->leftJoin('f0_status AS st2', 'cd.cdstatussebelumnya', '=' ,'st2.kode');
        $db->leftJoin('f0_user AS u1', 'cd.cdinputuser', '=' ,'u1.userid');
        $db->leftJoin('f0_user AS u2', 'cd.cdmodifikasiuser', '=' ,'u2.userid');
        $db->select('cd.*, br.bnama AS cdcabangnama, lc.lnama AS cdlokasinama,
                c.kkode AS cdkontakkode, c.knama AS cdkontaknama, coa.cnama AS cdnoreknama, st1.nama AS cdstatusnama,
                st2.nama AS cdstatussebelumnyanama, u1.unama AS cdinputusernama, u2.unama AS cdmodifikasiusernama');
        $db->selectFormatDate('cdinputtgl, cdmodifikasitgl');
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
            $res->data = "Related cd data not found."; 
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

        $db = new ModelsDB('f2_cd_history AS cd');
        $db->leftJoin('f1_branch AS br', 'cd.cdcabang', '=' ,'br.bkode');
        $db->leftJoin('f1_location AS lc', 'cd.cdlokasi', '=' ,'lc.lkode');
        $db->leftJoin('f1_contact AS c', 'cd.cdkontak', '=' ,'c.kid');
        $db->leftJoin('f1_coa AS coa', 'cd.cdnorek', '=' ,'coa.cnomor');
        $db->leftJoin('f0_status AS st1', 'cd.cdstatus', '=' ,'st1.kode');
        $db->leftJoin('f0_status AS st2', 'cd.cdstatussebelumnya', '=' ,'st2.kode');
        $db->leftJoin('f0_user AS u1', 'cd.cdinputuser', '=' ,'u1.userid');
        $db->leftJoin('f0_user AS u2', 'cd.cdmodifikasiuser', '=' ,'u2.userid');
        $db->select('`cd`.*,`br`.`bnama` AS `cdcabangnama`,`lc`.`lnama` AS `cdlokasinama`,`c`.`kkode` AS `cdkontakkode`,
        `c`.`knama` AS `cdkontaknama`,`coa`.`cnama` AS `cdnoreknama`,`st1`.`nama` AS `cdstatusnama`,`st2`.`nama` AS `cdstatussebelumnyanama`,
        `u1`.`unama` AS `cdinputusernama`,`u2`.`unama` AS `cdmodifikasiusernama`');
        $db->selectFormatDate('cdinputtgl, cdmodifikasitgl');
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
        $filter = "WHERE cdidhistory = $idtransaksi";
        
        $sql = "
            SELECT  `cd`.*,`br`.`bnama` AS `cdcabangnama`,`lc`.`lnama` AS `cdlokasinama`,`c`.`kkode` AS `cdkontakkode`,
                `c`.`knama` AS `cdkontaknama`,`coa`.`cnama` AS `cdnoreknama`,`st1`.`nama` AS `cdstatusnama`,`st2`.`nama` AS `cdstatussebelumnyanama`,
                `u1`.`unama` AS `cdinputusernama`,`u2`.`unama` AS `cdmodifikasiusernama`,`cdd`.`idhistorydetail` AS `idhistorydetail`,
                `cdd`.*,
                `coa1`.`cnama` AS `noreknama`,`cc`.`ccnama` AS `costcenternama`,`d`.`dnama` AS `divisinama`,`sd`.`sdnama` AS `subdivisinama`,
                `p`.`pnama` AS `proyeknama` 
            from `f2_cd_history` `cd` 
            join `f2_cd_detail_history` `cdd` on `cd`.`cdidhistory` = `cdd`.`idhistory` 
            left join `f1_branch` `br` on `cd`.`cdcabang` = `br`.`bkode` 
            left join `f1_location` `lc` on `cd`.`cdlokasi` = `lc`.`lkode` 
            left join `f1_contact` `c` on `cd`.`cdkontak` = `c`.`kid` 
            left join `f1_coa` `coa` on `cd`.`cdnorek` = `coa`.`cnomor` 
            left join `f0_status` `st1` on `cd`.`cdstatus` = `st1`.`kode` 
            left join `f0_status` `st2` on `cd`.`cdstatussebelumnya` = `st2`.`kode` 
            left join `f0_user` `u1` on `cd`.`cdinputuser` = `u1`.`userid` 
            left join `f0_user` `u2` on `cd`.`cdmodifikasiuser` = `u2`.`userid` 
            left join `f1_coa` `coa1` on `cdd`.`norek` = `coa1`.`cnomor` 
            left join `f1_cost_center` `cc` on `cdd`.`costcenter` = `cc`.`cckode` 
            left join `f1_division` `d` on `cdd`.`divisi` = `d`.`dkode` 
            left join `f1_subdivision` `sd` on `cdd`.`subdivisi` = `sd`.`sdkode` 
            left join `f1_project` `p` on `cdd`.`proyek` = `p`.`pkode` ";
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