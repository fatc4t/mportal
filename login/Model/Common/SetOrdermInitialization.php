<?php
    /**
     * @file    薬剤発注管理 初期化用クラス(Model)
     * @author  USE K.Kanda(media-craft.co.jp)
     * @date    2018/01/22
     * @version 1.00
     * @note    薬剤発注管理で使用する初期データの設定について定義する
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';

    /**
     * セキュリティクラス
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */
    class SetOrdermInitialization extends FwBaseClass
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
         * 薬剤発注管理システムの初期化
         * @note     ログイン時に行う勤怠処理の初期化
         * @return   無
         */
        public function setOrdermInit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setOrderInit");

            // 薬剤発注管理管理機能用のメニューリスト設定
            $this->setMenuList();
            // 薬剤発注管理機能で使用するマスタごとの参照テーブル一覧設定
            //$_SESSION["A_ACCESS_AUTHORITY_TABLE"] = $this->getAccessAuthorityTableList();
            $Log->trace("END setOrderInit");
        }

        /**
         * 管理機能用のメニューリスト取得
         * @note     管理機能用のメニューリスト取得
         * @return   管理メニューパスと画面名のリスト
         */
        private function setMenuList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setMenuList");


            $_SESSION["M_ORDERM_MENU"] = array(
                                              SystemParameters::$V_M_DATAMAINTENANCE           => "発注データメンテナンス",
                                              );

            $Log->trace("END setMenuList");
        }

        /**
         * マスタごとの参照テーブル一覧取得
         * @note     マスタごとの参照テーブル一覧取得
         * @return   管理メニューパスとテーブルのリスト
         */
        private function getAccessOrderTableList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessOrderTableList");

            $ret = array(
//                            SystemParameters::$V_A_SECTION               => "m_section",
                        );

            $Log->trace("END getAccessOrderTableList");
            return $ret;
        }
        /**
         * 全M-PORTALシステムのアクセス権限(参照)を取得
         * @note     全M-PORTALシステムのアクセス権限(参照)を取得
         * @return   全M-PORTALシステムのアクセス権限(参照)リスト
         */
        private function getAccessMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAccessMenuList");

            $sql   =  ' SELECT ma.url, ms.reference '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ms.security_id = :security_id AND ms.reference IN (1,2,3,4,5,9) '
                  .   ' ORDER BY ma.disp_order ';

            $parametersArray = array(
                ':security_id'   => $_SESSION["SECURITY_ID"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessMenuList = array();
            if( $result === false )
            {
                $Log->trace("END getAccessMenuList");
                return $accessMenuList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $accessMenu = array( $data['url']   => $data['reference'] );
                $accessMenuList = array_merge ($accessMenuList, $accessMenu);
            }

            $Log->trace("END getAccessMenuList");

            return $accessMenuList;
        }
    }

?>
