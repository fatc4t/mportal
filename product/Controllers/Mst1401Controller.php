<?php
    /**
     * @file      期間原価コントローラ
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      期間原価の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst1401.php';
    /**
     * 期間原価コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst1401Controller extends BaseController
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

//        /**
//         * 商品部門画面初期表示
//         * @return    なし
//         */
//        public function showAction()
//        {
//            global $Log; // グローバル変数宣言
//            $Log->trace("START showAction");
//
//            $_SESSION["PAGE_NO"] = 1;
//            
//            $this->initialDisplay();
//            $Log->trace("END   showAction");
//        }
//
//        /**
//         * 商品部門登録処理
//         * @return    なし
//         */
//        public function addInputAction()
//        {
//            global $Log; // グローバル変数宣言
//            $Log->trace("START addInputAction");
//            
//            $mst1401 = new Mst1401();
//            
//            print_r($_POST);
//            if ($_POST["updins"] == "ins" ) {
//                $mst1401->insertdata();
//            }
//            else{
//                $mst1401->updatedata();
//            }
//            
//            $this->initialDisplay();
//            $Log->trace("END   addInputAction");
//        }        
//
//        /**
//         * 商品部門削除処理
//         * @return    なし
//         */
//        public function delAction()
//        {
//            global $Log; // グローバル変数宣言
//            $Log->trace("START delAction");
//            $mst1401 = new Mst1401();
//
//            print_r($_GET);
//            if ($_GET["sectcd"]) {
//                $mst1401->deldata($_GET["sectcd"]);            
//                $_GET["sectcd"]="";
//                $this->initialDisplay();
//                $Log->trace("END   delAction");
//            }
//
//            if ($_GET["orgnid"] && $_GET["sectcd"]) {
//                $mst1401->deldata($_GET["orgnid"], $_GET["sectcd"]);            
//                $_GET["orgnid"]="";
//                $_GET["sectcd"]="";
//                $this->initialDisplay();
//                $Log->trace("END   delAction");
//            }
//            $Log->trace("END delAction");
//        }
//        
//        /**
//         * 商品部門検索画面表示
//         * @return    なし
//         */
//        public function searchAction()
//        {
//            global $Log; // グローバル変数宣言
//            $Log->trace("START searchAction");
//
//            // 商品部門モデルインスタンス化
//            $mst1401 = new Mst1401();
//
//            // 店舗コードプルダウン
//            $abbreviatedNameList        = $mst1401->setPulldown->getSearchOrganizationList( 'reference', true, true );
//            
//            // 商品部門一覧データ取得
//            $mst1401List        = $mst1401->getMst1401List();
//            $mst1401_searchdata = $mst1401->searchMst1401Data();
//            for ($intL = 0; $intL < Count($mst1401_searchdata); $intL ++) {
//                $mst1401_searchdata[$intL]['abbreviated_name'] = '';
//                foreach ($abbreviatedNameList as $abbreviatedName) {
//                    if ($mst1401_searchdata[$intL]['organization_id'] === $abbreviatedName['organization_id']) {
//                        $abbreviated_name = $abbreviatedName['abbreviated_name'];
//                        // 階層を示すための先頭の｜├└全角スペースを除去
//                        $abbreviated_name = ltrim($abbreviated_name, '｜');
//                        $abbreviated_name = ltrim($abbreviated_name, '├');
//                        $abbreviated_name = ltrim($abbreviated_name, '└');
//                        $abbreviated_name = ltrim($abbreviated_name, '　');
//                        $mst1401_searchdata[$intL]['abbreviated_name'] = $abbreviated_name;
//                        break;
//                    }
//                }
//            }
//
//            require_once './View/Mst1401Search.html';
//            $Log->trace("END searchAction");
//        } 
//
//        /**
//         * 期間原価画面
//         * @note     パラメータは、View側で使用
//         * @param    $startFlag (初期表示時フラグ)
//         * @return   無
//         */
//        private function initialDisplay()
//        {
//            global $Log;  // グローバル変数宣言
//            $Log->trace("START initialDisplay");
//            $mst1401_getdata    = '';
//            $mst1401_sd         = '';
//            $mst1401List        = '';
//            $mst1401_alldata    = '';
//            $mst0201_alldata    = '';
//
//            $iorgn_id           = '';
//            $ssect_cd           = '';
//
//            // 商品部門モデルインスタンス化
//            $mst1401 = new Mst1401();
//            // 商品部門一覧データ取得
//            $mst1401List        = $mst1401->getMst1401List();
//            $mst1401_searchdata = $mst1401->searchMst1401Data();
//print_r($mst1401_searchdata);
//            if ($_GET["orgnid"]) {
//                $iorgn_id = $_GET["orgnid"];
//            }
//            if ($_POST["ORGANIZATION_ID"]) {
//                $iorgn_id = $_POST["ORGANIZATION_ID"];
//            }
//
//            if ($_GET["sectcd"]) {
//                $ssect_cd = $_GET["sectcd"];
//            }
//            if ($_POST["SECT_CD"]) {
//                $ssect_cd = $_POST["SECT_CD"];
//            }
//
//            // organization_id:sect_cd 形式の値の場合は分割
//            if (strpos($ssect_cd, ':') > 0) {
//                $param = explode(":", $ssect_cd);
//                $iorgn_id = $param[0];
//                $ssect_cd = $param[1];                
//            } 
//            print_r($iorgn_id);
//            print_r($ssect_cd);
//            // 一覧表用検索項目
//            if ($ssect_cd != "" ){
//                    $mst1401_getdata = $mst1401->getMst1401Data($ssect_cd);
//            }
//            if ($iorgn_id != "" && $ssect_cd != "" ){
//                    $mst1401_getdata = $mst1401->getMst1401Data($iorgn_id, $ssect_cd);
//            }
//            else if ($iorgn_id != ""){
//                // 登録画面で組織プルダウン変更時
//                $mst1401_getdata = array(
//                    'organization_id'   => $iorgn_id,
//                    'insdatetime'       => date('Y-m-d H:i:s'),
//                    'upddatetime'       => date('Y-m-d H:i:s'));
//            }
//
//            // 店舗コードプルダウン
//            $abbreviatedNameList        = $mst1401->setPulldown->getSearchOrganizationList( 'reference', true, true );
//
//            // 分類一覧データ取得
//            $mst1401_alldata = $mst1401->getMst1401Data($iorgn_id);
//            
//            // 商品一覧データ取得
//            $mst0201_alldata = $mst1401->getMst0201Data($iorgn_id);
//            
//            // システムマスタ取得
//            $mst0010_getdata = $mst1401->getMst0010Data($iorgn_id);
//            // 部門コード入力長
//            if (intval($mst0010_getdata[0]['sectsize']) === 0) {
//                $mst0010_getdata[0]['sectsize'] = '5';
//            }
//            
//            // 顧客一覧データ取得
//           // $customerAllsearch = $customer->getCustomersearchdata();
//            // 顧客一覧レコード数
//            //$customerRecordCnt = count($customerAllList);
//            // 表示レコード数
//            //$pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
//
//            // 指定ページ
//           // $pageNo = $this->getDuringTransitionPageNo($customerRecordCnt, $pagedRecordCnt);
//
//            // シーケンスIDName
//            //$idName = "cust_id";
//            // 一覧表
//            //$customerList = $this->refineListDisplayNoSpecifiedPage($customerAllList, $idName, $pagedRecordCnt, $pageNo);
//
//            // 表示レコード数が11以上の場合、スクロールバーを表示
//            $isScrollBar = false;
//            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($customerList) >= 15)
//            {
//                $isScrollBar = true;
//            }
//
//            // 表示数リンクのロック処理
//            //$recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
//            // ページ数
//            //$pagedCnt = ceil($customerRecordCnt /  $pagedRecordCnt);
//            //$positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
//            // ソートマーク初期化
//            //$headerArray = $this->changeHeaderItemMark(2);
//            // NO表示用
//            //$display_no = 0;
//            // ソートがを選択されたかどうかのチェックフラグ（customerTablePanel.htmlにて使用）
//            //$customerNoSortFlag = false;
//
//            require_once './View/Mst1401Panel.html';
//
//            $Log->trace("END   initialDisplay");
//        }
//
//        /**
//         * 顧客テーブル画面
//         * @note     パラメータは、View側で使用
//         * @return   無
//         */
//        private function initialList()
//        {
//            global $Log;  // グローバル変数宣言
//            $Log->trace("START initialList");
//
//            $customer = new customer();
//
//            // シーケンスIDName
//            $idName = "cust_id";
//
//            $searchArray = array(
//                'is_del'                                => 0,
//                'minCustCd'                             => parent::escStr( $_POST['searchMinCustCd'] ),
//                'maxCustCd'                             => parent::escStr( $_POST['searchMaxCustCd'] ),
//                'custNm'                                => parent::escStr( $_POST['searchCustNm'] ),
//                'custKn'                                => parent::escStr( $_POST['searchCustKn'] ),
//                'custKnNullCheck'                       => parent::escStr( $_POST['searchCustKnNullCheck'] ),
//                'minBirth'                              => parent::escStr( $_POST['searchMinBirth'] ),
//                'maxBirth'                              => parent::escStr( $_POST['searchMaxBirth'] ),
//                'birthNullCheck'                        => parent::escStr( $_POST['searchBirthNullCheck'] ),
//                'birthMonth'                            => parent::escStr( $_POST['searchBirthMonth'] ),
//                'minMemorial'                           => parent::escStr( $_POST['searchMinMemorial'] ),
//                'maxMemorial'                           => parent::escStr( $_POST['searchMaxMemorial'] ),
//                'memorialNullCheck'                     => parent::escStr( $_POST['searchMemorialNullCheck'] ),
//                'zip'                                   => parent::escStr( $_POST['searchZip'] ),
//                'zipNullCheck'                          => parent::escStr( $_POST['searchZipNullCheck'] ),
//                'sex'                                   => parent::escStr( $_POST['searchSex'] ),
//                'sexNullCheck'                          => parent::escStr( $_POST['searchSexNullCheck'] ),
//                'addr1'                                 => parent::escStr( $_POST['searchAddr1'] ),
//                'addr1NullCheck'                        => parent::escStr( $_POST['searchAddr1NullCheck'] ),
//                'addr2'                                 => parent::escStr( $_POST['searchAddr2'] ),
//                'addr2NullCheck'                        => parent::escStr( $_POST['searchAddr2NullCheck'] ),
//                'addr3'                                 => parent::escStr( $_POST['searchAddr3'] ),
//                'addr3NullCheck'                        => parent::escStr( $_POST['searchAddr3NullCheck'] ),
//                'custSumDay'                            => parent::escStr( $_POST['searchCustSumDay'] ),
//                'custSumDayNullCheck'                   => parent::escStr( $_POST['searchCustSumDayNullCheck'] ),
//                'feesReceKbn'                           => parent::escStr( $_POST['searchFeesReceKbn'] ),
//                'feesReceKbnNullCheck'                  => parent::escStr( $_POST['searchFeesReceKbnNullCheck'] ),
//                'credImpoKbn'                           => parent::escStr( $_POST['searchCredImpoKbn'] ),
//                'credImpoKbnNullCheck'                  => parent::escStr( $_POST['searchCredImpoKbnNullCheck'] ),
//                'minPublish'                            => parent::escStr( $_POST['searchMinPublish'] ),
//                'maxPublish'                            => parent::escStr( $_POST['searchMaxPublish'] ),
//                'publishNullCheck'                      => parent::escStr( $_POST['searchPublishNullCheck'] ),
//                'minLimitdate'                          => parent::escStr( $_POST['searchMinLimitdate'] ),
//                'maxLimitdate'                          => parent::escStr( $_POST['searchMaxLimitdate'] ),
//                'limitdateNullCheck'                    => parent::escStr( $_POST['searchLimitdateNullCheck'] ),
//                'minRenew'                              => parent::escStr( $_POST['searchMinRenew'] ),
//                'maxRenew'                              => parent::escStr( $_POST['searchMaxRenew'] ),
//                'renewNullCheck'                        => parent::escStr( $_POST['searchRenewNullCheck'] ),
//                'dissenddm'                             => parent::escStr( $_POST['searchDissenddm'] ),
//                'dissendemail'                          => parent::escStr( $_POST['searchDissendemail'] ),
//                'dissendimode'                          => parent::escStr( $_POST['searchDissendimode'] ),
//                'taxCalcKbn'                            => parent::escStr( $_POST['searchTaxCalcKbn'] ),
//                'taxFracKbn'                            => parent::escStr( $_POST['searchTaxFracKbn'] ),
//                'tel'                                   => parent::escStr( $_POST['searchTel'] ),
//                'telNullCheck'                          => parent::escStr( $_POST['searchTelNullCheck'] ),
//                'fax'                                   => parent::escStr( $_POST['searchFax'] ),
//                'faxNullCheck'                          => parent::escStr( $_POST['searchTaxNullCheck'] ),
//                'hphone'                                => parent::escStr( $_POST['searchHphone'] ),
//                'hphoneNullCheck'                       => parent::escStr( $_POST['searchHphoneNullCheck'] ),
//                'email'                                 => parent::escStr( $_POST['searchEmail'] ),
//                'emailNullCheck'                        => parent::escStr( $_POST['searchEmailNullCheck'] ),
//                'imode'                                 => parent::escStr( $_POST['searchImode'] ),
//                'imodeNullCheck'                        => parent::escStr( $_POST['searchImodeNullCheck'] ),
//                'organizationName'                      => parent::escStr( $_POST['searchOrganizationName'] ),
//                'organizationNameNullCheck'             => parent::escStr( $_POST['searchOrganizationNameNullCheck'] ),
//                'custTypeCd'                            => parent::escStr( $_POST['searchCustTypeCd'] ),
//                'custTypeCdNullCheck'                   => parent::escStr( $_POST['searchCustTypeCdNullCheck'] ),
//                'areaCd'                                => parent::escStr( $_POST['searchAreaCd'] ),
//                'areaCdNullCheck'                       => parent::escStr( $_POST['searchAreaCdNullCheck'] ),
//                'userNo'                                => parent::escStr( $_POST['searchUserNo'] ),
//                'userNoNullCheck'                       => parent::escStr( $_POST['searchUserNoNullCheck'] ),
//                'minLstVisitDt'                         => parent::escStr( $_POST['searchMinLstVisitDt'] ),
//                'maxLstVisitDt'                         => parent::escStr( $_POST['searchMaxLstVisitDt'] ),
//                'lstVisitDtNullCheck'                   => parent::escStr( $_POST['searchLstVisitDtNullCheck'] ),
//                'lastOrganizationName'                  => parent::escStr( $_POST['searchLastOrganizationName'] ),
//                'lastOrganizationNameNullCheck'         => parent::escStr( $_POST['searchLastOrganizationNameNullCheck'] ),
//                'note1'                                 => parent::escStr( $_POST['searchNote1'] ),
//                'note1NullCheck'                        => parent::escStr( $_POST['searchNote1NullCheck'] ),
//                'note2'                                 => parent::escStr( $_POST['searchNote2'] ),
//                'note2NullCheck'                        => parent::escStr( $_POST['searchNote2NullCheck'] ),
//                'note3'                                 => parent::escStr( $_POST['searchNote3'] ),
//                'note3NullCheck'                        => parent::escStr( $_POST['searchNote3NullCheck'] ),
//                'note4'                                 => parent::escStr( $_POST['searchNote4'] ),
//                'note4NullCheck'                        => parent::escStr( $_POST['searchNote4NullCheck'] ),
//                'note5'                                 => parent::escStr( $_POST['searchNote5'] ),
//                'note5NullCheck'                        => parent::escStr( $_POST['searchNote5NullCheck'] ),
//                'custBCd01'                             => parent::escStr( $_POST['searchCustBCd01'] ),
//                'custBCd01NullCheck'                    => parent::escStr( $_POST['searchCustBCd01NullCheck'] ),
//                'custBCd02'                             => parent::escStr( $_POST['searchCustBCd02'] ),
//                'custBCd02NullCheck'                    => parent::escStr( $_POST['searchCustBCd02NullCheck'] ),
//                'custBCd03'                             => parent::escStr( $_POST['searchCustBCd03'] ),
//                'custBCd03NullCheck'                    => parent::escStr( $_POST['searchCustBCd03NullCheck'] ),
//                'custBCd04'                             => parent::escStr( $_POST['searchCustBCd04'] ),
//                'custBCd04NullCheck'                    => parent::escStr( $_POST['searchCustBCd04NullCheck'] ),
//                'custBCd05'                             => parent::escStr( $_POST['searchCustBCd05'] ),
//                'custBCd05NullCheck'                    => parent::escStr( $_POST['searchCustBCd05NullCheck'] ),
//                'custBCd06'                             => parent::escStr( $_POST['searchCustBCd06'] ),
//                'custBCd06NullCheck'                    => parent::escStr( $_POST['searchCustBCd06NullCheck'] ),
//                'custBCd07'                             => parent::escStr( $_POST['searchCustBCd07'] ),
//                'custBCd07NullCheck'                    => parent::escStr( $_POST['searchCustBCd07NullCheck'] ),
//                'custBCd08'                             => parent::escStr( $_POST['searchCustBCd08'] ),
//                'custBCd08NullCheck'                    => parent::escStr( $_POST['searchCustBCd08NullCheck'] ),
//                'custBCd09'                             => parent::escStr( $_POST['searchCustBCd09'] ),
//                'custBCd09NullCheck'                    => parent::escStr( $_POST['searchCustBCd09NullCheck'] ),
//                'custBCd10'                             => parent::escStr( $_POST['searchCustBCd10'] ),
//                'custBCd10NullCheck'                    => parent::escStr( $_POST['searchCustBCd10NullCheck'] ),
//                'minTrandate'                           => parent::escStr( $_POST['searchMinTrandate'] ),
//                'maxTrandate'                           => parent::escStr( $_POST['searchMaxTrandate'] ),
//                'minTotal'                              => parent::escStr( $_POST['searchMinTotal'] ),
//                'maxTotal'                              => parent::escStr( $_POST['searchMaxTotal'] ),
//                'minVisitCnt'                           => parent::escStr( $_POST['searchMinVisitCnt'] ),
//                'maxVisitCnt'                           => parent::escStr( $_POST['searchMaxVisitCnt'] ),
//                'prodCd01'                              => parent::escStr( $_POST['searchProdCd01'] ),
//                'prodCd02'                              => parent::escStr( $_POST['searchProdCd02'] ),
//                'prodCd03'                              => parent::escStr( $_POST['searchProdCd03'] ),
//                'prodCd04'                              => parent::escStr( $_POST['searchProdCd04'] ),
//                'prodCd05'                              => parent::escStr( $_POST['searchProdCd05'] ),
//                'prodCd06'                              => parent::escStr( $_POST['searchProdCd06'] ),
//                'prodCd07'                              => parent::escStr( $_POST['searchProdCd07'] ),
//                'prodCd08'                              => parent::escStr( $_POST['searchProdCd08'] ),
//                'prodCd09'                              => parent::escStr( $_POST['searchProdCd09'] ),
//                'prodCd10'                              => parent::escStr( $_POST['searchProdCd10'] ),
//                'sort'                                  => parent::escStr( $_POST['sortConditions'] ),
//            );
//
//            // 顧客一覧データ取得
//            $customerAllList = $customer->getListData($searchArray);
//            // 顧客一覧レコード数
//            $customerRecordCnt = count($customerAllList);
//            // セクション一覧表示レコード数
//            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
//
//            // 検索結果後の顧客レコードに対するページ数
//            $pagedCnt = ceil($customerRecordCnt /  $pagedRecordCnt);
//            // 指定ページ
//            $pageNo = $_SESSION["PAGE_NO"];
//            // 検索後の一覧画面
//            $customerList = $this->refineListDisplayNoSpecifiedPage($customerAllList, $idName, $pagedRecordCnt, $pageNo);
//
//            // 表示レコード数が11以上の場合、スクロールバーを表示
//            $isScrollBar = false;
//            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($customerList) >= 11)
//            {
//                $isScrollBar = true;
//            }
//
//            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
//            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
//            // 表示数リンクのロック処理
//            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
//            // ソート時のマーク変更メソッド
//            $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
//            // NO表示用
//            $display_no = $this->getDisplayNo( $customerRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
//            // ソートがを選択されたかどうかのチェックフラグ（customerTablePanel.htmlにて使用）
//            $customerNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
//
//            require_once './View/CustomerTablePanel.html';
//
//            $Log->trace("END   initialList");
//        }
//
//        /**
//         * ヘッダー部分ソート時のマーク変更
//         * @note     ソート番号により、ソートマークを設定する
//         * @param    $sortNo ソート条件番号
//         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
//         */
//        private function changeHeaderItemMark( $sortNo )
//        {
//            global $Log;  // グローバル変数宣言
//            $Log->trace("START changeHeaderItemMark");
//
//            // 初期化
//            $headerArray = array(
//                    'customerNoSortMark' => '',
//                    'customerCustCdSortMark' => '',
//                    'customerCustNmSortMark' => '',
//                    'customerCustKnSortMark' => '',
//                    'customerZipSortMark' => '',
//                    'customerAddr1SortMark' => '',
//                    'customerAddr2SortMark' => '',
//                    'customerAddr3SortMark' => '',
//                    'customerTelSortMark' => '',
//                    'customerFaxSortMark' => '',
//                    'customerHphoneSortMark' => '',
//                    'customerEmailSortMark' => '',
//                    'customerImodeSortMark' => '',
//                    'customerBirthSortMark' => '',
//                    'customerSexSortMark' => '',
//                    'customerMemorialSortMark' => '',
//                    'customerShopCdSortMark' => '',
//                    'customerCustTypeCdSortMark' => '',
//                    'customerAreaCdSortMark' => '',
//                    'customerStaffCdSortMark' => '',
//                    'customerDmTypeSortMark' => '',
//                    'customerOptionFlagSortMark' => '',
//                    'customerDissenddmSortMark' => '',
//                    'customerDissendEmailSortMark' => '',
//                    'customerDissendImodeSortMark' => '',
//                    'customerPublishSortMark' => '',
//                    'customerLimitdateSortMark' => '',
//                    'customerRenewSortMark' => '',
//                    'customerFeesReceKbnSortMark' => '',
//                    'customerCredImpoKbnSortMark' => '',
//                    'customerCustSumdaySortMark' => '',
//                    'customerTaxCalcKbnSortMark' => '',
//                    'customerTaxFracKbnSortMark' => '',
//                    'customerNote1SortMark' => '',
//                    'customerNote2SortMark' => '',
//                    'customerNote3SortMark' => '',
//                    'customerNote4SortMark' => '',
//                    'customerNote5SortMark' => '',
//                    'customerCustBNm1SortMark' => '',
//                    'customerCustBNm2SortMark' => '',
//                    'customerCustBNm3SortMark' => '',
//                    'customerCustBNm4SortMark' => '',
//                    'customerCustBNm5SortMark' => '',
//                    'customerCustBNm6SortMark' => '',
//                    'customerCustBNm7SortMark' => '',
//                    'customerCustBNm8SortMark' => '',
//                    'customerCustBNm9SortMark' => '',
//                    'customerCustBNm10SortMark' => '',
//                    'customerFstVisitDtSortMark' => '',
//                    'customerLstVisitDtSortMark' => '',
//                    'customerLstShopCdSortMark' => '',
//                    'customerVisitCntSortMark' => '',
//                    'customerAllTotalSortMark' => '',
//                    'customerAllProfitSortMark' => '',
//                    'customerAllPointSortMark' => '',                
//            );
//            if(!empty($sortNo))
//            {
//                $sortList[1] = "customerNoSortMark";
//                $sortList[2] = "customerNoSortMark";
//                $sortList[3] = "customerCustCdSortMark";
//                $sortList[4] = "customerCustCdSortMark";
//                $sortList[5] = "customerCustNmSortMark";
//                $sortList[6] = "customerCustNmSortMark";
//                $sortList[7] = "customerCustKnSortMark";
//                $sortList[8] = "customerCustKnSortMark";
//                $sortList[9] = "customerZipSortMark";
//                $sortList[10] = "customerZipSortMark";
//                $sortList[11] = "customerAddr1SortMark";
//                $sortList[12] = "customerAddr1SortMark";
//                $sortList[13] = "customerAddr2SortMark";
//                $sortList[14] = "customerAddr2SortMark";
//                $sortList[15] = "customerAddr3SortMark";
//                $sortList[16] = "customerAddr3SortMark";
//                $sortList[17] = "customerTelSortMark";
//                $sortList[18] = "customerTelSortMark";
//                $sortList[19] = "customerFaxSortMark";
//                $sortList[20] = "customerFaxSortMark";
//                $sortList[21] = "customerHphoneSortMark";
//                $sortList[22] = "customerHphoneSortMark";
//                $sortList[23] = "customerEmailSortMark";
//                $sortList[24] = "customerEmailSortMark";
//                $sortList[25] = "customerImodeSortMark";
//                $sortList[26] = "customerImodeSortMark";
//                $sortList[27] = "customerBirthSortMark";
//                $sortList[28] = "customerBirthSortMark";
//                $sortList[29] = "customerSexSortMark";
//                $sortList[30] = "customerSexSortMark";
//                $sortList[31] = "customerMemorialSortMark";
//                $sortList[32] = "customerMemorialSortMark";
//                $sortList[33] = "customerShopCdSortMark";
//                $sortList[34] = "customerShopCdSortMark";
//                $sortList[35] = "customerCustTypeCdSortMark";
//                $sortList[36] = "customerCustTypeCdSortMark";
//                $sortList[37] = "customerAreaCdSortMark";
//                $sortList[38] = "customerAreaCdSortMark";
//                $sortList[39] = "customerStaffCdSortMark";
//                $sortList[40] = "customerStaffCdSortMark";
//                $sortList[41] = "customerDmTypeSortMark";
//                $sortList[42] = "customerDmTypeSortMark";
//                $sortList[43] = "customerOptionFlagSortMark";
//                $sortList[44] = "customerOptionFlagSortMark";
//                $sortList[45] = "customerDissenddmSortMark";
//                $sortList[46] = "customerDissenddmSortMark";
//                $sortList[47] = "customerDissendEmailSortMark";
//                $sortList[48] = "customerDissendEmailSortMark";
//                $sortList[49] = "customerDissendImodeSortMark";
//                $sortList[50] = "customerDissendImodeSortMark";
//                $sortList[51] = "customerPublishSortMark";
//                $sortList[52] = "customerPublishSortMark";
//                $sortList[53] = "customerLimitdateSortMark";
//                $sortList[54] = "customerLimitdateSortMark";
//                $sortList[55] = "customerRenewSortMark";
//                $sortList[56] = "customerRenewSortMark";
//                $sortList[57] = "customerFeesReceKbnSortMark";
//                $sortList[58] = "customerFeesReceKbnSortMark";
//                $sortList[59] = "customerCredImpoKbnSortMark";
//                $sortList[60] = "customerCredImpoKbnSortMark";
//                $sortList[61] = "customerCustSumdaySortMark";
//                $sortList[62] = "customerCustSumdaySortMark";
//                $sortList[63] = "customerTaxCalcKbnSortMark";
//                $sortList[64] = "customerTaxCalcKbnSortMark";
//                $sortList[65] = "customerTaxFracKbnSortMark";
//                $sortList[66] = "customerTaxFracKbnSortMark";
//                $sortList[67] = "customerNote1SortMark";
//                $sortList[68] = "customerNote1SortMark";
//                $sortList[69] = "customerNote2SortMark";
//                $sortList[70] = "customerNote2SortMark";
//                $sortList[71] = "customerNote3SortMark";
//                $sortList[72] = "customerNote3SortMark";
//                $sortList[73] = "customerNote4SortMark";
//                $sortList[74] = "customerNote4SortMark";
//                $sortList[75] = "customerNote5SortMark";
//                $sortList[76] = "customerNote5SortMark";
//                $sortList[77] = "customerCustBNm1SortMark";
//                $sortList[78] = "customerCustBNm1SortMark";
//                $sortList[79] = "customerCustBNm2SortMark";
//                $sortList[80] = "customerCustBNm2SortMark";
//                $sortList[81] = "customerCustBNm3SortMark";
//                $sortList[82] = "customerCustBNm3SortMark";
//                $sortList[83] = "customerCustBNm4SortMark";
//                $sortList[84] = "customerCustBNm4SortMark";
//                $sortList[85] = "customerCustBNm5SortMark";
//                $sortList[86] = "customerCustBNm5SortMark";
//                $sortList[87] = "customerCustBNm6SortMark";
//                $sortList[88] = "customerCustBNm6SortMark";
//                $sortList[89] = "customerCustBNm7SortMark";
//                $sortList[90] = "customerCustBNm7SortMark";
//                $sortList[91] = "customerCustBNm8SortMark";
//                $sortList[92] = "customerCustBNm8SortMark";
//                $sortList[93] = "customerCustBNm9SortMark";
//                $sortList[94] = "customerCustBNm9SortMark";
//                $sortList[95] = "customerCustBNm10SortMark";
//                $sortList[96] = "customerCustBNm10SortMark";
//                $sortList[97] = "customerFstVisitDtSortMark";
//                $sortList[98] = "customerFstVisitDtSortMark";
//                $sortList[99] = "customerLstVisitDtSortMark";
//                $sortList[100] = "customerLstVisitDtSortMark";
//                $sortList[101] = "customerLstShopCdSortMark";
//                $sortList[102] = "customerLstShopCdSortMark";
//                $sortList[103] = "customerVisitCntSortMark";
//                $sortList[104] = "customerVisitCntSortMark";
//                $sortList[105] = "customerAllTotalSortMark";
//                $sortList[106] = "customerAllTotalSortMark";
//                $sortList[107] = "customerAllProfitSortMark";
//                $sortList[108] = "customerAllProfitSortMark";
//                $sortList[109] = "customerAllPointSortMark";
//                $sortList[110] = "customerAllPointSortMark";
//
//                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
//            }
//
//            $Log->trace("START changeHeaderItemMark");
//
//            return $headerArray;
//        }
//
//        /**
//         * 顧客入力画面
//         * @note     パラメータは、View側で使用
//         * @return   無
//         */
//        private function customerInputPanelDisplay()
//        {
//            global $Log, $TokenID; // グローバル変数宣言
//            $Log->trace( "START customerInputPanelDisplay" );
//
//            $customer = new Customer();
//
//            $customerDataList = array();
//            // 修正時フラグ　※登録データ更新時はtrueにする
//            $CorrectionFlag = false;
//            
//            // 登録データ更新時
//            if(!empty($_POST['custID']))
//            {
//                $customerDataList = $customer->getCustomerData( parent::escStr( $_POST['custID'] ) );
//                $CorrectionFlag = true;
//                $searchArray = array(
//                    'custTypeCd'                            => $customerDataList['cust_type_cd'],
//                    'areaCd'                                => $customerDataList['area_cd'],
//                    'organizationID'                        => $customerDataList['shop_cd'],
//                    'userNo'                                => $customerDataList['staff_cd'],
//                    'custBCd01'                             => $customerDataList['cust_b_cd1'],
//                    'custBCd02'                             => $customerDataList['cust_b_cd2'],
//                    'custBCd03'                             => $customerDataList['cust_b_cd3'],
//                    'custBCd04'                             => $customerDataList['cust_b_cd4'],
//                    'custBCd05'                             => $customerDataList['cust_b_cd5'],
//                    'custBCd06'                             => $customerDataList['cust_b_cd6'],
//                    'custBCd07'                             => $customerDataList['cust_b_cd7'],
//                    'custBCd08'                             => $customerDataList['cust_b_cd8'],
//                    'custBCd09'                             => $customerDataList['cust_b_cd9'],
//                    'custBCd10'                             => $customerDataList['cust_b_cd10'],
//                );
//            }
//            
//            // 店舗コード
//            $abbreviatedNameList        = $customer->setPulldown->getSearchOrganizationList( 'reference', true, true );
//            // 顧客分類コード
//            $customerClassificationList = $customer->setPulldown->getSearchCustomerClassificationList();
//            // 地区コード
//            $areaList                   = $customer->setPulldown->getSearchAreaList();
//            // 担当者コード
//            $userNoList                 = $customer->setPulldown->getSearchUserNoList( 'reference' );
//            // 顧客備考コード01
//            $customerRemarks01List      = $customer->setPulldown->getSearchCustomerRemarksList('01');
//            // 顧客備考コード02
//            $customerRemarks02List      = $customer->setPulldown->getSearchCustomerRemarksList('02');
//            // 顧客備考コード03
//            $customerRemarks03List      = $customer->setPulldown->getSearchCustomerRemarksList('03');
//            // 顧客備考コード04
//            $customerRemarks04List      = $customer->setPulldown->getSearchCustomerRemarksList('04');
//            // 顧客備考コード05
//            $customerRemarks05List      = $customer->setPulldown->getSearchCustomerRemarksList('05');
//            // 顧客備考コード06
//            $customerRemarks06List      = $customer->setPulldown->getSearchCustomerRemarksList('06');
//            // 顧客備考コード07
//            $customerRemarks07List      = $customer->setPulldown->getSearchCustomerRemarksList('07');
//            // 顧客備考コード08
//            $customerRemarks08List      = $customer->setPulldown->getSearchCustomerRemarksList('08');
//            // 顧客備考コード09
//            $customerRemarks09List      = $customer->setPulldown->getSearchCustomerRemarksList('09');
//            // 顧客備考コード10
//            $customerRemarks10List      = $customer->setPulldown->getSearchCustomerRemarksList('10');
//            // 商品コード
//            $profitMenuList             = $customer->setPulldown->getSearchProfitMenuList();
//
//            require_once './View/CustomerInputPanel.html';
//            $Log->trace("END customerInputPanelDisplay");
//        }
//
//        /**
//         * 顧客購買履歴画面
//         * @note     パラメータは、View側で使用
//         * @return   無
//         */
//        private function customerSalesPanelInitialDisplay()
//        {
//            global $Log, $TokenID; // グローバル変数宣言
//            $Log->trace( "START customerSalesPanelInitialDisplay" );
//
//            $customer = new customer();
//
//            $searchArray = array(
//                'is_del'                                => 0,
//                'custCd'                                => parent::escStr( $_POST['custCd'] ),
//                'denno'                                 => parent::escStr( $_POST['denno'] ),
//                'minTrndate'                            => parent::escStr( $_POST['searchMinTrndate'] ),
//                'maxTrndate'                            => parent::escStr( $_POST['searchMaxTrndate'] ),
//            );
//
//            $salesAllList = $customer->getSalesData($searchArray);
//            // 取得したレコード数
//            $salesRecordCnt = count($salesAllList);
//            // 表示レコード数
//            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
//
//            // 指定ページ
//            $pageNo = $this->getDuringTransitionPageNo($salesRecordCnt, $pagedRecordCnt);
//
//                        // シーケンスIDName
//            $idName = "hideseq";
//            // 一覧表
//            $salesList = $this->refineListDisplayNoSpecifiedPage($salesAllList, $idName, $pagedRecordCnt, $pageNo);
//            $salesList = $this->modBtnDelBtnDisabledCheck($salesList);
//
//            // 表示レコード数が11以上の場合、スクロールバーを表示
//            $isScrollBar = 'false';
//            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($salesList) >= 11)
//            {
//                $isScrollBar = 'true';
//            }
//
//            // 表示数リンクのロック処理
//            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
//            // ページ数
//            $pagedCnt = ceil($salesRecordCnt /  $pagedRecordCnt);
//            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
//
//            require_once './View/CustomerSalesPanel.html';
//            $Log->trace("END customerSalesPanelInitialDisplay");
//        }
//
//        /**
//         * 顧客購買履歴テーブル画面
//         * @note     パラメータは、View側で使用
//         * @return   無
//         */
//        private function customerSalesInitialList()
//        {
//            global $Log, $TokenID; // グローバル変数宣言
//            $Log->trace( "START customerSalesInitialList" );
//
//            $customer = new customer();
//
//            $searchArray = array(
//                'is_del'                                => 0,
//                'custCd'                                => parent::escStr( $_POST['custCd'] ),
//                'minTrndate'                            => parent::escStr( $_POST['searchMinTrndate'] ),
//                'maxTrndate'                            => parent::escStr( $_POST['searchMaxTrndate'] ),
//            );
//
//            $salesAllList = $customer->getSalesData($searchArray);
//            // 取得したレコード数
//            $salesRecordCnt = count($salesAllList);
//            // 表示レコード数
//            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
//            // ページ数
//            $pagedCnt = ceil($salesRecordCnt /  $pagedRecordCnt);
//
//            // 指定ページ
//            $pageNo = $_SESSION["PAGE_NO"];
//
//            // シーケンスIDName
//            $idName = "hideseq";
//            // 一覧表
//            $salesList = $this->refineListDisplayNoSpecifiedPage($salesAllList, $idName, $pagedRecordCnt, $pageNo);
//            $salesList = $this->modBtnDelBtnDisabledCheck($salesList);
//
//            // 表示レコード数が11以上の場合、スクロールバーを表示
//            $isScrollBar = 'false';
//            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($salesList) >= 11)
//            {
//                $isScrollBar = 'true';
//            }
//
//            // 表示数リンクのロック処理
//            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
//            
//            $searchArray = array(
//                'denno'                                 => parent::escStr( $_POST['denno'] ),
//            );
//
//            $salesDetailsAllList = array();
//            if($searchArray['denno'] !== "")
//            {
//                $salesDetailsAllList = $customer->getSalesDetailsData($searchArray);
//            }
//            
//            require_once './View/CustomerSalesPanel.html';
//            $Log->trace("END customerSalesInitialList");
//        }
//
//        /**
//         * 顧客購買履歴明細テーブル画面
//         * @note     パラメータは、View側で使用
//         * @return   無
//         */
//        private function customerSalesDetailsInitialList()
//        {
//            global $Log, $TokenID; // グローバル変数宣言
//            $Log->trace( "START customerSalesDetailsInitialList" );
//
//            require_once './View/CustomerSalesPanel.html';
//            $Log->trace("END customerSalesDetailsInitialList");
//        }
//
//        /**
//         * チェックボックスのエスケープ処理
//         * @note     チェックボックスの入力結果をINT型へ変換
//         * @return   true：1  false：0
//         */
//        private function isBoolToInt( $bool )
//        {
//            global $Log;  // グローバル変数宣言
//            $Log->trace("START isBoolToInt");
//
//            $ret = $bool === 'true' ? 1 : 0;
//
//            $Log->trace("END   isBoolToInt");
//            
//            return $ret;
//        }


        /**
         * 期間原価画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

//            $_SESSION["PAGE_NO"] = 1;
            
            // エラーメッセージ
            $aryErrMsg = array();

            $this->templateAction($aryErrMsg);
            $Log->trace("END   showAction");
        }
        

        /**
         * 期間原価画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        //public function templateAction()
        public function templateAction($aryErrMsg, $aryKeys = array())
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START templateAction");

            // 期間原価モデルインスタンス化
            $mst1401 = new Mst1401();

            // 分類種別プルダウン変更時
            if ($_GET['orgnid']){
                // from Mst1401template1.html
                $aryKeys = array();
                // 組織ID
                $aryKeys['organization_id'] = $_GET['orgnid'];
                // 仕入先コード
                $aryKeys['supp_cd'] = $_GET['cd'];
            }
            if ($_GET["search"]) {
                // from Mst1101Search.html
                $param = explode(":", $_GET['search']);
                $aryKeys = array();
                $aryKeys['organization_id'] = $param[0];
                $aryKeys['supp_cd']         = $param[1];
            }

            // 期間原価一覧データ取得
            $mst1401_data = array();
            //$data           = $mst1401->get_mst1401_data();
            $data = array();
            if (count($aryKeys) > 0){
                // 仕入先マスタ取得
                $mst1101_getdata = $mst1401->get_mst1101_data($aryKeys);

                // 期間原価マスタ取得
                $data           = $mst1401->get_mst1401_data($aryKeys);
                
                // 期間原価マスタ検索データ取得
                $mst1401_searchdata = $mst1401->searchMst1401Data($aryKeys['organization_id']);
            }
            if (count($data) > 0){
                for ($intL = 0; $intL < count($data); $intL ++){
                    // 発注期間開始
                    $data[$intL]['order_date_str'] = substr($data[$intL]['order_date_str'],0,4).'/'.substr($data[$intL]['order_date_str'],4,2).'/'.substr($data[$intL]['order_date_str'],6,2);
                    // 発注期間終了
                    $data[$intL]['order_date_end'] = substr($data[$intL]['order_date_end'],0,4).'/'.substr($data[$intL]['order_date_end'],4,2).'/'.substr($data[$intL]['order_date_end'],6,2);
                }
            }

            $title          = array("期間原価コード","商品コード","商品名","発注期間開始","発注期間終了","期間原価","発注ロット","商品コードBAK");
            $key            = array("peri_cost_cd","prod_cd","prod_nm","order_date_str","order_date_end","peri_costprice","order_lot","prod_cd_bak");
            $pr_key_id      = array("peri_cost_cd","prod_cd");
            $pr_key_list    = "peri_cost_cd,prod_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Product';
            $pagetitle      = '商品管理　期間原価マスタ';
            $destination    = 'Mst1401';

            // 商品一覧データ取得
            //$mst0201List        = $mst1401->getMst0201List();
            //$mst0201_searchdata = $mst1401->searchMst0201Data();
            $mst0201_searchdata = $mst1401->searchMst0201Data($aryKeys["organization_id"]);

            // 店舗コードプルダウン
            $abbreviatedNameList = $mst1401->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            // 仕入先コードリスト取得
            $mst1101List        = $mst1401->getmst1101List();
            
            //require_once '../pagetemplate1/View/template1.html';
            require_once '../product/View/Mst1401template1.html';

            $Log->trace("END   templateAction");
        }

        /**
         * 期間原価更新処理
         * @return    なし
         */
        public function changeinputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");

            // 期間原価モデルインスタンス化
            $mst1401 = new Mst1401();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $aryKeys = array();
            // 組織ID
            $aryKeys['organization_id']  = $_POST['organization_id'];
            $aryKeys['supp_cd']          = $_POST['supp_cd'];

