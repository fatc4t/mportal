<?php
    /**
     * @file      従業員登録画面試用期間デフォルト値設定
     * @author    USE M.Higashihara
     * @date      2016/07/05
     * @version   1.00
     * @note      従業員登録画面試用期間デフォルト値設定
     */
?>
                                <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                <!-- ajax差し替えエリア -->
                                <div id="jquery-replace-SearchRegisterTrial-ajax">
                                    <select id="trialPeriodType" name="trialPeriodType" onChange="changeTrial(this)" style="width: 130px">
                                        <?php foreach($trialPeriodList as $trial) { ?>
                                            <?php $selected =""; ?>
                                            <?php if($trial['trial_period_criteria_id'] == $trialArray['trial_period_type_id']) { ?>
                                                <?php $selected ="selected"; ?>
                                            <?php } ?>
                                            <option value="<?php hsc($trial['trial_period_criteria_id']); ?>" <?php hsc($selected); ?>><?php hsc($trial['trial_period_criteria_name']); ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="hidden" id="trialPeriodPostData" name="trialPeriodPostData" value="<?php hsc($trialArray['trial_period_criteria_value']); ?>">
                                    <script>
                                        var trialSelect = $("#trialPeriodType").val();
                                        var trialPeriodCriteria = $("#trialPeriodPostData").val();
                                        if(trialSelect > 1)
                                        {
                                            $("#trialPeriodWrite").prop("disabled", false);
                                            $("#trialPeriodWrite").prop("required", true);
                                            $('#trialPeriodWriteFont').css('display' , '');
                                            $("#trialPeriodWages").prop("disabled", false);
                                            $("#trialPeriodWages").prop("required", true);
                                            $('#trialPeriodWagesFont').css('display' , '');
                                            if(trialSelect == 2)
                                            {
                                                $('#trialPeriod').html('<input type="text" pattern="[1-9][0-9]*" title="<?php hsc($Log->getMsgLog('MSG_BASE_0300')); ?>" id="trialPeriodCriteria" name="trialPeriodCriteria" style="width: 130px" maxlength="5" value="" class="En" required>時間 <font size="3" color="#ff0000">*</font>');
                                                document.getElementById('trialPeriodCriteria').value = trialPeriodCriteria;
                                            }
                                            else if(trialSelect == 3)
                                            {
                                                $('#trialPeriod').html('<input type="text" pattern="[1-9][0-9]*" title="<?php hsc($Log->getMsgLog('MSG_BASE_0300')); ?>" id="trialPeriodCriteria" name="trialPeriodCriteria" style="width: 130px" maxlength="5" value="" class="En" required>日間 <font size="3" color="#ff0000">*</font>');
                                                document.getElementById('trialPeriodCriteria').value = trialPeriodCriteria;
                                            }
                                            else if(trialSelect == 4)
                                            {
                                                $('#trialPeriod').html('<input type="text" pattern="[1-9][0-9]*" title="<?php hsc($Log->getMsgLog('MSG_BASE_0300')); ?>" id="trialPeriodCriteria" name="trialPeriodCriteria" style="width: 130px" maxlength="5" value="" class="En" required>か月 <font size="3" color="#ff0000">*</font>');
                                                document.getElementById('trialPeriodCriteria').value = trialPeriodCriteria;
                                            }
                                            else if(trialSelect == 5)
                                            {
                                                $('#trialPeriod').html('<input type="text" pattern="[1-9][0-9]*" title="<?php hsc($Log->getMsgLog('MSG_BASE_0300')); ?>" id="trialPeriodCriteria" name="trialPeriodCriteria" style="width: 130px" maxlength="5" value="" class="En" required>年間 <font size="3" color="#ff0000">*</font>');
                                                document.getElementById('trialPeriodCriteria').value = trialPeriodCriteria;
                                            }
                                        }
                                        else
                                        {
                                            $('#trialPeriod').html('<input type="text" pattern="[1-9][0-9]*" title="<?php hsc($Log->getMsgLog('MSG_BASE_0300')); ?>" id="trialPeriodCriteria" name="trialPeriodCriteria" style="width: 130px" maxlength="5" value="" class="En" disabled>');
                                            $('#trialPeriodWrite').val('');
                                            $("#trialPeriodWrite").prop("disabled", true);
                                            $("#trialPeriodWrite").prop("required", false);
                                            $('#trialPeriodWriteFont').css('display' , 'none');
                                            $('#trialPeriodWages').val('');
                                            $("#trialPeriodWages").prop("disabled", true);
                                            $("#trialPeriodWages").prop("required", false);
                                            $('#trialPeriodWagesFont').css('display' , 'none');
                                        }
                                    </script>
                                </div><!-- /.jquery-replace-SearchRegisterTrial-ajax -->
