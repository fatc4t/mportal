<?php
    /**
     * @file      売上対比実績
     * @author    millionet kanderu
     * @date      2020/07/30
     * @version   1.00
     * @note      帳票 - 売上対比実績を表示
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    //require './Model/LedgerSheetItem.php';
    require './Model/LedgerSheetSalesResultsCompare.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetSalesResultsCompareController extends BaseController
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
         * 表示項目設定一覧画面検索
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay("show");

            $Log->trace("END   showAction");
        }
        ////ADDSTR 2020/06 kanderu*************************************************************************
        public function datasearchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");            
            $SLedgerSheetSalesResultsCompare = new LedgerSheetSalesResultsCompare();

            $searchparam = json_decode($_POST['param'],true);
           //print_r($searchparam);
            
            $result = $SLedgerSheetSalesResultsCompare->get_data_1($searchparam);
            
            
            print_r(json_encode($result));
            //print_r($_POST);            
        }  
       
        public function searchAction()       {
            global $Log; // グローバル変数宣言/            $Log->trace( "START searchAction" );
           // $Log->info( "MSG_INFO_1041" );
               
            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = parent::escStr( $_POST['displayPageNo'] );
            }
                
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = parent::escStr( $_POST['displayRecordCnt'] );
            }
                
            $this->initialList(false);
                
            $Log->trace("END   searchAction");
        }       
//////ADDEND 2020/06 kanderu*************************************************************************

        /**
         * 帳票一覧画面
         * @note     商品別データ取得
         * @return   無
         */
        private function initialDisplay($mode)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            // Model生成
            $LedgerSheetSalesResultsCompare = new LedgerSheetSalesResultsCompare();   
            // 日付リストを取得
            //$startDateM = parent::escStr( $_POST['start_dateM'] );
            $startDateR = parent::escStr( $_POST['start_dateR'] );
            $endDateR = parent::escStr( $_POST['end_dateR'] );
            $startDateT = parent::escStr( $_POST['start_dateT'] );
            $endDateT = parent::escStr( $_POST['end_dateT'] );
            $modal = new Modal();
            // 店舗リスト取得
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();              
            // 部門リスト取得
            $sect_detail    = [];
            $sect_detail    = $modal->getsect_detail();
                        // 画面フォームに日付を返す
            if(!isset($startDateR) OR $startDateR == ""){
                //現在日付の１日を設定
                $startDateR =date('Y/m/d', strtotime('first day of ' . $month));
            }
            if(!isset($endDateR) OR $endDateR == ""){
                //現在日付の設定
                $endDateR =date('Y/m/d', strtotime('Today' . $month));
            }
            
            // パラメータ変数定義
            $param          = [];    
            // POSTパラメーター
            if($_POST){
                $param = $_POST;
            }    
            // 各種変数定義
            $param["org_id"]    = "";
            $param["sect_cd"]   = "";
            // 店舗リスト
            $org_r = isset($_POST['org_r']) ? parent::escStr($_POST['org_r']) : '';
            if($org_r === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }
            else{
                $organizationId = $org_r;
            }
            
            // 店舗選択
            if($_POST['tenpo']){
                $organizationId = 'compile';
            }
            //部門
            $sect_cd = 'false';
            if($_POST['sect_r'] === ""){
                if($_POST['sect_select'] && $_POST['sect_select'] === 'empty'){
                    $sect_cd = $_POST['sect_cd'];
                }else{
                    $sect_cd = "'".$_POST['sect_select']."'";
                }
            }
            //ADDSTR 2020/06 kanderu*************************************************************************
            // アクセス権限一覧レコード数
//            $sectionRecordCnt = count($accessAllList);

            // 表示レコード数
