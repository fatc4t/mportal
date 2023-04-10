<?php
    /**
     * @file      締日更新処理 [M]
     * @author    バッタライ
     * @date      2020/03/19
     * @version   1.00
     * @note      締日更新処理の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetCustomerClosingdateupdate.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetCustomerClosingdateupdateController extends BaseController
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
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
         * 表示項目設定一覧画面初期表示
         * @return    なし
         */
        public function initialAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay("initial");

            $Log->trace("END   showAction");
        }

        /**
         * 表示項目設定一覧画面検索表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay("show");

            $Log->trace("END   showAction");
        }

        /**
         * 帳票一覧画面
         * @note     POS種別画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            //print_r($_POST);
            $LedgerSheetCustomerClosingdateupdate = new LedgerSheetCustomerClosingdateupdate();
            $modal = new Modal();
            $cust_detail    = $modal->getcust_detail();
           /* $param          = [];*/

            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            
 
            require_once './View/LedgerSheetCustomerClosingdateupdate.html';
        }
        /*データ取得ファンクション*/
        public function getdataAction()
        {
            $payload = file_get_contents('php://input');
            $joken = json_decode($payload,true);               
            global $Log;  // グローバル変数宣言
            $Log->trace("START getdataAction");  
            $LedgerSheetCustomerClosingdateupdate = new LedgerSheetCustomerClosingdateupdate();
            $list = $LedgerSheetCustomerClosingdateupdate->get_data($joken); 
            $dlist = array();
            foreach($list as $POST){
                foreach($joken['CUST_CHECK_LIST'] as $cust_check){
                    if($POST['cust_cd'] === $cust_check['CUST_CHECK']){
                        $POST['check_flg'] = 'checked';
                        break;
                    }else{
                        $POST['check_flg'] = '';
                    }
                }
                array_push($dlist,$POST);
            }
            $data['dataget'] = $dlist;            
            print_r(json_encode($data));
            $Log->trace("END   getdataAction");
        }
        /*データアップデートファンクション*/
        public function Release_dataAction()               
        {  
            $payload = file_get_contents('php://input');
            $JOKEN    = json_decode($payload,true);              
            global $Log;  // グローバル変数宣言
            $Log->trace("START Release_dataAction");
            $LedgerSheetCustomerClosingdateupdate = new LedgerSheetCustomerClosingdateupdate(); 
            //ループ1　対象顧客数分ループする   
            foreach($JOKEN as $POST){
                /*組織マスタから組織IDを取得*/
                $ORG_ID = $LedgerSheetCustomerClosingdateupdate->SELECT_ORG_ID();
                //foreach($ORG_ID as $ORG_ID_LIST){
                 //   $orgArray = array(
                  //      'ORGANIZATION_ID'     => $ORG_ID_LIST['organization_id'],
                  //  );                    
                    //顧客売掛取引税別明細-削除
                    $LedgerSheetCustomerClosingdateupdate->Delete_JSK4150($POST);
                    //顧客売掛取引-削除
                    $LedgerSheetCustomerClosingdateupdate->Delete_JSK4160($POST);
                    //顧客売掛-削除
                    $LedgerSheetCustomerClosingdateupdate->Delete_JSK4170($POST);
                    //売上伝票-締処理日クリア
                    $LedgerSheetCustomerClosingdateupdate->UPD_TRN0101($POST);
                    //J受注伝票-締処理日クリア
                    $LedgerSheetCustomerClosingdateupdate->UPD_TRN7101($POST);
                    //顧客売掛-最新締処理取得し顧客実績更新
                    $LedgerSheetCustomerClosingdateupdate->UPD_JSK4130($POST);   
                //ループ終了
                //}                
            }
            print_r(json_encode($POST));
            $Log->trace("END   Release_dataAction");
        }
        /*データインサートファンクション*/
        public function save_dataAction($POST)
        { 
            $payload = file_get_contents('php://input');
            $JOKEN = json_decode($payload,true);
            global $Log;  // グローバル変数宣言
            $Log->trace("START save_dataAction");  
            //画面からのパラメータ取得
            $LedgerSheetCustomerClosingdateupdate = new LedgerSheetCustomerClosingdateupdate();
            /*顧客ループ*/            
            foreach($JOKEN as $POST){
                /*変数初期化*/
                $dmlBEF_BALANCE  = 0;
                $dmlSALE_TOTAL   = 0;
                $dmlSALE_TAX     = 0;
                $dmlRECE_TOTAL   = 0;
                $dmlRECE_DISC    = 0;
                $dmlNOW_BALANCE  = 0;
                $dmlSALE_CNT     = 0;
                $dmlRECE_CNT     = 0;
                /*組織マスタから組織IDを取得*/
                $ORG_ID = $LedgerSheetCustomerClosingdateupdate->SELECT_ORG_ID();
                foreach($ORG_ID as $ORG_ID_LIST){
                    /*変数初期化*/
                    $dmlBEF_BALANCE     = 0;
                    $dmlRECE_TOTAL      = 0;
                    $PURE_RECE_TOTAL    = 0;
                    $PURE_RECE_TOTAL_Sub = 0;
                    $dmlRECE_DISC       = 0;
                    $dmlNOW_BALANCE     = 0;
                    $dmlRECE_CNT        = 0;
                    $dmlbillno          = 0; 
                    $dmlSALE_TOTAL      = 0;
                    $dmlSALE_TAX        = 0;   
                    $dmlSALE_CNT        = 0;
                   // $dmlRECE_TOTAL_Sub   = 0;/*入金金額*/
                    $orgArray = array(
                        'ORGANIZATION_ID'     => $ORG_ID_LIST['organization_id'],
                    );
                    /*データ取得(前回残高)*/
                    $BEF_BALANCE_L = $LedgerSheetCustomerClosingdateupdate->SELECT_BEF_BALANCE($POST,$orgArray);
                    foreach($BEF_BALANCE_L as $data_BEF_BALANCE){                            
                        $dmlBEF_BALANCE      = $dmlBEF_BALANCE + $data_BEF_BALANCE['now_balance'];
                        //$BEF_WELLSET_DATE = $data_BEF_BALANCE['wellset_date'];
                    }
                    /*データ取得(売上)*/
                    $list = $LedgerSheetCustomerClosingdateupdate->SELECT_TRN0101($POST,$orgArray);  
                    
                    foreach($list as $dataTRN0101){                   
                        $searchArray = array(
                            'ORGANIZATION_ID'  => $dataTRN0101['organization_id'],//組織ID
                            'CUST_CD'          => $dataTRN0101['cust_cd'],//顧客コード
                            'HIDESEQ'          => $dataTRN0101['hideseq'],//売上番号
                            'PROC_DATE'        => $dataTRN0101['proc_date'],//営業日
                            'SALE_TOTAL'       => $dataTRN0101['sale_total'],//売上金額
                            'SALE_UTAX'        => $dataTRN0101['sale_utax'],//取引外税額
                            'SALE_ITAX'        => $dataTRN0101['sale_itax'],//取引内税額
                            'RETURN_HIDESEQ'   => $dataTRN0101['return_hideseq'],//返品元レコード番号                            
                        ); 
                        /*変数初期化*/ 
                        $dmlKAKE_TOTAL_Sub   = 0;/*売掛金額*/
                        $dmlRECE_TOTAL_Sub   = 0;/*入金金額*/
                        /*掛売上金額取得*/
                        $KAKE_TOTAL_Sub = $LedgerSheetCustomerClosingdateupdate->SELECT_CREDIT_MONEY($searchArray,$orgArray); 
                        foreach($KAKE_TOTAL_Sub as $data_KAKE_TOTAL_Sub){
                            
                            $dmlKAKE_TOTAL_Sub  =  $dmlKAKE_TOTAL_Sub + $data_KAKE_TOTAL_Sub['credit_money'];         
                        }                        
                        /*掛売上チェック*/
                        if($dmlKAKE_TOTAL_Sub <> 0){ 
                            /*取引時入金金額取得*/
                            $RECE_TOTAL_Sub = $LedgerSheetCustomerClosingdateupdate->SELECT_RECE_MONEY($searchArray,$orgArray); 
                            foreach($RECE_TOTAL_Sub as $data_RECE_TOTAL_Sub){
                                
                                $dmlRECE_TOTAL_Sub  =  $data_RECE_TOTAL_Sub['rece_total'] - $dmlKAKE_TOTAL_Sub;
                            } 
                            /*掛売金額加算*/
                            $dmlSALE_TOTAL = $dmlSALE_TOTAL + (int)$searchArray['SALE_TOTAL'] + (int)$searchArray['SALE_UTAX'];//純売上+外税
                            $dmlSALE_TAX   = $dmlSALE_TAX + (int)$searchArray['SALE_UTAX']  + (int)$searchArray['SALE_ITAX'];//外税+内税         
                            
                            //掛入金額加算
                            $PURE_RECE_TOTAL_Sub +=  (int)$dmlRECE_TOTAL_Sub;
                            
                            /*売上伝票数加算*/
                            $dmlSALE_CNT += 1;
                            /*掛売上金額取得*/
                            $list1 = $LedgerSheetCustomerClosingdateupdate->SELECT_TRNDATA($searchArray,$orgArray);

                            $HIDESEQ_1       = 0;/*レコード番号*/ 
                            /*jsk4160hideseqを取得する*/
                            $JSK_HIDESEQ = $LedgerSheetCustomerClosingdateupdate->SELECT_JSK_HIDESEQ($orgArray);
                            foreach($JSK_HIDESEQ as $data_JSK_HIDESEQ){
                                $HIDESEQ_1  = $HIDESEQ_1 + $data_JSK_HIDESEQ['hideseq'];
                            }                                

                            foreach($list1 as $data_TRNDATA){
                                $HIDESEQ_1++;
                                $searchArray1 = array(
                                    'INSUSER_CD'      => $data_TRNDATA['insuser_cd'],
                                    'UPDUSER_CD'      => $data_TRNDATA['upduser_cd'],
                                    'DISABLED'        => $data_TRNDATA['disabled'],
                                    'LAN_KBN'         => $data_TRNDATA['lan_kbn'],
                                    'CONNECT_KBN'     => $data_TRNDATA['connect_kbn'],
                                    'ORGANIZATION_ID' => $data_TRNDATA['organization_id'],
                                    'HIDESEQ_1'       => $HIDESEQ_1,
                                    'CUST_CD'         => $data_TRNDATA['cust_cd'],
                                    'REJI_NO'         => $data_TRNDATA['reji_no'],
                                    'PROC_DATE'       => $data_TRNDATA['proc_date'],
                                    'OPEN_CNT'        => $data_TRNDATA['open_cnt'],
                                    'TRNDATE'         => $data_TRNDATA['trndate'],
                                    'TRNTIME'         => $data_TRNDATA['trntime'],
                                    'ACCOUNT_NO'      => (int)$data_TRNDATA['account_no'],
                                    'ACCOUNT_KBN'     => '0',
                                    'SECT_CD'         => $data_TRNDATA['sect_cd'],
                                    'PROD_CD'         => $data_TRNDATA['prod_cd'],
                                    'TAX_TYPE'        => $data_TRNDATA['tax_type'],
                                    'AMOUNT'          => (int)$data_TRNDATA['amount'],
                                    'SALEPRICE'       => (int)$data_TRNDATA['saleprice'],
                                    'DISCTOTAL'       => (int)$data_TRNDATA['disctotal'],
                                    'PURE_TOTAL'      => (int)$data_TRNDATA['pure_total'],
                                    'DEN_PURE_TOTAL'  =>  0,
                                    'DEN_TAX'         =>  0,
                                    'RECE_KBN'        => '0',
                                    'RECE_TOTAL'      =>  0,
                                    'SWITCH_OTC_KBN'  => $data_TRNDATA['switch_otc_kbn'],
                                    'TAX_RATE'        => (int)$data_TRNDATA['tax_rate'],
                                    'DEL_CUST_CD'     => $data_TRNDATA['del_cust_cd'],
                                    'SALE_HIDESEQ'    => (int)$data_TRNDATA['sale_hideseq'],                                    
                                );
                                //JSK4160のループ
                                $LedgerSheetCustomerClosingdateupdate->INSERT_JSK4160($searchArray1,$POST);                                
                            }
                            /*取引時入金金額チェック*/
                            if($dmlRECE_TOTAL_Sub <> 0){
                                /*変数初期化*/ 
                                
                                $sub_list = $LedgerSheetCustomerClosingdateupdate->SELECT_TRN0101_data($searchArray,$orgArray);
                                foreach($sub_list as $data_TRN0101){

                                    $HIDESEQ_1++;

                                    $searchArray1_1 = array(
                                        'INSUSER_CD'      => $data_TRN0101['insuser_cd'],
                                        'INSDATETIME'     => $data_TRN0101['insdatetime'],
                                        'UPDUSER_CD'      => $data_TRN0101['upduser_cd'],
                                        'UPDDATETIME'     => $data_TRN0101['upddatetime'],
                                        'DISABLED'        => $data_TRN0101['disabled'],
                                        'LAN_KBN'         => $data_TRN0101['lan_kbn'],
                                        'CONNECT_KBN'     => $data_TRN0101['connect_kbn'],
                                        'ORGANIZATION_ID' => $data_TRN0101['organization_id'],
                                        'HIDESEQ_1'       => $HIDESEQ_1,
                                        'CUST_CD'         => $data_TRN0101['cust_cd'],
                                        'REJI_NO'         => $data_TRN0101['reji_no'],
                                        'PROC_DATE'       => $data_TRN0101['proc_date'],
                                        'OPEN_CNT'        => $data_TRN0101['open_cnt'],
                                        'TRNDATE'         => $data_TRN0101['trndate'],
                                        'TRNTIME'         => $data_TRN0101['trntime'],
                                        'ACCOUNT_NO'      => $data_TRN0101['account_no'],
                                        'ACCOUNT_KBN'     => '1',
                                        'SECT_CD'         => '',
                                        'PROD_CD'         => '',
                                        'TAX_TYPE'        => '0',
                                        'AMOUNT'          =>  0,
                                        'SALEPRICE'       =>  0,
                                        'DISCTOTAL'       =>  0,
                                        'PURE_TOTAL'      =>  0,
                                        'DEN_PURE_TOTAL'  =>  0,
                                        'DEN_TAX'         =>  0,
                                        'RECE_KBN'        => '0',
                                        'RECE_TOTAL'      =>  (int)$dmlRECE_TOTAL_Sub,
                                        'SWITCH_OTC_KBN'  => '0',
                                        'TAX_RATE'        => '0',
                                        'DEL_CUST_CD'     => $data_TRN0101['del_cust_cd'],
                                        'SALE_HIDESEQ'    => $data_TRN0101['sale_hideseq'],                                   
                                    );
                                    /*顧客売掛取引(取引時入金)*/
                                    $LedgerSheetCustomerClosingdateupdate->INSERT_JSK4160_1($searchArray1_1,$POST);                                    
                                }
                                
                            }    
                            /*顧客売掛取引(売上伝票)取得*/
                            $list_sub_1 = $LedgerSheetCustomerClosingdateupdate->SELECT_TRN0101_data_1($searchArray,$orgArray);
                            foreach($list_sub_1 as $data_TRN0101_1){

                                $HIDESEQ_1++;
                                    
                                $searchArray1_2 = array(
                                    'INSUSER_CD'      => $data_TRN0101_1['insuser_cd'],
                                    'INSDATETIME'     => $data_TRN0101_1['insdatetime'],
                                    'UPDUSER_CD'      => $data_TRN0101_1['upduser_cd'],
                                    'UPDDATETIME'     => $data_TRN0101_1['upddatetime'],
                                    'DISABLED'        => $data_TRN0101_1['disabled'],
                                    'LAN_KBN'         => $data_TRN0101_1['lan_kbn'],
                                    'CONNECT_KBN'     => $data_TRN0101_1['connect_kbn'],
                                    'ORGANIZATION_ID' => $data_TRN0101_1['organization_id'],
                                    'HIDESEQ_1'       => $HIDESEQ_1,
                                    'CUST_CD'         => $data_TRN0101_1['cust_cd'],
                                    'REJI_NO'         => $data_TRN0101_1['reji_no'],
                                    'PROC_DATE'       => $data_TRN0101_1['proc_date'],
                                    'OPEN_CNT'        => $data_TRN0101_1['open_cnt'],
                                    'TRNDATE'         => $data_TRN0101_1['trndate'],
                                    'TRNTIME'         => $data_TRN0101_1['trntime'],
                                    'ACCOUNT_NO'      => $data_TRN0101_1['account_no'],
                                    'ACCOUNT_KBN'     => '3',
                                    'SECT_CD'         => '',
                                    'PROD_CD'         => '',
                                    'TAX_TYPE'        => '0',
                                    'AMOUNT'          =>  0,
                                    'SALEPRICE'       =>  0,
                                    'DISCTOTAL'       =>  0,
                                    'PURE_TOTAL'      =>  0,
                                    'DEN_PURE_TOTAL'  =>  $data_TRN0101_1['den_pure_total'],
                                    'DEN_TAX'         =>  $data_TRN0101_1['den_tax'],
                                    'RECE_KBN'        => '0',
                                    'RECE_TOTAL'      =>  0,
                                    'SWITCH_OTC_KBN'  => '0',
                                    'TAX_RATE'        => '0',
                                    'DEL_CUST_CD'     => $data_TRN0101_1['del_cust_cd'],
                                    'SALE_HIDESEQ'    => $data_TRN0101_1['sale_hideseq'],                             
                                );
                                $LedgerSheetCustomerClosingdateupdate->INSERT_JSK4160_2($searchArray1_2,$POST);                                    
                            }
                            $HIDESEQ_2       = 0;/*レコード番号*/ 
                            /*jsk4170hideseqを取得する*/
                            $JSK_HIDESEQ1 = $LedgerSheetCustomerClosingdateupdate->SELECT_JSK4170_HIDESEQ($orgArray);
                            foreach($JSK_HIDESEQ1 as $data_JSK4170_HIDESEQ){
                                $HIDESEQ_2  = $HIDESEQ_2 + $data_JSK4170_HIDESEQ['hideseq'];
                            }                             
                            /*登録(売上伝票税別明細)*/
                            $list2 = $LedgerSheetCustomerClosingdateupdate->SELECT_TAXDATA($searchArray,$orgArray);
                            foreach($list2 as $data_TAXDATA){
                                
                                $HIDESEQ_2++;
                                
                                $searchArray2 = array(
                                    'INSUSER_CD'      => $data_TAXDATA['insuser_cd'],
                                    'UPDUSER_CD'      => $data_TAXDATA['upduser_cd'],
                                    'DISABLED'        => $data_TAXDATA['disabled'],
                                    'LAN_KBN'         => $data_TAXDATA['lan_kbn'],
                                    'CONNECT_KBN'     => $data_TAXDATA['connect_kbn'],
                                    'ORGANIZATION_ID' => $data_TAXDATA['organization_id'],
                                    'HIDESEQ_2'       => $HIDESEQ_2,
                                    'CUST_CD'         => $data_TAXDATA['cust_cd'],
                                    'LINE_NO'         => $data_TAXDATA['line_no'],
                                    'REJI_NO'         => $data_TAXDATA['reji_no'],
                                    'TRNDATE'         => $data_TAXDATA['trndate'],
                                    'TRNTIME'         => $data_TAXDATA['trntime'],
                                    'ACCOUNT_NO'      => (int)$data_TAXDATA['account_no'],
                                    'TAX_TYPE'        => (int)$data_TAXDATA['tax_type'],
                                    'TAX_RATE'        => $data_TAXDATA['tax_rate'],
                                    'PURE_TOTAL'      => (int)$data_TAXDATA['pure_total'],
                                    'TAX_TOTAL'       => (int)$data_TAXDATA['tax_total'],
                                );
                                //JSK4170のループ回りながらインサート
                                $LedgerSheetCustomerClosingdateupdate->INSERT_JSK4170($searchArray2,$POST);                                    
                            }
                            
                        }                     
                        /*データ取得(掛入金)*/
                        $RECE_TOTAL = $LedgerSheetCustomerClosingdateupdate->SELECT_TRN0301($searchArray,$orgArray);   
                       // $PURE_RACE_TOTAL     = 0;
                        foreach($RECE_TOTAL as $data_TRN0301){                        
                            /*掛入金額加算*/
                            $dmlRECE_TOTAL = $dmlRECE_TOTAL + $data_TRN0301['rece_total'];
                            $dmlRECE_DISC  = $dmlRECE_DISC  + $data_TRN0301['rece_disc'];
                        }
                        /*入金伝票数加算*/
                        $dmlRECE_CNT  += 1;
                    
                        $HIDESEQ_3       = 0;/*レコード番号*/ 
                        /*jsk4160hideseqを取得する*/
                        $JSK_HIDESEQ3 = $LedgerSheetCustomerClosingdateupdate->SELECT_JSK_HIDESEQ($orgArray);
                        foreach($JSK_HIDESEQ3 as $data_JSK_HIDESEQ3){
                            $HIDESEQ_3  = $HIDESEQ_3 + $data_JSK_HIDESEQ3['hideseq'];
                        }                    
                        $RACE_LIST = $LedgerSheetCustomerClosingdateupdate->SELECT_RACE_DATA($searchArray,$orgArray);
                        foreach($RACE_LIST as $RACE_DATA){

                            $HIDESEQ_3++;

                            $searchArray1_3 = array(
                                'INSUSER_CD'      => $RACE_DATA['insuser_cd'],
                                'INSDATETIME'     => $RACE_DATA['insdatetime'],
                                'UPDUSER_CD'      => $RACE_DATA['upduser_cd'],
                                'UPDDATETIME'     => $RACE_DATA['upddatetime'],
                                'DISABLED'        => $RACE_DATA['disabled'],
                                'LAN_KBN'         => $RACE_DATA['lan_kbn'],
                                'CONNECT_KBN'     => $RACE_DATA['connect_kbn'],
                                'ORGANIZATION_ID' => $RACE_DATA['organization_id'],
                                'HIDESEQ_3'       => $HIDESEQ_3,
                                'CUST_CD'         => $RACE_DATA['cust_cd'],
                                'REJI_NO'         => $RACE_DATA['reji_no'],
                                'PROC_DATE'       => $RACE_DATA['proc_date'],
                                'OPEN_CNT'        => $RACE_DATA['open_cnt'],
                                'TRNDATE'         => $RACE_DATA['trndate'],
                                'TRNTIME'         => $RACE_DATA['trntime'],
                                'ACCOUNT_NO'      => $RACE_DATA['account_no'],
                                'ACCOUNT_KBN'     => '2',
                                'SECT_CD'         => '',
                                'PROD_CD'         => '',
                                'TAX_TYPE'        => '0',
                                'AMOUNT'          =>  0,
                                'SALEPRICE'       =>  0,
                                'DISCTOTAL'       =>  0,
                                'PURE_TOTAL'      =>  0,
                                'DEN_PURE_TOTAL'  =>  0,
                                'DEN_TAX'         =>  0,
                                'RECE_KBN'        => $RACE_DATA['rece_kbn'],
                                'RECE_TOTAL'      => (int)$RACE_DATA['rece_total'],
                                'SWITCH_OTC_KBN'  => '0',
                                'TAX_RATE'        => '0',
                                'DEL_CUST_CD'     => $RACE_DATA['del_cust_cd'],
                                'SALE_HIDESEQ'    => $RACE_DATA['sale_hideseq'],                           
                            );
                            /*顧客売掛取引(掛入金)*/
                            $LedgerSheetCustomerClosingdateupdate->INSERT_JSK4160_3($searchArray1_3,$POST);                                
                        }
                                   
                    }      
                    //$dmlNOW_BALANCE = (int)$dmlBEF_BALANCE + (int)$dmlSALE_TOTAL - (int)$dmlRECE_TOTAL - (int)$dmlRECE_DISC;/*売掛残高計算(前回繰越額 + 売上金額(外・内税込) - 掛入金額 - 取引時入金金額)*/
                    $PURE_RECE_TOTAL = (int)$dmlRECE_TOTAL + (int)$PURE_RECE_TOTAL_Sub;
                    $dmlNOW_BALANCE  = (int)$dmlBEF_BALANCE + (int)$dmlSALE_TOTAL - (int)$PURE_RECE_TOTAL - (int)$dmlRECE_DISC;/*売掛残高計算(前回繰越額 + 売上金額(外・内税込) - 掛入金額 - 取引時入金金額)*/
                    
                    $SELECT_BILLNO = $LedgerSheetCustomerClosingdateupdate->SELECT_BILLNO($orgArray);
                    foreach($SELECT_BILLNO as $bill_no){
                        $dmlbillno = $bill_no['bill_no'];
                    }
                    $dmlbillno++;
                    $searchArray3 = array(
                        'ORGANIGATION_ID'     => $ORG_ID_LIST['organization_id'],/*組織ID*/
                        'BILL_NO'             => (int)$dmlbillno,/*請求番号*/
                        'BEF_BALANCE'         => (int)$dmlBEF_BALANCE,/*前回残高*/
                        'SALE_TOTAL'          => (int)$dmlSALE_TOTAL,/*買上金額*/
                        'SALE_TAX'            => (int)$dmlSALE_TAX,/*買上消費税*/
                        //'RECE_TOTAL'          => (int)$dmlRECE_TOTAL,/*入金金額*/
                        'RECE_TOTAL'          => (int)$PURE_RECE_TOTAL,/*入金金額*/
                        'RECE_DISC'           => (int)$dmlRECE_DISC,/*入金値引*/
                        'NOW_BALANCE'         => (int)$dmlNOW_BALANCE,/*今回請求額*/
                        'SALE_CNT'            => (int)$dmlSALE_CNT,/*売上伝票数*/
                        'RECE_CNT'            => (int)$dmlRECE_CNT,/*入金伝票数*/   
                    ); 
                    /*顧客売掛*/
                    $LedgerSheetCustomerClosingdateupdate->INSERT_JSK4150($searchArray3,$POST); 
                    //売上伝票の締日を更新
                    $LedgerSheetCustomerClosingdateupdate->UPDATE_TRN0101($POST,$orgArray);
                    /*J発注伝票の締日を更新*/
                    $LedgerSheetCustomerClosingdateupdate->UPDATE_TRN7101($POST,$searchArray);                                        
                    /*顧客実績で顧客コードを取得*/
                    $check_cust_cd = $LedgerSheetCustomerClosingdateupdate->SELECT_JSK4130($POST);
                    foreach($check_cust_cd as $checkdata){
                       $checkArray = array(
                            'CUST_CD'  => $checkdata['cust_cd'],
                        );  
                    }  
                    /*顧客実績存在チェック*/ 
                    if($checkArray['cust_cd'] === ''){
                    /*データなし-空レコード作成*/
                    $LedgerSheetCustomerClosingdateupdate->INSERT_JSK4130($POST,$searchArray);
                    } else {
                    /*顧客データある場合更新日を更新する*/
                    $LedgerSheetCustomerClosingdateupdate->UPDATE_JSK4130($POST);
                    }                   
                }        
            }
            print_r(json_encode($POST));   
            $Log->trace("END  save_dataAction");
        }
    }
?>
