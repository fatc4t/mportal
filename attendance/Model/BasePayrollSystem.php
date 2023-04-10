<?php
    /**
     * @file      給与システム連携設定ベースマスタ
     * @author    USE S.Nakamura
     * @date      2016/07/22
     * @version   1.00
     * @note      給与システム連携設定マスタテーブルの管理(表示)を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 給与システム連携ベースクラス
     * @note   給与システム連携マスタテーブルの管理(表示)を行う
     */
    class BasePayrollSystem extends BaseModel
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
         * 給与システム連携マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ
         * @return   成功時：$payrollSystemList  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $payrollSysDataList = array();
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $payrollSysDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $payrollSysDataList, $data);
            }

            $payrollSystemList = array();
            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $payrollSystemList = $payrollSysDataList;
            }
            else
            {
                $payrollSystemList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $payrollSysDataList);
                if( $postArray['sort'] == 1 )
                {
                    $payrollSystemList = array_reverse($payrollSystemList);
                }
            }
            $Log->trace("END getListData");
 
  
            return $payrollSystemList;
        }

        /**
         * 出力項目一覧の取得
         * @param    $postArray   入力パラメータ
         * @return  成功時：$outputItemList  失敗：無
         */
        public function getOutputItemDeteilList( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOutputItemDeteilList");
            
            // 詳細取得
            $outputItemList = $this->getEditOutputItemDeteilList( $postArray );

            $Log->trace("END getOutputItemDeteilList");

            return $outputItemList;
        }
        
        /**
         * 給与システム連携マスタ一覧取得SQL文作成
         * @param    $postArray                 
         * @param    $searchArray               検索条件用パラメータ
         * @return   給与システム連携マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sqlWhereIn = $this->creatSqlWhereIn();
            $sqlGroup   = $this->creatSqlGroup();
            $sqlAnd     = $this->creatSqlAnd($postArray,$searchArray);

            $sql = ' SELECT di.payroll_system_id,mod.abbreviated_name,di.name,di.display_format,di.is_item_name,di.display_format,di.no_data_format, '
                .  ' di.counting_unit,di.comment,di.disp_order , '
                .  ' ( SELECT count(mpsd.is_display) FROM m_payroll_system_detail mpsd WHERE mpsd.payroll_system_id = di.payroll_system_id AND mpsd.is_display = 1 ) as count, ' 
                .  ' di.organization_id, di.is_del, di.update_time '
                .  ' FROM m_payroll_system_cooperation di INNER JOIN m_organization_detail mod ON di.organization_id = mod.organization_id '
                .  ' INNER JOIN m_payroll_system_detail did ON did.payroll_system_id = di.payroll_system_id , '
                .  ' ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                .  ' FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                .  ' WHERE od.application_date_start <= current_date AND o.is_del = 0 '
                .  ' GROUP BY od.organization_id, od.department_code, o.disp_order '
                .  ' ORDER BY o.disp_order, od.department_code ) organization '
                .  ' WHERE di.organization_id = organization.organization_id '
                .  ' AND mod.application_date_start = organization.application_date_start ';
            
            $sql .= $sqlWhereIn;
            $sql .= $sqlAnd;
            
            // GROUP文
            $sql .= $sqlGroup;

            // HAVING文
            if( !empty( $postArray['minCount'] ) && empty( $postArray['maxCount'] ) )
            {
                $sql .= ' HAVING COUNT(*) >= :minCount ';
                $minCountArray = array(':minCount' => $postArray['minCount'],);
                $searchArray = array_merge($searchArray, $minCountArray);
            }
            if( empty( $postArray['minCount'] ) && !empty( $postArray['maxCount'] ) )
            {
                $sql .= ' HAVING COUNT(*) <= :maxCount ';
                $maxCountArray = array(':maxCount' => $postArray['maxCount'],);
                $searchArray = array_merge($searchArray, $maxCountArray);
            }
            if( !empty( $postArray['minCount'] ) && !empty( $postArray['maxCount'] ) )
            {
                $sql .= ' HAVING COUNT(*) >= :minCount AND COUNT(*) <= :maxCount ';
                $maxCountArray = array(':minCount' => $postArray['minCount'], ':maxCount' => $postArray['maxCount'],);
                $searchArray = array_merge($searchArray, $maxCountArray);
            }
            $sql .= $this->creatSortSQL( $postArray['sort'] );
            $Log->trace("END creatSQL");
            return $sql;
        }
        
        /**
         * 給与システム連携マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   給与システム連携マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");
            $sql = '';

            // ソート条件作成
            $sortSqlList = array(
                                0       =>  ' ORDER BY di.organization_id, di.disp_order, di.payroll_system_id',                              // 初期表示
                                3       =>  ' ORDER BY di.organization_id DESC, di.disp_order, di.payroll_system_id',                         // 組織名の降順
                                4       =>  ' ORDER BY di.organization_id, di.disp_order, di.payroll_system_id',                              // 組織名の昇順
                                5       =>  ' ORDER BY di.name DESC, di.organization_id, di.disp_order, di.payroll_system_id',                // 設定名の降順
                                6       =>  ' ORDER BY di.name, di.organization_id, di.disp_order, di.payroll_system_id',                     // 設定名の昇順
                                7       =>  ' ORDER BY di.display_format DESC, di.organization_id, di.disp_order, di.payroll_system_id',      // 時間の表示形式の降順
                                8       =>  ' ORDER BY di.display_format, di.organization_id, di.disp_order, di.payroll_system_id',           // 時間の表示形式の昇順
                                9       =>  ' ORDER BY di.no_data_format DESC, di.organization_id, di.disp_order, di.payroll_system_id',      // 時間データなしの降順
                               10       =>  ' ORDER BY di.no_data_format, di.organization_id, di.disp_order, di.payroll_system_id',           // 時間データなしの昇順
                               11       =>  ' ORDER BY count DESC, di.organization_id, di.disp_order,  di.payroll_system_id',                 // 表示項目数の降順
                               12       =>  ' ORDER BY count, di.organization_id, di.disp_order,  di.payroll_system_id',                      // 表示項目数の昇順
                               13       =>  ' ORDER BY di.comment DESC, di.organization_id,  di.payroll_system_id',                           // コメントの降順
                               14       =>  ' ORDER BY di.comment, di.organization_id,  di.payroll_system_id',                                // コメントの昇順
                               15       =>  ' ORDER BY di.disp_order DESC, di.organization_id,  di.payroll_system_id',                        // 表示順の降順
                               16       =>  ' ORDER BY di.disp_order, di.organization_id,  di.payroll_system_id',                             // 表示順の昇順
                               17       =>  ' ORDER BY di.is_del DESC, di.organization_id,  di.payroll_system_id',                            // 削除フラグの昇順
                               18       =>  ' ORDER BY di.is_del, di.organization_id,  di.payroll_system_id',                                 // 削除フラグの昇順
                               19       =>  ' ORDER BY di.counting_unit DESC, di.organization_id,  di.payroll_system_id',                     // 集計単位の昇順
                               20       =>  ' ORDER BY di.counting_unit, di.organization_id,  di.payroll_system_id',                          // 集計単位の昇順

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
         * 給与システム連携マスタ一覧画面ソート時の閲覧範囲組織限定のためのSQL文作成
         * @return   $sqlWhereIn
         */
        private function creatSqlWhereIn()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlWhereIn");
            
            $sqlWhereIn = "";
            if( 0 < count( $_SESSION["REFERENCE"] ) )
            {
                $sqlWhereIn = ' AND organization.organization_id IN ( ';
                foreach($_SESSION["REFERENCE"] as $viewable)
                {
                    $sqlWhereIn .=  $viewable['organization_id'] . ', ';
                }
                $sqlWhereIn = substr($sqlWhereIn, 0, -2);
                $sqlWhereIn .= ' ) ';
            }
            $Log->trace("END creatSqlWhereIn");
            return $sqlWhereIn;
        }

        /**
         * AND用のSQL文作成
         * @param    $postArray                 
         * @param    $searchArray               検索条件用パラメータ
         * @return   $sqlAnd
         */
        private function creatSqlAnd($postArray,&$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlAnd");

            $sqlAnd = $this->creatSqlAndAnd($postArray,$searchArray);

            if( !empty( $postArray['organizationID'] ) )
            {
                $sqlAnd .= ' AND di.organization_id = :organizationID ';
                $organizationIDArray = array(':organizationID' => $postArray['organizationID'],);
                $searchArray = array_merge($searchArray, $organizationIDArray);
            }
            if( !empty( $postArray['optionName'] ) )
            {
                $sqlAnd .= ' AND di.name = :name ';
                $optionNameArray = array(':name' => $postArray['optionName'],);
                $searchArray = array_merge($searchArray, $optionNameArray);
            }
            if( !empty( $postArray['isItemName'] ) )
            {
                $sqlAnd .= ' AND di.is_item_name = :isItemName ';
                $isItemNameArray = array(':isItemName' => $postArray['isItemName'],);
                $searchArray = array_merge($searchArray, $isItemNameArray);
            }
            if( !empty( $postArray['displayFormat'] ) )
            {
                $sqlAnd .= ' AND di.display_format = :displayFormat ';
                $displayFormatArray = array(':displayFormat' => $postArray['displayFormat'],);
                $searchArray = array_merge($searchArray, $displayFormatArray);
            }
            if( !empty( $postArray['noDataFormat'] ) )
            {
                $sqlAnd .= ' AND di.no_data_format = :noDataFormat ';
                $noDataFormatArray = array(':noDataFormat' => $postArray['noDataFormat'],);
                $searchArray = array_merge($searchArray, $noDataFormatArray);
            }
            if( !empty( $postArray['countingUnit'] ) )
            {
                $sqlAnd .= ' AND di.counting_unit = :countingUnit ';
                $countingUnitArray = array(':countingUnit' => $postArray['countingUnit'],);
                $searchArray = array_merge($searchArray, $countingUnitArray);
            }
            if(  !$postArray['isDel']  )
            {
                $sqlAnd .= ' AND di.is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['isDel'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }
            $Log->trace("END creatSqlAnd");
            return $sqlAnd;
        }

        /**
         * AND用のSQL文作成2
         * @param    $postArray                 
         * @param    $searchArray               検索条件用パラメータ
         * @return   $sqlAnd
         */
        private function creatSqlAndAnd($postArray,&$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlAndAnd");

            $sqlAnd = "";

            if( !empty( $postArray['comment'] ) )
            {
                $sqlAnd .= ' AND di.comment LIKE :comment ';
                $comment = "%" . $postArray['comment'] . "%";
                $commentArray = array(':comment' => $comment,);
                $searchArray = array_merge($searchArray, $commentArray);
            }
            if( !empty( $postArray['payrollSystemId'] ) && $postArray['payrollSystemId'] !== 0 )
            {
                $sqlAnd .= ' AND di.payroll_system_id = :payroll_system_id ';
                $payrollSystemIdArray = array(':payroll_system_id' => $postArray['payrollSystemId'],);
                $searchArray = array_merge($searchArray, $payrollSystemIdArray);
            }

            $Log->trace("END creatSqlAndAnd");
            return $sqlAnd;
        }


        /**
         * GROUP BY用のSQL文作成
         * @return   $sqlGroup
         */
        private function creatSqlGroup()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlGroup");
            
            $sqlGroup = "";
            if( 0 < count( $_SESSION["REFERENCE"] ) )
            {
                $sqlGroup = ' GROUP BY di.payroll_system_id,mod.abbreviated_name,di.name,di.display_format,di.no_data_format,di.counting_unit,di.comment,di.disp_order,di.organization_id ';
            }
            $Log->trace("END creatSqlGroup");
            return $sqlGroup;
        }
        
        
        /**
         * 編集用出力項目一覧の取得
         * @param    $postArray   入力パラメータ
         * @return  成功時：$outputItemList  失敗：無
         */
        private function getEditOutputItemDeteilList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getEditOutputItemDeteilList");
            
            $sql = " SELECT payroll_system_id , "
                .  "        CASE "
                .  "             WHEN a.organization_id <> 0  THEN a.organization_id "
                .  "             WHEN h.organization_id <> 0  THEN h.organization_id "
                .  "             WHEN hn.organization_id <> 0 THEN hn.organization_id "
                .  "             ELSE 0 "
                .  "        END organization_id , "
                .  "        CASE "
                .  "             WHEN a.allowance_name <> '' THEN 2 "
                .  "             WHEN h.holiday <> ''        THEN 3 "
                .  "             WHEN hn.holiday_name <> ''  THEN 4 "
                .  "             WHEN poil.item_name <> ''   THEN 1 "
                .  "             ELSE 0 "
                .  "        END output_type_id , "
                .  "        CASE "
                .  "             WHEN poil.output_item_list_id <> 0 THEN poil.output_item_list_id "
                .  "             WHEN a.allowance_id <> 0           THEN a.allowance_id "
                .  "             WHEN h.holiday_id <> 0             THEN h.holiday_id "
                .  "             WHEN hn.holiday_name_id <> 0       THEN hn.holiday_name_id "
                .  "             ELSE 0 "
                .  "        END output_item_id , "
                .  "        CASE "
                .  "             WHEN output_type_id = 1     THEN poil.item_name "
                .  "             WHEN output_type_id = 2     THEN a.allowance_name "
                .  "             WHEN output_type_id = 3     THEN h.holiday "
                .  "             WHEN output_type_id = 4     THEN hn.holiday_name "
                .  "             WHEN poil.item_name <> ''   THEN poil.item_name "
                .  "             WHEN a.allowance_name <> '' THEN a.allowance_name "
                .  "             WHEN h.holiday <> ''        THEN h.holiday "
                .  "             WHEN hn.holiday_name <> ''  THEN hn.holiday_name "
                .  "             ELSE '' "
                .  "        END disp_name , "
                .  "        CASE "
                .  "             WHEN output_type_id = 1     THEN did.item_name "
                .  "             WHEN output_type_id = 2     THEN did.item_name "
                .  "             WHEN output_type_id = 3     THEN did.item_name "
                .  "             WHEN output_type_id = 4     THEN did.item_name "
                .  "             WHEN poil.item_name <> ''   THEN poil.item_name "
                .  "             WHEN a.allowance_name <> '' THEN a.allowance_name "
                .  "             WHEN h.holiday <> ''        THEN h.holiday "
                .  "             WHEN hn.holiday_name <> ''  THEN hn.holiday_name "
                .  "             ELSE '' "
                .  "        END item_name , "
                .  "        did.output_item_branch , "
                .  "        did.is_display , "
                .  "        did.disp_order  "
                .  " FROM  m_payroll_system_detail did "
                .  "       FULL OUTER JOIN public.m_output_item_list poil ON did.output_item_id = poil.output_item_list_id AND output_type_id = 1 AND did.payroll_system_id = :payroll_system_id "
                .  "       FULL OUTER JOIN m_allowance a ON did.output_item_id = a.allowance_id AND output_type_id = 2 AND did.payroll_system_id = :payroll_system_id "
                .  "       FULL OUTER JOIN m_holiday h ON did.output_item_id = h.holiday_id AND output_type_id = 3 AND did.payroll_system_id = :payroll_system_id "
                .  "       FULL OUTER JOIN m_holiday_name hn ON did.output_item_id = hn.holiday_name_id AND output_type_id = 4 AND did.payroll_system_id = :payroll_system_id "
                .  " WHERE ( did.payroll_system_id = :payroll_system_id OR did.payroll_system_id is null) AND "
                .  "       ( CASE "
                .  "             WHEN poil.is_del = 1 THEN 1 "
                .  "             WHEN a.is_del    = 1 THEN 1 "
                .  "             WHEN h.is_del    = 1 THEN 1 "
                .  "             WHEN hn.is_del   = 1 THEN 1 "
                .  "             ELSE 0 "
                .  "         END ) = 0 AND "
                .  "       ( CASE "
                .  "             WHEN a.organization_id <> 0  THEN a.organization_id "
                .  "             WHEN h.organization_id <> 0  THEN h.organization_id "
                .  "             WHEN hn.organization_id <> 0 THEN hn.organization_id "
                .  "             ELSE 0 "
                .  "         END ) IN ( " . $this->getAccessAuthorityId( $_SESSION["REGISTRATION"] ) . ")";
            $sql .=  " ORDER BY disp_order, output_type_id, output_item_id ";

            $searchArray = array(':payroll_system_id' => $postArray['payrollSystemId'],);
            $result = $DBA->executeSQL($sql, $searchArray);
            
            $outputItemList = array();
            if( $result === false )
            {
                $Log->trace("END getEditOutputItemDeteilList");
                return $outputItemList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $inputArray = array( 
                                'organization_id'        => $data['organization_id'] == 0 ? $postArray['organization_id'] : $data['organization_id'],
                                'payroll_system_id'      => $data['payroll_system_id'],
                                'output_type_id'         => $data['output_type_id'],
                                'output_item_id'         => $data['output_item_id'],
                                'output_item_branch'     => $data['output_item_branch'] == '' ? 0 : $data['output_item_branch'],
                                'disp_name'              => $data['disp_name'],
                                'item_name'              => $data['item_name'],
                                'is_display'             => $data['is_display'],
                                'disp_order'             => $data['disp_order'], 
                            );
                array_push( $outputItemList, $inputArray );
            }

            $Log->trace("END getEditOutputItemDeteilList");
            return $outputItemList;
        }
        
        /**
         * アクセス権がある、組織IDのリストを取得
         * @param   $organizationIdList   'organization_id'がある配列
         * @return  成功時：'organization_id'のカンマ区切りの文字列  失敗：無
         */
        private function getAccessAuthorityId( $organizationIdList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessAuthorityId");

            $ret = " 0 ";
            foreach($organizationIdList as $organizationId)
            {
                $ret .= ", " . $organizationId['organization_id'];
            }
            
            $Log->trace("END getAccessAuthorityId");
            
            return $ret;
        }
    }
?>
