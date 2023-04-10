<?php
    /**
     * @file      商品区分コントローラ
     * @author    尉
     * @date      2019/09/05
     * @version   1.00
     * @note      商品区分の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 商品区分モデル
    require './Model/OrderItemDivision.php';
    /**
     * 商品区分コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class OrderItemDivisionController extends BaseController
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
        public function templateAction($aryErrMsg, $cmb_prod_k = array())
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START templateAction");

            // 商品区分モデルインスタンス化
            $orderitemdivision = new OrderItemDivision();

            // 区分種別プルダウン変更時
            if ($_SESSION['MA_CD']){
                $cmb_prod_k = array();
                // メーカーコード（固定）
                $cmb_prod_k['ma_cd'] = isset($_GET['ma_cd']) ? $_GET['ma_cd'] : $_SESSION['MA_CD'];
                // 区分種別
                $cmb_prod_k['prod_k_type'] = $_GET['type'];
                // 大区分
                $cmb_prod_k['prod_k_cd1'] = isset($_GET['cd1']) ? $_GET['cd1'] : '';
                // 中区分
                $cmb_prod_k['prod_k_cd2'] = isset($_GET['cd2']) ? $_GET['cd2'] : '';
                // 小区分
                $cmb_prod_k['prod_k_cd3'] = isset($_GET['cd3']) ? $_GET['cd3'] : '';
                // 小区分
                $cmb_prod_k['prod_k_cd4'] = isset($_GET['cd4']) ? $_GET['cd4'] : '';
                // 小区分
                $cmb_prod_k['prod_k_cd5'] = isset($_GET['cd5']) ? $_GET['cd5'] : '';
            }

            // 商品区分一覧データ取得
            $data = array();
            if (count($cmb_prod_k) > 0){
                $data           = $orderitemdivision->get_orderitemdivision_data($cmb_prod_k);
            }
            $title          = array("コード","商品区分名","商品区分名カナ");
            $key            = array("prod_k_cd","prod_k_nm","prod_k_kn");
            $pr_key_id      = array("prod_k_cd");
            $pr_key_list    = "prod_k_cd";
            $addrow         = 1;
            $save           = 1;
            $controller     = 'Order';
            $pagetitle      = '商品管理　商品区分マスタ';
            $destination    = 'OrderItemDivision';

            $ma_cd = $_SESSION['MA_CD'];
            $ma_name = $_SESSION['USER_NAME'];
            // 業者コードプルダウン
            $makerList = $orderitemdivision->getMakerList();

            // 商品区分データ（プルダウン用）
            $alldata = $orderitemdivision->get_orderitemdivision_alldata();
            
            require_once '../order/View/OrderItemDivision.html';

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
            $orderitemdivision = new OrderItemDivision();

            // エラーメッセージ
            $aryErrMsg = array();
            
            $cmb_prod_k = array();
            // 組織ID
            $cmb_prod_k['ma_cd']            = $_POST['ma_cd'];
            $cmb_prod_k['prod_k_type']      = $_POST['prod_k_type'];
            $cmb_prod_k['prod_k_cd1']       = $_POST['prod_k_cd1'];
            $cmb_prod_k['prod_k_cd2']       = $_POST['prod_k_cd2'];
            $cmb_prod_k['prod_k_cd3']       = $_POST['prod_k_cd3'];
            $cmb_prod_k['prod_k_cd4']       = $_POST['prod_k_cd4'];
            $cmb_prod_k['prod_k_cd5']       = $_POST['prod_k_cd5'];

            // 登録・更新・削除一括処理
            $orderitemdivision->BatchRegist($cmb_prod_k, $_POST, $aryErrMsg);
           
            $this->templateAction($aryErrMsg, $cmb_prod_k);
            $Log->trace("END   changeinputAction");
            if(count($aryErrMsg) == 0){
                echo "<script type='text/javascript'>alert('データを更新しました。');</script>";
            }
        }
    }
