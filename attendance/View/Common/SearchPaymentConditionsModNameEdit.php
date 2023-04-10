<?php
    /**
     * @file      編集登録時支給条件検索用ドロップメニュー(共通)
     * @author    USE S.Kasai
     * @date      2016/06/16
     * @version   1.00
     * @note      編集登録時支給条件検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <?php $replaceIdName = "jquery-replace-SearchPaymentConditionsModNameEdit-ajax" . $allowance_no; ?>
                                    <div id="<?php hsc( $replaceIdName ); ?>">
                                            <select title="<?php hsc($Log->getMsgLog('MSG_BASE_0303')); ?>" id="allowancePaymentConditionsMod<?php hsc($allowance_no); ?>" name="allowancePaymentConditionsMod<?php hsc($allowance_no); ?>" style="width: 75px" onChange="setPaymentConditionsDetailMod(<?php hsc($allowance_no); ?> )"required>
                                                <?php foreach($payCndList as $payConditions) { ?>
                                                    <?php $selected = ""; ?>
                                                    <?php if($payConditions['payment_conditions_id'] == $allowance['payment_conditions_id']) { ?>
                                                        <?php $selected = "selected"; ?>
                                                    <?php } ?>
                                                    <option value="<?php hsc($payConditions['payment_conditions_id']); ?>" <?php hsc($selected); ?> ><?php hsc($payConditions['payment_conditions_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                    </div><!-- /.jquery-replace-SearchPaymentConditionsModNameEdit-ajax -->
