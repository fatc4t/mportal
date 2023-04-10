<?php
    /**
     * @file      特売マスタコントローラ
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      特売マスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst1301.php';
    /**
     * 商品バンドルコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst1301Controller extends BaseController
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

            // 特売モデルインスタンス化
            $mst1301 = new Mst1301();

            // 特売企画コード入力時
            if ($_GET['orgnid']){
                // from Mst1301template1.html
                $aryKeys = array();
                // 組織ID
                $aryKeys['organization_id'] = $_GET['orgnid'];
                // 特売企画コード
                $aryKeys['sale_plan_cd'] = $_GET['cd'];
            }
            if ($_GET["search"]) {
                // from Mst1301Search.html
                $param = explode(":", $_GET['search']);
                $aryKeys = array();
                $aryKeys['organization_id'] = $param[0];
                $aryKeys['sale_plan_cd']    = $param[1];
            }

            // 特売一覧データ取得
            $mst1301_data = array();
            //$data           = $mst1301->get_mst1301_data();
            $data = array();
            if (count($aryKeys) > 0){
                // MST1301とMST1303を結合した形で取得
                $data           = $mst1301->get_mst1301_data($aryKeys);
            }
            if (count($data) > 0){
                $aryKeys['sale_plan_nm']     = $data[0]['sale_plan_nm'];
                $aryKeys['sale_plan_str_dt'] = substr($data[0]['sale_plan_str_dt'],0,4).'/'.substr($data[0]['sale_plan_str_dt'],4,2).'/'.substr($data[0]['sale_plan_str_dt'],6,2);
                $aryKeys['sale_plan_end_dt'] = substr($data[0]['sale_plan_end_dt'],0,4).'/'.substr($data[0]['sale_plan_end_dt'],4,2).'/'.substr($data[0]['sale_plan_end_dt'],6,2);
                for ($intL = 0; $intL < count($data); $intL ++){
                    if ($data[$intL]['sale_str_dt1'] !== ''){
                        $data[$intL]['sale_str_dt1'] = substr($data[$intL]['sale_str_dt1'],0,4).'/'.substr($data[$intL]['sale_str_dt1'],4,2).'/'.substr($data[$intL]['sale_str_dt1'],6,2);
                    }
                    if ($data[$intL]['sale_end_dt1'] !== ''){
                        $data[$intL]['sale_end_dt1'] = substr($data[$intL]['sale_end_dt1'],0,4).'/'.substr($data[$intL]['sale_end_dt1'],4,2).'/'.substr($data[$intL]['sale_end_dt1'],6,2);
                    }
                    if ($data[$intL]['sale_str_dt2'] !== ''){
                        $data[$intL]['sale_str_dt2'] = substr($data[$intL]['sale_str_dt2'],0,4).'/'.substr($data[$intL]['sale_str_dt2'],4,2).'/'.substr($data[$intL]['sale_str_dt2'],6,2);
                    }
                    if ($data[$intL]['sale_end_dt2'] !== ''){
                        $data[$intL]['sale_end_dt2'] = substr($data[$intL]['sale_end_dt2'],0,4).'/'.substr($data[$intL]['sale_end_dt2'],4,2).'/'.substr($data[$intL]['sale_end_dt2'],6,2);
                    }
                }
            }

            // 原価1 ～ 更新キー は画面非表示項目
            $title          = array("商品コード","商品名","販売開始日1","販売終了日1","販売開始日2","販売終了日2","商品容量","定番原価","定番売価","原価1","売価1","会員売価1","得点区分1","原価2","売価2","会員売価2","得点区分2");
            $key            = array("prod_cd","prod_nm","sale_str_dt1","sale_end_dt1","sale_str_dt2","sale_end_dt2","prod_capa_nm","costprice","saleprice","costprice1","saleprice1","cust_saleprice1","point_kbn1","costprice2","saleprice2","cust_saleprice2","point_kbn2");
            $pr_key_id      = array("prod_cd");
            $pr_key_list    = "prod_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Product';
            $pagetitle      = '商品管理★特売マスタ';
            $destination    = 'Mst1301';

            // 店舗コードプルダウン
            $abbreviatedNameList = $mst1301->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            // 商品一覧データ取得
            //$mst0201List        = $mst5301->getMst0201List();
            //$mst0201_searchdata = $mst1301->searchMst0201Data($aryKeys['organization_id']);

            //require_once '../pagetemplate1/View/template1.html';
            require_once '../product/View/Mst1301template1.html';

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
            $mst1301 = new Mst1301();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $aryKeys = array();
            // 組織ID
            $aryKeys['organization_id']  = $_POST['organization_id'];
            $aryKeys['sale_plan_cd']     = $_POST['sale_plan_cd'];
            $aryKeys['sale_plan_nm']     = $_POST['sale_plan_nm'];
            $aryKeys['sale_plan_str_dt'] = str_replace('/', '', $_POST['sale_plan_str_dt']);
            $aryKeys['sale_plan_end_dt'] = str_replace('/', '', $_POST['sale_plan_end_dt']);

            // 登録・更新・削除一括処理
            $mst1301->BatchRegist($aryKeys, $_POST, $aryErrMsg);
            
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
            $mst1301 = new Mst1301();

            // 店舗コードプルダウン
            $abbreviatedNameList        = $mst1301->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            // 検索種別
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            if ($type === 's'){
                // 特売情報一覧データ取得
                //$mst1301List        = $mst1301->getMst1301List();
                $mst1301_searchdata = $mst1301->searchMst1301Data();
                for ($intL = 0; $intL < Count($mst1301_searchdata); $intL ++) {
                    $mst1301_searchdata[$intL]['abbreviated_name'] = '';
                    foreach ($abbreviatedNameList as $abbreviatedName) {
                        if ($mst1301_searchdata[$intL]['organization_id'] === $abbreviatedName['organization_id']) {
                            $abbreviated_name = $abbreviatedName['abbreviated_name'];
                            // 階層を示すための先頭の｜├└全角スペースを除去
                            $abbreviated_name = ltrim($abbreviated_name, '｜');
                            $abbreviated_name = ltrim($abbreviated_name, '├');
                            $abbreviated_name = ltrim($abbreviated_name, '└');
                            $abbreviated_name = ltrim($abbreviated_name, '　');
                            $mst1301_searchdata[$intL]['abbreviated_name'] = $abbreviated_name;
                            break;
                        }
                    }
                    // 有効年月日
                    $mst1301_searchdata[$intL]["sale_plan_str_dt"] = substr($mst1301_searchdata[$intL]["sale_plan_str_dt"],0,4).'/'.substr($mst1301_searchdata[$intL]["sale_plan_str_dt"],4,2).'/'.substr($mst1301_searchdata[$intL]["sale_plan_str_dt"],6,2);
                    $mst1301_searchdata[$intL]["sale_plan_end_dt"] = substr($mst1301_searchdata[$intL]["sale_plan_end_dt"],0,4).'/'.substr($mst1301_searchdata[$intL]["sale_plan_end_dt"],4,2).'/'.substr($mst1301_searchdata[$intL]["sale_plan_end_dt"],6,2);
                }
                
                // ダイアログで開かれるかどうかのフラグ
                $dlg_flg = '1';
                $destination = 'Mst1301';
                require_once './View/Mst1301Search.html';
            }
            else if ($type === 'p'){
                // 商品情報一覧データ取得
                $line = isset($_GET['line']) ? $_GET['line'] : '';

                // 行の[検索]のボタンクリック時の組織ID
                $orgn_id = isset($_GET["orgnid"]) ? $_GET["orgnid"] : "";

                //$mst0201List        = $mst1301->getMst0201List();
                //$mst0201_searchdata = $mst1301->searchMst0201Data();
                $mst0201_searchdata = $mst1301->searchMst0201Data($orgn_id);
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

                $destination = 'Mst1301';
                
                //require_once './View/Mst5102Search.html';
                require_once './View/Mst0201Search.html';
            }
            else{
                // 不正な検索種別
                
            }
            
            $Log->trace("END searchAction");
        }

        /**
         * 全削除
         * @return    なし
         */
        public function alldeleteAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START alldeleteAction");

            // 商品バンドルモデルインスタンス化
            $mst1301 = new Mst1301();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $aryKeys = array();
            // 組織ID
            $aryKeys['organization_id']  = $_GET['orgnid'];
            $aryKeys['sale_plan_cd']     = $_GET['cd'];

            // 削除
            if ($mst1301->AllDelete($aryKeys, $aryErrMsg) === true){
                // 初期化
                unset($_GET);
                $aryKeys = array();
            }
            
            //$this->templateAction();
            $this->templateAction($aryErrMsg, $aryKeys);
            $Log->trace("END   alldeleteAction");
        }

        /**
         * 特売明細画面表示
         * @return    なし
         */        
        public function detailAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START detailAction");
            
            // 特売モデルインスタンス化
            $mst1301 = new Mst1301();

            $mst1301_getdata = array();
            $mst1301_getdata['organization_id']     = $_GET['orgnid'];
            $mst1301_getdata['sale_plan_cd']        = $_GET['cd'];
            $mst1301_getdata['sale_plan_nm']        = urldecode($_GET['nm']);
            $mst1301_getdata['sale_plan_str_dt']    = $_GET['sdt'];
            $mst1301_getdata['sale_plan_end_dt']    = $_GET['edt'];
            // 選択行データあり
            if (isset($_GET['line']) === true ){
                $line = $_GET['line'];
                $row = json_decode(urldecode($_GET['row']));
                // Exclusion List Array (because these are header items.)
                $aryExcl = array('organization_id', 'sale_plan_cd', 'sale_plan_nm', 'sale_plan_str_dt', 'sale_plan_end_dt');
                foreach ($row as $key => $val){
                    if (in_array($key, $aryExcl, true) === true){
                        continue;
                    }
                    $mst1301_getdata[$key] = $val;
                }
            }
            else{
                $line = '';
            }

            // 商品マスタ検索用データ
            $mst0201_alldata    = $mst1301->searchMst0201Data($mst1301_getdata['organization_id']);

            require_once '../product/View/Mst1301Detail.html';

            $Log->trace("END   detailAction");
        }
    }
