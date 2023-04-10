<?php
    /**
     * @file      給与システム連携設定マスタ
     * @author    USE S.Nakamura
     * @date      2016/07/22
     * @version   1.00
     * @note      給与システム連携設定マスタテーブルの管理(追加/更新/削除)を行う
     */

    // BasePayrollSystem.phpを読み込む
    require './Model/BasePayrollSystem.php';

    /**
     * 給与システム連携設定クラス
     * @note   給与システム連携設定マスタテーブルの管理(追加/更新/削除)を行う
     */
    class PayrollSystem extends BasePayrollSystem
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
         * 給与システム連携設定マスタ新規データ登録
         * @param    $postArray(給与システム連携マスタ、または給与システム連携項目マスタへの登録情報)
         * @param    $outputList(項目名情報)
         * @param    $itemNameList(表示項目ID情報)
         * @param    $isDisplayList(表示可否情報)
         * @param    $outputTypeId(出力種別ID情報)
         * @param    $addFlag(新規登録時にはtrue)
         * @param    $outItemBranchList (項目枝番)
         * @param    $outputItemViewList (表示順)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray,$outputItemList,$itemNameList,$isDisplayList,$outputTypeId,$addFlag, $outItemBranchList, $outputItemViewList )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // 更新時は、関連チェックを行う
            if(!$addFlag)
            {
                // 更新時の関連チェック
                if( !$this->isUpdateData( $postArray['payrollSystemId'], $postArray['organization_id'] ) )
                {
                    return "MSG_WAR_2100";
                }
            }

            if($DBA->beginTransaction())
            {
                $ret = "";

                // 新規登録か更新かの判定
                $payrollSystem = $this->payrollSystemProcessingDistribution($postArray,$addFlag);
                if( $payrollSystem === 0 )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "表示項目設定マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
                
                // 給与システム連携項目マスタのデータの削除
                $ret = $this->deletePayrollSystemDetail($postArray);
                
                if( $ret !== "MSG_BASE_0000" )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "表示項目設定マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
                
                // 給与システム連携項目マスタへの登録、更新
                $arrayCount = count( $outputItemList );
                for( $i = 0; $i < $arrayCount; $i++ )
                {
                
                    // 給与システム連携項目マスタにデータ追加
                    $ret = $this->addPayrollSystemDetailNewData($payrollSystem, $outputItemList[$i],$itemNameList[$i],$isDisplayList[$i],$outputTypeId[$i], $outputItemViewList[$i], $outItemBranchList[$i]);

                    if( $ret !== "MSG_BASE_0000" )
                    {
                        // コミットエラー　ロールバック対応
                        $DBA->rollBack();
                        $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                        $errMsg = "給与システム連携マスタ登録処理のコミットに失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END addNewData");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
                    
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "給与システム連携マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "";
                $Log->fatalDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_TRANSACTION_NG";
            }
            $Log->trace("END addNewData");
            return $ret;
        }

        /**
         * 給与システム連携マスタ登録データ削除
         * @param    $postArray   
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            // 削除時の関連チェック
            if(!$this->isDeletableData($postArray['payrollSystemId'], $postArray['organizationId']))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_payroll_system_cooperation SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE payroll_system_id = :payroll_system_id AND update_time = :update_time ';
                
            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':payroll_system_id'         => $postArray['payrollSystemId'],
                ':update_time'               => $postArray['updateTime'],

            );
            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");
            return $ret;
        }
        
        /**
         * 給与システム連携マスタの検索用表示形式のプルダウン
         * @return   時間の表示形式リスト(時間の表示形式) 
         */
        public function getSearchDisplayFormatList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchDisplayFormatList");

            $sql = ' SELECT DISTINCT display_format, organization_id FROM m_payroll_system_cooperation '
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
         * 表示項目設定マスタのサブ項目の有無を検索
         * @return   サブ項目あり：true  サブ項目なし：false
         */
        public function isSubItem( $payrollSystemId, $outputTypeId, $outputItemId )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START isSubItem");

            // 給与連携マスタIDが、存在しない
            if( empty( $payrollSystemId ) )
            {
                $Log->trace("END isSubItem");
                return false;
            }

            $sql = ' SELECT COUNT ( payroll_system_id ) as cnt FROM m_payroll_system_detail '
                 . ' WHERE payroll_system_id = :payroll_system_id  '
                 . '   AND output_type_id = :output_type_id '
                 . '   AND output_item_id = :output_item_id ';
            $parametersArray = array(
                                        ':payroll_system_id' =>  $payrollSystemId,
                                        ':output_type_id'    =>  $outputTypeId,
                                        ':output_item_id'    =>  $outputItemId,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END isSubItem");
                return false;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['cnt'] > 0 )
                {
                    $Log->trace("END isSubItem");
                    return true;
                }
            }

            $Log->trace("END isSubItem");
            return false;
        }
        
        /**
         * 給与システム連携マスタの検索用時間データなしのプルダウン
         * @return   時間データなしリスト(時間データなし) 
         */
        public function getSearchNoDataFormatList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchNoDataFormatList");

            $sql = ' SELECT DISTINCT no_data_format, organization_id FROM m_payroll_system_cooperation '
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
         * 検索(集計単位)プルダウン
         * @return   $countingUnitList
         */
        public function getCountingUnitList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCountingUnitList");

            $sql = ' SELECT DISTINCT counting_unit, organization_id FROM m_payroll_system_cooperation '
                 . ' ORDER BY counting_unit ';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            $countingUnitList = array();

            if( $result === false )
            {
                $Log->trace("END getCountingUnitList");
                return $countingUnitList;
                
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($countingUnitList, $data);
            }

            $outList = array();
            $outList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $countingUnitList );

            $initial = array('counting_unit' => '',);
            array_unshift($outList, $initial );

            $column = "counting_unit";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getCountingUnitList");

            return $outList;
        }


        /**
         * 検索(設定名)プルダウン
         * @return   $optionNameList
         */
        public function getOptionNameList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOptionNameList");

            $sql = ' SELECT DISTINCT name, organization_id FROM m_payroll_system_cooperation '
                 . ' ORDER BY name ';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            $optionNameList = array();

            if( $result === false )
            {
                $Log->trace("END getOptionNameList");
                return $optionNameList;
                
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($optionNameList, $data);
            }

            $outList = array();
            $outList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $optionNameList );

            $initial = array('name' => '',);
            array_unshift($outList, $initial );

            $column = "name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getOptionNameList");

            return $outList;
        }

        /**
         * 給与システム連携マスタ新規データ登録
         * @param    $postArray  
         * @return   SQLの実行結果($LastUserDetailId/0)
         */
        private function addNewPayrollSystem( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewPayrollSystem");
            $sql = '';
            
            $sql = 'INSERT INTO m_payroll_system_cooperation( organization_id'
                .  '               , name'
                .  '               , is_item_name'
                .  '               , display_format'
                .  '               , no_data_format'
                .  '               , counting_unit' 
                .  '               , comment'
                .  '               , is_del'
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
                .  '               , :is_item_name'
                .  '               , :display_format'
                .  '               , :no_data_format'
                .  '               , :counting_unit'
                .  '               , :comment'
                .  '               , :is_del'
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
                ':is_item_name'              => $postArray['isItemName'],
                ':display_format'            => $postArray['displayFormat'],
                ':no_data_format'            => $postArray['noDataFormat'],
                ':counting_unit'             => $postArray['countingUnit'],
                ':comment'                   => $postArray['comment'],
                ':is_del'                    => 0,
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );
            
            $result = $DBA->executeSQL($sql, $parameters);
            
            // SQL実行
            if( !$result )
            {
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewPayrollSystem");
                return 0;
            }
            // Lastidの更新
            $LastUserDetailId = $DBA->lastInsertId( "m_payroll_system_cooperation" );

            $Log->trace("END addNewPayrollSystem");
            return $LastUserDetailId;
        }

        /**
         * 給与システム連携項目マスタ新規データ登録
         * @param    $payrollSystemId(給与システム連携ID)
         * @param    $itemName(出力名称)
         * @param    $outputID(出力項目ID)
         * @param    $isDisplay(表示可否)
         * @param    $outputTypeId(出力種別ID)
         * @param    $number(表示順)
         * @param    $outItemBranch(項目枝番)
         * @return   SQLの実行結果
         */
        private function addPayrollSystemDetailNewData($payrollSystemId, $itemName,$outputID,$isDisplay,$outputTypeId,$number, $outItemBranch)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addPayrollSystemDetailNewData");

            $sql = ' INSERT INTO m_payroll_system_detail( payroll_system_id '
                .  ' , output_type_id '
                .  '  , output_item_id '
                .  '  , item_name '
                .  '  , is_display '
                .  '  , output_item_branch '
                .  '  , disp_order '
                .  ' ) VALUES ( '
                .  '  :payroll_system_id '
                .  '  , :output_type_id '
                .  '  , :output_item_id '
                .  '  , :item_name '
                .  '  , :is_display '
                .  '  , :output_item_branch '
                .  '  , :disp_order )'; 
            $parameters = array(
                ':payroll_system_id'=> $payrollSystemId,
                ':output_type_id'   => $outputTypeId,
                ':output_item_id'   => $outputID,
                ':item_name'        => $itemName,
                ':is_display'       => $isDisplay === 'true' ? 1 : 0,
                ':output_item_branch'       => $outItemBranch,
                ':disp_order'       => $number,
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "表示項目ID：" . $payrollSystemId. "出力名称：" . $itemName . "の登録失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END addPayrollSystemDetailNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            $Log->trace("END addPayrollSystemDetailNewData");
            return "MSG_BASE_0000";
        }

        /**
         * 給与システム連携マスタ更新
         * @param    $postArray   更新する値の配列
         * @return   SQLの実行結果($payrollSystemId/0)
         */
        private function modPayrollSystemUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modPayrollSystemUpdateData");

            $sql = ' UPDATE m_payroll_system_cooperation SET '
                 . '   organization_id = :organization_id '
                 . ' , name = :name '
                 . ' , is_item_name = :is_item_name '
                 . ' , display_format = :display_format '
                 . ' , no_data_format = :no_data_format '
                 . ' , counting_unit = :counting_unit '
                 . ' , comment = :comment '
                 . ' , disp_order = :disp_order '
                 . ' , update_time = current_timestamp '
                 . ' , update_user_id = :update_user_id '
                 . ' , update_organization = :update_organization  '
                 . 'WHERE  payroll_system_id = :payroll_system_id AND update_time = :update_time ';

            $parameters = array(
                ':payroll_system_id'    => $postArray['payrollSystemId'],
                ':organization_id'      => $postArray['organization_id'],
                ':name'                 => $postArray['optionName'],
                ':is_item_name'         => $postArray['isItemName'],
                ':display_format'       => $postArray['displayFormat'],
                ':no_data_format'       => $postArray['noDataFormat'],
                ':counting_unit'        => $postArray['countingUnit'],
                ':comment'              => $postArray['comment'],
                ':disp_order'           => $postArray['dispOrder'],
                ':update_user_id'       => $postArray['user_id'],
                ':update_organization'  => $postArray['organization'],
                ':update_time'          => $postArray['updateTime'],

            );
            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "表示項目設定ID：" . $postArray['payrollSystemId'] . "の更新失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END modPayrollSystemUpdateData");
                return 0;
            }
            $Log->trace("END modPayrollSystemUpdateData");
            $payrollSystemId = $parameters[':payroll_system_id'];
            return $payrollSystemId;
        }

        /**
         * 給与システム連携マスタ追加/更新処理振り分け
         * @param    $postArray  給与システム連携マスタへの登録更新情報
         * @param    $addFlag    true：新規   false：更新
         * @return   $payrollSystemId(追加・更新の対象表示項目設定ID/0)
         */
        private function payrollSystemProcessingDistribution($postArray, $addFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START payrollSystemProcessingDistribution");
            // 新規登録の場合、給与システム連携マスタへ登録。新規登録以外は更新
            if($addFlag)
            {
                $payrollSystemId = $this->addNewPayrollSystem($postArray);
            }
            else 
            {
                $payrollSystemId = $this->modPayrollSystemUpdateData($postArray);
            }
            $Log->trace("END payrollSystemProcessingDistribution");
            return $payrollSystemId;
        }
        
        /**
         * 給与システム連携マスタ更新
         * @param    $postArray    給与システム連携IDが入った配列
         * @return   SQLの実行結果
         */
        private function deletePayrollSystemDetail($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deletePayrollSystemDetail");

            $sql = ' DELETE FROM m_payroll_system_detail '
                 . ' WHERE  payroll_system_id = :payroll_system_id ';
                 
            $parameters = array(
                ':payroll_system_id'   => $postArray['payrollSystemId'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "表示項目設定ID：" . $postArray['payrollSystemId'] . "の詳細テーブル削除の失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END deletePayrollSystemDetail");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            
            $Log->trace("END deletePayrollSystemDetail");
            return "MSG_BASE_0000";
        }
        
        /**
         * 更新可能データである
         * @return   時間データなしリスト(時間データなし) 
         */
        private function isUpdateData( $payrollSystemId, $organizationId  )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START isUpdateData");

            $sql = ' SELECT payroll_system_id FROM m_payroll_system_cooperation   '
                 . ' WHERE  payroll_system_id = :payroll_system_id AND organization_id = :organization_id ';
            $parametersArray = array( 
                                        ':payroll_system_id' => $payrollSystemId,
                                        ':organization_id' => $organizationId,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END isUpdateData");
                return false;
            }
            
            while ( $result->fetch(PDO::FETCH_ASSOC) )
            {
                return true;
            }

            // ビュー v_organization で利用中か？
            if( $this->isDeletableData($payrollSystemId,$organizationId) )
            {
                // 組織が変更になっても、v_organizationで利用していなければ、修正可能
                return true;
            }

            $Log->trace("END isUpdateData");
            
            return false;
        }
        
        /**
         * 削除可能データである
         * @return   時間データなしリスト(時間データなし) 
         */
        private function isDeletableData( $payrollSystemId, $organizationId )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START isDeletableData");

            $sql = " SELECT organization_detail_id FROM v_organization  "
                 . " WHERE  payroll_system_id = :payroll_system_id AND organization_id = :organization_id AND eff_code = '適用中' ";
            $parametersArray = array( ':payroll_system_id' => $payrollSystemId,
                                      ':organization_id' => $organizationId,);
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END isDeletableData");
                return false;
            }
            
            while ( $result->fetch(PDO::FETCH_ASSOC) )
            {
                return false;
            }

            $Log->trace("END isDeletableData");
            return true;
        }
        
    }
?>
