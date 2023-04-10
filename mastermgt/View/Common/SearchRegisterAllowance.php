<?php
    /**
     * @file      従業員登録画面手当額プルダウンメニュー(共通)
     * @author    USE M.Higashihara
     * @date      2016/06/17
     * @version   1.00
     * @note      従業員登録画面手当額プルダウンメニュー(共通)
     */
?>
<style type="text/css">
input[type=text]
{
       color: initial!important;
       font-size: initial!important;
       text-shadow: initial!important;
       border: initial!important;
       border:solid 1px #AAAAAA!important;
       border-radius:0!important;
}
</style>
                                <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                <!-- ajax差し替えエリア -->
                                <div id="jquery-replace-SearchRegisterAllowance-ajax">
                                    <table width="100%" id="allAddTable">
                                        <tbody id="allAddTbody">
                                            <tr>
                                                <th width="120px">手当</th>
                                                <td width="200px">
                                                    <select id="allowance" name="allowance" class="allowance" onChange="changeAllowance(this)" style="width: 130px">
                                                        <?php foreach($allowanceList as $allowance) { ?>
                                                            <option value="<?php hsc($allowance['allowance_id']); ?>"><?php hsc($allowance['allowance_name']); ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <th width="120px">手当額 <font id="allowanceAmountFont" size="3" style="display : none" color="#ff0000">*</font></th>
                                                <td width="160px"><input type="text" pattern="[1-9][0-9]*" title="<?php hsc($Log->getMsgLog('MSG_BASE_0307')); ?>" id="allowanceAmount" name="allowanceAmount" class="allowanceAmount" size="10" maxlength="8" value="" class="En" disabled>  円</td>
                                                <td width="100px" style="border-style:none;">
                                                    <input type="button" id="allSelectAdd" name="allSelectAdd" value="追加" class="add">
                                                    <input type="button" style="display : none" id="allSelectDel" name="allSelectDel" value="削除" class="delete">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <script>
                                        $('#allAddTbody > tr').eq(0).clone(true).insertAfter($('#allAddTbody > tr')).eq(0).find("#allowance").attr('id', "allowance0");
                                        $("#allowance0").parents('td').next().next().children('input').attr('id', "allowanceAmount0");
                                        $("#allowance0").parents('td').next().children('font').attr('id', "allowanceAmountFont0");
                                        
                                        <?php if( $isChange === 'true' ) { ?>
                                            if(jsonAllowance != 'null' && jsonAllowance != '')
                                            {
                                                var allowanceSelected = JSON.parse(jsonAllowance);
                                                $('#allowance0').val(allowanceSelected[0]);
                                                var amountSelected = JSON.parse(jsonAmount);
                                                $('#allowanceAmount0').val(amountSelected[0]);
                                                $("#allowanceAmount0").prop("disabled", false);
                                                $("#allowanceAmount0").prop("required", true);
                                                $("#allowanceAmountFont0").css('display' , '');
                                                if(allCnt > 0)
                                                {
                                                    for(var i = 1; i <= allCnt; i++)
                                                    {
                                                        $('#allSelectDel').css('display' , '');
                                                        $('#allAddTbody > tr').eq(0).clone(true).insertAfter(
                                                            $("#allowance0").parent().parent()
                                                        ).find("#allowance").attr('id', "allowance" + i);
                                                        $("#allowance" + i).parents('td').next().next().children('input').attr('id', "allowanceAmount" + i);
                                                        $("#allowance" + i).parents('td').next().children('font').attr('id', "allowanceAmountFont" + i);
                                                        $('#allowance' + i).val(allowanceSelected[i]);
                                                        $('#allowanceAmount' + i).val(amountSelected[i]);
                                                        $("#allowanceAmount" + i).prop("disabled", false);
                                                        $("#allowanceAmount" + i).prop("required", true);
                                                        $("#allowanceAmountFont" + i).css('display' , '');
                                                    }
                                                }
                                            }
                                        <?php } ?>
                                    </script>
                                    
                                </div><!-- /.jquery-replace-SearchRegisterAllowance-ajax -->