<?php
    /**
     * @file      POS商品セット構成マスタ
     * @author    川橋
     * @date      2018/12/27
     * @version   1.00
     * @note      POS商品セット構成マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 役職クラス
     * @note   役職マスタテーブルの管理を行う
     */
    class Mst5101 extends BaseModel
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
         * POS商品セット構成マスタ一覧画面一覧表
         * @param    $postArray
         * @return   成功時：$mst5101List / 失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $mst5101DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $mst5101DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                
                array_push( $mst5101DataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $mst5101DataList;
        }
        
        /**
         * POS商品セット構成マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/役職名/就業規則/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // POS商品マスタ存在チェック
            if ($this->is_ProdExists($postArray) === FALSE) {
                $Log->warn("POS商品在庫マスタの登録に失敗しました。");
                $errMsg = "POS商品在庫マスタの登録に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_NO_PROD_NOT_FOUND";
            }
            
            // POS商品マスタ登録
            $parameters = array(
                ':insuser_cd'               => 'mportal',
                ':insdatetime'              => date('Y/m/d H:i:s'),
                ':upduser_cd'               => 'mportal',
                ':upddatetime'              => date('Y/m/d H:i:s'),
                ':data_make_kbn'            => '0',
                ':lan_kbn'                  => '0',
                ':connect_kbn'              => '0',
                ':organization_id'          => $postArray['organization_id'],
                ':prod_cd'                  => $postArray['prod_cd'],
                ':setprod_cd'               => $postArray['setprod_cd'],
                ':setprod_amount'           => $postArray['setprod_amount'] == '' ? '0' : str_replace(',', '', $postArray['setprod_amount']),
                ':data_upd_kbn'             => '0'
            );

            $sql = 'INSERT INTO mst5101( '
                . '   insuser_cd'
                . ' , insdatetime'
                . ' , upduser_cd'
                . ' , upddatetime'
                . ' , data_make_kbn'
                . ' , lan_kbn'
                . ' , connect_kbn'
                . ' , organization_id'
                . ' , prod_cd'
                . ' , setprod_cd'
                . ' , setprod_amount'
                . ' , data_upd_kbn'
                . ' ) VALUES ('
                . '   :insuser_cd'
                . ' , :insdatetime'
                . ' , :upduser_cd'
                . ' , :upddatetime'
                . ' , :data_make_kbn'
                . ' , :lan_kbn'
                . ' , :connect_kbn'
                . ' , :organization_id'
                . ' , :prod_cd'
                . ' , :setprod_cd'
                . ' , :setprod_amount'
                . ' , :data_upd_kbn'
                . ' )';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                $Log->warn("POS商品在庫マスタの登録に失敗しました。");
                $errMsg = "POS商品在庫マスタの登録に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END addNewData");

            return "MSG_BASE_0000";
        }

        /**
         * POS商品セット構成マスタ登録データ修正
         * @param    $postArray   入力パラメータ(役職ID/コード/組織ID/役職名/就業規則/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            // POS商品マスタ存在チェック
            if ($this->is_ProdExists($postArray) === FALSE) {
                $Log->warn("POS商品セット構成マスタの更新に失敗しました。");
                $errMsg = "POS商品セット構成マスタの更新に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END modUpdateData");
                return "MSG_FW_NO_PROD_NOT_FOUND";
            }
            
            $parameters = array(
                ':key_organization_id'      => $postArray['organization_id'],
                ':key_prod_cd'              => $postArray['prod_cd'],
                ':key_setprod_cd'           => $postArray['setprod_cd_bef'],
                ':upduser_cd'               => 'mportal',
                ':upddatetime'              => date('Y/m/d H:i:s'),
                ':data_make_kbn'            => '0',
                ':lan_kbn'                  => '0',
                ':connect_kbn'              => '0',
                ':setprod_cd'               => $postArray['setprod_cd'],
                ':setprod_amount'           => $postArray['setprod_amount'] == '' ? '0' : str_replace(',', '', $postArray['setprod_amount']),
            );

            // POS商品マスタテーブル更新
            $sql = 'UPDATE mst5101 SET'
                . '   upduser_cd            = :upduser_cd'
                . ' , upddatetime           = :upddatetime'
                . ' , data_make_kbn         = :data_make_kbn'
                . ' , lan_kbn               = :lan_kbn'
                . ' , connect_kbn           = :connect_kbn'
                . ' , setprod_cd            = :setprod_cd'
                . ' , setprod_amount        = :setprod_amount'
                . ' WHERE organization_id   = :key_organization_id'
                . ' AND   prod_cd           = :key_prod_cd'
                . ' AND   setprod_cd        = :key_setprod_cd';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                $Log->warn("POS商品セット構成マスタの更新に失敗しました。");
                $errMsg = "POS商品セット構成マスタの更新に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END modUpdateData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END modUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * POS商品セット構成マスタ登録データ削除
         * @param    $postArray   入力パラメータ(役職ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            // POS商品セット構成マスタ
            $sql = ' DELETE FROM mst5101'
                    . ' WHERE organization_id   = :key_organization_id'
                    . ' AND   prod_cd           = :key_prod_cd'
                    . ' AND   setprod_cd        = :key_setprod_cd ';

            $parameters = array(
                ':key_organization_id'      => $postArray['organization_id'],
                ':key_prod_cd'              => $postArray['prod_cd'],
                ':key_setprod_cd'           => $postArray['setprod_cd_bef'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー
                $Log->warn("POS商品マスタの削除に失敗しました。");
                $errMsg = "POS商品マスタの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END delUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * POS商品セット構成マスタの検索用コードのプルダウン
         * @return   POS商品コードリスト(コード) 
         */
        public function getSearchProdList( $organization_id )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchProdList");

            $sql = ' SELECT prod_cd, prod_nm, prod_kn FROM mst0201 '
                 . ' WHERE disabled = :disabled '
                 . ' AND   organization_id = :organization '
                 . ' ORDER BY prod_cd ';
            $parametersArray = array(
                ':disabled'     => '0',
                ':organization' => $organization_id,
            );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $prodList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchProdList");
                return $prodList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($prodList, $data);
            }

            $Log->trace("END getSearchProdList");
            return $prodList;
        }

        /**
         * POS商品セット構成マスタ一覧取得SQL文作成
         * @param    $postArray
         * @param    $searchArray
         * @return   POS商品セット構成マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = " SELECT"
                    . "  mst5101.organization_id                          as organization_id"
                    . " ,v_organization.organization_name                 as organization_name"
                    . " ,v_organization.abbreviated_name                  as abbreviated_name"
                    . " ,v_organization.disp_order                        as o_disp_order"
                    . " ,mst5101.prod_cd                                  as prod_cd"
                    . " ,(SELECT prod_nm FROM mst0201 WHERE disabled = '0' AND organization_id = mst5101.organization_id AND prod_cd = mst5101.prod_cd) AS prod_nm"
                    . " ,(SELECT prod_kn FROM mst0201 WHERE disabled = '0' AND organization_id = mst5101.organization_id AND prod_cd = mst5101.prod_cd) AS prod_kn"
                    . " ,mst5101.setprod_cd                               as setprod_cd"
                    . " ,(SELECT prod_nm FROM mst0201 WHERE disabled = '0' AND organization_id = mst5101.organization_id AND prod_cd = mst5101.setprod_cd) AS setprod_nm"
                    . " ,(SELECT prod_kn FROM mst0201 WHERE disabled = '0' AND organization_id = mst5101.organization_id AND prod_cd = mst5101.setprod_cd) AS setprod_kn"
                    . " ,TO_CHAR(mst5101.setprod_amount, 'FM999,999,999') as setprod_amount"
                    . " FROM mst5101"
                    . " LEFT OUTER JOIN v_organization"
                    . " ON (v_organization.organization_id = mst5101.organization_id AND (v_organization.eff_code = '適用中' OR v_organization.eff_code = '適用予定'))"
                    . " WHERE 1 = 1 ";

            if( !empty( $postArray['organizationID'] ) )
            {
                $sql .= " AND mst5101.organization_id = :organizationID ";
                $organizationIDArray = array(':organizationID' => $postArray['organizationID'],);
                $searchArray = array_merge($searchArray, $organizationIDArray);
            }
            if( !empty( $postArray['prodCode'] ) )
            {
                $sql .= " AND mst5101.prod_cd = :prod_cd ";
                $codeIDArray = array(':prod_cd' => $postArray['prodCode']);
                $searchArray = array_merge($searchArray, $codeIDArray);
            }
            if( !empty( $postArray['prodName'] ) )
            {
                $sql = $sql
                        . " AND EXISTS ("
                        . "     SELECT prod_cd"
                        . "         FROM mst0201"
                        . "     WHERE organization_id = mst5101.organization_id"
                        . "     AND   prod_cd = mst5101.prod_cd"
                        . "     AND   disabled = :disabled"
                        . "     AND   prod_nm LIKE :prod_nm"
                        . " ) ";
                $codeIDArray = array(
                    ':disabled'     => '0',
                    ':prod_nm'      => '%'.$postArray['prodName'].'%'
                );
                $searchArray = array_merge($searchArray, $codeIDArray);
            }
            if( !empty( $postArray['setprodCode'] ) )
            {
                $sql .= " AND mst5101.setprod_cd = :setprod_cd ";
                $codeIDArray = array(':setprod_cd' => $postArray['setprodCode']);
                $searchArray = array_merge($searchArray, $codeIDArray);
            }
            if( !empty( $postArray['setprodName'] ) )
            {
                $sql = $sql
                        . " AND EXISTS ("
                        . "     SELECT prod_cd"
                        . "         FROM mst0201"
                        . "     WHERE organization_id = mst5101.organization_id"
                        . "     AND   prod_cd = mst5101.setprod_cd"
                        . "     AND   disabled = :disabled"
                        . "     AND   prod_nm LIKE :setprod_nm"
                        . " ) ";
                $codeIDArray = array(
                    ':disabled'     => '0',
                    ':setprod_nm'   => '%'.$postArray['setprodName'].'%'
                );
                $searchArray = array_merge($searchArray, $codeIDArray);
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * POS商品セット構成マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   POS商品セット構成マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY o_disp_order, prod_cd, setprod_cd ';

            // ソート条件作成
            $sortSqlList = array(
                                1       =>  ' ORDER BY abbreviated_name DESC,   o_disp_order,   organization_id,    prod_cd, setprod_cd',                   // 組織名の降順
                                2       =>  ' ORDER BY abbreviated_name,        o_disp_order,   organization_id,    prod_cd, setprod_cd',                   // 組織名の昇順
                                3       =>  ' ORDER BY prod_cd DESC,            setprod_cd,     o_disp_order,       organization_id',                       // 商品コードの降順
                                4       =>  ' ORDER BY prod_cd,                 setprod_cd,     o_disp_order,       organization_id',                       // 商品コードの昇順
                                5       =>  ' ORDER BY prod_nm DESC,            prod_cd,        setprod_cd,         o_disp_order,       organization_id',   // 商品名の降順
                                6       =>  ' ORDER BY prod_nm,                 prod_cd,        setprod_cd,         o_disp_order,       organization_id',   // 商品名の昇順
                                7       =>  ' ORDER BY setprod_cd DESC,         prod_cd,        o_disp_order,       organization_id',                       // セット商品コードの降順
                                8       =>  ' ORDER BY setprod_cd,              prod_cd,        o_disp_order,       organization_id',                       // セット商品コードの昇順
                                9       =>  ' ORDER BY setprod_nm DESC,         setprod_cd,     prod_cd,            o_disp_order,       organization_id',   // セット商品名の降順
                                10      =>  ' ORDER BY setprod_nm,              setprod_cd,     prod_cd,            o_disp_order,       organization_id',   // セット商品名の昇順
                            );
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");
            return $sql;
        }
        
        /**
         * POS商品マスタ存在チェック
         * @param type $postArray
         * @return boolean
         */
        private function is_ProdExists( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START is_ProdExists");
            
            // 13桁数字以外はエラー(商品コード)
            if (preg_match('/^\d{13}/', $postArray['prod_cd']) !== 1) {
                $Log->trace("END is_ProdExists");
                return FALSE;
            }

            // 13桁数字以外はエラー(セット商品コード)
            if (preg_match('/^\d{13}/', $postArray['setprod_cd']) !== 1) {
                $Log->trace("END is_ProdExists");
                return FALSE;
            }

            // POS商品存在チェック
            $sql = "SELECT COUNT(*) AS cnt"
                    . " FROM mst0201"
                    . " WHERE disabled = :disabled"
                    . " AND   organization_id = :organization_id"
                    . " AND   prod_cd = :prod_cd";

            // 検索キー(ベース商品)
            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $postArray['organization_id'],
                ':prod_cd'          => $postArray['prod_cd']
            );
            // SQL実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、FALSEを返す
            if( $result === false )
            {
                $Log->trace("END is_ProdExists");
                return FALSE;
            }

            // 取得したデータ群を配列に格納
            $data = $result->fetch(PDO::FETCH_ASSOC);
            if (intval($data['cnt']) === 0) {
                $Log->trace("END is_ProdExists");
                return FALSE;
            }

            // 検索キー(セット商品)
            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $postArray['organization_id'],
                ':prod_cd'          => $postArray['setprod_cd']
            );
            // SQL実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、FALSEを返す
            if( $result === false )
            {
                $Log->trace("END is_ProdExists");
                return FALSE;
            }

            // 取得したデータ群を配列に格納
            $data = $result->fetch(PDO::FETCH_ASSOC);
            if (intval($data['cnt']) === 0) {
                $Log->trace("END is_ProdExists");
                return FALSE;
            }

            $Log->trace("END getListData");
            return TRUE;
        }
    }
?>
