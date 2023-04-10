<?php
    /**
     * @file    共通モデル(Model)
     * @author  millionet oota
     * @date    2017/01/26
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

    }

?>
