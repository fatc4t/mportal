<?php
    /**
     * @file      パンくずリスト(View)
     * @author    USE Y.Sakata
     * @date      2016/06/03
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
                        $pathShow = SystemParameters::$V_M_BASE_PASH . $params[0] . '/show';
                        
                        // M-PORTAL機能管理のHOMEパスを設定
                        $breadcrumns[SystemParameters::$V_M_HOME] = $_SESSION["M_HOME_MENU"][SystemParameters::$V_M_HOME];
                        
                        if($path !== $_SESSION["M_HOME_MENU"][SystemParameters::$V_M_HOME])
                        {
                            $breadcrumns["#"] = "薬剤発注管理";
                            
                            // 入力画面用のパスを作成
                            $inputPath = explode('&', $path);

                            if( array_key_exists( $inputPath[0], $_SESSION["M_ORDER_MENU"] ) )
                            {
                                $breadcrumns[$path] = $_SESSION["M_ORDER_MENU"][$inputPath[0]];
                            }
                        }
                        
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
                                //echo("<a href=\"{$key}&home=1\">{$val}</a>");
								echo("<a href=\"{$key}\">{$val}</a>");
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

