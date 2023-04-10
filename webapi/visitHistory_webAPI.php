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

    //顧客IDを取得
    $cust_cd = $json_obj[data][0][rows][0][0][cust_cd];

    $cust_cd = $_GET['cust_cd'];
        if(!$cust_cd){$cust_cd = '2000120000018';}

    //企業マスタを読込み
    $sql = 'select process_date || process_time as process_date, point_use_money, point_add, total::numeric from mst_order where customer_jan_code = '.$cust_cd.'order by process_date';
    $companyContractList = getList($sql);

    foreach($companyContractList as $data){
		
        $aryAddRow =  array(
            'process_date'    => $data['process_date'],    // 来店日
            'point_add'       => $data['point_add'],       // 取得ポイント
            'point_use_money' => $data['point_use_money'], // 使用ポイント
            'total'           => $data['total'],           // 支払合計金額
        );

        $json_array[] = $aryAddRow;

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);

    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
