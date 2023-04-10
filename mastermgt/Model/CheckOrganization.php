<?php
    /**
     * @file      組織マスタ更新チェッククラス
     * @author    USE R.dendo
     * @date      2016/7/11
     * @version   1.00
     * @note      組織マスタテーブルの更新チェックを行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 組織マスタ更新チェッククラス
     * @note   組織マスタテーブルの更新チェックを行う
     */
    class CheckOrganization extends BaseModel
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
         * 入力データのエラーチェック
         * @param    $postArray     画面からの入力情報
         * @param    $addFlag       追加フラグ
         * @return   LOGメッセージ
         */
        protected function isInputError( $postArray, $addFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isInputError");

            // 適用開始日のエラー判定
            // 更新予定適用開始日をUnixtimestamp形式に変換
            $regApplicationUNIX = strtotime($postArray['application_date_start']);
            // 新規登録時以外、変更した適用開始時が現在日時より過去だった場合エラーとする
            if( $addFlag )
            {
                // 既存登録適用開始日をUnixtimestamp形式に変換
                $comApplicationUNIX = strtotime($postArray['up_application_date_start']);
                // 現在日時をUnixtimestamp形式にて取得
                $timestamp = time();
                if($regApplicationUNIX != $comApplicationUNIX &&$timestamp > $regApplicationUNIX)
                {
                    $Log->trace("END isInputError");
                    return "MSG_ERR_3093";
                }
                
                // 適用年月日の重複エラー判定 
                if( $this->applicationDateStartErrorCheck( $postArray['organizationID'], $postArray['applicationDateStart'] ) )
                {
                    $Log->trace("END isInputError");
                    return "MSG_ERR_3100";
                }
            }

            // 部門コード重複エラー判定 
            if( $this->departmentCodeErrorCheck( $postArray['organizationID'], $postArray['departmentCode'], $postArray['payrollFormat'] ) )
            {
                $Log->trace("END isInputError");
                return "MSG_ERR_3101";
            }

            // 給与フォーマットエラー判定 
            if( $this->isSalaryFormatError( $postArray['payrollFormat'] ) )
            {
                $Log->trace("END isInputError");
                return "MSG_ERR_3096";
            }

            // 上位組織の循環参照チェック
            if( $this->isCircularReference( $postArray['organizationID'], $postArray['upperLevelOrganization'] ) )
            {
                $Log->trace("END isInputError");
                return "MSG_ERR_3098";
            }
            
            // 上位組織の存在チェック(登録組織の適用日よりも、上位組織の適用日が前であるか)
            if( !$this->isPresent( $postArray['applicationDateStart'], $postArray['upperLevelOrganization'] ) )
            {
                $Log->trace("END isInputError");
                return "MSG_ERR_3102";
            }
            
            // 認証キーの重複チェック
            if( $this->authenticationKeyErrorCheck( $postArray['authenticationKey'], $postArray['organizationID'] ) )
            {
                $Log->trace("END isInputError");
                return "MSG_ERR_3097";
            }

            // 就業規則の重複チェック
            if( $this->priorityErrorCheck( $postArray['priority_o'], $postArray['priority_p'], $postArray['priority_e'] ) )
            {
                $Log->trace("END isInputError");
                return "MSG_ERR_3099";
            }
    
            return "MSG_BASE_0000";
        }

        /**
         * 削除してよいか
         * @param    $organizationID   組織ID
         * @return   true：削除可   false：削除不可
         */
        protected function isDel( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isDel");

            $relevantMasterList = array(
                                            "m_section",
                                            "m_holiday_name",
                                            "m_holiday",
                                            "m_allowance",
                                            "m_labor_regulations",
                                            "m_display_item",
                                            "m_payroll_system_cooperation",
                                            "m_position",
                                            "m_employment",
                                            "m_security",
                                        );

            // 各SQLで使用するパラメータ設定
            $parametersArray = array( ':organization_id'	=> $organizationID,);

            // 同一仕様のマスタテーブルでの使用有無をチェック
            foreach( $relevantMasterList as $registration )
            {
                $sql = " SELECT COUNT( organization_id ) as count "
                     . " FROM " . $registration . " WHERE is_del = 0 AND organization_id = :organization_id ";
                
                $result = $DBA->executeSQL($sql, $parametersArray);

                if( $result === false )
                {
                    $Log->trace("END isDel");
                    return false;
                    
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    if( $data['count'] != 0 )
                    {
                        return false;
                    }
                }
            }

            $sql = " SELECT COUNT( organization_id ) as count "
                 . " FROM v_user WHERE is_del = 0 AND status <> '退職' AND "
                 . "      ( eff_code = '適用中' OR eff_code = '適用予定' ) AND organization_id = :organization_id ";
            
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END isDel");
                return false;
                
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] != 0 )
                {
                    return false;
                }
            }

            $Log->trace("END isDel");

            // 全てで未使用の場合、trueを返す
            return true;
        }

        /**
         * 給与フォーマットのエラーチェック
         * @param    $payrollFormatID
         * @return   従業員Noの重複無：false   従業員Noの重複有：true
         */
        private function isSalaryFormatError( $payrollFormatID )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START isSalaryFormatError");

            // 給与フォーマットエラー判定
            $sql = ' SELECT COUNT( vu.employees_no ) '
                 . ' FROM      v_user vu INNER JOIN v_organization vo '
                 . '        ON vu.organization_id = vo.organization_id '
                 . " WHERE     vu.eff_code like '適用中' AND vu.status like '在籍' "
                 . "       AND vo.is_del = 0 AND vo.eff_code like '適用中' "
                 . '       AND vo.payroll_system_id = :payroll_system_id '
                 . ' GROUP BY vu.employees_no HAVING COUNT(vu.employees_no) <> 1 ';
            $searchArray = array( ':payroll_system_id' => $payrollFormatID, );

            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END isSalaryFormatError");
                return true;
            }
            
            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] > 0 )
                {
                    $ret = true;
                }
            }

            // 返り値
            $Log->trace("END isSalaryFormatError");
            return $ret;
        }
        
        /**
         * 上位組織の循環参照チェック
         * @param    $organization           現在の組織ID
         * @param    $upLevelOrganization    上位組織ID
         * @return   循環無：false  循環有：true
         */
        private function isCircularReference( $organization, $upLevelOrganization )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isCircularReference");

            // 新規登録時は、チェック対象外とする
            if( empty($organization) )
            {
                $Log->trace("END isCircularReference");
                return false;
            }

            // 同じ組織IDが指定されている場合、エラーとする
            if( $organization === $upLevelOrganization )
            {
                $Log->trace("END isCircularReference");
                return true;
            }

            $upperLevelList = $this->securityProcess->getLevelOrganizationList( $upLevelOrganization );
            
            $ret = false;
            foreach( $upperLevelList as $upperLevel )
            {
                if( $upperLevel['upper_level_organization'] == $organization )
                {
                    $ret = true;
                    break;
                }
            }

            // 返り値
            $Log->trace("END isCircularReference");
            return $ret;
        }

        /**
         * 上位組織の存在チェック(登録組織の適用日よりも、上位組織の適用日が前であるか)
         * @param    $applicationDateStart   現在の組織の適用日
         * @param    $upLevelOrganization    上位組織ID
         * @return   存在する：true    存在しない：false
         */
        private function isPresent( $applicationDateStart, $upLevelOrganization )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START isPresent");

            if( empty( $upLevelOrganization ) )
            {
                // 上位組織の指定がない場合、チェックしない
                $Log->trace("END isPresent");
                return true;
            }

            // 上位組織の存在確認
            $sql = ' SELECT COUNT ( abbreviated_name ) FROM  v_organization '
                 . ' WHERE is_del = 0 AND application_date_start <= :application_date_start AND organization_id = :organization_id ';
            $searchArray = array(   
                                    ':application_date_start' => $applicationDateStart,
                                    ':organization_id'        => $upLevelOrganization,
                                );
            $result = $DBA->executeSQL($sql, $searchArray);

            if( $result === false )
            {
                $Log->trace("END isPresent");
                return false;
            }

            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] > 0 )
                {
                    $ret = true;
                }
            }

            // 返り値
            $Log->trace("END isPresent");
            return $ret;
        }

        /**
         * 部門コードの重複チェック
         * @param    $organizationID        組織ID
         * @param    $departmentCode        部門コード
         * @param    $payrollFormatID       給与フォーマットID
         * @return   重複無：false  重複有：true
         */
        private function departmentCodeErrorCheck( $organizationID, $departmentCode, $payrollFormatID )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START departmentCodeErrorCheck");

            // 部門コードの重複エラー判定
            $sql = ' SELECT COUNT( organization_id ) as count '
                 . ' FROM   v_organization'
                 . ' WHERE  department_code = :departmentCode ';
            
            $searchArray = array( ':departmentCode'   => $departmentCode, );
            
            if( !is_null( $organizationID ) )
            {
                $sql .= ' AND organization_id != :organization_id ';
                $searchArray = array_merge($searchArray, array(   ':organization_id'  => $organizationID, ) );
            }
            
            if( !is_null( $payrollFormatID ) )
            {
                $sql .= ' AND payroll_system_id = :payroll_system_id ';
                $searchArray = array_merge($searchArray, array(   ':payroll_system_id' => $payrollFormatID,) );
            }
            
            $result = $DBA->executeSQL($sql, $searchArray);

            if( $result === false )
            {
                $Log->trace("END departmentCodeErrorCheck");
                return true;
            }
            
            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] > 0 )
                {
                    $ret = true;
                }
            }
            // 返り値
            $Log->trace("END departmentCodeErrorCheck");
            return $ret;
        }

        /**
         * 適用年月日の重複チェック
         * @param    $organizationID        組織ID
         * @param    $applicationDateStart  適用年月日
         * @return   重複無：false  重複有：true
         */
        private function applicationDateStartErrorCheck( $organizationID, $applicationDateStart )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START applicationDateStartErrorCheck");

            // 適用年月日の重複エラー判定
            $sql = ' SELECT COUNT( organization_id ) as count '
                 . ' FROM   v_organization'
                 . ' WHERE  organization_id = :organization_id AND application_date_start = :application_date_start ';
            
            $searchArray = array(   
                                    ':organization_id'        => $organizationID,
                                    ':application_date_start' => $applicationDateStart,
                                );
            
            $result = $DBA->executeSQL($sql, $searchArray);

            if( $result === false )
            {
                $Log->trace("END applicationDateStartErrorCheck");
                return true;
            }
            
            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] > 0 )
                {
                    $ret = true;
                }
            }
            // 返り値
            $Log->trace("END applicationDateStartErrorCheck");
            return $ret;
        }

        /**
         * 認証キーの重複チェック
         * @param    $authenticationKey  
         * @return   重複無：false  重複有：true
         */
        private function authenticationKeyErrorCheck( $authenticationKey, $organizationID)
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START authenticationKeyErrorCheck");

            // 認証キー重複エラー判定
            $sql = ' SELECT COUNT( authentication_key )'
                 . ' FROM      v_organization'
                 . ' WHERE     authentication_key = :authentication_key ';
            $searchArray = array(   ':authentication_key' => $authenticationKey,);

            if( !is_null($organizationID) )
            {
                $sql .= ' AND organization_id != :organization_id ';
                $searchArray = array_merge($searchArray, array(   ':organization_id' => $organizationID,) );
            }

            $result = $DBA->executeSQL($sql, $searchArray);

            if( $result === false )
            {
                $Log->trace("END authenticationKeyErrorCheck");
                return true;
            }
            
            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] > 0 )
                {
                    $ret = true;
                }
            }
            // 返り値
            $Log->trace("END authenticationKeyErrorCheck");
            return $ret;
        }
 
        /**
         * 就業規則優先順位の未設定チェック
         * @param    $priority_o, $priority_p, $priority_e  
         * @return   重複無：false  重複有：true
         */
        private function priorityErrorCheck( $priority_o, $priority_p, $priority_e )
        {
            global $Log;        // グローバル変数宣言
            $Log->trace("START priorityErrorCheck");

            // 優先順位が全て未設定の場合、エラーとする
            if( ( $priority_o == 0 && $priority_p == 0 && $priority_e == 0 ) )
            {
                $Log->trace("END isCircularReference");
                return true;
            }

            // 組織が未選択
            if( $priority_o == 0 )
            {
                $Log->trace("END isCircularReference");
                return true;
            }

            // 返り値
            $Log->trace("END priorityErrorCheck");
            return false;
        }
    }
?>
