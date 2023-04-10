<?php
    /**
     * @file      顧客コントローラ
     * @author    USE M.Higashihara
     * @date      2016/06/09
     * @version   1.00
     * @note      顧客の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/DataMaintenance.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class DataMaintenanceController extends BaseController
    {
        //private $scust_cd = "";
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
         * 顧客一覧画面初期表示
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
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addInputAction");
            $DataMaintenance = new DataMaintenance();
            //print_r($_POST);
            //echo "test:".$_POST[0];
            // delete data
            if(!$_POST['head'] && $_POST['key_list'] ){
                //print_r($_POST['key_list']);
                $DataMaintenance->deletedata(); 
            }else if ($_POST['key_list']){
                $DataMaintenance->virtualdeletedata();
            }
            // insert/update data
            $DataMaintenance->insupddata();
            $this->initialDisplay();
            $Log->trace("END   addInputAction");
        } 

        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delAction");
            $DataMaintenance = new DataMaintenance();      
            //print_r($_GET);
            if ($_GET["organization_id"]) {
                $DataMaintenance->deldata($_GET["organization_id"]);            
                $this->initialDisplay();
                $Log->trace("END   delAction");
            }
            $Log->trace("END delAction");
        }


//        /**
//         * 顧客一覧画面
//         * @note     パラメータは、View側で使用
//         * @param    $startFlag (初期表示時フラグ)
//         * @return   無
//         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            if($_GET['auth'] == 1){
                $auth = true;
            }else{
                $auth = false;
            }
            $modal = new Modal();
            $DataMaintenance = new DataMaintenance();
            // 帳票フォーム一覧データ取得
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $supp_detail    = [];
            $supp_detail    = $modal->getsupp_detail();
            $prod_detail    = [];
            //$prod_detail    = $modal->getprod_detail();
            $all_prod_data  = $DataMaintenance->getprod_detail();
            $prod_search_info    = $all_prod_data['search_info'];
            $prod_data_detail = $all_prod_data['detail_info'];
            $base           = new BaseModel();
            $Sizelist       = $base->getSizeList();              
            $Data           = $DataMaintenance->getdata();
            require_once './View/DataMaintenancePanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
