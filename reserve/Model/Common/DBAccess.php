<?php
    /**
     * @file    ログイン用DBアクセスクラス
     * @author  oota
     * @date    2016/06/23
     * @version 1.00
     * @note    ログイン用DBアクセスクラス
     */

    // BaseDBAccess.phpを読み込む
    require_once SystemParameters::$FW_COMMON_PATH . 'BaseDBAccess.php';

    /**
     * DBアクセスクラス
     * @note    DB共通アクセスクラス( 1アクション 1接続 )
     */
    class DBAccess extends BaseDBAccess
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ModelBaseのデストラクタ
            parent::__destruct();
        }

        /**
         * シーケンステーブル名取得
         * @param     $tabelName   Insert実施したテーブル名
         * @return    成功：シーケンステーブル名 失敗：空白
         */
        protected function getSequenceName($tabelName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getSequenceName");

            //シーケンス名
            $seqName = array(
                "m_organization" =>"m_organization_organization_id_seq",
                "m_organization_detail" =>"m_organization_detail_organization_detail_id_seq",
                "m_position" =>"m_position_position_id_seq",
                "m_security" =>"m_security_security_id_seq",
                "m_security_detail" =>"m_security_detail_security_detail_id_seq",
                "m_user" =>"m_user_user_id_seq",
                "m_user_detail" =>"m_user_detail_user_detail_id_seq",
            );

            // テーブル名から、シーケンス名を取得する
            if(array_key_exists("$tabelName",$seqName))
            {
                $Log->trace("END getSequenceName");
                return $seqName[$tabelName];
            }

            $Log->debug("シーケンステーブル未設定");
            $Log->trace("END getSequenceName");
            return "";
        }
    }
?>
