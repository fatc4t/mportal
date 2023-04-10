<?php
    /**
     * @file      商品券マスタコントローラ
     * @author    USE M.Higashihara
     * @date      2016/06/09
     * @version   1.00
     * @note       商品券マスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    //  商品券マスタモデル
    require './Model/Gift.php';
    /**
     *  商品券マスタコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class GiftController extends BaseController
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
         *  商品券マスタ一覧画面初期表示
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
            $gift= new gift();
            //print_r($_POST);
            //echo $org_id;
            if($_POST){
                if($_POST['new_data'] ){
                    $new_data = json_decode($_POST['new_data'],true);
                    $gift->add_mst5901($org_id,$new_data);
                }
                if($_POST['del_data'] ){
                    $del_data = json_decode($_POST['del_data'],true);
                    $gift->del_mst5901($org_id,$del_data);
                }
                if($_POST['upd_data'] ){
                    $upd_data = json_decode($_POST['upd_data'],true);
                    $gift->upd_mst5901($org_id,$upd_data);
                }
            }
            $this->initialDisplay();
        }

//        /**
//         * 商品券マスタ一覧画面
//         * @note     パラメータは、View側で使用
//         * @param    $startFlag (初期表示時フラグ)
//         * @return   無
//         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            $gift = new gift();
            $data = [];
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
                $data             = $gift->get_mst5901_data($org_id);
                $other['org_id']  = $org_id;
                $gift_size        = $gift->gift_size($org_id);
                $other['gift_size'] = $gift_size;
            }            
              
            $title            = ["コード","商品券名","商品券カナ","値引金額","区分","お釣り区分名","区分","得点区分名"];
            $key              = ["gift_certi_cd","gift_certi_nm","gift_certi_kn","disc_money","change_kbn","change_nm","point_kbn","point_nm"];
            $pr_key_id        = ["gift_certi_cd"];
            $pr_key_list      = "gift_certi_cd , gift_certi_nm";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'Gift';
            $pagetitle        = 'マスタ管理　商品券マスタ';
            $other['org_detail'] = $gift->getorg_detail();
            require_once '../pagetemplate1/View/template2.html';

            $Log->trace("END   initialDisplay");
        }
    }
