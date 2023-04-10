<?php
    /**
     * @file    プルダウン取得(Model)
     * @author  millionet oota
     * @date    2017/01/26
     * @version 1.00
     * @note    共通で使用するプルダウンモデルの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'Model/FwSetPulldown.php';
    require_once 'Model/Common/SecurityProcess.php';

    /**
     * プルダウン取得クラス
     * @note    共通で使用するプルダウンモデルの処理を定義
     */
    class SetPulldown extends FwSetPulldown
    {
        protected $securityProcess = null; ///< セキュリティクラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseModelのコンストラクタ
            parent::__construct();
            $this->securityProcess = new SecurityProcess();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseModelのデストラクタ
            parent::__destruct();
        }

        /**
         * 組織に紐づく登録用プルダウンリスト取得
         * @note    登録組織に紐づく選択肢のみ取得するプルダウンを作成する
         * @param   $target_id(対象組織ID)
         * @param   $columnId(項目ID)
         * @param   $columnName(項目名)
         * @param   $tableName(検索対象テーブル名)
         * @return  SQLの実行結果
         */
        public function getRegPulldownList($target_id, $columnId = "", $columnName = "",  $tableName = "" )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getRegPulldownList");

            $sql = ' SELECT ';
            $sql .= $columnId;
            $sql .= ' , ';
            $sql .= $columnName;
            $sql .= ' FROM ';
            $sql .= $tableName; 
            $sql .= ' WHERE organization_id = :organization_id AND is_del = :is_del ORDER BY disp_order ';
            $parametersArray = array(
                ':organization_id' => $target_id,
                ':is_del'          => 0, 
            );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $pulldownList = array();

            if( $result === false )
            {
                $Log->trace("END getrRegPositionList");
                return $pulldownList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($pulldownList, $data);
            }

            $initial = array($columnId => '', $columnName => '',);
            array_unshift($pulldownList, $initial);

            $Log->trace("END getRegPulldownList");

            return $pulldownList;
        }

        /**
         * アクセス可能な組織ID一覧取得
         * @note     セキュリティ設定用に、アクセス可能な組織IDの一覧を取得
         * @param    $urlKey        該当機能URL
         * @param    $authority     アクセス権限名
         * @return   上位組織ID一覧(section_id/section_name)
         */
        public function getAccessIDList( $urlKey, $authority )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessIDList");
            
            // アクセス可能な組織IDリストを作成
            $mAccessList = $this->securityProcess->getAccessAuthorityListForUrl( $urlKey );
            
            $organList = $this->securityProcess->getAccessDisplayOrderHierarchy( $mAccessList[$authority], $_SESSION["M_ACCESS_AUTHORITY_TABLE"][$urlKey] );

            $Log->trace("END getAccessIDList");
            
            return $organList;
        }
        
        /**
         * アクセス可能な組織ID一覧取得
         * @note     セキュリティ設定用に、アクセス可能な組織IDの一覧を取得
         * @param    $urlKey        該当機能URL
         * @param    $authority     アクセス権限名
         * @return   上位組織ID一覧(section_id/section_name)
         */
        public function getAccessIDListALL( $urlKey, $authority )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessIDList");
            
            $organList = $this->securityProcess->getAccessDisplayOrderHierarchy( 1, $_SESSION["M_ACCESS_AUTHORITY_TABLE"][$urlKey] );

            $Log->trace("END getAccessIDList");
            
            return $organList;
        }

        /**
         * アクセス可能な組織ID一覧取得(適用中のみとする)
         * @note     セキュリティ設定用に、アクセス可能な組織IDの一覧を取得
         * @param    $organList     アクセス可能組織IDリスト
         * @return   アクセス可能な組織ID一覧(適用中のみ)
         */
        protected function getOrganList( $organList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOrganList");
            
            $ret = array();
            // アクセス可能な組織IDリストを作成
            foreach($organList as $organ)
            {
                if( '適用中' === $organ['eff_code'] && ( false === array_search( $organ, $ret ) ) )
                {
                    array_push( $ret, $organ );
                }
            }

            $Log->trace("END getOrganList");
            
            return $ret;
        }
    }

?>
