<?php

    //**************************************************************************
    //
    // 機能     :-1データ作成（自動化）
    //
    // 機能説明 :
    //
    // 備考     :
    //
    // 作成日　：2022/08/26　　　　　　 作成者　：バッタライ
    // 
    // 修正日　：　　　　　　　　　　　　修正者　：
    // 　　　　　　　　
    //**************************************************************************
    require_once("DBAccess_Function.php");

    // 日本の時間を設定
    date_default_timezone_set('Asia/Tokyo');

    // 日時取得
    $currentdatetime = date('ymdHis');

    // 空白
    $space           = str_pad('', 5);
    
    // ログ名
    $logname         = "Automatic_".$currentdatetime."log";
    
    // ログ出力パス
    $log_path        = "/var/www/log/".$logname;

    // 処理開始ログ出力
    error_log($currentdatetime . $space ."開始" . "\n", 3, $log_path);

    // 企業名→本来はiniファイルで管理する予定
    $schemas = ['ikkou','millionet_test'];
#     $schemas = ['millionet_test'];

    foreach ($schemas as $schema){
        
        // 商品マスタ-
        $mst0201 = mst0201($schema);
        // ログ出力
        error_log($currentdatetime . $space .$mst0201. "\n", 3, $log_path);
        // 仕入れ先マスタ-
        $mst1101 = mst1101($schema);
        // ログ出力
        error_log($currentdatetime . $space .$mst1101. "\n", 3, $log_path);
        // 部門マスタ-
        $mst1201 = mst1201($schema);
        // ログ出力
        error_log($currentdatetime . $space .$mst1201. "\n", 3, $log_path);
        // クレジットマスタ-
        $mst5601 = mst5601($schema);
        // ログ出力
        error_log($currentdatetime . $space .$mst5601. "\n", 3, $log_path);
        // メーカーマスタ-
        $mst1001 = mst1001($schema);
        // ログ出力
        error_log($currentdatetime . $space .$mst1001. "\n", 3, $log_path);
        // 商品分類マスター
        $mst0801 = mst0801($schema);
        // ログ出力
        error_log($currentdatetime . $space .$mst0801. "\n", 3, $log_path);
        // 担当者マスタ-
        $mst0601 = mst0601($schema);
        // ログ出力
        error_log($currentdatetime . $space .$mst0601. "\n", 3, $log_path);
        
    }
    error_log($currentdatetime . $space ."終了" . "\n", 3, $log_path);
    
    //**************************************************************************
    // 機能　　　：商品マスタ-1データ作成
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    
    function mst0201($schema) {

        try {
            
            // 削除
            $delsql =" delete from ".$schema.".mst0201 where organization_id = -1 ";
            sqlExec($delsql);

            // 登録
            $inssql  = "";
            $inssql .= " INSERT INTO ".$schema.".mst0201(  ";
            $inssql .= "    insuser_cd                     ";
            $inssql .= "    , insdatetime                  ";
            $inssql .= "    , upduser_cd                   ";
            $inssql .= "    , upddatetime                  ";
            $inssql .= "    , disabled                     ";
            $inssql .= "    , lan_kbn                      ";
            $inssql .= "    , connect_kbn                  ";
            $inssql .= "    , organization_id              ";
            $inssql .= "    , prod_cd                      ";
            $inssql .= "    , jan_cd                       ";
            $inssql .= "    , itf_cd                       ";
            $inssql .= "    , prod_nm                      ";
            $inssql .= "    , prod_kn                      ";
            $inssql .= "    , prod_kn_rk                   ";
            $inssql .= "    , prod_capa_kn                 ";
            $inssql .= "    , prod_capa_nm                 ";
            $inssql .= "    , disp_prod_nm1                ";
            $inssql .= "    , disp_prod_nm2                ";
            $inssql .= "    , case_piece                   ";
            $inssql .= "    , case_bowl                    ";
            $inssql .= "    , bowl_piece                   ";
            $inssql .= "    , sect_cd                      ";
            $inssql .= "    , priv_class_cd                ";
            $inssql .= "    , jicfs_class_cd               ";
            $inssql .= "    , maker_cd                     ";
            $inssql .= "    , head_supp_cd                 ";
            $inssql .= "    , shop_supp_cd                 ";
            $inssql .= "    , serial_no                    ";
            $inssql .= "    , prod_note                    ";
            $inssql .= "    , priv_prod_cd                 ";
            $inssql .= "    , alcohol_cd                   ";
            $inssql .= "    , alcohol_capa_amount          ";
            $inssql .= "    , order_lot                    ";
            $inssql .= "    , return_lot                   ";
            $inssql .= "    , just_stock_amout             ";
            $inssql .= "    , appo_prod_kbn                ";
            $inssql .= "    , appo_medi_kbn                ";
            $inssql .= "    , tax_type                     ";
            $inssql .= "    , order_stop_kbn               ";
            $inssql .= "    , noreturn_kbn                 ";
            $inssql .= "    , point_kbn                    ";
            $inssql .= "    , point_magni                  ";
            $inssql .= "    , p_calc_prod_point            ";
            $inssql .= "    , p_calc_prod_money            ";
            $inssql .= "    , auto_order_kbn               ";
            $inssql .= "    , risk_type_kbn                ";
            $inssql .= "    , order_fin_kbn                ";
            $inssql .= "    , resale_kbn                   ";
            $inssql .= "    , investiga_kbn                ";
            $inssql .= "    , disc_not_kbn                 ";
            $inssql .= "    , regi_duty_kbn                ";
            $inssql .= "    , set_prod_kbn                 ";
            $inssql .= "    , bundle_kbn                   ";
            $inssql .= "    , mixture_kbn                  ";
            $inssql .= "    , sale_period_kbn              ";
            $inssql .= "    , delete_kbn                   ";
            $inssql .= "    , order_can_kbn                ";
            $inssql .= "    , order_can_date               ";
            $inssql .= "    , prod_t_cd1                   ";
            $inssql .= "    , prod_t_cd2                   ";
            $inssql .= "    , prod_t_cd3                   ";
            $inssql .= "    , prod_t_cd4                   ";
            $inssql .= "    , prod_k_cd1                   ";
            $inssql .= "    , prod_k_cd2                   ";
            $inssql .= "    , prod_k_cd3                   ";
            $inssql .= "    , prod_k_cd4                   ";
            $inssql .= "    , prod_k_cd5                   ";
            $inssql .= "    , prod_k_cd6                   ";
            $inssql .= "    , prod_k_cd7                   ";
            $inssql .= "    , prod_k_cd8                   ";
            $inssql .= "    , prod_k_cd9                   ";
            $inssql .= "    , prod_k_cd10                  ";
            $inssql .= "    , prod_res_cd1                 ";
            $inssql .= "    , prod_res_cd2                 ";
            $inssql .= "    , prod_res_cd3                 ";
            $inssql .= "    , prod_res_cd4                 ";
            $inssql .= "    , prod_res_cd5                 ";
            $inssql .= "    , prod_res_cd6                 ";
            $inssql .= "    , prod_res_cd7                 ";
            $inssql .= "    , prod_res_cd8                 ";
            $inssql .= "    , prod_res_cd9                 ";
            $inssql .= "    , prod_res_cd10                ";
            $inssql .= "    , prod_comment                 ";
            $inssql .= "    , shelf_block1                 ";
            $inssql .= "    , shelf_block2                 ";
            $inssql .= "    , fixeprice                    ";
            $inssql .= "    , saleprice                    ";
            $inssql .= "    , saleprice_ex                 ";
            $inssql .= "    , base_saleprice               ";
            $inssql .= "    , base_saleprice_ex            ";
            $inssql .= "    , cust_saleprice               ";
            $inssql .= "    , head_costprice               ";
            $inssql .= "    , shop_costprice               ";
            $inssql .= "    , contract_price               ";
            $inssql .= "    , empl_saleprice               ";
            $inssql .= "    , spcl_saleprice1              ";
            $inssql .= "    , spcl_saleprice2              ";
            $inssql .= "    , saleprice_limit              ";
            $inssql .= "    , saleprice_ex_limit           ";
            $inssql .= "    , time_saleprice               ";
            $inssql .= "    , time_saleamount              ";
            $inssql .= "    , smp1_str_dt                  ";
            $inssql .= "    , smp1_end_dt                  ";
            $inssql .= "    , smp1_saleprice               ";
            $inssql .= "    , smp1_cust_saleprice          ";
            $inssql .= "    , smp1_costprice               ";
            $inssql .= "    , smp1_point_kbn               ";
            $inssql .= "    , smp2_str_dt                  ";
            $inssql .= "    , smp2_end_dt                  ";
            $inssql .= "    , smp2_saleprice               ";
            $inssql .= "    , smp2_cust_saleprice          ";
            $inssql .= "    , smp2_costprice               ";
            $inssql .= "    , smp2_point_kbn               ";
            $inssql .= "    , sales_talk_kbn               ";
            $inssql .= "    , sales_talk                   ";
            $inssql .= "    , switch_otc_kbn               ";
            $inssql .= "    , prod_tax                     ";
            $inssql .= "    , bb_date_kbn                  ";
            $inssql .= "    , cartone                      ";
            $inssql .= "    , bb_days                      ";
            $inssql .= "    , order_prod_cd1               ";
            $inssql .= "    , order_prod_cd2               ";
            $inssql .= "    , order_prod_cd3               ";
            $inssql .= "    , case_costprice               ";
            $inssql .= "    , specific_nm                  ";
            $inssql .= " )                                 ";
            $inssql .= " SELECT                            ";
            $inssql .= " distinct  on (prod_cd)            ";
            $inssql .= "    insuser_cd                     ";
            $inssql .= "    , insdatetime                  ";
            $inssql .= "    , upduser_cd                   ";
            $inssql .= "    , upddatetime                  ";
            $inssql .= "    , disabled                     ";
            $inssql .= "    , lan_kbn                      ";
            $inssql .= "    , connect_kbn                  ";
            $inssql .= "    , '-1'                         ";
            $inssql .= "    , prod_cd                      ";
            $inssql .= "    , jan_cd                       ";
            $inssql .= "    , itf_cd                       ";
            $inssql .= "    , prod_nm                      ";
            $inssql .= "    , prod_kn                      ";
            $inssql .= "    , prod_kn_rk                   ";
            $inssql .= "    , prod_capa_kn                 ";
            $inssql .= "    , prod_capa_nm                 ";
            $inssql .= "    , disp_prod_nm1                ";
            $inssql .= "    , disp_prod_nm2                ";
            $inssql .= "    , case_piece                   ";
            $inssql .= "    , case_bowl                    ";
            $inssql .= "    , bowl_piece                   ";
            $inssql .= "    , sect_cd                      ";
            $inssql .= "    , priv_class_cd                ";
            $inssql .= "    , jicfs_class_cd               ";
            $inssql .= "    , maker_cd                     ";
            $inssql .= "    , head_supp_cd                 ";
            $inssql .= "    , shop_supp_cd                 ";
            $inssql .= "    , serial_no                    ";
            $inssql .= "    , prod_note                    ";
            $inssql .= "    , priv_prod_cd                 ";
            $inssql .= "    , alcohol_cd                   ";
            $inssql .= "    , alcohol_capa_amount          ";
            $inssql .= "    , order_lot                    ";
            $inssql .= "    , return_lot                   ";
            $inssql .= "    , just_stock_amout             ";
            $inssql .= "    , appo_prod_kbn                ";
            $inssql .= "    , appo_medi_kbn                ";
            $inssql .= "    , tax_type                     ";
            $inssql .= "    , order_stop_kbn               ";
            $inssql .= "    , noreturn_kbn                 ";
            $inssql .= "    , point_kbn                    ";
            $inssql .= "    , point_magni                  ";
            $inssql .= "    , p_calc_prod_point            ";
            $inssql .= "    , p_calc_prod_money            ";
            $inssql .= "    , auto_order_kbn               ";
            $inssql .= "    , risk_type_kbn                ";
            $inssql .= "    , order_fin_kbn                ";
            $inssql .= "    , resale_kbn                   ";
            $inssql .= "    , investiga_kbn                ";
            $inssql .= "    , disc_not_kbn                 ";
            $inssql .= "    , regi_duty_kbn                ";
            $inssql .= "    , set_prod_kbn                 ";
            $inssql .= "    , bundle_kbn                   ";
            $inssql .= "    , mixture_kbn                  ";
            $inssql .= "    , sale_period_kbn              ";
            $inssql .= "    , delete_kbn                   ";
            $inssql .= "    , order_can_kbn                ";
            $inssql .= "    , order_can_date               ";
            $inssql .= "    , prod_t_cd1                   ";
            $inssql .= "    , prod_t_cd2                   ";
            $inssql .= "    , prod_t_cd3                   ";
            $inssql .= "    , prod_t_cd4                   ";
            $inssql .= "    , prod_k_cd1                   ";
            $inssql .= "    , prod_k_cd2                   ";
            $inssql .= "    , prod_k_cd3                   ";
            $inssql .= "    , prod_k_cd4                   ";
            $inssql .= "    , prod_k_cd5                   ";
            $inssql .= "    , prod_k_cd6                   ";
            $inssql .= "    , prod_k_cd7                   ";
            $inssql .= "    , prod_k_cd8                   ";
            $inssql .= "    , prod_k_cd9                   ";
            $inssql .= "    , prod_k_cd10                  ";
            $inssql .= "    , prod_res_cd1                 ";
            $inssql .= "    , prod_res_cd2                 ";
            $inssql .= "    , prod_res_cd3                 ";
            $inssql .= "    , prod_res_cd4                 ";
            $inssql .= "    , prod_res_cd5                 ";
            $inssql .= "    , prod_res_cd6                 ";
            $inssql .= "    , prod_res_cd7                 ";
            $inssql .= "    , prod_res_cd8                 ";
            $inssql .= "    , prod_res_cd9                 ";
            $inssql .= "    , prod_res_cd10                ";
            $inssql .= "    , prod_comment                 ";
            $inssql .= "    , shelf_block1                 ";
            $inssql .= "    , shelf_block2                 ";
            $inssql .= "    , fixeprice                    ";
            $inssql .= "    , saleprice                    ";
            $inssql .= "    , saleprice_ex                 ";
            $inssql .= "    , base_saleprice               ";
            $inssql .= "    , base_saleprice_ex            ";
            $inssql .= "    , cust_saleprice               ";
            $inssql .= "    , head_costprice               ";
            $inssql .= "    , shop_costprice               ";
            $inssql .= "    , contract_price               ";
            $inssql .= "    , empl_saleprice               ";
            $inssql .= "    , spcl_saleprice1              ";
            $inssql .= "    , spcl_saleprice2              ";
            $inssql .= "    , saleprice_limit              ";
            $inssql .= "    , saleprice_ex_limit           ";
            $inssql .= "    , time_saleprice               ";
            $inssql .= "    , time_saleamount              ";
            $inssql .= "    , smp1_str_dt                  ";
            $inssql .= "    , smp1_end_dt                  ";
            $inssql .= "    , smp1_saleprice               ";
            $inssql .= "    , smp1_cust_saleprice          ";
            $inssql .= "    , smp1_costprice               ";
            $inssql .= "    , smp1_point_kbn               ";
            $inssql .= "    , smp2_str_dt                  ";
            $inssql .= "    , smp2_end_dt                  ";
            $inssql .= "    , smp2_saleprice               ";
            $inssql .= "    , smp2_cust_saleprice          ";
            $inssql .= "    , smp2_costprice               ";
            $inssql .= "    , smp2_point_kbn               ";
            $inssql .= "    , sales_talk_kbn               ";
            $inssql .= "    , sales_talk                   ";
            $inssql .= "    , switch_otc_kbn               ";
            $inssql .= "    , prod_tax                     ";
            $inssql .= "    , bb_date_kbn                  ";
            $inssql .= "    , cartone                      ";
            $inssql .= "    , bb_days                      ";
            $inssql .= "    , order_prod_cd1               ";
            $inssql .= "    , order_prod_cd2               ";
            $inssql .= "    , order_prod_cd3               ";
            $inssql .= "    , case_costprice               ";
            $inssql .= "    , specific_nm                  ";
            $inssql .= " FROM                              ";
            $inssql .= "    ".$schema.".mst0201            ";
            $inssql .= " where                             ";
            $inssql .= " organization_id <> -1             ";
            $inssql .= " and organization_id <> 1          ";

            sqlExec($inssql);
            
            return 'mst0201:sucessfully created ';
            
        } catch (Exception $ex) {
            
            echo $ex;
            return $ex;
            
        }
    }
    //**************************************************************************
    // 機能　　　：仕入先マスタ-1データ作成
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function mst1101($schema){
        
        try{
            
            // 削除
            $delsql ="delete from ".$schema.".mst1101 where organization_id = -1 ";
            sqlExec($delsql);
            
            // 登録
            $inssql = "";
            $inssql .= " INSERT INTO ".$schema.".mst1101( ";
            $inssql .= "    insuser_cd                    ";
            $inssql .= "    , insdatetime                 ";
            $inssql .= "    , upduser_cd                  ";
            $inssql .= "    , upddatetime                 ";
            $inssql .= "    , disabled                    ";
            $inssql .= "    , lan_kbn                     ";
            $inssql .= "    , connect_kbn                 ";
            $inssql .= "    , organization_id             ";
            $inssql .= "    , supp_cd                     ";
            $inssql .= "    , supp_kbn                    ";
            $inssql .= "    , supp_nm                     ";
            $inssql .= "    , supp_kn                     ";
            $inssql .= "    , zip                         ";
            $inssql .= "    , addr1                       ";
            $inssql .= "    , addr2                       ";
            $inssql .= "    , addr3                       ";
            $inssql .= "    , tel                         ";
            $inssql .= "    , fax                         ";
            $inssql .= "    , pay_close_day1              ";
            $inssql .= "    , arr_need_day                ";
            $inssql .= "    , ord_form_kbn                ";
            $inssql .= "    , supp_deta_kbn               ";
            $inssql .= "    , kics_supp_cd                ";
            $inssql .= "    , noworder_no                 ";
            $inssql .= "    , order_line                  ";
            $inssql .= "    , biko                        ";
            $inssql .= "    , order_under_price           ";
            $inssql .= "    , fax_detail_cnt              ";
            $inssql .= "    , fax_denno                   ";
            $inssql .= "    , tax_calc_kbn                ";
            $inssql .= "    , tax_frac_kbn                ";
            $inssql .= "    , csv_denno                   ";
            $inssql .= "    , order_prod_kbn              ";
            $inssql .= "    , order_sp_cd                 ";
            $inssql .= " )                                ";
            $inssql .= " SELECT                           ";
            $inssql .= " distinct on (supp_cd)            ";
            $inssql .= "    insuser_cd                    ";
            $inssql .= "    , insdatetime                 ";
            $inssql .= "    , upduser_cd                  ";
            $inssql .= "    , upddatetime                 ";
            $inssql .= "    , disabled                    ";
            $inssql .= "    , lan_kbn                     ";
            $inssql .= "    , connect_kbn                 ";
            $inssql .= "    , '-1'                        ";
            $inssql .= "    , supp_cd                     ";
            $inssql .= "    , supp_kbn                    ";
            $inssql .= "    , supp_nm                     ";
            $inssql .= "    , supp_kn                     ";
            $inssql .= "    , zip                         ";
            $inssql .= "    , addr1                       ";
            $inssql .= "    , addr2                       ";
            $inssql .= "    , addr3                       ";
            $inssql .= "    , tel                         ";
            $inssql .= "    , fax                         ";
            $inssql .= "    , pay_close_day1              ";
            $inssql .= "    , arr_need_day                ";
            $inssql .= "    , ord_form_kbn                ";
            $inssql .= "    , supp_deta_kbn               ";
            $inssql .= "    , kics_supp_cd                ";
            $inssql .= "    , noworder_no                 ";
            $inssql .= "    , order_line                  ";
            $inssql .= "    , biko                        ";
            $inssql .= "    , order_under_price           ";
            $inssql .= "    , fax_detail_cnt              ";
            $inssql .= "    , fax_denno                   ";
            $inssql .= "    , tax_calc_kbn                ";
            $inssql .= "    , tax_frac_kbn                ";
            $inssql .= "    , csv_denno                   ";
            $inssql .= "    , order_prod_kbn              ";
            $inssql .= "    , order_sp_cd                 ";
            $inssql .= " FROM                             ";
            $inssql .= "    ".$schema.".mst1101           ";
            $inssql .= " where                            ";
            $inssql .= " organization_id <>-1             ";
            $inssql .= " and organization_id <> 1         ";
            
            sqlExec($inssql);
            
            return 'mst1101:sucessfully created ';
            
        } catch (Exception $ex){
            return $ex;
        }
    }
    //**************************************************************************
    // 機能　　　：部門マスタ-1データ作成
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function mst1201($schema){
        
        try{
            
            // 削除
            $delsql ="delete from ".$schema.".mst1201 where organization_id = -1 ";
            sqlExec($delsql);
            
            // 登録
            $inssql .= " INSERT INTO ".$schema.".mst1201( ";
            $inssql .= "        insuser_cd                   ";
            $inssql .= "        , insdatetime                ";
            $inssql .= "        , upduser_cd                 ";
            $inssql .= "        , upddatetime                ";
            $inssql .= "        , disabled                   ";
            $inssql .= "        , lan_kbn                    ";
            $inssql .= "        , connect_kbn                ";
            $inssql .= "        , organization_id            ";
            $inssql .= "        , sect_cd                    ";
            $inssql .= "        , sect_nm                    ";
            $inssql .= "        , sect_kn                    ";
            $inssql .= "        , prod_cd                    ";
            $inssql .= "        , type_cd                    ";
            $inssql .= "        , sect_profit                ";
            $inssql .= "        , tax_type                   ";
            $inssql .= "        , sect_tax                   ";
            $inssql .= "        , point_kbn                  ";
            $inssql .= "        , sect_prate                 ";
            $inssql .= "        , sect_disc_rate             ";
            $inssql .= "        , cust_disc_rate             ";
            $inssql .= "        , empl_kbn                   ";
            $inssql .= "        , disc_not_kbn               ";
            $inssql .= "        , today_sale_prt             ";
            $inssql .= "    )                                ";
            $inssql .= "    SELECT distinct                  ";
            $inssql .= "         on (sect_cd)                ";
            $inssql .= "         insuser_cd                  ";
            $inssql .= "         , insdatetime               ";
            $inssql .= "         , upduser_cd                ";
            $inssql .= "         , upddatetime               ";
            $inssql .= "         , disabled                  ";
            $inssql .= "         , lan_kbn                   ";
            $inssql .= "         , connect_kbn               ";
            $inssql .= "         , '-1'                      ";
            $inssql .= "         , sect_cd                   ";
            $inssql .= "         , sect_nm                   ";
            $inssql .= "         , sect_kn                   ";
            $inssql .= "         , prod_cd                   ";
            $inssql .= "         , type_cd                   ";
            $inssql .= "         , sect_profit               ";
            $inssql .= "         , tax_type                  ";
            $inssql .= "         , sect_tax                  ";
            $inssql .= "         , point_kbn                 ";
            $inssql .= "         , sect_prate                ";
            $inssql .= "         , sect_disc_rate            ";
            $inssql .= "         , cust_disc_rate            ";
            $inssql .= "         , empl_kbn                  ";
            $inssql .= "         , disc_not_kbn              ";
            $inssql .= "         , today_sale_prt            ";
            $inssql .= "         FROM                        ";
            $inssql .= "          ".$schema.".mst1201        ";
            $inssql .= "         where                       ";
            $inssql .= "         organization_id <> - 1      ";
            $inssql .= "         and organization_id <> 1    ";
            
            
            sqlExec($inssql);
            
           return 'mst1201:sucessfully created ';
            
        } catch (Exception $ex){
            
            return $ex;
        }
    }
    //**************************************************************************
    // 機能　　　：クレジットマスタ-1データ作成
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function mst5601($schema){
        
        try{
            
            // 削除
            $delsql ="delete from ".$schema.".mst5601 where organization_id = -1 ";
            sqlExec($delsql);
            
            // 登録
            $inssql  = "";
            $inssql .= " INSERT INTO ".$schema.".mst5601( ";
            $inssql .= "        insuser_cd           ";
            $inssql .= "        , insdatetime        ";
            $inssql .= "        , upduser_cd         ";
            $inssql .= "        , upddatetime        ";
            $inssql .= "        , disabled           ";
            $inssql .= "        , lan_kbn            ";
            $inssql .= "        , connect_kbn        ";
            $inssql .= "        , organization_id    ";
            $inssql .= "        , credit_cd          ";
            $inssql .= "        , credit_nm          ";
            $inssql .= "        , credit_kn          ";
            $inssql .= "        , credit_kbn         ";
            $inssql .= "        , add_prate          ";
            $inssql .= "        , refund_kbn         ";
            $inssql .= "    )                        ";
            $inssql .= "    SELECT                   ";
            $inssql .= "    distinct  on (credit_cd) ";
            $inssql .= "        insuser_cd           ";
            $inssql .= "        , insdatetime        ";
            $inssql .= "        , upduser_cd         ";
            $inssql .= "        , upddatetime        ";
            $inssql .= "        , disabled           ";
            $inssql .= "        , lan_kbn            ";
            $inssql .= "        , connect_kbn        ";
            $inssql .= "        , '-1'               ";
            $inssql .= "        , credit_cd          ";
            $inssql .= "        , credit_nm          ";
            $inssql .= "        , credit_kn          ";
            $inssql .= "        , credit_kbn         ";
            $inssql .= "        , add_prate          ";
            $inssql .= "        , refund_kbn         ";
            $inssql .= "    FROM                     ";
            $inssql .= "        ".$schema.".mst5601  ";
            $inssql .= "    where                    ";
            $inssql .= "    organization_id <>-1     ";
            $inssql .= "    and organization_id <> 1 ";
            
            sqlExec($inssql);
            
            return 'mst5601:sucessfully created ';
            
        } catch (Exception $ex){
            return $ex;
        }
    }
    //**************************************************************************
    // 機能　　　：メーカーマスタマスタ-1データ作成
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function mst1001($schema){
        
        try{
            
            // 削除
            $delsql ="delete from ".$schema.".mst1001 where organization_id = -1 ";
            sqlExec($delsql);
            
            // 登録
            $inssql  = "";
            $inssql .= " INSERT INTO ".$schema.".mst1001( ";
            $inssql .= "        insuser_cd           ";
            $inssql .= "        , insdatetime        ";
            $inssql .= "        , upduser_cd         ";
            $inssql .= "        , upddatetime        ";
            $inssql .= "        , disabled           ";
            $inssql .= "        , lan_kbn            ";
            $inssql .= "        , connect_kbn        ";
            $inssql .= "        , organization_id    ";
            $inssql .= "        , maker_cd           ";
            $inssql .= "        , maker_nm           ";
            $inssql .= "        , maker_nm_rk        ";
            $inssql .= "        , maker_kn           ";
            $inssql .= "        , maker_kn_rk        ";
            $inssql .= "    )                        ";
            $inssql .= "    SELECT                   ";
            $inssql .= "    distinct  on (maker_cd)  ";
            $inssql .= "        insuser_cd           ";
            $inssql .= "        , insdatetime        ";
            $inssql .= "        , upduser_cd         ";
            $inssql .= "        , upddatetime        ";
            $inssql .= "        , disabled           ";
            $inssql .= "        , lan_kbn            ";
            $inssql .= "        , connect_kbn        ";
            $inssql .= "        , '-1'               ";
            $inssql .= "        , maker_cd           ";
            $inssql .= "        , maker_nm           ";
            $inssql .= "        , maker_nm_rk        ";
            $inssql .= "        , maker_kn           ";
            $inssql .= "        , maker_kn_rk        ";
            $inssql .= "    FROM                     ";
            $inssql .= "        ".$schema.".mst1001  ";
            $inssql .= "    where                    ";
            $inssql .= "    organization_id <>-1     ";
            $inssql .= "    and organization_id <> 1 ";
            sqlExec($inssql);
            
            return 'mst1001:sucessfully created ';
            
        } catch (Exception $ex){
            
            return $ex;
        }
    }
    //**************************************************************************
    // 機能　　　：商品分類マスタマスタ-1データ作成
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function mst0801($schema){
        
        try{
            
            // 削除
            $delsql =" delete from ".$schema.".mst0801 where organization_id = -1 ";
            sqlExec($delsql);
            
            // 登録
            $inssql  = "";
            $inssql .= " INSERT INTO ".$schema.".mst0801( ";
            $inssql .= "        insuser_cd            ";
            $inssql .= "        , insdatetime         ";
            $inssql .= "        , upduser_cd          ";
            $inssql .= "        , upddatetime         ";
            $inssql .= "        , disabled            ";
            $inssql .= "        , lan_kbn             ";
            $inssql .= "        , connect_kbn         ";
            $inssql .= "        , organization_id     ";
            $inssql .= "        , prod_t_type         ";
            $inssql .= "        , prod_t_cd1          ";
            $inssql .= "        , prod_t_cd2          ";
            $inssql .= "        , prod_t_cd3          ";
            $inssql .= "        , prod_t_cd4          ";
            $inssql .= "        , prod_t_nm           ";
            $inssql .= "        , prod_t_kn           ";
            $inssql .= "    )                         ";
            $inssql .= "    SELECT                    ";
            $inssql .= "    distinct  on (prod_t_cd1) ";
            $inssql .= "        insuser_cd            ";
            $inssql .= "        , insdatetime         ";
            $inssql .= "        , upduser_cd          ";
            $inssql .= "        , upddatetime         ";
            $inssql .= "        , disabled            ";
            $inssql .= "        , lan_kbn             ";
            $inssql .= "        , connect_kbn         ";
            $inssql .= "        , '-1'                ";
            $inssql .= "        , prod_t_type         ";
            $inssql .= "        , prod_t_cd1          ";
            $inssql .= "        , prod_t_cd2          ";
            $inssql .= "        , prod_t_cd3          ";
            $inssql .= "        , prod_t_cd4          ";
            $inssql .= "        , prod_t_nm           ";
            $inssql .= "        , prod_t_kn           ";
            $inssql .= "    FROM                      ";
            $inssql .= "        ".$schema.".mst0801   ";
            $inssql .= "    where                     ";
            $inssql .= "    organization_id <>-1      ";
            $inssql .= "    and organization_id <> 1  ";
            
            sqlExec($inssql);
            
            return 'mst0801:sucessfully created ';
            
        } catch (Exception $ex){
            
            return $ex;
        }
    } 
    //**************************************************************************
    // 機能　　　：担当者マスタ-1データ作成
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function mst0601($schema){
        
        try{
            
            // 削除
            $delsql =" delete from ".$schema.".mst0601 where organization_id = -1 ";
            sqlExec($delsql);
            
            // 登録
            $inssql  = "";
            $inssql .= " INSERT INTO ".$schema.".mst0601( ";
            $inssql .= "        insuser_cd           ";
            $inssql .= "        , insdatetime        ";
            $inssql .= "        , upduser_cd         ";
            $inssql .= "        , upddatetime        ";
            $inssql .= "        , disabled           ";
            $inssql .= "        , lan_kbn            ";
            $inssql .= "        , connect_kbn        ";
            $inssql .= "        , organization_id    ";
            $inssql .= "        , staff_cd           ";
            $inssql .= "        , staff_nm           ";
            $inssql .= "        , staff_kn           ";
            $inssql .= "        , employee_cd        ";
            $inssql .= "        , employee_kbn       ";
            $inssql .= "        , supp_cd            ";
            $inssql .= "    )                        ";
            $inssql .= "    SELECT                   ";
            $inssql .= "    distinct  on (staff_cd)  ";
            $inssql .= "        insuser_cd           ";
            $inssql .= "        , insdatetime        ";
            $inssql .= "        , upduser_cd         ";
            $inssql .= "        , upddatetime        ";
            $inssql .= "        , disabled           ";
            $inssql .= "        , lan_kbn            ";
            $inssql .= "        , connect_kbn        ";
            $inssql .= "        , '-1'               ";
            $inssql .= "        , staff_cd           ";
            $inssql .= "        , staff_nm           ";
            $inssql .= "        , staff_kn           ";
            $inssql .= "        , employee_cd        ";
            $inssql .= "        , employee_kbn       ";
            $inssql .= "        , supp_cd            ";
            $inssql .= "    FROM                     ";
            $inssql .= "        ".$schema.".mst0601  ";
            $inssql .= "    where                    ";
            $inssql .= "    organization_id <>-1     ";
            $inssql .= "    and organization_id <> 1 ";
            
            sqlExec($inssql);
            
            return 'mst0601:sucessfully created ';
            
        } catch (Exception $ex){
            
            return $ex;
        }
    } 
?>
