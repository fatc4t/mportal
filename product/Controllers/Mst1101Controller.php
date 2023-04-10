<?php
    /**
     * @file      仕入先コントローラ
     * @author    川橋
     * @date      2019/01/17
     * @version   1.00
     * @note      仕入先の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst1101.php';
    /**
     * 仕入先コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst1101Controller extends BaseController
    {
        //private $ssupp_cd = "";
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
         * 仕入先画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $_SESSION["PAGE_NO"] = 1;
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 仕入先登録処理
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addInputAction");
            
            $mst1101 = new Mst1101();
            
            //print_r($_POST);
            if ($_POST["updins"] == "ins" ) {
                $mst1101->insertdata();
            }
            else{
                $mst1101->updatedata();
            }
            
            $this->initialDisplay();
            $Log->trace("END   addInputAction");
        }        

        /**
         * 商品部門削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delAction");
            $mst1101 = new Mst1101();

            //print_r($_GET);
//            if ($_GET["suppcd"]) {
//                $mst1101->deldata($_GET["suppcd"]);            
//                $_GET["suppcd"]="";
//                $this->initialDisplay();
//                $Log->trace("END   delAction");
//            }

            if ($_GET["orgnid"] && $_GET["suppcd"]) {
                $mst1101->deldata($_GET["orgnid"], $_GET["suppcd"]);            
                $_GET["orgnid"]="";
                $_GET["suppcd"]="";
                $this->initialDisplay();
                $Log->trace("END   delAction");
            }
            $Log->trace("END delAction");
        }
        
        /**
         * 仕入先検索画面表示
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");

            // 仕入先モデルインスタンス化
            $mst1101 = new Mst1101();

            // 店舗コードプルダウン
            $abbreviatedNameList        = $mst1101->setPulldown->getSearchOrganizationList( 'reference', true, true );
        //  $pageMaxNo = $mst1101->pageMaxNo();
            // 仕入先一覧データ取得
           // $pageMaxNo = $mst1101->count();
            $mst1101List        = $mst1101->getmst1101List();
            $mst1101_searchdata = $mst1101->searchmst1101Data();
         
            
                         //  $pagedRecordCnt 
          //   $pageNo = 1;
         //  $pageNo = $_POST['pageNo'];
//             if($pageNo === 0 || $pageNo === ''){
//                $pageNo =  $this->getDuringTransitionPageNo($pagedRecordCnt);
//             } 
//             $pagedRecordCnt = 0;
//            
//            if($pagedRecordCnt === 0 || $pagedRecordCnt === ''){
//              //  $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
//                 $pagedRecordCnt = 30;
//
//            }
//            //MAXページを計算する
////            $pageMaxNo=0;
//             
//           
//            
////            $pageMaxNo=$pageMaxNo[0]["rownos"] / $pagedRecordCnt;
//            if($pageMaxNo[0]["rownos"] > 0){
//                $round_up = $pageMaxNo[0]["rownos"] / $pagedRecordCnt;
//                $pageMaxNo = ceil(round($round_up,2,PHP_ROUND_HALF_UP));
//                //$pageMaxNo+1;
//            }
//            
            
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

            $destination = 'Mst1101';
            require_once './View/Mst1101Search.html';
            $Log->trace("END searchAction");
        } 

        /**
         * 仕入先画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            $mst1101_getdata    = '';
            $mst1101_sd         = '';
            $mst1101List        = '';

            $mst6301            = '';   // 曜日別仕入先発注マスタ
            $jsk5200            = '';   // 仕入先月別実績データ
            
            $iorgn_id           = '';
            $ssupp_cd           = '';

            // 商品部門モデルインスタンス化
            $mst1101 = new Mst1101();
            // 商品部門一覧データ取得
            $mst1101List        = $mst1101->getmst1101List();
            $mst1101_searchdata = $mst1101->searchmst1101Data();
//print_r($mst1101_searchdata);
            if ($_GET["orgnid"]) {
                $iorgn_id = $_GET["orgnid"];
            }
            if ($_POST["ORGANIZATION_ID"]) {
                $iorgn_id = $_POST["ORGANIZATION_ID"];
            }

            //if ($_GET["suppcd"]) {
            //    $ssupp_cd = $_GET["suppcd"];
            //}
            if ($_GET["suppcd"]) {
                $ssupp_cd = $_GET["suppcd"];
            }
            if ($_POST["SUPP_CD"]) {
                $ssupp_cd = $_POST["SUPP_CD"];
            }
            if ($_GET["search"]) {
                $ssupp_cd = $_GET["search"];
            }            

            // organization_id:supp_cd 形式の値の場合は分割
            if (strpos($ssupp_cd, ':') > 0) {
                $param = explode(":", $ssupp_cd);
                $iorgn_id = $param[0];
                $ssupp_cd = $param[1];                
            } 
            //print_r($iorgn_id);
            //print_r($ssupp_cd);
            // 一覧表用検索項目
//            if ($ssupp_cd != "" ){
//                    $mst1101_getdata = $mst1101->getmst1101Data($ssupp_cd);
//            }
            if ($iorgn_id != "" && $ssupp_cd != "" ){
                    $mst1101_getdata = $mst1101->getmst1101Data($iorgn_id, $ssupp_cd);
            }
            else if ($iorgn_id != ""){
                // 登録画面で組織プルダウン変更時
                $mst1101_getdata = array(
                    'organization_id'   => $iorgn_id,
                    'insdatetime'       => date('Y-m-d H:i:s'),
                    'upddatetime'       => date('Y-m-d H:i:s'));
            }

            // 店舗コードプルダウン
            $abbreviatedNameList        = $mst1101->setPulldown->getSearchOrganizationList( 'reference', true, true );

            // システムマスタ取得
            $mst0010_getdata = $mst1101->getMst0010Data($iorgn_id);
            // 部門コード入力長
            if (intval($mst0010_getdata[0]['suppsize']) === 0) {
                $mst0010_getdata[0]['suppsize'] = '4';
            }
            
            // 曜日別仕入先発注マスタ取得
            $mst6301_getdata = $mst1101->getMst6301Data($iorgn_id, $ssupp_cd);

            // 仕入先月別実績データ取得
            $jsk5200_getdata = $mst1101->getJsk5200Data($iorgn_id, $ssupp_cd);
            

            require_once './View/Mst1101Panel.html';

            $Log->trace("END   initialDisplay");
        }

    }
