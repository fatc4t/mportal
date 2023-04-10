<?php
    /**
     * @file      商品部門コントローラ
     * @author    川橋
     * @date      2019/01/15
     * @version   1.00
     * @note      商品部門の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst1201.php';
    /**
     * 商品部門コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst1201Controller extends BaseController
    {
        //private $ssect_cd = "";
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
         * 商品部門画面初期表示
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
         * 商品部門登録処理
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addInputAction");
            
            $mst1201 = new Mst1201();
            
            //print_r($_POST);
            if ($_POST["updins"] == "ins" ) {
                echo 'ins';
                $mst1201->insertdata();
            }
            else{
                echo 'upd';
                $mst1201->updatedata();
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
            $mst1201 = new Mst1201();

            //print_r($_GET);
            if ($_GET["orgnid"] && $_GET["sectcd"]) {
                $mst1201->deldata($_GET["orgnid"], $_GET["sectcd"]);            
                $_GET["orgnid"]="";
                $_GET["sectcd"]="";
                $this->initialDisplay();
                $Log->trace("END   delAction");
            }
            $Log->trace("END delAction");
        }
        
        /**
         * 商品部門検索画面表示
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");

            // 商品部門モデルインスタンス化
            $mst1201 = new Mst1201();

            // 店舗コードプルダウン
            $abbreviatedNameList        = $mst1201->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            // 商品部門一覧データ取得
            $mst1201List        = $mst1201->getMst1201List();
            $mst1201_searchdata = $mst1201->searchMst1201Data();
            for ($intL = 0; $intL < Count($mst1201_searchdata); $intL ++) {
                $mst1201_searchdata[$intL]['abbreviated_name'] = '';
                foreach ($abbreviatedNameList as $abbreviatedName) {
                    if ($mst1201_searchdata[$intL]['organization_id'] === $abbreviatedName['organization_id']) {
                        $abbreviated_name = $abbreviatedName['abbreviated_name'];
                        // 階層を示すための先頭の｜├└全角スペースを除去
                        $abbreviated_name = ltrim($abbreviated_name, '｜');
                        $abbreviated_name = ltrim($abbreviated_name, '├');
                        $abbreviated_name = ltrim($abbreviated_name, '└');
                        $abbreviated_name = ltrim($abbreviated_name, '　');
                        $mst1201_searchdata[$intL]['abbreviated_name'] = $abbreviated_name;
                        break;
                    }
                }
            }

            require_once './View/Mst1201Search.html';
            $Log->trace("END searchAction");
        } 

        /**
         * 商品部門画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            $mst1201_getdata    = '';
            $mst1201_sd         = '';
            $mst1201List        = '';
            $mst1205_alldata    = '';
            $mst0201_alldata    = '';

            $iorgn_id           = '';
            $ssect_cd           = '';

            // 商品部門モデルインスタンス化
            $mst1201 = new Mst1201();
            // 商品部門一覧データ取得
            $mst1201List        = $mst1201->getMst1201List();
            $mst1201_searchdata = $mst1201->searchMst1201Data();
//print_r($mst1201_searchdata);
            if ($_GET["orgnid"]) {
                $iorgn_id = $_GET["orgnid"];
            }
            if ($_POST["ORGANIZATION_ID"]) {
                $iorgn_id = $_POST["ORGANIZATION_ID"];
            }

            if ($_GET["sectcd"]) {
                $ssect_cd = $_GET["sectcd"];
            }
            if ($_POST["SECT_CD"]) {
                $ssect_cd = $_POST["SECT_CD"];
            }

            // organization_id:sect_cd 形式の値の場合は分割
            if (strpos($ssect_cd, ':') > 0) {
                $param = explode(":", $ssect_cd);
                $iorgn_id = $param[0];
                $ssect_cd = $param[1];                
            } 
            //print_r($iorgn_id);
            //print_r($ssect_cd);
            if ($iorgn_id != "" && $ssect_cd != "" ){
                    $mst1201_getdata = $mst1201->getMst1201Data($iorgn_id, $ssect_cd);
            }
            else if ($iorgn_id != ""){
                // 登録画面で組織プルダウン変更時
                $mst1201_getdata = array(
                    'organization_id'   => $iorgn_id);
                    //'insdatetime'       => date('Y-m-d H:i:s'),
                    //'upddatetime'       => date('Y-m-d H:i:s'));
            }

            // 店舗コードプルダウン
            $abbreviatedNameList        = $mst1201->setPulldown->getSearchOrganizationList( 'reference', true, true );

            // 部門分類一覧データ取得
            $mst1205_alldata = $mst1201->getMst1205Data($iorgn_id);
            
            // 商品一覧データ取得
            $mst0201_alldata = $mst1201->getMst0201Data($iorgn_id);
            
            // システムマスタ取得
            $mst0010_getdata = $mst1201->getMst0010Data($iorgn_id);
            // 部門コード入力長
            if (intval($mst0010_getdata[0]['sectsize']) === 0) {
                $mst0010_getdata[0]['sectsize'] = '5';
            }
           
            require_once './View/Mst1201Panel.html';

            $Log->trace("END   initialDisplay");
        }

    }
