<?php
    /**
     * @file    共通コントローラ(Controller)
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    共通で使用するコントローラの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseController.php';

    /**
     * 各コントローラの基本クラス
     * @note    共通で使用するコントローラの処理を定義
     */
    class BaseController extends FwBaseController
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
        }
        
        /**
         * デストラクタ
         */
        public function __destruct()
        {
            
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }

        /**
         * 配列の値をstring型からint型に変換して配列に再度入れ直す
         * @param    $stringArray
         * @param    $flag
         * @return   $intarray
         */
        protected function changeStringArrayIntArray($stringCommaArray, $flag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeStringArrayIntArray");

            if(!empty($flag))
            {
                $stringComma = $stringCommaArray[0];
            }
            else
            {
                $stringComma = $stringCommaArray[1];
            }

            $stringArray = explode(",", $stringComma);

            foreach($stringArray as $string)
            {
                if(!empty($string))
                {
                    $intArray[] = intval($string);
                }
            }

            $Log->trace("END changeStringArrayIntArray");

            return $intArray;
        }

    }
?>
