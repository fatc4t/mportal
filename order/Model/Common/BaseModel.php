<?php
    /**
     * @file    共通モデル(Model)
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    共通で使用するモデルの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseModel.php';
    require_once 'Model/Common/SecurityProcess.php';
    require_once 'Model/Common/SetPulldown.php';

    /**
     * 各モデルの基本クラス
     * @note    共通で使用するモデルの処理を定義
     */
    class BaseModel extends FwBaseModel
    {
        protected $securityProcess = null;       ///< セキュリティクラス
        public    $setPulldown = null;           ///< プルダウンクラス

        //CSVフォルダパス
        const CSVFLD = '/var/www/tmp/';

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseModelのコンストラクタ
            parent::__construct();
            $this->securityProcess       = new SecurityProcess();
            $this->setPulldown           = new SetPulldown();
            $this->securityProcess->getAccessViewOrder();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseModelのデストラクタ
            parent::__destruct();
        }

        /**
         * 使用中のマスタ情報を取得
         * @param   $idName（各機能のシーケンスID名）
         * @return  呼び出し先のシーケンスIDリスト
         */
        public function getInUseCheckList($idName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getInUseCheckList");

            $sql = 'SELECT ud.organization_id, ud.position_id'
                . ' , ud.employment_id, ud.section_id'
                . ' , ud.security_id FROM m_user_detail ud';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $inUseCheckArray = array();

            if( $result === false )
            {
                $Log->trace("END getInUseCheckList");
                return $inUseCheckArray;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($inUseCheckArray, $data[$idName]);
            }

            //配列で重複している物を削除する
            $unique = array_unique($inUseCheckArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $inUseCheckList = array_values($unique);

            $Log->trace("END getInUseCheckList");

            return $inUseCheckList;
        }

        /**
         * 従業員マスタで使用している手当マスタ情報を取得
         * @param   $allowanceIdName（各機能のシーケンスID名）
         * @return  呼び出し先のシーケンスIDリスト
         */
        public function getInUseAllowanceCheckList($allowanceIdName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getInUseAllowanceCheckList");

            $sql = 'SELECT ua.user_allowance_id, ua.allowance_id'
                . ' FROM m_user_allowance ua';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $inUseCheckArray = array();

            if( $result === false )
            {
                $Log->trace("END getInUseAllowanceCheckList");
                return $inUseCheckArray;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($inUseCheckArray, $data[$allowanceIdName]);
            }

            //配列で重複している物を削除する
            $unique = array_unique($inUseCheckArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $inUseCheckList = array_values($unique);

            $Log->trace("END getInUseAllowanceCheckList");

            return $inUseCheckList;
        }

        /**
         * 指定配列の重複削除
         * @param    $array           プルダウンリスト
         * @param    $column          配列のキーとする名称
         * @return   $uniqueArray     重複削除したリスト
         */
        protected function getUniqueArray($array, $column)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getUniqueArray");

            $uniqueArray = $this->setPulldown->getUniqueArray($array, $column);

            $Log->trace("END getUniqueArray");
            return $uniqueArray;
        }

        /**
         * 1テーブル更新
         * @param    $sql           実行するSQL文
         * @param    $parameters    実行するSQL文のパラメータ
         * @param    $tableName     INSERTしたテーブル名を指定する(その他は指定なし)
         * @return   実行結果
         */
        protected function executeOneTableSQL( $sql, $parameters, $tableName = "" )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            // 親クラスの呼び出し
            $ret = parent::executeOneTableSQL( $sql, $parameters, $tableName );

            // 更新結果がOKである
            if( "MSG_FW_OK" === $ret )
            {
                // 勤怠システム用のOKフラグに修正
                $ret = "MSG_BASE_0000";
            }

            $Log->trace("END executeOneTableSQL");
            return $ret;
        }

        /**
         * 従業員マスタ更新時空白にNULLセット
         * @param    $postArray
         * @param    $parameters
         * @param    $sqlUpdate
         * @return   SQLの実行結果
         */
        protected function setUserUpdateNullValue($postArray, &$parameters, &$sqlUpdate)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setUserUpdateNuLLValue");

            if(empty($postArray['birthday']))
            {
                $sqlUpdate .= ' , birthday = :birthday ';
                $parameters[':birthday'] = null;
            }
            if(empty($postArray['sex']))
            {
                $sqlUpdate .= ' , sex = :sex ';
                $parameters[':sex'] = null;
            }
            if(empty($postArray['leaving_date']))
            {
                $sqlUpdate .= ' , leaving_date = :leaving_date ';
                $parameters[':leaving_date'] = null;
            }
            if( $postArray['trial_period_type_id'] == 1 )
            {
                $sqlUpdate .= ' , trial_period_criteria_value = :trial_period_criteria_value ';
                $sqlUpdate .= ' , trial_period_write_down_criteria = :trial_period_write_down_criteria ';
                $sqlUpdate .= ' , trial_period_wages_value = :trial_period_wages_value ';
                $parameters[':trial_period_criteria_value'] = null;
                $parameters[':trial_period_write_down_criteria'] = null;
                $parameters[':trial_period_wages_value'] = null;
            }

            $Log->trace("END setUserUpdateNuLLValue");
        }

        /**
         * 従業員マスタ更新時空白にNULLセット
         * @param    $postArray
         * @param    $parameters
         * @param    $sqlUpdate
         * @return   SQLの実行結果
         */
        protected function setUserDetailUpdateNullValue($postArray, &$parameters, &$sqlUpdate)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setUserDetailUpdateNuLLValue");

            if(empty($postArray['section_id']))
            {
                $sqlUpdate .= ' , section_id = :section_id ';
                $parameters[':section_id'] = null;
            }
            if(empty($postArray['address']))
            {
                $sqlUpdate .= ' , address = :address ';
                $parameters[':address'] = null;
            }
            if(empty($postArray['tel']))
            {
                $sqlUpdate .= ' , tel = :tel ';
                $parameters[':tel'] = null;
            }
            if(empty($postArray['cellphone']))
            {
                $sqlUpdate .= ' , cellphone = :cellphone ';
                $parameters[':cellphone'] = null;
            }
            if(empty($postArray['mail_address']))
            {
                $sqlUpdate .= ' , mail_address = :mail_address ';
                $parameters[':mail_address'] = null;
            }
            if(empty($postArray['hourly_wage']))
            {
                $sqlUpdate .= ' , hourly_wage = :hourly_wage ';
                $parameters[':hourly_wage'] = 0;
            }
            if(empty($postArray['base_salary']))
            {
                $sqlUpdate .= ' , base_salary = :base_salary ';
                $parameters[':base_salary'] = 0;
            }
            if(empty($postArray['comment']))
            {
                $sqlUpdate .= ' , comment = :comment ';
                $parameters[':comment'] = null;
            }

            $Log->trace("END setUserDetailUpdateNuLLValue");
        }

        /**
         * 表示順のSQL文
         * @param    $sortNo          ソートNo
         * @return   セキュリティマスタ一覧取得SQL文（ORDER BY）
         */
        protected function creatSort( $sortNo, $sortSqlList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = 'ORDER BY ms.disp_order';

            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");

            return $sql;
        }
        
        public function getSizeList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSizeList");

            $sql = 'SELECT custsize
                        ,prodsize
                        ,shopsize
                        ,shop_areasize
                        ,custtypesize
                        ,areasize
                        ,staffsize
                        ,bikousize
                        ,prodtype1size
                        ,prodtype2size
                        ,prodtype3size
                        ,prodtype4size
                        ,pkubunsize
                        ,makersize
                        ,suppsize
                        ,sectsize
                        ,sale_plansize
                        ,pratesize
                        ,mixmatchsize
                        ,bundlesize
                        ,jicfs_classsize
                        ,priv_classsize
                        ,creditsize
                        ,commentsize
                        ,alcoholsize
                        ,gift_certisize
                        ,relationsize
                        ,receiptsize
                        ,tmzonesize
                        ,clientelesize
                        ,couponsize
                    from mst0010 limit 1';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $sizelist = array();
            
            if( $result === false )
            {
                $Log->trace("END getSizeList");
                return $sizelist;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $sizelist = $data;
            }

            $Log->trace("END getSizeList");

            return $sizelist;
        }      

    }

?>
