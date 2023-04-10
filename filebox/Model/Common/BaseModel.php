<?php
    /**
     * @file    共通モデル(Model)
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    共通で使用するモデルの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseModel.php';
    require_once 'Model/Common/SecurityProcess.php';
    require_once 'Model/Common/SetPulldown.php';

    /**
     * 各モデルの基本クラス
     * @note    共通で使用するモデルの処理を定義
     */
    class BaseModel extends FwBaseModel
    {
        protected $securityProcess = null;       ///< セキュリティクラス
        public    $setPulldown = null;           ///< プルダウンクラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseModelのコンストラクタ
            parent::__construct();
            $this->securityProcess       = new SecurityProcess();
            $this->setPulldown           = new SetPulldown();
            $this->securityProcess->getAccessViewOrder();
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
         * 使用中のマスタ情報を取得
         * @param   $idName（各機能のシーケンスID名）
         * @return  呼び出し先のシーケンスIDリスト
         */
        public function getInUseCheckList($idName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getInUseCheckList");

            $sql = 'SELECT ud.organization_id, ud.position_id'
                . ' , ud.employment_id, ud.section_id'
                . ' , ud.security_id FROM m_user_detail ud';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $inUseCheckArray = array();
            
            if( $result === false )
            {
                $Log->trace("END getInUseCheckList");
                return $inUseCheckArray;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($inUseCheckArray, $data[$idName]);
            }
            
            //配列で重複している物を削除する
            $unique = array_unique($inUseCheckArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $inUseCheckList = array_values($unique);

            $Log->trace("END getInUseCheckList");

            return $inUseCheckList;
        }
        
        /**
         * 従業員マスタで使用している手当マスタ情報を取得
         * @param   $allowanceIdName（各機能のシーケンスID名）
         * @return  呼び出し先のシーケンスIDリスト
         */
        public function getInUseAllowanceCheckList($allowanceIdName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getInUseAllowanceCheckList");

            $sql = 'SELECT ua.user_allowance_id, ua.allowance_id'
                . ' FROM m_user_allowance ua';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $inUseCheckArray = array();
            
            if( $result === false )
            {
                $Log->trace("END getInUseAllowanceCheckList");
                return $inUseCheckArray;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($inUseCheckArray, $data[$allowanceIdName]);
            }
            
            //配列で重複している物を削除する
            $unique = array_unique($inUseCheckArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $inUseCheckList = array_values($unique);

            $Log->trace("END getInUseAllowanceCheckList");

            return $inUseCheckList;
        }
        
        /**
         * 指定配列の重複削除
         * @param    $array           プルダウンリスト
         * @param    $column          配列のキーとする名称
         * @return   $uniqueArray     重複削除したリスト
         */
        protected function getUniqueArray($array, $column)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getUniqueArray");
            
            $uniqueArray = $this->setPulldown->getUniqueArray($array, $column);
            
            $Log->trace("END getUniqueArray");
            return $uniqueArray;
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
            global $Log; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            // 親クラスの呼び出し
            $ret = parent::executeOneTableSQL( $sql, $parameters, $tableName );

            // 更新結果がOKである
            if( "MSG_FW_OK" === $ret )
            {
                // 勤怠システム用のOKフラグに修正
                $ret = "MSG_BASE_0000";
            }

            $Log->trace("END executeOneTableSQL");
            return $ret;
        }
    }

?>
