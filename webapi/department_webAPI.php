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
    $cust_cd = $json_obj[data][0][rows][0][0][class_code];

    $id = '';

   $cust_cd = '0002';

    $cust_cd = $_GET['cust_cd']; 
   if(!$cust_cd){ $cust_cd = 1;}

   if($cust_cd == '1'){
      $id = '4';
   }else if($cust_cd == '2'){
      $id = '1,3,9,10,11';
   }else if($cust_cd == '3'){
      $id = '2,13,12';
   }else{
     $id = '6,14';
   }

    //企業マスタを読込み
    $sql = 'SELECT id::int,name FROM mst_product_category where id in ( '.$id.') order by id ' ;
    $companyContractList = getList($sql);

    $sFlg = 0;

    foreach($companyContractList as $data){
        $aryAddRow = array(
            'category_id'    => $data['id'], // 分類コード
            'category_name' => $data['name'], // 部門分類名
        );
        $json_array[]=$aryAddRow;
        

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
