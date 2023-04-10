<?php
    /**
     * @file      セキュリティマスタ
     * @author    USE S.Nakamura
     * @date      2016/07/14
     * @version   1.00
     * @note      セキュリティマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * セキュリティマスタクラス
     * @note   セキュリティマスタの初期設定を行う
     */
    class error_management extends BaseModel
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
         * セキュリティマスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ
         * @return   成功時：$securityDataList 失敗：無
         */
        public function getData()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = " select * from public.m_transfert_error where resolved = false order by company_nm,organization_id,err_time ";

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getData");
                return $datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $datas, $data);
            }

            $Log->trace("END getData");

            // 一覧表を返す
            return $datas;
        }
        
        public function setresolve(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START setresolve"); 
            $sql  = "";
            $sql .= " update public.m_transfert_error set ";
            $sql .= "  resolved = true, resolved_time = now() ";
            $sql .= " where file_nm = :file_nm ";
            $sql .= "   and company_nm = :company_nm ";
            $sql .= "   and organization_id = :organization_id ";
            $sql .= "   and to_char(err_time,'YYYY-MM-DD HH24:MI:SS') = :err_time ";
            $sql .= "   and resolved = false ";
            $param=[];
            $param[':file_nm'] = $_POST['filename'];
            $param[':company_nm'] = $_POST['comp'];
            $param[':organization_id'] = $_POST['org_id'];
            $param[':err_time'] = $_POST['date'];
            $result = $DBA->executeSQL_no_searchpath($sql, $param);
            $Log->trace("END setresolve");
        }

    }

?>