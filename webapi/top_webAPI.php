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

    //企業マスタを読込み
    $sql = 'SELECT * FROM company ';
    $companyContractList = getList($sql);

    foreach($companyContractList as $data){
		$json_array['btnType'] = $data['fax'];
		$json_array['noticeCount'] = '3';
		$json_array['couponCount'] = '2';
		$json_array['recommendCount'] = '4';
		$json_array['address'] = $data['address'];
		$json_array['address2'] = $data['address2'];
		$json_array['app_content_background_color'] = $data['app_content_background_color'];
		$json_array['app_content_character_color'] = $data['app_content_character_color'];
		$json_array['app_footer_background_color'] = $data['app_footer_background_color'];
		$json_array['app_footer_character_color'] = $data['app_footer_character_color'];
		$json_array['app_header_background_color'] = $data['app_header_background_color'];
		$json_array['app_header_character_color'] = $data['app_header_character_color'];
		$json_array['company_code'] = $data['company_code'];
		$json_array['created_at'] = $data['created_at'];
		$json_array['created_by'] = $data['created_by'];
		$json_array['del_flg'] = $data['del_flg'];
		$json_array['email'] = $data['email'];
		$json_array['fax'] = $data['fax'];
		$json_array['home_page'] = $data['home_page'];
		$json_array['id'] = $data['id'];
		$json_array['name'] = $data['name'];
		$json_array['name_kana'] = $data['name_kana'];
		$json_array['post_code'] = $data['post_code'];
		$json_array['tel'] = $data['tel'];
		$json_array['updated_at'] = $data['updated_at'];
		$json_array['updated_by'] = $data['updated_by'];	

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
