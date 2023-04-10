<?php
    /**
     * @file      POS予約商品マスタ(MST0211)
     * @author    川橋
     * @date      2019/01/21
     * @version   1.00
     * @note      POS予約商品マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * POS予約商品マスタ(MST0211)クラス
     * @note   POS予約商品マスタテーブルの管理を行う。
     */
    class Mst0211 extends BaseModel
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ModelBaseのデストラクタ
            parent::__destruct();
        }
        public function get_jicfs_data($searchArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_jicfs_data");
            $where ="";
            if( $searchArray['prod_cd'] !== '' )
            {
                $where .= " where jan_cd ='". $searchArray['prod_cd']."'";
            }else{
                $where .= " where jan_cd = '' ";   
            }
            $sql  = " SELECT   * from public.m_jicfs_jan_basic " ;
            $sql  .=   $where ;              
               // print_r($sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            //print_r($result);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }

            $Log->trace("END get_jicfs_data");

            return $Datas;
        }        
        
//ADDKANDERU
  public function gettenpolist( $prod_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START gettenpolist");

                // 商品マスタ
                $tbl_name = 'mst0201';
            $sql  = "SELECT";
            $sql .= "   v.abbreviated_name as org_nm ";
            $sql .= " ,  $tbl_name.insuser_cd";
            $sql .= " , $tbl_name.insdatetime";
            $sql .= " , $tbl_name.upduser_cd";
            $sql .= " , $tbl_name.upddatetime";
            $sql .= " , $tbl_name.disabled";
            $sql .= " , $tbl_name.lan_kbn";
         //  $sql .= " , $tbl_name.organization_id";
            $sql .= " , $tbl_name.prod_cd";
            $sql .= " , $tbl_name.jan_cd";
            $sql .= " , $tbl_name.itf_cd";
            $sql .= " , $tbl_name.prod_nm";
            $sql .= " , $tbl_name.prod_kn";
            $sql .= " , $tbl_name.prod_kn_rk";
            $sql .= " , $tbl_name.prod_capa_kn";
            $sql .= " , $tbl_name.prod_capa_nm";
            $sql .= " , $tbl_name.disp_prod_nm1";
            $sql .= " , $tbl_name.disp_prod_nm2";
            $sql .= " , TO_CHAR($tbl_name.case_piece,             'FM999,999,990')    AS case_piece";
            $sql .= " , TO_CHAR($tbl_name.case_bowl,              'FM999,999,990')    AS case_bowl";
            $sql .= " , TO_CHAR($tbl_name.bowl_piece,             'FM999,999,990')    AS bowl_piece";
            $sql .= " , $tbl_name.sect_cd";
            $sql .= " , $tbl_name.priv_class_cd";
            $sql .= " , $tbl_name.jicfs_class_cd";
            $sql .= " , $tbl_name.maker_cd";
            $sql .= " , $tbl_name.head_supp_cd";
            $sql .= " , $tbl_name.shop_supp_cd";
            $sql .= " , $tbl_name.serial_no";
            $sql .= " , $tbl_name.prod_note";
            $sql .= " , $tbl_name.priv_prod_cd";
            $sql .= " , $tbl_name.alcohol_cd";
            $sql .= " , TO_CHAR($tbl_name.alcohol_capa_amount,    'FM999,999,990')    AS alcohol_capa_amount";
            $sql .= " , TO_CHAR($tbl_name.order_lot,              'FM999,999,990')    AS order_lot";
            $sql .= " , TO_CHAR($tbl_name.return_lot,             'FM999,999,990')    AS return_lot";
            $sql .= " , TO_CHAR($tbl_name.just_stock_amout,       'FM999,999,990')    AS just_stock_amout";
            $sql .= " , $tbl_name.appo_prod_kbn";
            $sql .= " , $tbl_name.appo_medi_kbn";
            $sql .= " , trim($tbl_name.tax_type)                                      AS tax_type";
            $sql .= " , $tbl_name.order_stop_kbn";
            $sql .= " , $tbl_name.noreturn_kbn";
            $sql .= " , $tbl_name.point_kbn";
            $sql .= " , TO_CHAR($tbl_name.point_magni,            'FM999,999,990')    AS point_magni";
            $sql .= " , TO_CHAR($tbl_name.p_calc_prod_point,      'FM999,999,990')    AS p_calc_prod_point";
//ADDSSTR kanderu            
//            $sql .= " , $tbl_name.dir_del_kbn";一時的に封印
//            $sql .= " , $tbl_name.sale_kbn";一時的に封印
//ADDEND kanderu               
            $sql .= " , TO_CHAR($tbl_name.p_calc_prod_money,      'FM999,999,990')    AS p_calc_prod_money";
            $sql .= " , $tbl_name.auto_order_kbn";
            $sql .= " , $tbl_name.risk_type_kbn";
            $sql .= " , $tbl_name.order_fin_kbn";
            $sql .= " , $tbl_name.resale_kbn"; // 直送区分
            $sql .= " , $tbl_name.investiga_kbn";
            $sql .= " , $tbl_name.disc_not_kbn";
            $sql .= " , $tbl_name.regi_duty_kbn";
            $sql .= " , $tbl_name.set_prod_kbn";
            $sql .= " , $tbl_name.bundle_kbn";
            $sql .= " , $tbl_name.mixture_kbn";
            $sql .= " , $tbl_name.sale_period_kbn";
            $sql .= " , $tbl_name.delete_kbn";
            $sql .= " , $tbl_name.order_can_kbn";
            $sql .= " , $tbl_name.order_can_date";
            $sql .= " , $tbl_name.prod_t_cd1";
            $sql .= " , $tbl_name.prod_t_cd2";
            $sql .= " , $tbl_name.prod_t_cd3";
            $sql .= " , $tbl_name.prod_t_cd4";
            $sql .= " , $tbl_name.prod_k_cd1";
            $sql .= " , $tbl_name.prod_k_cd2";
            $sql .= " , $tbl_name.prod_k_cd3";
            $sql .= " , $tbl_name.prod_k_cd4";
            $sql .= " , $tbl_name.prod_k_cd5";
            $sql .= " , $tbl_name.prod_k_cd6";
            $sql .= " , $tbl_name.prod_k_cd7";
            $sql .= " , $tbl_name.prod_k_cd8";
            $sql .= " , $tbl_name.prod_k_cd9";
            $sql .= " , $tbl_name.prod_k_cd10";
            $sql .= " , $tbl_name.prod_res_cd1";
            $sql .= " , $tbl_name.prod_res_cd2";
            $sql .= " , $tbl_name.prod_res_cd3";
            $sql .= " , $tbl_name.prod_res_cd4";
            $sql .= " , $tbl_name.prod_res_cd5";
            $sql .= " , $tbl_name.prod_res_cd6";
            $sql .= " , $tbl_name.prod_res_cd7";
            $sql .= " , $tbl_name.prod_res_cd8";
            $sql .= " , $tbl_name.prod_res_cd9";
            $sql .= " , $tbl_name.prod_res_cd10";
            $sql .= " , $tbl_name.prod_comment";
            $sql .= " , $tbl_name.shelf_block1";
            $sql .= " , $tbl_name.shelf_block2";
            $sql .= " , TO_CHAR($tbl_name.fixeprice,              'FM999,999,990')    AS fixeprice";
            $sql .= " , TO_CHAR($tbl_name.saleprice,              'FM999,999,990')    AS saleprice";
            $sql .= " , TO_CHAR($tbl_name.saleprice_ex,           'FM999,999,990')    AS saleprice_ex";
            $sql .= " , TO_CHAR($tbl_name.base_saleprice,         'FM999,999,990')    AS base_saleprice";
            $sql .= " , TO_CHAR($tbl_name.base_saleprice_ex,      'FM999,999,990')    AS base_saleprice_ex";
            $sql .= " , TO_CHAR($tbl_name.cust_saleprice,         'FM999,999,990')    AS cust_saleprice";
            $sql .= " , TO_CHAR($tbl_name.head_costprice,         'FM999,999,990.00') AS head_costprice";
            $sql .= " , TO_CHAR($tbl_name.shop_costprice,         'FM999,999,990')    AS shop_costprice";
            $sql .= " , TO_CHAR($tbl_name.contract_price,         'FM999,999,990.00') AS contract_price";
            $sql .= " , TO_CHAR($tbl_name.empl_saleprice,         'FM999,999,990')    AS empl_saleprice";
            $sql .= " , TO_CHAR($tbl_name.spcl_saleprice1,        'FM999,999,990')    AS spcl_saleprice1";
            $sql .= " , TO_CHAR($tbl_name.spcl_saleprice2,        'FM999,999,990')    AS spcl_saleprice2";
            $sql .= " , TO_CHAR($tbl_name.saleprice_limit,        'FM999,999,990')    AS saleprice_limit";
            $sql .= " , TO_CHAR($tbl_name.saleprice_ex_limit,     'FM999,999,990')    AS saleprice_ex_limit";
            $sql .= " , TO_CHAR($tbl_name.time_saleprice,         'FM999,999,990')    AS time_saleprice";
            $sql .= " , TO_CHAR($tbl_name.time_saleamount,        'FM999,999,990')    AS time_saleamount";
            $sql .= " , $tbl_name.smp1_str_dt";
            $sql .= " , $tbl_name.smp1_end_dt";
            $sql .= " , TO_CHAR($tbl_name.smp1_saleprice,         'FM999,999,990')    AS smp1_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp1_cust_saleprice,    'FM999,999,990')    AS smp1_cust_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp1_costprice,         'FM999,999,990.00') AS smp1_costprice";
            $sql .= " , $tbl_name.smp1_point_kbn";
            $sql .= " , $tbl_name.smp2_str_dt";
            $sql .= " , $tbl_name.smp2_end_dt";
            $sql .= " , TO_CHAR($tbl_name.smp2_saleprice,         'FM999,999,990')    AS smp2_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp2_cust_saleprice,    'FM999,999,990')    AS smp2_cust_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp2_costprice,         'FM999,999,990.00') AS smp2_costprice";
            $sql .= " , $tbl_name.smp2_point_kbn";
            $sql .= " , $tbl_name.sales_talk_kbn";
            $sql .= " , $tbl_name.sales_talk";
            $sql .= " , $tbl_name.switch_otc_kbn";
            $sql .= " , TO_CHAR($tbl_name.prod_tax,               'FM999,999,990.00') AS prod_tax";
            $sql .= " , $tbl_name.bb_date_kbn";
            $sql .= " , TO_CHAR($tbl_name.cartone,                'FM999,999,990')    AS cartoon";
            $sql .= " , TO_CHAR($tbl_name.bb_days,                'FM999,999,990')    AS bb_days";
            $sql .= " , $tbl_name.order_prod_cd1";
            $sql .= " , $tbl_name.order_prod_cd2";
            $sql .= " , $tbl_name.order_prod_cd3";
            $sql .= " , TO_CHAR($tbl_name.case_costprice,         'FM999,999,990.00') AS case_costprice";
            $sql .= " , trim(mst1201.tax_type)                                        AS sect_tax_type";
            $sql .= " , TO_CHAR(mst1201.sect_tax,                 'FM999,999,990.00') AS sect_tax";
            $sql .= " , '0'     AS total_stock_amout";
            $sql .= " , '0'     AS fill_amount";
            $sql .= " , '0'     AS case_stock_amout";
            $sql .= " , '0'     AS bowl_stock_amout";
            $sql .= " , '0'     AS piece_stock_amout";
            $sql .= " , '0'     AS endmon_amount";
            $sql .= " , '0'     AS nmondiff_amount";
            $sql .= " , '0'     AS emondiff_amount";
            $sql .= " , '0'     AS nmondiff_amount";
            $sql .= " , '0'     AS emondiff_amount";
          //  $sql .= " , coalesce((SELECT staff_nm FROM mst0601 WHERE organization_id = $tbl_name.organization_id AND staff_cd = $tbl_name.insuser_cd),$tbl_name.insuser_cd) AS insuser_nm";
           // $sql .= " , coalesce((SELECT staff_nm FROM mst0601 WHERE organization_id = $tbl_name.organization_id AND staff_cd = $tbl_name.upduser_cd),$tbl_name.upduser_cd) AS upduser_nm";
            $sql .= " FROM $tbl_name";
            $sql .= " LEFT JOIN mst1201 ON (mst1201.organization_id = $tbl_name.organization_id AND mst1201.sect_cd = $tbl_name.sect_cd)";
            $sql .= " left join  m_organization_detail v on ($tbl_name.organization_id = v.organization_id) ";
//            $sql .= " LEFT JOIN mst0204 ON (mst0204.organization_id = $tbl_name.organization_id AND mst0204.prod_cd = $tbl_name.prod_cd AND mst0204.location_no = '')";
            $sql .= " WHERE  $tbl_name.prod_cd = :prod_cd and $tbl_name.organization_id <> -1 ";
           // $sql .= " AND   $tbl_name.prod_cd = :prod_cd";

            $searchArray = array(
               
                ':prod_cd'              => $prod_cd,
            );
        $result = $DBA->executeSQL($sql,$searchArray); 
            // 一覧表を格納する空の配列宣言
            $Datas = [];
          // print_r($sql);
           echo '';
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END gettenpolist");
                return $Datas;
            }
                
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }
                
            $Log->trace("END gettenpolist");
                
            return $Datas;
        }
        //kanderu
        /**
         * POS予約商品マスタ一覧画面一覧表
         * @param    $postArray    入力パラメータ
         * @param    $effFlag      状態フラグ
         * @param    $statusFlag   在籍状況フラグ
         * @return   成功時：$userDataList 失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0211DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $mst0211DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0211DataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $mst0211DataList;
        }

        /**
         * POS予約商品マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   グループマスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $where = 'where 1 = 1';
            
            // 検索条件追加
            // 組織ID
            if( !empty( $postArray['organizationID'] ) )
            {
                $where .= " AND organization_id = :organizationId ";
                $searchArray = array_merge($searchArray, array(':organizationId' => $postArray['organizationID'],));
            }
            //部門コード
            if( !empty( $postArray['sect_cd'] ) )
            {
                $where .= " AND sect_cd = :sectcd ";
                $searchArray = array_merge($searchArray, array(':sectcd' => $postArray['sect_cd'],));
            }

             //商品コード
            if( !empty( $postArray['prod_cd'] ) )
            {
                $where .= " AND prod_cd = :prodCode ";
                $searchArray = array_merge($searchArray, array(':prodCode' => $postArray['prod_cd'],));
            }
            
            // 商品名
            if( !empty( $postArray['prod_nm'] ) )
            {
                $where .= " AND prod_nm LIKE :prodName ";
                $prodName = "%" . $postArray['prod_nm'] . "%";
                $searchArray = array_merge($searchArray, array(':prodName' => $prodName,));
            }

            // 予約商品マスタ
            $sql  = " SELECT";
            $sql .= "  mst0211.organization_id              as organization_id";
            $sql .= " ,v_organization.organization_name     as organization_name";
            $sql .= " ,v_organization.abbreviated_name      as abbreviated_name";
            $sql .= " ,v_organization.disp_order            as o_disp_order";
            $sql .= " ,mst0211.prod_cd                      as prod_cd";
            $sql .= " ,mst0211.change_dt                    as change_dt";
            $sql .= " ,coalesce(mst0211.prod_nm, '')        as prod_nm";
            $sql .= " ,coalesce(mst0211.prod_kn, '')        as prod_kn";
            $sql .= " ,coalesce(mst0211.prod_capa_kn, '')   as prod_capa_kn";
            $sql .= " ,coalesce(mst0211.prod_capa_nm, '')   as prod_capa_nm";
            $sql .= " ,mst0211.sect_cd                      as sect_cd";
            $sql .= " ,coalesce(mst1201.sect_nm, '')        as sect_nm";
            $sql .= " ,mst0211.head_supp_cd                 as supp_cd";
            $sql .= " ,coalesce(mst1101.supp_nm, '')        as supp_nm";
            $sql .= " from (select * from mst0211 ";
            $sql .= $where;
            $sql .= " ) as mst0211";
            $sql .= " left outer join v_organization";
            $sql .= " on (v_organization.organization_id = mst0211.organization_id AND (v_organization.eff_code = '適用中' OR v_organization.eff_code = '適用予定'))";
            $sql .= " left outer join mst1201";
            $sql .= " on (mst1201.organization_id = mst0211.organization_id and mst1201.sect_cd = mst0211.sect_cd)";
            $sql .= " left outer join mst1101";
            $sql .= " on (mst1101.organization_id = mst0211.organization_id and mst1101.supp_cd = mst0211.head_supp_cd)";
            $sql .= " where mst0211.organization_id <> 1 and mst0211.organization_id <> -1 ";
            $sql .= " UNION";
            // 商品マスタ
            $sql .= " SELECT";
            $sql .= "  mst0201.organization_id              as organization_id";
            $sql .= " ,v_organization.organization_name     as organization_name";
            $sql .= " ,v_organization.abbreviated_name      as abbreviated_name";
            $sql .= " ,v_organization.disp_order            as o_disp_order";
            $sql .= " ,mst0201.prod_cd                      as prod_cd";
            $sql .= " ,''                                   as change_dt";
            $sql .= " ,coalesce(mst0201.prod_nm, '')        as prod_nm";
            $sql .= " ,coalesce(mst0201.prod_kn, '')        as prod_kn";
            $sql .= " ,coalesce(mst0201.prod_capa_kn, '')   as prod_capa_kn";
            $sql .= " ,coalesce(mst0201.prod_capa_nm, '')   as prod_capa_nm";
            $sql .= " ,mst0201.sect_cd                      as sect_cd";
            $sql .= " ,coalesce(mst1201.sect_nm, '')        as sect_nm";
            $sql .= " ,mst0201.head_supp_cd                 as supp_cd";
            $sql .= " ,coalesce(mst1101.supp_nm, '')        as supp_nm";
            $sql .= " from (select * from mst0201 ";
            $sql .= $where;
            $sql .= " ) as mst0201";
            $sql .= " left outer join v_organization";
            $sql .= " on (v_organization.organization_id = mst0201.organization_id AND (v_organization.eff_code = '適用中' OR v_organization.eff_code = '適用予定'))";
            $sql .= " left outer join mst1201";
            $sql .= " on (mst1201.organization_id = mst0201.organization_id and mst1201.sect_cd = mst0201.sect_cd)";
            $sql .= " left outer join mst1101";
            $sql .= " on (mst1101.organization_id = mst0201.organization_id and mst1101.supp_cd = mst0201.head_supp_cd)";
            $sql .= " WHERE 1 = 1 and mst0201.organization_id <> 1 and mst0201.organization_id <> -1  ";
           
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $sql .= " limit ".$postArray['limit'];
            $sql .= " offset ".$postArray['offset'];
            //print_r($sql);
//            print_r($searchArray);
            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * POS予約商品マスタ一覧画面一覧表
         * @param    $postArray    入力パラメータ
         * @param    $effFlag      状態フラグ
         * @param    $statusFlag   在籍状況フラグ
         * @return   成功時：$userDataList 失敗：無
         */
        public function getListDataCnt( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = $this->creatSQLCnt( $postArray, $searchArray );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 件数を格納する
            $cnt = 0;

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $cnt;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $cnt = $cnt + $data["count"];
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $cnt;
        }

        /**
         * POS予約商品マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   グループマスタ一覧取得SQL文
         */
        private function creatSQLCnt( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $where = 'where 1 = 1';
            
            // 検索条件追加
            // 組織ID
            if( !empty( $postArray['organizationID'] ) )
            {
                $where .= " AND organization_id = :organizationId ";
                $searchArray = array_merge($searchArray, array(':organizationId' => $postArray['organizationID'],));
            }
            //部門コード
            if( !empty( $postArray['sect_cd'] ) )
            {
                $where .= " AND sect_cd = :sectcd ";
                $searchArray = array_merge($searchArray, array(':sectcd' => $postArray['sect_cd'],));
            }
            // 商品コード
            if( !empty( $postArray['prod_cd'] ) )
            {
                $where .= " AND prod_cd = :prodCode ";
                $searchArray = array_merge($searchArray, array(':prodCode' => $postArray['prod_cd'],));
            }
            
            // 商品名
            if( !empty( $postArray['prod_nm'] ) )
            {
                $where .= " AND prod_nm LIKE :prodName ";
                $prodName = "%" . $postArray['prod_nm'] . "%";
                $searchArray = array_merge($searchArray, array(':prodName' => $prodName,));
            }

            // 予約商品マスタ
            $sql  = " SELECT";
            $sql .= "  count('x')";
            $sql .= " from (select * from mst0211 ";
            $sql .= $where;
            $sql .= " ) as mst0211";
            $sql .= " left outer join v_organization";
            $sql .= " on (v_organization.organization_id = mst0211.organization_id AND (v_organization.eff_code = '適用中' OR v_organization.eff_code = '適用予定'))";
            $sql .= " left outer join mst1201";
            $sql .= " on (mst1201.organization_id = mst0211.organization_id and mst1201.sect_cd = mst0211.sect_cd)";
            $sql .= " left outer join mst1101";
            $sql .= " on (mst1101.organization_id = mst0211.organization_id and mst1101.supp_cd = mst0211.head_supp_cd)";
            $sql .= " where mst0211.organization_id <> 1 and mst0211.organization_id <> -1 ";
            $sql .= " UNION";
            // 商品マスタ
            $sql .= " SELECT";
            $sql .= "  count('x')";
            $sql .= " from (select * from mst0201 ";
            $sql .= $where;
            $sql .= " ) as mst0201";
            $sql .= " left outer join v_organization";
            $sql .= " on (v_organization.organization_id = mst0201.organization_id AND (v_organization.eff_code = '適用中' OR v_organization.eff_code = '適用予定'))";
            $sql .= " left outer join mst1201";
            $sql .= " on (mst1201.organization_id = mst0201.organization_id and mst1201.sect_cd = mst0201.sect_cd)";
            $sql .= " left outer join mst1101";
            $sql .= " on (mst1101.organization_id = mst0201.organization_id and mst1101.supp_cd = mst0201.head_supp_cd)";
            $sql .= " where mst0201.organization_id <> 1 and mst0201.organization_id <> -1 ";
            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * POS予約商品マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   POS予約商品マスタ条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            // ソート初期設定
            $sqlSort = " ORDER BY o_disp_order,        organization_name,      organization_id,    prod_cd";
            $sqlSort .= " ,change_dt DESC";

            // ソート条件作成
            $sortSqlList = array(
                                3    => " ORDER BY o_disp_order DESC,   organization_name DESC, organization_id,    prod_cd",
                                4    => " ORDER BY o_disp_order,        organization_name,      organization_id,    prod_cd",
                                5    => " ORDER BY prod_cd DESC,        o_disp_order,           organization_name,  organization_id",
                                6    => " ORDER BY prod_cd,             o_disp_order,           organization_name,  organization_id",
                                7    => " ORDER BY prod_nm DESC,        o_disp_order,           organization_name,  organization_id,    prod_cd",
                                8    => " ORDER BY prod_nm,             o_disp_order,           organization_name,  organization_id,    prod_cd",
                                9    => " ORDER BY prod_kn DESC,        o_disp_order,           organization_name,  organization_id,    prod_cd",
                                10   => " ORDER BY prod_kn,             o_disp_order,           organization_name,  organization_id,    prod_cd",
                                11   => " ORDER BY prod_capa_nm DESC,   o_disp_order,           organization_name,  organization_id,    prod_cd",
                                12   => " ORDER BY prod_capa_nm,        o_disp_order,           organization_name,  organization_id,    prod_cd",
                                13   => " ORDER BY change_dt DESC,      o_disp_order,           organization_name,  organization_id,    prod_cd",
                                14   => " ORDER BY change_dt,           o_disp_order,           organization_name,  organization_id,    prod_cd",
            );

            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sqlSort = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");

            return $sqlSort;
        }
        
        /**
         * POS予約商品マスタ修正画面登録データ検索
         * @param    $organization_id, $prod_cd
         * @return   SQLの実行結果
         */
        public function getMst0211Data( $organization_id, $prod_cd, $change_dt )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst0211Data");

            if ($change_dt !== ''){
                // 予約商品マスタ
                $tbl_name = 'mst0211';
            }
            else{
                // 商品マスタ
                $tbl_name = 'mst0201';
            }

            $sql  = "SELECT";
            $sql .= "   $tbl_name.insuser_cd";
            $sql .= " , $tbl_name.insdatetime";
            $sql .= " , $tbl_name.upduser_cd";
            $sql .= " , $tbl_name.upddatetime";
            $sql .= " , $tbl_name.disabled";
            $sql .= " , $tbl_name.lan_kbn";
            $sql .= " , $tbl_name.organization_id";
            $sql .= " , $tbl_name.prod_cd";
            if ($change_dt === ''){
                $sql .= " , '' as change_dt";
            }
            else{
                $sql .= " , $tbl_name.change_dt";
            }
            $sql .= " , $tbl_name.jan_cd";
            $sql .= " , $tbl_name.itf_cd";
            $sql .= " , $tbl_name.prod_nm";
            $sql .= " , $tbl_name.prod_kn";
            $sql .= " , $tbl_name.prod_kn_rk";
            $sql .= " , $tbl_name.prod_capa_kn";
            $sql .= " , $tbl_name.prod_capa_nm";
            $sql .= " , $tbl_name.disp_prod_nm1";
            $sql .= " , $tbl_name.disp_prod_nm2";
            $sql .= " , TO_CHAR($tbl_name.case_piece,             'FM999,999,990')    AS case_piece";
            $sql .= " , TO_CHAR($tbl_name.case_bowl,              'FM999,999,990')    AS case_bowl";
            $sql .= " , TO_CHAR($tbl_name.bowl_piece,             'FM999,999,990')    AS bowl_piece";
            $sql .= " , $tbl_name.sect_cd";
            $sql .= " , $tbl_name.priv_class_cd";
            $sql .= " , $tbl_name.jicfs_class_cd";
            $sql .= " , $tbl_name.maker_cd";
            $sql .= " , $tbl_name.head_supp_cd";
            $sql .= " , $tbl_name.shop_supp_cd";
            $sql .= " , $tbl_name.serial_no";
            $sql .= " , $tbl_name.prod_note";
            $sql .= " , $tbl_name.priv_prod_cd";
            $sql .= " , $tbl_name.alcohol_cd";
            $sql .= " , TO_CHAR($tbl_name.alcohol_capa_amount,    'FM999,999,990')    AS alcohol_capa_amount";
            $sql .= " , TO_CHAR($tbl_name.order_lot,              'FM999,999,990')    AS order_lot";
            $sql .= " , TO_CHAR($tbl_name.return_lot,             'FM999,999,990')    AS return_lot";
            $sql .= " , TO_CHAR($tbl_name.just_stock_amout,       'FM999,999,990')    AS just_stock_amout";
            $sql .= " , $tbl_name.appo_prod_kbn";
            $sql .= " , $tbl_name.appo_medi_kbn";
            $sql .= " , trim($tbl_name.tax_type)                                      AS tax_type";
            $sql .= " , $tbl_name.order_stop_kbn";
            $sql .= " , $tbl_name.noreturn_kbn";
            $sql .= " , $tbl_name.point_kbn";
            $sql .= " , TO_CHAR($tbl_name.point_magni,            'FM999,999,990')    AS point_magni";
            $sql .= " , TO_CHAR($tbl_name.p_calc_prod_point,      'FM999,999,990')    AS p_calc_prod_point";
