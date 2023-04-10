<?php
    /**
     * @file      商品分類コントローラ
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品分類の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst0801.php';
    /**
     * 商品分類コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst0801Controller extends BaseController
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
         * 商品分類画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        //public function templateAction()
        public function templateAction($aryErrMsg, $cmb_prod_t = array())
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START templateAction");

            // 商品分類モデルインスタンス化
            $mst0801 = new Mst0801();

            // 分類種別プルダウン変更時
            if ($_GET['orgnid']){
                $cmb_prod_t = array();
                // 組織ID
                $cmb_prod_t['organization_id'] = $_GET['orgnid'];
                // 分類種別
                $cmb_prod_t['prod_t_type'] = $_GET['type'];
                // 大分類
                $cmb_prod_t['prod_t_cd1'] = isset($_GET['cd1']) ? $_GET['cd1'] : '';
                // 中分類
                $cmb_prod_t['prod_t_cd2'] = isset($_GET['cd2']) ? $_GET['cd2'] : '';
                // 小分類
                $cmb_prod_t['prod_t_cd3'] = isset($_GET['cd3']) ? $_GET['cd3'] : '';
            }

            // 商品分類一覧データ取得
            //$data           = $mst0801->get_mst0801_data();
            $data = array();
            if (count($cmb_prod_t) > 0){
                $data           = $mst0801->get_mst0801_data($cmb_prod_t);
            }
            $title          = array("コード","商品分類名","商品分類名カナ");
            $key            = array("prod_t_cd","prod_t_nm","prod_t_kn");
            $pr_key_id      = array("prod_t_cd");
            $pr_key_list    = "prod_t_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Product';
            $pagetitle      = '商品管理★商品分類マスタ';
            $destination    = 'Mst0801';

            // 店舗コードプルダウン
            $abbreviatedNameList = $mst0801->setPulldown->getSearchOrganizationList( 'reference', true, true );

            // 商品分類データ（プルダウン用）
            $alldata = $mst0801->get_mst0801_alldata();
            
            //require_once '../pagetemplate1/View/template1.html';
            require_once '../product/View/Mst0801template1.html';

            $Log->trace("END   templateAction");
        }

        /**
         * 商品分類更新処理
         * @return    なし
         */
        public function changeinputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");

            // 商品分類モデルインスタンス化
            $mst0801 = new Mst0801();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $cmb_prod_t = array();
            // 組織ID
            $cmb_prod_t['organization_id']  = $_POST['organization_id'];
            $cmb_prod_t['prod_t_type']      = $_POST['prod_t_type'];
            $cmb_prod_t['prod_t_cd1']       = $_POST['prod_t_cd1'];
            $cmb_prod_t['prod_t_cd2']       = $_POST['prod_t_cd2'];
            $cmb_prod_t['prod_t_cd3']       = $_POST['prod_t_cd3'];

            // 登録・更新・削除一括処理
            $mst0801->BatchRegist($cmb_prod_t, $_POST, $aryErrMsg);
            
            //$this->templateAction();
            $this->templateAction($aryErrMsg, $cmb_prod_t);
            $Log->trace("END   changeinputAction");
        }
    }
