<?php
    /**
     * @file      POS予約商品マスタコントローラ
     * @author    川橋
     * @date      2019/01/21
     * @version   1.00
     * @note      POS予約商品マスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // POS商品マスタモデル
    require './Model/Mst0211.php';
    
    /**
     * POS商品マスタコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst0211Controller extends BaseController
    {

        protected $mst0211 = null;     ///< POS商品マスタモデル

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
            global $mst0211;
            $mst0211 = new Mst0211();
        }

         /**
         * デストラクタ
         */
        public function __destruct()
        {
            // BaseControllerのデストラクタ
            parent::__destruct();
        }

        /**
         * POS商品マスタ一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START mst0211 showAction");

            $startFlag = true;

            $this->initialDisplay($startFlag);
            $Log->trace("END mst0211 showAction");
        }
        
        /**
         * POS商品マスタ一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START mst0211 searchAction" );
            
            $startFlag = false;
            
            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = $_POST['displayPageNo'];
            }
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = $_POST['displayRecordCnt'];
            }

            $this->initialDisplay($startFlag);
            
            $Log->trace("END mst0211 searchAction");
            
        }

        /**
         * POS商品マスタ入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START mst0211 addInputAction" );

            $this->Mst0211InputPanelDisplay();

            $Log->trace("END mst0211 addInputAction");
            
        }

        /**
         * POS商品マスタ新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START mst0211 addAction" );

            $mst0211 = new Mst0211();

            $startFlag = true;
            $message = "MSG_BASE_0000";
            
            $postArray = array(
                'organization_id'           => parent::escStr( $_POST['organization_id'] ),
                'prod_cd'                   => parent::escStr( $_POST['prod_cd'] ),
                'change_dt'                 => parent::escStr( $_POST['change_dt'] ),
                'jan_cd'                    => parent::escStr( $_POST['jan_cd'] ),
                'itf_cd'                    => parent::escStr( $_POST['itf_cd'] ),
                'prod_nm'                   => parent::escStr( $_POST['prod_nm'] ),
                'prod_kn'                   => parent::escStr( $_POST['prod_kn'] ),
                'prod_kn_rk'                => parent::escStr( $_POST['prod_kn_rk'] ),
                'prod_capa_kn'              => parent::escStr( $_POST['prod_capa_kn'] ),
                'prod_capa_nm'              => parent::escStr( $_POST['prod_capa_nm'] ),
                'disp_prod_nm1'             => parent::escStr( $_POST['disp_prod_nm1'] ),
                'disp_prod_nm2'             => parent::escStr( $_POST['disp_prod_nm2'] ),
                'case_piece'                => parent::escStr( $_POST['case_piece'] ),
                'case_bowl'                 => parent::escStr( $_POST['case_bowl'] ),
                'bowl_piece'                => parent::escStr( $_POST['bowl_piece'] ),
                'sect_cd'                   => parent::escStr( $_POST['sect_cd'] ),
                'priv_class_cd'             => parent::escStr( $_POST['priv_class_cd'] ),
                'jicfs_class_cd'            => parent::escStr( $_POST['jicfs_class_cd'] ),
                'maker_cd'                  => parent::escStr( $_POST['maker_cd'] ),
                'head_supp_cd'              => parent::escStr( $_POST['head_supp_cd'] ),
                'shop_supp_cd'              => parent::escStr( $_POST['shop_supp_cd'] ),
                'serial_no'                 => parent::escStr( $_POST['serial_no'] ),
                'prod_note'                 => parent::escStr( $_POST['prod_note'] ),
                'priv_prod_cd'              => parent::escStr( $_POST['priv_prod_cd'] ),
                'alcohol_cd'                => parent::escStr( $_POST['alcohol_cd'] ),
                'alcohol_capa_amount'       => parent::escStr( $_POST['alcohol_capa_amount'] ),
                'order_lot'                 => parent::escStr( $_POST['order_lot'] ),
                'return_lot'                => parent::escStr( $_POST['return_lot'] ),
                'just_stock_amout'          => parent::escStr( $_POST['just_stock_amout'] ),
                'appo_prod_kbn'             => parent::escStr( $_POST['appo_prod_kbn'] ),
                'appo_medi_kbn'             => parent::escStr( $_POST['appo_medi_kbn'] ),
                'tax_type'                  => parent::escStr( $_POST['tax_type'] ),
                'order_stop_kbn'            => parent::escStr( $_POST['order_stop_kbn'] ),
                'noreturn_kbn'              => parent::escStr( $_POST['noreturn_kbn'] ),
                'point_kbn'                 => parent::escStr( $_POST['point_kbn'] ),
                'point_magni'               => parent::escStr( $_POST['point_magni'] ),
                'p_calc_prod_point'         => parent::escStr( $_POST['p_calc_prod_point'] ),
                
//                'dir_del_kbn'         => parent::escStr( $_POST['dir_del_kbn'] ), 一時的に封印
//                'sale_kbn'         => parent::escStr( $_POST['sale_kbn'] ),一時的に封印
                
                
                'p_calc_prod_money'         => parent::escStr( $_POST['p_calc_prod_money'] ),
                'auto_order_kbn'            => parent::escStr( $_POST['auto_order_kbn'] ),
                'risk_type_kbn'             => parent::escStr( $_POST['risk_type_kbn'] ),
                'order_fin_kbn'             => parent::escStr( $_POST['order_fin_kbn'] ),
                'resale_kbn'                => parent::escStr( $_POST['resale_kbn'] ),
                'investiga_kbn'             => parent::escStr( $_POST['investiga_kbn'] ),
                'disc_not_kbn'              => parent::escStr( $_POST['disc_not_kbn'] ),
                'regi_duty_kbn'             => parent::escStr( $_POST['regi_duty_kbn'] ),
                'set_prod_kbn'              => parent::escStr( $_POST['set_prod_kbn'] ),
                'bundle_kbn'                => parent::escStr( $_POST['bundle_kbn'] ),
                'mixture_kbn'               => parent::escStr( $_POST['mixture_kbn'] ),
                'sale_period_kbn'           => parent::escStr( $_POST['sale_period_kbn'] ),
                'delete_kbn'                => parent::escStr( $_POST['delete_kbn'] ),
                'order_can_kbn'             => parent::escStr( $_POST['order_can_kbn'] ),
                'order_can_date'            => parent::escStr( $_POST['order_can_date'] ),
                'prod_t_cd1'                => parent::escStr( $_POST['prod_t_cd1'] ),
                'prod_t_cd2'                => parent::escStr( $_POST['prod_t_cd2'] ),
                'prod_t_cd3'                => parent::escStr( $_POST['prod_t_cd3'] ),
                'prod_t_cd4'                => parent::escStr( $_POST['prod_t_cd4'] ),
                'prod_k_cd1'                => parent::escStr( $_POST['prod_k_cd1'] ),
                'prod_k_cd2'                => parent::escStr( $_POST['prod_k_cd2'] ),
                'prod_k_cd3'                => parent::escStr( $_POST['prod_k_cd3'] ),
                'prod_k_cd4'                => parent::escStr( $_POST['prod_k_cd4'] ),
                'prod_k_cd5'                => parent::escStr( $_POST['prod_k_cd5'] ),
                'prod_k_cd6'                => parent::escStr( $_POST['prod_k_cd6'] ),
                'prod_k_cd7'                => parent::escStr( $_POST['prod_k_cd7'] ),
                'prod_k_cd8'                => parent::escStr( $_POST['prod_k_cd8'] ),
                'prod_k_cd9'                => parent::escStr( $_POST['prod_k_cd9'] ),
                'prod_k_cd10'               => parent::escStr( $_POST['prod_k_cd10'] ),
                'prod_res_cd1'              => parent::escStr( $_POST['prod_res_cd1'] ),
                'prod_res_cd2'              => parent::escStr( $_POST['prod_res_cd2'] ),
                'prod_res_cd3'              => parent::escStr( $_POST['prod_res_cd3'] ),
                'prod_res_cd4'              => parent::escStr( $_POST['prod_res_cd4'] ),
                'prod_res_cd5'              => parent::escStr( $_POST['prod_res_cd5'] ),
                'prod_res_cd6'              => parent::escStr( $_POST['prod_res_cd6'] ),
                'prod_res_cd7'              => parent::escStr( $_POST['prod_res_cd7'] ),
                'prod_res_cd8'              => parent::escStr( $_POST['prod_res_cd8'] ),
                'prod_res_cd9'              => parent::escStr( $_POST['prod_res_cd9'] ),
                'prod_res_cd10'             => parent::escStr( $_POST['prod_res_cd10'] ),
                'prod_comment'              => parent::escStr( $_POST['prod_comment'] ),
                'shelf_block1_1'            => parent::escStr( $_POST['shelf_block1_1'] ),
                'shelf_block1_2'            => parent::escStr( $_POST['shelf_block1_2'] ),
                'shelf_block1_3'            => parent::escStr( $_POST['shelf_block1_3'] ),
                'shelf_block2_1'            => parent::escStr( $_POST['shelf_block2_1'] ),
                'shelf_block2_2'            => parent::escStr( $_POST['shelf_block2_2'] ),
                'shelf_block2_3'            => parent::escStr( $_POST['shelf_block2_3'] ),
                'fixeprice'                 => parent::escStr( $_POST['fixeprice'] ),
                'saleprice'                 => parent::escStr( $_POST['saleprice'] ),
                'saleprice_ex'              => parent::escStr( $_POST['saleprice_ex'] ),
                'base_saleprice'            => parent::escStr( $_POST['base_saleprice'] ),
                'base_saleprice_ex'         => parent::escStr( $_POST['base_saleprice_ex'] ),
                'cust_saleprice'            => parent::escStr( $_POST['cust_saleprice'] ),
                'head_costprice'            => parent::escStr( $_POST['head_costprice'] ),
                'shop_costprice'            => parent::escStr( $_POST['shop_costprice'] ),
                'contract_price'            => parent::escStr( $_POST['contract_price'] ),
                'empl_saleprice'            => parent::escStr( $_POST['empl_saleprice'] ),
                'spcl_saleprice1'           => parent::escStr( $_POST['spcl_saleprice1'] ),
                'spcl_saleprice2'           => parent::escStr( $_POST['spcl_saleprice2'] ),
                'saleprice_limit'           => parent::escStr( $_POST['saleprice_limit'] ),
                'saleprice_ex_limit'        => parent::escStr( $_POST['saleprice_ex_limit'] ),
                'time_saleprice'            => parent::escStr( $_POST['time_saleprice'] ),
                'time_saleamount'           => parent::escStr( $_POST['time_saleamount'] ),
                'smp1_str_dt'               => parent::escStr( $_POST['smp1_str_dt'] ),
                'smp1_end_dt'               => parent::escStr( $_POST['smp1_end_dt'] ),
                'smp1_saleprice'            => parent::escStr( $_POST['smp1_saleprice'] ),
                'smp1_cust_saleprice'       => parent::escStr( $_POST['smp1_cust_saleprice'] ),
                'smp1_costprice'            => parent::escStr( $_POST['smp1_costprice'] ),
                'smp1_point_kbn'            => parent::escStr( $_POST['smp1_point_kbn'] ),
                'smp2_str_dt'               => parent::escStr( $_POST['smp2_str_dt'] ),
                'smp2_end_dt'               => parent::escStr( $_POST['smp2_end_dt'] ),
                'smp2_saleprice'            => parent::escStr( $_POST['smp2_saleprice'] ),
                'smp2_cust_saleprice'       => parent::escStr( $_POST['smp2_cust_saleprice'] ),
                'smp2_costprice'            => parent::escStr( $_POST['smp2_costprice'] ),
                'smp2_point_kbn'            => parent::escStr( $_POST['smp2_point_kbn'] ),
                'sales_talk_kbn'            => parent::escStr( $_POST['sales_talk_kbn'] ),
                'sales_talk'                => parent::escStr( $_POST['sales_talk'] ),
                'switch_otc_kbn'            => parent::escStr( $_POST['switch_otc_kbn'] ),
                'prod_tax'                  => parent::escStr( $_POST['prod_tax'] ),
                'bb_date_kbn'               => parent::escStr( $_POST['bb_date_kbn'] ),
                'cartone'                   => parent::escStr( $_POST['cartone'] ),
                'bb_days'                   => parent::escStr( $_POST['bb_days'] ),
                'order_prod_cd1'            => parent::escStr( $_POST['order_prod_cd1'] ),
                'order_prod_cd2'            => parent::escStr( $_POST['order_prod_cd2'] ),
                'order_prod_cd3'            => parent::escStr( $_POST['order_prod_cd3'] ),
                'case_costprice'            => parent::escStr( $_POST['case_costprice'] ),
                'total_stock_amout'         => parent::escStr( $_POST['total_stock_amout'] ),
                'fill_amount'               => parent::escStr( $_POST['fill_amount'] ),
                'total_stock_amout_load'    => parent::escStr( $_POST['total_stock_amout_load'] ),
                'fill_amount_load'          => parent::escStr( $_POST['fill_amount_load'] ),
                'case_stock_amout_load'     => parent::escStr( $_POST['case_stock_amout_load'] ),
                'bowl_stock_amout_load'     => parent::escStr( $_POST['bowl_stock_amout_load'] ),
                'piece_stock_amout_load'    => parent::escStr( $_POST['piece_stock_amout_load'] ),
                'endmon_amount_load'        => parent::escStr( $_POST['endmon_amount_load'] ),
                'nmondiff_amount_load'      => parent::escStr( $_POST['nmondiff_amount_load'] ),
                'emondiff_amount_load'      => parent::escStr( $_POST['emondiff_amount_load'] ),
                'shelf_no1_1'               => parent::escStr( $_POST['shelf_no1_1'] ),
                'shelf_no1_2'               => parent::escStr( $_POST['shelf_no1_2'] ),
                'shelf_no1_3'               => parent::escStr( $_POST['shelf_no1_3'] ),
                'shelf_no2_1'               => parent::escStr( $_POST['shelf_no2_1'] ),
                'shelf_no2_2'               => parent::escStr( $_POST['shelf_no2_2'] ),
                'shelf_no2_3'               => parent::escStr( $_POST['shelf_no2_3'] ),
                'shelf_no3_1'               => parent::escStr( $_POST['shelf_no3_1'] ),
                'shelf_no3_2'               => parent::escStr( $_POST['shelf_no3_2'] ),
                'shelf_no3_3'               => parent::escStr( $_POST['shelf_no3_3'] ),
                'shop_cd'                   => parent::escStr( $_POST['shop_cd'] ),
            );

            // 変更実施日
            $postArray['change_dt'] = str_replace('/', '', $postArray['change_dt']);

            // 非課税
            if ($postArray['tax_type'] === '9') {
                $postArray['prod_tax'] = '0.00';
            }
            
            //簡易特売期間
            $postArray['smp1_str_dt'] = str_replace('/', '', $postArray['smp1_str_dt']);
            $postArray['smp1_end_dt'] = str_replace('/', '', $postArray['smp1_end_dt']);
            $postArray['smp2_str_dt'] = str_replace('/', '', $postArray['smp2_str_dt']);
            $postArray['smp2_end_dt'] = str_replace('/', '', $postArray['smp2_end_dt']);

            // ブロック結合（ゼロ埋めとか一箇所入力したら残りの未入力もゼロで埋めるとかはJavaScriptで処理する方針で）
            $postArray = array_merge($postArray, array(
                    'shelf_block1' => $postArray['shelf_block1_1'].$postArray['shelf_block1_2'].$postArray['shelf_block1_3'],
                    'shelf_block2' => $postArray['shelf_block2_1'].$postArray['shelf_block2_2'].$postArray['shelf_block2_3']
                )
            );

            // 棚番結合（ゼロ埋めとか一箇所入力したら残りの未入力もゼロで埋めるとかはJavaScriptで処理する方針で）
            $postArray = array_merge($postArray, array(
                    'shelf_no' => array(
                        $postArray['shelf_no1_1'].$postArray['shelf_no1_2'].$postArray['shelf_no1_3'],
                        $postArray['shelf_no2_1'].$postArray['shelf_no2_2'].$postArray['shelf_no2_3'],
                        $postArray['shelf_no3_1'].$postArray['shelf_no3_2'].$postArray['shelf_no3_3']
                    )
                )
            );

            // 新規登録
            $message = $mst0211->addNewData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
            $Log->trace("END mst0211 addAction");
        }

        /**
         * POS商品マスタ新規登録処理
         * @return    なし
         */
        public function allAddAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START mst0211 addAction" );

            $mst0211 = new Mst0211();

            $startFlag = true;
            $message = "MSG_BASE_0000";
            
            // 登録用所属組織プルダウン
            $abbreviatedList        = $mst0211->setPulldown->getSearchOrganizationList( 'registration', true, true );

            // 組織数分実行
            foreach($abbreviatedList as $data){
                if($data['organization_id'] != '' ){
                    $postArray = array(
                        'organization_id'           => $data['organization_id'],
                        'prod_cd'                   => parent::escStr( $_POST['prod_cd'] ),
                        'change_dt'                 => parent::escStr( $_POST['change_dt'] ),
                        'jan_cd'                    => parent::escStr( $_POST['jan_cd'] ),
                        'itf_cd'                    => parent::escStr( $_POST['itf_cd'] ),
                        'prod_nm'                   => parent::escStr( $_POST['prod_nm'] ),
                        'prod_kn'                   => parent::escStr( $_POST['prod_kn'] ),
                        'prod_kn_rk'                => parent::escStr( $_POST['prod_kn_rk'] ),
                        'prod_capa_kn'              => parent::escStr( $_POST['prod_capa_kn'] ),
                        'prod_capa_nm'              => parent::escStr( $_POST['prod_capa_nm'] ),
                        'disp_prod_nm1'             => parent::escStr( $_POST['disp_prod_nm1'] ),
                        'disp_prod_nm2'             => parent::escStr( $_POST['disp_prod_nm2'] ),
                        'case_piece'                => parent::escStr( $_POST['case_piece'] ),
                        'case_bowl'                 => parent::escStr( $_POST['case_bowl'] ),
                        'bowl_piece'                => parent::escStr( $_POST['bowl_piece'] ),
                        'sect_cd'                   => parent::escStr( $_POST['sect_cd'] ),
                        'priv_class_cd'             => parent::escStr( $_POST['priv_class_cd'] ),
                        'jicfs_class_cd'            => parent::escStr( $_POST['jicfs_class_cd'] ),
                        'maker_cd'                  => parent::escStr( $_POST['maker_cd'] ),
                        'head_supp_cd'              => parent::escStr( $_POST['head_supp_cd'] ),
                        'shop_supp_cd'              => parent::escStr( $_POST['shop_supp_cd'] ),
                        'serial_no'                 => parent::escStr( $_POST['serial_no'] ),
                        'prod_note'                 => parent::escStr( $_POST['prod_note'] ),
                        'priv_prod_cd'              => parent::escStr( $_POST['priv_prod_cd'] ),
                        'alcohol_cd'                => parent::escStr( $_POST['alcohol_cd'] ),
                        'alcohol_capa_amount'       => parent::escStr( $_POST['alcohol_capa_amount'] ),
                        'order_lot'                 => parent::escStr( $_POST['order_lot'] ),
                        'return_lot'                => parent::escStr( $_POST['return_lot'] ),
                        'just_stock_amout'          => parent::escStr( $_POST['just_stock_amout'] ),
                        'appo_prod_kbn'             => parent::escStr( $_POST['appo_prod_kbn'] ),
                        'appo_medi_kbn'             => parent::escStr( $_POST['appo_medi_kbn'] ),
                        'tax_type'                  => parent::escStr( $_POST['tax_type'] ),
                        'order_stop_kbn'            => parent::escStr( $_POST['order_stop_kbn'] ),
                        'noreturn_kbn'              => parent::escStr( $_POST['noreturn_kbn'] ),
                        'point_kbn'                 => parent::escStr( $_POST['point_kbn'] ),
                        'point_magni'               => parent::escStr( $_POST['point_magni'] ),
                        'p_calc_prod_point'         => parent::escStr( $_POST['p_calc_prod_point'] ),
                        
//                        'dir_del_kbn'         => parent::escStr( $_POST['dir_del_kbn'] ),一時的に封印
//                        'sale_kbn'            => parent::escStr( $_POST['sale_kbn'] ),一時的に封印
                        
                        
                        'p_calc_prod_money'         => parent::escStr( $_POST['p_calc_prod_money'] ),
                        'auto_order_kbn'            => parent::escStr( $_POST['auto_order_kbn'] ),
                        'risk_type_kbn'             => parent::escStr( $_POST['risk_type_kbn'] ),
                        'order_fin_kbn'             => parent::escStr( $_POST['order_fin_kbn'] ),
                        'resale_kbn'                => parent::escStr( $_POST['resale_kbn'] ),
                        'investiga_kbn'             => parent::escStr( $_POST['investiga_kbn'] ),
                        'disc_not_kbn'              => parent::escStr( $_POST['disc_not_kbn'] ),
                        'regi_duty_kbn'             => parent::escStr( $_POST['regi_duty_kbn'] ),
                        'set_prod_kbn'              => parent::escStr( $_POST['set_prod_kbn'] ),
                        'bundle_kbn'                => parent::escStr( $_POST['bundle_kbn'] ),
                        'mixture_kbn'               => parent::escStr( $_POST['mixture_kbn'] ),
                        'sale_period_kbn'           => parent::escStr( $_POST['sale_period_kbn'] ),
                        'delete_kbn'                => parent::escStr( $_POST['delete_kbn'] ),
                        'order_can_kbn'             => parent::escStr( $_POST['order_can_kbn'] ),
                        'order_can_date'            => parent::escStr( $_POST['order_can_date'] ),
                        'prod_t_cd1'                => parent::escStr( $_POST['prod_t_cd1'] ),
                        'prod_t_cd2'                => parent::escStr( $_POST['prod_t_cd2'] ),
                        'prod_t_cd3'                => parent::escStr( $_POST['prod_t_cd3'] ),
                        'prod_t_cd4'                => parent::escStr( $_POST['prod_t_cd4'] ),
                        'prod_k_cd1'                => parent::escStr( $_POST['prod_k_cd1'] ),
                        'prod_k_cd2'                => parent::escStr( $_POST['prod_k_cd2'] ),
                        'prod_k_cd3'                => parent::escStr( $_POST['prod_k_cd3'] ),
                        'prod_k_cd4'                => parent::escStr( $_POST['prod_k_cd4'] ),
                        'prod_k_cd5'                => parent::escStr( $_POST['prod_k_cd5'] ),
                        'prod_k_cd6'                => parent::escStr( $_POST['prod_k_cd6'] ),
                        'prod_k_cd7'                => parent::escStr( $_POST['prod_k_cd7'] ),
                        'prod_k_cd8'                => parent::escStr( $_POST['prod_k_cd8'] ),
                        'prod_k_cd9'                => parent::escStr( $_POST['prod_k_cd9'] ),
                        'prod_k_cd10'               => parent::escStr( $_POST['prod_k_cd10'] ),
                        'prod_res_cd1'              => parent::escStr( $_POST['prod_res_cd1'] ),
                        'prod_res_cd2'              => parent::escStr( $_POST['prod_res_cd2'] ),
                        'prod_res_cd3'              => parent::escStr( $_POST['prod_res_cd3'] ),
                        'prod_res_cd4'              => parent::escStr( $_POST['prod_res_cd4'] ),
                        'prod_res_cd5'              => parent::escStr( $_POST['prod_res_cd5'] ),
                        'prod_res_cd6'              => parent::escStr( $_POST['prod_res_cd6'] ),
                        'prod_res_cd7'              => parent::escStr( $_POST['prod_res_cd7'] ),
                        'prod_res_cd8'              => parent::escStr( $_POST['prod_res_cd8'] ),
                        'prod_res_cd9'              => parent::escStr( $_POST['prod_res_cd9'] ),
                        'prod_res_cd10'             => parent::escStr( $_POST['prod_res_cd10'] ),
                        'prod_comment'              => parent::escStr( $_POST['prod_comment'] ),
                        'shelf_block1_1'            => parent::escStr( $_POST['shelf_block1_1'] ),
                        'shelf_block1_2'            => parent::escStr( $_POST['shelf_block1_2'] ),
                        'shelf_block1_3'            => parent::escStr( $_POST['shelf_block1_3'] ),
                        'shelf_block2_1'            => parent::escStr( $_POST['shelf_block2_1'] ),
                        'shelf_block2_2'            => parent::escStr( $_POST['shelf_block2_2'] ),
                        'shelf_block2_3'            => parent::escStr( $_POST['shelf_block2_3'] ),
                        'fixeprice'                 => parent::escStr( $_POST['fixeprice'] ),
                        'saleprice'                 => parent::escStr( $_POST['saleprice'] ),
                        'saleprice_ex'              => parent::escStr( $_POST['saleprice_ex'] ),
                        'base_saleprice'            => parent::escStr( $_POST['base_saleprice'] ),
                        'base_saleprice_ex'         => parent::escStr( $_POST['base_saleprice_ex'] ),
                        'cust_saleprice'            => parent::escStr( $_POST['cust_saleprice'] ),
                        'head_costprice'            => parent::escStr( $_POST['head_costprice'] ),
                        'shop_costprice'            => parent::escStr( $_POST['shop_costprice'] ),
                        'contract_price'            => parent::escStr( $_POST['contract_price'] ),
                        'empl_saleprice'            => parent::escStr( $_POST['empl_saleprice'] ),
                        'spcl_saleprice1'           => parent::escStr( $_POST['spcl_saleprice1'] ),
                        'spcl_saleprice2'           => parent::escStr( $_POST['spcl_saleprice2'] ),
                        'saleprice_limit'           => parent::escStr( $_POST['saleprice_limit'] ),
                        'saleprice_ex_limit'        => parent::escStr( $_POST['saleprice_ex_limit'] ),
                        'time_saleprice'            => parent::escStr( $_POST['time_saleprice'] ),
                        'time_saleamount'           => parent::escStr( $_POST['time_saleamount'] ),
                        'smp1_str_dt'               => parent::escStr( $_POST['smp1_str_dt'] ),
                        'smp1_end_dt'               => parent::escStr( $_POST['smp1_end_dt'] ),
                        'smp1_saleprice'            => parent::escStr( $_POST['smp1_saleprice'] ),
                        'smp1_cust_saleprice'       => parent::escStr( $_POST['smp1_cust_saleprice'] ),
                        'smp1_costprice'            => parent::escStr( $_POST['smp1_costprice'] ),
                        'smp1_point_kbn'            => parent::escStr( $_POST['smp1_point_kbn'] ),
                        'smp2_str_dt'               => parent::escStr( $_POST['smp2_str_dt'] ),
                        'smp2_end_dt'               => parent::escStr( $_POST['smp2_end_dt'] ),
                        'smp2_saleprice'            => parent::escStr( $_POST['smp2_saleprice'] ),
                        'smp2_cust_saleprice'       => parent::escStr( $_POST['smp2_cust_saleprice'] ),
                        'smp2_costprice'            => parent::escStr( $_POST['smp2_costprice'] ),
                        'smp2_point_kbn'            => parent::escStr( $_POST['smp2_point_kbn'] ),
                        'sales_talk_kbn'            => parent::escStr( $_POST['sales_talk_kbn'] ),
                        'sales_talk'                => parent::escStr( $_POST['sales_talk'] ),
                        'switch_otc_kbn'            => parent::escStr( $_POST['switch_otc_kbn'] ),
                        'prod_tax'                  => parent::escStr( $_POST['prod_tax'] ),
                        'bb_date_kbn'               => parent::escStr( $_POST['bb_date_kbn'] ),
                        'cartone'                   => parent::escStr( $_POST['cartone'] ),
                        'bb_days'                   => parent::escStr( $_POST['bb_days'] ),
                        'order_prod_cd1'            => parent::escStr( $_POST['order_prod_cd1'] ),
                        'order_prod_cd2'            => parent::escStr( $_POST['order_prod_cd2'] ),
                        'order_prod_cd3'            => parent::escStr( $_POST['order_prod_cd3'] ),
                        'case_costprice'            => parent::escStr( $_POST['case_costprice'] ),
                        'total_stock_amout'         => parent::escStr( $_POST['total_stock_amout'] ),
                        'fill_amount'               => parent::escStr( $_POST['fill_amount'] ),
                        'total_stock_amout_load'    => parent::escStr( $_POST['total_stock_amout_load'] ),
                        'fill_amount_load'          => parent::escStr( $_POST['fill_amount_load'] ),
                        'case_stock_amout_load'     => parent::escStr( $_POST['case_stock_amout_load'] ),
                        'bowl_stock_amout_load'     => parent::escStr( $_POST['bowl_stock_amout_load'] ),
                        'piece_stock_amout_load'    => parent::escStr( $_POST['piece_stock_amout_load'] ),
                        'endmon_amount_load'        => parent::escStr( $_POST['endmon_amount_load'] ),
                        'nmondiff_amount_load'      => parent::escStr( $_POST['nmondiff_amount_load'] ),
                        'emondiff_amount_load'      => parent::escStr( $_POST['emondiff_amount_load'] ),
                        'shelf_no1_1'               => parent::escStr( $_POST['shelf_no1_1'] ),
                        'shelf_no1_2'               => parent::escStr( $_POST['shelf_no1_2'] ),
                        'shelf_no1_3'               => parent::escStr( $_POST['shelf_no1_3'] ),
                        'shelf_no2_1'               => parent::escStr( $_POST['shelf_no2_1'] ),
                        'shelf_no2_2'               => parent::escStr( $_POST['shelf_no2_2'] ),
                        'shelf_no2_3'               => parent::escStr( $_POST['shelf_no2_3'] ),
                        'shelf_no3_1'               => parent::escStr( $_POST['shelf_no3_1'] ),
                        'shelf_no3_2'               => parent::escStr( $_POST['shelf_no3_2'] ),
                        'shelf_no3_3'               => parent::escStr( $_POST['shelf_no3_3'] ),
                        'shop_cd'                   => parent::escStr( $_POST['shop_cd'] ),
                    );

                    // 変更実施日
                    $postArray['change_dt'] = str_replace('/', '', $postArray['change_dt']);

                    // 非課税
                    if ($postArray['tax_type'] === '9') {
                        $postArray['prod_tax'] = '0.00';
                    }

                    //簡易特売期間
                    $postArray['smp1_str_dt'] = str_replace('/', '', $postArray['smp1_str_dt']);
                    $postArray['smp1_end_dt'] = str_replace('/', '', $postArray['smp1_end_dt']);
                    $postArray['smp2_str_dt'] = str_replace('/', '', $postArray['smp2_str_dt']);
                    $postArray['smp2_end_dt'] = str_replace('/', '', $postArray['smp2_end_dt']);

                    // ブロック結合（ゼロ埋めとか一箇所入力したら残りの未入力もゼロで埋めるとかはJavaScriptで処理する方針で）
                    $postArray = array_merge($postArray, array(
                            'shelf_block1' => $postArray['shelf_block1_1'].$postArray['shelf_block1_2'].$postArray['shelf_block1_3'],
                            'shelf_block2' => $postArray['shelf_block2_1'].$postArray['shelf_block2_2'].$postArray['shelf_block2_3']
                        )
                    );

                    // 棚番結合（ゼロ埋めとか一箇所入力したら残りの未入力もゼロで埋めるとかはJavaScriptで処理する方針で）
                    $postArray = array_merge($postArray, array(
                            'shelf_no' => array(
                                $postArray['shelf_no1_1'].$postArray['shelf_no1_2'].$postArray['shelf_no1_3'],
                                $postArray['shelf_no2_1'].$postArray['shelf_no2_2'].$postArray['shelf_no2_3'],
                                $postArray['shelf_no3_1'].$postArray['shelf_no3_2'].$postArray['shelf_no3_3']
                            )
                        )
                    );

                    // 新規登録
                    $message = $mst0211->addNewData($postArray);
                }
            }

            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
            $Log->trace("END mst0211 addAction");
        }

        /**
         * POS商品マスタ更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START mst0211 modAction" );

            $mst0211 = new Mst0211();

            $startFlag = true;
            // 登録用所属組織プルダウン
            $abbreviatedList        = $mst0211->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $prodlist               =$mst0211->prodlist($Datas);   
            // 組織数分実行
           // foreach($abbreviatedList as $data){
          //  if($data['organization_id'] != '' ){
            $postArray = array(
               // 'key_organizationID'        => parent::escStr( $_POST['keyOrganizationID'] ),
                'key_prod_cd'               => parent::escStr( $_POST['keyProdCode'] ),
                'key_change_dt'             => parent::escStr( $_POST['keyChangeDT'] ),
                'organization_id'           => parent::escStr( $_POST['organization_id'] ),
                'prod_cd'                   => parent::escStr( $_POST['prod_cd'] ),
                'change_dt'                 => parent::escStr( $_POST['change_dt'] ),
                'jan_cd'                    => parent::escStr( $_POST['jan_cd'] ),
                'itf_cd'                    => parent::escStr( $_POST['itf_cd'] ),
                'prod_nm'                   => parent::escStr( $_POST['prod_nm'] ),
                'prod_kn'                   => parent::escStr( $_POST['prod_kn'] ),
                'prod_kn_rk'                => parent::escStr( $_POST['prod_kn_rk'] ),
                'prod_capa_kn'              => parent::escStr( $_POST['prod_capa_kn'] ),
                'prod_capa_nm'              => parent::escStr( $_POST['prod_capa_nm'] ),
                'disp_prod_nm1'             => parent::escStr( $_POST['disp_prod_nm1'] ),
                'disp_prod_nm2'             => parent::escStr( $_POST['disp_prod_nm2'] ),
                'case_piece'                => parent::escStr( $_POST['case_piece'] ),
                'case_bowl'                 => parent::escStr( $_POST['case_bowl'] ),
                'bowl_piece'                => parent::escStr( $_POST['bowl_piece'] ),
                'sect_cd'                   => parent::escStr( $_POST['sect_cd'] ),
                'priv_class_cd'             => parent::escStr( $_POST['priv_class_cd'] ),
                'jicfs_class_cd'            => parent::escStr( $_POST['jicfs_class_cd'] ),
                'maker_cd'                  => parent::escStr( $_POST['maker_cd'] ),
                'head_supp_cd'              => parent::escStr( $_POST['head_supp_cd'] ),
                'shop_supp_cd'              => parent::escStr( $_POST['shop_supp_cd'] ),
                'serial_no'                 => parent::escStr( $_POST['serial_no'] ),
                'prod_note'                 => parent::escStr( $_POST['prod_note'] ),
                'priv_prod_cd'              => parent::escStr( $_POST['priv_prod_cd'] ),
                'alcohol_cd'                => parent::escStr( $_POST['alcohol_cd'] ),
                'alcohol_capa_amount'       => parent::escStr( $_POST['alcohol_capa_amount'] ),
                'order_lot'                 => parent::escStr( $_POST['order_lot'] ),
                'return_lot'                => parent::escStr( $_POST['return_lot'] ),
                'just_stock_amout'          => parent::escStr( $_POST['just_stock_amout'] ),
                'appo_prod_kbn'             => parent::escStr( $_POST['appo_prod_kbn'] ),
                'appo_medi_kbn'             => parent::escStr( $_POST['appo_medi_kbn'] ),
                'tax_type'                  => parent::escStr( $_POST['tax_type'] ),
                'order_stop_kbn'            => parent::escStr( $_POST['order_stop_kbn'] ),
                'noreturn_kbn'              => parent::escStr( $_POST['noreturn_kbn'] ),
                'point_kbn'                 => parent::escStr( $_POST['point_kbn'] ),
                'point_magni'               => parent::escStr( $_POST['point_magni'] ),
                'p_calc_prod_point'         => parent::escStr( $_POST['p_calc_prod_point'] ),
                
//                'dir_del_kbn'         => parent::escStr( $_POST['dir_del_kbn'] ),一時的に封印
//                'sale_kbn'           => parent::escStr( $_POST['sale_kbn'] ),一時的に封印
                
                
                'p_calc_prod_money'         => parent::escStr( $_POST['p_calc_prod_money'] ),
                'auto_order_kbn'            => parent::escStr( $_POST['auto_order_kbn'] ),
                'risk_type_kbn'             => parent::escStr( $_POST['risk_type_kbn'] ),
                'order_fin_kbn'             => parent::escStr( $_POST['order_fin_kbn'] ),
                'resale_kbn'                => parent::escStr( $_POST['resale_kbn'] ),
                'investiga_kbn'             => parent::escStr( $_POST['investiga_kbn'] ),
                'disc_not_kbn'              => parent::escStr( $_POST['disc_not_kbn'] ),
                'regi_duty_kbn'             => parent::escStr( $_POST['regi_duty_kbn'] ),
                'set_prod_kbn'              => parent::escStr( $_POST['set_prod_kbn'] ),
                'bundle_kbn'                => parent::escStr( $_POST['bundle_kbn'] ),
                'mixture_kbn'               => parent::escStr( $_POST['mixture_kbn'] ),
                'sale_period_kbn'           => parent::escStr( $_POST['sale_period_kbn'] ),
                'delete_kbn'                => parent::escStr( $_POST['delete_kbn'] ),
                'order_can_kbn'             => parent::escStr( $_POST['order_can_kbn'] ),
                'order_can_date'            => parent::escStr( $_POST['order_can_date'] ),
                'prod_t_cd1'                => parent::escStr( $_POST['prod_t_cd1'] ),
                'prod_t_cd2'                => parent::escStr( $_POST['prod_t_cd2'] ),
                'prod_t_cd3'                => parent::escStr( $_POST['prod_t_cd3'] ),
                'prod_t_cd4'                => parent::escStr( $_POST['prod_t_cd4'] ),
                'prod_k_cd1'                => parent::escStr( $_POST['prod_k_cd1'] ),
                'prod_k_cd2'                => parent::escStr( $_POST['prod_k_cd2'] ),
                'prod_k_cd3'                => parent::escStr( $_POST['prod_k_cd3'] ),
                'prod_k_cd4'                => parent::escStr( $_POST['prod_k_cd4'] ),
                'prod_k_cd5'                => parent::escStr( $_POST['prod_k_cd5'] ),
                'prod_k_cd6'                => parent::escStr( $_POST['prod_k_cd6'] ),
                'prod_k_cd7'                => parent::escStr( $_POST['prod_k_cd7'] ),
                'prod_k_cd8'                => parent::escStr( $_POST['prod_k_cd8'] ),
                'prod_k_cd9'                => parent::escStr( $_POST['prod_k_cd9'] ),
                'prod_k_cd10'               => parent::escStr( $_POST['prod_k_cd10'] ),
                'prod_res_cd1'              => parent::escStr( $_POST['prod_res_cd1'] ),
                'prod_res_cd2'              => parent::escStr( $_POST['prod_res_cd2'] ),
                'prod_res_cd3'              => parent::escStr( $_POST['prod_res_cd3'] ),
                'prod_res_cd4'              => parent::escStr( $_POST['prod_res_cd4'] ),
                'prod_res_cd5'              => parent::escStr( $_POST['prod_res_cd5'] ),
                'prod_res_cd6'              => parent::escStr( $_POST['prod_res_cd6'] ),
                'prod_res_cd7'              => parent::escStr( $_POST['prod_res_cd7'] ),
                'prod_res_cd8'              => parent::escStr( $_POST['prod_res_cd8'] ),
                'prod_res_cd9'              => parent::escStr( $_POST['prod_res_cd9'] ),
                'prod_res_cd10'             => parent::escStr( $_POST['prod_res_cd10'] ),
                'prod_comment'              => parent::escStr( $_POST['prod_comment'] ),
                'shelf_block1_1'            => parent::escStr( $_POST['shelf_block1_1'] ),
                'shelf_block1_2'            => parent::escStr( $_POST['shelf_block1_2'] ),
                'shelf_block1_3'            => parent::escStr( $_POST['shelf_block1_3'] ),
                'shelf_block2_1'            => parent::escStr( $_POST['shelf_block2_1'] ),
                'shelf_block2_2'            => parent::escStr( $_POST['shelf_block2_2'] ),
                'shelf_block2_3'            => parent::escStr( $_POST['shelf_block2_3'] ),
                'fixeprice'                 => parent::escStr( $_POST['fixeprice'] ),
                'saleprice'                 => parent::escStr( $_POST['saleprice'] ),
                'saleprice_ex'              => parent::escStr( $_POST['saleprice_ex'] ),
                'base_saleprice'            => parent::escStr( $_POST['base_saleprice'] ),
                'base_saleprice_ex'         => parent::escStr( $_POST['base_saleprice_ex'] ),
                'cust_saleprice'            => parent::escStr( $_POST['cust_saleprice'] ),
                'head_costprice'            => parent::escStr( $_POST['head_costprice'] ),
                'shop_costprice'            => parent::escStr( $_POST['shop_costprice'] ),
                'contract_price'            => parent::escStr( $_POST['contract_price'] ),
                'empl_saleprice'            => parent::escStr( $_POST['empl_saleprice'] ),
                'spcl_saleprice1'           => parent::escStr( $_POST['spcl_saleprice1'] ),
                'spcl_saleprice2'           => parent::escStr( $_POST['spcl_saleprice2'] ),
                'saleprice_limit'           => parent::escStr( $_POST['saleprice_limit'] ),
                'saleprice_ex_limit'        => parent::escStr( $_POST['saleprice_ex_limit'] ),
                'time_saleprice'            => parent::escStr( $_POST['time_saleprice'] ),
                'time_saleamount'           => parent::escStr( $_POST['time_saleamount'] ),
                'smp1_str_dt'               => parent::escStr( $_POST['smp1_str_dt'] ),
                'smp1_end_dt'               => parent::escStr( $_POST['smp1_end_dt'] ),
                'smp1_saleprice'            => parent::escStr( $_POST['smp1_saleprice'] ),
                'smp1_cust_saleprice'       => parent::escStr( $_POST['smp1_cust_saleprice'] ),
                'smp1_costprice'            => parent::escStr( $_POST['smp1_costprice'] ),
                'smp1_point_kbn'            => parent::escStr( $_POST['smp1_point_kbn'] ),
                'smp2_str_dt'               => parent::escStr( $_POST['smp2_str_dt'] ),
                'smp2_end_dt'               => parent::escStr( $_POST['smp2_end_dt'] ),
                'smp2_saleprice'            => parent::escStr( $_POST['smp2_saleprice'] ),
                'smp2_cust_saleprice'       => parent::escStr( $_POST['smp2_cust_saleprice'] ),
                'smp2_costprice'            => parent::escStr( $_POST['smp2_costprice'] ),
                'smp2_point_kbn'            => parent::escStr( $_POST['smp2_point_kbn'] ),
                'sales_talk_kbn'            => parent::escStr( $_POST['sales_talk_kbn'] ),
                'sales_talk'                => parent::escStr( $_POST['sales_talk'] ),
                'switch_otc_kbn'            => parent::escStr( $_POST['switch_otc_kbn'] ),
                'prod_tax'                  => parent::escStr( $_POST['prod_tax'] ),
                'bb_date_kbn'               => parent::escStr( $_POST['bb_date_kbn'] ),
                'cartone'                   => parent::escStr( $_POST['cartone'] ),
                'bb_days'                   => parent::escStr( $_POST['bb_days'] ),
                'order_prod_cd1'            => parent::escStr( $_POST['order_prod_cd1'] ),
                'order_prod_cd2'            => parent::escStr( $_POST['order_prod_cd2'] ),
                'order_prod_cd3'            => parent::escStr( $_POST['order_prod_cd3'] ),
                'case_costprice'            => parent::escStr( $_POST['case_costprice'] ),
                'total_stock_amout'         => parent::escStr( $_POST['total_stock_amout'] ),
                'fill_amount'               => parent::escStr( $_POST['fill_amount'] ),
                'total_stock_amout_load'    => parent::escStr( $_POST['total_stock_amout_load'] ),
                'fill_amount_load'          => parent::escStr( $_POST['fill_amount_load'] ),
                'case_stock_amout_load'     => parent::escStr( $_POST['case_stock_amout_load'] ),
                'bowl_stock_amout_load'     => parent::escStr( $_POST['bowl_stock_amout_load'] ),
                'piece_stock_amout_load'    => parent::escStr( $_POST['piece_stock_amout_load'] ),
                'endmon_amount_load'        => parent::escStr( $_POST['endmon_amount_load'] ),
                'nmondiff_amount_load'      => parent::escStr( $_POST['nmondiff_amount_load'] ),
                'emondiff_amount_load'      => parent::escStr( $_POST['emondiff_amount_load'] ),
                'shelf_no1_1'               => parent::escStr( $_POST['shelf_no1_1'] ),
                'shelf_no1_2'               => parent::escStr( $_POST['shelf_no1_2'] ),
                'shelf_no1_3'               => parent::escStr( $_POST['shelf_no1_3'] ),
                'shelf_no2_1'               => parent::escStr( $_POST['shelf_no2_1'] ),
                'shelf_no2_2'               => parent::escStr( $_POST['shelf_no2_2'] ),
                'shelf_no2_3'               => parent::escStr( $_POST['shelf_no2_3'] ),
                'shelf_no3_1'               => parent::escStr( $_POST['shelf_no3_1'] ),
                'shelf_no3_2'               => parent::escStr( $_POST['shelf_no3_2'] ),
                'shelf_no3_3'               => parent::escStr( $_POST['shelf_no3_3'] ),
                'shop_cd'                   => parent::escStr( $_POST['shop_cd'] ),
            );

            // 変更実施日
            $postArray['change_dt'] = str_replace('/', '', $postArray['change_dt']);

            //簡易特売期間
            $postArray['smp1_str_dt'] = str_replace('/', '', $postArray['smp1_str_dt']);
            $postArray['smp1_end_dt'] = str_replace('/', '', $postArray['smp1_end_dt']);
            $postArray['smp2_str_dt'] = str_replace('/', '', $postArray['smp2_str_dt']);
            $postArray['smp2_end_dt'] = str_replace('/', '', $postArray['smp2_end_dt']);

            // ブロック結合（ゼロ埋めとか一箇所入力したら残りの未入力もゼロで埋めるとかはJavaScriptで処理する方針で）
            $postArray = array_merge($postArray, array(
                    'shelf_block1' => $postArray['shelf_block1_1'].$postArray['shelf_block1_2'].$postArray['shelf_block1_3'],
                    'shelf_block2' => $postArray['shelf_block2_1'].$postArray['shelf_block2_2'].$postArray['shelf_block2_3']
                )
            );
            
            // 棚番結合（ゼロ埋めとか一箇所入力したら残りの未入力もゼロで埋めるとかはJavaScriptで処理する方針で）
            $postArray = array_merge($postArray, array(
                    'shelf_no' => array(
                        $postArray['shelf_no1_1'].$postArray['shelf_no1_2'].$postArray['shelf_no1_3'],
                        $postArray['shelf_no2_1'].$postArray['shelf_no2_2'].$postArray['shelf_no2_3'],
                        $postArray['shelf_no3_1'].$postArray['shelf_no3_2'].$postArray['shelf_no3_3']
                    )
                )
            );

            // 更新
            $message = $mst0211->updateData($postArray);
              //  }
                
           // }
            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END mst0211 modAction");
        }

        /**
         * POS商品マスタ削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START mst0211 delAction" );

            $mst0211 = new Mst0211();

            $startFlag = true;

            $postArray = array(
                'key_organizationID'        => parent::escStr( $_POST['keyOrganizationID'] ),
                'key_prod_cd'               => parent::escStr( $_POST['keyProdCode'] ),
                'key_change_dt'             => parent::escStr( $_POST['keyChangeDT'] ),
            );

            // 記事を削除
            $message = $mst0211->delData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 削除成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END mst0211 delAction");
        }

        /**
         * POS商品マスタ一覧検索
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START mst0211 initialDisplay");

            // グループマスタモデルインスタンス化
            $mst0211 = new Mst0211();
            $mst0211List = [];
            $json_mst0201PulldownList = "";
            $selectOrgSize = 200;

            // 一覧表用検索項目
            $searchArray = array(
                'organizationID'            => parent::escStr( $_POST['searchOrganizationID'] ),
                'prod_cd'                   => parent::escStr( $_POST['searchProdCode'] ),
                'prod_nm'                   => parent::escStr( $_POST['searchProdName'] ),
                'prod_kn'                   => parent::escStr( $_POST['searchProdKana'] ),
                'sect_cd'                   => parent::escStr( $_POST['searchsectcd'] ),

                'sort'                      => parent::escStr( $_POST['sortConditions'] ),
                 
            );

            if(!$startFlag){
                // POS商品マスタ一覧データ取得
                $mst0211RecordCnt = $mst0211->getListDataCnt($searchArray);

                // 表示レコード数
                $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            }

            // 指定ページ
            if($startFlag){
                // 初期表示の場合
                $pageNo = $this->getDuringTransitionPageNo($mst0211RecordCnt, $pagedRecordCnt);
            }else{
                // その他の場合
                $pageNo = $_SESSION["PAGE_NO"];
            }

            // シーケンスIDName
            $idName = "prod_cd";

            // 一覧表用検索項目
            $searchArray = array(
                'organizationID'            => parent::escStr( $_POST['searchOrganizationID'] ),
                'prod_cd'                   => parent::escStr( $_POST['searchProdCode'] ),
                'sect_cd'                   => parent::escStr( $_POST['searchsectcd'] ),
                'prod_nm'                   => parent::escStr( $_POST['searchProdName'] ),
                'prod_kn'                   => parent::escStr( $_POST['searchProdKana'] ),
                'sort'                      => parent::escStr( $_POST['sortConditions'] ),
                'limit'                     => $pagedRecordCnt,
                'offset'                    => ($pageNo - 1  )* $pagedRecordCnt,
            );
            
            if(!$startFlag){
                // POS商品マスタ一覧データ取得
                $mst0211List = $mst0211->getListData($searchArray);
                $mst0211List = $this->modBtnDelBtnDisabledCheck($mst0211List);
            }
            //print_r($mst0211List);
           
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($mst0211List) >= 15)
            {
                $isScrollBar = true;
            }

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            if($pagedRecordCnt != 0){
                $pagedCnt = ceil($mst0211RecordCnt /  $pagedRecordCnt);
            }else{$pagedCnt = 0;}
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソート時のマーク変更メソッド
            if($startFlag){
                // ソートマーク初期化
                $headerArray = $this->changeHeaderItemMark(0);
                // NO表示用
                $display_no = 0;
                // ソートがを選択されたかどうかのチェックフラグ（Mst0211TablePanel.htmlにて使用）
                $mst0211NoSortFlag = false;
            }else{
                // 対象項目へマークを付与
                $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
                // NO表示用
                $display_no = $this->getDisplayNo( $mst0211RecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
                // ソートがを選択されたかどうかのチェックフラグ（Mst0211TablePanel.htmlにて使用）
                $mst0211NoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            }

            if($startFlag){
                require_once './View/Mst0211Panel.html';
            }else{
                require_once './View/Mst0211TablePanel.html';
            }

            $Log->trace("END mst0211 initialDisplay");
        }

        /**
         * 商品予約マスタ入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function mst0211InputPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START mst0211InputPanelDisplay" );

            $mst0211 = new Mst0211();
            $mst5501DataList = [];
            $mst0211DataList = array();
            // 配列初期化
             $searchArray = array(
//            'prod_cd'                   => parent::escStr( $_POST['searchProdCode'] )
            );
            $mst0211->initMst0211DataList($mst0211DataList);
            $prodlist=$mst0211->prodlist($Datas);

            // 修正時フラグ（false）
            $CorrectionFlag = false;
            $allFlg = false;
            // 登録データ更新時
            if(!empty($_POST['mst0211pk']))
            {
                $mst0211pk = $_POST['mst0211pk'];
                $mst0211Param = explode( ':', $mst0211pk );
                $organization_id    = $mst0211Param[0];
                $prod_cd            = $mst0211Param[1];
                $change_dt          = $mst0211Param[2];

                // POS商品マスタデータを取得
                $mst0211DataList = $mst0211->getMst0211Data( $organization_id, $prod_cd, $change_dt );
                $gettenpolist = $mst0211->gettenpolist(  $prod_cd );

               
                // セキュリティに合わせて削除可能か設定←いずれ
                $del_disabled = true;
                // 更新フラグ
                //$CorrectionFlag = true;
                if ($mst0211DataList['change_dt'] !== ''){
                    $CorrectionFlag = true;
                }
            }else{
                $allFlg = true;
            }

//            $mst1201taxinfo['sect_tax_type']    = $mst0211DataList['sect_tax_type'];
//            $mst1201taxinfo['sect_tax']         = $mst0211DataList['sect_tax'];
//            // (部門)税区分ラベル
//            switch ($mst1201taxinfo['sect_tax_type']) {
//                case '1':
//                    $mst1201taxinfo['lbl_sect_tax'] = '外税（'.intval($mst1201taxinfo['sect_tax']).'％）';
//                    break;
//                case '2':
//                    $mst1201taxinfo['lbl_sect_tax'] = '内税（'.intval($mst1201taxinfo['sect_tax']).'％）';
//                    break;
//                case '9':
//                    $mst1201taxinfo['lbl_sect_tax'] = '非課税';
//                    break;
//                default:
//                    $mst1201taxinfo['lbl_sect_tax'] = '';
//                    break;
//            }

            // 原価
            $costprice = floatval(str_replace(',', '', $mst0211DataList['head_costprice']));
            // 売価(税抜)
            $saleprice = floatval(str_replace(',', '', $mst0211DataList['saleprice_ex']));
            // 粗利額：売価 - 原価            
            $profit = $saleprice - $costprice;
            // 粗利率：粗利÷売価
            if ($saleprice === 0.00) {
                $profit_per = 0;
            }
            else {
                $profit_per = $profit / $saleprice;
            }
            // 粗利額
            $mst0211DataList['profit'] = number_format($profit);
            // 粗利率
            $mst0211DataList['profit_per'] = number_format($profit_per, 2);
            
            // 簡易特売期間をスラッシュ分割
            if (isset($mst0211DataList['smp1_str_dt']) === true && $mst0211DataList['smp1_str_dt'] !== '') {
                $mst0211DataList['smp1_str_dt'] = substr($mst0211DataList['smp1_str_dt'],0,4).'/'.substr($mst0211DataList['smp1_str_dt'],4,2).'/'.substr($mst0211DataList['smp1_str_dt'],6,2);
            }
            if (isset($mst0211DataList['smp1_end_dt']) === true && $mst0211DataList['smp1_end_dt'] !== '') {
                $mst0211DataList['smp1_end_dt'] = substr($mst0211DataList['smp1_end_dt'],0,4).'/'.substr($mst0211DataList['smp1_end_dt'],4,2).'/'.substr($mst0211DataList['smp1_end_dt'],6,2);
            }
            if (isset($mst0211DataList['smp2_str_dt']) === true && $mst0211DataList['smp2_str_dt'] !== '') {
                $mst0211DataList['smp2_str_dt'] = substr($mst0211DataList['smp2_str_dt'],0,4).'/'.substr($mst0211DataList['smp2_str_dt'],4,2).'/'.substr($mst0211DataList['smp2_str_dt'],6,2);
            }
            if (isset($mst0211DataList['smp2_end_dt']) === true && $mst0211DataList['smp2_end_dt'] !== '') {
                $mst0211DataList['smp2_end_dt'] = substr($mst0211DataList['smp2_end_dt'],0,4).'/'.substr($mst0211DataList['smp2_end_dt'],4,2).'/'.substr($mst0211DataList['smp2_end_dt'],6,2);
            }
            
            // ブロック1を分割
            if (isset($mst0211DataList['shelf_block1']) === false || $mst0211DataList['shelf_block1'] === '') {
                $array_shelf_block = array(
                        'shelf_block1_1' => '',
                        'shelf_block1_2' => '',
                        'shelf_block1_3' => ''
                    );
            }
            else {
                $strTmp = str_pad($mst0211DataList['shelf_block1'], 9, '0', STR_PAD_RIGHT);
                $array_shelf_block = array(
                        'shelf_block1_1' => substr($strTmp, 0, 3),
                        'shelf_block1_2' => substr($strTmp, 3, 3),
                        'shelf_block1_3' => substr($strTmp, 6, 3)
                    );
            }
            $mst0211DataList = array_merge($mst0211DataList, $array_shelf_block);
            
            // ブロック2を分割
            if (isset($mst0211DataList['shelf_block2']) === false || $mst0211DataList['shelf_block2'] === '') {
                $array_shelf_block = array(
                        'shelf_block2_1' => '',
                        'shelf_block2_2' => '',
                        'shelf_block2_3' => ''
                    );
            }
            else {
                $strTmp = str_pad($mst0211DataList['shelf_block2'], 9, '0', STR_PAD_RIGHT);
                $array_shelf_block = array(
                        'shelf_block2_1' => substr($strTmp, 0, 3),
                        'shelf_block2_2' => substr($strTmp, 3, 3),
                        'shelf_block2_3' => substr($strTmp, 6, 3)
                    );
            }
            $mst0211DataList = array_merge($mst0211DataList, $array_shelf_block);
            
            // 棚番1を分割
            if (isset($mst0211DataList['shelf_no'][0]) === false || $mst0211DataList['shelf_no'][0] === '') {
                $array_shelf_no = array(
                        'shelf_no1_1' => '',
                        'shelf_no1_2' => '',
                        'shelf_no1_3' => ''
                    );
            }
            else {
                $strTmp = str_pad($mst0211DataList['shelf_no'][0], 9, '0', STR_PAD_RIGHT);
                $array_shelf_no = array(
                        'shelf_no1_1' => substr($strTmp, 0, 3),
                        'shelf_no1_2' => substr($strTmp, 3, 3),
                        'shelf_no1_3' => substr($strTmp, 6, 3)
                    );
            }
            $mst0211DataList = array_merge($mst0211DataList, $array_shelf_no);

            // 棚番2を分割
            if (isset($mst0211DataList['shelf_no'][1]) === false || $mst0211DataList['shelf_no'][1] === '') {
                $array_shelf_no = array(
                        'shelf_no2_1' => '',
                        'shelf_no2_2' => '',
                        'shelf_no2_3' => ''
                    );
            }
            else {
                $strTmp = str_pad($mst0211DataList['shelf_no'][1], 9, '0', STR_PAD_RIGHT);
                $array_shelf_no = array(
                        'shelf_no2_1' => substr($strTmp, 0, 3),
                        'shelf_no2_2' => substr($strTmp, 3, 3),
                        'shelf_no2_3' => substr($strTmp, 6, 3)
                    );
            }
            $mst0211DataList = array_merge($mst0211DataList, $array_shelf_no);

            // 棚番3を分割
            if (isset($mst0211DataList['shelf_no'][2]) === false || $mst0211DataList['shelf_no'][2] === '') {
                $array_shelf_no = array(
                        'shelf_no3_1' => '',
                        'shelf_no3_2' => '',
                        'shelf_no3_3' => ''
                    );
            }
            else {
                $strTmp = str_pad($mst0211DataList['shelf_no'][2], 9, '0', STR_PAD_RIGHT);
                $array_shelf_no = array(
                        'shelf_no3_1' => substr($strTmp, 0, 3),
                        'shelf_no3_2' => substr($strTmp, 3, 3),
                        'shelf_no3_3' => substr($strTmp, 6, 3)
                    );
            }
            $mst0211DataList = array_merge($mst0211DataList, $array_shelf_no);

            // 登録用所属組織プルダウン
            $abbreviatedList        = $mst0211->setPulldown->getSearchOrganizationList( 'registration', true, true );
            // 登録用POS商品部門プルダウン
            $mst1201DataList        = $mst0211->setPulldown->getSearchMst1201List( $organization_id );
            // 登録用POS商品仕入先プルダウン
            $mst1101DataList        = $mst0211->setPulldown->getSearchMst1101List( $organization_id );
            // 登録用POS商品自社分類プルダウン
            $mst5501DataList        = $mst0211->setPulldown->getSearchMst5501List( $organization_id );
            // 登録用POS商品JICFS分類プルダウン
            $mst5401DataList        = $mst0211->setPulldown->getSearchMst5401List( $organization_id );
            // 登録用POS商品メーカープルダウン
            $mst1001DataList        = $mst0211->setPulldown->getSearchMst1001List( $organization_id );
            // 登録用POS商品区分プルダウン
            $mst0901DataList        = $mst0211->setPulldown->getSearchMst0901List( $organization_id );
            // POS商品分類プルダウン
            $mst0801DataList        = $mst0211->setPulldown->getSearchMst0801List( $organization_id );
            
            // 部門プルダウンをJSONに変換
            $json_mst1201DataList = json_encode($mst1201DataList, JSON_UNESCAPED_UNICODE);
            // 仕入先プルダウンをJSONに変換
            $json_mst1101DataList = json_encode($mst1101DataList, JSON_UNESCAPED_UNICODE);
            // 自社分類プルダウンをJSONに変換
            $json_mst5501DataList = json_encode($mst5501DataList, JSON_UNESCAPED_UNICODE);
            // JICFS分類プルダウンをJSONに変換
            $json_mst5401DataList = json_encode($mst5401DataList, JSON_UNESCAPED_UNICODE);
            // メーカープルダウンをJSONに変換
            $json_mst1001DataList = json_encode($mst1001DataList, JSON_UNESCAPED_UNICODE);
            // 商品区分プルダウンをJSONに変換
            $json_mst0901DataList = json_encode($mst0901DataList, JSON_UNESCAPED_UNICODE);
            // 商品分類プルダウンをJSONに変換
            $json_mst0801DataList = json_encode($mst0801DataList, JSON_UNESCAPED_UNICODE);

            // 賞味期限利用区分
            $selopt_bb_date_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '1',     'name'  =>  '利用する'),
                array('value'   => '2',     'name'  =>  '利用しない'),
                array('value'   => '9',     'name'  =>  '製造日利用')
            );
            
            // 指定商品区分(0～9※任意)
            $selopt_appo_prod_kbn = array();
            $aryTmp = array('value' => '', 'name' => '');
            array_push($selopt_appo_prod_kbn, $aryTmp);
            for ($i = 0; $i < 10; $i ++) {
                $strNum = strval($i);
                $aryTmp = array( 'value' => $strNum, 'name' => $strNum);
                array_push($selopt_appo_prod_kbn, $aryTmp);
            }
            // 指定医薬品区分(00～99※任意)
            $selopt_appo_medi_kbn = array();
            $aryTmp = array('value' => '', 'name' => '');
            array_push($selopt_appo_medi_kbn, $aryTmp);
            for ($i = 0; $i < 100; $i ++) {
                $strNum = sprintf('%02d', $i);
                $aryTmp = array( 'value' => $strNum, 'name' => $strNum);
                array_push($selopt_appo_medi_kbn, $aryTmp);
            }
            // 税種別区分(1:税込/2:税抜/9:非課税)
            $selopt_tax_type = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '1',     'name'  =>  '外税'),
                array('value'   => '2',     'name'  =>  '内税'),
                array('value'   => '9',     'name'  =>  '非課税')
            );
            
            // スイッチOTC薬控除(0:対象外/1:対象)
            $selopt_switch_otc_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '対象外'),
                array('value'   => '1',     'name'  =>  '対象')
            );
            
            // リスク分類(0:その他/1:第一類医薬品/2:指定第二類医薬品/3:第二類医薬品/4:第三類医薬品/5:要指導医薬品
            $selopt_risk_type_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  'その他'),
                array('value'   => '1',     'name'  =>  '第一類医薬品'),
                array('value'   => '2',     'name'  =>  '指定第二類医薬品'),
                array('value'   => '3',     'name'  =>  '第二類医薬品'),
                array('value'   => '4',     'name'  =>  '第三類医薬品'),
                array('value'   => '5',     'name'  =>  '要指導医薬品')
            );

            // 返品不可区分
            $selopt_noreturn_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '返品可能'),
                array('value'   => '1',     'name'  =>  '返品不可能商品')
            );
            
            // 値引不可区分
            $selopt_disc_not_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '可能'),
                array('value'   => '1',     'name'  =>  '不可')
            );
            
            // 得点区分
            $selopt_point_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '対象'),
                array('value'   => '1',     'name'  =>  '対象外')
            );
            
 //ADDSTR kanderu 20201020 一旦封印
            //直送区分
//            $direct_kbn = array(
//                 array('value'   => '',      'name'  =>  ''),
//                 array('value'   => '0',     'name'  =>  '0:非直送'),
//                 array('value'   => '1',     'name'  =>  '1:直送')
//             ); 
            //特売区分
//             $sale_kbn = array(
//                 array('value'   => '',      'name'  =>  ''),
//                 array('value'   => '0',     'name'  =>  '0:非直送'),
//                 array('value'   => '1',     'name'  =>  '1:直送')
 //            ); 
//ADDEND kanderu 20201020  
            
            // 発注停止区分
            $selopt_order_stop_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '発注可能'),
                array('value'   => '1',     'name'  =>  '発注停止品')
            );
            
            // 自動発注区分
            $selopt_auto_order_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '未設定'),
                array('value'   => '1',     'name'  =>  '発注点'),
                array('value'   => '2',     'name'  =>  '売上補充(切上げ)'),
                array('value'   => '3',     'name'  =>  '売上補充(切下げ)')
            );
            
            // 直送区分
            $selopt_resale_kbn = array(
                 array('value'   => '',      'name'  =>  ''),
                 array('value'   => '0',     'name'  =>  '非直送'),
                 array('value'   => '1',     'name'  =>  '直送')
            );
            
            // 発注済区分
            $selopt_order_fin_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '未発注'),
                array('value'   => '1',     'name'  =>  '発注済')
            );
            
            // 発注取消区分
            $selopt_order_can_kbn = array(
                array('value'   => '',      'name'  =>  ''),
                array('value'   => '0',     'name'  =>  '発注可能'),
                array('value'   => '1',     'name'  =>  '発注停止品')
            );
            
            $mst0010DataList = array();
            // POSシステムマスタ取得
            $mst0010DataList = $mst0211->getMst0010Data( $organization_id );
            // POSシステムマスタ：店舗コード
            $mst0211DataList['shop_cd'] = $mst0010DataList['shop_cd'];
            // POSシステムマスタ：消費税端数処理区分(0:切り捨て/1:四捨五入/2:切り上げ)
            $mst0211DataList['tax_frac_kbn'] = $mst0010DataList['tax_frac_kbn'];
            

            require_once './View/Mst0211InputPanel.html';
            
            $Log->trace("END mst0211InputPanelDisplay");
        }

        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     ソート番号により、ソートマークを設定する
         * @param    $sortNo ソート条件番号
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark( $sortNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START mst0211 changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'mst0211NoSortMark'         => '',
                    'prodOrganizationSortMark'  => '',
                    'prodCodeSortMark'          => '',
                    'prodNameSortMark'          => '',
                    'prodKanaSortMark'          => '',
                    'capaNameSortMark'          => '',
                    'changeDTSortMark'          => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "mst0211NoSortMark";
                $sortList[2]  = "mst0211NoSortMark";
                $sortList[3]  = "prodOrganizationSortMark";
                $sortList[4]  = "prodOrganizationSortMark";
                $sortList[5]  = "prodCodeSortMark";
                $sortList[6]  = "prodCodeSortMark";
                $sortList[7]  = "prodNameSortMark";
                $sortList[8]  = "prodNameSortMark";
                $sortList[9]  = "prodKanaSortMark";
                $sortList[10] = "prodKanaSortMark";
                $sortList[11] = "capaNameSortMark";
                $sortList[12] = "capaNameSortMark";
                $sortList[13] = "changeDTSortMark";
                $sortList[14] = "changeDTSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START mst0211 changeHeaderItemMark");

            return $headerArray;
        }
        
        /**
         * 登録用POS商品部門リスト情報の更新
         * @note     入力画面で組織プルダウンの値を変更した時にPOS商品部門情報を更新する
         * @return   POS商品部門情報
         */
        public function searchMst1201OptionAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchMst1201OptionAction");
            $Log->info( "MSG_INFO_1086_1" );
            
            $mst0211 = new Mst0211();

            $organization_id = parent::escStr( $_POST['organization_id'] );
            
            // 登録用POS商品部門プルダウン
            $mst1201DataList        = $mst0211->setPulldown->getSearchMst1201List( $organization_id );
            // JSONに変換
            $json_mst1201DataList = json_encode($mst1201DataList);

            require_once './View/Common/SearchMst1201Option.php';
            $Log->trace("END searchMst1201OptionAction");
        }
        
        /**
         * 登録用POS商品部門リスト情報の更新
         * @note     入力画面で組織プルダウンの値を変更した時にPOS商品部門情報を更新する
         * @return   POS商品部門情報
         */
        public function searchMst1101OptionAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchMst1101OptionAction");
            $Log->info( "MSG_INFO_1086_1" );
            
            $mst0211 = new Mst0211();

            $organization_id = parent::escStr( $_POST['organization_id'] );
            
            // 登録用POS商品部門プルダウン
            $mst1101DataList        = $mst0211->setPulldown->getSearchMst1101List( $organization_id );

            require_once './View/Common/SearchMst1101Option.php';
            $Log->trace("END searchMst1101OptionAction");
        }
        
        /**
         * 登録用POS商品区分リスト情報の更新
         * @note     入力画面で組織プルダウンの値を変更した時にPOS商品区分情報を更新する
         * @return   POS商品区分情報
         */
        public function searchMstProdKbnOptionAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchMstProdKbnOptionAction");
            $Log->info( "MSG_INFO_1086_1" );
            
            $mst0211 = new Mst0211();

            $organization_id    = parent::escStr( $_POST['organization_id'] );
            
            // 登録用POS商品自社分類プルダウン
            $mst5501DataList        = $mst0211->setPulldown->getSearchMst5501List( $organization_id );
            // 登録用POS商品JICFS分類プルダウン
            $mst5401DataList        = $mst0211->setPulldown->getSearchMst5401List( $organization_id );
            // 登録用POS商品メーカープルダウン
            $mst1001DataList        = $mst0211->setPulldown->getSearchMst1001List( $organization_id );
            // 登録用POS商品区分プルダウン
            $mst0901DataList        = $mst0211->setPulldown->getSearchMst0901List( $organization_id );

            require_once './View/Common/SearchMstProdKbnOption.php';
            $Log->trace("END searchMstProdKbnOptionAction");
        }
        
        /**
         * 登録用POS商品分類リスト情報の更新
         * @note     入力画面で組織プルダウンの値を変更した時にPOS商品大分類情報を更新する
         * @return   POS商品分類情報
         */
        public function searchMst0801OptionAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchMst0801OptionAction");
            $Log->info( "MSG_INFO_1086_1" );
            
            $mst0211 = new Mst0211();

            $organization_id    = parent::escStr( $_POST['organization_id'] );
            
            // 登録用POS商品分類プルダウン
            $mst0801DataList      = $mst0211->setPulldown->getSearchMst0801List( $organization_id );

            require_once './View/Common/SearchMst0801Option.php';
            $Log->trace("END searchMst0801OptionAction");
        }
    }
?>
