<?php
    /**
     * @file      ファイルアップロードコントローラ
     * @author    USE Y.Sakata
     * @date      2016/07/29
     * @version   1.00
     * @note      ファイルアップロードの制御を行う
     */

    // BaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwDirectAccessController.php';

    /**
     * ファイルアップロードコントローラクラス
     * @note   ファイルアップロード(シフト表)のアップロード用クラス
     */
    class FileUploadController extends FwDirectAccessController
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
         * ファイルのアップロード
         * @return   なし
         */
        public function uploadAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START uploadAction");
            $Log->info( "MSG_INFO_1711" );
            
            session_start();
            $current_id = session_id();
            session_write_close();
            $UpLoadFile = "";                   //一時ファイル
            //一時ファイルまでのディレクトリをセットし、ファイルを取得する
            $UpLoadFile = SystemParameters::$SHIFT_SAVE_PATH . $current_id.'_'.$_FILES["upfile"]["name"];

            if (move_uploaded_file($_FILES['upfile']['tmp_name'], $UpLoadFile)) {
                echo "File UpLoad OK";
            } else {
                echo "File UpLoad NG";
            }
            
            $Log->trace("END   uploadAction");
        }
    }
?>
