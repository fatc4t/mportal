<?php
    /**
     * @file    トップモデル
     * @author  スクリプト作者
     * @date    日付
     * @version バージョン
     * @note    注釈を記述
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * トップモデルクラス
     * @note   注釈を記述
     */
    class Top extends BaseModel
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

        /**
         * HOGE検索
         * @param    $postArray   検索パラメータ
         * @return   検索結果(Array)
         */
        public function searchHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchHoge");
            
            $sql = "";
            $sql = $this->getSearchSql($postArray);
            $retArray = array();
            
            $Log->trace("END   searchHoge");
            return $retArray;
        }

        /**
         * HOGE登録
         * @param    $postArray   登録パラメータ
         * @return   成功時：true  失敗：false
         */
        public function addHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START addHoge");
            
            $retArray = $postArray;
            $retArray = array();
            
            $Log->trace("END   addHoge");
            return true;
        }

        /**
         * HOGE修正
         * @param    $postArray   修正パラメータ
         * @return   成功時：true  失敗：false
         */
        public function modHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START modHoge");
            
            $retArray = $postArray;
            $retArray = array();
            
            $Log->trace("END   modHoge");
            return true;
        }

        /**
         * HOGE削除
         * @param    $postArray   削除パラメータ
         * @return   成功時：true  失敗：false
         */
        public function delHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START delHoge");
            
            $retArray = $postArray;
            $retArray = array();
            
            $Log->trace("END   delHoge");
            return true;
        }

        /**
         * 一覧表示用SQL作成
         * @note     一覧表示と検索用SQLを共有させる
         * @param    $postArray   検索パラメータ
         * @return   検索用SQL文
         */
        private function getSearchSql( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getSearchSql");
            
            $sql = "";
            $retArray = $postArray;
            $retArray = array();
            
            $Log->trace("END   getSearchSql");
            return $sql;
        }

    }
?>
