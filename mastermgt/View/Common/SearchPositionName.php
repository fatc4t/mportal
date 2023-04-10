<?php
    /**
     * @file      役職検索用ドロップメニュー(共通)
     * @author    USE S.kasai
     * @date      2016/06/13
     * @version   1.00
     * @note      役職検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchPositionName-ajax">
                                        <select name="positionName" id="positionName" style="width: 100px">
                                            <?php foreach($positionNameList as $position) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($position['position_name'] == $searchArray['positionName']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($position['position_name']); ?>" <?php hsc($selected); ?>><?php hsc($position['position_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchPositionName-ajax -->
