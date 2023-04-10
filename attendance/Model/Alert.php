<?php
    /**
     * @file      アラートマスタ
     * @author    USE R.dendo
     * @date      2016/06/24
     * @version   1.00
     * @note      アラートマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * アラートマスタクラス
     * @note   アラートマスタテーブルの管理を行うの初期設定を行う
     */
    class Alert extends BaseModel
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
         * アラートマスタ一覧画面一覧表
         * @return   成功時：$alertList(is_labor_standards_act/is_labor_standards_act_warning/warning_value)  失敗：無
         */
        public function getListData()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $sql = $this->creatSQL();

            $parametersArray = array();
            $result = $DBA->executeSQL( $sql, $parametersArray);

            $alertDataList = array();
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $alertDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $alertDataList, $data);
            }

            $Log->trace("END getListData");

            return $alertDataList;
        }

        /**
         * アラートマスタ登録データ修正
         * @param    $postArray   入力パラメータ(is_labor_standards_act/is_labor_standards_act_warning/warning_value)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_alert SET'
                . '   is_labor_standards_act = :is_labor_standards_act'
                . ' , is_labor_standards_act_warning = :is_labor_standards_act_warning'
                . ' , warning_value = :warning_value'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE alert_id = :alert_id AND update_time = :update_time ';

            $parameters = array(
                ':is_labor_standards_act'            => $postArray['is_labor_standards_act'],
                ':is_labor_standards_act_warning'    => $postArray['is_labor_standards_act_warning'],
                ':warning_value'                     => $postArray['warning_value'],
                ':update_user_id'                    => $postArray['user_id'],
                ':update_organization'               => $postArray['organization'],
                ':alert_id'                          => $postArray['alertID'],
                ':update_time'                       => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * アラートマスタ一覧取得SQL文作成
         * @return   アラートマスタ一覧取得SQL文
         */
        private function creatSQL( )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT alert_id, is_labor_standards_act, is_labor_standards_act_warning, warning_value, update_time '
                 . ' FROM m_alert';
            $Log->trace("END creatSQL");
            
            return $sql;
        }
    }
?>
