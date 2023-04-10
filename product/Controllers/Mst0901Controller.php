<?php
    /**
     * @file      商品区分コントローラ
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品区分の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/Mst0901.php';
    /**
     * 商品区分コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst0901Controller extends BaseController
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
         * 商品区分画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        //public function templateAction()
        public function templateAction($aryErrMsg, $cmb_prod_k = array())
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START templateAction");

            // 商品区分モデルインスタンス化
            $mst0901 = new Mst0901();

            // 分類種別プルダウン変更時
            if ($_GET['orgnid']){
                $cmb_prod_k = array();
                // 組織ID
                $cmb_prod_k['organization_id'] = $_GET['orgnid'];
                // 区分種別
                $cmb_prod_k['prod_k_type'] = $_GET['type'];
            }

            // 商品区分一覧データ取得
            //$data           = $mst0901->get_mst0901_data();
            $data = array();
            if (count($cmb_prod_k) > 0){
                $data           = $mst0901->get_mst0901_data($cmb_prod_k);
            }

            $title          = array("コード","商品区分名");
            $key            = array("prod_k_cd","prod_k_nm");
            $pr_key_id      = array("prod_k_cd");
            $pr_key_list    = "prod_k_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Product';
            $pagetitle      = '商品管理　商品区分マスタ';
            $destination    = 'Mst0901';

            // システムマスタ
            $mst0010_alldata = $mst0901->get_mst0010_alldata();
            
            // プルダウン選択した商品区分名称を取得
            $txt_prod_k_type = '';
            if (isset($cmb_prod_k)){
                for ($i = 0; $i < count($mst0010_alldata); $i ++){
                    if ($mst0010_alldata[$i]['organization_id'] === intval($cmb_prod_k['organization_id'])){
                        $txt_prod_k_type = $mst0010_alldata[$i]['p_kubun'.$cmb_prod_k['prod_k_type']];
                        break;
                    }
                }
            }

            // 店舗コードプルダウン
            $abbreviatedNameList = $mst0901->setPulldown->getSearchOrganizationList( 'reference', true, true );
            
            //require_once '../pagetemplate1/View/template1.html';
            require_once '../product/View/Mst0901template1.html';

            $Log->trace("END   templateAction");
        }

        /**
         * 商品区分更新処理
         * @return    なし
         */
        public function changeinputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");

            // 商品区分モデルインスタンス化
            $mst0901 = new Mst0901();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $cmb_prod_k = array();
            // 組織ID
            $cmb_prod_k['organization_id']  = $_POST['organization_id'];
            $cmb_prod_k['prod_k_type']      = $_POST['prod_k_type'];
            $cmb_prod_k['prod_k_type_nm']   = $_POST['prod_k_type_nm'];


            // 登録・更新・削除一括処理
            $mst0901->BatchRegist($cmb_prod_k, $_POST, $aryErrMsg);
            
            //$this->templateAction();
            $this->templateAction($aryErrMsg, $cmb_prod_k);
            $Log->trace("END   changeinputAction");
        }
    }
