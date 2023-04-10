<?php
    /**
     * @file    共通モデル(Model)
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    共通で使用するモデルの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseModel.php';

    /**
     * 各モデルの基本クラス
     * @note    共通で使用するモデルの処理を定義
     */
    class BaseModel extends FwBaseModel
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseModelのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseModelのデストラクタ
            parent::__destruct();
        }
    }
?>
