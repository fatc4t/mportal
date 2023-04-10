<?php
    /**
     * @file    共通SQL(Model)
     * @author  USE M.Higashihara
     * @date    2016/06/29
     * @version 1.00
     * @note    テーブル名などを引数で渡し、SQL文または、実行結果を返す
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';

    /**
     * SQL生成クラス
     * @note    共通するSQL文の処理モデルの処理を定義
     */
    class GenerationSQLSentence extends FwBaseClass
    {
        // DBアクセスクラス
        protected $DBA = null;    ///< DBアクセスクラス

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
         * 登録データ数の有無確認
         * @param    $keyID
         * @param    $tableName
         * @param    $parameterName
         * @param    $applicationFlag
         * @return   $registrationCount(登録情報が有の場合1)
         */
        public function getRegistrationCount($keyID, $tableName, $parameterName, $applicationFlag = false)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getRegistrationCount");

            $keyName = ":" . $parameterName;

            $sql  = ' SELECT count(*) as cnt FROM ';
            $sql .=  $tableName; 
            $sql .= ' WHERE ';
            $sql .=  $parameterName; 
            $sql .= ' = ';
            $sql .=  $keyName;

            if(!empty($applicationFlag))
            {
                // 該当データが適用予定であるか判断する為、=はつけない
                $sql .= ' AND application_date_start > current_timestamp ';
            }

            $parametersArray[$keyName] = $keyID;

            $result = $DBA->executeSQL($sql, $parametersArray);

            $registrationCount = "";
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $registrationCount = $data['cnt'];
            }

            $Log->trace("END getRegistrationCount");

            return $registrationCount;
        }

        /**
         * データ削除処理
         * @param    $keyID
         * @param    $tableName
         * @param    $parameterName
         * @return   SQLの実行結果(true/false)
         */
        public function physicalDeleteData($keyID, $tableName, $parameterName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START physicalDeleteData");

            $keyName = ":" . $parameterName;

            $sql  = ' DELETE FROM ';
            $sql .= $tableName;
            $sql .= ' WHERE ';
            $sql .= $parameterName;
            $sql .= ' = ';
            $sql .= $keyName;

            $parameters[$keyName] = $keyID;

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3010");
                $errMsg = $tableName . "内のカラム" . $parameterName . "に対する値" . $keyID . "の登録情報の削除失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3010";
            }

            $Log->trace("END physicalDeleteData");

            return "MSG_BASE_0000";
        }

        /**
         * インサート文補完メソッド
         * @param    $postArray
         * @param    $sqlParamenter(呼び元の引数の値を変更する)
         * @param    $parameters(呼び元の引数の値を変更する)
         * @param    $columnList（getColumnListメソッド参照）
         * @return   SQL文とパラメータ配列
         */
        public function creatInsertSQL($postArray, &$sqlParamenter, &$parameters, $columnList)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatInsertSQL");
        
            $sqlColumn = "";
            
            foreach($postArray as $postKey => $postValue)
            {
                foreach($columnList as $columnKey => $columnvalue)
                {
                    if($postKey === $columnKey && !empty($postValue))
                    {
                        if($columnvalue === 'date')
                        {
                            $postValue = "'" . $postValue . "'";
                        }
                        $keyName = ":" . $postKey;
                        $sqlColumn .= '                , ' . $postKey;
                        $sqlParamenter .= '            , ' . $keyName;
                        $parameters[$keyName] = $postValue;
                    }
                }
            }

            $Log->trace("END creatInsertSQL");

            return $sqlColumn;
        }

        /**
         * アップデート文補完メソッド
         * @param    $postArray
         * @param    $parameters(呼び元の引数の値を変更する)
         * @return   SQL文とパラメータ配列
         */
        public function creatUpdateSQL($postArray, &$parameters, $columnList)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUpdateSQL");

            $sqlUpdate = "";
            foreach($postArray as $postKey => $postValue)
            {
                foreach($columnList as $columnKey => $columnvalue)
                {
                    if($postKey === $columnKey && !empty($postValue))
                    {
                        if($columnvalue === 'date')
                        {
                            $postValue = "'" . $postValue . "'";
                        }
                        $keyName = ":" . $postKey;
                        $sqlUpdate .= ' , ' . $postKey . ' = ' . $keyName;
                        $parameters[$keyName] = $postValue;
                    }
                }
            }

            $Log->trace("END creatUpdateSQL");

            return $sqlUpdate;
        }

        /**
         * 適用状況検索チェックボックスのSQL条件文追加
         * @param    $effFlag
         * @return   $sqlAndEffCheck
         */
        public function creatAndEffCheckSQL($effFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAndEffCheckSQL");

            $sqlAndEffCheck = "";
            // 4ビット目 ON
            if( !( $effFlag & 8 ) )
            {
                $sqlAndEffCheck .= " AND eff_code != '適用中' ";
            }
            
            // 3ビット目 ON
            if( !( $effFlag & 4 ) )
            {
                $sqlAndEffCheck .= " AND eff_code != '適用予定' ";
            }
            
            // 2ビット目 ON
            if( !( $effFlag & 2 ) )
            {
                $sqlAndEffCheck .= " AND eff_code != '適用外' ";
            }
            
            // 1ビット目 ON
            if( !( $effFlag & 1 ) )
            {
                $sqlAndEffCheck .= " AND eff_code != '削除' ";
            }
            
            $Log->trace("END creatAndEffCheckSQL");

            return $sqlAndEffCheck;
        }

        /**
         * 在籍状況検索チェックボックスのSQL条件文追加
         * @param    $statusFlag
         * @return   $sqlAndStatusCheck
         */
        public function creatAndStatusCheckSQL($statusFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAndStatusCheckSQL");

            $sqlAndStatusCheck = "";

            if( !( $statusFlag & 4 ) )
            {
                $sqlAndStatusCheck .= " AND status != '在籍' ";
            }
            if( !( $statusFlag & 2 ) )
            {
                $sqlAndStatusCheck .= " AND status != '退職' ";
            }
            if( !($statusFlag & 1 ) )
            {
                $sqlAndStatusCheck .= " AND status != '試用' ";
            }

            $Log->trace("END creatAndStatusCheckSQL");

            return $sqlAndStatusCheck;
        }

    }
?>
