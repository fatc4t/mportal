<?php

/* * *****************************************************************************
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :豊興一括物流データ作成処理
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/07/29
  '*  作成者      :バッタライ
  '*
  '*----------------------------------------------------------------------------
  '*  修正履歴 
  '*  修正日      修正者  修正内容
  '*
  '*
  '**************************************************************************** */

require_once("create_engine_as400_order_Function.php");

// 豊興店舗コード
$base_firm_cust_cd = '131491';
// ベンダーコードを取得
$online_cd_list = get_houkou_list($base_firm_cust_cd);
// ループ開始
foreach ($online_cd_list as $val) {

    $online_cd = $val['online_cd'];

    // ファイルを取得
    $strOutBuf = get_txt_file($online_cd);

    // データ出力
     PutFileOrderDat($base_firm_cust_cd, $strOutBuf);
}

//**************************************************************************
//
// 機能     :対処の発注データファイルを取得
//
// 機能説明 :パラメータ：卸コード/ベンダーコード
//
// 備考     :(@)
//
//**************************************************************************    

function get_txt_file($online_cd) {

    // 納品確定データ元ファイルパス
    $path = '/var/www/as_400/order/' . $online_cd . '/';
    // 納品データファイル名
    $filename = $online_cd . '.TXT';
    // ファイルパスとファイル名
    $strDatCnvFile = $path . $filename;
    // ファイルを取得
    $buffer = file_get_contents($strDatCnvFile);
    return $buffer;
}
?>