//ADDSSTR kanderu  
//            $sql .= " , $tbl_name.dir_del_kbn";一時的に封印
//            $sql .= " , $tbl_name.sale_kbn";一時的に封印
//addend kanderu
            $sql .= " , TO_CHAR($tbl_name.p_calc_prod_money,      'FM999,999,990')    AS p_calc_prod_money";
            $sql .= " , $tbl_name.auto_order_kbn";
            $sql .= " , $tbl_name.risk_type_kbn";
            $sql .= " , $tbl_name.order_fin_kbn";
            $sql .= " , $tbl_name.resale_kbn"; // 直送区分
            $sql .= " , $tbl_name.investiga_kbn";
            $sql .= " , $tbl_name.disc_not_kbn";
            $sql .= " , $tbl_name.regi_duty_kbn";
            $sql .= " , $tbl_name.set_prod_kbn";
            $sql .= " , $tbl_name.bundle_kbn";
            $sql .= " , $tbl_name.mixture_kbn";
            $sql .= " , $tbl_name.sale_period_kbn";
            $sql .= " , $tbl_name.delete_kbn";
            $sql .= " , $tbl_name.order_can_kbn";
            $sql .= " , $tbl_name.order_can_date";
            $sql .= " , $tbl_name.prod_t_cd1";
            $sql .= " , $tbl_name.prod_t_cd2";
            $sql .= " , $tbl_name.prod_t_cd3";
            $sql .= " , $tbl_name.prod_t_cd4";
            $sql .= " , $tbl_name.prod_k_cd1";
            $sql .= " , $tbl_name.prod_k_cd2";
            $sql .= " , $tbl_name.prod_k_cd3";
            $sql .= " , $tbl_name.prod_k_cd4";
            $sql .= " , $tbl_name.prod_k_cd5";
            $sql .= " , $tbl_name.prod_k_cd6";
            $sql .= " , $tbl_name.prod_k_cd7";
            $sql .= " , $tbl_name.prod_k_cd8";
            $sql .= " , $tbl_name.prod_k_cd9";
            $sql .= " , $tbl_name.prod_k_cd10";
            $sql .= " , $tbl_name.prod_res_cd1";
            $sql .= " , $tbl_name.prod_res_cd2";
            $sql .= " , $tbl_name.prod_res_cd3";
            $sql .= " , $tbl_name.prod_res_cd4";
            $sql .= " , $tbl_name.prod_res_cd5";
            $sql .= " , $tbl_name.prod_res_cd6";
            $sql .= " , $tbl_name.prod_res_cd7";
            $sql .= " , $tbl_name.prod_res_cd8";
            $sql .= " , $tbl_name.prod_res_cd9";
            $sql .= " , $tbl_name.prod_res_cd10";
            $sql .= " , $tbl_name.prod_comment";
            $sql .= " , $tbl_name.shelf_block1";
            $sql .= " , $tbl_name.shelf_block2";
            $sql .= " , TO_CHAR($tbl_name.fixeprice,              'FM999,999,990')    AS fixeprice";
            $sql .= " , TO_CHAR($tbl_name.saleprice,              'FM999,999,990')    AS saleprice";
            $sql .= " , TO_CHAR($tbl_name.saleprice_ex,           'FM999,999,990')    AS saleprice_ex";
            $sql .= " , TO_CHAR($tbl_name.base_saleprice,         'FM999,999,990')    AS base_saleprice";
            $sql .= " , TO_CHAR($tbl_name.base_saleprice_ex,      'FM999,999,990')    AS base_saleprice_ex";
            $sql .= " , TO_CHAR($tbl_name.cust_saleprice,         'FM999,999,990')    AS cust_saleprice";
            $sql .= " , TO_CHAR($tbl_name.head_costprice,         'FM999,999,990.00') AS head_costprice";
            $sql .= " , TO_CHAR($tbl_name.shop_costprice,         'FM999,999,990')    AS shop_costprice";
            $sql .= " , TO_CHAR($tbl_name.contract_price,         'FM999,999,990.00') AS contract_price";
            $sql .= " , TO_CHAR($tbl_name.empl_saleprice,         'FM999,999,990')    AS empl_saleprice";
            $sql .= " , TO_CHAR($tbl_name.spcl_saleprice1,        'FM999,999,990')    AS spcl_saleprice1";
            $sql .= " , TO_CHAR($tbl_name.spcl_saleprice2,        'FM999,999,990')    AS spcl_saleprice2";
            $sql .= " , TO_CHAR($tbl_name.saleprice_limit,        'FM999,999,990')    AS saleprice_limit";
            $sql .= " , TO_CHAR($tbl_name.saleprice_ex_limit,     'FM999,999,990')    AS saleprice_ex_limit";
            $sql .= " , TO_CHAR($tbl_name.time_saleprice,         'FM999,999,990')    AS time_saleprice";
            $sql .= " , TO_CHAR($tbl_name.time_saleamount,        'FM999,999,990')    AS time_saleamount";
            $sql .= " , $tbl_name.smp1_str_dt";
            $sql .= " , $tbl_name.smp1_end_dt";
            $sql .= " , TO_CHAR($tbl_name.smp1_saleprice,         'FM999,999,990')    AS smp1_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp1_cust_saleprice,    'FM999,999,990')    AS smp1_cust_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp1_costprice,         'FM999,999,990.00') AS smp1_costprice";
            $sql .= " , $tbl_name.smp1_point_kbn";
            $sql .= " , $tbl_name.smp2_str_dt";
            $sql .= " , $tbl_name.smp2_end_dt";
            $sql .= " , TO_CHAR($tbl_name.smp2_saleprice,         'FM999,999,990')    AS smp2_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp2_cust_saleprice,    'FM999,999,990')    AS smp2_cust_saleprice";
            $sql .= " , TO_CHAR($tbl_name.smp2_costprice,         'FM999,999,990.00') AS smp2_costprice";
            $sql .= " , $tbl_name.smp2_point_kbn";
            $sql .= " , $tbl_name.sales_talk_kbn";
            $sql .= " , $tbl_name.sales_talk";
            $sql .= " , $tbl_name.switch_otc_kbn";
            $sql .= " , TO_CHAR($tbl_name.prod_tax,               'FM999,999,990.00') AS prod_tax";
            $sql .= " , $tbl_name.bb_date_kbn";
            $sql .= " , TO_CHAR($tbl_name.cartone,                'FM999,999,990')    AS cartoon";
            $sql .= " , TO_CHAR($tbl_name.bb_days,                'FM999,999,990')    AS bb_days";
            $sql .= " , $tbl_name.order_prod_cd1";
            $sql .= " , $tbl_name.order_prod_cd2";
            $sql .= " , $tbl_name.order_prod_cd3";
            $sql .= " , TO_CHAR($tbl_name.case_costprice,         'FM999,999,990.00') AS case_costprice";
            $sql .= " , trim(mst1201.tax_type)                                        AS sect_tax_type";
            $sql .= " , TO_CHAR(mst1201.sect_tax,                 'FM999,999,990.00') AS sect_tax";
            $sql .= " , '0'     AS total_stock_amout";
            $sql .= " , '0'     AS fill_amount";
            $sql .= " , '0'     AS case_stock_amout";
            $sql .= " , '0'     AS bowl_stock_amout";
            $sql .= " , '0'     AS piece_stock_amout";
            $sql .= " , '0'     AS endmon_amount";
            $sql .= " , '0'     AS nmondiff_amount";
            $sql .= " , '0'     AS emondiff_amount";
            $sql .= " , '0'     AS nmondiff_amount";
            $sql .= " , '0'     AS emondiff_amount";
            $sql .= " , coalesce((SELECT staff_nm FROM mst0601 WHERE organization_id = $tbl_name.organization_id AND staff_cd = $tbl_name.insuser_cd),$tbl_name.insuser_cd) AS insuser_nm";
            $sql .= " , coalesce((SELECT staff_nm FROM mst0601 WHERE organization_id = $tbl_name.organization_id AND staff_cd = $tbl_name.upduser_cd),$tbl_name.upduser_cd) AS upduser_nm";
            $sql .= " FROM $tbl_name";
            $sql .= " LEFT JOIN mst1201 ON (mst1201.organization_id = $tbl_name.organization_id AND mst1201.sect_cd = $tbl_name.sect_cd)";
