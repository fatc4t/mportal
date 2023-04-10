<?php
    /**
     * @file      給与フォーマット検索用ドロップメニュー(共通)
     * @author    USE R.dendo
     * @date      2016/06/28
     * @version   1.00
     * @note      給与フォーマット検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchPayrollFormat-ajax">
                                        <select id="payrollFormat" name="payrollFormat" style="width: 100px">
                                        <?php foreach($payrollList as $payroll) { ?>
                                         <?php $selected = ""; ?>
                                         <?php if($payroll['payroll_system_id'] == $searchArray['payrollFormat']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                            <?php $selected = ""; ?>
                                            <option value="<?php hsc($payroll['payroll_system_id']);?>" <?php hsc($selected); ?>><?php hsc($payroll['name']);?></option>
                                        <?php } ?>
                                    </select>
                                    </div><!-- /.jquery-replace-SearchPayrollFormat-ajax -->
