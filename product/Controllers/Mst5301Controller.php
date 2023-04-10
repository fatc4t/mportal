<?php
    /**
     * @file      商品バンドルコントローラ
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品バンドルの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst5301.php';
    /**
     * 商品バンドルコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst5301Controller extends BaseController
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
         * 商品部門画面初期表示
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
         * 商品バンドル画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        //public function templateAction()
        public function templateAction($aryErrMsg, $aryKeys = array())
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START templateAction");

            // 商品バンドルモデルインスタンス化
            $mst5301 = new Mst5301();

            // 分類種別プルダウン変更時
            if ($_GET['orgnid']){
                // from Mst5301template1.html
                $aryKeys = array();
                // 組織ID
                $aryKeys['organization_id'] = $_GET['orgnid'];
                // 構成商品コード
                $aryKeys['bundle_cd'] = $_GET['cd'];
            }
            if ($_GET["search"]) {
                // from Mst5301Search.html
                $param = explode(":", $_GET['search']);
                $aryKeys = array();
                $aryKeys['organization_id'] = $param[0];
                $aryKeys['bundle_cd']       = $param[1];                
            }

            // 商品バンドル一覧データ取得
            $mst5301_data = array();
            //$data           = $mst5301->get_mst5301_data();
            $data = array();
            if (count($aryKeys) > 0){
                // MST5301とMST5302を結合した形で取得
                $data           = $mst5301->get_mst5301_data($aryKeys);
            }
            if (count($data) > 0){
                $aryKeys['expira_str'] = substr($data[0]['expira_str'],0,4).'/'.substr($data[0]['expira_str'],4,2).'/'.substr($data[0]['expira_str'],6,2);
                $aryKeys['expira_end'] = substr($data[0]['expira_end'],0,4).'/'.substr($data[0]['expira_end'],4,2).'/'.substr($data[0]['expira_end'],6,2);
                $aryKeys['sale_price'] = $data[0]['sale_price'];
                for ($intL = 0; $intL < count($data); $intL ++){
                    // 売価合計
                    $saleprice  = str_replace(',', '', $data[$intL]['saleprice']);
                    $amount     = str_replace(',', '', $data[$intL]['constitu_amount']);
                    $data[$intL]['saleprice_all'] = number_format($saleprice * $amount);
                }
            }

            $title          = array("商品コード","商品名","容量","商品売価","構成数","売価合計");
            $key            = array("prod_cd","prod_nm","prod_capa_nm","saleprice","constitu_amount","saleprice_all");
            $pr_key_id      = array("prod_cd");
            $pr_key_list    = "prod_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Product';
            $pagetitle      = '商品管理　バンドルマスタ';
            $destination    = 'Mst5301';

            // 商品一覧データ取得
            //$mst0201List        = $mst5301->getMst0201List();
            $mst0201_searchdata = $mst5301->searchMst0201Data();

            // バンドル一覧データ取得
            //$mst5301List        = $mst5301->getMst5301List();
            $mst5301_searchdata = $mst5301->searchMst5301Data();
            
            // ミックスマッチ一覧データ取得
            $mst5201_searchdata = $mst5301->searchMst5201Data();

            // 店舗コードプルダウン
            $abbreviatedNameList = $mst5301->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            //require_once '../pagetemplate1/View/template1.html';
            require_once '../product/View/Mst5301template1.html';

            $Log->trace("END   templateAction");
        }

        /**
         * 商品バンドル更新処理
         * @return    なし
         */
        public function changeinputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");

            // 商品バンドルモデルインスタンス化
            $mst5301 = new Mst5301();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $aryKeys = array();
            // 組織ID
            $aryKeys['organization_id']  = $_POST['organization_id'];
            $aryKeys['bundle_cd']        = $_POST['bundle_cd'];
            $aryKeys['expira_str']       = str_replace('/', '', $_POST['expira_str']);
            $aryKeys['expira_end']       = str_replace('/', '', $_POST['expira_end']);
            $aryKeys['sale_price']       = str_replace(',', '', $_POST['sale_price']);
            if ($aryKeys['sale_price'] === ""){
                $aryKeys['sale_price'] = '0';
            }

            // 登録・更新・削除一括処理
            $mst5301->BatchRegist($aryKeys, $_POST, $aryErrMsg);
            
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
            $mst5301 = new Mst5301();

            // 店舗コードプルダウン
            $abbreviatedNameList        = $mst5301->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            // 検索種別
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            if ($type === 'b'){
                // バンドル情報一覧データ取得
                //$mst5301List        = $mst5301->getMst5301List();
                $mst5301_searchdata = $mst5301->searchMst5301Data();
                for ($intL = 0; $intL < Count($mst5301_searchdata); $intL ++) {
                    $mst5301_searchdata[$intL]['abbreviated_name'] = '';
                    foreach ($abbreviatedNameList as $abbreviatedName) {
                        if ($mst5301_searchdata[$intL]['organization_id'] === $abbreviatedName['organization_id']) {
                            $abbreviated_name = $abbreviatedName['abbreviated_name'];
                            // 階層を示すための先頭の｜├└全角スペースを除去
                            $abbreviated_name = ltrim($abbreviated_name, '｜');
                            $abbreviated_name = ltrim($abbreviated_name, '├');
                            $abbreviated_name = ltrim($abbreviated_name, '└');
                            $abbreviated_name = ltrim($abbreviated_name, '　');
                            $mst5301_searchdata[$intL]['abbreviated_name'] = $abbreviated_name;
                            break;
                        }
                    }
                    // 有効年月日
                    $mst5301_searchdata[$intL]["expira_str"] = substr($mst5301_searchdata[$intL]["expira_str"],0,4).'/'.substr($mst5301_searchdata[$intL]["expira_str"],4,2).'/'.substr($mst5301_searchdata[$intL]["expira_str"],6,2);
                    $mst5301_searchdata[$intL]["expira_end"] = substr($mst5301_searchdata[$intL]["expira_end"],0,4).'/'.substr($mst5301_searchdata[$intL]["expira_end"],4,2).'/'.substr($mst5301_searchdata[$intL]["expira_end"],6,2);
                }

                // 商品一覧データ取得
                $mst0201_searchdata = $mst5301->searchMst0201Data();

                $destination = 'Mst5301';
                require_once './View/Mst5301Search.html';
            }
            else if ($type === 'p'){
                // 商品情報一覧データ取得
                $line = isset($_GET['line']) ? $_GET['line'] : '';

                //$mst0201List        = $mst5301->getMst0201List();
                $mst0201_searchdata = $mst5301->searchMst0201Data();
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

                // 行の[検索]のボタンクリック時の組織ID
                $orgn_id = isset($_GET["orgnid"]) ? $_GET["orgnid"] : "";

                // ダイアログで開かれるかどうかのフラグ
                $dlg_flg = '1';

                $destination = 'Mst5301';
                
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
            $mst5301 = new Mst5301();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $aryKeys = array();
            // 組織ID
            //$aryKeys['organization_id']  = $_GET['orgnid'];
            $aryKeys['bundle_cd']        = $_GET['cd'];

            // 削除
            if ($mst5301->AllDelete($aryKeys, $aryErrMsg) === true){
                // 初期化
                unset($_GET);
                $aryKeys = array();
            }
            
            //$this->templateAction();
            $this->templateAction($aryErrMsg, $aryKeys);
            $Log->trace("END   alldeleteAction");
        }
    }
