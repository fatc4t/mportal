<?php
    /**
     * @file      時間別マッピング設定マスタ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      時間別マッピング設定マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class PosMappingTime extends BaseModel
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
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/displayItemName/sort)
         * @return   成功時：$displayItemList(organization_id/displayItem_name/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");
           
            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            
            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $displayItemList = $displayItemDataList;
            }
            else
            {
                $displayItemList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $displayItemDataList);
                if( $postArray['sort'] == 1 )
                {
                    $displayItemList = array_reverse($displayItemList);
                }
            }

                        
            $Log->trace("END getListData");
            return $displayItemList;
        }
        
        /**
         * 表示項目設定マスタ新規データ登録
         * @param    $postArray(従業員マスタ、または従業員詳細マスタへの登録情報)
         * @param    $approvalArray(承認組織情報)
         * @param    $allowanceArray(手当情報)
         * @param    $addFlag(新規登録時にはtrue)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray,$outputItemList,$outputItemIdList,$itemNameList,$isDisplayList,$outputDispOrderList,$addFlag)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");
            
            

                        
            if($DBA->beginTransaction())
            {
                $ret = "";

                // 新規登録か既存組織の適用予定作成かの判定
                
                $posBrand = $this->displayItemProcessingDistribution($postArray,$addFlag);
                
                // 従業員詳細マスタへの登録が完了した場合にSQLの実行結果へ成功メッセージを代入
                if(!(strpos($posBrand, "MSG")) || $posBrand != 0)
                {
                    $ret = "MSG_BASE_0000";
                }
                
                $outputID = $this->getOutputItemList($postArray);
                
                $Log->debug($outputDispOrderList);
                
                // 従業員詳細マスタへの登録
                $arrayCount = count( $outputItemList );
                for( $i = 0; $i < $arrayCount; $i++ )
                {
                    $ret = $this->addDisplayItemDetailNewData(  $posBrand, 
                                                                $outputItemList[$i],
                                                                $outputID[$i]['output_item_list_id'],
                                                                $itemNameList[$i],
                                                                $isDisplayList[$i],
                                                                $outputDispOrderList[$i]);

                    
                    if( $ret !== "MSG_BASE_0000" )
                    {
                        // コミットエラー　ロールバック対応
                        $DBA->rollBack();
                        $Log->warn("MSG_WAR_2000");
                        $errMsg = "POS種別マスタ登録処理のコミットに失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END addNewData");
                        return "MSG_WAR_2000";
                    }
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_WAR_2000");
                    $errMsg = "POS種別マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_WAR_2000";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FATAL_4001");
                $errMsg = "";
                $Log->fatalDetail($errMsg);
                return "MSG_FATAL_4001";
            }

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 表示項目設定マスタ登録データ修正
         * @param    $postArray   入力パラメータ(表示項目設定ID/コード/組織ID/表示項目設定名/就業規則/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $id_name = "display_item_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            // POSTで来た表示項目設定IDを数値化する
            $intDisplayItemId = intval($postArray['displayItemID']);
            // 使用済みの表示項目設定情報の判定
            if(in_array($intDisplayItemId, $inUseArray))
            {
                // 使用中の場合、組織名を変更するときにはエラーとする
                $used_organization_id = $this->getUseDataOrganizationId($intDisplayItemId);
                // POSTで来た組織IDを数値化する
                $intOrganizationId = intval($postArray['organizationID']);
                if($used_organization_id != $intOrganizationId)
                {
                    return "MSG_WAR_2100";
                }
            }

            $sql = 'UPDATE m_display_item SET'
                . ' organization_id = :organization_id'
                . ' , code = :code'
                . ' , display_item_name = :display_item_name'
                . ' , labor_regulations_id = :labor_regulations_id'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE display_item_id = :display_item_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['displayItemCode'],
                ':display_item_name'             => $postArray['displayItemName'],
                ':labor_regulations_id'      => $postArray['laborRegulationsID'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':display_item_id'               => $postArray['displayItemID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 表示項目設定マスタ登録データ削除
         * @param    $postArray   入力パラメータ(表示項目設定ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $id_name = "display_item_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            $intDisplayItemId = intval($postArray['displayItemID']);
            if(in_array($intDisplayItemId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_displayItem SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE display_item_id = :display_item_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':display_item_id'               => $postArray['displayItemID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }
        
        /**
         * 表示項目設定マスタの検索用表示形式のプルダウン
         * @return   時間の表示形式リスト(時間の表示形式) 
         */
        public function getSearchDisplayFormatList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchDisplayFormatList");

            $sql = ' SELECT DISTINCT display_format, organization_id FROM m_display_item '
                 . ' ORDER BY display_format ';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $displayFormatList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchDisplayFormatList");
                return $displayFormatList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($displayFormatList, $data);
            }

            $outList = array();
            $outList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $displayFormatList );

            $initial = array('display_format' => '',);
            array_unshift($outList, $initial);

            $column = "display_format";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchDisplayFormatList");
            return $outList;
        }
        
                /**
         * 表示項目設定マスタの検索用時間データなしのプルダウン
         * @return   時間データなしリスト(時間データなし) 
         */
        public function getSearchNoDataFormatList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchNoDataFormatList");

            $sql = ' SELECT DISTINCT no_data_format, organization_id FROM m_display_item '
                 . ' ORDER BY no_data_format ';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $noDataFormatList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchNoDataFormatList");
                return $noDataFormatList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($noDataFormatList, $data);
            }

            $outList = array();
            $outList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $noDataFormatList );

            $initial = array('no_data_format' => '',);
            array_unshift($outList, $initial);

            $column = "no_data_format";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchNoDataFormatList");
            return $outList;
        }

        /**
         * 出力項目一覧の取得
         * @param   $postArray                 入力パラメータ(organization_id/is_del/organizationID/displayItemName/sort)
         * @param   $searchArray               検索条件用パラメータ
         * @return  成功時：$displayItemList(organization_id/displayItem_name/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getOutputItemList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOutputItemList");
           
            $searchArray = array();
            $sql = ' SELECT * FROM public.m_output_item_list ';
            
            
            $result = $DBA->executeSQL($sql, $searchArray);
            
            $outputItemList = array();
            
            if( $result === false )
            {
                $Log->trace("END getOutputItemList");
                return $outputItemList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $outputItemList, $data);
            }
            
            
            $Log->trace("END getOutputItemList");
            return $outputItemList;

        }

        /**
         * 出力項目一覧の取得
         * @param   $postArray                 入力パラメータ(organization_id/is_del/organizationID/displayItemName/sort)
         * @param   $searchArray               検索条件用パラメータ
         * @return  成功時：$displayItemList(organization_id/displayItem_name/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getOutputItemDeteilList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOutputItemDeteilList");
           
            $searchArray = array();
            $sql = ' SELECT * FROM m_display_item_detail ';
            
                            var_dump($postArray['displayItemId']);
            if( !empty( $postArray['displayItemId'] ) )
            {
                
                $sql .= ' WHERE display_item_id = :display_item_id ';
                $displayFormatArray = array(':display_item_id' => $postArray['displayItemId'],);
                $searchArray = array_merge($searchArray, $displayFormatArray);
            }

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $outputItemList = array();
            
            if( $result === false )
            {
                $Log->trace("END getOutputItemDetailList");
                return $outputItemList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $outputItemList, $data);
            }
            
            
            $Log->trace("END getOutputItemList");
            return $outputItemList;

        }
        
        /**
         * 表示項目設定マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/displayItemName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   表示項目設定マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sqlWhereIn = $this->creatSqlWhereIn();
            $sqlGroup   = $this->creatSqlGroup();

            $sql = ' SELECT di.display_item_id,mod.abbreviated_name,di.name,di.display_format,di.no_data_format,did.is_display, '
                .  ' di.comment,di.disp_order ,count(*) as count, di.organization_id '
                .  ' FROM m_display_item di INNER JOIN m_organization_detail mod ON di.organization_id = mod.organization_id '
                .  ' INNER JOIN m_display_item_detail did ON did.display_item_id = di.display_item_id , '
                .  ' ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                .  ' FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                .  ' WHERE od.application_date_start <= current_date AND o.is_del = 0 '
                .  ' GROUP BY od.organization_id, od.department_code, o.disp_order '
                .  ' ORDER BY o.disp_order, od.department_code ) organization '
                .  ' WHERE di.organization_id = organization.organization_id '
                .  ' AND mod.application_date_start = organization.application_date_start '
                .  ' AND did.is_display = 1 ';
                
                $sql .= $sqlWhereIn;
                
            // AND条件
            if( !empty( $postArray['organizationID'] ) )
            {
                $sql .= ' AND di.organization_id = :organizationID ';
                $organizationIDArray = array(':organizationID' => $postArray['organizationID'],);
                $searchArray = array_merge($searchArray, $organizationIDArray);
            }
            if( !empty( $postArray['optionName'] ) )
            {
                $sql .= ' AND di.display_item_id = :name ';
                $optionNameArray = array(':name' => $postArray['optionName'],);
                $searchArray = array_merge($searchArray, $optionNameArray);
            }
            if( !empty( $postArray['displayFormat'] ) )
            {
                $sql .= ' AND di.display_format = :displayFormat ';
                $displayFormatArray = array(':displayFormat' => $postArray['displayFormat'],);
                $searchArray = array_merge($searchArray, $displayFormatArray);
            }
            if( !empty( $postArray['noDataFormat'] ) )
            {
                $sql .= ' AND di.no_data_format = :noDataFormat ';
                $noDataFormatArray = array(':noDataFormat' => $postArray['noDataFormat'],);
                $searchArray = array_merge($searchArray, $noDataFormatArray);
            }
            if( !empty( $postArray['comment'] ) )
            {
                $sql .= ' AND di.comment LIKE :comment ';
                $comment = "%" . $postArray['comment'] . "%";
                $commentArray = array(':comment' => $comment,);
                $searchArray = array_merge($searchArray, $commentArray);
            }
            if( !empty( $postArray['displayItemId'] ) )
            {
                $sql .= ' AND di.display_item_id = :display_item_id ';
                $noDataFormatArray = array(':display_item_id' => $postArray['displayItemId'],);
                $searchArray = array_merge($searchArray, $noDataFormatArray);
            }
            

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
         * 表示項目設定マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   表示項目設定マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = '';

            // ソート条件作成
            $sortSqlList = array(
                                0       =>  ' ORDER BY di.organization_id, di.disp_order, di.display_item_id',                              // 初期表示
                                3       =>  ' ORDER BY di.organization_id DESC, di.disp_order, di.display_item_id',   // 組織名の降順
                                4       =>  ' ORDER BY di.organization_id, di.disp_order, di.display_item_id',        // 組織名の昇順
                                5       =>  ' ORDER BY di.display_item_id DESC, di.organization_id, di.disp_order, di.display_item_id',     // 設定名の降順
                                6       =>  ' ORDER BY di.display_item_id, di.organization_id, di.disp_order, di.display_item_id',          // 設定名の昇順
                                7       =>  ' ORDER BY di.display_format DESC, di.organization_id, di.disp_order, di.display_item_id',      // 時間の表示形式の降順
                                8       =>  ' ORDER BY di.display_format, di.organization_id, di.disp_order, di.display_item_id',           // 時間の表示形式の昇順
                                9       =>  ' ORDER BY di.no_data_format DESC, di.organization_id, di.disp_order, di.display_item_id',      // 時間データなしの降順
                               10       =>  ' ORDER BY di.no_data_format, di.organization_id, di.disp_order, di.display_item_id',           // 時間データなしの昇順
                               11       =>  ' ORDER BY count DESC, di.organization_id, di.disp_order,  di.display_item_id',                 // 表示項目数の降順
                               12       =>  ' ORDER BY count, di.organization_id, di.disp_order,  di.display_item_id',                      // 表示項目数の昇順
                               13       =>  ' ORDER BY di.comment DESC, di.organization_id,  di.display_item_id',                           // コメントの降順
                               14       =>  ' ORDER BY di.comment, di.organization_id,  di.display_item_id',                                // コメントの昇順
                               15       =>  ' ORDER BY di.disp_order DESC, di.organization_id,  di.display_item_id',                        // 表示順の降順
                               16       =>  ' ORDER BY di.disp_order, di.organization_id,  di.display_item_id',                             // 表示順の昇順
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
         * 表示項目設定マスタ一覧画面ソート時の閲覧範囲組織限定のためのSQL文作成
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
                $sqlGroup = ' GROUP BY di.display_item_id,mod.abbreviated_name,di.name,di.display_format,di.no_data_format,did.is_display,di.comment,di.disp_order,di.organization_id ';
            }

            $Log->trace("END creatSqlGroup");
            
            return $sqlGroup;
        }

        /**
         * 表示項目設定マスタ更新時使用済み表示項目設定データ組織ID取得
         * @param    $displayItemID   入力パラメータ(表示項目設定ID)
         * @return   組織ID
         */
        private function getUseDataOrganizationId( $displayItemID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUseDataOrganizationId");

            $sql = 'SELECT organization_id FROM m_display_item  WHERE display_item_id = :display_item_id';
            $searchArray = array(':display_item_id' => $displayItemID,);
            // sql実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            if( $result === false )
            {
                $Log->trace("END getUseDataOrganizationId");
                return -1;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $organization_id = $data['organization_id'];
            }

            $Log->trace("END getUseDataOrganizationId");

            return $organization_id;
        }
        
        
        /**
         * 表示項目設定マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/表示項目設定名/就業規則/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        private function addNewDisplayItem( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewPosBrand");


            $sql = '';
            
            $sql = 'INSERT INTO m_display_item( organization_id'
                .  '               , name'
                .  '               , display_format'
                .  '               , no_data_format'
                .  '               , comment'
                .  '               , disp_order'
                .  '               , registration_time'
                .  '               , registration_user_id'
                .  '               , registration_organization'
                .  '               , update_time'
                .  '               , update_user_id'
                .  '               , update_organization'
                .  '                 ) VALUES ( '
                .  '                :organization_id'
                .  '               , :name'
                .  '               , :display_format'
                .  '               , :no_data_format'
                .  '               , :comment'
                .  '               , :disp_order'
                .  '               , current_timestamp'
                .  '               , :registration_user_id'
                .  '               , :registration_organization'
                .  '               , current_timestamp'
                .  '               , :update_user_id'
                .  '               , :update_organization) ';

            $parameters = array(
                ':organization_id'           => $postArray['organization_id'],
                ':name'                      => $postArray['optionName'],
                ':display_format'            => $postArray['displayFormat'],
                ':no_data_format'            => $postArray['noDataFormat'],
                ':comment'                   => $postArray['comment'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );
            
            $Log->debug($parameters[':organization_id']);
            $Log->debug($parameters[':name']);
            $Log->debug($parameters[':display_format']);
            $Log->debug($parameters[':no_data_format']);
            $Log->debug($parameters[':comment']);
            $Log->debug($parameters[':disp_order']);
            $Log->debug($parameters[':registration_user_id']);
            $Log->debug($parameters[':registration_organization']);
            $Log->debug($parameters[':update_user_id']);
            $Log->debug($parameters[':update_organization']);
            
            $result = $DBA->executeSQL($sql, $parameters);
            
            // SQL実行
            if( !$result )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("MSG_ERR_3000");
                $errMsg = "";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3000";
            }

            // Lastidの更新
            $LastUserDetailId = $DBA->lastInsertId( "m_display_item" );

            $Log->trace("END addNewPosBrand");

            return $LastUserDetailId;
        }


        /**
         * 承認組織マスタ新規データ登録
         * @param    $userDetailId(ユーザ詳細ID)
         * @param    $organization_id(組織ID)
         * @return   SQLの実行結果
         */
        private function addDisplayItemDetailNewData($displayItemId, $organization_id,$outputItemId,$outputID,$isDisplay,$outputDispOrder)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addPosBrandDetailNewData");
            var_dump($outputItemId);
            
            $sql = ' INSERT INTO m_display_item_detail(   display_item_id '
                .  ' , output_type_id '
                .  '  , output_item_id '
                .  '  , item_name '
                .  '  , is_display '
                .  '  , disp_order '
                .  ' ) VALUES ( '
                .  '  :display_item_id '
                .  '  , :output_type_id '
                .  '  , :output_item_id '
                .  '  , :item_name '
                .  '  , :is_display '
                .  '  , :disp_order )'; 
            $parameters = array(
                ':display_item_id'  => $displayItemId,
                ':output_type_id'   => 1,
                ':output_item_id'   => $outputDispOrder,
                ':item_name'        => $organization_id,
                ':is_display'       => $isDisplay === 'true' ? 1 : 0,
                ':disp_order'       => $outputItemId,
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_LOGIN_FRAUD");
                $errMsg = "従業員ID：" . $userDetailId. "組織ID：" . $organization_id . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_FW_LOGIN_FRAUD";
            }

            $Log->trace("END addPosBrandDetailNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 表示項目設定マスタ更新
         * @param    $postArray(ログインID/パスワード)
         * @param    $userId
         * @return   SQLの実行結果(true/false)
         */
        private function modDisplayItemUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modPosBrandUpdateData");

            $sql = ' UPDATE m_display_item SET '
                 . '   organization_id = :organization_id '
                 . ' , name = :name '
                 . ' , display_format = :display_format '
                 . ' , no_data_format = :no_data_format '
                 . ' , comment = :comment '
                 . ' , disp_order = :disp_order '
                 . ' , update_time = current_timestamp '
                 . ' , update_user_id = :update_user_id '
                 . ' , update_organization = :update_organization  '
                 . 'WHERE  display_item_id = :display_item_id ';

            $parameters = array(
                ':display_item_id'      => $postArray['displayItemId'],
                ':organization_id'      => $postArray['organization_id'],
                ':name'                 => $postArray['optionName'],
                ':display_format'       => $postArray['displayFormat'],
                ':no_data_format'       => $postArray['noDataFormat'],
                ':comment'              => $postArray['comment'],
                ':disp_order'           => $postArray['dispOrder'],
                ':update_user_id'       => $postArray['user_id'],
                ':update_organization'  => $postArray['organization'],
            );
            
            var_dump($parameters);

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3087");
                $errMsg = "ログインID：" . $postarray['login_id']. "パスワード：" . $postarray['password'] . "の更新失敗";
                $Log->warnDetail($errMsg);
                return false;
            }

            $Log->trace("END modPosBrandUpdateData");
            
            $displayItemId = $parameters[':display_item_id'];
            return $displayItemId;
        }

        /**
         * 表示項目詳細マスタ更新
         * @param    $postArray(ログインID/パスワード)
         * @param    $userId
         * @return   SQLの実行結果(true/false)
         */
        private function modDisplayItemDetailUpdateData($displayItemId, $organization_id,$outputItemId,$outputID,$isDisplay,$outputDispOrder)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modPosBrandUpdateData");

            $sql = ' UPDATE m_display_item_detail SET '
                 . '   display_item_id = :display_item_id '
                 . ' , output_type_id = :output_type_id '
                 . ' , output_item_id = :output_item_id '
                 . ' , item_name = :item_name '
                 . ' , is_display = :is_display '
                 . ' , disp_order = :disp_order '
                 . 'WHERE  display_item_detail_id = :display_item_detail_id ';
                 
            $parameters = array(
                ':display_item_id'          => $displayItemId,
                ':output_type_id'           => 1,
                ':output_item_id'           => $outputDispOrder,
                ':item_name'                => $organization_id,
                ':is_display'               => $isDisplay === 'true' ? 1 : 0,
                ':disp_order'               => $outputItemId,
                ':display_item_detail_id'   => $outputDispOrder,
            );
            
            var_dump($parameters);
            
            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3087");
                $errMsg = "ログインID：" . $postarray['login_id']. "パスワード：" . $postarray['password'] . "の更新失敗";
                $Log->warnDetail($errMsg);
                return false;
            }

            $Log->trace("END modPosBrandUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * 表示項目設定マスタ追加/更新処理振り分け
         * @param    $postArray(従業員マスタ、または従業員詳細マスタへの登録情報)
         * @param    $addFlag
         * @return   $userId(追加/更新の対象従業員ID)
         */
        private function displayItemProcessingDistribution($postArray, $addFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START userProcessingDistribution");


                // 新規登録の場合従業員マスタへ登録
            $displayItemId = $this->addNewDisplayItem($postArray);
            
            $Log->debug($displayItemId);

            $Log->trace("START userProcessingDistribution");

            return $displayItemId;
        }

    }
?>
