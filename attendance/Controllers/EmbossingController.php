<?php
    /**
     * @file      打刻管理コントローラ
     * @author    USE Y.Sakata
     * @date      2016/07/09
     * @version   1.00
     * @note      打刻管理の新規登録/修正/削除を行う
     */

    // BaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwDirectAccessController.php';
    // 打刻管理モデル
    require './Model/Embossing.php';

    /**
     * 打刻管理コントローラクラス
     * @note   打刻用機器からのアクセスも対応する
     */
    class EmbossingController extends FwDirectAccessController
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
         * 従業員登録可能、組織一覧の取得
         * @return   なし
         */
        public function getOrganizationAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOrganizationAction");
            $Log->info( "MSG_INFO_1080" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $key = isset($_GET['key']) ? parent::escStr($_GET['key']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $list = $mmbossing->getOrganizationList( $key );

            foreach($list as $data )
            {
                echo $data['organization_id'] . '/' . $data['authentication_key'] . '/' . $data['abbreviated_name'] . ',';
            }
            
            $Log->trace("END   getOrganizationAction");
        }
        
        /**
         * 従業員一覧の取得
         * @return   なし
         */
        public function getUserAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getUserAction");
            $Log->info( "MSG_INFO_1081" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $key = isset($_GET['key']) ? parent::escStr($_GET['key']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $list = $mmbossing->getUserList( $key );

            foreach($list as $data )
            {
                echo $data['organization_id'] . '/' . $data['employees_no'] . '/' . $data['employees_no'] . '：' .$data['user_name'] . ',';
            }
            
            $Log->trace("END   getUserAction");
        }

        /**
         * 従業員の生体情報を保存
         * @return   なし
         */
        public function setBiologicalInfoAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setBiologicalInfoAction");
            $Log->info( "MSG_INFO_1082" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $organID = parent::escStr( $_POST['organID'] );
            $biologicalInfo = $_POST['biologicalInfo'];
            $employeesNo = parent::escStr( $_POST['employeesNo'] );
            
            $ret = $mmbossing->setBiologicalInfo( $organID, $employeesNo, $biologicalInfo );
            
            $Log->info( $ret );
            echo $ret;
            
            $Log->trace("END   setBiologicalInfoAction");
        }

        /**
         * 打刻情報を保存
         * @return   なし
         */
        public function setEmbossingAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEmbossingAction");
            $Log->info( "MSG_INFO_1803" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $key = isset($_GET['key']) ? parent::escStr($_GET['key']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $organID = parent::escStr( $_POST['organID'] );
            $embossingType = parent::escStr( $_POST['embossingType'] );
            $employeesNo = parent::escStr( $_POST['employeesNo'] );
            $dateTime = parent::escStr( $_POST['dateTime'] );
            $isViolation = parent::escStr( $_POST['verify'] );
            
            $ret = $mmbossing->setEmbossing( $organID, $employeesNo, $key, $embossingType, $dateTime, $isViolation, 1 );
            
            //$Log->info( $ret );
            echo $ret;
            
            $Log->trace("END   setEmbossingAction");
        }

        /**
         * 打刻情報を保存
         * @return   なし
         */
        public function setEmbossing1Action()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEmbossingAction");
            $Log->info( "MSG_INFO_1803" );
            
            $companyID = "testuse";
            $key = "sapStation";
            $mmbossing = new Embossing( $companyID );
            
            $organID = 3;
            $embossingType = 5;
            $employeesNo = "0102";
            $dateTime = "2016-08-05 22:29:00";
            $isViolation = 0;
            
            $ret = $mmbossing->setEmbossing( $organID, $employeesNo, $key, $embossingType, $dateTime, $isViolation, 1 );
            
            $Log->info( $ret );
            echo $ret;
            
            $Log->trace("END   setEmbossingAction");
        }

        /**
         * 全組織一覧の取得
         * @return   なし
         */
        public function getAllOrganizationAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAllOrganizationAction");
            $Log->info( "MSG_INFO_1804" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $list = $mmbossing->getAllOrganizationList();

            foreach($list as $data )
            {
                echo $data['organization_id'] . '/' . $data['authentication_key'] . ',';
            }
            
            $Log->trace("END   getAllOrganizationAction");
        }

        /**
         * 全従業員情報一覧の取得
         * @return   なし
         */
        public function getAllUserAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAllUserAction");
            $Log->info( "MSG_INFO_1805" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $list = $mmbossing->getUserList(null);

            foreach($list as $data )
            {
                echo $data['organization_id'] . '/' . $data['employees_no'] . '/' . $data['employees_no'] . '：' .$data['user_name'] . '/' . $data['is_embossing'] . '/' . $data['biological_info']  . ',';
            }
            
            $Log->trace("END   getAllUserAction");
        }

        /**
         * シフト一覧の取得
         * @return   なし
         */
        public function getShiftAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAllUserAction");
            $Log->info( "MSG_INFO_1805" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $key = isset($_GET['key']) ? parent::escStr($_GET['key']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $list = $mmbossing->getShiftList($key);

            foreach($list as $data )
            {
                // 出勤ありか
                $isAttendance = 0;
                if( $data['attendance'] != $data['taikin'] )
                {
                    $isAttendance = 1;
                }
                echo $data['organization_id'] . '/' . $data['employees_no'] . '/' . $data['day'] . '/' . $isAttendance  . ',';
            }
            
            $Log->trace("END   getAllUserAction");
        }

        /**
         * 削除組織一覧の取得
         * @return   なし
         */
        public function getDelOrganizationAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getDelOrganizationAction");
            $Log->info( "MSG_INFO_1806" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $list = $mmbossing->getDelOrganizationList();

            foreach($list as $data )
            {
                echo $data['organization_id'] . '/' . $data['authentication_key'] . '/' . $data['abbreviated_name'] . ',';
            }
            
            $Log->trace("END   getDelOrganizationAction");
        }

        /**
         * 削除従業員情報一覧の取得
         * @return   なし
         */
        public function getDelUserAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getDelUserAction");
            $Log->info( "MSG_INFO_1807" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $list = $mmbossing->getDelUserList();

            foreach($list as $data )
            {
                echo $data['user_id'] . '/' . $data['employees_no'] . '/' . $data['employees_no'] . '：' .$data['user_name'] . '/' . $data['biological_info']  . ',';
            }
            
            $Log->trace("END   getDelUserAction");
        }

        /**
         * 組織名を取得
         * @return   なし
         */
        public function getOrganizationNameAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getDelUserAction");
            $Log->info( "MSG_INFO_1807" );
            
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            $key = isset($_GET['key']) ? parent::escStr($_GET['key']) : null;
            $mmbossing = new Embossing( $companyID );
            
            $organizationName = $mmbossing->getOrganizationName($key);

            echo $organizationName;
            
            $Log->trace("END   getDelUserAction");
        }

    }
?>