//            // 削除
//            if (isset($_POST['del_data']) === true){
//                $del_data = json_decode($_POST['del_data'], true);
//                //$mst1401->Delete($del_data);
//                $mst1401->Delete($organization_id, $del_data, $aryErrMsg);
//            }
//            // 新規
//            if (isset($_POST['new_data']) === true){
//                $new_data = json_decode($_POST['new_data'], true);
//                //$mst1401->Insert($new_data);
//                $mst1401->Insert($organization_id, $new_data, $aryErrMsg);
//            }
//            // 更新
//            if (isset($_POST['upd_data']) === true){
//                $upd_data = json_decode($_POST['upd_data'], true);
//                //$mst1401->Update($upd_data);
//                $mst1401->Update($organization_id, $upd_data, $aryErrMsg);
//            }
            // 登録・更新・削除一括処理
            $mst1401->BatchRegist($aryKeys, $_POST, $aryErrMsg);
            
            //$this->templateAction();
            $this->templateAction($aryErrMsg, $aryKeys);
            $Log->trace("END   changeinputAction");
        }
        
        /**
         * 構成商品コード検索画面表示
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");

            // 商品バンドルモデルインスタンス化
            $mst1401 = new Mst1401();

            // 店舗コードプルダウン
            $abbreviatedNameList        = $mst1401->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            // 検索種別
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            if ($type === 's'){
                // 仕入先情報一覧データ取得
                //$mst1401List        = $mst1401->getMst1401List();
                $mst1101_searchdata = $mst1401->searchMst1101Data();
                for ($intL = 0; $intL < Count($mst1101_searchdata); $intL ++) {
                    $mst1101_searchdata[$intL]['abbreviated_name'] = '';
                    foreach ($abbreviatedNameList as $abbreviatedName) {
                        if ($mst1101_searchdata[$intL]['organization_id'] === $abbreviatedName['organization_id']) {
                            $abbreviated_name = $abbreviatedName['abbreviated_name'];
                            // 階層を示すための先頭の｜├└全角スペースを除去
                            $abbreviated_name = ltrim($abbreviated_name, '｜');
                            $abbreviated_name = ltrim($abbreviated_name, '├');
                            $abbreviated_name = ltrim($abbreviated_name, '└');
                            $abbreviated_name = ltrim($abbreviated_name, '　');
                            $mst1101_searchdata[$intL]['abbreviated_name'] = $abbreviated_name;
                            break;
                        }
                    }
                }
                // ダイアログで開かれるかどうかのフラグ
                $dlg_flg = '1';
                $destination = 'Mst1401';
                require_once './View/Mst1101Search.html';
            }
            else if ($type === 'p'){
                // 商品情報一覧データ取得
                $line = isset($_GET['line']) ? $_GET['line'] : '';

                // 行の[検索]のボタンクリック時の組織ID
                $orgn_id = isset($_GET["orgnid"]) ? $_GET["orgnid"] : "";

                //$mst0201List        = $mst1401->getMst0201List();
                $mst0201_searchdata = $mst1401->searchMst0201Data($orgn_id);
                for ($intL = 0; $intL < Count($mst0201_searchdata); $intL ++) {
                    $mst0201_searchdata[$intL]['abbreviated_name'] = '';
                    foreach ($abbreviatedNameList as $abbreviatedName) {
                        if ($mst0201_searchdata[$intL]['organization_id'] === $abbreviatedName['organization_id']) {
                            $abbreviated_name = $abbreviatedName['abbreviated_name'];
                            // 階層を示すための先頭の｜├└全角スペースを除去
                            $abbreviated_name = ltrim($abbreviated_name, '｜');
                            $abbreviated_name = ltrim($abbreviated_name, '├');
                            $abbreviated_name = ltrim($abbreviated_name, '└');
                            $abbreviated_name = ltrim($abbreviated_name, '　');
                            $mst0201_searchdata[$intL]['abbreviated_name'] = $abbreviated_name;
                            break;
                        }
                    }
                }

                // ダイアログで開かれるかどうかのフラグ
                $dlg_flg = '1';

                $destination = 'Mst1401';
                
                //require_once './View/Mst5102Search.html';
                require_once './View/Mst0201Search.html';
            }
            else{
                // 不正な検索種別
                
            }
            
            $Log->trace("END searchAction");
        }

        public function alldeleteAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START alldeleteAction");

            // 商品バンドルモデルインスタンス化
            $mst1401 = new Mst1401();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $aryKeys = array();
            // 組織ID
            $aryKeys['organization_id']  = $_GET['orgnid'];
            $aryKeys['supp_cd']          = $_GET['cd'];
            $aryKeys['order_date_str']   = isset($_GET['sdt']) ? $_GET['sdt'] : '';
            $aryKeys['order_date_end']   = isset($_GET['edt']) ? $_GET['edt'] : '';

            // 削除
            if ($mst1401->AllDelete($aryKeys, $aryErrMsg) === true){
                // 初期化
                unset($_GET);
                $aryKeys = array();
            }
            
            //$this->templateAction();
            $this->templateAction($aryErrMsg, $aryKeys);
            $Log->trace("END   alldeleteAction");
        }
    }
