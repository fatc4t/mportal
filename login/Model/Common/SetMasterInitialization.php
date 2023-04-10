<?php
    /**
     * @file    M-PORTAL管理 初期化用クラス(Model)
     * @author  USE Y.Sakata
     * @date    2016/07/01
     * @version 1.00
     * @note    M-PORTAL管理で使用する初期データの設定について定義する
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';

    /**
     * M-PORTAL管理 初期化用クラス
     * @note    M-PORTAL管理 初期化の処理を定義
     */
    class SetMasterInitialization extends FwBaseClass
    {
        // DBアクセスクラス
        protected $DBA = null;    ///< DBアクセスクラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }

        /**
         * M-PORTAL管理システムの初期化
         * @note     ログイン時に行う勤怠処理の初期化
         * @param     $user_id   ユーザID
         * @return   無
         */
        public function setMasterInit( $user_id )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceInit");

            // 使用IDの初期設定
            $this->setInitID( $user_id );
            // 管理機能用のメニューリスト設定
            $this->setMenuList();
            // マスタごとの参照テーブル一覧設定
            $_SESSION["M_ACCESS_AUTHORITY_TABLE"] = $this->getAccessAuthorityTableList();
            // 勤怠管理システムの各画面アクセス権限(参照)の設定
            $_SESSION["ACCESS_MENU_LIST"] = $this->getAccessMenuList();
            // 勤怠管理システムの各画面アクセス権限(登録)の設定
            $_SESSION["WRITE_MENU_LIST"] = $this->getWriteMenuList();
            // 勤怠管理システムの各画面アクセス権限(削除)の設定
            $_SESSION["DELETE_MENU_LIST"] = $this->getDeleteMenuList();
            // 勤怠管理システムの各画面アクセス権限(承認)の設定
            $_SESSION["APPROVAL_MENU_LIST"] = $this->getApprovalMenuList();
            // 勤怠管理システムの各画面アクセス権限(印刷)の設定
            $_SESSION["PRINT_MENU_LIST"] = $this->getPrintMenuList();
            // 勤怠管理システムの各画面アクセス権限(印刷)の設定
            $_SESSION["OUTPUT_MENU_LIST"] = $this->getOutputMenuList();
            // 祝日情報リストの設定
            $_SESSION["A_PUBLIC_HOLIDAY_LIST"] = $this->getPublicHolidayInfoList();

            $Log->trace("END setAttendanceInit");
        }

        /**
         * M-PORTAL管理システムのID設定
         * @note     ログイン時に行う、ログイン者固有情報を設定
         * @param     $user_id   ユーザID
         * @return   無
         */
        private function setInitID( $user_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->debug("START setInitID");



            $sql =  ' SELECT u.user_id, u.employees_no, u.organization_id, u.position_id, u.employment_id, '
                 .  '        u.section_id, u.wage_form_id, u.security_id, u.user_name, s.security_name, s.display_item_id '
                 .  ' , ( SELECT payroll_system_id FROM m_organization_detail od '
                 .  '         , ( SELECT organization_id, max( application_date_start ) as application_date_start '
                 .  '             FROM m_organization_detail WHERE organization_id = u.organization_id '
                 .  '             AND application_date_start <= current_timestamp GROUP BY organization_id ) nowdata '
                 .  '     WHERE od.organization_id = nowdata.organization_id AND od.application_date_start = nowdata.application_date_start ) '
                 .  ' FROM m_user_detail u INNER JOIN m_security s ON u.security_id = s.security_id '
                 .  ' WHERE u.application_date_start <= current_timestamp AND u.user_id = :user_id '
                 .  ' ORDER BY u.application_date_start DESC offset 0 limit 1';

            

            $parametersArray = array( ':user_id' => $user_id, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END setInitID");
                return;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $_SESSION["USER_ID"]             = $data["user_id"];
                $_SESSION["EMPLOYEES_NO"]        = $data["employees_no"];
                $_SESSION["ORGANIZATION_ID"]     = $data["organization_id"];
                $_SESSION["POSITION_ID"]         = $data["position_id"];
                $_SESSION["EMPLOYMENT_ID"]       = $data["employment_id"];
                $_SESSION["SECTION_ID"]          = $data["section_id"];
                $_SESSION["WAGE_FORM_ID"]        = $data["wage_form_id"];
                $_SESSION["SECURITY_ID"]         = $data["security_id"];
                $_SESSION["USER_NAME"]           = $data["user_name"];
                $_SESSION["SECURITY_NAME"]       = $data["security_name"];
                $_SESSION["DISPLAY_ITEM_ID"]     = $data["display_item_id"];
                $_SESSION["PAYROLL_SYSTEM_ID"]   = $data["payroll_system_id"];
                $_SESSION["PAGE_NO"]             = 1;
                $_SESSION["DISPLAY_RECORD_CNT"]  = 10;
                $_SESSION["GROUP_ID"]             = $data["group_id"];
            }

            //薬局がログインした場合
            $_SESSION['PHA_ID'] = "";
            if($_SESSION["SECURITY_ID"]=="9") {
              $sql =  ' SELECT pha_id FROM ord_m_pharmacy WHERE user_id = :user_id ';
              $parametersArray = array( ':user_id' => $user_id, );
              $result = $DBA->executeSQL($sql, $parametersArray);
              if( $result === false )
              {
                  $Log->trace("END setInitID");
                  return;
              }
              $row    = $result->fetch(PDO::FETCH_ASSOC);
              if($row) {
                $_SESSION['PHA_ID'] = $row['pha_id'];
              }
            }
            //薬剤メーカーがログインした場合
            $_SESSION['MA_CD'] = "";
            if($_SESSION["SECURITY_ID"]=="8") {
              $sql =  ' SELECT ma_cd FROM ord_m_maker WHERE user_id = :user_id ';
              $parametersArray = array( ':user_id' => $user_id, );
              $result = $DBA->executeSQL($sql, $parametersArray);
              if( $result === false )
              {
                  $Log->trace("END setInitID");
                  return;
              }
              $row    = $result->fetch(PDO::FETCH_ASSOC);
              if($row) {
                $_SESSION['MA_CD'] = $row['ma_cd'];
              }
            }


            $Log->trace("END setInitID");

            return;
        }

        /**
         * 管理機能用のメニューリスト取得（左メニュー）
         * @note     管理機能用のメニューリスト取得
         * @return   管理メニューパスと画面名のリスト
         */
        private function setMenuList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setMenuList");

            $_SESSION["M_PORTAL_SYS_MENU"] = '';
            if( $this->isSysAmin() )
            {
                $_SESSION["M_PORTAL_SYS_MENU"] = array(
                                                        SystemParameters::$V_M_ACCESS_AUTHORITY      => "アクセス管理",
                                                    );
            }

            $_SESSION["M_MANAGEMENT_MENU"] = array(

                                                    SystemParameters::$V_M_ORGANIZATION              => "組織マスタ画面",
                                                    SystemParameters::$V_M_POSITION                  => "役職マスタ画面",
                                                    SystemParameters::$V_M_EMPLOYMENT                => "雇用形態マスタ画面",
                                                    SystemParameters::$V_M_USER                      => "従業員マスタ画面",
                                                    SystemParameters::$V_M_SECURITY                  => "セキュリティマスタ画面",
                                                    SystemParameters::$V_M_GROUP                     => "グループマスタ画面",
                                                    SystemParameters::$V_M_AREA                      => "エリアマスタ画面",
                                                    SystemParameters::$V_M_ERR                       => "エラー画面",
                                                );
            $_SESSION["M_HOME_MENU"]        = array(
                                                    SystemParameters::$V_M_HOME                      => "ホーム",
                                                );

            $_SESSION["M_INPUT_MENU"]      = array(
                                                    SystemParameters::$V_M_M_USER                    => "従業員マスタ編集画面",
                                                    SystemParameters::$V_M_M_SECURITY                => "セキュリティマスタ編集画面",
                                                );

            $Log->trace("END setMenuList");
        }

        /**
         * マスタごとの参照テーブル一覧取得
         * @note     マスタごとの参照テーブル一覧取得
         * @return   管理メニューパスとテーブルのリスト
         */
        private function getAccessAuthorityTableList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessAuthorityTableList");

            $ret = array(
                            SystemParameters::$V_M_AREA                  => "m_area",
                            SystemParameters::$V_M_ORGANIZATION          => "m_organization_detail",
                            SystemParameters::$V_M_POSITION              => "m_position",
                            SystemParameters::$V_M_EMPLOYMENT            => "m_employment",
                            SystemParameters::$V_M_USER                  => "m_user_detail",
                            SystemParameters::$V_M_SECURITY              => "m_security",
                            SystemParameters::$V_M_GROUP                 => "m_group",
                            SystemParameters::$V_A_SECTION               => "m_section",
                            SystemParameters::$V_A_HOLIDAY_NAME          => "m_holiday_name",
                            SystemParameters::$V_A_HOLIDAY               => "m_holiday",
                            SystemParameters::$V_A_ALLOWANCE             => "m_allowance",
                            SystemParameters::$V_A_LABOR_REGULATIONS     => "m_labor_regulations",
                            SystemParameters::$V_A_ORGANIZATION_CALENDAR => "m_organization_calendar",
                            SystemParameters::$V_A_DISPLAY_ITEM          => "m_display_item",
                            SystemParameters::$V_A_PAYROLL_SYSTEM        => "m_payroll_system_cooperation",
                            SystemParameters::$V_A_PAYROLL_DATA_OUT      => "",
                        );

            $Log->trace("END getAccessAuthorityTableList");
            return $ret;
        }


        /**
         * 全M-PORTALシステムのアクセス権限(参照)を取得
         * @note     全M-PORTALシステムのアクセス権限(参照)を取得
         * @return   全M-PORTALシステムのアクセス権限(参照)リスト
         */
        private function getAccessMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAccessMenuList");

            $sql   =  ' SELECT ma.url, ms.reference '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ms.security_id = :security_id AND ms.reference IN (1,2,3,4,5,8,9) '
                  .   ' ORDER BY ma.disp_order ';

            $parametersArray = array(
                ':security_id'   => $_SESSION["SECURITY_ID"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessMenuList = array();
            if( $result === false )
            {
                $Log->trace("END getAccessMenuList");
                return $accessMenuList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $accessMenu = array( $data['url']   => $data['reference'] );
                $accessMenuList = array_merge ($accessMenuList, $accessMenu);
            }

            $Log->trace("END getAccessMenuList");

            return $accessMenuList;
        }

        /**
         * 全M-PORTALシステムのアクセス権限(登録)を取得
         * @note     全M-PORTALシステムのアクセス権限(登録)を取得
         * @return   全M-PORTALシステムのアクセス権限(登録)リスト
         */
        private function getWriteMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getWriteMenuList");

            $sql   =  ' SELECT ma.url, ms.registration '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ms.security_id = :security_id AND ms.registration IN (1,2,3,4,5) '
                  .   ' ORDER BY ma.disp_order ';

            $parametersArray = array(
                ':security_id'   => $_SESSION["SECURITY_ID"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessMenuList = array();
            if( $result === false )
            {
                $Log->trace("END getWriteMenuList");
                return $accessMenuList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $accessMenu = array( $data['url']   => $data['registration'] );
                $accessMenuList = array_merge ($accessMenuList, $accessMenu);
            }

            $Log->trace("END getWriteMenuList");

            return $accessMenuList;
        }

        /**
         * 全M-PORTALシステムのアクセス権限(削除)を取得
         * @note     全M-PORTALシステムのアクセス権限(削除)を取得
         * @return   全M-PORTALシステムのアクセス権限(削除)リスト
         */
        private function getDeleteMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDeleteMenuList");

            $sql   =  ' SELECT ma.url, ms.delete '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ms.security_id = :security_id AND ms.delete IN (1,2,3,4,5) '
                  .   ' ORDER BY ma.disp_order ';

            $parametersArray = array(
                ':security_id'   => $_SESSION["SECURITY_ID"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessMenuList = array();
            if( $result === false )
            {
                $Log->trace("END getDeleteMenuList");
                return $accessMenuList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $accessMenu = array( $data['url']   => $data['delete'] );
                $accessMenuList = array_merge ($accessMenuList, $accessMenu);
            }

            $Log->trace("END getDeleteMenuList");

            return $accessMenuList;
        }

        /**
         * 全M-PORTALシステムのアクセス権限(承認)を取得
         * @note     全M-PORTALシステムのアクセス権限(承認)を取得
         * @return   全M-PORTALシステムのアクセス権限(承認)リスト
         */
        private function getApprovalMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDeleteMenuList");

            $sql   =  ' SELECT ma.url, ms.approval '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ms.security_id = :security_id AND ms.approval IN (1,2,3,4,5) '
                  .   ' ORDER BY ma.disp_order ';

            $parametersArray = array(
                ':security_id'   => $_SESSION["SECURITY_ID"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessMenuList = array();
            if( $result === false )
            {
                $Log->trace("END getApprovalMenuList");
                return $accessMenuList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $accessMenu = array( $data['url']   => $data['approval'] );
                $accessMenuList = array_merge ($accessMenuList, $accessMenu);
            }

            $Log->trace("END getApprovalMenuList");

            return $accessMenuList;
        }

        /**
         * 全M-PORTALシステムのアクセス権限(印刷)を取得
         * @note     全M-PORTALシステムのアクセス権限(印刷)を取得
         * @return   全M-PORTALシステムのアクセス権限(印刷)リスト
         */
        private function getPrintMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPrintMenuList");

            $sql   =  ' SELECT ma.url, ms.printing '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ms.security_id = :security_id AND ms.printing IN (1,2,3,4,5) '
                  .   ' ORDER BY ma.disp_order ';

            $parametersArray = array(
                ':security_id'   => $_SESSION["SECURITY_ID"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessMenuList = array();
            if( $result === false )
            {
                $Log->trace("END getPrintMenuList");
                return $accessMenuList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $accessMenu = array( $data['url']   => $data['printing'] );
                $accessMenuList = array_merge ($accessMenuList, $accessMenu);
            }

            $Log->trace("END getPrintMenuList");

            return $accessMenuList;
        }

        /**
         * 全M-PORTALシステムのアクセス権限(出力)を取得
         * @note     全M-PORTALシステムのアクセス権限(出力)を取得
         * @return   全M-PORTALシステムのアクセス権限(出力)リスト
         */
        private function getOutputMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOutputMenuList");

            $sql   =  ' SELECT ma.url, ms.output '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ms.security_id = :security_id AND ms.output IN (1,2,3,4,5) '
                  .   ' ORDER BY ma.disp_order ';

            $parametersArray = array(
                ':security_id'   => $_SESSION["SECURITY_ID"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessMenuList = array();
            if( $result === false )
            {
                $Log->trace("END getOutputMenuList");
                return $accessMenuList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $accessMenu = array( $data['url']   => $data['output'] );
                $accessMenuList = array_merge ($accessMenuList, $accessMenu);
            }

            $Log->trace("END getOutputMenuList");

            return $accessMenuList;
        }

        /**
         * ログイン企業がシステム管理企業であるか
         * @note     ログイン企業がシステム管理企業であるか判定
         * @return   true：システム管理企業　false：一般企業
         */
        private function isSysAmin()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isSysAmin");

            $sql   =  ' SELECT price_id '
                  .   ' FROM   public.m_company_contract '
                  .   ' WHERE  company_name = :company_name  ';

            $parametersArray = array(
                ':company_name'   => $_SESSION["SCHEMA"],
            );

            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END isSysAmin");
                return false;
            }

            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['price_id'] == 0 )
                {
                    $ret = true;
                }
            }

            $Log->trace("END isSysAmin");

            return $ret;
        }
    }

?>
