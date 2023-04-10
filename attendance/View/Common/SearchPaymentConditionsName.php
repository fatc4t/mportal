<?php
    /**
     * @file      支給条件検索用ドロップメニュー(共通)
     * @author    USE S.Kasai
     * @date      2016/06/16
     * @version   1.00
     * @note      支給条件検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchPaymentConditionsName-ajax">
                                            <select name="paymentConditionsName" id="paymentConditionsName" style="width: 100px">
                                                <?php foreach($payCndNameList as $paymentConditions) { ?>
                                                    <?php $selected = ""; ?>
                                                    <?php if($paymentConditions['payment_conditions_id'] == $searchArray['paymentConditionsID']) { ?>
                                                        <?php $selected = "selected"; ?>
                                                    <?php } ?>
                                                    <option value="<?php hsc($paymentConditions['payment_conditions_id']); ?>" <?php hsc($selected); ?>><?php hsc($paymentConditions['payment_conditions_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                    </div><!-- /.jquery-replace-SearchPaymentConditionsName-ajax -->