//            $sql .= " LEFT JOIN mst0204 ON (mst0204.organization_id = $tbl_name.organization_id AND mst0204.prod_cd = $tbl_name.prod_cd AND mst0204.location_no = '')";
            $sql .= " WHERE $tbl_name.organization_id = :organization_id";
            $sql .= " AND   $tbl_name.prod_cd = :prod_cd";
            if ($change_dt !== ''){
                $sql .= " AND   $tbl_name.change_dt = :change_dt";
            }

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':prod_cd'              => $prod_cd,
            );
            if ($change_dt !== ''){
                $searchArray = array_merge($searchArray, array(':change_dt' => $change_dt));
            }
//print_r($sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0211DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst0211Data");
                return $mst0211DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst0211DataList = $data;
            }
//print_r($sql);
            $Log->trace("END getMst0211Data");

            return $mst0211DataList;
        }
        
        /**
         * POS予約商品マスタ新規データ登録
         * @param    $postArray(POS予約商品マスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");
            // トランザクション開始
            $DBA->beginTransaction();
//Addstr kanderu 20210729            
            //$schema name
            $schema =   $_SESSION["SCHEMA"];
            //ikkou version
            if($schema === 'kubo' OR $schema === 'jrkusuri' OR $schema === 'millionet_test'){
                // POS予約商品マスタ登録
                $parameters = array(
                    ':insuser_cd'               => 'mportal',
                    ':insdatetime'              => date('Y/m/d H:i:s'),
                    ':upduser_cd'               => 'mportal',
                    ':upddatetime'              => date('Y/m/d H:i:s'),
                    ':disabled'                 => '0',
                    ':lan_kbn'                  => '0',
                    ':connect_kbn'              => '0',
                    ':organization_id'          => $postArray['organization_id'],
                    ':prod_cd'                  => $postArray['prod_cd'],
                    ':change_dt'                => $postArray['change_dt'],
                    ':jan_cd'                   => $postArray['jan_cd'],
                    ':itf_cd'                   => $postArray['itf_cd'],
                    ':prod_nm'                  => $postArray['prod_nm'],
                    ':prod_kn'                  => $postArray['prod_kn'],
                    ':prod_kn_rk'               => $postArray['prod_kn_rk'],
                    ':prod_capa_kn'             => $postArray['prod_capa_kn'],
                    ':prod_capa_nm'             => $postArray['prod_capa_nm'],
                    ':disp_prod_nm1'            => $postArray['disp_prod_nm1'],
                    ':disp_prod_nm2'            => $postArray['disp_prod_nm2'],
                    ':case_piece'               => $postArray['case_piece'] == '' ? '0' : str_replace(',', '', $postArray['case_piece']),
                    ':case_bowl'                => $postArray['case_bowl']  == '' ? '0' : str_replace(',', '', $postArray['case_bowl']),
                    ':bowl_piece'               => $postArray['bowl_piece'] == '' ? '0' : str_replace(',', '', $postArray['bowl_piece']),
                    ':sect_cd'                  => $postArray['sect_cd'],
                    ':priv_class_cd'            => $postArray['priv_class_cd'],
                    ':jicfs_class_cd'           => $postArray['jicfs_class_cd'],
                    ':maker_cd'                 => $postArray['maker_cd'],
                    ':head_supp_cd'             => $postArray['head_supp_cd'],
                    ':shop_supp_cd'             => $postArray['shop_supp_cd'],
                    ':serial_no'                => $postArray['serial_no'],
                    ':prod_note'                => $postArray['prod_note'],
                    ':priv_prod_cd'             => $postArray['priv_prod_cd'],
                    ':alcohol_cd'               => $postArray['alcohol_cd'],
                    ':alcohol_capa_amount'      => $postArray['alcohol_capa_amount'] == '' ? '0' : str_replace(',', '', $postArray['alcohol_capa_amount']),
                    ':order_lot'                => $postArray['order_lot'] == '' ? '0' : str_replace(',', '', $postArray['order_lot']),
                    ':return_lot'               => $postArray['return_lot'] == '' ? '0' : str_replace(',', '', $postArray['return_lot']),
                    ':just_stock_amout'         => $postArray['just_stock_amout'] == '' ? '0' : str_replace(',', '', $postArray['just_stock_amout']),
                    ':appo_prod_kbn'            => $postArray['appo_prod_kbn'],
                    ':appo_medi_kbn'            => $postArray['appo_medi_kbn'],
                    ':tax_type'                 => $postArray['tax_type'],
                    ':order_stop_kbn'           => $postArray['order_stop_kbn'],
                    ':noreturn_kbn'             => $postArray['noreturn_kbn'],
                    ':point_kbn'                => $postArray['point_kbn'],
                    ':point_magni'              => $postArray['point_magni'] == '' ? '0' : str_replace(',', '', $postArray['point_magni']),
                    ':p_calc_prod_point'        => $postArray['p_calc_prod_point'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_point']),
    //ADDSTR kanderu
    //                ':dir_del_kbn'        => $postArray['dir_del_kbn'] == '' ? '0' : str_replace(',', '', $postArray['dir_del_kbn']),一時的に封印
    //                ':sale_kbn'        => $postArray['sale_kbn'] == '' ? '0' : str_replace(',', '', $postArray['sale_kbn']),一時的に封印
    //ADDEND kanderu                
                    ':p_calc_prod_money'        => $postArray['p_calc_prod_money'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_money']),
                    ':auto_order_kbn'           => $postArray['auto_order_kbn'],
                    ':risk_type_kbn'            => $postArray['risk_type_kbn'],
                    ':order_fin_kbn'            => $postArray['order_fin_kbn'],
                    ':resale_kbn'               => $postArray['resale_kbn'], // 直送区分
                    ':investiga_kbn'            => $postArray['investiga_kbn'],
                    ':disc_not_kbn'             => $postArray['disc_not_kbn'],
                    ':regi_duty_kbn'            => $postArray['regi_duty_kbn'],
                    //':set_prod_kbn'             => $postArray['set_prod_kbn'],
					':set_prod_kbn'             => '0',
                    ':bundle_kbn'               => $postArray['bundle_kbn'],
                    ':mixture_kbn'              => $postArray['mixture_kbn'],
                    ':sale_period_kbn'          => $postArray['sale_period_kbn'],
                    ':delete_kbn'               => $postArray['delete_kbn'],
                    ':order_can_kbn'            => $postArray['order_can_kbn'],
                    ':order_can_date'           => $postArray['order_can_date'],
                    ':prod_t_cd1'               => $postArray['prod_t_cd1'],
                    ':prod_t_cd2'               => $postArray['prod_t_cd2'],
                    ':prod_t_cd3'               => $postArray['prod_t_cd3'],
                    ':prod_t_cd4'               => $postArray['prod_t_cd4'],
                    ':prod_k_cd1'               => $postArray['prod_k_cd1'],
                    ':prod_k_cd2'               => $postArray['prod_k_cd2'],
                    ':prod_k_cd3'               => $postArray['prod_k_cd3'],
                    ':prod_k_cd4'               => $postArray['prod_k_cd4'],
                    ':prod_k_cd5'               => $postArray['prod_k_cd5'],
                    ':prod_k_cd6'               => $postArray['prod_k_cd6'],
                    ':prod_k_cd7'               => $postArray['prod_k_cd7'],
                    ':prod_k_cd8'               => $postArray['prod_k_cd8'],
                    ':prod_k_cd9'               => $postArray['prod_k_cd9'],
                    ':prod_k_cd10'              => $postArray['prod_k_cd10'],
                    ':prod_res_cd1'             => $postArray['prod_res_cd1'],
                    ':prod_res_cd2'             => $postArray['prod_res_cd2'],
                    ':prod_res_cd3'             => $postArray['prod_res_cd3'],
                    ':prod_res_cd4'             => $postArray['prod_res_cd4'],
                    ':prod_res_cd5'             => $postArray['prod_res_cd5'],
                    ':prod_res_cd6'             => $postArray['prod_res_cd6'],
                    ':prod_res_cd7'             => $postArray['prod_res_cd7'],
                    ':prod_res_cd8'             => $postArray['prod_res_cd8'],
                    ':prod_res_cd9'             => $postArray['prod_res_cd9'],
                    ':prod_res_cd10'            => $postArray['prod_res_cd10'],
                    ':prod_comment'             => $postArray['prod_comment'],
                    ':shelf_block1'             => $postArray['shelf_block1'],
                    ':shelf_block2'             => $postArray['shelf_block2'],
                    ':fixeprice'                => $postArray['fixeprice'] == '' ? '0' : str_replace(',', '', $postArray['fixeprice']),
                    ':saleprice'                => $postArray['saleprice'] == '' ? '0' : str_replace(',', '', $postArray['saleprice']),
                    ':saleprice_ex'             => $postArray['saleprice_ex'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_ex']),
                    ':base_saleprice'           => $postArray['base_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['base_saleprice']),
                    ':base_saleprice_ex'        => $postArray['base_saleprice_ex'] == '' ? '0' : str_replace(',', '', $postArray['base_saleprice_ex']),
                    ':cust_saleprice'           => $postArray['cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['cust_saleprice']),
                    ':head_costprice'           => $postArray['head_costprice'] == '' ? '0' : str_replace(',', '', $postArray['head_costprice']),
                    ':shop_costprice'           => $postArray['shop_costprice'] == '' ? '0' : str_replace(',', '', $postArray['shop_costprice']),
                    ':contract_price'           => $postArray['contract_price'] == '' ? '0' : str_replace(',', '', $postArray['contract_price']),
                    ':empl_saleprice'           => $postArray['empl_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['empl_saleprice']),
                    ':spcl_saleprice1'          => $postArray['spcl_saleprice1'] == '' ? '0' : str_replace(',', '', $postArray['spcl_saleprice1']),
                    ':spcl_saleprice2'          => $postArray['spcl_saleprice2'] == '' ? '0' : str_replace(',', '', $postArray['spcl_saleprice2']),
                    ':saleprice_limit'          => $postArray['saleprice_limit'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_limit']),
                    ':saleprice_ex_limit'       => $postArray['saleprice_ex_limit'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_ex_limit']),
                    ':time_saleprice'           => $postArray['time_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['time_saleprice']),
                    ':time_saleamount'          => $postArray['time_saleamount'] == '' ? '0' : str_replace(',', '', $postArray['time_saleamount']),
                    ':smp1_str_dt'              => str_replace('/', '', $postArray['smp1_str_dt']),
                    ':smp1_end_dt'              => str_replace('/', '', $postArray['smp1_end_dt']),
                    ':smp1_saleprice'           => $postArray['smp1_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_saleprice']),
                    ':smp1_cust_saleprice'      => $postArray['smp1_cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_cust_saleprice']),
                    ':smp1_costprice'           => $postArray['smp1_costprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_costprice']),
                    ':smp1_point_kbn'           => $postArray['smp1_point_kbn'],
                    ':smp2_str_dt'              => str_replace('/', '', $postArray['smp2_str_dt']),
                    ':smp2_end_dt'              => str_replace('/', '', $postArray['smp2_end_dt']),
                    ':smp2_saleprice'           => $postArray['smp2_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_saleprice']),
                    ':smp2_cust_saleprice'      => $postArray['smp2_cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_cust_saleprice']),
                    ':smp2_costprice'           => $postArray['smp2_costprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_costprice']),
                    ':smp2_point_kbn'           => $postArray['smp2_point_kbn'],
                    ':sales_talk_kbn'           => $postArray['sales_talk_kbn'],
                    ':sales_talk'               => $postArray['sales_talk'],
                    ':switch_otc_kbn'           => $postArray['switch_otc_kbn'],
                    ':prod_tax'                 => $postArray['prod_tax'] == '' ? '0' : str_replace(',', '', $postArray['prod_tax']),
                    ':bb_date_kbn'              => $postArray['bb_date_kbn'],
                    ':cartone'                  => $postArray['cartone'] == '' ? '0' : str_replace(',', '', $postArray['cartone']),
                    ':bb_days'                  => $postArray['bb_days'] == '' ? '0' : str_replace(',', '', $postArray['bb_days']),
                    ':order_prod_cd1'           => $postArray['order_prod_cd1'],
                    ':order_prod_cd2'           => $postArray['order_prod_cd2'],
                    ':order_prod_cd3'           => $postArray['order_prod_cd3'],
                    ':case_costprice'           => $postArray['case_costprice'] == '' ? '0' : str_replace(',', '', $postArray['case_costprice'])
                   // ':specific_nm'              => $postArray[""]
                );
                $sql = 'INSERT INTO '. $_SESSION["SCHEMA"] .'.mst0211( '
                    . '   insuser_cd'
                    . ' , insdatetime'
                    . ' , upduser_cd'
                    . ' , upddatetime'
                    . ' , disabled'
                    . ' , lan_kbn'
                    . ' , connect_kbn'
                    . ' , organization_id'
                    . ' , prod_cd'
                    . ' , change_dt'
                    . ' , jan_cd'
                    . ' , itf_cd'
                    . ' , prod_nm'
                    . ' , prod_kn'
                    . ' , prod_kn_rk'
                    . ' , prod_capa_kn'
                    . ' , prod_capa_nm'
                    . ' , disp_prod_nm1'
                    . ' , disp_prod_nm2'
                    . ' , case_piece'
                    . ' , case_bowl'
                    . ' , bowl_piece'
                    . ' , sect_cd'
                    . ' , priv_class_cd'
                    . ' , jicfs_class_cd'
                    . ' , maker_cd'
                    . ' , head_supp_cd'
                    . ' , shop_supp_cd'
                    . ' , serial_no'
                    . ' , prod_note'
                    . ' , priv_prod_cd'
                    . ' , alcohol_cd'
                    . ' , alcohol_capa_amount'
                    . ' , order_lot'
                    . ' , return_lot'
                    . ' , just_stock_amout'
                    . ' , appo_prod_kbn'
                    . ' , appo_medi_kbn'
                    . ' , tax_type'
                    . ' , order_stop_kbn'
                    . ' , noreturn_kbn'
                    . ' , point_kbn'
                    . ' , point_magni'
                    . ' , p_calc_prod_point'
    //addstr kanderu
    //                . ' , dir_del_kbn'一時的に封印
    //                . ' , sale_kbn'一時的に封印
    //addend kanderu                
                    . ' , p_calc_prod_money'
                    . ' , auto_order_kbn'
                    . ' , risk_type_kbn'
                    . ' , order_fin_kbn'
                    . ' , resale_kbn' // 直送区分
                    . ' , investiga_kbn'
                    . ' , disc_not_kbn'
                    . ' , regi_duty_kbn'
                    . ' , set_prod_kbn'
                    . ' , bundle_kbn'
                    . ' , mixture_kbn'
                    . ' , sale_period_kbn'
                    . ' , delete_kbn'
                    . ' , order_can_kbn'
                    . ' , order_can_date'
                    . ' , prod_t_cd1'
                    . ' , prod_t_cd2'
                    . ' , prod_t_cd3'
                    . ' , prod_t_cd4'
                    . ' , prod_k_cd1'
                    . ' , prod_k_cd2'
                    . ' , prod_k_cd3'
                    . ' , prod_k_cd4'
                    . ' , prod_k_cd5'
                    . ' , prod_k_cd6'
                    . ' , prod_k_cd7'
                    . ' , prod_k_cd8'
                    . ' , prod_k_cd9'
                    . ' , prod_k_cd10'
                    . ' , prod_res_cd1'
                    . ' , prod_res_cd2'
                    . ' , prod_res_cd3'
                    . ' , prod_res_cd4'
                    . ' , prod_res_cd5'
                    . ' , prod_res_cd6'
                    . ' , prod_res_cd7'
                    . ' , prod_res_cd8'
                    . ' , prod_res_cd9'
                    . ' , prod_res_cd10'
                    . ' , prod_comment'
                    . ' , shelf_block1'
                    . ' , shelf_block2'
                    . ' , fixeprice'
                    . ' , saleprice'
                    . ' , saleprice_ex'
                    . ' , base_saleprice'
                    . ' , base_saleprice_ex'
                    . ' , cust_saleprice'
                    . ' , head_costprice'
                    . ' , shop_costprice'
                    . ' , contract_price'
                    . ' , empl_saleprice'
                    . ' , spcl_saleprice1'
                    . ' , spcl_saleprice2'
                    . ' , saleprice_limit'
                    . ' , saleprice_ex_limit'
                    . ' , time_saleprice'
                    . ' , time_saleamount'
                    . ' , smp1_str_dt'
                    . ' , smp1_end_dt'
                    . ' , smp1_saleprice'
                    . ' , smp1_cust_saleprice'
                    . ' , smp1_costprice'
                    . ' , smp1_point_kbn'
                    . ' , smp2_str_dt'
                    . ' , smp2_end_dt'
                    . ' , smp2_saleprice'
                    . ' , smp2_cust_saleprice'
                    . ' , smp2_costprice'
                    . ' , smp2_point_kbn'
                    . ' , sales_talk_kbn'
                    . ' , sales_talk'
                    . ' , switch_otc_kbn'
                    . ' , prod_tax'
                    . ' , bb_date_kbn'
                    . ' , cartone'
                    . ' , bb_days'
                    . ' , order_prod_cd1'
                    . ' , order_prod_cd2'
                    . ' , order_prod_cd3'
                    . ' , case_costprice'
                   // . ' , specific_nm'
                    . ' ) VALUES ('
                    . '   :insuser_cd'
                    . ' , :insdatetime'
                    . ' , :upduser_cd'
                    . ' , :upddatetime'
                    . ' , :disabled'
                    . ' , :lan_kbn'
                    . ' , :connect_kbn'
                    . ' , :organization_id'
                    . ' , :prod_cd'
                    . ' , :change_dt'
                    . ' , :jan_cd'
                    . ' , :itf_cd'
                    . ' , :prod_nm'
                    . ' , :prod_kn'
                    . ' , :prod_kn_rk'
                    . ' , :prod_capa_kn'
                    . ' , :prod_capa_nm'
                    . ' , :disp_prod_nm1'
                    . ' , :disp_prod_nm2'
                    . ' , :case_piece'
                    . ' , :case_bowl'
                    . ' , :bowl_piece'
                    . ' , :sect_cd'
                    . ' , :priv_class_cd'
                    . ' , :jicfs_class_cd'
                    . ' , :maker_cd'
                    . ' , :head_supp_cd'
                    . ' , :shop_supp_cd'
                    . ' , :serial_no'
                    . ' , :prod_note'
                    . ' , :priv_prod_cd'
                    . ' , :alcohol_cd'
                    . ' , :alcohol_capa_amount'
                    . ' , :order_lot'
                    . ' , :return_lot'
                    . ' , :just_stock_amout'
                    . ' , :appo_prod_kbn'
                    . ' , :appo_medi_kbn'
                    . ' , :tax_type'
                    . ' , :order_stop_kbn'
                    . ' , :noreturn_kbn'
                    . ' , :point_kbn'
                    . ' , :point_magni'
                    . ' , :p_calc_prod_point'
    //ADDstr kanderu           
    //                . ' , :dir_del_kbn'一時的に封印
    //                . ' , :sale_kbn' 一時的に封印
    //addend kanderu                    
                    . ' , :p_calc_prod_money'
                    . ' , :auto_order_kbn'
                    . ' , :risk_type_kbn'
                    . ' , :order_fin_kbn'
                    . ' , :resale_kbn' // 直送区分
                    . ' , :investiga_kbn'
                    . ' , :disc_not_kbn'
                    . ' , :regi_duty_kbn'
                    . ' , :set_prod_kbn'
                    . ' , :bundle_kbn'
                    . ' , :mixture_kbn'
                    . ' , :sale_period_kbn'
                    . ' , :delete_kbn'
                    . ' , :order_can_kbn'
                    . ' , :order_can_date'
                    . ' , :prod_t_cd1'
                    . ' , :prod_t_cd2'
                    . ' , :prod_t_cd3'
                    . ' , :prod_t_cd4'
                    . ' , :prod_k_cd1'
                    . ' , :prod_k_cd2'
                    . ' , :prod_k_cd3'
                    . ' , :prod_k_cd4'
                    . ' , :prod_k_cd5'
                    . ' , :prod_k_cd6'
                    . ' , :prod_k_cd7'
                    . ' , :prod_k_cd8'
                    . ' , :prod_k_cd9'
                    . ' , :prod_k_cd10'
                    . ' , :prod_res_cd1'
                    . ' , :prod_res_cd2'
                    . ' , :prod_res_cd3'
                    . ' , :prod_res_cd4'
                    . ' , :prod_res_cd5'
                    . ' , :prod_res_cd6'
                    . ' , :prod_res_cd7'
                    . ' , :prod_res_cd8'
                    . ' , :prod_res_cd9'
                    . ' , :prod_res_cd10'
                    . ' , :prod_comment'
                    . ' , :shelf_block1'
                    . ' , :shelf_block2'
                    . ' , :fixeprice'
                    . ' , :saleprice'
                    . ' , :saleprice_ex'
                    . ' , :base_saleprice'
                    . ' , :base_saleprice_ex'
                    . ' , :cust_saleprice'
                    . ' , :head_costprice'
                    . ' , :shop_costprice'
                    . ' , :contract_price'
                    . ' , :empl_saleprice'
                    . ' , :spcl_saleprice1'
                    . ' , :spcl_saleprice2'
                    . ' , :saleprice_limit'
                    . ' , :saleprice_ex_limit'
                    . ' , :time_saleprice'
                    . ' , :time_saleamount'
                    . ' , :smp1_str_dt'
                    . ' , :smp1_end_dt'
                    . ' , :smp1_saleprice'
                    . ' , :smp1_cust_saleprice'
                    . ' , :smp1_costprice'
                    . ' , :smp1_point_kbn'
                    . ' , :smp2_str_dt'
                    . ' , :smp2_end_dt'
                    . ' , :smp2_saleprice'
                    . ' , :smp2_cust_saleprice'
                    . ' , :smp2_costprice'
                    . ' , :smp2_point_kbn'
                    . ' , :sales_talk_kbn'
                    . ' , :sales_talk'
                    . ' , :switch_otc_kbn'
                    . ' , :prod_tax'
                    . ' , :bb_date_kbn'
                    . ' , :cartone'
                    . ' , :bb_days'
                    . ' , :order_prod_cd1'
                    . ' , :order_prod_cd2'
                    . ' , :order_prod_cd3'
                    . ' , :case_costprice'
                  //  . ' , :specific_nm'
                    . ' )';   
            }else{
            //ikkou 以外version
            // POS予約商品マスタ登録
            $parameters = array(
                ':insuser_cd'               => 'mportal',
                ':insdatetime'              => date('Y/m/d H:i:s'),
                ':upduser_cd'               => 'mportal',
                ':upddatetime'              => date('Y/m/d H:i:s'),
                ':disabled'                 => '0',
                ':lan_kbn'                  => '0',
                ':connect_kbn'              => '0',
                ':organization_id'          => $postArray['organization_id'],
                ':prod_cd'                  => $postArray['prod_cd'],
                ':change_dt'                => $postArray['change_dt'],
                ':jan_cd'                   => $postArray['jan_cd'],
                ':itf_cd'                   => $postArray['itf_cd'],
                ':prod_nm'                  => $postArray['prod_nm'],
                ':prod_kn'                  => $postArray['prod_kn'],
                ':prod_kn_rk'               => $postArray['prod_kn_rk'],
                ':prod_capa_kn'             => $postArray['prod_capa_kn'],
                ':prod_capa_nm'             => $postArray['prod_capa_nm'],
                ':disp_prod_nm1'            => $postArray['disp_prod_nm1'],
                ':disp_prod_nm2'            => $postArray['disp_prod_nm2'],
                ':case_piece'               => $postArray['case_piece'] == '' ? '0' : str_replace(',', '', $postArray['case_piece']),
                ':case_bowl'                => $postArray['case_bowl']  == '' ? '0' : str_replace(',', '', $postArray['case_bowl']),
                ':bowl_piece'               => $postArray['bowl_piece'] == '' ? '0' : str_replace(',', '', $postArray['bowl_piece']),
                ':sect_cd'                  => $postArray['sect_cd'],
                ':priv_class_cd'            => $postArray['priv_class_cd'],
                ':jicfs_class_cd'           => $postArray['jicfs_class_cd'],
                ':maker_cd'                 => $postArray['maker_cd'],
                ':head_supp_cd'             => $postArray['head_supp_cd'],
                ':shop_supp_cd'             => $postArray['shop_supp_cd'],
                ':serial_no'                => $postArray['serial_no'],
                ':prod_note'                => $postArray['prod_note'],
                ':priv_prod_cd'             => $postArray['priv_prod_cd'],
                ':alcohol_cd'               => $postArray['alcohol_cd'],
                ':alcohol_capa_amount'      => $postArray['alcohol_capa_amount'] == '' ? '0' : str_replace(',', '', $postArray['alcohol_capa_amount']),
                ':order_lot'                => $postArray['order_lot'] == '' ? '0' : str_replace(',', '', $postArray['order_lot']),
                ':return_lot'               => $postArray['return_lot'] == '' ? '0' : str_replace(',', '', $postArray['return_lot']),
                ':just_stock_amout'         => $postArray['just_stock_amout'] == '' ? '0' : str_replace(',', '', $postArray['just_stock_amout']),
                ':appo_prod_kbn'            => $postArray['appo_prod_kbn'],
                ':appo_medi_kbn'            => $postArray['appo_medi_kbn'],
                ':tax_type'                 => $postArray['tax_type'],
                ':order_stop_kbn'           => $postArray['order_stop_kbn'],
                ':noreturn_kbn'             => $postArray['noreturn_kbn'],
                ':point_kbn'                => $postArray['point_kbn'],
                ':point_magni'              => $postArray['point_magni'] == '' ? '0' : str_replace(',', '', $postArray['point_magni']),
                ':p_calc_prod_point'        => $postArray['p_calc_prod_point'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_point']),
//ADDSTR kanderu
//                ':dir_del_kbn'        => $postArray['dir_del_kbn'] == '' ? '0' : str_replace(',', '', $postArray['dir_del_kbn']),一時的に封印
//                ':sale_kbn'        => $postArray['sale_kbn'] == '' ? '0' : str_replace(',', '', $postArray['sale_kbn']),一時的に封印
//ADDEND kanderu                
                ':p_calc_prod_money'        => $postArray['p_calc_prod_money'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_money']),
                ':auto_order_kbn'           => $postArray['auto_order_kbn'],
                ':risk_type_kbn'            => $postArray['risk_type_kbn'],
                ':order_fin_kbn'            => $postArray['order_fin_kbn'],
                ':resale_kbn'               => $postArray['resale_kbn'], // 直送区分
                ':investiga_kbn'            => $postArray['investiga_kbn'],
                ':disc_not_kbn'             => $postArray['disc_not_kbn'],
                ':regi_duty_kbn'            => $postArray['regi_duty_kbn'],
                //':set_prod_kbn'             => $postArray['set_prod_kbn'],
				':set_prod_kbn'             => '0',
                ':bundle_kbn'               => $postArray['bundle_kbn'],
                ':mixture_kbn'              => $postArray['mixture_kbn'],
                ':sale_period_kbn'          => $postArray['sale_period_kbn'],
                ':delete_kbn'               => $postArray['delete_kbn'],
                ':order_can_kbn'            => $postArray['order_can_kbn'],
                ':order_can_date'           => $postArray['order_can_date'],
                ':prod_t_cd1'               => $postArray['prod_t_cd1'],
                ':prod_t_cd2'               => $postArray['prod_t_cd2'],
                ':prod_t_cd3'               => $postArray['prod_t_cd3'],
                ':prod_t_cd4'               => $postArray['prod_t_cd4'],
                ':prod_k_cd1'               => $postArray['prod_k_cd1'],
                ':prod_k_cd2'               => $postArray['prod_k_cd2'],
                ':prod_k_cd3'               => $postArray['prod_k_cd3'],
                ':prod_k_cd4'               => $postArray['prod_k_cd4'],
                ':prod_k_cd5'               => $postArray['prod_k_cd5'],
                ':prod_k_cd6'               => $postArray['prod_k_cd6'],
                ':prod_k_cd7'               => $postArray['prod_k_cd7'],
                ':prod_k_cd8'               => $postArray['prod_k_cd8'],
                ':prod_k_cd9'               => $postArray['prod_k_cd9'],
                ':prod_k_cd10'              => $postArray['prod_k_cd10'],
                ':prod_res_cd1'             => $postArray['prod_res_cd1'],
                ':prod_res_cd2'             => $postArray['prod_res_cd2'],
                ':prod_res_cd3'             => $postArray['prod_res_cd3'],
                ':prod_res_cd4'             => $postArray['prod_res_cd4'],
                ':prod_res_cd5'             => $postArray['prod_res_cd5'],
                ':prod_res_cd6'             => $postArray['prod_res_cd6'],
                ':prod_res_cd7'             => $postArray['prod_res_cd7'],
                ':prod_res_cd8'             => $postArray['prod_res_cd8'],
                ':prod_res_cd9'             => $postArray['prod_res_cd9'],
                ':prod_res_cd10'            => $postArray['prod_res_cd10'],
                ':prod_comment'             => $postArray['prod_comment'],
                ':shelf_block1'             => $postArray['shelf_block1'],
                ':shelf_block2'             => $postArray['shelf_block2'],
                ':fixeprice'                => $postArray['fixeprice'] == '' ? '0' : str_replace(',', '', $postArray['fixeprice']),
                ':saleprice'                => $postArray['saleprice'] == '' ? '0' : str_replace(',', '', $postArray['saleprice']),
                ':saleprice_ex'             => $postArray['saleprice_ex'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_ex']),
                ':base_saleprice'           => $postArray['base_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['base_saleprice']),
                ':base_saleprice_ex'        => $postArray['base_saleprice_ex'] == '' ? '0' : str_replace(',', '', $postArray['base_saleprice_ex']),
                ':cust_saleprice'           => $postArray['cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['cust_saleprice']),
                ':head_costprice'           => $postArray['head_costprice'] == '' ? '0' : str_replace(',', '', $postArray['head_costprice']),
                ':shop_costprice'           => $postArray['shop_costprice'] == '' ? '0' : str_replace(',', '', $postArray['shop_costprice']),
                ':contract_price'           => $postArray['contract_price'] == '' ? '0' : str_replace(',', '', $postArray['contract_price']),
                ':empl_saleprice'           => $postArray['empl_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['empl_saleprice']),
                ':spcl_saleprice1'          => $postArray['spcl_saleprice1'] == '' ? '0' : str_replace(',', '', $postArray['spcl_saleprice1']),
                ':spcl_saleprice2'          => $postArray['spcl_saleprice2'] == '' ? '0' : str_replace(',', '', $postArray['spcl_saleprice2']),
                ':saleprice_limit'          => $postArray['saleprice_limit'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_limit']),
                ':saleprice_ex_limit'       => $postArray['saleprice_ex_limit'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_ex_limit']),
                ':time_saleprice'           => $postArray['time_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['time_saleprice']),
                ':time_saleamount'          => $postArray['time_saleamount'] == '' ? '0' : str_replace(',', '', $postArray['time_saleamount']),
                ':smp1_str_dt'              => str_replace('/', '', $postArray['smp1_str_dt']),
                ':smp1_end_dt'              => str_replace('/', '', $postArray['smp1_end_dt']),
                ':smp1_saleprice'           => $postArray['smp1_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_saleprice']),
                ':smp1_cust_saleprice'      => $postArray['smp1_cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_cust_saleprice']),
                ':smp1_costprice'           => $postArray['smp1_costprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_costprice']),
                ':smp1_point_kbn'           => $postArray['smp1_point_kbn'],
                ':smp2_str_dt'              => str_replace('/', '', $postArray['smp2_str_dt']),
                ':smp2_end_dt'              => str_replace('/', '', $postArray['smp2_end_dt']),
                ':smp2_saleprice'           => $postArray['smp2_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_saleprice']),
                ':smp2_cust_saleprice'      => $postArray['smp2_cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_cust_saleprice']),
                ':smp2_costprice'           => $postArray['smp2_costprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_costprice']),
                ':smp2_point_kbn'           => $postArray['smp2_point_kbn'],
                ':sales_talk_kbn'           => $postArray['sales_talk_kbn'],
                ':sales_talk'               => $postArray['sales_talk'],
                ':switch_otc_kbn'           => $postArray['switch_otc_kbn'],
                ':prod_tax'                 => $postArray['prod_tax'] == '' ? '0' : str_replace(',', '', $postArray['prod_tax']),
                ':bb_date_kbn'              => $postArray['bb_date_kbn'],
                ':cartone'                  => $postArray['cartone'] == '' ? '0' : str_replace(',', '', $postArray['cartone']),
                ':bb_days'                  => $postArray['bb_days'] == '' ? '0' : str_replace(',', '', $postArray['bb_days']),
                ':order_prod_cd1'           => $postArray['order_prod_cd1'],
                ':order_prod_cd2'           => $postArray['order_prod_cd2'],
                ':order_prod_cd3'           => $postArray['order_prod_cd3'],
                ':case_costprice'           => $postArray['case_costprice'] == '' ? '0' : str_replace(',', '', $postArray['case_costprice']),
				':specific_nm'              => $postArray[""]
            );
            $sql = 'INSERT INTO '. $_SESSION["SCHEMA"] .'.mst0211( '
                . '   insuser_cd'
                . ' , insdatetime'
                . ' , upduser_cd'
                . ' , upddatetime'
                . ' , disabled'
                . ' , lan_kbn'
                . ' , connect_kbn'
                . ' , organization_id'
                . ' , prod_cd'
                . ' , change_dt'
                . ' , jan_cd'
                . ' , itf_cd'
                . ' , prod_nm'
                . ' , prod_kn'
                . ' , prod_kn_rk'
                . ' , prod_capa_kn'
                . ' , prod_capa_nm'
                . ' , disp_prod_nm1'
                . ' , disp_prod_nm2'
                . ' , case_piece'
                . ' , case_bowl'
                . ' , bowl_piece'
                . ' , sect_cd'
                . ' , priv_class_cd'
                . ' , jicfs_class_cd'
                . ' , maker_cd'
                . ' , head_supp_cd'
                . ' , shop_supp_cd'
                . ' , serial_no'
                . ' , prod_note'
                . ' , priv_prod_cd'
                . ' , alcohol_cd'
                . ' , alcohol_capa_amount'
                . ' , order_lot'
                . ' , return_lot'
                . ' , just_stock_amout'
                . ' , appo_prod_kbn'
                . ' , appo_medi_kbn'
                . ' , tax_type'
                . ' , order_stop_kbn'
                . ' , noreturn_kbn'
                . ' , point_kbn'
                . ' , point_magni'
                . ' , p_calc_prod_point'
//addstr kanderu
//                . ' , dir_del_kbn'一時的に封印
//                . ' , sale_kbn'一時的に封印
//addend kanderu                
                . ' , p_calc_prod_money'
                . ' , auto_order_kbn'
                . ' , risk_type_kbn'
                . ' , order_fin_kbn'
                . ' , resale_kbn' // 直送区分
                . ' , investiga_kbn'
                . ' , disc_not_kbn'
                . ' , regi_duty_kbn'
                . ' , set_prod_kbn'
                . ' , bundle_kbn'
                . ' , mixture_kbn'
                . ' , sale_period_kbn'
                . ' , delete_kbn'
                . ' , order_can_kbn'
                . ' , order_can_date'
                . ' , prod_t_cd1'
                . ' , prod_t_cd2'
                . ' , prod_t_cd3'
                . ' , prod_t_cd4'
                . ' , prod_k_cd1'
                . ' , prod_k_cd2'
                . ' , prod_k_cd3'
                . ' , prod_k_cd4'
                . ' , prod_k_cd5'
                . ' , prod_k_cd6'
                . ' , prod_k_cd7'
                . ' , prod_k_cd8'
                . ' , prod_k_cd9'
                . ' , prod_k_cd10'
                . ' , prod_res_cd1'
                . ' , prod_res_cd2'
                . ' , prod_res_cd3'
                . ' , prod_res_cd4'
                . ' , prod_res_cd5'
                . ' , prod_res_cd6'
                . ' , prod_res_cd7'
                . ' , prod_res_cd8'
                . ' , prod_res_cd9'
                . ' , prod_res_cd10'
                . ' , prod_comment'
                . ' , shelf_block1'
                . ' , shelf_block2'
                . ' , fixeprice'
                . ' , saleprice'
                . ' , saleprice_ex'
                . ' , base_saleprice'
                . ' , base_saleprice_ex'
                . ' , cust_saleprice'
                . ' , head_costprice'
                . ' , shop_costprice'
                . ' , contract_price'
                . ' , empl_saleprice'
                . ' , spcl_saleprice1'
                . ' , spcl_saleprice2'
                . ' , saleprice_limit'
                . ' , saleprice_ex_limit'
                . ' , time_saleprice'
                . ' , time_saleamount'
                . ' , smp1_str_dt'
                . ' , smp1_end_dt'
                . ' , smp1_saleprice'
                . ' , smp1_cust_saleprice'
                . ' , smp1_costprice'
                . ' , smp1_point_kbn'
                . ' , smp2_str_dt'
                . ' , smp2_end_dt'
                . ' , smp2_saleprice'
                . ' , smp2_cust_saleprice'
                . ' , smp2_costprice'
                . ' , smp2_point_kbn'
                . ' , sales_talk_kbn'
                . ' , sales_talk'
                . ' , switch_otc_kbn'
                . ' , prod_tax'
                . ' , bb_date_kbn'
                . ' , cartone'
                . ' , bb_days'
                . ' , order_prod_cd1'
                . ' , order_prod_cd2'
                . ' , order_prod_cd3'
                . ' , case_costprice'
				. ' , specific_nm'
                . ' ) VALUES ('
                . '   :insuser_cd'
                . ' , :insdatetime'
                . ' , :upduser_cd'
                . ' , :upddatetime'
                . ' , :disabled'
                . ' , :lan_kbn'
                . ' , :connect_kbn'
                . ' , :organization_id'
                . ' , :prod_cd'
                . ' , :change_dt'
                . ' , :jan_cd'
                . ' , :itf_cd'
                . ' , :prod_nm'
                . ' , :prod_kn'
                . ' , :prod_kn_rk'
                . ' , :prod_capa_kn'
                . ' , :prod_capa_nm'
                . ' , :disp_prod_nm1'
                . ' , :disp_prod_nm2'
                . ' , :case_piece'
                . ' , :case_bowl'
                . ' , :bowl_piece'
                . ' , :sect_cd'
                . ' , :priv_class_cd'
                . ' , :jicfs_class_cd'
                . ' , :maker_cd'
                . ' , :head_supp_cd'
                . ' , :shop_supp_cd'
                . ' , :serial_no'
                . ' , :prod_note'
                . ' , :priv_prod_cd'
                . ' , :alcohol_cd'
                . ' , :alcohol_capa_amount'
                . ' , :order_lot'
                . ' , :return_lot'
                . ' , :just_stock_amout'
                . ' , :appo_prod_kbn'
                . ' , :appo_medi_kbn'
                . ' , :tax_type'
                . ' , :order_stop_kbn'
                . ' , :noreturn_kbn'
                . ' , :point_kbn'
                . ' , :point_magni'
                . ' , :p_calc_prod_point'
//ADDstr kanderu           
//                . ' , :dir_del_kbn'一時的に封印
//                . ' , :sale_kbn' 一時的に封印
//addend kanderu                    
                . ' , :p_calc_prod_money'
                . ' , :auto_order_kbn'
                . ' , :risk_type_kbn'
                . ' , :order_fin_kbn'
                . ' , :resale_kbn' // 直送区分
                . ' , :investiga_kbn'
                . ' , :disc_not_kbn'
                . ' , :regi_duty_kbn'
                . ' , :set_prod_kbn'
                . ' , :bundle_kbn'
                . ' , :mixture_kbn'
                . ' , :sale_period_kbn'
                . ' , :delete_kbn'
                . ' , :order_can_kbn'
                . ' , :order_can_date'
                . ' , :prod_t_cd1'
                . ' , :prod_t_cd2'
                . ' , :prod_t_cd3'
                . ' , :prod_t_cd4'
                . ' , :prod_k_cd1'
                . ' , :prod_k_cd2'
                . ' , :prod_k_cd3'
                . ' , :prod_k_cd4'
                . ' , :prod_k_cd5'
                . ' , :prod_k_cd6'
                . ' , :prod_k_cd7'
                . ' , :prod_k_cd8'
                . ' , :prod_k_cd9'
                . ' , :prod_k_cd10'
                . ' , :prod_res_cd1'
                . ' , :prod_res_cd2'
                . ' , :prod_res_cd3'
                . ' , :prod_res_cd4'
                . ' , :prod_res_cd5'
                . ' , :prod_res_cd6'
                . ' , :prod_res_cd7'
                . ' , :prod_res_cd8'
                . ' , :prod_res_cd9'
                . ' , :prod_res_cd10'
                . ' , :prod_comment'
                . ' , :shelf_block1'
                . ' , :shelf_block2'
                . ' , :fixeprice'
                . ' , :saleprice'
                . ' , :saleprice_ex'
                . ' , :base_saleprice'
                . ' , :base_saleprice_ex'
                . ' , :cust_saleprice'
                . ' , :head_costprice'
                . ' , :shop_costprice'
                . ' , :contract_price'
                . ' , :empl_saleprice'
                . ' , :spcl_saleprice1'
                . ' , :spcl_saleprice2'
                . ' , :saleprice_limit'
                . ' , :saleprice_ex_limit'
                . ' , :time_saleprice'
                . ' , :time_saleamount'
                . ' , :smp1_str_dt'
                . ' , :smp1_end_dt'
                . ' , :smp1_saleprice'
                . ' , :smp1_cust_saleprice'
                . ' , :smp1_costprice'
                . ' , :smp1_point_kbn'
                . ' , :smp2_str_dt'
                . ' , :smp2_end_dt'
                . ' , :smp2_saleprice'
                . ' , :smp2_cust_saleprice'
                . ' , :smp2_costprice'
                . ' , :smp2_point_kbn'
                . ' , :sales_talk_kbn'
                . ' , :sales_talk'
                . ' , :switch_otc_kbn'
                . ' , :prod_tax'
                . ' , :bb_date_kbn'
                . ' , :cartone'
                . ' , :bb_days'
                . ' , :order_prod_cd1'
                . ' , :order_prod_cd2'
                . ' , :order_prod_cd3'
                . ' , :case_costprice'
				. ' , :specific_nm'
                . ' )';
            }
//Addend kanderu 20210729              
            // SQL実行
            if( !$DBA->executeSQL_no_searchpath($sql, $parameters) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品マスタの登録に失敗しました。");
                $errMsg = "POS商品マスタの登録に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            // コミット
            $DBA->commit();
            
            $Log->trace("END addNewData");

            return "MSG_BASE_0000";
        }

        /**
         * POS予約商品マスタデータを更新
         * @param    $postArray(POS商品マスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START updateData");

            // トランザクション開始
            $DBA->beginTransaction();

            $parameters = array(
                //':key_organization_id'      => $postArray['key_organizationID'],
                ':key_prod_cd'              => $postArray['key_prod_cd'],
                ':key_change_dt'            => $postArray['key_change_dt'],
//                ':insuser_cd'               => $postArray['registration_user_id'],
//                ':insuser_cd'               => 'mportal',
//                ':insdatetime'              => date('Y/m/d H:i:s'),
//                ':upduser_cd'               => $postArray['update_user_id'],
                ':upduser_cd'               => 'mportal',
              //  ':upddatetime'              => date('Y/m/d H:i:s'),
                ':disabled'                 => '0',
                ':lan_kbn'                  => '0',
                ':connect_kbn'              => '0',
               // ':organization_id'          => $postArray['organization_id'],
                ':prod_cd'                  => $postArray['prod_cd'],
                ':change_dt'                => $postArray['change_dt'],
//                ':jan_cd'                   => $postArray['jan_cd'],
//                ':itf_cd'                   => $postArray['itf_cd'],
                ':prod_nm'                  => $postArray['prod_nm'],
                ':prod_kn'                  => $postArray['prod_kn'],
                ':prod_kn_rk'               => $postArray['prod_kn_rk'],
                ':prod_capa_kn'             => $postArray['prod_capa_kn'],
                ':prod_capa_nm'             => $postArray['prod_capa_nm'],
//                ':disp_prod_nm1'            => $postArray['disp_prod_nm1'],
//                ':disp_prod_nm2'            => $postArray['disp_prod_nm2'],
                ':case_piece'               => $postArray['case_piece'] == '' ? '0' : str_replace(',', '', $postArray['case_piece']),
//                ':case_bowl'                => $postArray['case_bowl']  == '' ? '0' : str_replace(',', '', $postArray['case_bowl']),
//                ':bowl_piece'               => $postArray['bowl_piece'] == '' ? '0' : str_replace(',', '', $postArray['bowl_piece']),
                ':sect_cd'                  => $postArray['sect_cd'],
                ':priv_class_cd'            => $postArray['priv_class_cd'],
                ':jicfs_class_cd'           => $postArray['jicfs_class_cd'],
                ':maker_cd'                 => $postArray['maker_cd'],
                ':head_supp_cd'             => $postArray['head_supp_cd'],
//                ':shop_supp_cd'             => $postArray['shop_supp_cd'],
//                ':serial_no'                => $postArray['serial_no'],
//                ':prod_note'                => $postArray['prod_note'],
//                ':priv_prod_cd'             => $postArray['priv_prod_cd'],
//                ':alcohol_cd'               => $postArray['alcohol_cd'],
//                ':alcohol_capa_amount'      => $postArray['alcohol_capa_amount'] == '' ? 0 : str_replace(',', '', $postArray['alcohol_capa_amount']),
                ':order_lot'                => $postArray['order_lot'] == '' ? '0' : str_replace(',', '', $postArray['order_lot']),
//                ':return_lot'               => $postArray['return_lot'] == '' ? '0' : str_replace(',', '', $postArray['return_lot']),
                ':just_stock_amout'         => $postArray['just_stock_amout'] == '' ? '0' : str_replace(',', '', $postArray['just_stock_amout']),
                ':appo_prod_kbn'            => $postArray['appo_prod_kbn'],
                ':appo_medi_kbn'            => $postArray['appo_medi_kbn'],
                ':tax_type'                 => $postArray['tax_type'],
                ':order_stop_kbn'           => $postArray['order_stop_kbn'],
                ':noreturn_kbn'             => $postArray['noreturn_kbn'],
                ':point_kbn'                => $postArray['point_kbn'],
                ':point_magni'              => $postArray['point_magni'] == '' ? '0' : str_replace(',', '', $postArray['point_magni']),
                ':p_calc_prod_point'        => $postArray['p_calc_prod_point'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_point']),
 //ADDSTR kanderu                   
//                ':dir_del_kbn'        => $postArray['dir_del_kbn'] == '' ? '0' : str_replace(',', '', $postArray['dir_del_kbn']),一時的に封印
//                ':sale_kbn'        => $postArray['sale_kbn'] == '' ? '0' : str_replace(',', '', $postArray['sale_kbn']),一時的に封印
//ADDEND kanderu                
                ':p_calc_prod_money'        => $postArray['p_calc_prod_money'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_money']),
                ':auto_order_kbn'           => $postArray['auto_order_kbn'],
                ':risk_type_kbn'            => $postArray['risk_type_kbn'],
                ':order_fin_kbn'            => $postArray['order_fin_kbn'],
                ':resale_kbn'               => $postArray['resale_kbn'], // 直送区分
//                ':investiga_kbn'            => $postArray['investiga_kbn'],
                ':disc_not_kbn'             => $postArray['disc_not_kbn'],
//                ':regi_duty_kbn'            => $postArray['regi_duty_kbn'],
                //':set_prod_kbn'             => $postArray['set_prod_kbn'],
				':set_prod_kbn'             => '0',
                ':bundle_kbn'               => $postArray['bundle_kbn'],
                ':mixture_kbn'              => $postArray['mixture_kbn'],
//                ':sale_period_kbn'          => $postArray['sale_period_kbn'],
//                ':delete_kbn'               => $postArray['delete_kbn'],
                ':order_can_kbn'            => $postArray['order_can_kbn'],
//                ':order_can_date'           => $postArray['order_can_date'],
                ':prod_t_cd1'               => $postArray['prod_t_cd1'],
                ':prod_t_cd2'               => $postArray['prod_t_cd2'],
                ':prod_t_cd3'               => $postArray['prod_t_cd3'],
                ':prod_t_cd4'               => $postArray['prod_t_cd4'],
                ':prod_k_cd1'               => $postArray['prod_k_cd1'],
                ':prod_k_cd2'               => $postArray['prod_k_cd2'],
                ':prod_k_cd3'               => $postArray['prod_k_cd3'],
                ':prod_k_cd4'               => $postArray['prod_k_cd4'],
                ':prod_k_cd5'               => $postArray['prod_k_cd5'],
                ':prod_comment'             => $postArray['prod_comment'],
                ':shelf_block1'             => $postArray['shelf_block1'],
                ':shelf_block2'             => $postArray['shelf_block2'],
                ':fixeprice'                => $postArray['fixeprice'] == '' ? '0' : str_replace(',', '', $postArray['fixeprice']),
                ':saleprice'                => $postArray['saleprice'] == '' ? '0' : str_replace(',', '', $postArray['saleprice']),
                ':saleprice_ex'             => $postArray['saleprice_ex'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_ex']),
                ':base_saleprice'           => $postArray['base_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['base_saleprice']),
                ':base_saleprice_ex'        => $postArray['base_saleprice_ex'] == '' ? '0' : str_replace(',', '', $postArray['base_saleprice_ex']),
                ':cust_saleprice'           => $postArray['cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['cust_saleprice']),
                ':head_costprice'           => $postArray['head_costprice'] == '' ? '0' : str_replace(',', '', $postArray['head_costprice']),
                ':shop_costprice'           => $postArray['shop_costprice'] == '' ? '0' : str_replace(',', '', $postArray['shop_costprice']),
                ':contract_price'           => $postArray['contract_price'] == '' ? '0' : str_replace(',', '', $postArray['contract_price']),
//                ':empl_saleprice'           => $postArray['empl_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['empl_saleprice']),
                ':spcl_saleprice1'          => $postArray['spcl_saleprice1'] == '' ? '0' : str_replace(',', '', $postArray['spcl_saleprice1']),
                ':spcl_saleprice2'          => $postArray['spcl_saleprice2'] == '' ? '0' : str_replace(',', '', $postArray['spcl_saleprice2']),
                ':saleprice_limit'          => $postArray['saleprice_limit'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_limit']),
                ':saleprice_ex_limit'       => $postArray['saleprice_ex_limit'] == '' ? '0' : str_replace(',', '', $postArray['saleprice_ex_limit']),
//                ':time_saleprice'           => $postArray['time_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['time_saleprice']),
//                ':time_saleamount'          => $postArray['time_saleamount'] == '' ? '0' : str_replace(',', '', $postArray['time_saleamount']),
                ':smp1_str_dt'              => str_replace('/', '', $postArray['smp1_str_dt']),
                ':smp1_end_dt'              => str_replace('/', '', $postArray['smp1_end_dt']),
                ':smp1_saleprice'           => $postArray['smp1_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_saleprice']),
                ':smp1_cust_saleprice'      => $postArray['smp1_cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_cust_saleprice']),
                ':smp1_costprice'           => $postArray['smp1_costprice'] == '' ? '0' : str_replace(',', '', $postArray['smp1_costprice']),
                ':smp1_point_kbn'           => $postArray['smp1_point_kbn'],
                ':smp2_str_dt'              => str_replace('/', '', $postArray['smp2_str_dt']),
                ':smp2_end_dt'              => str_replace('/', '', $postArray['smp2_end_dt']),
                ':smp2_saleprice'           => $postArray['smp2_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_saleprice']),
                ':smp2_cust_saleprice'      => $postArray['smp2_cust_saleprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_cust_saleprice']),
                ':smp2_costprice'           => $postArray['smp2_costprice'] == '' ? '0' : str_replace(',', '', $postArray['smp2_costprice']),
                ':smp2_point_kbn'           => $postArray['smp2_point_kbn'],
                ':sales_talk_kbn'           => $postArray['sales_talk_kbn'],
                ':sales_talk'               => $postArray['sales_talk'],
                ':switch_otc_kbn'           => $postArray['switch_otc_kbn'],
                ':prod_tax'                 => $postArray['prod_tax'] == '' ? '0' : str_replace(',', '', $postArray['prod_tax']),
                ':bb_date_kbn'              => $postArray['bb_date_kbn'],
                ':cartone'                  => $postArray['cartone'] == '' ? '0' : str_replace(',', '', $postArray['cartone']),
                ':bb_days'                  => $postArray['bb_days'] == '' ? '0' : str_replace(',', '', $postArray['bb_days']),
                ':order_prod_cd1'           => $postArray['order_prod_cd1'],
                ':order_prod_cd2'           => $postArray['order_prod_cd2'],
                ':order_prod_cd3'           => $postArray['order_prod_cd3'],
                ':case_costprice'           => $postArray['case_costprice'] == '' ? '0' : str_replace(',', '', $postArray['case_costprice'])
            );

            // POS商品マスタテーブル更新
            $sql = 'UPDATE '. $_SESSION["SCHEMA"] .'.mst0211 SET'
                . '   upduser_cd            = :upduser_cd'
               // . ' , upddatetime           = :upddatetime'
                . ' , disabled              = :disabled'
                . ' , lan_kbn               = :lan_kbn'
                . ' , connect_kbn           = :connect_kbn'
                //. ' , organization_id       = :organization_id'
                . ' , prod_cd               = :prod_cd'
                . ' , change_dt             = :change_dt'
                . ' , prod_nm               = :prod_nm'
                . ' , prod_kn               = :prod_kn'
                . ' , prod_kn_rk            = :prod_kn_rk'
                . ' , prod_capa_kn          = :prod_capa_kn'
                . ' , prod_capa_nm          = :prod_capa_nm'
                . ' , case_piece            = :case_piece'
//                . ' , case_bowl             = :case_bowl'
//                . ' , bowl_piece            = :bowl_piece'
                . ' , sect_cd               = :sect_cd'
                . ' , priv_class_cd         = :priv_class_cd'
                . ' , jicfs_class_cd        = :jicfs_class_cd'
                . ' , maker_cd              = :maker_cd'
                . ' , head_supp_cd          = :head_supp_cd'
                . ' , order_lot             = :order_lot'
//                . ' , return_lot            = :return_lot'
                . ' , just_stock_amout      = :just_stock_amout'
                . ' , appo_prod_kbn         = :appo_prod_kbn'
                . ' , appo_medi_kbn         = :appo_medi_kbn'
                . ' , tax_type              = :tax_type'
                . ' , order_stop_kbn        = :order_stop_kbn'
                . ' , noreturn_kbn          = :noreturn_kbn'
                . ' , point_kbn             = :point_kbn'
                . ' , point_magni           = :point_magni'
                . ' , p_calc_prod_point     = :p_calc_prod_point'
//addstr kanderu              
//                  . ' , dir_del_kbn     = :dir_del_kbn'   一時的に封印
//                   . ' , sale_kbn     = :sale_kbn'  一時的に封印 
//addend kanderu                    
                . ' , p_calc_prod_money     = :p_calc_prod_money'
                . ' , auto_order_kbn        = :auto_order_kbn'
                . ' , risk_type_kbn         = :risk_type_kbn'
                . ' , order_fin_kbn         = :order_fin_kbn'
                . ' , resale_kbn            = :resale_kbn' // 直送区分
//                . ' , investiga_kbn         = :investiga_kbn'
                . ' , disc_not_kbn          = :disc_not_kbn'
//                . ' , regi_duty_kbn         = :regi_duty_kbn'
                . ' , set_prod_kbn          = :set_prod_kbn'
                . ' , bundle_kbn            = :bundle_kbn'
                . ' , mixture_kbn           = :mixture_kbn'
//                . ' , sale_period_kbn       = :sale_period_kbn'
//                . ' , delete_kbn            = :delete_kbn'
                . ' , order_can_kbn         = :order_can_kbn'
//                . ' , order_can_date        = :order_can_date'
                . ' , prod_t_cd1            = :prod_t_cd1'
                . ' , prod_t_cd2            = :prod_t_cd2'
                . ' , prod_t_cd3            = :prod_t_cd3'
                . ' , prod_t_cd4            = :prod_t_cd4'
                . ' , prod_k_cd1            = :prod_k_cd1'
                . ' , prod_k_cd2            = :prod_k_cd2'
                . ' , prod_k_cd3            = :prod_k_cd3'
                . ' , prod_k_cd4            = :prod_k_cd4'
                . ' , prod_k_cd5            = :prod_k_cd5'
                . ' , prod_comment          = :prod_comment'
                . ' , shelf_block1          = :shelf_block1'
                . ' , shelf_block2          = :shelf_block2'
                . ' , fixeprice             = :fixeprice'
                . ' , saleprice             = :saleprice'
                . ' , saleprice_ex          = :saleprice_ex'
                . ' , base_saleprice        = :base_saleprice'
                . ' , base_saleprice_ex     = :base_saleprice_ex'
                . ' , cust_saleprice        = :cust_saleprice'
                . ' , head_costprice        = :head_costprice'
                . ' , shop_costprice        = :shop_costprice'
                . ' , contract_price        = :contract_price'
//                . ' , empl_saleprice        = :empl_saleprice'
                . ' , spcl_saleprice1       = :spcl_saleprice1'
                . ' , spcl_saleprice2       = :spcl_saleprice2'
                . ' , saleprice_limit       = :saleprice_limit'
                . ' , saleprice_ex_limit    = :saleprice_ex_limit'
//                . ' , time_saleprice        = :time_saleprice'
//                . ' , time_saleamount       = :time_saleamount'
                . ' , smp1_str_dt           = :smp1_str_dt'
                . ' , smp1_end_dt           = :smp1_end_dt'
                . ' , smp1_saleprice        = :smp1_saleprice'
                . ' , smp1_cust_saleprice   = :smp1_cust_saleprice'
                . ' , smp1_costprice        = :smp1_costprice'
                . ' , smp1_point_kbn        = :smp1_point_kbn'
                . ' , smp2_str_dt           = :smp2_str_dt'
                . ' , smp2_end_dt           = :smp2_end_dt'
                . ' , smp2_saleprice        = :smp2_saleprice'
                . ' , smp2_cust_saleprice   = :smp2_cust_saleprice'
                . ' , smp2_costprice        = :smp2_costprice'
                . ' , smp2_point_kbn        = :smp2_point_kbn'
                . ' , sales_talk_kbn        = :sales_talk_kbn'
                . ' , sales_talk            = :sales_talk'
                . ' , switch_otc_kbn        = :switch_otc_kbn'
                . ' , prod_tax              = :prod_tax'
                . ' , bb_date_kbn           = :bb_date_kbn'
                . ' , cartone               = :cartone'
                . ' , bb_days               = :bb_days'
                . ' , order_prod_cd1        = :order_prod_cd1'
                . ' , order_prod_cd2        = :order_prod_cd2'
                . ' , order_prod_cd3        = :order_prod_cd3'
                . ' , case_costprice        = :case_costprice'
               // . ' WHERE organization_id   = :key_organization_id'
                . ' WHERE  prod_cd           = :key_prod_cd'
                . ' AND   change_dt         = :key_change_dt';
            // SQL実行
            if( !$DBA->executeSQL_no_searchpath($sql, $parameters, true) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                $Log->warn("POS商品マスタの更新に失敗しました。");
                $errMsg = "POS商品マスタの更新に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END updateData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            

            // コミット
            $DBA->commit();
            
            $Log->trace("END updateData");
            return "MSG_BASE_0000";
        }

        /**
         * POS商品マスタのデータ削除
         * @param    $postArray(グループマスタID)
         * @return   SQLの実行結果
         */
        public function delData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delData");
            

            // トランザクション開始
            $DBA->beginTransaction();
            
            // POS商品マスタ
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0211 ";
            $query .= " set UPDUSER_CD='delete' where prod_cd = :prod_cd AND change_dt = :change_dt ";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".mst0211_CHANGED ";
            $query_del .= " where  prod_cd = :prod_cd AND change_dt = :change_dt ";
            
            $sql = ' DELETE FROM '. $_SESSION["SCHEMA"] .'.mst0211 WHERE  prod_cd = :prod_cd AND change_dt = :change_dt ';
            $parameters = array(
               // ':organization_id'      => $postArray['key_organizationID'],
                ':prod_cd'              => $postArray['key_prod_cd'],
                ':change_dt'            => $postArray['key_change_dt'],
            );
            $DBA->executeSQL_no_searchpath($query, $parameters );
            $DBA->executeSQL_no_searchpath($query_del, $parameters );
            // SQL実行
            if( !$DBA->executeSQL_no_searchpath($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("POS商品マスタの削除に失敗しました。");
                $errMsg = "POS商品マスタの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            
            // コミット
            $DBA->commit();
            
            $Log->trace("END delData");

            return "MSG_BASE_0000";
        }
         //kanderu
        public function prodlist( $postArray, &$searchArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START prodlist");
            $searchArray = array();
           
            $sql  = "";    
            $sql .= "   SELECT                                                                 ";
            $sql .= "v.abbreviated_name as org_nm                                                    ";
            $sql .= ",*                                                                              ";
            $sql .= "from mst0201 m1                                                                 ";
            $sql .= "left join  m_organization_detail v on (m1.organization_id = v.organization_id)  ";
            $sql .= "where                                                                           ";
            $sql .= "m1.prod_cd = '4967576203456'                                                    ";
            $sql .= "and m1.organization_id<>-1                                                      ";
            $sql .= "order by m1.organization_id                                                     ";
            $result = $DBA->executeSQL($sql);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];

           echo '';
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END prodlist");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }
            $Log->trace("END prodlist");

            return $Datas;
        
        }
        //kanderu
        /**
         * POS商品マスタのデータ格納配列初期化
         * @param    $&$mst0211(POS商品マスタ配列)
         * @return
         */
        public function initMst0211DataList(&$mst0211)
        {
            $mst0211['insuser_cd']          = '';
            $mst0211['insdatetime']         = '';
            $mst0211['upduser_cd']          = '';
            $mst0211['upddatetime']         = '';
            $mst0211['disabled']            = '';
            $mst0211['lan_kbn']             = '';
            $mst0211['organization_id']     = '0';
            $mst0211['prod_cd']             = '';
            $mst0211['change_dt']           = '';
            $mst0211['jan_cd']              = '';
            $mst0211['itf_cd']              = '';
            $mst0211['prod_nm']             = '';
            $mst0211['prod_kn']             = '';
            $mst0211['prod_kn_rk']          = '';
            $mst0211['prod_capa_kn']        = '';
            $mst0211['prod_capa_nm']        = '';
            $mst0211['disp_prod_nm1']       = '';
            $mst0211['disp_prod_nm2']       = '';
            $mst0211['case_piece']          = '0';
            $mst0211['case_bowl']           = '0';
            $mst0211['bowl_piece']          = '0';
            $mst0211['sect_cd']             = '';
            $mst0211['priv_class_cd']       = '';
            $mst0211['jicfs_class_cd']      = '';
            $mst0211['maker_cd']            = '';
            $mst0211['head_supp_cd']        = '';
            $mst0211['shop_supp_cd']        = '';
            $mst0211['serial_no']           = '';
            $mst0211['prod_note']           = '';
            $mst0211['priv_prod_cd']        = '';
            $mst0211['alcohol_cd']          = '';
            $mst0211['alcohol_capa_amount'] = '0';
            $mst0211['order_lot']           = '0';
            $mst0211['return_lot']          = '0';
            $mst0211['just_stock_amout']    = '0';
            $mst0211['appo_prod_kbn']       = '';
            $mst0211['appo_medi_kbn']       = '';
            $mst0211['tax_type']            = '';
            $mst0211['order_stop_kbn']      = '';
            $mst0211['noreturn_kbn']        = '';
            $mst0211['point_kbn']           = '';
            $mst0211['point_magni']         = '0';
            $mst0211['p_calc_prod_point']   = '0';
//ADDSTR kanderu 20201020            
//            $mst0211['dir_del_kbn']         = ''; //直送区分 一時的に封印
//            $mst0211['sale_kbn']            = '';//特売区分 一時的に封印
//ADDEND kanderu 20201020  
            $mst0211['p_calc_prod_money']   = '0';
            $mst0211['auto_order_kbn']      = '';
            $mst0211['risk_type_kbn']       = '';
            $mst0211['order_fin_kbn']       = '';
            $mst0211['resale_kbn']          = ''; // 直送区分
            $mst0211['investiga_kbn']       = '';
            $mst0211['disc_not_kbn']        = '';
            $mst0211['regi_duty_kbn']       = '';
            $mst0211['set_prod_kbn']        = '';
            $mst0211['bundle_kbn']          = '';
            $mst0211['mixture_kbn']         = '';
            $mst0211['sale_period_kbn']     = '';
            $mst0211['delete_kbn']          = '';
            $mst0211['order_can_kbn']       = '';
            $mst0211['order_can_date']      = '';
            $mst0211['prod_t_cd1']          = '';
            $mst0211['prod_t_cd2']          = '';
            $mst0211['prod_t_cd3']          = '';
            $mst0211['prod_t_cd4']          = '';
            $mst0211['prod_k_cd1']          = '';
            $mst0211['prod_k_cd2']          = '';
            $mst0211['prod_k_cd3']          = '';
            $mst0211['prod_k_cd4']          = '';
            $mst0211['prod_k_cd5']          = '';
            $mst0211['prod_k_cd6']          = '';
            $mst0211['prod_k_cd7']          = '';
            $mst0211['prod_k_cd8']          = '';
            $mst0211['prod_k_cd9']          = '';
            $mst0211['prod_k_cd10']         = '';
            $mst0211['prod_res_cd1']        = '';
            $mst0211['prod_res_cd2']        = '';
            $mst0211['prod_res_cd3']        = '';
            $mst0211['prod_res_cd4']        = '';
            $mst0211['prod_res_cd5']        = '';
            $mst0211['prod_res_cd6']        = '';
            $mst0211['prod_res_cd7']        = '';
            $mst0211['prod_res_cd8']        = '';
            $mst0211['prod_res_cd9']        = '';
            $mst0211['prod_res_cd10']       = '';
            $mst0211['prod_comment']        = '';
            $mst0211['shelf_block1']        = '';
            $mst0211['shelf_block2']        = '';
            $mst0211['fixeprice']           = '0';
            $mst0211['saleprice']           = '0';
            $mst0211['saleprice_ex']        = '0';
            $mst0211['base_saleprice']      = '0';
            $mst0211['base_saleprice_ex']   = '0';
            $mst0211['cust_saleprice']      = '0';
            $mst0211['head_costprice']      = '0.00';
            $mst0211['shop_costprice']      = '0';
            $mst0211['contract_price']      = '0.00';
            $mst0211['empl_saleprice']      = '0';
            $mst0211['spcl_saleprice1']     = '0';
            $mst0211['spcl_saleprice2']     = '0';
            $mst0211['saleprice_limit']     = '0';
            $mst0211['saleprice_ex_limit']  = '0';
            $mst0211['time_saleprice']      = '0';
            $mst0211['time_saleamount']     = '0';
            $mst0211['smp1_str_dt']         = '';
            $mst0211['smp1_end_dt']         = '';
            $mst0211['smp1_saleprice']      = '0';
            $mst0211['smp1_cust_saleprice'] = '0';
            $mst0211['smp1_costprice']      = '0.00';
            $mst0211['smp1_point_kbn']      = '';
            $mst0211['smp2_str_dt']         = '';
            $mst0211['smp2_end_dt']         = '';
            $mst0211['smp2_saleprice']      = '0';
            $mst0211['smp2_cust_saleprice'] = '0';
            $mst0211['smp2_costprice']      = '0.00';
            $mst0211['smp2_point_kbn']      = '';
            $mst0211['sales_talk_kbn']      = '';
            $mst0211['sales_talk']          = '';
            $mst0211['switch_otc_kbn']      = '';
            $mst0211['prod_tax']            = '0.00';
            $mst0211['bb_date_kbn']         = '';
            $mst0211['cartone']             = '0';
            $mst0211['bb_days']             = '0';
            $mst0211['order_prod_cd1']      = '';
            $mst0211['order_prod_cd2']      = '';
            $mst0211['order_prod_cd3']      = '';
            $mst0211['case_costprice']      = '0.00';
            $mst0211['sect_tax_type']       = '';
            $mst0211['sect_tax']            = '0.00';
            $mst0211['profit']              = '0';
            $mst0211['profit_per']          = '0.00';
            $mst0211['total_stock_amout']   = '0';
            $mst0211['fill_amount']         = '0';
            $mst0211['case_stock_amout']    = '0';
            $mst0211['bowl_stock_amout']    = '0';
            $mst0211['piece_stock_amout']   = '0';
            $mst0211['endmon_amount']       = '0';
            $mst0211['nmondiff_amount']     = '0';
            $mst0211['emondiff_amount']     = '0';
            $mst0211['shelf_no'][0]         = '';
            $mst0211['shelf_no'][1]         = '';
            $mst0211['shelf_no'][2]         = '';
            $mst0211['insuser_nm']          = '';
            $mst0211['upduser_nm']          = '';
        }
        
        /**
         * POSシステムマスタ取得
         * @param    $organization_id
         * @return   SQLの実行結果
         */
        public function getMst0010Data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst0010Data");

            $sql = "SELECT * FROM mst0010 WHERE organization_id = :organization_id";

            $searchArray = array(
                ':organization_id'      => $organization_id,
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0010DataList = array();

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst0010DataList = $data;
            }

            $Log->trace("END getMst0010Data");

            return $mst0010DataList;
        }
        
        /**
         * POS商品部門マスタデータ検索
         * @param    $organization_id, $sect_cd
         * @return   SQLの実行結果
         */
        public function getMst1201Data( $organization_id, $sect_cd )
        {
            global $DBA, $Log; // getMst1201Data変数宣言
            $Log->trace("START getMst1201Data");

            $sql  = "";
            $sql .= " SELECT";
            $sql .= "   mst1201.insuser_cd";
            $sql .= " , mst1201.insdatetime";
            $sql .= " , mst1201.upduser_cd";
            $sql .= " , mst1201.upddatetime";
            $sql .= " , mst1201.disabled";
            $sql .= " , mst1201.lan_kbn";
            $sql .= " , mst1201.organization_id";
            $sql .= " , mst1201.sect_cd";
            $sql .= " , mst1201.sect_nm";
            $sql .= " , mst1201.sect_kn";
            $sql .= " , mst1201.prod_cd";
            $sql .= " , mst1201.type_cd";
            $sql .= " , mst1201.sect_profit";
            $sql .= " , mst1201.tax_type";
            $sql .= " , mst1201.sect_tax";
            $sql .= " , mst1201.point_kbn";
            $sql .= " , mst1201.sect_prate";
            $sql .= " , mst1201.sect_disc_rate";
            $sql .= " , mst1201.cust_disc_rate";
            $sql .= " , mst1201.empl_kbn";
            $sql .= " , mst1201.disc_not_kbn";
            $sql .= " , mst1201.today_sale_prt";
            $sql .= " FROM mst1201";
            $sql .= " where mst1201.organization_id = :organization_id and mst1201.sect_cd = :sect_cd";

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':sect_cd'              => $sect_cd,
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1201Data");
                return $mst1201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1201DataList = $data;
            }

            $Log->trace("END getMst1201Data");

            return $mst1201DataList;
        }
        


        /**
         * 他テーブル使用状況チェック処理
         * @param type $postArray(POS商品マスタテーブルへの登録情報)
         * @return boolean
         */
        function UsedTableCheck($postArray) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $searchArray = array(
                ':organization_id'      => $postArray['key_organizationID'],
                ':prod_cd'              => $postArray['key_prod_cd']
            );

            $sqlArray = array(
                // 特売マスタ明細
                'SELECT COUNT(sale_plan_cd) AS CNT FROM mst1303 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 期間売価マスタ
                'SELECT COUNT(peri_cost_cd) AS CNT FROM mst1401 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 商品セット構成マスタ
                'SELECT COUNT(prod_cd) AS CNT FROM mst5101 WHERE organization_id = :organization_id AND (prod_cd = :prod_cd OR setprod_cd = :prod_cd)',
                // 商品入数構成マスタ
                'SELECT COUNT(prod_cd) AS CNT FROM mst5102 WHERE organization_id = :organization_id AND (prod_cd = :prod_cd OR setprod_cd = :prod_cd)',
                // ミックスマッチ商品構成マスタ
                'SELECT COUNT(mixmatch_cd) AS CNT FROM mst5202 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // バンドル商品構成マスタ
                'SELECT COUNT(bundle_cd) AS CNT FROM mst5302 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // クーポンマスタ(商品)
                'SELECT COUNT(coupon_cd) AS CNT FROM mst9002 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 売上明細
                'SELECT COUNT(hideseq) AS CNT FROM trn0102 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 仕入明細
                'SELECT COUNT(hideseq) AS CNT FROM trn1102 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 入出金明細
                'SELECT COUNT(hideseq) AS CNT FROM trn1402 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 店間移動明細
                'SELECT COUNT(hideseq) AS CNT FROM trn1502 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 発注明細
                'SELECT COUNT(hideseq) AS CNT FROM trn1602 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
                // 棚卸データ
                'SELECT COUNT(hideseq) AS CNT FROM trn1711 WHERE organization_id = :organization_id AND prod_cd = :prod_cd',
            );

            for ($intL = 0; $intL < Count($sqlArray) - 1; $intL ++) {
                // SQLの実行
                $result = $DBA->executeSQL($sqlArray[$intL], $searchArray);
                if ($result === false) {
                    $Log->trace("END UsedTableCheck");
                    return false;
                }
                $numRet = 0;
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                    $numRet = intval($data['cnt']);
                }
                if ($numRet > 0) {
                    $Log->trace("END UsedTableCheck");
                    return false;
                }
            }
            $Log->trace("END UsedTableCheck");
            return true;
        }
    }
?>