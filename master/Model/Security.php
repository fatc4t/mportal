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
    class Security extends BaseModel
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
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $dataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $dataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $dataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $securityDataList = $dataList;
            }
            else 
            {
                $securityDataList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $dataList);
                if( $postArray['sort'] == 1 )
                {
                    $securityDataList = array_reverse($securityDataList);
                }
            }
            $Log->trace("END getListData");

            // 一覧表を返す
            return $securityDataList;
        }

        /**
         * セキュリティマスタ新規データ登録
         * @param    $postArray(セキュリティマスタへの登録情報)
         * @param    $referenceList, $registrationList, $deleteList, $approvalList, $printingList, $outputList, $accessIDList(セキュリティ詳細マスタへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray, $referenceList, $registrationList, $deleteList, $approvalList, $printingList, $outputList, $accessIDList )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            if($DBA->beginTransaction())
            {
                $ret = "";
                $securityID = "0";
                
                $ret = $this->addSecurityNewData( $postArray );
                if( $ret === false )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "SQL実行に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
                
                $securityID = $DBA->lastInsertId( "m_security" );
                
                // セキュリティ詳細マスタへの登録
                $ret = $this->setSecurityDetail( $securityID, $referenceList, $registrationList, $deleteList, 
                                                 $approvalList, $printingList, $outputList, $accessIDList);
                if( $ret === false )
                {
                    // コミットエラー  ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "詳細情報の登録に失敗致しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END addNewData");
                return "MSG_FW_DB_TRANSACTION_NG";
            }
            
            $Log->trace("END addNewData");
            
            return "MSG_BASE_0000";
        }

        /**
         * セキュリティマスタデータ更新
         * @param    $postArray(セキュリティマスタへの登録情報)
         * @param    $referenceList, $registrationList, $deleteList, $approvalList, $printingList, $outputList, $accessIDList(セキュリティ詳細マスタへの登録情報)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray, $referenceList, $registrationList, $deleteList, $approvalList, $printingList, $outputList, $accessIDList )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            if( $DBA->beginTransaction() )
            {
                $ret = "";
                $securityID = $postArray['securityID'];
                
                $ret = $this->modSecurityUpdateData( $postArray );
                if( $ret === false )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "セキュリティマスタテーブルの更新に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                $ret = $this->modSecurityDetailDel($securityID);
                if( $ret === false )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "セキュリティ詳細マスタテーブルの更新に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // セキュリティ詳細マスタへの登録
                $ret = $this->setSecurityDetail( $securityID, $referenceList, $registrationList, $deleteList, 
                                                 $approvalList, $printingList, $outputList, $accessIDList);
                if( $ret === false )
                {
                    // コミットエラー  ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "詳細情報の登録に失敗致しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "セキュリティマスタ更新処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END modUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * セキュリティマスタデータ削除
         * @param    $postArray
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            // セキュリティの使用の有無
            if( !$this->isDel( $postArray['securityID'] ) )
            {
                return "MSG_WAR_2101";
            }

            $sql = ' UPDATE m_security SET'
                . '   is_del = 1'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE security_id = :security_id AND update_time = :update_time ';

            $parameters = array(
                ':security_id'               => $postArray['securityID'],
                ':update_organization'       => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行(ヘッダテーブルのみ更新)
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 「区分」のプルダウンリスト取得
         * @param    $classID      検索可能な最大の区分値
         * @return   「区分」のプルダウンリスト
         */
        public function getSearchClassification( $classID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getSearchClassification");

            $ret = array(
                ""       => '',
                1        => 'システム管理者',
                2        => '管理者',
                3        => '一般',
            );

            // 管理者の場合
            if( 2 == $classID )
            {
                unset( $ret[1] );
            }
            // 一般の場合
            else if( 3 <= $classID )
            {
                unset( $ret[1] );
                unset( $ret[2] );
            }

            $Log->trace("END getSearchClassification");

            return $ret;
        }

        /**
         * 検索(参照等)プルダウン
         * @param    $authority     アクセス権限名
         * @return   参照リスト
         */
        public function getSearchList($authority)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchList");

            $sql = 'SELECT security_class_id, security_class_name FROM public.m_security_class '
                . ' WHERE  security_class_id >= :authority ORDER BY security_class_id';
            $parametersArray = array( ':authority'	=> $authority,);
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            $referenceList = array();

            if( $result === false )
            {
                $Log->trace("END getSearchList");
                return $referenceList;
                
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($referenceList, $data);
            }
            $initial = array('security_class_id' => '', 'security_class_name' => '',);
            array_unshift($referenceList, $initial );

            $Log->trace("END getSearchList");

            return $referenceList;
        }

        /**
         * アクセス権限マスタ一覧の取得
         * @param    $authority     アクセス権限名
         * @return   AccessList
         */
        public function getAccessList( $securityID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAccessList");

            $sql = ' SELECT pma.access_authority_id ,pma.function_id, pma.screen_name, msd.reference, '
                 . '        msd.registration, msd.delete, msd.approval, msd.printing, msd.output  '
                 . ' FROM public.m_access_authority pma LEFT OUTER JOIN m_security_detail msd ON '
                 . '      pma.access_authority_id = msd.access_authority_id AND msd.security_id = :security_id '
                 . ' WHERE  pma.is_del = 0 '
                 . ' ORDER BY pma.disp_order ';

            $parametersArray = array( ':security_id'  => $securityID );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessList = array();
            if( $result === false )
            {
                $Log->trace("END getAccessList");
                return $accessList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $detailArray = array(
                                        'access_authority_id' => $data['access_authority_id'],
                                        'function_id'         => $data['function_id'],
                                        'screen_name'         => $data['screen_name'],
                                        'reference'           => $this->setValue( $data['reference'] ),
                                        'registration'        => $this->setValue( $data['registration'] ),
                                        'delete'              => $this->setValue( $data['delete'] ),
                                        'approval'            => $this->setValue( $data['approval'] ),
                                        'printing'            => $this->setValue( $data['printing'] ),
                                        'output'              => $this->setValue( $data['output'] ),
                                    );
                array_push( $accessList, $detailArray );
            }

            $Log->trace("END getAccessList");

            return $accessList;
        }

        /**
         * 編集画面のアクセス権限設定画面一覧の取得
         * @param    $securityID     セキュリティID
         * @return   AuthorityNameList
         */
        public function getAuthority( $securityID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAuthority");

            $sql = ' SELECT sd.reference, sd.registration, sd.delete, '
                 . ' sd.approval, sd.printing, sd.output, aa.screen_name  '
                 . ' FROM m_security_detail sd INNER JOIN public.m_access_authority aa '
                 . ' ON sd.access_authority_id = aa.access_authority_id '
                 . ' WHERE sd.security_id = :security_id AND aa.access_authority_id = 205 ORDER BY sd.access_authority_id ';
                   
            $searchArray = array(':security_id' => $securityID, );
            $result = $DBA->executeSQL($sql, $searchArray);
            
            $ret = array(
                            'reference'    => 6,
                            'registration' => 6,
                            'delete'       => 6,
                            'approval'     => 6,
                            'printing'     => 6,
                            'output'       => 6,
                        );

            if( $result === false )
            {
                $Log->trace("END getAuthority");
                return $ret;
                
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END getAuthority");

            return $ret;
        }


        /**
         * 編集画面のヘッダ部の情報を取得
         * @param    $securityID     セキュリティID
         * @return   NameList
         */
        public function getNameList($securityID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getNameList");

            $sql = ' SELECT s.organization_id, s.security_name, s.display_item_id,s.is_del, '
                 . ' s.comment, s.disp_order, s.update_time '
                 . ' FROM m_security s '
                 . ' WHERE s.security_id = :security_id ';
                
            $searchArray = array(':security_id' => $securityID, );
 
            $result = $DBA->executeSQL($sql, $searchArray);
            
            $nameList = array();

            if( $result === false )
            {
                $Log->trace("END getNameList");
                return $nameList;

            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $nameList = $data;
            }

            $Log->trace("END getNameList");

            return $nameList;
        }

        /**
         * アクセス権限マスタ一覧の取得
         * @param    $authority     アクセス権限名
         * @return   AccessList
         */
        private function setValue( $value )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setValue");

            $ret = $value == null ? 6 :$value;

            $Log->trace("END setValue");

            return $ret;
        }

        /**
         * セキュリティマスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   セキュリティマスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = 'SELECT '
                 . '    ms.security_id '
                 . '  , classification.min_classification '
                 . '  , ms.organization_id '
                 . '  , mod.abbreviated_name '
                 . '  , ms.display_item_id '
                 . '  , mdi.name as display_item_name'
                 . '  , ms.security_name '
                 . '  , ms.comment '
                 . '  , ms.is_del '
                 . '  , ms.disp_order '
                 . '  , min_msd.min_reference '
                 . '  , pms_ref.security_class_name as min_reference_name '
                 . '  , min_msd.min_registration '
                 . '  , pms_reg.security_class_name as min_registration_name '
                 . '  , min_msd.min_delete '
                 . '  , pms_del.security_class_name as min_delete_name '
                 . '  , min_msd.min_approval '
                 . '  , pms_app.security_class_name as min_approval_name '
                 . '  , min_msd.min_printing '
                 . '  , pms_pri.security_class_name as min_printing_name '
                 . '  , min_msd.min_output '
                 . '  , pms_out.security_class_name as min_output_name '
                 . 'FROM '
                 . '    m_security ms INNER JOIN ( SELECT '
                 . '    msd.security_id '
                 . '  , min( msd.reference ) as min_reference '
                 . '  , min( msd.registration ) as min_registration '
                 . '  , min( msd.delete ) as min_delete '
                 . '  , min( msd.approval ) as min_approval '
                 . '  , min( msd.printing ) as min_printing '
                 . '  , min( msd.output ) as min_output '
                 . 'FROM '
                 . '  m_security_detail msd '
                 . 'GROUP BY msd.security_id '
                 . 'ORDER BY msd.security_id ) min_msd ON ms.security_id = min_msd.security_id '
                 . 'INNER JOIN ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                 . '             FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                 . '             GROUP BY od.organization_id '
                 . '             ORDER BY od.organization_id ) organization ON organization.organization_id = ms.organization_id '
                 . 'INNER JOIN m_organization_detail mod ON mod.organization_id = ms.organization_id AND  '
                 . '                                        organization.application_date_start = mod.application_date_start '
                 . 'LEFT OUTER JOIN m_display_item mdi ON mdi.display_item_id = ms.display_item_id '
                 . 'INNER JOIN public.m_security_class pms_ref ON pms_ref.security_class_id = min_msd.min_reference '
                 . 'INNER JOIN public.m_security_class pms_reg ON pms_reg.security_class_id = min_registration '
                 . 'INNER JOIN public.m_security_class pms_del ON pms_del.security_class_id = min_delete '
                 . 'INNER JOIN public.m_security_class pms_app ON pms_app.security_class_id = min_approval '
                 . 'INNER JOIN public.m_security_class pms_pri ON pms_pri.security_class_id = min_printing '
                 . 'INNER JOIN public.m_security_class pms_out ON pms_out.security_class_id = min_output '
                 . 'INNER JOIN (           SELECT security_id, min(classification) as min_classification FROM ( '
                 . '                       SELECT security_id, reference as classification FROM m_security_detail '
                 . '             UNION ALL SELECT security_id, registration as classification FROM m_security_detail '
                 . '             UNION ALL SELECT security_id, delete as classification FROM m_security_detail '
                 . '             UNION ALL SELECT security_id, approval as classification FROM m_security_detail '
                 . '             UNION ALL SELECT security_id, printing as classification FROM m_security_detail '
                 . '             UNION ALL SELECT security_id, output as classification FROM m_security_detail  ) local_class '
                 . '             GROUP BY security_id ) classification ON classification.security_id = ms.security_id ';

            // 参照権限による検索条件追加
            $sql .= $this->creatConditionsSQL( $postArray, $searchArray );
            
            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * セキュリティマスタ一覧取得条件SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   セキュリティマスタ一覧取得SQL文
         */
        private function creatConditionsSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatConditionsSQL");

            // 参照権限による検索条件追加
            $searchedColumn = ' WHERE organization.';
            $sql = $this->creatSqlWhereInConditions($searchedColumn);

            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND ms.is_del = :is_del ';
                $searchArray = array_merge($searchArray, array(':is_del' => $postArray['is_del'],));
            }

            if( !empty( $postArray['comment'] ) )
            {
                $sql .= ' AND ms.comment like :comment ';
                $commentVal = "%" . $postArray['comment'] . "%";
                $searchArray = array_merge($searchArray, array(':comment' => $commentVal,));
            }

            // 検索条件作成
            $searchSqlList = array(
                                    'searchClassID'     =>  ' AND classification.min_classification = :searchClassID ',     // 区分
                                    'organizationName'  =>  ' AND ms.organization_id = :organizationName ',                 // 組織ID
                                    'security'          =>  ' AND ms.security_name = :security ',                           // セキュリティ名
                                    'optionName'        =>  ' AND mdi.name = :optionName ',                                 // 表示項目名
                                    'reference'         =>  ' AND min_msd.min_reference = :reference ',                     // 参照
                                    'registration'      =>  ' AND min_msd.min_registration = :registration ',               // 登録
                                    'delete'            =>  ' AND min_msd.min_delete = :delete ',                           // 削除
                                    'approval'          =>  ' AND min_msd.min_approval = :approval ',                       // 承認
                                    'printing'          =>  ' AND min_msd.min_printing = :printing ',                       // 印刷
                                    'output'            =>  ' AND min_msd.min_output = :output ',                           // 出力
                                    );

            foreach( $searchSqlList as $key => $val )
            {
                if( !empty( $postArray[$key] ) )
                {
                    $sql .= $val;
                    $setKey = ":" . $key;
                    $searchArray = array_merge($searchArray, array( $setKey => $postArray[$key],));
                }
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );
  
            $Log->trace("END creatConditionsSQL");
            return $sql;
        }

        /**
         * セキュリティマスタ一覧の表示順のSQL文
         * @param    $sortNo          ソートNo
         * @return   セキュリティマスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");
            
            // ソート条件作成
            $sortSqlList = array(
                                3  => ' ORDER BY classification.min_classification DESC, organization.organization_id, ms.disp_order',         // 区分の降順
                                4  => ' ORDER BY classification.min_classification, organization.organization_id, ms.disp_order',              // 区分の昇順
                                5  => ' ORDER BY mod.abbreviated_name DESC, organization.organization_id, ms.disp_order',                      // 組織名の降順
                                6  => ' ORDER BY mod.abbreviated_name, organization.organization_id, ms.disp_order',                           // 組織名の昇順
                                7  => ' ORDER BY ms.security_name DESC, organization.organization_id, ms.disp_order ',                         // セキュリティ名の降順
                                8  => ' ORDER BY ms.security_name, organization.organization_id, ms.disp_order ',                              // セキュリティ名の降順
                                9  => ' ORDER BY ms.display_item_id DESC, organization.organization_id, ms.disp_order ',                       // 表示項目の降順
                                10 => ' ORDER BY ms.display_item_id, organization.organization_id, ms.disp_order',                             // 表示項目の昇順
                                11 => ' ORDER BY min_msd.min_reference DESC, organization.organization_id, ms.disp_order ',                    // 参照の降順
                                12 => ' ORDER BY min_msd.min_reference, organization.organization_id, ms.disp_order ',                         // 参照の昇順
                                13 => ' ORDER BY min_msd.min_registration DESC, organization.organization_id, ms.disp_order ',                 // 登録の降順
                                14 => ' ORDER BY min_msd.min_registration, organization.organization_id, ms.disp_order ',                      // 登録の昇順
                                15 => ' ORDER BY min_msd.min_delete DESC, organization.organization_id, ms.disp_order ',                       // 削除の降順
                                16 => ' ORDER BY min_msd.min_delete, organization.organization_id, ms.disp_order ',                            // 削除の昇順
                                17 => ' ORDER BY min_msd.min_approval DESC, organization.organization_id, ms.disp_order ',                     // 承認の降順
                                18 => ' ORDER BY min_msd.min_approval, organization.organization_id, ms.disp_order ',                          // 承認の昇順
                                19 => ' ORDER BY min_msd.min_printing DESC, organization.organization_id, ms.disp_order',                      // 印刷の降順
                                20 => ' ORDER BY min_msd.min_printing , organization.organization_id, ms.disp_order',                          // 印刷の昇順
                                21 => ' ORDER BY min_msd.min_output DESC, organization.organization_id, ms.disp_order',                        // 出力の降順
                                22 => ' ORDER BY min_msd.min_output , organization.organization_id, ms.disp_order',                            // 出力の昇順
                                23 => ' ORDER BY ms.comment DESC, organization.organization_id, ms.disp_order ',                               // コメントの昇順
                                24 => ' ORDER BY ms.comment, organization.organization_id, ms.disp_order ',                                    // コメントの降順
                                25 => ' ORDER BY ms.disp_order DESC, organization.organization_id',                                            // 表示順の降順
                                26 => ' ORDER BY ms.disp_order, organization.organization_id',                                                 // 表示順の昇順
                                27 => ' ORDER BY ms.is_del DESC, organization.organization_id, ms.disp_order',                                 // 状態の降順
                                28 => ' ORDER BY ms.is_del, organization.organization_id, ms.disp_order',                                      // 状態の昇順
                                );

            
            $sql = $this->creatSort( $sortNo, $sortSqlList );

            $Log->trace("END creatSortSQL");

            return $sql;
        }

        /**
         * セキュリティマスタ新規データ登録
         * @param    $postArray
         * @return   SQLの実行結果
         */
        private function addSecurityNewData( $postArray )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START addSecurityNewData");
            $sql = ' INSERT INTO m_security( '
                  . '                    organization_id '
                  . '                  , display_item_id '
                  . '                  , security_name '
                  . '                  , comment '
                  . '                  , is_del '
                  . '                  , disp_order '
                  . '                  , registration_time '
                  . '                  , registration_user_id '
                  . '                  , registration_organization '
                  . '                  , update_time '
                  . '                  , update_user_id '
                  . '                  , update_organization '
                  . '                  ) VALUES ('
                  . '                  :organization_id'
                  . '                  , :display_item_id'
                  . '                  , :security_name'
                  . '                  , :comment'
                  . '                  , :is_del'
                  . '                  , :disp_order'
                  . '                  , current_timestamp'
                  . '                  , :registration_user_id'
                  . '                  , :registration_organization'
                  . '                  , current_timestamp'
                  . '                  , :update_user_id'
                  . '                  , :update_organization)';

            $parameters = array(
                ':organization_id'           => $postArray['organizationName'],
                ':display_item_id'           => $postArray['optionName'],
                ':comment'                   => $postArray['comment'],
                ':security_name'             => $postArray['security_name'],
                ':is_del'                    => $postArray['isDel'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $DBA->executeSQL( $sql, $parameters, true );
            $Log->trace("END addSecurityNewData");
            return $ret;
        }


        /**
         * セキュリティ詳細マスタ新規データ登録
         * @param    $postArray
         * @return   SQLの実行結果
         */
        private function addSecurityDetailNewData( $securityID, $accessAuthorityID, $reference, $registration, $delete, $approval, $printing, $output )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START addSecurityNewData");


            $sql = ' INSERT INTO m_security_detail( '
                  . '                    security_id '
                  . '                  , access_authority_id'
                  . '                  , reference'
                  . '                  , registration'
                  . '                  , delete'
                  . '                  , approval'
                  . '                  , printing'
                  . '                  , output'
                  . '                  ) VALUES ('
                  . '                    :security_id'
                  . '                  , :access_authority_id'
                  . '                  , :reference'
                  . '                  , :registration'
                  . '                  , :delete'
                  . '                  , :approval'
                  . '                  , :printing'
                  . '                  , :output)';

            $parameters = array(
                ':security_id'                   => $securityID,
                ':access_authority_id'           => $accessAuthorityID,
                ':reference'                     => $reference,
                ':registration'                  => $registration,
                ':delete'                        => $delete,
                ':approval'                      => $approval,
                ':printing'                      => $printing,
                ':output'                        => $output,
            );

            // 新規登録SQLの実行
            $ret = $DBA->executeSQL( $sql, $parameters );
            
            $Log->trace("END addSecurityDetailNewData");

            return $ret;
        }

        /**
         * セキュリティマスタ更新
         * @param    $postArray
         * @return   SQLの実行結果($displayItemId/0)
         */
        private function modSecurityUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modSecurityUpdateData");

            $sql = ' UPDATE m_security SET '
                 . '   organization_id = :organization_id '
                 . ' , display_item_id = :display_item_id '
                 . ' , security_name = :security_name '
                 . ' , comment = :comment '
                 . ' , disp_order = :disp_order '
                 . ' , update_time = current_timestamp '
                 . ' , update_user_id = :update_user_id '
                 . ' , update_organization = :update_organization  '
                 . 'WHERE  security_id = :security_id AND update_time = :update_time ';

            $parameters = array(
                ':security_id'               => $postArray['securityID'],
                ':organization_id'           => $postArray['organizationName'],
                ':display_item_id'           => $postArray['optionName'],
                ':comment'                   => $postArray['comment'],
                ':security_name'             => $postArray['security_name'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':update_time'               => $postArray['updateTime'],

            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, true);
            
            $Log->trace("END modSecurityUpdateData");

            return $ret;
        }


        /**
         * セキュリティ詳細マスタ更新
         * @param    $securityID  セキュリティID
         * @return   SQLの実行結果(MSG_BASE_0000/MSG_FW_LOGIN_FRAUD)
         */
        private function modSecurityDetailDel( $securityID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modSecurityDetailDel");

            $sql = ' DELETE FROM m_security_detail WHERE security_id = :security_id ';
            $parameters = array( ':security_id' => $securityID, );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, true);

            $Log->trace("END modSecurityDetailDel");
            return $ret;
        }

        /**
         * セキュリティ詳細マスタの登録
         * @param    $securityID  セキュリティID
         * @return   SQLの実行結果(MSG_BASE_0000/MSG_FW_LOGIN_FRAUD)
         */
        private function setSecurityDetail( $securityID, $referenceList, $registrationList, $deleteList, $approvalList, $printingList, $outputList, $accessIDList )
        {
            global $Log; // グローバル変数宣言
            
            $Log->trace("START setSecurityDetail");
            
            // セキュリティマスタへの登録
            $arrayCount = count( $referenceList );
            for( $i = 0; $i < $arrayCount; $i++ )
            {
                $ret = $this->addSecurityDetailNewData( $securityID, 
                                                        $accessIDList[$i], 
                                                        $referenceList[$i],
                                                        $registrationList[$i],
                                                        $deleteList[$i],
                                                        $approvalList[$i],
                                                        $printingList[$i],
                                                        $outputList[$i]
                                                      );
                if( $ret === false )
                {
                    // コミットエラー  ロールバック対応
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "詳細情報の登録に失敗致しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END setSecurityDetail");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            
            $Log->trace("END setSecurityDetail");
            return "MSG_BASE_0000";
        }

        /**
         * 削除してよいか
         * @param    $securityID     セキュリティID
         * @return   true：削除可   false：削除不可
         */
        private function isDel( $securityID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isDel");

            $sql = " SELECT COUNT( user_id ) as count "
                 . " FROM v_user WHERE is_del = 0 AND status <> '退職' AND "
                 . "      ( eff_code = '適用中' OR eff_code = '適用予定' ) AND security_id = :security_id ";
            $parametersArray = array( ':security_id'	=> $securityID,);
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END isDel");
                return false;
                
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] == 0 )
                {
                    return true;
                }
            }

            $Log->trace("END isDel");

            return false;
        }

    }

?>