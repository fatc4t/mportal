<?php
    /**
     * @file      手当名検索用ドロップメニュー(共通)
     * @author    USE S.Kasai
     * @date      2016/06/16
     * @version   1.00
     * @note      手当名検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchAllowanceName-ajax">
                                        <select name="allowanceName" id="allowanceName" style="width: 100px">
                                            <?php foreach($allowanceNameList as $allowance) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($allowance['allowance_name'] == $searchArray['allowanceName']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($allowance['allowance_name']); ?>" <?php hsc($selected); ?>><?php hsc($allowance['allowance_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchAllowanceName-ajax -->
