<?php

    /**
     * @file      情報取得用WebApi
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      トップメッセージマスタテーブルの管理を行う
     */

    // DB情報を読み込み
    require_once("./common/DBAccess_Function2.php");

    //POSTからパラメータを取得
    $json_string = isset( $_POST['params'] )       === true ? $_POST['params']    : '';
    $json_obj = json_decode($json_string, true);

    //テーブル番号を取得
    $table_num = $json_obj[data][0][rows][0][0]['table_num'];

    //企業マスタを読込み
    $sql = 'SELECT * FROM order_history';
    $companyContractList = getList($sql);

    foreach($companyContractList as $data){
		$json_array['table_id'] = $data['table_id'];
		$json_array['n_code'] = $data['n_code'];
		$json_array['menu_name'] = $data['menu_name'];
		$json_array['menu_rank'] = $data['menu_rank'];
		$json_array['total_price'] = $data['total_price'];	
		$fulljson_array[] = $json_array;

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($fulljson_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
