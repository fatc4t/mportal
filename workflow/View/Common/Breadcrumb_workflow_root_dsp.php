<?php
    /**
     * @file      パンくずリスト(View)
     * @author    millionet oota
     * @date      2017/01/26
     * @version   1.00
     * @note      パンくずリスト
     */
?>

            <!-- menuNameArea -->
            <div class="menuNameArea">
                <h2>
                    <a href="#"><span class="menu"></span></a>
                    <?php 
                        // 現在のパスを設定
                        $path = $_SERVER['REQUEST_URI'];
                        
                        // 機能基準パスを設定
                        // パラメータ取得
                        $param = isset( $_GET['param'] ) ? parent::escStr( $_GET['param'] ) : null;

                        // パラメータ分割
                        $params = array();
                        if ('' != $param)
                        {
                            // パラメーターを / で分割
                            $params = explode('/', $param);
                        }

                        // 一覧のパスを取得
                        $pathShow = SystemParameters::$V_TM_TOP . $params[0] . '/show';
                        
                        // トップメッセージのHOMEパスを設定
                        $breadcrumns["#"] = "ワークフロー > ルートマスタ";
                        $breadcrumns[SystemParameters::$V_TM_HOME] = $_SESSION["TM_HOME_MENU"][SystemParameters::$V_TM_HOME];
                        
                        $screenName = '';
                        $first = true;

                        foreach($breadcrumns as $key => $val)
                        {
                            if( $first )
                            {
                                // 最初は、「>」を表示しない
                                $first = false;
                            }
                            else
                            {
                                echo('<span style="color:#ffffff;"> > </span>');
                            }
                            
                            if( $key != '#' )
                            {
                                echo("<a href=\"{$key}&home=1\">{$val}</a>");
                            }
                            else
                            {
                                echo('<span style="color:#ffffff;">' . $val . '</span>');
                            }

                            // 画面の最後のみ保存する（ダイアログ表示用）
                            $screenName = $val;
                        }

                    ?>
                </h2>
            </div><!-- /.menuNameArea -->

