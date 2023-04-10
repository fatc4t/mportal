<?php
    /**
     * @file      手当マスタ
     * @author    USE S.Kasai
     * @date      2016/06/15
     * @version   1.00
     * @note      手当マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * ログインクラス
     * @note   ログイン時の認証処理及び、セッションの初期設定を行う
     */
    class Allowance extends BaseModel
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
         * 手当マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/allowanceName/allowanceNameID/sort)
         * @return   成功時：$allowanceList(organization_id/allowance_name/allowance_name_id/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );
            $result = $DBA->executeSQL($sql, $searchArray);

            $allowanceDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $allowanceDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $allowanceDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $allowanceList = $allowanceDataList;
            }
            else
            {
                $allowanceList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $allowanceDataList);
                if( $postArray['sort'] == 1 )
                {
                    $allowanceList = array_reverse($allowanceList);
                }
            }

            $Log->trace("END getListData");

            return $allowanceList;
        }

        /**
         * 手当マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/手当名称名/手当名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_allowance( organization_id'
                . '                    , code'
                . '                    , allowance_name'
                . '                    , wage_form_type_id'
                . '                    , payment_conditions_id'
                . '                    , working_hours'
                . '                    , working_days'
                . '                    , comment'
                . '                    , is_del'
                . '                    , disp_order'
                . '                    , registration_time'
                . '                    , registration_user_id'
                . '                    , registration_organization'
                . '                    , update_time'
                . '                    , update_user_id'
                . '                    , update_organization'
                . '                    ) VALUES ('
                . '                    :organization_id'
                . '                    , :code'
                . '                    , :allowance_name'
                . '                    , :wage_form_type_id'
                . '                    , :payment_conditions_id'
                . '                    , :working_hours'
                . '                    , :working_days'
                . '                    , :comment'
                . '                    , :is_del'
                . '                    , :disp_order'
                . '                    , current_timestamp'
                . '                    , :registration_user_id'
                . '                    , :registration_organization'
                . '                    , current_timestamp'
                . '                    , :update_user_id'
                . '                    , :update_organization)';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['allowanceCode'],
                ':allowance_name'            => $postArray['allowanceName'],
                ':wage_form_type_id'         => $postArray['wageFormTypeID'],
                ':payment_conditions_id'     => $postArray['paymentConditionsID'],
                ':working_hours'             => $postArray['paymentConditionsHours'],
                ':working_days'              => $postArray['paymentConditionsDays'],
                ':comment'                   => $postArray['comment'],
                ':is_del'                    => $postArray['is_del'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_allowance" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 手当マスタ登録データ修正
         * @param    $postArray   入力パラメータ(手当ID/コード/組織ID/手当名称名/手当名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_allowance SET'
                . ' organization_id = :organization_id'
                . ' , code = :code'
                . ' , allowance_name = :allowance_name'
                . ' , wage_form_type_id = :wage_form_type_id'
                . ' , payment_conditions_id = :payment_conditions_id'
                . ' , working_hours = :working_hours'
                . ' , working_days = :working_days'
                . ' , comment = :comment'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE allowance_id = :allowance_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['allowanceCode'],
                ':allowance_name'            => $postArray['allowanceName'],
                ':wage_form_type_id'         => $postArray['wageFormTypeID'],
                ':payment_conditions_id'     => $postArray['paymentConditionsID'],
                ':working_hours'             => $postArray['paymentConditionsHours'],
                ':working_days'              => $postArray['paymentConditionsDays'],
                ':comment'                   => $postArray['comment'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':allowance_id'              => $postArray['allowanceID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 手当マスタ登録データ削除
         * @param    $postArray   入力パラメータ(手当ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");
            
            $id_name = "allowance_id";
            $inUseArray = $this->getInUseAllowanceCheckList($id_name);
            $intAllowanceId = intval($postArray['allowanceID']);
            if(in_array($intAllowanceId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_allowance SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE allowance_id = :allowance_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':allowance_id'              => $postArray['allowanceID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 手当マスタの検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = ' SELECT DISTINCT code, organization_id FROM m_allowance '
                 . ' WHERE is_del = :is_del ORDER BY code ';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $codeList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchCodeList");
                return $codeList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($codeList, $data);
            }

            $outList = array();
            $outList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $codeList );

            $initial = array('code' => '',);
            array_unshift($outList, $initial);

            $column = "code";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchCodeList");
            return $outList;
        }
        
        /**
         * 支給単位設定時の支給条件のプルダウン
         * @return   支給単位に紐付く支給条件リスト 
         */
        public function getSearchPaymentConditionsEditList($searchPayCndID)
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchPaymentConditionsEditList");

            // 支給条件を取得
            $sql = ' SELECT payment_conditions_id, payment_conditions_name, disp_order FROM public.m_payment_conditions '
                 . ' ORDER BY disp_order ';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $payCndList = array();
            if( $result === false )
            {
                $Log->trace("END getSearchPaymentConditionsEditList");
                return $payCndList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($payCndList, $data);
            }
            $initial = array('payment_conditions_id' => '', 'payment_conditions_name' => '');
            array_unshift($payCndList, $initial);
            
            if( $searchPayCndID == 1 )
            {
                unset($payCndList[5]);
                unset($payCndList[4]);
            }
            if( $searchPayCndID == 2 )
            {
                unset($payCndList[3]);
            }
            if( $searchPayCndID == 4 )
            {
                unset($payCndList[3]);
            }
            if( $searchPayCndID == 3 )
            {
                unset($payCndList[4]);
                unset($payCndList[3]);
                unset($payCndList[2]);
            }
            
            $Log->trace("END getSearchPaymentConditionsEditList");
            return $payCndList;
        }

        /**
         * 手当マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/allowanceName/allowanceNameID/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   手当マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchedColumn = ' AND organization.';
            $sqlWhereIn = $this->creatSqlWhereInConditions($searchedColumn);

            $sql = ' SELECT a.allowance_id, a.allowance_name, a.is_del, a.code, a.wage_form_type_id, mpu.payment_unit_name, a.payment_conditions_id, mpc.payment_conditions_name, '
                 . '         a.update_time, a.disp_order, organization.organization_id, mod.abbreviated_name, a.working_hours, a.working_days, a.comment '
                 . ' FROM m_allowance a INNER JOIN m_organization_detail mod ON a.organization_id = mod.organization_id '
                 . '       INNER JOIN public.m_payment_unit mpu ON a.wage_form_type_id = mpu.payment_unit_id '
                 . '       INNER JOIN public.m_payment_conditions mpc ON a.payment_conditions_id = mpc.payment_conditions_id, '
                 . '     ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                 . '       FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                 . '       WHERE od.application_date_start <= current_date AND o.is_del = 0 '
                 . '       GROUP BY od.organization_id, od.department_code, o.disp_order'
                 . '       ORDER BY o.disp_order, od.department_code ) organization '
                 . ' WHERE a.organization_id = organization.organization_id '
                 . '       AND mod.application_date_start = organization.application_date_start ';
            $sql .= $sqlWhereIn;

            $sql .= $this->creatWhereSQL($postArray, $searchArray);

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 手当マスタ一覧の検索条件リスト作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/allowanceName/allowanceNameID/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   手当マスタ一覧検索条件リストSQL文
         */
        private function creatWhereSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatWhereSQL");

            $searchArray = array();
            $sql = "";
            
            // 検索条件リスト作成
            $whereSqlList = array(
                                'organizationID'      =>  ' AND a.organization_id = :organizationID ',              // 組織
                                'allowanceName'       =>  ' AND a.allowance_name = :allowanceName ',                // 手当名
                                'wageFormTypeID'      =>  ' AND a.wage_form_type_id = :wageFormTypeID ',            // 支給単位
                                'paymentConditionsID' =>  ' AND a.payment_conditions_id = :paymentConditionsID ',   // 支給条件
                                'codeID'              =>  ' AND a.code = :codeID ',                                 // コード
                            );
            
            foreach($whereSqlList as $key => $val)
            {
                if( !empty( $postArray[$key] ) )
                {
                    $sql .= $val;
                    $param = ':' . $key;
                    $searchParamArray = array( $param => $postArray[$key],);
                    $searchArray = array_merge($searchArray, $searchParamArray);
                }
                if( !empty( $postArray['comment'] ) )
                {
                    $sql .= ' AND a.comment LIKE :comment ';
                    $commentArray = array(':comment' => "%" . $postArray['comment'] . "%" ,);
                    $searchArray = array_merge($searchArray, $commentArray);
                }
                if( $postArray['is_del'] == 0 )
                {
                    $sql .= ' AND a.is_del = :is_del ';
                    $isDelArray = array(':is_del' => $postArray['is_del'],);
                    $searchArray = array_merge($searchArray, $isDelArray);
                }
            }

            $Log->trace("END creatWhereSQL");
            return $sql;
        }

        /**
         * 手当マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   手当マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY a.disp_order, a.code ';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY a.is_del DESC, organization.organization_id, a.disp_order, a.code',                               // 状態の降順
                                4       =>  ' ORDER BY a.is_del, organization.organization_id, a.disp_order, a.code',                                    // 状態の昇順
                                5       =>  ' ORDER BY a.code DESC, organization.organization_id, a.disp_order',                                         // コードの降順
                                6       =>  ' ORDER BY a.code, organization.organization_id, a.disp_order',                                              // コードの昇順
                                7       =>  ' ORDER BY mod.abbreviated_name DESC, organization.organization_id, a.disp_order, a.code',                   // 組織名の降順
                                8       =>  ' ORDER BY mod.abbreviated_name, organization.organization_id, a.disp_order, a.code',                        // 組織名の昇順
                                9       =>  ' ORDER BY a.allowance_name DESC, organization.organization_id, a.disp_order, a.code',                       // 手当名の降順
                                10      =>  ' ORDER BY a.allowance_name, organization.organization_id, a.disp_order, a.code',                            // 手当名の昇順
                                11      =>  ' ORDER BY a.wage_form_type_id DESC, organization.organization_id, a.disp_order, a.code',                    // 支給単位の降順
                                12      =>  ' ORDER BY a.wage_form_type_id, organization.organization_id, a.disp_order, a.code',                         // 支給単位の昇順
                                13      =>  ' ORDER BY a.payment_conditions_id DESC, organization.organization_id, a.disp_order, a.code',                // 支給条件の降順
                                14      =>  ' ORDER BY a.payment_conditions_id, organization.organization_id, a.disp_order, a.code',                     // 支給条件の昇順
                                15      =>  ' ORDER BY a.working_hours DESC, a.working_days DESC, organization.organization_id, a.disp_order, a.code',   // 支給条件詳細の降順
                                16      =>  ' ORDER BY a.working_hours, a.working_days, organization.organization_id, a.disp_order, a.code',             // 支給条件詳細の昇順
                                17      =>  ' ORDER BY a.comment DESC, organization.organization_id, a.disp_order, a.code',                              // コメントの降順
                                18      =>  ' ORDER BY a.comment, organization.organization_id, a.disp_order, a.code',                                   // コメントの昇順
                                19      =>  ' ORDER BY a.disp_order DESC, organization.organization_id, a.code',                                         // 表示順の降順
                                20      =>  ' ORDER BY a.disp_order, organization.organization_id, a.code',                                              // 表示順の昇順
                            );
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");
            return $sql;
        }
    }
?>
