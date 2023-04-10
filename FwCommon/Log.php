<?php
    /**
     * @file    FW共通ログ出力クラス
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    ログ出力のFW共通処理用クラス
     *          呼び出しシステムごとに、SystemParametersクラスを設定し、以下の変数を定義すること
     *          $LOG_SETTING_FILE：ログ設定ファイル
     *          $LOG_APPENDER    ：使用するログ設定アダプタ
     *          $LOG_MESSAGE_FILE：ログメッセージ定義ファイル
     */

    // Log4phpの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'log4php/Logger.php';
    Logger::configure( SystemParameters::$LOG_SETTING_FILE );

    // ログメッセージ定義ファイル
    require_once SystemParameters::$LOG_MESSAGE_FILE;

    /**
     * ログ出力クラス
     * @note    ログ出力の共通処理用クラス
     */
    class Log
    {
        // Log4PHPアクセスクラス
        protected $logger = null;        ///< Log4PHPアクセスクラス

        // LogMessageクラス
        protected $logMessage = null;    ///< LogMessageクラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            global $logger, $logMessage;    // グローバル変数宣言
            
            // Log4phpの初期化
            $logger = Logger::getLogger( SystemParameters::$LOG_APPENDER );
            $logMessage = new LogMessage();
        }

        /**
         * デストラクタ
         * @note    Log関係のメモリは、コントローラから明示的にクリアする
         */
        public function __destruct()
        {
            // Log関係のメモリは、コントローラから明示的にクリアする
            // 呼び出しメソッド：clearLog()
        }

        /**
         * Logメモリ開放
         * @return    なし
         */
        public function clearLog()
        {
            global $logger, $logMessage;    // グローバル変数宣言
            $logger = null;
            $logMessage = null;
        }

        /**
         * バックトレース取得
         * @return    1つ前の実行したソースコードの位置の文字列情報
         */
        private function getBackTrace()
        {
            // グローバル変数宣言
            $dbt = debug_backtrace();
            $file = $dbt[1]['file'];
            $line = $dbt[1]['line'];
            
            $schema = "";
            if( isset( $_SESSION["SCHEMA"] ) )
            {
                $schema = $_SESSION["SCHEMA"];
            }
            
            return $schema . " " . $file . "(" . $line .") ";
        }

        /**
         * トレースログ
         * @note      本番では、出力しないログを設定
         *            関数のIN/OUTに定義する
         * @return    なし
         */
        public function trace($msg)
        {
            global $logger;   // グローバル変数宣言
            $logger->trace( $this->getBackTrace() . $msg . "\n");
        }

        /**
         * デバッグログ
         * @note      本番では、出力しないログを設定
         *            エラー発生時の変数の情報を定義
         * @return    なし
         */
        public function debug($msg)
        {
            global $logger;   // グローバル変数宣言
            $logger->debug( $this->getBackTrace() . $msg . "\n");
        }
        
        /**
         * 操作ログ (定型文)
         * @note      本番で出力するログ
         *            操作ログとして、正常系のログを定義
         * @return    なし
         */
        public function info($msg)
        {
            global $logger;    // グローバル変数宣言
            $msgFormat = $this->getMsgLog($msg);
            $logger->info( $this->getBackTrace() . $msgFormat . "\n");
        }

        /**
         * ワーニングログ (定型文)
         * @note      本番で出力するログ
         *            操作ログとして、入力ミスなどを定義
         * @return    なし
         */
        public function warn($msg)
        {
            global $logger;    // グローバル変数宣言
            $msgFormat = $this->getMsgLog($msg);
            $logger->warn( $this->getBackTrace() . $msgFormat . "\n");
        }

        /**
         * ワーニングログ (詳細)
         * @note      ワーニング詳細ログ
         *            操作ログとして、入力ミス時の変数値を定義
         * @return    なし
         */
        public function warnDetail($msg)
        {
            global $logger;    // グローバル変数宣言
            $logger->warn( $this->getBackTrace() . $msg . "\n");
        }

        /**
         * エラーログ (定型文)
         * @note      本番で出力するログ
         *            操作ログとして、システムエラーなどを定義
         * @return    なし
         */
        public function error($msg)
        {
            global $logger;    // グローバル変数宣言
            $msgFormat = $this->getMsgLog($msg);
            $logger->error( $this->getBackTrace() . $msgFormat . "\n");
        }

        /**
         * エラー詳細ログ (詳細)
         * @note      エラー詳細ログ
         *            操作ログとして、システムエラー時の変数値を定義
         * @return    なし
         */
        public function errorDetail($msg)
        {
            global $logger;    // グローバル変数宣言
            $logger->error( $this->getBackTrace() . $msg . "\n");
        }

        /**
         * 障害ログ (定型文)
         * @note      本番で出力するログ
         *            操作ログとして、システム障害(ハード/ソフトウェア設定ミス)などを定義
         * @return    なし
         */
        public function fatal($msg)
        {
            global $logger;    // グローバル変数宣言
            $msgFormat = $this->getMsgLog($msg);
            $logger->fatal( $this->getBackTrace() . $msgFormat . "\n");
        }

        /**
         * 障害詳細ログ (詳細)
         * @note      エラー詳細ログ
         *            操作ログとして、システム障害(ハード/ソフトウェア設定ミス)時の変数値を定義
         * @return    なし
         */
        public function fatalDetail($msg)
        {
            global $logger;    // グローバル変数宣言
            $logger->fatal( $this->getBackTrace() . $msg . "\n");
        }

        /**
         * 出力ログメッセージ
         * @note      ログメッセージの定義一覧から、メッセージの文字列を取得
         * @param     $msg   MSG定義名
         * @return    メッセージの文字列
         */
        public function getMsgLog($msg)
        {
            global $logMessage;    // グローバル変数宣言
            return $logMessage->$msg;
        }
    }
?>
