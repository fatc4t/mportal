<?php
    /**
     * @file    共通コントローラ(Controller)
     * @author  USE Y.Sakata
     * @date    2016/06/27
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
    }
?>
