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
            'now_point' => '981',
            'p_limit_dt' => '2019/12/31',
            'rank' =>  'シルバー',
            'pointup_conditions1' => '8',
            'pointup_conditions2' => '1000',
            'pointup_conditions3' => '5632',
        );
		$json_array[] = $aryAddRow;
    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
