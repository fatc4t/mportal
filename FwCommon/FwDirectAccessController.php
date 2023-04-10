<?php
    /**
     * @file    FW直接アクセス用コントローラ(Controller)
     * @author  USE Y.Sakata
     * @date    2016/06/22
     * @version 1.00
     * @note    FW共通コントローラの処理を定義
     */

    // BaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseClass.php';

    /**
     * 各コントローラの基本クラス
     * @note    共通で使用するコントローラの処理を定義
     */
    class FwDirectAccessController extends FwBaseClass
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
            
            // 初期化(セッション開始)
            session_regenerate_id(true);
            // クリックジャッキング対策
            header('X-FRAME-OPTIONS:DENY');
        }
        
        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ログクラス開放処理
            global $Log;  // グローバル変数宣言
            if( !is_null( $Log ) )
            {
                $Log->clearLog();
                $Log = null;
            }
            
            // セッション変数のクリア
            $_SESSION = array();
            // セッションクリア
            @session_destroy();
            
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }

        /**
         * エスケープ処理 (セキュリティ対策)
         * @note      コントローラからモデルへ値を渡す際に使用する
         * @param     $str   エスケープ対象文字列
         * @return    エスケープ終了後の文字列
         */
        public function escStr( $str )
        {
            // NULLバイト攻撃&SQLインジェクション対策
            $ret = $this->delNullStr( $str );
            return $this->pgEscapeStr( $ret );
        }

        /**
         * NULLバイト攻撃対策
         * @note      NULL文字を空白へ変換
         * @param     $str   エスケープ対象文字列
         * @return    変換後の文字列
         */
        private function delNullStr( $str )
        {
            return str_replace( "\0", "", $str );
        }
        
        /**
         * SQLインジェクション対策
         * @note      DBのエスケープ処理
         * @param     $str   エスケープ対象文字列
         * @return    エスケープ終了後の文字列
         */
        private function pgEscapeStr( $str )
        {
            return pg_escape_string($str);
        }

    }
?>
