<?php
    /**
     * @file      休日名称名検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/06/13
     * @version   1.00
     * @note      休日名称名検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchHolidayNameName-ajax">
                                            <select name="holidayNameName" id="holidayNameName"  style="width: 100px">
                                                <?php foreach($holidayNameNameList as $holidayNameName) { ?>
                                                    <?php $selected = ""; ?>
                                                    <?php if($holidayNameName['holiday_name_id'] == $searchArray['holidayNameID']) { ?>
                                                        <?php $selected = "selected"; ?>
                                                    <?php } ?>
                                                    <option value="<?php hsc($holidayNameName['holiday_name_id']); ?>" <?php hsc($selected); ?>><?php hsc($holidayNameName['holiday_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                    </div><!-- /.jquery-replace-SearchHolidayNameName-ajax -->
