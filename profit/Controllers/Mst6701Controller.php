<?php
    /**
     * @file      画面PLU設定マスタコントローラ
     * @author    柴田
     * @date      
     * @version  
     * @note      画面PLU設定編集
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // POS商品マスタモデル
    require './Model/Mst6701.php';
    
    /**
     * POS商品マスタコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class Mst6701Controller extends BaseController
    {

        protected $mst0211 = null;     ///< POS商品マスタモデル

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
         * POS商品マスタ一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START mst0211 showAction");

            $startFlag = true;

            $this->initialDisplay($startFlag);
            $Log->trace("END mst0211 showAction");
        }
 
        /**
         * POS商品マスタ一覧画面初期表示
         * @return    なし
         */
        public function insert_testAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START mst0211 showAction");

            $startFlag = true;

            $this->initialDisplay($startFlag);
            $Log->trace("END mst0211 showAction");
        }
 

        /**
         * POS商品マスタ一覧検索
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START mst0211 initialDisplay");
            
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();     
            $prod_list    = [];
            //$prod_list    = $modal->getprod_detail();    
            $sect_list    = [];
//            $sect_list    = $modal->getsect_detail(); 
            
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();       
            
            require_once './View/Mst6701Panel.html';
            
            $Log->trace("END mst0211InputPanelDisplay");
        }
        
        public function datasearchAction(){
            $payload = file_get_contents('php://input');
            $joken = json_decode($payload,true);             
            global $Log;  // グローバル変数宣言
            $Log->trace("START datasearch"); 
            $Mst6701 = new Mst6701();
            $data = [];
            
            //$joken = json_decode($_POST['param'],true);
            //$joken['param']['org_id'];
            ////print_r(json_encode($joken));
            $data['mst0010'] = $Mst6701->getMst0010Data($joken['org_id']);
            $data['mst0201'] = $Mst6701->getprod_detail($joken['org_id']);
            $data['mst1201'] = $Mst6701->getsect_detail($joken['org_id']);
            $data['mst6701'] = $Mst6701->getMst6701Data($joken['org_id']);
            $data['reji_no_list'] = $Mst6701->getreji_no_detail($joken['org_id']);
            print_r(json_encode($data));
            //print_r(json_encode($joken));
            $Log->trace("END datasearch");            
        }
        
        public  function dataupdateAction(){
                $payload = file_get_contents('php://input');
                $joken = json_decode($payload,true);             
                global $Log;  // グローバル変数宣言
                $Log->trace("START dataupdateAction");
                $Mst6701 = new Mst6701();
                //$joken = json_decode($_POST['param'],true);
                ////print_r($_POST);
                $Mst6701->add_mst6701($joken);
                $Mst6701->upd_mst0010($joken);
                //$Mst6701->upd_mst0201($joken);
                
                $Mst6701->copy_mst6701($joken);
                $Mst6701->prod_mst0201($joken);
                if(!($joken['org_select1'] === 'empty' &&  $joken['org_list'] === '') ){                
                    $Mst6701->prodsecond_mst0201($joken);
                }
                print_r(json_encode($joken));
                $Log->trace("END dataupdateAction"); 
        }
        
        public  function datacopyAction(){
                $payload = file_get_contents('php://input');
                $joken = json_decode($payload,true);             
                global $Log;  // グローバル変数宣言
                $Log->trace("START datacopyAction");
                $Mst6701 = new Mst6701();
                //$joken = json_decode($_POST['param'],true);
                ////print_r($_POST);
                $Mst6701->copy_mst6701($joken);
                //$Mst6701->upd_mst0010($joken);
                //$Mst6701->upd_mst0201($joken);
                $Log->trace("END datacopyAction"); 
        }
        
        public  function dataprodAction(){
                global $Log;  // グローバル変数宣言
                $Log->trace("START datacopyAction");
                $payload = file_get_contents('php://input');
                $joken = json_decode($payload,true);                 
                $Mst6701 = new Mst6701();
                //$joken = json_decode($_POST['param'],true);
                ////print_r($_POST);
                //$Mst6701->copy_mst6701($joken);
                //$Mst6701->upd_mst0010($joken);
                $Mst6701->prod_mst0201($joken);
                $Log->trace("END datacopyAction"); 
        }
        
        
        public  function dataprodsecondAction(){
                $payload = file_get_contents('php://input');
                $joken = json_decode($payload,true);             
                global $Log;  // グローバル変数宣言
                $Log->trace("START datacopyAction");
                $Mst6701 = new Mst6701();
                //$joken = json_decode($_POST['param'],true);
                ////print_r($_POST);
                //$Mst6701->copy_mst6701($joken);
                //$Mst6701->upd_mst0010($joken);
                $Mst6701->prodsecond_mst0201($joken);
                $Log->trace("END datacopyAction"); 
        }
        
        public  function datacleanAction(){
                $payload = file_get_contents('php://input');
                $joken = json_decode($payload,true);             
                global $Log;  // グローバル変数宣言
                $Log->trace("START datacopyAction");
                $Mst6701 = new Mst6701();
                //$joken = json_decode($_POST['param'],true);
                ////print_r($_POST);
                //$Mst6701->copy_mst6701($joken);
                //$Mst6701->upd_mst0010($joken);
                $Mst6701->clean_mst0201($joken);
                $Log->trace("END datacopyAction"); 
        }
        
        public  function datadeleteAction(){
                //$payload = file_get_contents('php://input');
                //$joken = json_decode($payload,true);             
                global $Log;  // グローバル変数宣言
                $Log->trace("START dataupdateAction");
                $Mst6701 = new Mst6701();
                $joken = json_decode($_POST['param'],true);
                ////print_r($joken);
                $Mst6701->delete_mst6701($joken);
                $test = $_GET;
                
                $test['param'] = "Mst6701/show";
                $_SERVER['REQUEST_URI'] = '/profit/index.php?param=Mst6701/show&home=1';
                $_SERVER['QUERY_STRING'] = 'param=Mst6701/show&home=1';
               // //print_r($_SERVER);
                $this->initialDisplay(true);
                $Log->trace("END dataupdateAction"); 
        }   
        public  function testAction(){
            $payload = file_get_contents('php://input');
            $data = json_decode($payload);            
            
             print_r(json_encode($data));
        }
   
    }

    
?>