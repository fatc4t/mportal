<?php
    /**
     * @file      従業員マスタ　所属情報エリア
     * @author    USE Y.Sakata
     * @date      2016/11/07
     * @version   1.00
     * @note      従業員マスタ　所属情報エリア
     */
?>
                            <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                            <!-- ajax差し替えエリア -->
                            <div id="jquery-replace-SearchAffiliationInformation-ajax">
                                <script type="text/javascript">
                                    /**
                                     * 入社日/退社日のデイトピッカーを設定
                                     */
                                    $(function()
                                    {
                                        $( "#hire_date, #leaving_date" ).datepicker({
                                            numberOfMonths: 2,
                                            showButtonPanel: true,
                                            dateFormat: 'yy/mm/dd'
                                        });
                                    });
                                </script>
                                <table width="100%">
                                    <tbody>
                                        <tr>
                                            <td width="120px" style="border-style:none;">【所属情報欄】</td>
                                            <td width="200px" style="border-style:none;"></td>
                                            <td width="120px" style="border-style:none;"></td>
                                            <td width="200px" style="border-style:none;"></td>
                                            <td width="50px" style="border-style:none;"></td>
                                        </tr>
                                        <tr>
                                            <th><?php hsc($Log->getMsgLog('MSG_BASE_0878')); ?> <font size="3" color="#ff0000">*</font></th>
                                            <td><input type="text" pattern="\d{4}/\d{2}/\d{2}" title="<?php hsc($Log->getMsgLog('MSG_BASE_0305')); ?>" id="hire_date" name="hire_date" style="width: 130px" value="<?php hsc($userDataList['hire_date']); ?>" required></td>
                                            <th><?php hsc($Log->getMsgLog('MSG_BASE_0879')); ?></th>
                                            <td><input type="text" pattern="\d{4}/\d{2}/\d{2}" title="<?php hsc($Log->getMsgLog('MSG_BASE_0305')); ?>" id="leaving_date" name="leaving_date" style="width: 130px" value="<?php hsc($userDataList['leaving_date']); ?>"></td>
                                            <td style="border-style:none;"></td>
                                        </tr>
                                        <tr>
                                            <th>所属組織 <font size="3" color="#ff0000">*</font></th>
                                            <td>
                                                <select title="<?php hsc($Log->getMsgLog('MSG_BASE_0303')); ?>" id="organization" name="organization" style="width: 130px" onchange="changeOrganization()"  required>
                                                    <?php foreach($abbreviatedList as $abbreviated) { ?>
                                                        <?php $selected =""; ?>
                                                        <?php if($abbreviated['organization_id'] == $userDataList['organization_id']) { ?>
                                                            <?php $selected ="selected"; ?>
                                                        <?php } ?>
                                                        <option value="<?php hsc($abbreviated['organization_id']); ?>" <?php hsc($selected); ?>><?php hsc($abbreviated['abbreviated_name']); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <th>役職 <font size="3" color="#ff0000">*</font></th>
                                            <td>
                                                <?php include("SearchRegisterPosition.php"); ?>
                                            </td>
                                            <td style="border-style:none;"></td>
                                        </tr>
                                        <tr>
                                            <th>雇用形態 <font size="3" color="#ff0000">*</font></th>
                                            <td>
                                                <?php include("SearchRegisterEmployment.php"); ?>
                                            </td>
                                            <th>セクション</th>
                                            <td>
                                                <?php include("SearchRegisterSection.php"); ?>
                                            </td>
                                            <td style="border-style:none;"></td>
                                        </tr>
                                        <tr>
                                            <th>セキュリティ <font size="3" color="#ff0000">*</font></th>
                                            <td>
                                                <?php include("SearchRegisterSecurity.php"); ?>
                                            </td>
                                            <th>打刻制限</th>
                                            <td>
                                                <?php
                                                    $checked = "";
                                                    if( 1 == $userDataList['is_embossing'] )
                                                    {
                                                        $checked = "checked";
                                                    }
                                                ?>
                                                <input type="checkbox" id="is_embossing" name="is_embossing" <?php hsc($checked); ?> > ヘルプ (シフト有）のみ
                                            </td>
                                            <td style="border-style:none;"></td>
                                        </tr>
                                        <tr>
                                            <td style="border-style:none;"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div><!-- /.jquery-replace-SearchAffiliationInformation-ajax -->

