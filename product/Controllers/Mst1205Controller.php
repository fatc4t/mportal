<?php
    /**
     * @file      商品部門分類コントローラ
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品部門分類の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst1205.php';
    /**
     * 商品部門分類コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst1205Controller extends BaseController
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
         * 商品部門分類画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        //public function templateAction()
        public function templateAction($aryErrMsg, $organization_id = '')
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START templateAction");

            // 商品部門分類モデルインスタンス化
            $mst1205 = new Mst1205();

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

            // 商品部門分類一覧データ取得
            //$data           = $mst1205->get_mst1205_data();
            $data = array();
            if ($orgn_id !== ''){
                $data           = $mst1205->get_mst1205_data($orgn_id);
            }

            $title          = array("分類コード","分類名","分類カナ");
            $key            = array("type_cd","type_nm","type_kn");
            $pr_key_id      = array("type_cd");
            $pr_key_list    = "type_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Product';
            $pagetitle      = '商品管理　部門分類マスタ';
            $destination    = 'Mst1205';

            // 店舗コードプルダウン
            $abbreviatedNameList = $mst1205->setPulldown->getSearchOrganizationList( 'reference', true, true );

            require_once '../pagetemplate1/View/template1.html';

            $Log->trace("END   templateAction");
        }

        /**
         * 商品部門分類更新処理
         * @return    なし
         */
        public function changeinputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");

            // 商品部門分類モデルインスタンス化
            $mst1205 = new Mst1205();

            // エラーメッセージ
            $aryErrMsg = array();
            
            // 組織ID
            $organization_id = $_POST['organization_id'];
            
            // 登録・更新・削除一括処理
            $mst1205->BatchRegist($_POST, $aryErrMsg);
            
            //$this->templateAction();
            $this->templateAction($aryErrMsg, $organization_id);
            $Log->trace("END   changeinputAction");
        }
    }
