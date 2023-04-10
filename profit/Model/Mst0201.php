<?php
    /**
     * @file      POS商品マスタ(MST0201)
     * @author    川橋
     * @date      2018/11/15
     * @version   1.00
     * @note      POS商品マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * POS商品マスタ(MST0201)クラス
     * @note   POS商品マスタテーブルの管理を行う。
     */
    class Mst0201 extends BaseModel
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

        /**
         * POS商品マスタ一覧画面一覧表
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
            $mst0201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $mst0201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0201DataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $mst0201DataList;
        }

        /**
         * POS商品マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   グループマスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $sql  = " SELECT";
            $sql .= "  mst0201.organization_id              as organization_id";
            $sql .= " ,v_organization.organization_name     as organization_name";
            $sql .= " ,v_organization.abbreviated_name      as abbreviated_name";
            $sql .= " ,v_organization.disp_order            as o_disp_order";
            $sql .= " ,mst0201.prod_cd                      as prod_cd";
            $sql .= " ,coalesce(mst0201.prod_nm, '')        as prod_nm";
            $sql .= " ,coalesce(mst0201.prod_kn, '')        as prod_kn";
            $sql .= " ,coalesce(mst0201.prod_capa_kn, '')   as prod_capa_kn";
            $sql .= " ,coalesce(mst0201.prod_capa_nm, '')   as prod_capa_nm";
            $sql .= " ,mst0201.sect_cd                      as sect_cd";
            $sql .= " ,coalesce(mst1201.sect_nm, '')        as sect_nm";
            $sql .= " ,mst0201.head_supp_cd                 as supp_cd";
            $sql .= " ,coalesce(mst1101.supp_nm, '')        as supp_nm";
            $sql .= " FROM mst0201";
            $sql .= " left outer join v_organization";
            $sql .= " on (v_organization.organization_id = mst0201.organization_id AND (v_organization.eff_code = '適用中' OR v_organization.eff_code = '適用予定'))";
            $sql .= " left outer join mst1201";
            $sql .= " on (mst1201.organization_id = mst0201.organization_id and mst1201.sect_cd = mst0201.sect_cd)";
            $sql .= " left outer join mst1101";
            $sql .= " on (mst1101.organization_id = mst0201.organization_id and mst1101.supp_cd = mst0201.head_supp_cd)";
            $sql .= " WHERE 1 = 1";
            
            // 検索条件追加
            // 組織ID
            if( !empty( $postArray['organizationID'] ) )
            {
                $sql .= " AND mst0201.organization_id = :organizationId ";
                $searchArray = array_merge($searchArray, array(':organizationId' => $postArray['organizationID'],));
            }

            // 商品コード
            if( !empty( $postArray['prod_cd'] ) )
            {
                $sql .= " AND mst0201.prod_cd = :prodCode ";
                $searchArray = array_merge($searchArray, array(':prodCode' => $postArray['prod_cd'],));
            }
            
            // 商品名
            if( !empty( $postArray['prod_nm'] ) )
            {
                $sql .= " AND mst0201.prod_nm LIKE :prodName ";
                $prodName = "%" . $postArray['prod_nm'] . "%";
                $searchArray = array_merge($searchArray, array(':prodName' => $prodName,));
            }
            
            // 商品名カナ
            if( !empty( $postArray['prod_kn'] ) )
            {
                $sql .= " AND mst0201.prod_kn LIKE :prodKana ";
                $prodKana = "%" . $postArray['prod_kn'] . "%";
                $searchArray = array_merge($searchArray, array(':prodKana' => $prodKana,));
            }
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * POS商品マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   POS商品マスタ条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            // ソート初期設定
            $sqlSort = " ORDER BY o_disp_order,        organization_name,      organization_id,    prod_cd";

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
                                13   => " ORDER BY sect_nm DESC,        sect_cd,                o_disp_order,       organization_name,  organization_id,    prod_cd",
                                14   => " ORDER BY sect_nm,             sect_cd,                o_disp_order,       organization_name,  organization_id,    prod_cd",
                                15   => " ORDER BY supp_nm DESC,        supp_cd,                o_disp_order,       organization_name,  organization_id,    prod_cd",
                                16   => " ORDER BY supp_nm,             supp_cd,                o_disp_order,       organization_name,  organization_id,    prod_cd",
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
         * POS商品マスタ修正画面登録データ検索
         * @param    $organization_id, $prod_cd
         * @return   SQLの実行結果
         */
        public function getMst0201Data( $organization_id, $prod_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst0201Data");

            $sql = "SELECT"
                . "   mst0201.insuser_cd"
                . " , mst0201.insdatetime"
                . " , mst0201.upduser_cd"
                . " , mst0201.upddatetime"
                . " , mst0201.disabled"
                . " , mst0201.lan_kbn"
                . " , mst0201.organization_id"
                . " , mst0201.prod_cd"
                . " , mst0201.jan_cd"
                . " , mst0201.itf_cd"
                . " , mst0201.prod_nm"
                . " , mst0201.prod_kn"
                . " , mst0201.prod_kn_rk"
                . " , mst0201.prod_capa_kn"
                . " , mst0201.prod_capa_nm"
                . " , mst0201.disp_prod_nm1"
                . " , mst0201.disp_prod_nm2"
                . " , TO_CHAR(mst0201.case_piece,             'FM999,999,990')    AS case_piece"
                . " , TO_CHAR(mst0201.case_bowl,              'FM999,999,990')    AS case_bowl"
                . " , TO_CHAR(mst0201.bowl_piece,             'FM999,999,990')    AS bowl_piece"
                . " , mst0201.sect_cd"
                . " , mst0201.priv_class_cd"
                . " , mst0201.jicfs_class_cd"
                . " , mst0201.maker_cd"
                . " , mst0201.head_supp_cd"
                . " , mst0201.shop_supp_cd"
                . " , mst0201.serial_no"
                . " , mst0201.prod_note"
                . " , mst0201.priv_prod_cd"
                . " , mst0201.alcohol_cd"
                . " , TO_CHAR(mst0201.alcohol_capa_amount,    'FM999,999,990')    AS alcohol_capa_amount"
                . " , TO_CHAR(mst0201.order_lot,              'FM999,999,990')    AS order_lot"
                . " , TO_CHAR(mst0201.return_lot,             'FM999,999,990')    AS return_lot"
                . " , TO_CHAR(mst0201.just_stock_amout,       'FM999,999,990')    AS just_stock_amout"
                . " , mst0201.appo_prod_kbn"
                . " , mst0201.appo_medi_kbn"
                . " , trim(mst0201.tax_type)                                      AS tax_type"
                . " , mst0201.order_stop_kbn"
                . " , mst0201.noreturn_kbn"
                . " , mst0201.point_kbn"
                . " , TO_CHAR(mst0201.point_magni,            'FM999,999,990')    AS point_magni"
                . " , TO_CHAR(mst0201.p_calc_prod_point,      'FM999,999,990')    AS p_calc_prod_point"
                . " , TO_CHAR(mst0201.p_calc_prod_money,      'FM999,999,990')    AS p_calc_prod_money"
                . " , mst0201.auto_order_kbn"
                . " , mst0201.risk_type_kbn"
                . " , mst0201.order_fin_kbn"
                . " , mst0201.resale_kbn"
                . " , mst0201.investiga_kbn"
                . " , mst0201.disc_not_kbn"
                . " , mst0201.regi_duty_kbn"
                . " , mst0201.set_prod_kbn"
                . " , mst0201.bundle_kbn"
                . " , mst0201.mixture_kbn"
                . " , mst0201.sale_period_kbn"
                . " , mst0201.delete_kbn"
                . " , mst0201.order_can_kbn"
                . " , mst0201.order_can_date"
                . " , mst0201.prod_t_cd1"
                . " , mst0201.prod_t_cd2"
                . " , mst0201.prod_t_cd3"
                . " , mst0201.prod_t_cd4"
                . " , mst0201.prod_k_cd1"
                . " , mst0201.prod_k_cd2"
                . " , mst0201.prod_k_cd3"
                . " , mst0201.prod_k_cd4"
                . " , mst0201.prod_k_cd5"
                . " , mst0201.prod_k_cd6"
                . " , mst0201.prod_k_cd7"
                . " , mst0201.prod_k_cd8"
                . " , mst0201.prod_k_cd9"
                . " , mst0201.prod_k_cd10"
                . " , mst0201.prod_res_cd1"
                . " , mst0201.prod_res_cd2"
                . " , mst0201.prod_res_cd3"
                . " , mst0201.prod_res_cd4"
                . " , mst0201.prod_res_cd5"
                . " , mst0201.prod_res_cd6"
                . " , mst0201.prod_res_cd7"
                . " , mst0201.prod_res_cd8"
                . " , mst0201.prod_res_cd9"
                . " , mst0201.prod_res_cd10"
                . " , mst0201.prod_comment"
                . " , mst0201.shelf_block1"
                . " , mst0201.shelf_block2"
                . " , TO_CHAR(mst0201.fixeprice,              'FM999,999,990')    AS fixeprice"
                . " , TO_CHAR(mst0201.saleprice,              'FM999,999,990')    AS saleprice"
                . " , TO_CHAR(mst0201.saleprice_ex,           'FM999,999,990')    AS saleprice_ex"
                . " , TO_CHAR(mst0201.base_saleprice,         'FM999,999,990')    AS base_saleprice"
                . " , TO_CHAR(mst0201.base_saleprice_ex,      'FM999,999,990')    AS base_saleprice_ex"
                . " , TO_CHAR(mst0201.cust_saleprice,         'FM999,999,990')    AS cust_saleprice"
                . " , TO_CHAR(mst0201.head_costprice,         'FM999,999,990.00') AS head_costprice"
                . " , TO_CHAR(mst0201.shop_costprice,         'FM999,999,990')    AS shop_costprice"
                . " , TO_CHAR(mst0201.contract_price,         'FM999,999,990.00') AS contract_price"
                . " , TO_CHAR(mst0201.empl_saleprice,         'FM999,999,990')    AS empl_saleprice"
                . " , TO_CHAR(mst0201.spcl_saleprice1,        'FM999,999,990')    AS spcl_saleprice1"
                . " , TO_CHAR(mst0201.spcl_saleprice2,        'FM999,999,990')    AS spcl_saleprice2"
                . " , TO_CHAR(mst0201.saleprice_limit,        'FM999,999,990')    AS saleprice_limit"
                . " , TO_CHAR(mst0201.saleprice_ex_limit,     'FM999,999,990')    AS saleprice_ex_limit"
                . " , TO_CHAR(mst0201.time_saleprice,         'FM999,999,990')    AS time_saleprice"
                . " , TO_CHAR(mst0201.time_saleamount,        'FM999,999,990')    AS time_saleamount"
                . " , mst0201.smp1_str_dt"
                . " , mst0201.smp1_end_dt"
                . " , TO_CHAR(mst0201.smp1_saleprice,         'FM999,999,990')    AS smp1_saleprice"
                . " , TO_CHAR(mst0201.smp1_cust_saleprice,    'FM999,999,990')    AS smp1_cust_saleprice"
                . " , TO_CHAR(mst0201.smp1_costprice,         'FM999,999,990.00') AS smp1_costprice"
                . " , mst0201.smp1_point_kbn"
                . " , mst0201.smp2_str_dt"
                . " , mst0201.smp2_end_dt"
                . " , TO_CHAR(mst0201.smp2_saleprice,         'FM999,999,990')    AS smp2_saleprice"
                . " , TO_CHAR(mst0201.smp2_cust_saleprice,    'FM999,999,990')    AS smp2_cust_saleprice"
                . " , TO_CHAR(mst0201.smp2_costprice,         'FM999,999,990.00') AS smp2_costprice"
                . " , mst0201.smp2_point_kbn"
                . " , mst0201.sales_talk_kbn"
                . " , mst0201.sales_talk"
                . " , mst0201.switch_otc_kbn"
                . " , TO_CHAR(mst0201.prod_tax,               'FM999,999,990.00') AS prod_tax"
                . " , mst0201.bb_date_kbn"
                . " , TO_CHAR(mst0201.cartone,                'FM999,999,990')    AS cartoon"
                . " , TO_CHAR(mst0201.bb_days,                'FM999,999,990')    AS bb_days"
                . " , mst0201.order_prod_cd1"
                . " , mst0201.order_prod_cd2"
                . " , mst0201.order_prod_cd3"
                . " , TO_CHAR(mst0201.case_costprice,         'FM999,999,990.00') AS case_costprice"
                . " , trim(mst1201.tax_type)                                      AS sect_tax_type"
                . " , TO_CHAR(mst1201.sect_tax,               'FM999,999,990.00') AS sect_tax"
                . " , TO_CHAR(mst0204.total_stock_amout,      'FM999,999,990')    AS total_stock_amout"
                . " , TO_CHAR(mst0204.fill_amount,            'FM999,999,990')    AS fill_amount"
                . " , TO_CHAR(mst0204.case_stock_amout,       'FM999999990')      AS case_stock_amout"
                . " , TO_CHAR(mst0204.bowl_stock_amout,       'FM999999990')      AS bowl_stock_amout"
                . " , TO_CHAR(mst0204.piece_stock_amout,      'FM999999990')      AS piece_stock_amout"
                . " , TO_CHAR(mst0204.endmon_amount,          'FM999999990')      AS endmon_amount"
                . " , TO_CHAR(mst0204.nmondiff_amount,        'FM999999990')      AS nmondiff_amount"
                . " , TO_CHAR(mst0204.emondiff_amount,        'FM999999990')      AS emondiff_amount"
                . " , coalesce((SELECT staff_nm FROM mst0601 WHERE organization_id = mst0201.organization_id AND staff_cd = mst0201.insuser_cd),mst0201.insuser_cd) AS insuser_nm"
                . " , coalesce((SELECT staff_nm FROM mst0601 WHERE organization_id = mst0201.organization_id AND staff_cd = mst0201.upduser_cd),mst0201.upduser_cd) AS upduser_nm"
                . " FROM mst0201"
                . " LEFT JOIN mst1201 ON (mst1201.organization_id = mst0201.organization_id AND mst1201.sect_cd = mst0201.sect_cd)"
                . " LEFT JOIN mst0204 ON (mst0204.organization_id = mst0201.organization_id AND mst0204.prod_cd = mst0201.prod_cd AND mst0204.location_no = '')"
                . " WHERE mst0201.organization_id = :organization_id AND mst0201.prod_cd = :prod_cd";

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':prod_cd'              => $prod_cd,
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst0201Data");
                return $mst0201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst0201DataList = $data;
            }

            // 棚番
            for ($intL = 0; $intL < 3; $intL ++) {
                $mst0201DataList['shelf_no'][$intL] = '';
            }
            $sql = "SELECT"
                . "   location_no"
                . " FROM mst0204"
                . " WHERE organization_id = :organization_id AND prod_cd = :prod_cd"
                . " AND   location_no <> ''"
                . " ORDER BY location_no"
                . " LIMIT 3";

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            if ($result !== false ) {
                $intL = 0;
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                    $mst0201DataList['shelf_no'][$intL ++] = $data['location_no'];
                 }
            }

            $Log->trace("END getMst0201Data");

            return $mst0201DataList;
        }
        
        /**
         * POS商品マスタ新規データ登録
         * @param    $postArray(POS商品マスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // トランザクション開始
            $DBA->beginTransaction();
            
            // POS商品マスタ登録
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
                ':p_calc_prod_money'        => $postArray['p_calc_prod_money'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_money']),
                ':auto_order_kbn'           => $postArray['auto_order_kbn'],
                ':risk_type_kbn'            => $postArray['risk_type_kbn'],
                ':order_fin_kbn'            => $postArray['order_fin_kbn'],
                ':resale_kbn'               => $postArray['resale_kbn'],
                ':investiga_kbn'            => $postArray['investiga_kbn'],
                ':disc_not_kbn'             => $postArray['disc_not_kbn'],
                ':regi_duty_kbn'            => $postArray['regi_duty_kbn'],
                ':set_prod_kbn'             => $postArray['set_prod_kbn'],
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
            );

            $sql = 'INSERT INTO mst0201( '
                . '   insuser_cd'
                . ' , insdatetime'
                . ' , upduser_cd'
                . ' , upddatetime'
                . ' , disabled'
                . ' , lan_kbn'
                . ' , connect_kbn'
                . ' , organization_id'
                . ' , prod_cd'
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
                . ' , p_calc_prod_money'
                . ' , auto_order_kbn'
                . ' , risk_type_kbn'
                . ' , order_fin_kbn'
                . ' , resale_kbn'
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
                . ' , :p_calc_prod_money'
                . ' , :auto_order_kbn'
                . ' , :risk_type_kbn'
                . ' , :order_fin_kbn'
                . ' , :resale_kbn'
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
                . ' )';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品マスタの登録に失敗しました。");
                $errMsg = "POS商品マスタの登録に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            // POS商品在庫マスタ削除
            $sql = ' DELETE FROM mst0204 WHERE organization_id = :organization_id AND prod_cd = :prod_cd  ';
            $parameters = array(
                ':organization_id'      => $postArray['organization_id'],
                ':prod_cd'              => $postArray['prod_cd'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品在庫マスタの登録に失敗しました。");
                $errMsg = "POS商品在庫マスタの登録に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            // POS商品在庫マスタ登録
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
                ':location_no'              => '',
                ':case_stock_amout'         => '0',
                ':bowl_stock_amout'         => '0',
                ':piece_stock_amout'        => '0',
                ':total_stock_amout'        => $postArray['total_stock_amout'] === '' ? '0' : str_replace(',', '', $postArray['total_stock_amout']),
                ':endmon_amount'            => '0',
                ':nmondiff_amount'          => '0',
                ':emondiff_amount'          => '0',
                ':fill_amount'              => $postArray['fill_amount'] === '' ? '0' : str_replace(',', '', $postArray['fill_amount']),
                ':fill_amount_bef'          => '0',
                ':bb_date'                  => ''
            );

            $sql = 'INSERT INTO mst0204( '
                . '   insuser_cd'
                . ' , insdatetime'
                . ' , upduser_cd'
                . ' , upddatetime'
                . ' , disabled'
                . ' , lan_kbn'
                . ' , connect_kbn'
                . ' , organization_id'
                . ' , prod_cd'
                . ' , location_no'
                . ' , case_stock_amout'
                . ' , bowl_stock_amout'
                . ' , piece_stock_amout'
                . ' , total_stock_amout'
                . ' , endmon_amount'
                . ' , nmondiff_amount'
                . ' , emondiff_amount'
                . ' , fill_amount'
                . ' , fill_amount_bef'
                . ' , bb_date'
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
                . ' , :location_no'
                . ' , :case_stock_amout'
                . ' , :bowl_stock_amout'
                . ' , :piece_stock_amout'
                . ' , :total_stock_amout'
                . ' , :endmon_amount'
                . ' , :nmondiff_amount'
                . ' , :emondiff_amount'
                . ' , :fill_amount'
                . ' , :fill_amount_bef'
                . ' , :bb_date'
                . ' )';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品在庫マスタの登録に失敗しました。");
                $errMsg = "POS商品在庫マスタの登録に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            // POS商品在庫更新記録トラン登録(更新種別区分 = 1)
            if ($this->insTrn3021Data($postArray, '1') === false) {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品在庫更新記録トランの登録に失敗しました。");
                $errMsg = "POS商品在庫更新記録トランの登録に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            // POS商品在庫マスタ(棚番)登録
            if ($this->insMst0204LocationData($postArray) === false) {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品在庫マスタの登録に失敗しました。");
                $errMsg = "POS商品在庫マスタの登録に失敗しました。";
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
         * POS商品マスタデータを更新
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
                ':key_organization_id'      => $postArray['key_organizationID'],
                ':key_prod_cd'              => $postArray['key_prod_cd'],
//                ':insuser_cd'               => $postArray['registration_user_id'],
//                ':insuser_cd'               => 'mportal',
//                ':insdatetime'              => date('Y/m/d H:i:s'),
//                ':upduser_cd'               => $postArray['update_user_id'],
                ':upduser_cd'               => 'mportal',
                ':upddatetime'              => date('Y/m/d H:i:s'),
                ':disabled'                 => '0',
                ':lan_kbn'                  => '0',
                ':connect_kbn'              => '0',
                ':organization_id'          => $postArray['organization_id'],
                ':prod_cd'                  => $postArray['prod_cd'],
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
                ':p_calc_prod_money'        => $postArray['p_calc_prod_money'] == '' ? '0' : str_replace(',', '', $postArray['p_calc_prod_money']),
                ':auto_order_kbn'           => $postArray['auto_order_kbn'],
                ':risk_type_kbn'            => $postArray['risk_type_kbn'],
                ':order_fin_kbn'            => $postArray['order_fin_kbn'],
//                ':resale_kbn'               => $postArray['resale_kbn'],
//                ':investiga_kbn'            => $postArray['investiga_kbn'],
                ':disc_not_kbn'             => $postArray['disc_not_kbn'],
//                ':regi_duty_kbn'            => $postArray['regi_duty_kbn'],
                ':set_prod_kbn'             => $postArray['set_prod_kbn'],
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
//                ':prod_k_cd6'               => $postArray['prod_k_cd6'],
//                ':prod_k_cd7'               => $postArray['prod_k_cd7'],
//                ':prod_k_cd8'               => $postArray['prod_k_cd8'],
//                ':prod_k_cd9'               => $postArray['prod_k_cd9'],
//                ':prod_k_cd10'              => $postArray['prod_k_cd10'],
//                ':prod_res_cd1'             => $postArray['prod_res_cd1'],
//                ':prod_res_cd2'             => $postArray['prod_res_cd2'],
//                ':prod_res_cd3'             => $postArray['prod_res_cd3'],
//                ':prod_res_cd4'             => $postArray['prod_res_cd4'],
//                ':prod_res_cd5'             => $postArray['prod_res_cd5'],
//                ':prod_res_cd6'             => $postArray['prod_res_cd6'],
//                ':prod_res_cd7'             => $postArray['prod_res_cd7'],
//                ':prod_res_cd8'             => $postArray['prod_res_cd8'],
//                ':prod_res_cd9'             => $postArray['prod_res_cd9'],
//                ':prod_res_cd10'            => $postArray['prod_res_cd10'],
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
            $sql = 'UPDATE mst0201 SET'
                . '   upduser_cd            = :upduser_cd'
                . ' , upddatetime           = :upddatetime'
                . ' , disabled              = :disabled'
                . ' , lan_kbn               = :lan_kbn'
                . ' , connect_kbn           = :connect_kbn'
                . ' , organization_id       = :organization_id'
                . ' , prod_cd               = :prod_cd'
                . ' , prod_nm               = :prod_nm'
                . ' , prod_kn               = :prod_kn'
                . ' , prod_kn_rk            = :prod_kn_rk'
                . ' , prod_capa_kn          = :prod_capa_kn'
                . ' , prod_capa_nm          = :prod_capa_nm'
//                . ' , disp_prod_nm1         = :disp_prod_nm1'
//                . ' , disp_prod_nm2         = :disp_prod_nm2'
                . ' , case_piece            = :case_piece'
//                . ' , case_bowl             = :case_bowl'
//                . ' , bowl_piece            = :bowl_piece'
                . ' , sect_cd               = :sect_cd'
                . ' , priv_class_cd         = :priv_class_cd'
                . ' , jicfs_class_cd        = :jicfs_class_cd'
                . ' , maker_cd              = :maker_cd'
                . ' , head_supp_cd          = :head_supp_cd'
//                . ' , shop_supp_cd          = :shop_supp_cd'
//                . ' , serial_no             = :serial_no'
//                . ' , prod_note             = :prod_note'
//                . ' , priv_prod_cd          = :priv_prod_cd'
//                . ' , alcohol_cd            = :alcohol_cd'
//                . ' , alcohol_capa_amount   = :alcohol_capa_amount'
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
                . ' , p_calc_prod_money     = :p_calc_prod_money'
                . ' , auto_order_kbn        = :auto_order_kbn'
                . ' , risk_type_kbn         = :risk_type_kbn'
                . ' , order_fin_kbn         = :order_fin_kbn'
//                . ' , resale_kbn            = :resale_kbn'
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
//                . ' , prod_k_cd6            = :prod_k_cd6'
//                . ' , prod_k_cd7            = :prod_k_cd7'
//                . ' , prod_k_cd8            = :prod_k_cd8'
//                . ' , prod_k_cd9            = :prod_k_cd9'
//                . ' , prod_k_cd10           = :prod_k_cd10'
//                . ' , prod_res_cd1          = :prod_res_cd1'
//                . ' , prod_res_cd2          = :prod_res_cd2'
//                . ' , prod_res_cd3          = :prod_res_cd3'
//                . ' , prod_res_cd4          = :prod_res_cd4'
//                . ' , prod_res_cd5          = :prod_res_cd5'
//                . ' , prod_res_cd6          = :prod_res_cd6'
//                . ' , prod_res_cd7          = :prod_res_cd7'
//                . ' , prod_res_cd8          = :prod_res_cd8'
//                . ' , prod_res_cd9          = :prod_res_cd9'
//                . ' , prod_res_cd10         = :prod_res_cd10'
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
                . ' WHERE organization_id   = :key_organization_id'
                . ' AND   prod_cd           = :key_prod_cd';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品マスタの更新に失敗しました。");
                $errMsg = "POS商品マスタの更新に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END updateData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            // 現在在庫数 or 補充数量の変更チェック
            if ($postArray['total_stock_amout'] !== $postArray['total_stock_amout_load'] ||
                $postArray['fill_amount'] !== $postArray['fill_amount_load']) {
                // POS商品在庫マスタ更新
                $parameters = array(
                    ':key_organization_id'      => $postArray['key_organizationID'],
                    ':key_prod_cd'              => $postArray['key_prod_cd'],
//                    ':insuser_cd'               => 'mportal',
//                    ':insdatetime'              => date('Y/m/d H:i:s'),
                    ':upduser_cd'               => 'mportal',
                    ':upddatetime'              => date('Y/m/d H:i:s'),
                    ':disabled'                 => '0',
                    ':lan_kbn'                  => '0',
                    ':connect_kbn'              => '0',
                    ':organization_id'          => $postArray['organization_id'],
                    ':prod_cd'                  => $postArray['prod_cd'],
//                    ':location_no'              => '',
//                    ':case_stock_amout'         => '0',
//                    ':bowl_stock_amout'         => '0',
//                    ':piece_stock_amout'        => '0',
                    ':total_stock_amout'        => $postArray['total_stock_amout'] === '' ? '0' : str_replace(',', '', $postArray['total_stock_amout']),
//                    ':endmon_amount'            => '0',
//                    ':nmondiff_amount'          => '0',
//                    ':emondiff_amount'          => '0',
                    ':fill_amount'              => $postArray['fill_amount'] === '' ? '0' : str_replace(',', '', $postArray['fill_amount']),
//                    ':fill_amount_bef'          => $postArray['fill_amount_load'],
//                    ':bb_date'                  => ''
                );

                $sql = 'UPDATE mst0204 SET'
                    . '   upduser_cd            = :upduser_cd'
                    . ' , upddatetime           = :upddatetime'
                    . ' , disabled              = :disabled'
                    . ' , lan_kbn               = :lan_kbn'
                    . ' , connect_kbn           = :connect_kbn'
                    . ' , organization_id       = :organization_id'
                    . ' , prod_cd               = :prod_cd'
//                    . ' , location_no           = :location_no'
//                    . ' , case_stock_amout    = :case_stock_amout'
//                    . ' , bowl_stock_amout    = :bowl_stock_amout'
//                    . ' , piece_stock_amout   = :piece_stock_amout'
                    . ' , total_stock_amout   = :total_stock_amout'
//                    . ' , endmon_amount       = :endmon_amount'
//                    . ' , nmondiff_amount     = :nmondiff_amount'
//                    . ' , emondiff_amount     = :emondiff_amount'
                    . ' , fill_amount         = :fill_amount'
//                    . ' , fill_amount_bef     = :fill_amount_bef'
//                    . ' , bb_date             = :bb_date'
                    . ' WHERE organization_id   = :key_organization_id'
                    . ' AND   prod_cd           = :key_prod_cd';

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters) )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();

                    $Log->warn("POS商品マスタの更新に失敗しました。");
                    $errMsg = "POS商品マスタの更新に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END updateData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 現在在庫数の変更チェック
                if ($postArray['total_stock_amout'] !== $postArray['total_stock_amout_load']) {
                    // POS商品在庫修正記録(TRN3025)挿入
                    if ($this->insTrn3025Data($postArray) === false) {
                        // SQL実行エラー　ロールバック対応
                        $DBA->rollBack();

                        $Log->warn("POS商品在庫修正記録トランの登録に失敗しました。");
                        $errMsg = "POS商品在庫修正記録トランの登録に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END updateData");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
                    
                    // POS商品在庫更新記録トラン登録(更新種別区分 = 2)
                    if ($this->insTrn3021Data($postArray, '2') === false) {
                        // SQL実行エラー　ロールバック対応
                        $DBA->rollBack();

                        $Log->warn("POS商品在庫更新記録トランの登録に失敗しました。");
                        $errMsg = "POS商品在庫更新記録トランの登録に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END updateData");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
                }
            }

            // POS商品在庫マスタ(棚番)登録
            if ($this->insMst0204LocationData($postArray) === false) {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();

                $Log->warn("POS商品在庫マスタ(棚番)の登録に失敗しました。");
                $errMsg = "POS商品在庫マスタ(棚番)の登録に失敗しました。";
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
            
            // 他テーブル使用状況チェック
            if ($this->UsedTableCheck($postArray) === false) {
                $Log->warn("POS商品マスタの削除に失敗しました。");
                $errMsg = "POS商品マスタの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            // トランザクション開始
            $DBA->beginTransaction();
            
            // POS商品マスタ
            $sql = ' DELETE FROM mst0201 WHERE organization_id = :organization_id AND prod_cd = :prod_cd  ';
            $parameters = array(
                ':organization_id'      => $postArray['key_organizationID'],
                ':prod_cd'              => $postArray['key_prod_cd'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
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
            
            // POS商品在庫マスタ
            $sql = ' DELETE FROM mst0204 WHERE organization_id = :organization_id AND prod_cd = :prod_cd  ';
            $parameters = array(
                ':organization_id'      => $postArray['organization_id'],
                ':prod_cd'              => $postArray['prod_cd'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("POS商品在庫マスタの削除に失敗しました。");
                $errMsg = "POS商品在庫マスタの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            // POS顧客別売価マスタ
            $sql = ' DELETE FROM mst1701 WHERE organization_id = :organization_id AND prod_cd = :prod_cd  ';
            $parameters = array(
                ':organization_id'      => $postArray['key_organizationID'],
                ':prod_cd'              => $postArray['key_prod_cd'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("POS顧客別売価マスタの削除に失敗しました。");
                $errMsg = "POS顧客別売価マスタの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            // コミット
            $DBA->commit();
            
            $Log->trace("END delData");

            return "MSG_BASE_0000";
        }

        /**
         * POS商品マスタのデータ格納配列初期化
         * @param    $&$mst0201(POS商品マスタ配列)
         * @return
         */
        public function initMst0201DataList(&$mst0201)
        {
            $mst0201['insuser_cd']          = '';
            $mst0201['insdatetime']         = '';
            $mst0201['upduser_cd']          = '';
            $mst0201['upddatetime']         = '';
            $mst0201['disabled']            = '';
            $mst0201['lan_kbn']             = '';
            $mst0201['organization_id']     = '0';
            $mst0201['prod_cd']             = '';
            $mst0201['jan_cd']              = '';
            $mst0201['itf_cd']              = '';
            $mst0201['prod_nm']             = '';
            $mst0201['prod_kn']             = '';
            $mst0201['prod_kn_rk']          = '';
            $mst0201['prod_capa_kn']        = '';
            $mst0201['prod_capa_nm']        = '';
            $mst0201['disp_prod_nm1']       = '';
            $mst0201['disp_prod_nm2']       = '';
            $mst0201['case_piece']          = '0';
            $mst0201['case_bowl']           = '0';
            $mst0201['bowl_piece']          = '0';
            $mst0201['sect_cd']             = '';
            $mst0201['priv_class_cd']       = '';
            $mst0201['jicfs_class_cd']      = '';
            $mst0201['maker_cd']            = '';
            $mst0201['head_supp_cd']        = '';
            $mst0201['shop_supp_cd']        = '';
            $mst0201['serial_no']           = '';
            $mst0201['prod_note']           = '';
            $mst0201['priv_prod_cd']        = '';
            $mst0201['alcohol_cd']          = '';
            $mst0201['alcohol_capa_amount'] = '0';
            $mst0201['order_lot']           = '0';
            $mst0201['return_lot']          = '0';
            $mst0201['just_stock_amout']    = '0';
            $mst0201['appo_prod_kbn']       = '';
            $mst0201['appo_medi_kbn']       = '';
            $mst0201['tax_type']            = '';
            $mst0201['order_stop_kbn']      = '';
            $mst0201['noreturn_kbn']        = '';
            $mst0201['point_kbn']           = '';
            $mst0201['point_magni']         = '0';
            $mst0201['p_calc_prod_point']   = '0';
            $mst0201['p_calc_prod_money']   = '0';
            $mst0201['auto_order_kbn']      = '';
            $mst0201['risk_type_kbn']       = '';
            $mst0201['order_fin_kbn']       = '';
            $mst0201['resale_kbn']          = '';
            $mst0201['investiga_kbn']       = '';
            $mst0201['disc_not_kbn']        = '';
            $mst0201['regi_duty_kbn']       = '';
            $mst0201['set_prod_kbn']        = '';
            $mst0201['bundle_kbn']          = '';
            $mst0201['mixture_kbn']         = '';
            $mst0201['sale_period_kbn']     = '';
            $mst0201['delete_kbn']          = '';
            $mst0201['order_can_kbn']       = '';
            $mst0201['order_can_date']      = '';
            $mst0201['prod_t_cd1']          = '';
            $mst0201['prod_t_cd2']          = '';
            $mst0201['prod_t_cd3']          = '';
            $mst0201['prod_t_cd4']          = '';
            $mst0201['prod_k_cd1']          = '';
            $mst0201['prod_k_cd2']          = '';
            $mst0201['prod_k_cd3']          = '';
            $mst0201['prod_k_cd4']          = '';
            $mst0201['prod_k_cd5']          = '';
            $mst0201['prod_k_cd6']          = '';
            $mst0201['prod_k_cd7']          = '';
            $mst0201['prod_k_cd8']          = '';
            $mst0201['prod_k_cd9']          = '';
            $mst0201['prod_k_cd10']         = '';
            $mst0201['prod_res_cd1']        = '';
            $mst0201['prod_res_cd2']        = '';
            $mst0201['prod_res_cd3']        = '';
            $mst0201['prod_res_cd4']        = '';
            $mst0201['prod_res_cd5']        = '';
            $mst0201['prod_res_cd6']        = '';
            $mst0201['prod_res_cd7']        = '';
            $mst0201['prod_res_cd8']        = '';
            $mst0201['prod_res_cd9']        = '';
            $mst0201['prod_res_cd10']       = '';
            $mst0201['prod_comment']        = '';
            $mst0201['shelf_block1']        = '';
            $mst0201['shelf_block2']        = '';
            $mst0201['fixeprice']           = '0';
            $mst0201['saleprice']           = '0';
            $mst0201['saleprice_ex']        = '0';
            $mst0201['base_saleprice']      = '0';
            $mst0201['base_saleprice_ex']   = '0';
            $mst0201['cust_saleprice']      = '0';
            $mst0201['head_costprice']      = '0.00';
            $mst0201['shop_costprice']      = '0';
            $mst0201['contract_price']      = '0.00';
            $mst0201['empl_saleprice']      = '0';
            $mst0201['spcl_saleprice1']     = '0';
            $mst0201['spcl_saleprice2']     = '0';
            $mst0201['saleprice_limit']     = '0';
            $mst0201['saleprice_ex_limit']  = '0';
            $mst0201['time_saleprice']      = '0';
            $mst0201['time_saleamount']     = '0';
            $mst0201['smp1_str_dt']         = '';
            $mst0201['smp1_end_dt']         = '';
            $mst0201['smp1_saleprice']      = '0';
            $mst0201['smp1_cust_saleprice'] = '0';
            $mst0201['smp1_costprice']      = '0.00';
            $mst0201['smp1_point_kbn']      = '';
            $mst0201['smp2_str_dt']         = '';
            $mst0201['smp2_end_dt']         = '';
            $mst0201['smp2_saleprice']      = '0';
            $mst0201['smp2_cust_saleprice'] = '0';
            $mst0201['smp2_costprice']      = '0.00';
            $mst0201['smp2_point_kbn']      = '';
            $mst0201['sales_talk_kbn']      = '';
            $mst0201['sales_talk']          = '';
            $mst0201['switch_otc_kbn']      = '';
            $mst0201['prod_tax']            = '0.00';
            $mst0201['bb_date_kbn']         = '';
            $mst0201['cartone']             = '0';
            $mst0201['bb_days']             = '0';
            $mst0201['order_prod_cd1']      = '';
            $mst0201['order_prod_cd2']      = '';
            $mst0201['order_prod_cd3']      = '';
            $mst0201['case_costprice']      = '0.00';
            $mst0201['sect_tax_type']       = '';
            $mst0201['sect_tax']            = '0.00';
            $mst0201['profit']              = '0';
            $mst0201['profit_per']          = '0.00';
            $mst0201['total_stock_amout']   = '0';
            $mst0201['fill_amount']         = '0';
            $mst0201['case_stock_amout']    = '0';
            $mst0201['bowl_stock_amout']    = '0';
            $mst0201['piece_stock_amout']   = '0';
            $mst0201['endmon_amount']       = '0';
            $mst0201['nmondiff_amount']     = '0';
            $mst0201['emondiff_amount']     = '0';
            $mst0201['shelf_no'][0]         = '';
            $mst0201['shelf_no'][1]         = '';
            $mst0201['shelf_no'][2]         = '';
            $mst0201['insuser_nm']          = '';
            $mst0201['upduser_nm']          = '';
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
         * POS商品在庫更新記録トラン登録
         * @param    $postArray(POS商品マスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function insTrn3021Data($postArray, $upd_type)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insTrn3021Data");

            // POS商品在庫更新記録トランHIDESEQ取得
            $sql = "SELECT nextval('hideseq_trn3021') as hideseq_trn3021";
            $searchArray = array();
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            // データ取得ができなかった場合、エラーを返す
            if( $result === false )
            {
                // SQL実行エラー
                $Log->trace("END insTrn3021Data");
                return false;
            }
            $hideseq_trn3021 = '';
            // 取得したデータを格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $hideseq_trn3021 = $data['hideseq_trn3021'];
            }
            if ($hideseq_trn3021 === '') {
                // SQL実行エラー
                $Log->trace("END insTrn3021Data");
                return false;
            }

            // POS商品在庫更新記録トラン登録
            $parameters = array(
                ':insuser_cd'               => 'mportal',
                ':insdatetime'              => date('Y/m/d H:i:s'),
                ':upduser_cd'               => 'mportal',
                ':upddatetime'              => date('Y/m/d H:i:s'),
                ':disabled'                 => '0',
                ':lan_kbn'                  => '0',
                ':connect_kbn'              => '0',
                ':organization_id'          => $postArray['organization_id'],
                ':hideseq'                  => $hideseq_trn3021,
                ':proc_date'                => date('Ymd'),
                ':upd_type'                 => $upd_type,
                ':prod_cd'                  => $postArray['prod_cd'],
                ':location_no'              => '',
                ':case_stock_amout'         => $postArray['case_stock_amout_load'],
                ':bowl_stock_amout'         => $postArray['bowl_stock_amout_load'],
                ':piece_stock_amout'        => $postArray['piece_stock_amout_load'],
                ':total_stock_amout'        => str_replace(',', '', $postArray['total_stock_amout']),
                ':endmon_amount'            => $postArray['endmon_amount_load'],
                ':nmondiff_amount'          => $postArray['nmondiff_amount_load'],
                ':emondiff_amount'          => $postArray['emondiff_amount_load'],
                ':fill_amount'              => str_replace(',', '', $postArray['fill_amount']),
                ':fill_amount_bef'          => str_replace(',', '', $postArray['fill_amount_load']),
                ':van_data_kbn'             => '0',
                ':van_data_date'            => ''
            );

            $sql = 'INSERT INTO trn3021( '
                . '   insuser_cd'
                . ' , insdatetime'
                . ' , upduser_cd'
                . ' , upddatetime'
                . ' , disabled'
                . ' , lan_kbn'
                . ' , connect_kbn'
                . ' , organization_id'
                . ' , hideseq'
                . ' , proc_date'
                . ' , upd_type'
                . ' , prod_cd'
                . ' , location_no'
                . ' , case_stock_amout'
                . ' , bowl_stock_amout'
                . ' , piece_stock_amout'
                . ' , total_stock_amout'
                . ' , endmon_amount'
                . ' , nmondiff_amount'
                . ' , emondiff_amount'
                . ' , fill_amount'
                . ' , fill_amount_bef'
                . ' , van_data_kbn'
                . ' , van_data_date'
                . ' ) VALUES ('
                . '   :insuser_cd'
                . ' , :insdatetime'
                . ' , :upduser_cd'
                . ' , :upddatetime'
                . ' , :disabled'
                . ' , :lan_kbn'
                . ' , :connect_kbn'
                . ' , :organization_id'
                . ' , :hideseq'
                . ' , :proc_date'
                . ' , :upd_type'
                . ' , :prod_cd'
                . ' , :location_no'
                . ' , :case_stock_amout'
                . ' , :bowl_stock_amout'
                . ' , :piece_stock_amout'
                . ' , :total_stock_amout'
                . ' , :endmon_amount'
                . ' , :nmondiff_amount'
                . ' , :emondiff_amount'
                . ' , :fill_amount'
                . ' , :fill_amount_bef'
                . ' , :van_data_kbn'
                . ' , :van_data_date'
                . ' )';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->trace("END insTrn3021Data");
                return false;
            }

            $Log->trace("END insTrn3021Data");
            return true;
        }
        
        /**
         * POS商品在庫修正記録トラン登録
         * @param    $postArray(POS商品マスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function insTrn3025Data($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insTrn3025Data");

            // POS商品在庫更新記録トランHIDESEQ取得
            $sql = "SELECT nextval('hideseq_trn3025') as hideseq_trn3025";
            $searchArray = array();
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            // データ取得ができなかった場合、エラーを返す
            if( $result === false )
            {
                // SQL実行エラー
                $Log->trace("END insTrn3025Data");
                return false;
            }
            $hideseq_trn3025 = '';
            // 取得したデータを格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $hideseq_trn3025 = $data['hideseq_trn3025'];
            }
            if ($hideseq_trn3025 === '') {
                // SQL実行エラー
                $Log->trace("END insTrn3025Data");
                return false;
            }

            // POS商品在庫更新記録トラン登録
            $parameters = array(
                ':insuser_cd'               => 'mportal',
                ':insdatetime'              => date('Y/m/d H:i:s'),
                ':upduser_cd'               => 'mportal',
                ':upddatetime'              => date('Y/m/d H:i:s'),
                ':disabled'                 => '0',
                ':lan_kbn'                  => '0',
                ':connect_kbn'              => '0',
                ':organization_id'          => $postArray['organization_id'],
                ':hideseq'                  => $hideseq_trn3025,
                ':proc_date'                => date('YmdHis'),
                ':reji_no'                  => '',
                ':shop_cd'                  => $postArray['shop_cd'],
                ':staff_cd'                 => 'mportal',
                ':prod_cd'                  => $postArray['prod_cd'],
                ':edit_bff_stock_amout'     => str_replace(',', '', $postArray['total_stock_amout_load']),
                ':edit_aft_stock_amout'     => str_replace(',', '', $postArray['total_stock_amout'])
            );

            $sql = 'INSERT INTO trn3025( '
                . '   insuser_cd'
                . ' , insdatetime'
                . ' , upduser_cd'
                . ' , upddatetime'
                . ' , disabled'
                . ' , lan_kbn'
                . ' , connect_kbn'
                . ' , organization_id'
                . ' , hideseq'
                . ' , proc_date'
                . ' , reji_no'
                . ' , shop_cd'
                . ' , staff_cd'
                . ' , prod_cd'
                . ' , edit_bff_stock_amout'
                . ' , edit_aft_stock_amout'
                . ' ) VALUES ('
                . '   :insuser_cd'
                . ' , :insdatetime'
                . ' , :upduser_cd'
                . ' , :upddatetime'
                . ' , :disabled'
                . ' , :lan_kbn'
                . ' , :connect_kbn'
                . ' , :organization_id'
                . ' , :hideseq'
                . ' , :proc_date'
                . ' , :reji_no'
                . ' , :shop_cd'
                . ' , :staff_cd'
                . ' , :prod_cd'
                . ' , :edit_bff_stock_amout'
                . ' , :edit_aft_stock_amout'
                . ' )';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->trace("END insTrn3025Data");
                return false;
            }

            $Log->trace("END insTrn3025Data");
            return true;
        }
        
        /**
         * POS商品在庫マスタ(棚番)登録
         * @param    $postArray(POS商品マスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function insMst0204LocationData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insMst0204LocationData");

            // POS商品在庫マスタ削除
            $sql = "DELETE FROM mst0204 WHERE organization_id = :organization_id AND prod_cd = :prod_cd AND location_no <> ''";
            $parameters = array(
                ':organization_id'      => $postArray['organization_id'],
                ':prod_cd'              => $postArray['prod_cd'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー
                $Log->trace("END insMst0204LocationData");
                return false;
            }

            // POS商品在庫マスタ登録
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
                ':location_no'              => '',
                ':case_stock_amout'         => '0',
                ':bowl_stock_amout'         => '0',
                ':piece_stock_amout'        => '0',
                ':total_stock_amout'        => $postArray['total_stock_amout'] === '' ? '0' : str_replace(',', '', $postArray['total_stock_amout']),
                ':endmon_amount'            => '0',
                ':nmondiff_amount'          => '0',
                ':emondiff_amount'          => '0',
                ':fill_amount'              => $postArray['fill_amount'] === '' ? '0' : str_replace(',', '', $postArray['fill_amount']),
                ':fill_amount_bef'          => '0',
                ':bb_date'                  => ''
            );
            
            $sql = 'INSERT INTO mst0204( '
                . '   insuser_cd'
                . ' , insdatetime'
                . ' , upduser_cd'
                . ' , upddatetime'
                . ' , disabled'
                . ' , lan_kbn'
                . ' , connect_kbn'
                . ' , organization_id'
                . ' , prod_cd'
                . ' , location_no'
                . ' , case_stock_amout'
                . ' , bowl_stock_amout'
                . ' , piece_stock_amout'
                . ' , total_stock_amout'
                . ' , endmon_amount'
                . ' , nmondiff_amount'
                . ' , emondiff_amount'
                . ' , fill_amount'
                . ' , fill_amount_bef'
                . ' , bb_date'
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
                . ' , :location_no'
                . ' , :case_stock_amout'
                . ' , :bowl_stock_amout'
                . ' , :piece_stock_amout'
                . ' , :total_stock_amout'
                . ' , :endmon_amount'
                . ' , :nmondiff_amount'
                . ' , :emondiff_amount'
                . ' , :fill_amount'
                . ' , :fill_amount_bef'
                . ' , :bb_date'
                . ' )';

            for ($intL = 0; $intL < 3; $intL ++) {
                if ($postArray['shelf_no'][$intL] !== '') {
                    $parameters[':location_no'] = $postArray['shelf_no'][$intL];

                    // SQL実行
                    if( !$DBA->executeSQL($sql, $parameters) )
                    {
                        // SQL実行エラー
                        $Log->trace("END insMst0204LocationData");
                        return false;
                    }
                }
            }

            $Log->trace("END insMst0204LocationData");
            return true;
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

            for ($intL = 0; $intL < Count($sqlArray); $intL ++) {
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