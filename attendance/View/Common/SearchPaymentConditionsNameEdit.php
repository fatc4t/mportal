<?php
    /**
     * @file      新規登録時支給条件検索用ドロップメニュー(共通)
     * @author    USE S.Kasai
     * @date      2016/06/16
     * @version   1.00
     * @note      新規登録時支給条件検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchPaymentConditionsNameEdit-ajax">
                                            <select title="<?php hsc($Log->getMsgLog('MSG_BASE_0303')); ?>" name="paymentConditionsNewName" id="paymentConditionsNewName" onChange="setPaymentConditionsDetail()" required>
                                                <?php foreach($payCndList as $payConditions) { ?>
                                                    <option value="<?php hsc($payConditions['payment_conditions_id']); ?>"><?php hsc($payConditions['payment_conditions_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                    </div><!-- /.jquery-replace-SearchPaymentConditionsNameEdit-ajax -->
