<?php
    /**
     * @file    日報DBアクセスクラス
     * @author  millionet oota
     * @date    2017/01/26
     * @version 1.00
     * @note    勤怠用DBアクセスクラス
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
                "t_top_message" => "t_daily_report_daily_report_id_seq"
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
