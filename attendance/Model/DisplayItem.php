<?php
    /**
     * @file      表示項目設定マスタ
     * @author    USE K.Narita
     * @date      2016/06/21
     * @version   1.00
     * @note      表示項目設定マスタテーブルの管理(追加/更新/削除)を行う
     */

    // BaseDisplayItem.phpを読み込む
    require './Model/BaseDisplayItem.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理(追加/更新/削除)を行う
     */
    class DisplayItem extends BaseDisplayItem
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
         * 表示項目設定マスタ新規データ登録
         * @param    $postArray(表示項目設定マスタ、または表示項目詳細マスタへの登録情報)
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
                if( !$this->isUpdateData( $postArray['displayItemId'], $postArray['organization_id'] ) )
                {
                    return "MSG_WAR_2100";
                }
            }

            if($DBA->beginTransaction())
            {
                $ret = "";

                // 新規登録か更新かの判定
                $displayItem = $this->displayItemProcessingDistribution($postArray,$addFlag);
                if( $displayItem === 0 )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "表示項目設定マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
                
                // 表示項目詳細マスタのデータの削除
                $ret = $this->deleteDisplayItemDetail($postArray);
                
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
                
                // 表示項目詳細マスタへの登録、更新
                $arrayCount = count( $outputItemList );
                for( $i = 0; $i < $arrayCount; $i++ )
                {
                
                    // 表示項目詳細マスタにデータ追加
                    $ret = $this->addDisplayItemDetailNewData($displayItem, $outputItemList[$i],$itemNameList[$i],$isDisplayList[$i],$outputTypeId[$i], $outputItemViewList[$i], $outItemBranchList[$i]);

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
                    
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "表示項目設定マスタ登録処理のコミットに失敗しました。";
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
         * 表示項目設定マスタ登録データ削除
         * @param    $postArray   入力パラメータ(表示項目設定ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            // 削除時の関連チェック
            if(!$this->isDeletableData($postArray['displayItemId']))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_display_item SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE display_item_id = :display_item_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':display_item_id'           => $postArray['displayItemId'],
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
         * 表示項目設定マスタのサブ項目の有無を検索
         * @return   サブ項目あり：true  サブ項目なし：false
         */
        public function isSubItem( $displayItemId, $outputTypeId, $outputItemId )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START isSubItem");

            // 表示項目マスタIDが、存在しない
            if( empty( $displayItemId ) )
            {
                $Log->trace("END isSubItem");
                return false;
            }

            $sql = ' SELECT COUNT ( display_item_id ) as cnt FROM m_display_item_detail '
                 . ' WHERE display_item_id = :display_item_id  '
                 . '   AND output_type_id = :output_type_id '
                 . '   AND output_item_id = :output_item_id ';
            $parametersArray = array(
                                        ':display_item_id' =>  $displayItemId,
                                        ':output_type_id'  =>  $outputTypeId,
                                        ':output_item_id'  =>  $outputItemId,
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
         * 表示項目設定マスタ新規データ登録
         * @param    $postArray   入力パラメータ(組織ID/設定名/時間の表示形式/時間データなしの表示方式/コメント/削除フラグ0/表示順/登録ユーザID/登録組織)
         * @return   SQLの実行結果($LastUserDetailId/0)
         */
        private function addNewDisplayItem( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewDisplayItem");
            $sql = '';
            
            $sql = 'INSERT INTO m_display_item( organization_id'
                .  '               , name'
                .  '               , display_format'
                .  '               , no_data_format'
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
                .  '               , :display_format'
                .  '               , :no_data_format'
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
                ':display_format'            => $postArray['displayFormat'],
                ':no_data_format'            => $postArray['noDataFormat'],
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
                $Log->trace("END addNewDisplayItem");
                return 0;
            }
            // Lastidの更新
            $LastUserDetailId = $DBA->lastInsertId( "m_display_item" );

            $Log->trace("END addNewDisplayItem");
            return $LastUserDetailId;
        }

        /**
         * 表示項目詳細マスタ新規データ登録
         * @param    $userDetailId(ユーザ詳細ID)
         * @param    $itemName(出力名称)
         * @param    $outputID(出力項目ID)
         * @param    $isDisplay(表示可否)
         * @param    $outputTypeId(出力種別ID)
         * @param    $number(表示順)
         * @param    $outItemBranch(項目枝番)
         * @return   SQLの実行結果
         */
        private function addDisplayItemDetailNewData($displayItemId, $itemName,$outputID,$isDisplay,$outputTypeId,$number, $outItemBranch)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addDisplayItemDetailNewData");
            
            $sql = ' INSERT INTO m_display_item_detail(   display_item_id '
                .  ' , output_type_id '
                .  '  , output_item_id '
                .  '  , item_name '
                .  '  , is_display '
                .  '  , output_item_branch '
                .  '  , disp_order '
                .  ' ) VALUES ( '
                .  '  :display_item_id '
                .  '  , :output_type_id '
                .  '  , :output_item_id '
                .  '  , :item_name '
                .  '  , :is_display '
                .  '  , :output_item_branch '
                .  '  , :disp_order )'; 
            $parameters = array(
                ':display_item_id'  => $displayItemId,
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
                $errMsg = "表示項目ID：" . $displayItemId. "出力名称：" . $itemName . "の登録失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END addDisplayItemDetailNewData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            $Log->trace("END addDisplayItemDetailNewData");
            return "MSG_BASE_0000";
        }

        /**
         * 表示項目設定マスタ更新
         * @param    $postArray   更新する値の配列
         * @return   SQLの実行結果($displayItemId/0)
         */
        private function modDisplayItemUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modDisplayItemUpdateData");

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
                 . 'WHERE  display_item_id = :display_item_id AND update_time = :update_time ';

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
                ':update_time'          => $postArray['updateTime'],

            );
            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "表示項目設定ID：" . $postArray['displayItemId'] . "の更新失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END modDisplayItemUpdateData");
                return 0;
            }
            $Log->trace("END modDisplayItemUpdateData");
            $displayItemId = $parameters[':display_item_id'];
            return $displayItemId;
        }

        /**
         * 表示項目設定マスタ追加/更新処理振り分け
         * @param    $postArray  表示項目設定マスタへの登録更新情報
         * @param    $addFlag    true：新規   false：更新
         * @return   $displayItemId(追加・更新の対象表示項目設定ID/0)
         */
        private function displayItemProcessingDistribution($postArray, $addFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START displayItemProcessingDistribution");
            // 新規登録の場合、表示項目設定マスタへ登録。新規登録以外は更新
            if($addFlag)
            {
                $displayItemId = $this->addNewDisplayItem($postArray);
            }
            else 
            {
                $displayItemId = $this->modDisplayItemUpdateData($postArray);
            }
            $Log->trace("END displayItemProcessingDistribution");
            return $displayItemId;
        }
        
        /**
         * 表示項目詳細マスタ更新
         * @param    $postArray    表示項目設定IDが入った配列
         * @return   SQLの実行結果
         */
        private function deleteDisplayItemDetail($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deleteDisplayItemDetail");

            $sql = ' DELETE FROM m_display_item_detail '
                 . ' WHERE  display_item_id = :display_item_id ';
                 
            $parameters = array(
                ':display_item_id'   => $postArray['displayItemId'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "表示項目設定ID：" . $postArray['displayItemId'] . "の詳細テーブル削除の失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END deleteDisplayItemDetail");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            
            $Log->trace("END deleteDisplayItemDetail");
            return "MSG_BASE_0000";
        }
        
        /**
         * 更新可能データである
         * @return   時間データなしリスト(時間データなし) 
         */
        private function isUpdateData( $displayItemID, $organizationID  )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START isUpdateData");

            $sql = ' SELECT display_item_id FROM m_display_item   '
                 . ' WHERE  display_item_id = :display_item_id AND organization_id = :organization_id ';
            $parametersArray = array( 
                                        ':display_item_id' => $displayItemID,
                                        ':organization_id' => $organizationID,
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

            // セキュリティマスタで利用中か？
            if( $this->isDeletableData($displayItemID) )
            {
                // 組織が変更になっても、セキュリティマスタで利用していなければ、修正可能
                return true;
            }

            $Log->trace("END isUpdateData");
            
            return false;
        }
        
        /**
         * 削除可能データである
         * @return   時間データなしリスト(時間データなし) 
         */
        private function isDeletableData( $displayItemID )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START isDeletableData");

            $sql = ' SELECT security_id FROM m_security  '
                 . ' WHERE  display_item_id = :display_item_id AND is_del = 0 ';
            $parametersArray = array( ':display_item_id' => $displayItemID );
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
