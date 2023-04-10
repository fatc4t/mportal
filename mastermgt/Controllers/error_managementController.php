<?php
    /**
     * @file      帳票 - 仕入先・店舗別支払一覧表コントローラ
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 仕入先・店舗別支払一覧表の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/error_management.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class error_managementController extends BaseController
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
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addInputAction");
            
            if( $_POST['action'] && $_POST['action'] === 'download' ){
                $this->csvoutputAction();
            }elseif( $_POST['action'] && $_POST['action'] === 'resolved' ){
                $err_man = new error_management();
                //test
                $data =  $err_man->setresolve();
                $this->initialDisplay("initial");
            }
            $Log->trace("END   addInputAction");
        }         

        /**
         * 表示項目設定一覧画面初期表示
         * @return    なし
         */
        public function initialAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay("initial");

            $Log->trace("END   showAction");
        }

        /**
         * 表示項目設定一覧画面検索表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay("show");

            $Log->trace("END   showAction");
        }

        /**
         * 帳票一覧画面
         * @note     POS種別画面全てを更新
         * @return   無
         */
        private function initialDisplay($mode)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $err_man = new error_management();

            $search_data=[];
            if($_POST['search_comp']){
                $search_data['search_comp'] = $_POST['search_comp'];
            }
            if($_POST['search_org_id']){
                $search_data['search_org_id'] = $_POST['search_org_id'];
            }
            if($_POST['search_date_start']){
                $search_data['search_date_start'] = $_POST['search_date_start'];
            }
            if($_POST['search_date_end']){
                $search_data['search_date_end'] = $_POST['search_date_end'];
            }
            if($_POST['search_table_start']){
                $search_data['search_table_start'] = $_POST['search_table_start'];
            }
            if($_POST['search_table_end']){
                $search_data['search_table_end'] = $_POST['search_table_end'];
            }
          
            
            $data =  $err_man->getData();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/error_managementPanel.html';
            }
        }
        
        /**
         * CSV出力
         * @return    なし
         */
        public function csvoutputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START csvoutputAction");
            $path = $_POST['path_file'];
            $filename = $_POST['filename'];

            $file_path = $path.$filename;
            if(!file_exists($file_path)){
                //echo 'no file; file_path:'.$file_path;
                die('file not found');
            }
                
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=".$file_path.";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");
            
        }
    }
?>
