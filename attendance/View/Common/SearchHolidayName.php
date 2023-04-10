<?php
    /**
     * @file      休日名検索用ドロップメニュー(共通)
     * @author    USE S.kasai
     * @date      2016/06/14
     * @version   1.00
     * @note      休日名検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchHolidayName-ajax">
                                        <select name="holidayName" id="holidayName" style="width: 100px">
                                            <?php foreach($holidayNameList as $holiday) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($holiday['holiday'] == $searchArray['holidayName']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($holiday['holiday']); ?>" <?php hsc($selected); ?>><?php hsc($holiday['holiday']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchHolidayName-ajax -->