//            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
             $pageNo = 0;
             $pageNo = $_POST['pageNo'];
             if($pageNo === 0 || $pageNo === ''){
                $pageNo =  $this->getDuringTransitionPageNo($pagedRecordCnt);
             }
             
             $pagedRecordCnt = 0;
            
             if( $pagedRecordCnt = $_POST['limit_r']){
                 $btn_next=$_POST['limit_r'];   
             }
            
            if($pagedRecordCnt === 0 || $pagedRecordCnt === ''){
              //  $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
                 $pagedRecordCnt = 100;

            }
             // 検索条件設定 
            $searchArray = array(
                'organization_id'   => $organizationId,
                'start_date_r'      => $startDateR,
                'end_date_r'        => $endDateR,
                'start_date_t'      => $startDateT,
                'end_date_t'        => $endDateT,
                'sect_cd'           => $sect_cd,
                'sort'              => $sort,);

            //MAXページを計算する
            $pageMaxNo=0;
             
            $pageMaxNo = $LedgerSheetSalesResultsCompare->pageMaxNo();
            
//            $pageMaxNo=$pageMaxNo[0]["rownos"] / $pagedRecordCnt;
            if($pageMaxNo[0]["rownos"] > 0){
                $round_up = $pageMaxNo[0]["rownos"] / $pagedRecordCnt;
                $pageMaxNo = ceil(round($round_up,2,PHP_ROUND_HALF_UP));
                //$pageMaxNo+1;
            }
      
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($List) >= 11)
            {
                $isScrollBar = true;
            }
            $isScrollBar = false;
