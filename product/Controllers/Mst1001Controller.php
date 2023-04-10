<?php
    /**
     * @file      商品メーカーコントローラ
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品メーカーの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst1001.php';
    /**
     * 商品メーカーコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst1001Controller extends BaseController
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
         * 商品メーカー画面初期表示
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
         * 商品メーカー画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        //public function templateAction()
        public function templateAction($aryErrMsg, $organization_id = '')
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START templateAction");

            // 商品メーカーモデルインスタンス化
            $mst1001 = new Mst1001();

            // 組織ID
            $orgn_id = '';
            // データ更新(changeinputAction)後
            if ($organization_id !== ''){
                $orgn_id = $organization_id;
            }
            // 組織プルダウン変更時
            else if ($_GET['orgnid']){
                $orgn_id = $_GET['orgnid'];
            }

            // 商品メーカー一覧データ取得
            //$data           = $mst1001->get_mst1001_data();
            $data = array();
            if ($orgn_id !== ''){
                $data           = $mst1001->get_mst1001_data($orgn_id);
            }

            $title          = array("コード","メーカー名","メーカー名略","メーカーカナ","メーカーカナ略");
            $key            = array("maker_cd","maker_nm","maker_nm_rk","maker_kn","maker_kn_rk");
            $pr_key_id      = array("maker_cd");
            $pr_key_list    = "maker_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Product';
            $pagetitle      = '商品管理★メーカーマスタ';
            $destination    = 'Mst1001';

            // 店舗コードプルダウン
            $abbreviatedNameList = $mst1001->setPulldown->getSearchOrganizationList( 'reference', true, true );

            require_once '../product/View/Mst1001.html';

            $Log->trace("END   templateAction");
        }

        /**
         * 商品メーカー更新処理
         * @return    なし
         */
        public function changeinputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");

            // 商品メーカーモデルインスタンス化
            $mst1001 = new Mst1001();

            // エラーメッセージ
            $aryErrMsg = array();

            // エラーメッセージ
            $aryErrMsg = array();

            // 組織ID
            $organization_id = $_POST['organization_id'];
            // 登録・更新・削除一括処理
            $mst1001->BatchRegist($_POST, $aryErrMsg);

            //$this->templateAction();
            $this->templateAction($aryErrMsg, $organization_id);
            $Log->trace("END   changeinputAction");
        }
    }
