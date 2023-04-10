<?php
    /**
     * @file    共通セキュリティ(Model)
     * @author  USE Y.Sakata
     * @date    2016/06/06
     * @version 1.00
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */

    // FwSecurityProcessの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'Model/FwSecurityProcess.php';

    /**
     * セキュリティクラス
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */
    class SecurityProcess extends FwSecurityProcess
    {
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
         * 各マスタで、組織のトップIDを取得
         * @note     各マスタで、全組織IDを取得
         * @param    $organization_id   組織ID
         * @param    $tableName         マスタのテーブル名
         * @return   組織ID
         */
        public function getTopOrganizationID( $organization_id, $tableName = "" )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTopOrganizationID");
            
            // テーブル名を取得
            if( $tableName == "" )
            {
                // URLからコントローラ名を取得
                $keyURL = $this->getUrlKey();
                if( isset( $_SESSION["P_ACCESS_AUTHORITY_TABLE"][$keyURL] ) )
                {
                    $tableName = $_SESSION["P_ACCESS_AUTHORITY_TABLE"][$keyURL];
                }
            }
            
            // 組織IDと関係ない画面の場合、入力された組織IDを返却
            if( $tableName == "" )
            {
                // URLからコントローラ名を取得
                $Log->trace("END getTopOrganizationID");
                return $organization_id;
            }
            
            $sql  =  ' SELECT COUNT(*) as count FROM '
                 .   $tableName
                 .   ' WHERE organization_id = :organization_id ';

            $levelOrganList = $this->getLevelOrganizationList( $organization_id );
            $ret = $organization_id;
            foreach($levelOrganList as $value )
            {
                $ret = $value['organization_id'];
                $parametersArray = array( 
                    ':organization_id' => $value['organization_id'],
                );
                
                $result = $DBA->executeSQL($sql, $parametersArray);

                if( $result === false )
                {
                    $Log->trace("END getLevelOrganizationList");
                    break;
                }

                $cnt = 0;
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    $cnt = $data['count'];
                }

                if( $cnt > 0 )
                {
                    break;
                }
                
            }
            $Log->trace("END getTopOrganizationID");
            return $ret;
        }

        /**
         * キーURL取得
         * @note     アクセスされたURLから、キーURLを取得する
         * @return   URLキー
         */
        protected function getUrlKey()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getUrlKey");

            // URLからコントローラ名を取得
            $pathNames = explode( "param=", $_SERVER['REQUEST_URI'] );
            $controllerNames = explode( "/", $pathNames[1] );
            $controllerName = $controllerNames[0];

            $keyURL = '';
            // 売上の管理機能画面のメニューがあるか
            if( isset( $_SESSION["P_MANAGEMENT_MENU"] ) )
            {
                $keyURL = $this->getControllerUrlKey( $_SESSION["P_MANAGEMENT_MENU"], $controllerName );
                if( '' !== $keyURL )
                {
                    $Log->trace("END getUrlKey");
                    return $keyURL;
                }
            }

            // 売上報告書画面のメニューがあるか
            if( isset( $_SESSION["REPORT_MENU"] ) )
            {
                $keyURL = $this->getControllerUrlKey( $_SESSION["REPORT_MENU"], $controllerName );
                if( '' !== $keyURL )
                {
                    $Log->trace("END getUrlKey");
                    return $keyURL;
                }
            }

            // 売上帳票画面のメニューがあるか
            if( isset( $_SESSION["LEDGER_SHEET_MENU"] ) )
            {
                $keyURL = $this->getControllerUrlKey( $_SESSION["LEDGER_SHEET_MENU"], $controllerName );
                if( '' !== $keyURL )
                {
                    $Log->trace("END getUrlKey");
                    return $keyURL;
                }
            }

            // 売上トップ画面のメニューがあるか
            if( isset( $_SESSION["P_TOP_MENU"] ) )
            {
                $keyURL = $this->getControllerUrlKey( $_SESSION["P_TOP_MENU"], $controllerName );
            }
            
            $Log->trace("END getUrlKey");

            return $keyURL;
        }
    }

?>
