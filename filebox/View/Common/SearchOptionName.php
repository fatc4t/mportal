<?php
    /**
     * @file      設定名検索用ドロップメニュー(共通)
     * @author    USE K.Narita
     * @date      2016/06/21
     * @version   1.00
     * @note      設定名検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchOptionName-ajax">
                                        <select name="optionName" id="optionName" style="width: 150px">
                                            <?php foreach($optionNameList as $option) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($option['name'] == $searchArray['optionName']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($option['display_item_id']); ?>" <?php hsc($selected); ?>><?php hsc($option['name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchOptionName-ajax -->
