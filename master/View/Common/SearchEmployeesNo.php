<?php
    /**
     * @file      従業員No検索用ドロップメニュー(共通)
     * @author    USE M.Higashihara
     * @date      2016/06/23
     * @version   1.00
     * @note      従業員No検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchEmployeesNo-ajax">
                                        <select name="userNo" id="userNo" style="width: 100px">
                                            <?php foreach($userNoList as $user) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($user['employees_no'] == $searchArray['userNo']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                <option value="<?php hsc($user['employees_no']); ?>" <?php hsc($selected); ?>><?php hsc($user['employees_no']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchEmployeesNo-ajax -->
