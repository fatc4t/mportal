<?php

    /**
     * @file      情報取得用WebApi
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      トップメッセージマスタテーブルの管理を行う
     */

    // DB情報を読み込み
    require_once("./common/DBAccess_Function.php");

    //POSTからパラメータを取得
    $json_string = isset( $_POST['params'] )       === true ? $_POST['params']    : '';
    $json_obj = json_decode($json_string, true);

    //顧客IDを取得
    $cust_cd = $json_obj[data][0][rows][0][0][cust_cd];

    //企業マスタを読込み
    $sql = 'SELECT * FROM jinseido.mst0101 limit 1';
    $companyContractList = getList($sql);

    foreach($companyContractList as $data){
		
        $aryAddRow = array(
            'proc_date' => '2019/06/22',
            'use_store_name' => '店舗D',
            'use_Contents' =>  'ポイント支払',
            'point' => '20P',
        );
		$json_array[] = $aryAddRow;
		
        $aryAddRow =  array(
            'proc_date' => '2019/05/14',
            'use_store_name' => '店舗C',
            'use_Contents' =>  'ポイント取得',
            'point' => '63P',
        );

        $json_array[] = $aryAddRow;

        $aryAddRow =  array(
            'proc_date' => '2019/05/03',
            'use_store_name' => '店舗A',
            'use_Contents' =>  'ポイント取得',
            'point' => '31P',
        );

        $json_array[] = $aryAddRow;

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);

    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
