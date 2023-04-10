<?php
    /**
     * @file      時間データなし検索用ドロップメニュー(共通)
     * @author    USE K.Narita
     * @date      2016/06/21
     * @version   1.00
     * @note      時間データなし検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchNoDataFormat-ajax">
                                            <select name="noDataFormat" id="noDataFormat" style="width: 100px">
                                                <?php 
                                                    foreach($noDataFormatList as $noDataFormat)
                                                    {
                                                        $selected = "";
                                                        if($noDataFormat['no_data_format'] == $searchArray['noDataFormat'])
                                                        {
                                                            $selected = "selected";
                                                        }
                                                        $noDataFormatName = "";
                                                        if($noDataFormat['no_data_format'] == 1 )
                                                        {
                                                            $noDataFormatName = "数値出力[0.00/0:00]";
                                                        } 
                                                        else if($noDataFormat['no_data_format'] == 2 )
                                                        {
                                                            $noDataFormatName = "空白出力";
                                                        } 
                                                ?>
                                                    <option value="<?php hsc($noDataFormat['no_data_format']); ?>" <?php hsc($selected); ?>><?php hsc($noDataFormatName); ?></option>
                                                <?php } ?>
                                            </select>
                                    </div><!-- /.jquery-replace-SearchNoDataFormat-ajax -->
