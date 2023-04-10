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
    $category_id = $json_obj[data][0][rows][0][0][category_id];

    $category_id = $_GET['category_id'];
	if(!$category_id){$category_id = 1;}

    //企業マスタを読込み
    $sql = 'SELECT category_id,jan_code::numeric,name,unit_price,image1 FROM mst_product where category_id = '.$category_id.' order by jan_code' ;
    $companyContractList = getList($sql);

    $sFlg = 0;

    foreach($companyContractList as $data){

        $aryAddRow =  array(
	    'category_id' => $data['category_id'],// カテゴリーコード
            'n_code'      => $data['jan_code'],   // 商品コード
            'menu_name'   => $data['name'],       // 商品名
            'unit_price'  => $data['unit_price'], // 価格
            'unit_img'    => $data['image1']      // 画像1
//            'unit_img'  => 'M6l0WFOr51G96j7VHC91XfCbBf-UCk7r.jpg',   // 価格
        );
        $json_array[]=$aryAddRow;
    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
