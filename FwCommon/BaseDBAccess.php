<?php
    /**
     * @file    FW共通DBアクセスクラス
     * @author  USE Y.Sakata
     * @date    2016/06/23
     * @version 1.00
     * @note    FW共通DBアクセスクラス
     */

    // Log4phpの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'Log.php';

    /**
     * FW共通DBアクセスクラス(抽象クラス)
     * @note    DB共通アクセスクラス( 1アクション 1接続 )
     */
    abstract class BaseDBAccess
    {
        // DB接続情報領域
        private $DSN    = "";    ///< データベース接続情報
        private $USER   = "";    ///< データベースユーザ
        private $PWD    = "";    ///< データベースパスワード
        private $pdo    = null;  ///< DB操作変数

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // DB接続情報取得
            require SystemParameters::$FW_DB_CON_FILE;

            // 取得した各情報設定
            $this->DSN    = $M_DSN;
            $this->USER   = $M_USER;
            $this->PWD    = $M_PWD;
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // 取得した各情報を初期化
            $this->DSN    = "";
            $this->USER   = "";
            $this->PWD    = "";
        }

        /**
         * トランザクション開始
         * @return    成功時：true  失敗：false
         */
        public function beginTransaction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START beginTransaction");
             
            try
            {
                // 接続チェック
                if( !$this->isDBConnection() )
                {
                    $Log->fatal("MSG_FW_DB_DISCONNECTED");
                    return false;
                }

                // トランザクションの結果を直接返す
                return $this->pdo->beginTransaction();
            }
            catch( Exception $e )
            {
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $Log->fatalDetail($e->getMessage());
                $Log->trace("END beginTransaction");
                return false;
            }
        }

        /**
         * SQL実行(トランザクションの有無は関係なく、共通対応)
         * @param    $sql   実行するSQL文
         * @param    $array SQLに設定するパラメータ
         * @param    $isUpdate 更新結果を確認する
         * @return   成功：PDOStatementオブジェクト 失敗：false
         */
        public function executeSQL( $sql, $array, $isUpdate = false )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START executeSQL");
            try
            {
                // 接続チェック
                if( !$this->isDBConnection() )
                {
                    $Log->fatal("MSG_FW_DB_DISCONNECTED");
                    return false;
                }

                // SQL実行前に、スキーマを設定する
                if( isset( $_SESSION["SCHEMA"] ) )
                {
                    $exeSql  = "set search_path to " . $_SESSION["SCHEMA"] . ";";
                    // SQL文の設定
                    $stmt = $this->pdo->prepare( $exeSql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    // SQL実行(パラメータ引数設定)
                    $stmt->execute();
                }

                // SQL文の設定
                $stmt = $this->pdo->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                // SQL実行(パラメータ引数設定)
                $result = $stmt->execute($array);

                // 更新行があるか確認する
                if( !$this->isUpdateResult( $result, $isUpdate, $stmt ) )
                {
                    // 更新行がない場合、エラーとする
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $Log->debug($sql);
                    return false;
                }
                
                // SQL実行結果
                if ( !$result )
                {
                    $Log->fatal("MSG_FW_DB_EXECUTE_NG");
                    $Log->debug($sql);
                    $list = "";
                    foreach($array as $arr)
                    {
                        $list .= $arr . ",";
                    }
                    $list = rtrim($list, ',');
                    $Log->debug($list);
                    $infoArray = $this->pdo->fatalInfo();
                    foreach($infoArray as $info)
                    {
                        $Log->fatalDetail($info);
                    }
                }
                $Log->trace("END executeSQL");

                return $stmt;
            }
            catch( Exception $e )
            {
                $Log->fatal("MSG_FW_DB_EXECUTE_EXCEPTION");
                $Log->debug($sql);
                $list = "";
                foreach($array as $arr)
                {
                    $list .= $arr . ",";
                }
                $list = rtrim($list, ',');
                $Log->debug($list);
                $Log->fatalDetail($e->getMessage());
            }

            $Log->trace("END executeSQL");
            return false;
        }
