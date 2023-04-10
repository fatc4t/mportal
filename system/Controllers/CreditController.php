<?php
    /**
     * @file      クレジットマスタコントローラ
     * @author    USE M.Higashihara
     * @date      2016/06/09
     * @version   1.00
     * @note      クレジットマスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // クレジットマスタモデル
    require './Model/Credit.php';
    /**
     * クレジットマスタコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class CreditController extends BaseController
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
         * クレジットマスタ一覧画面初期表示
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

        public function changeinputAction(){
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");
            $org_id = $_GET["org_id"];
            $credit= new credit();
            //print_r($_POST);
            //echo $org_id;
            if($_POST){
                if($_POST['del_data'] ){
                    $del_data = json_decode($_POST['del_data'],true);
                    $credit->del_mst5601($org_id,$del_data);
                }                
                if($_POST['new_data'] ){
                    $new_data = json_decode($_POST['new_data'],true);
                    $credit->add_mst5601($org_id,$new_data);
                    //print_r('add');
                }
                if($_POST['upd_data'] ){
                    $upd_data = json_decode($_POST['upd_data'],true);
                    $credit->upd_mst5601($org_id,$upd_data);
                }
            }
            $this->initialDisplay();
        }

//        /**
//         * クレジットマスタ一覧画面
//         * @note     パラメータは、View側で使用
//         * @param    $startFlag (初期表示時フラグ)
//         * @return   無
//         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            $credit = new credit();
            $data = [];
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
                $data             = $credit->get_mst5601_data($org_id);
                $other['org_id']  = $org_id;
            }            
            
            $title            = ["コード","クレジット名","クレジットカナ","区分","クレジット区分名","得点レート","区分","返金区分名"];
            $key              = ["credit_cd","credit_nm","credit_kn","credit_kbn","credit_kbn_nm","add_prate","refund_kbn","refund_nm"];
            $pr_key_id        = ["credit_cd"];
            $pr_key_list      = "credit_cd , credit_nm";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'Credit';
            $pagetitle        = 'マスタ管理　クレジットマスタ';
            $other['org_detail'] = $credit->getorg_detail();
            require_once '../pagetemplate1/View/template2.html';

            $Log->trace("END   initialDisplay");
        }
    }
