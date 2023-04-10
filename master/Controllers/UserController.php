<?php
    /**
     * @file      従業員コントローラ
     * @author    USE M.Higashihara
     * @date      2016/06/09
     * @version   1.00
     * @note      従業員の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 従業員モデル
    require './Model/User.php';
    
    /**
     * 従業員コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class UserController extends BaseController
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
        }

         /**
         * デストラクタ
         */
        public function __destruct()
        {
            // BaseControllerのデストラクタ
            parent::__destruct();
        }

        /**
         * 従業員一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1080" );

            $startFlag = true;
            if(isset($_POST['back']))
            {
                $startFlag = false;
            }

            $this->initialDisplay($startFlag);
            $Log->trace("END   showAction");
        }
        
        /**
         * 従業員一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1081" );
            
            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = $_POST['displayPageNo'];
            }
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = $_POST['displayRecordCnt'];
            }
            
            $this->initialList(false);
            
            $Log->trace("END searchAction");
            
        }

        /**
         * 従業員情報入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addInputAction" );
            $Log->info( "MSG_INFO_1082" );

            $this->userInputPanelDisplay();

            $Log->trace("END addInputAction");
            
        }

        /**
         * 従業員新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1083" );

            $user = new User();

            $startFlag = false;

            $postArray = array(
                'application_date_start'           => parent::escStr( $_POST['application_date_start'] ),
                'employees_no'                     => parent::escStr( $_POST['employees_no'] ),
                'user_name'                        => parent::escStr( $_POST['user_name'] ),
                'hire_date'                        => parent::escStr( $_POST['hire_date'] ),
                'leaving_date'                     => parent::escStr( $_POST['leaving_date'] ),
                'login_id'                         => parent::escStr( $_POST['login_id'] ),
                'password'                         => parent::escStr( $_POST['password'] ),
                'birthday'                         => parent::escStr( $_POST['birthday'] ),
                'sex'                              => parent::escStr( $_POST['sex'] ),
                'address'                          => parent::escStr( $_POST['address'] ),
                'tel'                              => parent::escStr( $_POST['tel'] ),
                'cellphone'                        => parent::escStr( $_POST['cellphone'] ),
                'mail_address'                     => parent::escStr( $_POST['mail_address'] ),
                'comment'                          => parent::escStr( $_POST['comment'] ),
                'organization_id'                  => parent::escStr( $_POST['organization_id'] ),
                'position_id'                      => parent::escStr( $_POST['position_id'] ),
                'employment_id'                    => parent::escStr( $_POST['employment_id'] ),
                'section_id'                       => parent::escStr( $_POST['section_id'] ),
                'security_id'                      => parent::escStr( $_POST['security_id'] ),
                'is_embossing'                     => $this->isBoolToInt( parent::escStr( $_POST['is_embossing'] ) ),
                'wage_form_id'                     => parent::escStr( $_POST['wage_form_id'] ),
                'hourly_wage'                      => parent::escStr( $_POST['hourly_wage'] ),
                'base_salary'                      => parent::escStr( $_POST['base_salary'] ),
                'trial_period_type_id'             => parent::escStr( $_POST['trial_period_type_id'] ),
                'trial_period_criteria_value'      => parent::escStr( $_POST['trial_period_criteria_value'] ),
                'trial_period_write_down_criteria' => parent::escStr( $_POST['trial_period_write_down_criteria'] ),
                'trial_period_wages_value'         => parent::escStr( $_POST['trial_period_wages_value'] ),
                'is_del'                           => 0,
                'user_id'                          => $_SESSION["USER_ID"],
                'organization'                     => $_SESSION["ORGANIZATION_ID"],
                'up_user_id'                       => "",
                'up_user_detail_id'                => "",
                'up_application_date_start'        => "",
                'up_employees_no'                  => "",
            );

            // 登録用承認組織リスト（メソッド内でエスケープ処理済）
            $approvalArray = $this->toOrganizeAnArray( $_POST['approvalList'], "organization_id");

            // 登録用手当IDリスト（メソッド内でエスケープ処理済）
            $allowanceArray = $this->toOrganizeAnArray( $_POST['allowanceList'], "allowance_id");

            // 登録用手当金額リスト（メソッド内でエスケープ処理済）
            $amountArray = $this->toOrganizeAnArray( $_POST['amountList'], "allowance_amount");

            $allowanceBondList = array();
            $allCnt = 0;
            foreach($allowanceArray as $allowance)
            {
                $allowanceBond = array(
                    'allowance_id'     => $allowance['allowance_id'],
                    'allowance_amount' => $amountArray[$allCnt]['allowance_amount'],
                );
                array_push($allowanceBondList, $allowanceBond);
                $allCnt++;
            }

            // 新規登録フラグ
            $addFlag = true;

            $message = $user->addNewData($postArray, $approvalArray, $allowanceBondList, $addFlag);
            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
            $Log->trace("END addAction");
        }

        /**
         * 登録従業員更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1084" );

            $user = new User();

            $startFlag = false;

            $postArray = array(
                'application_date_start'           => parent::escStr( $_POST['application_date_start'] ),
                'employees_no'                     => parent::escStr( $_POST['employees_no'] ),
                'user_name'                        => parent::escStr( $_POST['user_name'] ),
                'hire_date'                        => parent::escStr( $_POST['hire_date'] ),
                'leaving_date'                     => parent::escStr( $_POST['leaving_date'] ),
                'login_id'                         => parent::escStr( $_POST['login_id'] ),
                'password'                         => parent::escStr( $_POST['password'] ),
                'birthday'                         => parent::escStr( $_POST['birthday'] ),
                'sex'                              => parent::escStr( $_POST['sex'] ),
                'address'                          => parent::escStr( $_POST['address'] ),
                'tel'                              => parent::escStr( $_POST['tel'] ),
                'cellphone'                        => parent::escStr( $_POST['cellphone'] ),
                'mail_address'                     => parent::escStr( $_POST['mail_address'] ),
                'comment'                          => parent::escStr( $_POST['comment'] ),
                'organization_id'                  => parent::escStr( $_POST['organization_id'] ),
                'position_id'                      => parent::escStr( $_POST['position_id'] ),
                'employment_id'                    => parent::escStr( $_POST['employment_id'] ),
                'section_id'                       => parent::escStr( $_POST['section_id'] ),
                'security_id'                      => parent::escStr( $_POST['security_id'] ),
                'is_embossing'                     => $this->isBoolToInt( parent::escStr( $_POST['is_embossing'] ) ),
                'wage_form_id'                     => parent::escStr( $_POST['wage_form_id'] ),
                'hourly_wage'                      => parent::escStr( $_POST['hourly_wage'] ),
                'base_salary'                      => parent::escStr( $_POST['base_salary'] ),
                'trial_period_type_id'             => parent::escStr( $_POST['trial_period_type_id'] ),
                'trial_period_criteria_value'      => parent::escStr( $_POST['trial_period_criteria_value'] ),
                'trial_period_write_down_criteria' => parent::escStr( $_POST['trial_period_write_down_criteria'] ),
                'trial_period_wages_value'         => parent::escStr( $_POST['trial_period_wages_value'] ),
                'user_id'                          => $_SESSION["USER_ID"],
                'organization'                     => $_SESSION["ORGANIZATION_ID"],
                'up_user_id'                       => parent::escStr( $_POST['userId'] ),
                'up_user_detail_id'                => parent::escStr( $_POST['userDtailID'] ),
                'up_application_date_start'        => parent::escStr( $_POST['applicationDateStart'] ),
                'up_employees_no'                  => parent::escStr( $_POST['employeesNo'] ),
                'updateTime'                       => parent::escStr( $_POST['updateTime'] ),
            );

            // 登録用承認組織リスト（メソッド内でエスケープ処理済）
            $approvalArray = $this->toOrganizeAnArray( $_POST['approvalList'], "organization_id");
            // 登録用手当IDリスト（メソッド内でエスケープ処理済）
            $allowanceArray = $this->toOrganizeAnArray( $_POST['allowanceList'], "allowance_id");

            // 登録用手当金額リスト（メソッド内でエスケープ処理済）
            $amountArray = $this->toOrganizeAnArray( $_POST['amountList'], "allowance_amount");

            // 手当IDリストと手当金額リストを結合して返す
            $allowanceBondList = $this->getAllowanceBondList($allowanceArray, $amountArray);

            // 新規登録フラグ
            $addFlag = false;

            $message = $user->addNewData($postArray, $approvalArray, $allowanceBondList, $addFlag);

            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END modAction");
        }

        /**
         * 登録従業員削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1085" );

            $user = new User();

            $startFlag = false;

            $postArray = array(
                'is_del'                => 1,
                'del_user_id'           => parent::escStr( $_POST['userId'] ),
                'update_time'           => parent::escStr( $_POST['updateTime'] ),
                'userDtailID'           => parent::escStr( $_POST['userDtailID'] ),
                'applicationDateStart'  => parent::escStr( $_POST['applicationDateStart'] ),
                'user_id'               => $_SESSION["USER_ID"],
                'organization'          => $_SESSION["ORGANIZATION_ID"],
            );

            $message = $user->delUpdateData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END delAction");
        }

        /**
         * 所属情報の更新
         * @note     入力画面で組織プルダウンの値を変更した時に所属情報を更新する
         * @return   所属情報
         */
        public function searchAffiliationInfoAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchAffiliationInfoAction");
            $Log->info( "MSG_INFO_1086_1" );

            $user = new User();
            $securityProcess = new SecurityProcess();

            $organizationID = parent::escStr( $_POST['organization_id'] );

            // 所属組織
            $abbreviatedList = $user->setPulldown->getSearchOrganizationList( 'registration', true, true );

            // 役職情報
            $targetOrganization = $securityProcess->getTopOrganizationID( $organizationID, 'm_position');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $positionList = $user->setPulldown->getRegPulldownList($targetOrganization, 'position_id', 'position_name', 'm_position');

            // 雇用形態
            $targetOrganization = $securityProcess->getTopOrganizationID( $organizationID, 'm_employment');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $employmentList = $user->setPulldown->getRegPulldownList($targetOrganization, 'employment_id', 'employment_name', 'm_employment');

            // セクション
            $targetOrganization = $securityProcess->getTopOrganizationID( $organizationID, 'm_section');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $sectionList = $user->setPulldown->getRegPulldownList($targetOrganization, 'section_id', 'section_name', 'm_section');

            // セキュリティ
            $targetOrganization = $securityProcess->getTopOrganizationID( $organizationID, 'm_security');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $securityList = $user->setPulldown->getRegPulldownList($targetOrganization, 'security_id', 'security_name', 'm_security');
            
            // 各データの初期設定
            $userDataList = array();
            $userDataList['hire_date']       = parent::escStr( $_POST['hire_date'] );
            $userDataList['leaving_date']    = parent::escStr( $_POST['leaving_date'] );
            $userDataList['organization_id'] = $organizationID;
            $userDataList['is_embossing']    = $this->isBoolToInt( parent::escStr( $_POST['is_embossing'] ) );

            require_once './View/Common/SearchAffiliationInformation.php';
            $Log->trace("END searchAffiliationInfoAction");
        }

        /**
         * 登録用試用期間の更新
         * @note     従業員新規登録時に選択した組織、役職、雇用形態によって試用期間の初期表示を変える
         * @return   なし
         */
        public function searchRegisterTrialAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchRegisterTrialAction");
            $Log->info( "MSG_INFO_1086_6" );

            $user = new User();
            $trialPeriodList = $user->setPulldown->getSearchTrialPeriodCrieriaList();
            
            if(!empty($_POST['organization_id']))
            {
                $priorityList = $user->getLaborRegulationsPriority(parent::escStr( $_POST['organization_id'] ));
                $topPriority = array_search(1, $priorityList);
                $secondPriority = array_search(2, $priorityList);
                $thirdPriority = array_search(3, $priorityList);

                $trialArray = $this->getDefaultTrialValue($topPriority, parent::escStr( $_POST['organization_id'] ), parent::escStr( $_POST['position_id'] ), parent::escStr( $_POST['employment_id'] ) );
                if(!empty($trialArray['trial_period_type_id']))
                {
                    $Log->trace("END searchRegisterTrialAction");
                    require_once './View/Common/SearchRegisterTrial.php';
                    return;
                }

                $trialArray = $this->getDefaultTrialValue($secondPriority, parent::escStr( $_POST['organization_id'] ), parent::escStr( $_POST['position_id'] ), parent::escStr( $_POST['employment_id'] ) );
                if(!empty($trialArray['trial_period_type_id']))
                {
                    $Log->trace("END searchRegisterTrialAction");
                    require_once './View/Common/SearchRegisterTrial.php';
                    return;
                }

                $trialArray = $this->getDefaultTrialValue($thirdPriority, parent::escStr( $_POST['organization_id'] ), parent::escStr( $_POST['position_id'] ), parent::escStr( $_POST['employment_id'] ) );
                if(!empty($trialArray['trial_period_type_id']))
                {
                    $Log->trace("END searchRegisterTrialAction");
                    require_once './View/Common/SearchRegisterTrial.php';
                    return;
                }
            }

            require_once './View/Common/SearchRegisterTrial.php';
            $Log->trace("END searchRegisterTrialAction");
        }

        /**
         * 登録用手当リストの更新
         * @note     入力画面で組織プルダウンの値を変更した時にセキュリティプルダウンの値を変更する
         * @return   登録用セキュリティリスト
         */
        public function searchRegisterAllowanceAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchRegisterAllowanceAction");
            $Log->info( "MSG_INFO_1086_5" );

            $user = new User();
            $securityProcess = new SecurityProcess();

            $targetOrganization = $securityProcess->getTopOrganizationID(parent::escStr( $_POST['organization_id'] ), 'm_allowance');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }

            $allowanceList = $user->setPulldown->getRegPulldownList($targetOrganization, 'allowance_id', 'allowance_name', 'm_allowance');

            // ajaxでのコード更新
            $isChange = $_GET['isChange'];

            require_once './View/Common/SearchRegisterAllowance.php';
            $Log->trace("END searchRegisterAllowanceAction");
        }

        /**
         * 登録用試用期間の更新
         * @note     
         * @param    $priorityArray
         * @param    $organization_id
         * @param    $position_id
         * @param    $employment_id
         * @return   登録用セキュリティリスト
         */
        private function getDefaultTrialValue($priorityFlag, $organization_id, $position_id, $employment_id)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getDefaultTrialValue");

            $user = new User();

            $trialArray = array();
            if($priorityFlag == 'priority_o')
            {
                $organizationDetailId = $user->getAppliedOrganizationDetailId($organization_id);
                $trialArray = $user->laborRegulationsSetProbation($organizationDetailId, 'm_organization_detail', 'organization_detail_id');
            }
            else if($priorityFlag == 'priority_p')
            {
                if(!empty($_POST['position_id']))
                {
                    $trialArray = $user->laborRegulationsSetProbation($position_id, 'm_position', 'position_id');
                }
            }
            else
            {
                if(!empty($_POST['employment_id']))
                {
                    $trialArray = $user->laborRegulationsSetProbation($employment_id, 'm_employment', 'employment_id');
                }
            }

            $Log->trace("END getDefaultTrialValue");

            return $trialArray;
        }

        

        /**
         * 従業員一覧画面
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            // 従業員モデルインスタンス化
            $user = new user();

            // 検索用プルダウン
            $abbreviatedNameList = $user->setPulldown->getSearchOrganizationList( 'reference', true, true );    // 組織略称名リスト
            $sectionNameList     = $user->setPulldown->getSearchSectionList( 'reference' );               // セクションリスト
            $userNoList          = $user->setPulldown->getSearchUserNoList( 'reference' );                // 従業員Noリスト
            $positionNameList    = $user->setPulldown->getSearchPositionList( 'reference' );              // 役職リスト
            $employmentNameList  = $user->setPulldown->getSearchEmploymentList( 'reference' );            // 雇用形態名リスト
            $wageFormList        = $user->setPulldown->getSearchWageFormList();                           // 賃金形態リスト
            $securityList        = $user->setPulldown->getSearchSecurityList( 'reference' );              // セキュリティリスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            // 検索バー用チッェクボックスの状況を配列に入れる
            if(!empty($startFlag))
            {
                $effArray = array(1,0,0,0);
                $stateArray = array(1,0,0);
            }
            else
            {
                $effArray = array();
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplyCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplySchCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchNonApplyCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchDelCheck']);
                $stateArray = array();
                $stateArray = $this->booleanTypeChangeIntger($stateArray, $_POST['searchDuringCheck']);
                $stateArray = $this->booleanTypeChangeIntger($stateArray, $_POST['searchRetirementCheck']);
                $stateArray = $this->booleanTypeChangeIntger($stateArray, $_POST['searchTrialCheck']);
            }
            // チェックボックス状況の配列を2進数に直したうえで10進数にして返す
            $effFlag = $this->getStateFlag($effArray);
            $statusFlag = $this->getStateFlag($stateArray);

            // 一覧表用検索項目
            $searchArray = array(
                'is_del'                => 0,
                'organizationID'        => parent::escStr( $_POST['searchOrganizationID'] ),
                'userNo'                => parent::escStr( $_POST['searchUserNo'] ),
                'userName'              => parent::escStr( $_POST['searchUserName'] ),
                'sectionName'           => parent::escStr( $_POST['searchSectionName'] ),
                'positionName'          => parent::escStr( $_POST['searchPositionName'] ),
                'employmentName'        => parent::escStr( $_POST['searchEmploymentName'] ),
                'wageForm'              => parent::escStr( $_POST['searchWageForm'] ),
                'minBaseSalaey'         => parent::escStr( $_POST['searchMinBaseSalary'] ),
                'maxBaseSalaey'         => parent::escStr( $_POST['searchMaxBaseSalary'] ),
                'minHourlyWage'         => parent::escStr( $_POST['searchMinHourlyWage'] ),
                'maxHourlyWage'         => parent::escStr( $_POST['searchMaxHourlyWage'] ),
                'sAppStartDate'         => parent::escStr( $_POST['searchSAppStartDate'] ),
                'eAppStartDate'         => parent::escStr( $_POST['searchEAppStartDate'] ),
                'startHireDate'         => parent::escStr( $_POST['searchSHireDate'] ),
                'endHireDate'           => parent::escStr( $_POST['searchEHireDate'] ),
                'startLeavingDate'      => parent::escStr( $_POST['searchSLeavingDate'] ),
                'endLeavingDate'        => parent::escStr( $_POST['searchELeavingDate'] ),
                'security'              => parent::escStr( $_POST['searchSecurity'] ),
                'approval'              => parent::escStr( $_POST['searchApproval'] ),
                'comment'               => parent::escStr( $_POST['searchComment'] ),
                'sort'                  => 2,
            );

            // 従業員一覧データ取得
            $userAllList = $user->getListData($searchArray, $effFlag, $statusFlag);
            // 従業員一覧レコード数
            $userRecordCnt = count($userAllList);
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($userRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "user_id";
            // 一覧表
            $userList = $this->refineListDisplayNoSpecifiedPage($userAllList, $idName, $pagedRecordCnt, $pageNo);
            $userList = $this->modBtnDelBtnDisabledCheck($userList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($userList) >= 15)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->


            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            $pagedCnt = ceil($userRecordCnt /  $pagedRecordCnt);
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソートマーク初期化
            $headerArray = $this->changeHeaderItemMark(0);
            // NO表示用
            $display_no = 0;
            // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
            $userNoSortFlag = false;

            require_once './View/UserPanel.html';

            $Log->trace("END   initialDisplay");
        }


        /**
         * 従業員テーブル画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialList()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialList");

            $user = new user();

            // シーケンスIDName
            $idName = "user_id";

            // 承認組織
            $searchApproval = parent::escStr( $_POST['searchApproval'] );
            // 組織階層を削除
            if( $searchApproval != '' )
            {
                $searchApproval = str_replace( "｜", "", $searchApproval );
                $searchApproval = str_replace( "　", "", $searchApproval );
                $searchApproval = str_replace( "├", "", $searchApproval );
                $searchApproval = str_replace( "└", "", $searchApproval );
            }

            $searchArray = array(
                'is_del'                => 0,
                'organizationID'        => parent::escStr( $_POST['searchOrganizationID'] ),
                'userNo'                => parent::escStr( $_POST['searchUserNo'] ),
                'userName'              => parent::escStr( $_POST['searchUserName'] ),
                'sectionName'           => parent::escStr( $_POST['searchSectionName'] ),
                'positionName'          => parent::escStr( $_POST['searchPositionName'] ),
                'employmentName'        => parent::escStr( $_POST['searchEmploymentName'] ),
                'wageForm'              => parent::escStr( $_POST['searchWageForm'] ),
                'minBaseSalaey'         => parent::escStr( $_POST['searchMinBaseSalary'] ),
                'maxBaseSalaey'         => parent::escStr( $_POST['searchMaxBaseSalary'] ),
                'minHourlyWage'         => parent::escStr( $_POST['searchMinHourlyWage'] ),
                'maxHourlyWage'         => parent::escStr( $_POST['searchMaxHourlyWage'] ),
                'sAppStartDate'         => parent::escStr( $_POST['searchSAppStartDate'] ),
                'eAppStartDate'         => parent::escStr( $_POST['searchEAppStartDate'] ),
                'startHireDate'         => parent::escStr( $_POST['searchSHireDate'] ),
                'endHireDate'           => parent::escStr( $_POST['searchEHireDate'] ),
                'startLeavingDate'      => parent::escStr( $_POST['searchSLeavingDate'] ),
                'endLeavingDate'        => parent::escStr( $_POST['searchELeavingDate'] ),
                'security'              => parent::escStr( $_POST['searchSecurity'] ),
                'approval'              => $searchApproval,
                'comment'               => parent::escStr( $_POST['searchComment'] ),
                'sort'                  => parent::escStr( $_POST['sortConditions'] ),
            );

            // 適用状態の表示フラグ
            $effArray = array();
            $effArray = $this->booleanTypeChangeIntger($effArray, parent::escStr( $_POST['searchApplyCheck'] ) );
            $effArray = $this->booleanTypeChangeIntger($effArray, parent::escStr( $_POST['searchApplySchCheck'] ) );
            $effArray = $this->booleanTypeChangeIntger($effArray, parent::escStr( $_POST['searchNonApplyCheck'] ) );
            $effArray = $this->booleanTypeChangeIntger($effArray, parent::escStr( $_POST['searchDelCheck'] ) );
            $stateArray = array();
            $stateArray = $this->booleanTypeChangeIntger($stateArray, parent::escStr( $_POST['searchDuringCheck'] ) );
            $stateArray = $this->booleanTypeChangeIntger($stateArray, parent::escStr( $_POST['searchRetirementCheck'] ) );
            $stateArray = $this->booleanTypeChangeIntger($stateArray, parent::escStr( $_POST['searchTrialCheck'] ) );
            $effFlag = $this->getStateFlag($effArray);
            $statusFlag = $this->getStateFlag($stateArray);

            // 従業員一覧データ取得
            $userAllList = $user->getListData($searchArray, $effFlag, $statusFlag);
            // 従業員一覧レコード数
            $userRecordCnt = count($userAllList);
            // セクション一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の従業員レコードに対するページ数
            $pagedCnt = ceil($userRecordCnt /  $pagedRecordCnt);
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];
            // 検索後の一覧画面
            $userList = $this->refineListDisplayNoSpecifiedPage($userAllList, $idName, $pagedRecordCnt, $pageNo);
            $userList = $this->modBtnDelBtnDisabledCheck($userList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($userList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
            // NO表示用
            $display_no = $this->getDisplayNo( $userRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
            // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
            $userNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            require_once './View/UserTablePanel.html';

            $Log->trace("END   initialList");
        }

        /**
         * 従業員入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function userInputPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START userInputPanelDisplay" );

            $user = new User();
            $securityProcess = new SecurityProcess();

            $userDataList = array();
            $userDataList['organization_id'] = 0;
            // 修正時フラグ（false）
            $CorrectionFlag = false;
            // 登録データ更新時
            if(!empty($_POST['userDtailID']))
            {
                $userDataList = $user->getUserData( parent::escStr( $_POST['userDtailID'] ) );
                // 承認組織IDを1元配列に入れ直しjavascriptへ受け渡すため、JSON形式へ変換する
                $approvalViewArray = $user->getApprovalOrganizationList( parent::escStr( $_POST['userDtailID'] ) );
                $appCnt = count($approvalViewArray);
                $approvalCnt = 0;
                if($appCnt > 0)
                {
                    $approvalCnt = $appCnt - 1;
                }
                $jsonApprovalArray = json_encode($approvalViewArray);
                // 手当IDと手当金額をそれぞれを1元配列に入れ直しjavascriptへ受け渡すため、JSON形式へ変換する
                $allowanceList = $user->getUserAllowanceList( parent::escStr( $_POST['userDtailID'] ) );
                $allowanceViewArray = $this->changeStringArrayIntArray($allowanceList, true);
                $amountViewArray = $this->changeStringArrayIntArray($allowanceList, false);
                $allCnt = count($allowanceViewArray);
                $allowanceCnt = 0;
                if($allCnt > 0)
                {
                    $allowanceCnt = $allCnt -1;
                }
                $jsonAllowanceArray = json_encode($allowanceViewArray);
                $jsonAmountArray = json_encode($amountViewArray);
                // 対象従業員の所属組織に対する削除権限の判定
                $del_disabled = $this->checkDeleteTarget($userDataList['organization_id']);
                // 更新フラグ
                $CorrectionFlag = true;
            }

            // 登録用所属組織プルダウン
            $abbreviatedList = $user->setPulldown->getSearchOrganizationList( 'registration', true, true );
            
            // 役職情報
            $targetOrganization = $securityProcess->getTopOrganizationID( $userDataList['organization_id'], 'm_position');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $positionList = $user->setPulldown->getRegPulldownList($targetOrganization, 'position_id', 'position_name', 'm_position');

            // 雇用形態
            $targetOrganization = $securityProcess->getTopOrganizationID( $userDataList['organization_id'], 'm_employment');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $employmentList = $user->setPulldown->getRegPulldownList($targetOrganization, 'employment_id', 'employment_name', 'm_employment');

            // セクション
            $targetOrganization = $securityProcess->getTopOrganizationID( $userDataList['organization_id'], 'm_section');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $sectionList = $user->setPulldown->getRegPulldownList($targetOrganization, 'section_id', 'section_name', 'm_section');

            // セキュリティ
            $targetOrganization = $securityProcess->getTopOrganizationID( $userDataList['organization_id'], 'm_security');
            if(empty($targetOrganization))
            {
                $targetOrganization = 0;
            }
            $securityList = $user->setPulldown->getRegPulldownList($targetOrganization, 'security_id', 'security_name', 'm_security');

            // 賃金形態プルダウン
            $wageFormList = $user->setPulldown->getSearchWageFormList();
            // 試用期間プルダウン
            $trialPeriodList = $user->setPulldown->getSearchTrialPeriodCrieriaList();

            // 手当マスタ
            $allowanceList = $user->setPulldown->getRegPulldownList( $userDataList['organization_id'], 'allowance_id', 'allowance_name', 'm_allowance');

            require_once './View/UserInputPanel.html';
            $Log->trace("END userInputPanelDisplay");
        }

        /**
         * 手当IDListと手当額リストの結合
         * @note     登録時に使用
         * @param    $allowanceArray
         * @param    $amountArray
         * @return   $allowanceBondList
         */
        private function getAllowanceBondList($allowanceArray, $amountArray)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getAllowanceBondList");

            $allowanceBondList = array();
            $allCnt = 0;
            foreach($allowanceArray as $allowance)
            {
                $allowanceBond = array(
                    'allowance_id'     => $allowance['allowance_id'],
                    'allowance_amount' => $amountArray[$allCnt]['allowance_amount'],
                );
                array_push($allowanceBondList, $allowanceBond);
                $allCnt++;
            }

            $Log->trace("END getAllowanceBondList");

            return $allowanceBondList;
        }

        /**
         * POSTで帰ってきた検索チェックボックスの値をboolean型から0/1へ変換する
         * @param    $checkArray
         * @param    $checkFlag
         * @return   $checkArray
         */
        private function booleanTypeChangeIntger($checkArray, $checkFlag )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START booleanTypeChangeIntger");

            array_push($checkArray, $checkFlag === 'true' ? 1 : 0);

            $Log->trace("END booleanTypeChangeIntger");

            return $checkArray;
        }

        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     ソート番号により、ソートマークを設定する
         * @param    $sortNo ソート条件番号
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark( $sortNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'userNoSortMark'          => '',
                    'userStateSortMark'       => '',
                    'userEnrollmentSortMark'  => '',
                    'userEmployeesNoSortMark' => '',
                    'userNameSortMark'        => '',
                    'userTelSortMark'         => '',
                    'userAbbreviatedSortMark' => '',
                    'userPositionSortMark'    => '',
                    'userEmploymentSortMark'  => '',
                    'userSectionSortMark'     => '',
                    'userWageFormSortMark'    => '',
                    'userBaseSalarySortMark'  => '',
                    'userHourlyWageSortMark'  => '',
                    'userHireDateSortMark'    => '',
                    'userLeavingDateSortMark' => '',
                    'userDateStartSortMark'   => '',
                    'userApprovalSortMark'    => '',
                    'userSecuritySortMark'    => '',
                    'userCommentSortMark'     => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "userNoSortMark";
                $sortList[2] = "userNoSortMark";
                $sortList[3] = "userStateSortMark";
                $sortList[4] = "userStateSortMark";
                $sortList[5] = "userEnrollmentSortMark";
                $sortList[6] = "userEnrollmentSortMark";
                $sortList[7] = "userEmployeesNoSortMark";
                $sortList[8] = "userEmployeesNoSortMark";
                $sortList[9] = "userNameSortMark";
                $sortList[10] = "userNameSortMark";
                $sortList[11] = "userTelSortMark";
                $sortList[12] = "userTelSortMark";
                $sortList[13] = "userAbbreviatedSortMark";
                $sortList[14] = "userAbbreviatedSortMark";
                $sortList[15] = "userPositionSortMark";
                $sortList[16] = "userPositionSortMark";
                $sortList[17] = "userEmploymentSortMark";
                $sortList[18] = "userEmploymentSortMark";
                $sortList[19] = "userSectionSortMark";
                $sortList[20] = "userSectionSortMark";
                $sortList[21] = "userWageFormSortMark";
                $sortList[22] = "userWageFormSortMark";
                $sortList[23] = "userBaseSalarySortMark";
                $sortList[24] = "userBaseSalarySortMark";
                $sortList[25] = "userHourlyWageSortMark";
                $sortList[26] = "userHourlyWageSortMark";
                $sortList[27] = "userHireDateSortMark";
                $sortList[28] = "userHireDateSortMark";
                $sortList[29] = "userLeavingDateSortMark";
                $sortList[30] = "userLeavingDateSortMark";
                $sortList[31] = "userDateStartSortMark";
                $sortList[32] = "userDateStartSortMark";
                $sortList[33] = "userApprovalSortMark";
                $sortList[34] = "userApprovalSortMark";
                $sortList[35] = "userSecuritySortMark";
                $sortList[36] = "userSecuritySortMark";
                $sortList[37] = "userCommentSortMark";
                $sortList[38] = "userCommentSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START changeHeaderItemMark");

            return $headerArray;
        }

        /**
         * チェックボックスのエスケープ処理
         * @note     チェックボックスの入力結果をINT型へ変換
         * @return   true：1  false：0
         */
        private function isBoolToInt( $bool )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START isBoolToInt");

            $ret = $bool === 'true' ? 1 : 0;

            $Log->trace("END   isBoolToInt");
            
            return $ret;
        }

    }
?>