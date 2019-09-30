<?php
namespace App\Helpers;

use DB;
use Validator;

class Nomor  
{
    static function M0_Notransaksi($cabang, $lokasi, $kodetabel, $tgl, $sumber = "", $smodule = 0, $matauang = "")
    {
        $notransaksi = ""; $mukodenotransaksi = "";
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

        // 'AMBIL SETTING, PAKAI SUMBER ATAU TIDAK
        $rsSetting = F_getSetting($smodule, $sgrup, $sumber . "NoTransactionSumber");
        $withSumber = (notEmpty($rsSetting)) ? $rsSetting : 1;

        // 'AMBIL SETTING, PAKAI TAHUN ATAU TIDAK
        $rsSetting = F_getSetting($smodule, $sgrup, $sumber . "NoTransactionTahun");
        $withTahun = (notEmpty($rsSetting)) ? $rsSetting : 1;

        // 'AMBIL SETTING, PAKAI BULAN ATAU TIDAK
        $rsSetting = F_getSetting($smodule, $sgrup, $sumber . "NoTransactionBulan");
        $withBulan = (notEmpty($rsSetting)) ? $rsSetting : 1;

        // 'AMBIL SETTING, RESET PERBULAN ATAU PERTAHUN
        $rsSetting = F_getSetting($smodule, $sgrup, $sumber . "NoTransactionPeriode");
        $resetBulan = (notEmpty($rsSetting)) ? $rsSetting : 1;

        // 'SET TAHUN, BULAN
        $get_tgl = get_date($tgl);
        $thn = $get_tgl->year;
        $bln = $get_tgl->month;

        $blnFilter = $bln;
        if ($resetBulan != 1) $blnFilter = "1";

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
             
        // 'AMBIL AWALAN, JMLDIGIT, NOBERIKUTNYA BERDASARKAN KODETABEL, CABANG, LOKASI, TAHUN, BULAN
        $sqlambil = "SELECT n.awalan, n.jmldigit, nb.noberikutnya FROM f0_nomor n JOIN f0_nomor_next nb ON n.kodetabel=SUBSTR(nb.kodetabel FROM 1 FOR length(nb.kodetabel) - length('" . $mukodenotransaksi . "')) WHERE n.kodetabel='" . $kodetabel . "' AND nb.kodetabel='" . $kodetabel . $mukodenotransaksi . "' AND nb.cabang='" . $cabang . "' AND nb.lokasi='" . $lokasi . "' AND nb.tahun='" . $thn . "' AND nb.bulan='" . $blnFilter . "'";
        $dt = DB::select($sqlambil);
        if (notEmpty($dt))
        {
            $awalan = $dt[0]->awalan . $mukodenotransaksi;
            $jmldigit = $dt[0]->jmldigit;
            $noberikutnya = $dt[0]->noberikutnya + 1;

            // 'SET SQL
            $sql = "UPDATE f0_Nomor_Next SET noberikutnya = '" . $noberikutnya . "' WHERE cabang='" . $cabang . "' AND lokasi='" . $lokasi . "' AND kodetabel='" . $kodetabel . $mukodenotransaksi . "' AND tahun='" . $thn . "' AND bulan='" . $blnFilter . "'";
        }
        else
        {
            // 'AMBIL AWALAN, JMLDIGIT BERDASARKAN KODETABEL
            $sqlambil = "SELECT awalan, jmldigit FROM f0_nomor WHERE kodetabel='" . $kodetabel . "'";
            $dt = DB::select($sqlambil);
            if (notEmpty($dt))
            {
                $awalan = $dt[0]->awalan . $mukodenotransaksi;
                $jmldigit = $dt[0]->jmldigit;
                $noberikutnya = 1;

                // 'SET SQL
                $sql = "Insert into f0_Nomor_Next (cabang, lokasi, kodetabel, tahun, bulan, noberikutnya) values('" . $cabang . "', '" . $lokasi . "', '" . $kodetabel . $mukodenotransaksi . "', " . $thn . ", " . $blnFilter . ", '2')";
            }
            else
            {
                $errmessage = "Could not find '" . $kodetabel . "' in f0_nomor."; goto selesai;
            }
        }

        // 'SET NOTRANSAKSI
        // 'coba Kawata
        if ($awalan == "SQ" || $awalan == "RI")
        {
            $notransaksi = ($withCabang == 1) ? $cabang : "";
            $notransaksi .= ($withLokasi == 1) ? $lokasi : "";
            $notransaksi .= ($withTahun == 1) ? $thn : "";
        }
        else
        {
            $notransaksi = ($withCabang == 1) ? $cabang : "";
            $notransaksi .= ($withLokasi == 1) ? $lokasi : "";
            $notransaksi .= ($withSumber == 1) ? $awalan : "";
            $notransaksi .= ($withTahun == 1) ? $thn : "";
        }

        // 'SET BULAN NOTRANSAKSI
        if ($withBulan == 1)
            if (notEmpty($bln))
                $notransaksi .= $bln;
            else
                $notransaksi .= "0" . $bln;

        // 'SET DIGIT NOTRANSAKSI
        $digit = $noberikutnya;
        for($i = strlen($digit) + 1; $i < strlen($digit) ; $jmldigit)
        {
            $digit = "0" . $digit;
        }

        $notransaksi .= $digit;

        $success = 1;
selesai:
        $res = new \StdClass;
        $res->success = $success;
        $res->errmessage = $errmessage;
        $res->notransaksi = $notransaksi;
        $res->sql = $sql;
        return $res;
    }
}