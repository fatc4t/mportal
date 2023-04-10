<?php
    /**
     * @file    トークン管理クラス
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    CSRF(クロスサイトリクエストフォージェリ) 対策用のトークン管理を行う
     */

    /**
     * トークン管理クラス
     * @note    ワンタイムトークンではなく、規定時間でトークンを変更する
     */
    class Token
    {
        // トークンの有効期限
        private $TTL;   ///< トークンの有効期限

        // トークン名
        private $TOKEN_NAME = 'TOKENS'; ///< トークンを保持しているセッション変数名

        /**
         * コンストラクタ
         * @param     $inputTtl   トークンの有効期限
         */
        public function __construct( $inputTtl )
        {
            // CSRF 検出トークン最大有効期限(秒)
            // 最小期限はこの値の 1/2 (900 の場合は、450秒間は最低保持される)
            $this->TTL = (int)$inputTtl;
        }

        /**
         * トークンを生成
         * @return    トークン
         */
        function createToken()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START createToken" );
            
            $curr = time();
            $tokens = isset( $_SESSION[$this->TOKEN_NAME] ) ? $_SESSION[$this->TOKEN_NAME] : array();
            foreach ( $tokens as $id => $time )
            {
                // 有効期限切れの場合はリストから削除
                if ( $time < $curr - $this->TTL ) {
                    unset( $tokens[$id] );
                }
                else {
                    $uniq_id = $id;
                }
            }
            if ( count( $tokens ) < 2 ) 
            {            
                if ( ! $tokens || ( $curr - (int)( $this->TTL / 2 ) ) >= max( $tokens ) ) 
                {
                    $uniq_id = sha1( uniqid( rand(), true ) );
                    $tokens[$uniq_id] = time();
                }

            }
            // リストをセッションに登録
            $_SESSION[$this->TOKEN_NAME] = $tokens;
            
            $Log->trace("END   createToken");

            return $uniq_id;
        }

        /**
         * CSRF(クロスサイトリクエストフォージェリ) のリクエストである
         * @note      セッションのリストにトークンが存在しているか
         * @param     $token   トークン
         * @return    CSRFのリクエスト：true  正常リクエスト：false
         */
        function isCSRF( $token )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START isCSRF" );
            
            $tokens = $_SESSION[$this->TOKEN_NAME];
            if ( isset( $tokens[$token] ) ) 
            {
                return false;
            }
            $Log->trace("END isCSRF");
            return true;
        }
        
        /**
         * トークンの有効期限が切れている
         * @note      セッションのリストにトークンが存在し、トークンが有効期限内の場合は FALSE を返す
         * @param     $token   トークン
         * @return    トークンの有効期限切れ：true  正常リクエスト：false
         */
        function isTimeOut( $token )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START isTimeOut" );
            
            $tokens = $_SESSION[$this->TOKEN_NAME];

            if ( isset( $tokens[$token] ) && $tokens[$token] > time() - $this->TTL ) {
                return false;
            }
            $Log->trace("END isTimeOut");
            return true;
        }
    }
?>