//PME
        /**
         * SQL実行(トランザクションの有無は関係なく、共通対応)
         * @param    $sql   実行するSQL文
         * @param    $array SQLに設定するパラメータ
         * @param    $isUpdate 更新結果を確認する
         * @return   成功：PDOStatementオブジェクト 失敗：false
         */
        public function executeSQL_no_searchpath( $sql, $array, $isUpdate = false )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START executeSQL_no_searchpath");
            try
            {
                // 接続チェック
                if( !$this->isDBConnection() )
                {
                    $Log->fatal("MSG_FW_DB_DISCONNECTED");
                    return false;
                }

                // SQL実行前に、スキーマを設定する

                $exeSql  = "set search_path to public ;";
                // SQL文の設定
                $stmt = $this->pdo->prepare( $exeSql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                // SQL実行(パラメータ引数設定)
                $stmt->execute();


                // SQL文の設定
                $stmt = $this->pdo->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                // SQL実行(パラメータ引数設定)
                $result = $stmt->execute($array);

                // 更新行があるか確認する
                if( !$this->isUpdateResult( $result, $isUpdate, $stmt ) )
                {
                    // 更新行がない場合、エラーとする
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $Log->debug($sql);
                    return false;
                }
                
                // SQL実行結果
                if ( !$result )
                {
                    $Log->fatal("MSG_FW_DB_EXECUTE_NG");
                    $Log->debug($sql);
                    $list = "";
                    foreach($array as $arr)
                    {
                        $list .= $arr . ",";
                    }
                    $list = rtrim($list, ',');
                    $Log->debug($list);
                    $infoArray = $this->pdo->fatalInfo();
                    foreach($infoArray as $info)
                    {
                        $Log->fatalDetail($info);
                    }
                }
                $Log->trace("END executeSQL");

                return $stmt;
            }
            catch( Exception $e )
            {
                $Log->fatal("MSG_FW_DB_EXECUTE_EXCEPTION");
                $Log->debug($sql);
                $list = "";
                foreach($array as $arr)
                {
                    $list .= $arr . ",";
                }
                $list = rtrim($list, ',');
                $Log->debug($list);
                $Log->fatalDetail($e->getMessage());
            }

            $Log->trace("END executeSQL_no_searchpath");
            return false;
        }        
//PME END        
        /**
         * Insert時のテーブルIDを取得
         * @param     $tabelName   Insert実施したテーブル名
         * @return    成功：lastInsertId 失敗：0
         */
        public function lastInsertId( $tabelName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START lastInsertId");
            try
            {
                if( isset( $this->pdo ) )
                {
                    $seq = $this->getSequenceName($tabelName);
                    $Log->debug("seq：" . $seq . ", tabelName:" . $tabelName );
                    return $this->pdo->lastInsertId($seq);
                }
                $Log->debug("トランザクションなしの為、0を返却");
            }
            catch( Exception $e )
            {
                $Log->fatal("MSG_FW_DB_LAST_INSERT_ID_NG");
                $Log->fatalDetail($e->getMessage());
            }

            $Log->trace("END lastInsertId");
            return 0;
        }

        /**
         * コミット処理
         * @return    成功時：true  失敗：false
         */
        public function commit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START commit");
            try
            {
                if( isset( $this->pdo ) )
                {
                    return $this->pdo->commit();
                }
                $Log->debug("トランザクションなしの為、エラー");
            }
            catch( Exception $e )
            {
                $Log->fatal("MSG_FW_DB_COMMIT_EXCEPTION");
                $Log->fatalDetail($e->getMessage());
            }

            $Log->trace("END commit");
            return false;
        }

        /**
         * ロールバック処理
         * @return    成功時：true  失敗：false
         */
        public function rollBack()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START rollBack");
            try
            {
                if( isset( $this->pdo ) )
                {
                    return $this->pdo->rollBack();
                }

                $Log->debug("トランザクションなしの為、エラー");
            }
            catch( Exception $e )
            {
                $Log->fatal("MSG_FW_DB_ROLL_BACK_EXCEPTION");
                $Log->fatalDetail($e->getMessage());
            }

            $Log->trace("END rollBack");
            return false;
        }

        /**
         * シーケンステーブル名取得(抽象メソッド)
         * @param     $tabelName   Insert実施したテーブル名
         * @return    成功：シーケンステーブル名 失敗：空白
         */
        abstract protected function getSequenceName( $tabelName );

        /**
         * データベース接続
         * @return    成功時：true  失敗：false
         */
        private function connectDB()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START connectDB");
            try
            {
                $this->pdo = new PDO( $this->DSN, $this->USER, $this->PWD);
                $this->pdo->query("SET NAMES utf8");
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $Log->trace("END connectDB");
                return true;
            }
            catch( Exception $e )
            {
                $Log->fatal("MSG_FW_DB_DISCONNECTED");
                $Log->fatalDetail($e->getMessage());
            }

            $Log->trace("END connectDB");
            return false;
        }

        /**
         * データベース接続チェック
         * @return    成功時：true  失敗：false
         */
        private function isDBConnection()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isDBConnection");
            
            // DB接続チェック
            if( !isset( $this->pdo ) )
            {
                if( !$this->connectDB() )
                {
                    $Log->trace("END isDBConnection");
                    return false;
                }
            }
            
            $Log->trace("END isDBConnection");
            return true;
        }
        
        /**
         * SQLの更新結果チェック
         * @param    $result   SQLの実行結果
         * @param    $isUpdate 更新結果を確認する
         * @return    成功時：true  失敗：false
         */
        private function isUpdateResult( $result, $isUpdate, $stmt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isUpdateResult");
            if( $result && $isUpdate )
            {
                if( $stmt->rowCount() == 0 )
                {
                    // 更新行がない場合、エラーとする
                    $Log->trace("END isUpdateResult");
                    return false;
                }
            }
            
            $Log->trace("END isUpdateResult");
            return true;
        }
    }
?>
