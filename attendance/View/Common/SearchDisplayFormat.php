<?php
    /**
     * @file      時間の表示形式検索用ドロップメニュー(共通)
     * @author    USE K.Narita
     * @date      2016/06/21
     * @version   1.00
     * @note      時間の表示形式検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchDisplayFormat-ajax">
                                        <select name="displayFormat" id="displayFormat" style="width: 100px">
                                            <?php 
                                                foreach($displayFormatList as $displayFormat) 
                                                {
                                                    $selected = "";
                                                    if($displayFormat['display_format'] == $searchArray['displayFormat']) 
                                                    {
                                                        $selected = "selected";
                                                    }
                                                    $displayFormatName = "";
                                                    if($displayFormat['display_format'] == 1 ) 
                                                    {
                                                        $displayFormatName = "10進数[1.5]";
                                                    } 
                                                    else if($displayFormat['display_format'] == 2 )
                                                    {
                                                        $displayFormatName = "時刻[1:30]";
                                                    } 
                                            ?>
                                                    <option value="<?php hsc($displayFormat['display_format']); ?>" <?php hsc($selected); ?>><?php hsc($displayFormatName); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchDisplayFormat-ajax -->