//ADDSEND 2020/06 kanderu*************************************************************************
            
            
            

            // 検索条件設定 
            $searchArray = array(
                'organization_id'   => $organizationId,
                'start_date_r'      => $startDateR,
                'end_date_r'        => $endDateR,
                'start_date_t'      => $startDateT,
                'end_date_t'        => $endDateT,
                'sect_cd'           => $sect_cd,
                'sort'              => $sort,
                ////ADDSTR 2020/06 kanderu*************************************************************************
                'limit'                     => $pagedRecordCnt,
               'offset'                    => ($pageNo - 1  )* $pagedRecordCnt,
////ADDEND 2020/06 kanderu*************************************************************************
            );

            // 初期表示かどうかの確認
            if($mode != "initial")
            {
                //データの取得
              $list = $LedgerSheetSalesResultsCompare -> getListData($searchArray);
                // 店舗合計
                $sum_res1               = 0; 
                $sum_cnt                = 0;
                $sum_cnt2               = 0;
                $sum_pure_total        = 0; 
                $sum_utax              = 0; 
                $sum_pure_total_i      = 0; 
                $sum_tax_total_08      = 0;
                $sum_tax_total_10      = 0;
                //$sum_res2              = 0; 
                $sum_total_profit_i    = 0; 
                $sum_total_amount      = 0; 
                //$sum_total_cnt         = 0; 
                $sum_res3              = 0; 
                $sum_res4              = 0; 
                $sum_res5              = 0; 	
                $sum_res6              = 0; 
                $sum_res6per           = 0;
                $sum_res5per           = 0;
                $sum_res4per           = 0;
                $sum_res3per           = 0;
                $sum_cntper            = 0;
                $sum_utaxper           = 0;
                $sum_pure_totalper     = 0;
                $sum_total_cntper       = 0;
                $sum_res1per1           = 0;
                $sum_pure_total_iper    = 0;
                $sum_tax_total_08per    = 0;
                $sum_tax_total_10per     = 0;  
                $sum_total_profit_iper   = 0;          
                $sum_total_amountper      = 0;
                      

                // 総合計
               $sumall_pure_total        = 0; 
               $sumall_utax              = 0; 
               $sumall_cnt               = 0;
                $sumall_cnt2             = 0;
               //$sumall_res1              = 0; 
               $sumall_pure_total_i      = 0; 
               $sumall_tax_total_08      = 0;
               $sumall_tax_total_10      = 0; 
               $sumall_total_profit_i    = 0; 
               $sumall_total_amount      = 0; 
               //$sumall_total_cnt         = 0; 
               $sumall_res3              = 0; 
               $sumall_res4              = 0; 
               $sumall_res5              = 0; 	
               $sumall_res6              = 0; 
               
                // 店舗合計
               $sum_cnt                = 0;
                $sum_cnt2               = 0;
                $sum_pure_total2        = 0; 
                $sum_utax2              = 0; 
               // $sum_res12              = 0; 
                $sum_pure_total_i2      = 0; 
                $sum_tax_total_082      = 0;
                $sum_tax_total_102      = 0; 
                $sum_total_profit_i2    = 0; 
                $sum_total_amount2      = 0; 
                //$sum_total_cnt2         = 0; 
                $sum_res32              = 0; 
                $sum_res42              = 0; 
                $sum_res52              = 0; 	
                $sum_res62              = 0;       
                // 総合計
                $sumall_utax2              = 0; 
               $sumall_pure_total2      = 0; 
              // $sumall_res12              = 0; 
               $sumall_pure_total_i2      = 0; 
               $sumall_tax_total_082      = 0;
               $sumall_tax_total_102      = 0;
              // $sumall_res22              = 0; 
               $sumall_total_profit_i2    = 0; 
               $sumall_total_amount2      = 0; 
              // $sumall_total_cnt2         = 0; 
               $sumall_res32              = 0; 
               $sumall_res42              = 0; 
               $sumall_res52              = 0; 	
               $sumall_res62              = 0; 

                $ledgerSheetDetailList = array();
                
                
                // 首都データで合計計算 +α
                for ($intL = 0; $intL < count($list); $intL ++){
                    
                    if (isset($list[$intL]['organization_id']) === true){
                        $organization_id = intval($list[$intL]['organization_id']);
                    }
                    else{
                        $organization_id = '';
                    }


                    // 店舗が変わったら実施
        
                        
                        // 合計行の追加処理
                        if ($intL > 0){
                            // 店舗合計行
                            $aryAddRow = array();
                            $aryAddRow['org_nm']          = '合計';
                            $aryAddRow['utax']            = ($sum_utax);
                            $aryAddRow['pure_total']      = ($sum_pure_total);
                            $aryAddRow['cnt']             = ($sum_cnt);
                           //$aryAddRow['res1']            = ($sum_res1);
                            $aryAddRow['pure_total_i']    = $sum_pure_total_i; 
                            $aryAddRow['tax_total_08']    = $sum_tax_total_08; 
                            $aryAddRow['tax_total_10']    = $sum_tax_total_10;     
                            $aryAddRow['total_profit_i']  = $sum_total_profit_i;           
                            $aryAddRow['total_amount']    = $sum_total_amount;               
                           // $aryAddRow['total_cnt']       = $sum_total_cnt;      
                            $aryAddRow['res3']            = $sum_res3;             
                            $aryAddRow['res4']            = $sum_res4;           
                            $aryAddRow['res5']            = $sum_res5;        
                            $aryAddRow['res6']            = $sum_res6; 
                           // 合計	対象日
                            $aryAddRow['utax2']            = ($sum_utax2);
                            $aryAddRow['pure_total2']      = ($sum_pure_total2);
                            $aryAddRow['cnt2']            = ($sum_cnt2);
                           // $aryAddRow['res12']            = $sum_res12; 
                            $aryAddRow['pure_total_i2']    = $sum_pure_total_i2; 
                            $aryAddRow['tax_total_082']    = $sum_tax_total_082; 
                            $aryAddRow['tax_total_102']    = $sum_tax_total_102;   
                            $aryAddRow['total_profit_i2']  = $sum_total_profit_i2;           
                            $aryAddRow['total_amount2']    = $sum_total_amount2;               
                           // $aryAddRow['total_cnt2']       = $sum_total_cnt2;      
                            $aryAddRow['res32']            = $sum_res32;             
                            $aryAddRow['res42']            = $sum_res42;           
                            $aryAddRow['res52']            = $sum_res52;        
                            $aryAddRow['res62']            = $sum_res62; 
                            //合計	対象日比
                            $aryAddRow['cntper']            = ($sum_cntper);
                            $aryAddRow['utaxper']            = $sum_utaxper; 
                            $aryAddRow['pure_totalper']      = $sum_pure_totalper; 
                            $aryAddRow['total_cntper']       = $sum_total_cntper; 
                            $aryAddRow['res1per1']           = $sum_res1per1; 
                            $aryAddRow['pure_total_iper']    = $sum_pure_total_iper; 
                            $aryAddRow['tax_total_08per']    = $sum_tax_total_08per; 
                            $aryAddRow['tax_total_10per']    = $sum_tax_total_10per;   
                            $aryAddRow['total_profit_iper']  = $sum_total_profit_iper;           
                            $aryAddRow['total_amountper']    = $sum_total_amountper;               
                            //$aryAddRow['total_cntper']       = $sum_total_cntper;      
                            $aryAddRow['res3per']            = $sum_res3per;             
                            $aryAddRow['res4per']            = $sum_res4per;           
                            $aryAddRow['res5per']            = $sum_res5per;        
                            $aryAddRow['res6per']            = $sum_res6per; 
                            $aryAddRow['record_type'] = 'S';
                            // 行追加
                            if(!$_POST['tenpo']){
                                array_push($ledgerSheetDetailList, $aryAddRow);
                            }
                        }      
                        // 店舗合計のクリア
                         $sum_utax            = 0; 
                         $sum_cnt           = 0; 
                        $sum_pure_total      = 0; 
                       // $sum_res1            = 0; 
                        $sum_pure_total_i      = 0; 
                        $sum_tax_total_08      = 0;
                        $sum_tax_total_10      = 0;
                        //$sum_res2              = 0; 
                        $sum_total_profit_i    = 0; 
                        $sum_total_amount      = 0; 
                       // $sum_total_cnt         = 0; 
                        $sum_res3              = 0; 
                        $sum_res4              = 0; 
                        $sum_res5              = 0; 	
                        $sum_res6              = 0; 
                           // 店舗合計のクリア
                        $sum_utax2           = 0;
                         $sum_cnt2            = 0; 
                        $sum_pure_total2      = 0;
                        //$sum_res12            = 0; 
                        $sum_pure_total_i2      = 0; 
                        $sum_tax_total_082      = 0;
                        $sum_tax_total_102      = 0;
                        //$sum_res2              = 0; 
                        $sum_total_profit_i2    = 0; 
                        $sum_total_amount2      = 0; 
                        //$sum_total_cnt2         = 0; 
                        $sum_res32              = 0; 
                        $sum_res42              = 0; 
                        $sum_res52              = 0; 	
                        $sum_res62              = 0; 	


          

                    // 取得したリストを初期セット
                    $aryAddRow = $list[$intL];
                    $organization_id = intval($list[$intL]['organization_id']);
                    
//                    // 店舗が変わったときは小計行になるので店舗名を空白にする
//                    if ($intL > 0 && $organization_id === $organization_id_bef){
//                        $aryAddRow['abbreviated_name'] = '';
//                    }

                    // 企業計モードの時は店舗名部分に企業計を設定
//                    if($_POST['tenpo'] && $intL === 0){
//                        $aryAddRow['abbreviated_name'] = '企業計';
//                    }

//                    // 店舗が変わったときは小計行になるので各内容のを空白にする
//                    if ($organization_id === $organizationId){
//                        $aryAddRow['res1'] = '';
//                        $aryAddRow['pure_total_i'] = '';
//                        $aryAddRow['tax_total_08'] = '';
//                        $aryAddRow['tax_total_10'] = '';
//                        //$aryAddRow['res2'] = '';
//                        $aryAddRow['total_profit_i'] = '';
//                        $aryAddRow['total_amount'] = '';
//                        $aryAddRow['total_cnt'] = '';
//                        $aryAddRow['res3'] = '';
//                        $aryAddRow['res4'] = '';
//                        $aryAddRow['res5'] = '';
//                        $aryAddRow['res6'] = '';
                   // }
                    // リストの生成
                    $aryAddRow['record_type'] = 'D';    // Detail

                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                    
                    // 店舗合計に加算
                    $sum_utax                  += $list[$intL]['utax'];
                     $sum_cnt                  += $list[$intL]['cnt'];
                    $sum_pure_total           += $list[$intL]['pure_total'];
                  //  $sum_res1                   += $list[$intL]['res1'];
                    $sum_pure_total_i           += $list[$intL]['pure_total_i'];
                    $sum_tax_total_08           += $list[$intL]['tax_total_08'];
                    $sum_tax_total_10           += $list[$intL]['tax_total_10'];
                    //$sum_res2                   += $list[$intL]['res2'];
                    $sum_total_profit_i         += $list[$intL]['total_profit_i'];	
                    $sum_total_amount           += $list[$intL]['total_amount'];
                   // $sum_total_cnt              += $list[$intL]['total_cnt'];
                    $sum_res3                   += $list[$intL]['res3'];	
                    $sum_res4                   += $list[$intL]['res4'];
                    $sum_res5                   += $list[$intL]['res5'];	
                    $sum_res6                   += $list[$intL]['res6'];
                    //	合計	対象日
                    $sum_utax2                  += $list[$intL]['utax2'];
                    $sum_cnt2                  += $list[$intL]['cnt2'];
                    $sum_pure_total2           += $list[$intL]['pure_total2'];
                  //  $sum_res12                  += $list[$intL]['res12'];
                    $sum_pure_total_i2           += $list[$intL]['pure_total_i2'];
                    $sum_tax_total_082           += $list[$intL]['tax_total_082'];
                    $sum_tax_total_102           += $list[$intL]['tax_total_102'];
                    $sum_total_profit_i2         += $list[$intL]['total_profit_i2'];	
                    $sum_total_amount2           += $list[$intL]['total_amount2'];
                    //$sum_total_cnt2              += $list[$intL]['total_cnt2'];
                    $sum_res32                   += $list[$intL]['res32'];	
                    $sum_res42                   += $list[$intL]['res42'];
                    $sum_res52                   += $list[$intL]['res52'];	
                    $sum_res62                   += $list[$intL]['res62'];
                    //合計	対象日比
                    //            $pageMaxNo=$pageMaxNo[0]["rownos"] / $pagedRecordCnt;
//            if($pageMaxNo[0]["rownos"] > 0){
//                $round_up = $pageMaxNo[0]["rownos"] / $pagedRecordCnt;
//                $pageMaxNo = ceil(round($round_up,2,PHP_ROUND_HALF_UP));
//                //$pageMaxNo+1;
//            }
                   // $sum_utaxper                   = ceil($sum_res1/ $sum_res12*100);
                    //$sum_pure_totalper           =ceil($sum_pure_total_i/$sum_pure_total_i2*100);
                    $sum_cntper                  = ($sum_cnt/$sum_cnt2*100);
                    $sum_res1per1                  = (($sum_utax + $sum_pure_total) / ($sum_utax2+$sum_pure_total2)*100);
                    $sum_pure_total_iper           =($sum_pure_total_i/$sum_pure_total_i2*100);
                    $sum_tax_total_08per           =($sum_tax_total_08/$sum_tax_total_082*100);
                    $sum_tax_total_10per           =($sum_tax_total_10/$sum_tax_total_102*100);
                    $sum_total_profit_iper         =($sum_total_profit_i/$sum_total_profit_i2 *100);
                    $sum_total_amountper           =($sum_total_amount/$sum_total_amount2*100);    
                    $sum_res3per                   =($sum_res3/$sum_res32*100);
                    $sum_res4per                   =($sum_res4/$sum_res42*100); 
                    $sum_res5per                   =($sum_res5/$sum_res52*100); 
                    $sum_res6per                   =($sum_res6/$sum_res62*100); 
                    
                   // 企業計に加算
                    //	基準日
                    $sumall_utax                   += $list[$intL]['utax'];
                    $sumall_cnt                   += $list[$intL]['cnt'];
                   $sumall_pure_total           += $list[$intL]['pure_total'];
                  // $sumall_res1                   += $list[$intL]['res1'];
                   $sumall_pure_total_i           += $list[$intL]['pure_total_i'];
                   $sumall_tax_total_08           += $list[$intL]['tax_total_08'];
                   $sumall_tax_total_10           += $list[$intL]['tax_total_10'];
                   $sumall_total_profit_i         += $list[$intL]['total_profit_i'];	
                   $sumall_total_amount           += $list[$intL]['total_amount'];
                   //$sumall_total_cnt              += $list[$intL]['total_cnt'];
                   $sumall_res3                   += $list[$intL]['res3'];	
                   $sumall_res4                   += $list[$intL]['res4'];
                   $sumall_res5                   += $list[$intL]['res5'];	
                   $sumall_res6                   += $list[$intL]['res6'];
                  //総合計	対象日
                    $sumall_utax2                   += $list[$intL]['utax2'];
                   $sumall_cnt2                   += $list[$intL]['cnt2'];
                   $sumall_pure_total2           += $list[$intL]['pure_total2'];
                  // $sumall_res12                   += $list[$intL]['res12'];
                   $sumall_pure_total_i2           += $list[$intL]['pure_total_i2'];
                   $sumall_tax_total_082          += $list[$intL]['tax_total_082'];
                   $sumall_tax_total_102           += $list[$intL]['tax_total_102'];
                   $sumall_total_profit_i2         += $list[$intL]['total_profit_i2'];	
                   $sumall_total_amount2           += $list[$intL]['total_amount2'];
                  // $sumall_total_cnt2              += $list[$intL]['total_cnt2'];
                   $sumall_res32                   += $list[$intL]['res32'];	
                   $sumall_res42                 += $list[$intL]['res42'];
                   $sumall_res52                   += $list[$intL]['res52'];	
                   $sumall_res62                   += $list[$intL]['res62'];
                  //総合計	対象日比
                   //$sumall_res1per1                   += $list[$intL]['res1per'];
//                   $sumall_pure_total_iper           += $list[$intL]['pure_total_iper'];
//                   $sumall_tax_total_08per           += $list[$intL]['tax_total_08per'];
//                   $sumall_tax_total_10per           += $list[$intL]['tax_total_10per'];
//                   $sumall_total_profit_iper         += $list[$intL]['total_profit_iper'];	
//                   $sumall_total_amountper           += $list[$intL]['total_amountper'];
//                   $sumall_total_cntper              += $list[$intL]['total_cntper'];
//                   $sumall_res3per                   += $list[$intL]['res3per'];	
//                   $sumall_res4per                   += $list[$intL]['res4per'];
//                   $sumall_res5per                   += $list[$intL]['res5per'];	
//                   $sumall_res6per                   += $list[$intL]['res6per'];
//                    // 値保持
//                    $organization_id_bef = $organization_id;
                }
                // ループ脱出後
                if (count($list) > 0){
                    // 店舗合計行
                    $aryAddRow = array();
                    $aryAddRow['org_nm']               = '合計';
                    $aryAddRow['utax']                 =  $sum_utax;
                    $aryAddRow['cnt']                  = $sum_cnt; 
                    $aryAddRow['pure_total']           =  $sum_pure_total; 
                   // $aryAddRow['res1']                 =  $sum_res1;
                    $aryAddRow['pure_total_i']     =  $sum_pure_total_i; 
                    $aryAddRow['tax_total_08']     =  $sum_tax_total_08 ;
                    $aryAddRow['tax_total_10']     =  $sum_tax_total_10; 
                    $aryAddRow['total_profit_i']   =  $sum_total_profit_i;
                    $aryAddRow['total_amount']     =  $sum_total_amount; 
                   // $aryAddRow['total_cnt']       =  $sum_total_cnt;
                    $aryAddRow['res3']             =  $sum_res3;
                    $aryAddRow['res4']             =  $sum_res4;
                    $aryAddRow['res5']             =  $sum_res5;
                    $aryAddRow['res6']             =  $sum_res6;  
                    // 合計	対象日
                    $aryAddRow['utax2']                 =  $sum_utax2;
                    $aryAddRow['pure_total2']          =  $sum_pure_total2;
                    $aryAddRow['cnt2']                = $sum_cnt2; 
                   // $aryAddRow['res12']            = $sum_res12; 
                    $aryAddRow['pure_total_i2']    = $sum_pure_total_i2; 
                    $aryAddRow['tax_total_082']    = $sum_tax_total_082; 
                    $aryAddRow['tax_total_102']    = $sum_tax_total_102;   
                    $aryAddRow['total_profit_i2']  = $sum_total_profit_i2;           
                    $aryAddRow['total_amount2']    = $sum_total_amount2;               
                  //  $aryAddRow['total_cnt2']       = $sum_total_cnt2;      
                    $aryAddRow['res32']            = $sum_res32;             
                    $aryAddRow['res42']            = $sum_res42;           
                    $aryAddRow['res52']            = $sum_res52;        
                    $aryAddRow['res62']            = $sum_res62; 
                    //合計	対象日比
                    $aryAddRow['utaxper']           = $sum_utaxper;
                    $aryAddRow['cntper']           = $sum_cntper; 
                    $aryAddRow['pure_totalper']      = $sum_pure_totalper; 
                    $aryAddRow['res1per1']            = $sum_res1per1; 
                    $aryAddRow['pure_total_iper']    = $sum_pure_total_iper; 
                    $aryAddRow['tax_total_08per']    = $sum_tax_total_08per; 
                    $aryAddRow['tax_total_10per']    = $sum_tax_total_10per;   
                    $aryAddRow['total_profit_iper']  = $sum_total_profit_iper;           
                    $aryAddRow['total_amountper']    = $sum_total_amountper;               
                    $aryAddRow['total_cntper']       = $sum_total_cntper;      
                    $aryAddRow['res3per']            = $sum_res3per;             
                    $aryAddRow['res4per']            = $sum_res4per;           
                    $aryAddRow['res5per']            = $sum_res5per;        
                    $aryAddRow['res6per']            = $sum_res6per; 
                    $aryAddRow['record_type'] = 'S';
                    // 行追加
                    if(!$_POST['tenpo']){
                        array_push($ledgerSheetDetailList, $aryAddRow);
                    }
                    
                    // 仕切りの空行(background-color: blue;
                    $aryAddRow = array();
                    $aryAddRow['record_type'] = 'P';    // Partition
                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                    
                    // 総合計 	基準日
                    $aryAddRow = array();
                    $aryAddRow['org_nm']   = '総合計';
                     $aryAddRow['utax']             = $sumall_utax;
                     $aryAddRow['cnt']             = $sumall_cnt;
                    $aryAddRow['pure_total']     = $sumall_pure_total; 
                   // $aryAddRow['res1']             = $sumall_res1;
                    $aryAddRow['pure_total_i']     = $sumall_pure_total_i; 
                    $aryAddRow['tax_total_08']     = $sumall_tax_total_08 ;
                    $aryAddRow['tax_total_10']     = $sumall_tax_total_10; 
                    $aryAddRow['total_profit_i']   = $sumall_total_profit_i;
                    $aryAddRow['total_amount']     = $sumall_total_amount; 
                    //$aryAddRow['total_cnt']        = $sumall_total_cnt;
                    $aryAddRow['res3']             = $sumall_res3;
                    $aryAddRow['res4']             = $sumall_res4;
                    $aryAddRow['res5']             = $sumall_res5;
                    $aryAddRow['res6']             = $sumall_res6; 
                    //	総合計	対象日
                     $aryAddRow['utax2']            =$sumall_utax2; 
                    $aryAddRow['cnt2']              =$sumall_cnt2; 
                    $aryAddRow['pure_total2']       =$sumall_pure_total2; 
                   // $aryAddRow['res12']            =$sumall_res12; 
                    $aryAddRow['pure_total_i2']    =$sumall_pure_total_i2; 
                    $aryAddRow['tax_total_082']    =$sumall_tax_total_082; 
                    $aryAddRow['tax_total_102']    =$sumall_tax_total_102;   
                    $aryAddRow['total_profit_i2']  =$sumall_total_profit_i2;           
                    $aryAddRow['total_amount2']    =$sumall_total_amount2;               
                   // $aryAddRow['total_cnt2']       =$sumall_total_cnt2;      
                    $aryAddRow['res32']            =$sumall_res32;             
                    $aryAddRow['res42']            =$sumall_res42;           
                    $aryAddRow['res52']            =$sumall_res52;        
                    $aryAddRow['res62']            =$sumall_res62;  
                   //	総合計	対象日比
                    $aryAddRow['res1per12']            =ceil(($sumall_pure_total + $sumall_utax) /($sumall_pure_total2 + $sumall_utax2 ) *100 ); 
                   // $aryAddRow['pure_totalper']        =ceil($sumall_pure_total_i /$sumall_pure_total_i2 *100 ); 
                   // $aryAddRow['utaxper']            =ceil($sumall_tax_total_08 /$sumall_tax_total_082 *100); 
                    //$sumall_res1per12                 =ceil($sumall_pure_total_i /$sumall_pure_total_i2 *100 ); 
                    $aryAddRow['pure_total_iper']    =ceil($sumall_pure_total_i /$sumall_pure_total_i2 *100 ); 
                    $aryAddRow['cntper']             =ceil($sumall_cnt /$sumall_cnt2 *100 ); 
                    $aryAddRow['tax_total_08per']    =ceil($sumall_tax_total_08 /$sumall_tax_total_082 *100); 
                    $aryAddRow['tax_total_10per']    =ceil($sumall_tax_total_10 / $sumall_tax_total_102 *100);   
                    $aryAddRow['total_profit_iper']  =ceil($sumall_total_profit_i / $sumall_total_profit_i2*100);           
                    $aryAddRow['total_amountper']    =ceil($sumall_total_amount /$sumall_total_amount2 *100) ;               
                   // $aryAddRow['total_cntper']       =ceil($sumall_total_cnt / $sumall_total_cnt2 *100);      
                    $aryAddRow['res3per']            =ceil($sumall_res3 / $sumall_res32 *100);             
                    $aryAddRow['res4per']            =ceil($sumall_res4 / $sumall_res42 *100);           
                    $aryAddRow['res5per']            =ceil($sumall_res5 / $sumall_res52 *100);        
                    $aryAddRow['res6per']            =ceil($sumall_res6 /$sumall_res62*100); 
                    $aryAddRow['record_type'] = 'F';    // Footer
                     //行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                }
            }

            // 検索組織
            $searchArray = array(
                'org_id'            => str_replace("'","",$_POST['org_id']),
                //'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
//                'bb_date_kbn'       => $bb_date_kbn,
//                'bb_date_dspkbn'    => $bb_date_dspkbn,
//                'zero_kbn'          => $zero_kbn,
//                'amount_kbn'        => $amount_kbn,
//                'amount_str'        => $amount_str,
//                'amount_end'        => $amount_end,
//                'saleprice_kbn'     => $saleprice_kbn,
//                'prodclass_cd'      => str_replace("'","",$_POST['prodclass_cd']),
//                'supp_cd'           => str_replace("'","",$_POST['supp_cd']),
                'sort'              => $sort,
            );
            $checked = "";
            if($_POST['tenpo']){
                $searchArray['tenpo'] = true;
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetSalesResultsComparePanel.html';
            }
        }  
        /**
         * CSV出力
         * @return    なし
         */
        public function csvoutputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START csvoutputAction");

            if(!$_POST["csv_data"]){
                return;
            }
           // $startDateM = date("Ymd");
            $startDateR =date("Ymd");
            $endDateR   =date("Ymd");
            $startDateT = date("Ymd");
            $endDateT   = date("Ymd");
            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateR,$endDateR,$startDateT,$endDateT )).".csv", 'SJIS-win', 'UTF-8');

            if( touch($file_path) ){
                // オブジェクト生成
                $file = new SplFileObject( $file_path, "w" ); 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                //$file = fopen($file_path, 'a');
                // 取得結果を画面と同じように再構築して行として搭載
                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                $csv_data = json_decode($_POST['csv_data'],true);
                foreach( $csv_data as $str ){
                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    //$export_arr = explode(",", $str);
                    
                    // 内容行を1行ごとにCSVデータへ
                    if($str[0] === '"'){
                        $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
                    }else{
                        $file = fopen($file_path, 'a');fwrite($file,$str."\r");
                    }   
                }
            }
            $_POST["csv_data"] = '';            
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=棚卸一覧表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");
        }
    }     
?>
