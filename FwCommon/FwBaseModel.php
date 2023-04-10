<?php
    /**
     * @file    FW共通モデル(Model)
     * @author  USE Y.Sakata
     * @date    2016/06/23
     * @version 1.00
     * @note    FW共通で使用するモデルの処理を定義
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';
    // SQL自動作成クラス
    require_once '../FwCommon/Model/GenerationSQLSentence.php';

    /**
     * 各モデルの基本クラス
     * @note    共通で使用するモデルの処理を定義
     */
    class FwBaseModel extends FwBaseClass
    {
        // DBアクセスクラス
        protected $DBA = null;                 ///< DBアクセスクラス
        protected $Lastid = 0;                 ///< ラストID
        public    $generationSQL = null;       ///< SQL生成クラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();

            global $DBA;  // グローバル変数宣言
            // DBAccessClassの初期化
            $DBA                  = new DBAccess();
            $this->generationSQL  = new GenerationSQLSentence();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseClassのデストラクタ
            parent::__destruct();

            global $DBA;  // グローバル変数宣言
            $DBA = null;
        }

        /**
         * インサート後のラストIDを返す
         * @return   ラストID
         */
        public function getLastid()
        {
            global $Lastid, $Log; // グローバル変数宣言
            $Log->trace("START getSectionListId");

            $Log->trace("END getSectionListId");

            return $Lastid;
        }

        /**
         * 1テーブル更新
         * @param    $sql           実行するSQL文
         * @param    $parameters    実行するSQL文のパラメータ
         * @param    $tableName     INSERTしたテーブル名を指定する(その他は指定なし)
         * @return   実行結果
         */
        protected function executeOneTableSQL( $sql, $parameters, $tableName = "" )
        {
            global $DBA, $Log, $Lastid; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            if( $DBA->beginTransaction() )
            {
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters, true) )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "SQL実行に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
                
                // Lastidの更新
                if( "" !== $tableName )
                {
                    $Lastid = $DBA->lastInsertId( $tableName );
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END executeOneTableSQL");
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END executeOneTableSQL");

            return "MSG_FW_OK";
        }
        

        /**
         * 閲覧可能組織情報のみに、一覧を編集
         * @param    $viewOrganList     閲覧可能組織リスト(組織ID/組織詳細ID/上位組織ID/適用開始日)
         * @param    $dataList          編集対象の情報一覧
         * @return   $viewList          閲覧可能組織の情報一覧
         */
        protected function creatAccessControlledList( $viewOrganList, $dataList )
        {
            global $Log; // グローバル変数宣言

            $Log->trace("START creatAccessControlledList");
            
            $viewList = array();
            if( empty($viewOrganList[0]['organization_id']) || empty($dataList[0]['organization_id']) )
            {
                $Log->trace("END creatAccessControlledList");
                return $viewList;
            }

            foreach($viewOrganList as $viewable)
            {
                foreach($dataList as $data)
                {
                    if(   $viewable['organization_id'] == $data['organization_id'] && 
                        ( false === array_search( $data, $viewList ) ) )
                    {
                        array_push($viewList, $data);
                    }
                }
            }
            
            $Log->trace("END creatAccessControlledList");

            return $viewList;
        }

        /**
         * 画面ソート時の閲覧範囲組織限定のためのSQL文作成
         * @param    $searchedColumn    検索する組織IDある表名 ( 例： ' AND organization. ' )
         * @return   $sqlWhereIn
         */
        protected function creatSqlWhereInConditions($searchedColumn)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlWhereInConditions");

            $sqlWhereIn = "";
            if( 0 < count( $_SESSION["REFERENCE"] ) && !empty($_SESSION["REFERENCE"][0]['organization_id']) )
            {
                $sqlWhereIn = $searchedColumn;
                $sqlWhereIn .= 'organization_id IN ( ';
                foreach($_SESSION["REFERENCE"] as $viewable)
                {
                    $sqlWhereIn .=  $viewable['organization_id'] . ', ';
                }
                $sqlWhereIn = substr($sqlWhereIn, 0, -2);
                $sqlWhereIn .= ' ) ';
            }

            $Log->trace("END creatSqlWhereInConditions");

            return $sqlWhereIn;
        }

        /**
         * 一覧表編集削除権限の判定
         * @note     画面遷移する機能で対象のデータの設定組織に対して、閲覧ユーザに編集/削除権限がない場合入力画面への遷移を制御するためのフラグを設ける
         * @param    $dataList
         * @return   $list(disabledの有無を追加したリスト)
         */
        protected function updateControl($dataList)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START updateControl");

            foreach($_SESSION["REGISTRATION"] as $reg)
            {
                $regArray[] = $reg['organization_id'];
            }
            foreach($_SESSION["DELETE"] as $del)
            {
                $delArray[] = $del['organization_id'];
            }

            foreach($dataList as $data)
            {
                $updateDisabled = "";
                if(!in_array($data['organization_id'], $regArray) && !in_array($data['organization_id'], $delArray))
                {
                    $updateDisabled = "disabled";
                }

                if( ( $data['eff_code'] === $Log->getMsgLog('MSG_FW_STATE_NOT_APPLICABLE') ) || ( $data['eff_code'] === $Log->getMsgLog('MSG_FW_STATE_DELETE') ) )
                {
                    $updateDisabled = "disabled";
                }

                $disabledCheck = array('updateDisabled' => $updateDisabled,);
                $list[] = array_merge($data, $disabledCheck);
            }

            $Log->trace("END updateControl");

            return $list;
        }
        
        /**
         * 専用URLから、スキーマ名を取得
         * @param     $companyCode   企業コード
         * @return    DBスキーマ名
         */
        protected function getSchema( $companyCode )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSchema");
            
            $_SESSION["SCHEMA"] = 'public';
            
            $sql = 'SELECT company_name FROM m_company_contract WHERE company_code = :company_code;';
            $array = array(':company_code' => $companyCode );
            $result = $DBA->executeSQL($sql, $array);

            $schema_name = "";
            if( false === $result )
            {
                // スキーマなし
                $Log->trace("END   getSchema");
                return "";
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $schema_name = $data['company_name'];
            }

            $Log->trace("END   getSchema");
            return $schema_name;
        }

        /**
         * 配列の中の重複している値を削除してキーの振り直しを行う
         * @param     $overlapArray
         * @return    $uniqueArray
         */
        protected function setUniqueArray( $overlapArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setUniqueArray");

            //配列で重複している物を削除する
            $unique = array_unique($overlapArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $uniqueArray = array_values($unique);

            $Log->trace("END setUniqueArray");
            return $uniqueArray;
        }

    }

?>
