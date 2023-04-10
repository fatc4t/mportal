<?php
    /**
     * @file      商品情報
     * @author    川橋
     * @date      2019/01/15
     * @version   1.00
     * @note      商品情報テーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 商品情報ベースクラス
     * @note   商品情報テーブルの管理を行う。
     */
    class BaseProduct extends BaseModel
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ModelBaseのデストラクタ
            parent::__destruct();
        }



    }

?>