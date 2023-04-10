<?php
    /**
     * @file      M-PORTALアクセス権限マスタ
     * @author    USE Y.Sakata
     * @date      2016/07/01
     * @version   1.00
     * @note      アクセス権限マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * アクセス権限マスタクラス
     * @note   アクセス権限マスタテーブルの管理を行う
     */
    class AccessAuthority extends BaseModel
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
         * アクセス権限マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ
         * @return   成功時：$sectionList  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $accessAuthorityList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $accessAuthorityList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $accessAuthorityList, $data);
            }

            $Log->trace("END getListData");

            return $accessAuthorityList;
        }

        /**
         * アクセス権限マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/セクション名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO public.m_access_authority(  '
                 . '                   access_authority_id '
                 . '                 , function_id '
                 . '                 , screen_name '
                 . '                 , url '
                 . '                 , comment '
                 . '                 , reference '
                 . '                 , registration '
                 . '                 , delete '
                 . '                 , approval '
                 . '                 , printing '
                 . '                 , output '
                 . '                 , disp_order '
                 . '                 , is_del '
                 . '                 , registration_time '
                 . '                 , registration_user_id '
                 . '                 , update_time '
                 . '                 , update_user_id '
                 . '               ) VALUES (  '
                 . '                   :access_authority_id '
                 . '                 , :function_id '
                 . '                 , :screen_name '
                 . '                 , :url '
                 . '                 , :comment '
                 . '                 , :reference '
                 . '                 , :registration '
                 . '                 , :delete '
                 . '                 , :approval '
                 . '                 , :printing '
                 . '                 , :output '
                 . '                 , :disp_order '
                 . '                 , 0 '
                 . '                 , current_timestamp '
                 . '                 , :user_id '
                 . '                 , current_timestamp '
                 . '                 , :user_id ) '; 


            $parameters = array(
                ':access_authority_id'  => $postArray['accessAuthorityID'],
                ':function_id'          => $postArray['functionID'],
                ':screen_name'          => $postArray['screenName'],
                ':url'                  => $postArray['url'],
                ':comment'              => $postArray['comment'],
                ':reference'            => $postArray['reference'],
                ':registration'         => $postArray['registration'],
                ':delete'               => $postArray['delete'],
                ':approval'             => $postArray['approval'],
                ':printing'             => $postArray['printing'],
                ':output'               => $postArray['output'],
                ':disp_order'           => $postArray['dispOrder'],
                ':user_id'              => $postArray['user_id'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * アクセス権限マスタ登録データ修正
         * @param    $postArray   入力パラメータ(セクションID/コード/組織ID/セクション名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE public.m_access_authority SET '
                . '   function_id   = :function_id'
                . ' , screen_name   = :screen_name'
                . ' , url           = :url'
                . ' , comment       = :comment'
                . ' , reference     = :reference'
                . ' , registration  = :registration'
                . ' , delete        = :delete'
                . ' , approval      = :approval'
                . ' , printing      = :printing'
                . ' , output        = :output'
                . ' , disp_order    = :disp_order'
                . ' , update_time   = current_timestamp'
                . ' , update_user_id = :user_id'
                . ' WHERE access_authority_id = :access_authority_id AND update_time = :update_time ';

            $parameters = array(
                ':access_authority_id'  => $postArray['accessAuthorityID'],
                ':function_id'          => $postArray['functionID'],
                ':screen_name'          => $postArray['screenName'],
                ':url'                  => $postArray['url'],
                ':comment'              => $postArray['comment'],
                ':reference'            => $postArray['reference'],
                ':registration'         => $postArray['registration'],
                ':delete'               => $postArray['delete'],
                ':approval'             => $postArray['approval'],
                ':printing'             => $postArray['printing'],
                ':output'               => $postArray['output'],
                ':disp_order'           => $postArray['dispOrder'],
                ':user_id'              => $postArray['user_id'],
                ':update_time'          => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * アクセス権限マスタ登録データ削除
         * @param    $postArray   入力パラメータ(セクションID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $sql = 'UPDATE public.m_access_authority SET '
                 . ' is_del             = :is_del'
                . ' , update_time       = current_timestamp'
                . ' , update_user_id    = :user_id'
                . ' WHERE access_authority_id = :access_authority_id AND update_time = :update_time ';

            $parameters = array(
                ':access_authority_id'  => $postArray['accessAuthorityID'],
                ':is_del'               => $postArray['isDel'],
                ':user_id'              => $postArray['user_id'],
                ':update_time'          => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * アクセス権限IDプルダウン
         * @return   アクセス権限IDリスト
         */
        public function getAccessAuthorityList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchAccessAuthorityList");

            $sql = 'SELECT access_authority_id FROM public.m_access_authority '
                . ' WHERE is_del = :is_del ORDER BY disp_order';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $accessIdList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchAccessAuthorityList");
                return $accessIdList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($accessIdList, $data);
            }

            $initial = array('access_authority_id' => '',);
            array_unshift($accessIdList, $initial );

            $Log->trace("END getSearchAccessAuthorityList");

            return $accessIdList;
        }

        /**
         * 機能IDプルダウン
         * @return   機能IDリスト
         */
        public function getFunctionList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchFunctionList");

            $sql = 'SELECT function_id, function_name FROM public.m_function '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $functionIdList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchFunctionList");
                return $functionIdList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($functionIdList, $data);
            }

            $initial = array(
                                'function_id'         => '',
                                'function_name'       => '', 
                            );
            array_unshift($functionIdList, $initial );

            $Log->trace("END getSearchFunctionList");

            return $functionIdList;
        }

        /**
         * アクセス権限マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    &$searchArray              検索条件用パラメータ
         * @return   アクセス権限マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql   = ' SELECT maa.access_authority_id, maa.function_id, mf.function_name , maa.screen_name, maa.url, maa.comment, '
                  .  '        maa.reference, maa.registration, maa.delete, maa.approval, maa.printing, maa.output, '
                  .  '        maa.disp_order, maa.is_del, maa.update_time '
                  .  ' FROM public.m_access_authority maa INNER JOIN public.m_function mf ON maa.function_id = mf.function_id ';
            $sql  .= ' WHERE 1=1 ';
            
            if( !empty( $postArray['accessAuthorityId'] ) )
            {
                $sql .= ' AND maa.access_authority_id = :access_authority_id ';
                $searchArray = array_merge($searchArray, array(':access_authority_id' => $postArray['accessAuthorityId'],) );
            }
            if( !empty( $postArray['functionId'] ) )
            {
                $sql .= ' AND maa.function_id = :function_id ';
                $searchArray = array_merge($searchArray, array(':function_id' => $postArray['functionId'],) );
            }
            if( !empty( $postArray['screenName'] ) )
            {
                $sql .= ' AND maa.screen_name LIKE :screen_name ';
                $searchArray = array_merge($searchArray, array(':screen_name' => "%" . $postArray['screenName'] . "%",) );
            }
            if( !empty( $postArray['url'] ) )
            {
                $sql .= ' AND maa.url LIKE :url ';
                $searchArray = array_merge($searchArray, array(':url' => "%" . $postArray['url'] . "%",) );
            }
            if( !empty( $postArray['comment'] ) )
            {
                $sql .= ' AND maa.comment LIKE :comment ';
                $searchArray = array_merge($searchArray, array(':comment' => "%" . $postArray['comment'] . "%" ,) );
            }
            
            $sql .= $this->creatCheckBoxSQL( $postArray );

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * アクセス権限マスタチェックボックス設定のSQL文作成
         * @param    $postArray                 入力パラメータ
         * @return   チェックボックス設定分のWHERE句
         */
        private function creatCheckBoxSQL( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatCheckBoxSQL");

            $sql = '';

            if( $postArray['reference'] == 1 )
            {
                $sql .= ' AND maa.reference = 1 ';
            }
            if( $postArray['registration'] == 1 )
            {
                $sql .= ' AND maa.registration = 1 ';
            }
            if( $postArray['delete'] == 1 )
            {
                $sql .= ' AND maa.delete = 1 ';
            }
            if( $postArray['approval'] == 1 )
            {
                $sql .= ' AND maa.approval = 1 ';
            }
            if( $postArray['printing'] == 1 )
            {
                $sql .= ' AND maa.printing = 1 ';
            }
            if( $postArray['output'] == 1 )
            {
                $sql .= ' AND maa.output = 1 ';
            }
            if( $postArray['isDel'] == 0 )
            {
                $sql .= ' AND maa.is_del = 0 ';
            }

            $Log->trace("END creatCheckBoxSQL");
            
            return $sql;
        }

        /**
         * アクセス権限マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   アクセス権限マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");
            
            $sql = ' ORDER BY maa.disp_order, maa.function_id, maa.access_authority_id';

            // ソート条件作成
            $sortSqlList = array(
                                1       =>  ' ORDER BY maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',                             // Noの降順
                                2       =>  ' ORDER BY maa.disp_order, maa.function_id, maa.access_authority_id ',                                           // Noの昇順
                                3       =>  ' ORDER BY maa.is_del DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',            // 状態の降順
                                4       =>  ' ORDER BY maa.is_del, maa.disp_order, maa.function_id, maa.access_authority_id ',                               // 状態の昇順
                                5       =>  ' ORDER BY maa.access_authority_id DESC, maa.disp_order DESC, maa.function_id DESC ',                            // アクセス権限IDの降順
                                6       =>  ' ORDER BY maa.access_authority_id, maa.disp_order, maa.function_id ',                                           // アクセス権限IDの昇順
                                7       =>  ' ORDER BY maa.function_id DESC, maa.disp_order DESC, maa.access_authority_id DESC',                             // 機能の降順
                                8       =>  ' ORDER BY maa.function_id, maa.disp_order, maa.access_authority_id ',                                           // 機能の昇順
                                9       =>  ' ORDER BY maa.screen_name DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',       // 画面名の降順
                               10       =>  ' ORDER BY maa.screen_name, maa.disp_order, maa.function_id, maa.access_authority_id ',                          // 画面名の昇順
                               11       =>  ' ORDER BY maa.url DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',               // URLの降順
                               12       =>  ' ORDER BY maa.url, maa.disp_order, maa.function_id, maa.access_authority_id ',                                  // URLの昇順
                               13       =>  ' ORDER BY maa.comment DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',           // コメントの降順
                               14       =>  ' ORDER BY maa.comment, maa.disp_order, maa.function_id, maa.access_authority_id ',                              // コメントの昇順
                               15       =>  ' ORDER BY maa.reference DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',         // 参照の降順
                               16       =>  ' ORDER BY maa.reference, maa.disp_order, maa.function_id, maa.access_authority_id ',                            // 参照の昇順
                               17       =>  ' ORDER BY maa.registration DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',      // 登録の降順
                               18       =>  ' ORDER BY maa.registration, maa.disp_order, maa.function_id, maa.access_authority_id ',                         // 登録の昇順
                               19       =>  ' ORDER BY maa.delete DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',            // 削除の降順
                               20       =>  ' ORDER BY maa.delete, maa.disp_order, maa.function_id, maa.access_authority_id ',                               // 削除の昇順
                               21       =>  ' ORDER BY maa.approval DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',          // 承認の降順
                               22       =>  ' ORDER BY maa.approval, maa.disp_order, maa.function_id, maa.access_authority_id ',                             // 承認の昇順
                               23       =>  ' ORDER BY maa.printing DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',          // 印刷の降順
                               24       =>  ' ORDER BY maa.printing, maa.disp_order, maa.function_id, maa.access_authority_id ',                             // 印刷の昇順
                               25       =>  ' ORDER BY maa.output DESC, maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',            // 出力の降順
                               26       =>  ' ORDER BY maa.output, maa.disp_order, maa.function_id, maa.access_authority_id ',                               // 出力の昇順
                               27       =>  ' ORDER BY maa.disp_order DESC, maa.function_id DESC, maa.access_authority_id DESC',                             // 表示順の降順
                               28       =>  ' ORDER BY maa.disp_order, maa.function_id, maa.access_authority_id ',                                           // 表示順の昇順
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
