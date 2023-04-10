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
