<?php
/*******************************************************************************
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :分散処理-納品確定データ
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/02/20
  '*  作成者      :バッタライ
  '*
  '*----------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日      修正者  修正内容
  '*
  '*
  '*****************************************************************************/

    require_once("DBAccess_Function.php");

    // 納品確定データ取得
    $datas     = get_txt_file();
    // ヘッダー行「A」
    $needle    = 'A';
    // 初期化
    $offset    = 0;
    // 配列初期化
    $result    = array();
    // 変数初期化
    $file_name = '';
    // 変数初期化
    $IN_strBuf = '';
    // trueの場合
    while (true) {
        // ヘッダーに存在するAのバイト数取得
        $pos     = mb_strpos($datas, $needle, $offset);
        // false　の場合ブレーキ
        if ($pos === false) {
            // 最後の1回分を格納する 2021/08/14 add oota
            $result[] = substr($datas, $offset - 1);           
            break;
        } else {
            // 納品確定データを分析し、配列に入れる
            //$result[] = substr($datas, $offset - 1, $pos - $offset1);
            //最後の1バイトが欠落していたので修正 2021/08/14 edit oota
            $result[] = substr($datas, $offset - 1, $pos - $offset + 1);
            // 変数の値をAのバイト数より1足す
            $offset   = $pos + 1;
            
        }
    }

    // ループ回しながらコード変換テーブルを取得し、データ生成へパラメータを渡す
    foreach ($result as $key => $val) {
        
        // データ存在しない場合は次へ
        
        if (!$val) {
            continue;
        }
        
        // 元ベンダーコード取得
        $strBef_Online_cd = mb_substr($val, 31, 6);

        // ベンダーコード変換マスタ取得し、ファイル名とフォルダー名を指定する
        $file_name = get_m_change_shop($strBef_Online_cd);

        // Kヘッダーを追加 2021/08/14 add oota 
        $khead = 'K01'.date('ymdHis').date('ymd').'000001  '.$file_name.'          128000000000000080                                                                                                                                                                                                 ';

        // 分析したデータを変数に代入する
        $IN_strBuf = $khead.$val;
        // 納品確定データ生成
        PutFileDat($file_name, $IN_strBuf);
    }

    //**************************************************************************
    //
    // 機能     :納品確定データファイル読み込み
    //
    // 機能説明 :
    //
    // 備考     :AS400ダウンサイジング
    //
    //**************************************************************************

    function get_txt_file() {

        // 納品確定データ元ファイルパス
        $path = '/var/www/as_400/delivery_rec/';
        // 納品データファイル名
        $filename = 'J8.txt';
        // ファイルパスとファイル名
        $strDatCnvFile = $path . $filename;
        //ファイルオープン
        $fp = fopen($strDatCnvFile, "r");

        if ($fp) {
            if (flock($fp, LOCK_SH)) {
                while ((!feof($fp))) {
                    // ファイルを取得
                    $buffer = fgets($fp);
                }
            }
        }
        fclose($fp);
        // 2021/12/06 bhattarai バックアップ対応
        // バックアップパス
        $des_path = '/var/www/as_400/delivery_back/J8_'.date('ymdHis').'.txt';
        // バックアップへ移動する
        rename($path.'J8.txt', $des_path);

        return $buffer;
    }

    //**************************************************************************
    //
    // 機能     :納品確定データ分析
    //
    // 機能説明 :
    //
    // 備考     :AS400ダウンサイジング
    //
    //**************************************************************************

    function PutFileDat($file_name, $strOutBuf) {

        //納品確定データファイル保存パス（ベンダー用）
        $dir = "/var/www/as_400/delivery_send/" . $file_name;

        //フォルダーが存在しない場合、ベンダー毎のフォルダーを作成
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($strOutBuf === '') {
            return false;
        }

        $path = $dir;

        $strDatCnvFile = $path . '/' . $file_name . '.TXT';

        //ファイルオープン(追加）       
        $fp = fopen($strDatCnvFile, "a");
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                if (fwrite($fp, $strOutBuf) === false) {
                    
                }
                //ロック解除
                flock($fp, LOCK_UN);
            } else {
                //何にもしない
                //NOP
            }
            //ファイルクローズ
            fclose($fp);
        }
        if (file_exists($strDatCnvFile) === true) {
            chown($strDatCnvFile, 'apache');
            chgrp($strDatCnvFile, 'apache');
        }
        return true;
    }

    //**************************************************************************
    //
    // 機能     :納品確定データ
    //
    // 機能説明 :ベンダーコード変換マスタ取得
    //
    // 備考     :(@)
    //
    //**************************************************************************

    function get_m_change_shop($tot_ord_cd) {

        $rows_shop = [];

        $sql = "";
        $sql .= "select                    ";
        $sql .= " del_conf_cd              ";
        $sql .= "from public.m_change_shop ";
        $sql .= "where ";
        $sql .= "tot_ord_cd = " . "'" . $tot_ord_cd . "'";
        //　SQL　実行
        $rows_shop = getList($sql);

        foreach ($rows_shop as $row_shop => $val) {
            // 変換後ベンダーコードを返す
            return $val['del_conf_cd'];
        }
    }
?>
