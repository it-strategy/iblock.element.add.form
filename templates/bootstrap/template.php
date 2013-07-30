<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
?><form name="iblock_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data" class="form-horizontal">
    <?=bitrix_sessid_post()?><?
    if (count($arResult['ERRORS'])) {?>
        <div class="alert alert-block alert-error">
            <strong><?=GetMessage('IBLOCK_FORM_SUBMIT_ERROR');?></strong><?
        foreach ($arResult['ERRORS'] as $propertyID => $arErrors) {
            if (in_array($propertyID, $arResult['PROPERTY_LIST']))
                continue;?>
            <p><?=implode('<br />', $arErrors);?></p><?
        }?>
        </div><?
    }?><?
    if (strlen($arResult['MESSAGE']) > 0) {?>
        <div class="alert alert-block alert-<?
            if ($arResult['SHOW_FORM']) {
                ?>info<?
            } else {
                ?>success<?
            }?>">
            <?=ShowNote($arResult['MESSAGE']);?><?
            if (!$arResult['SHOW_FORM']) {
                if (defined('YAMETRIKA') && strlen(YAMETRIKA) > 0) {?>
                <script type="text/javascript">
                    try {
                        window.yaCounter<?=YAMETRIKA;?>.reachGoal('form_submit', {
                            title: title
                        });
                    } catch(e) { }
                </script><?
                }?>
                <p><a href="<?=$APPLICATION->GetCurPage();?>"<?
                    if (isset($arParams['AJAX_MODE']) && $arParams['AJAX_MODE'] == 'Y') {
                        ?> onclick="return true;"<?
                    }?>><?=GetMessage('IBLOCK_FORM_SUBMIT_MORE');?></a></p><?
            }?>
        </div><?
    }?>
    <?if ($arResult['SHOW_FORM']) {
        if (is_array($arResult['PROPERTY_LIST']) && !empty($arResult['PROPERTY_LIST'])):
            foreach ($arResult['PROPERTY_LIST'] as $propertyID) {
                $isHidden = true;
                if (!is_array($arParams['PROPERTY_HIDDEN']) || !in_array($propertyID, $arParams['PROPERTY_HIDDEN'])) {
                    $isHidden = false;?>
                <div class="control-group<?
                    if (isset($arResult['ERRORS'][$propertyID])) {
                        ?> error<?
                    }?>">
                    <label class="control-label"><?if (intval($propertyID) > 0):?><?=$arResult['PROPERTY_LIST_FULL'][$propertyID]['NAME']?><?else:?><?=!empty($arParams['CUSTOM_TITLE_'.$propertyID]) ? $arParams['CUSTOM_TITLE_'.$propertyID] : GetMessage('IBLOCK_FIELD_'.$propertyID)?><?endif?><?if(in_array($propertyID, $arResult['PROPERTY_REQUIRED'])):?><span class="starrequired">*</span><?endif?></label>
                    <div class="controls"><?
                }
                        if (intval($propertyID) > 0) {
                            if (
                                $arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'] == 'T'
                                &&
                                $arResult['PROPERTY_LIST_FULL'][$propertyID]['ROW_COUNT'] == '1'
                            )
                                $arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'] = 'S';
                            elseif (
                                (
                                    $arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'] == 'S'
                                    ||
                                    $arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'] == 'N'
                                )
                                &&
                                $arResult['PROPERTY_LIST_FULL'][$propertyID]['ROW_COUNT'] > '1'
                            )
                                $arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'] = 'T';
                        }
                        elseif (($propertyID == 'TAGS') && CModule::IncludeModule('search'))
                            $arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'] = 'TAGS';

                        if ($arResult['PROPERTY_LIST_FULL'][$propertyID]['MULTIPLE'] == 'Y')
                        {
                            $inputNum = ($arParams['ID'] > 0 || count($arResult['ERRORS']) > 0) ? count($arResult['ELEMENT_PROPERTIES'][$propertyID]) : 0;
                            $inputNum += $arResult['PROPERTY_LIST_FULL'][$propertyID]['MULTIPLE_CNT'];
                        }
                        else
                        {
                            $inputNum = 1;
                        }

                        if($arResult['PROPERTY_LIST_FULL'][$propertyID]['GetPublicEditHTML'])
                            $INPUT_TYPE = 'USER_TYPE';
                        else
                            $INPUT_TYPE = $arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'];

                        switch ($INPUT_TYPE):
                            case 'USER_TYPE':
                                for ($i = 0; $i<$inputNum; $i++) {
                                    if ($arParams['ID'] > 0 || count($arResult['ERRORS']) > 0 || isset($arResult['ELEMENT_PROPERTIES'][$propertyID]) || isset($arResult['ELEMENT'][$propertyID]))
                                    {
                                        $value = intval($propertyID) > 0 ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['~VALUE'] : $arResult['ELEMENT'][$propertyID];
                                        $description = intval($propertyID) > 0 ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['DESCRIPTION'] : '';
                                    }
                                    elseif ($i == 0)
                                    {
                                        $value = intval($propertyID) <= 0 ? '' : $arResult['PROPERTY_LIST_FULL'][$propertyID]['DEFAULT_VALUE'];
                                        $description = '';
                                    }
                                    else
                                    {
                                        $value = '';
                                        $description = '';
                                    }
                                    if ($isHidden) {?>
                                        <input type="hidden" name="PROPERTY[<?=$propertyID?>][<?=$i?>][VALUE]" value="<?=$value?>" />
                                        <input type="hidden" name="PROPERTY[<?=$propertyID?>][<?=$i?>][DESCRIPTION]" value="<?=$description?>" /><?
                                    } else {
                                    echo call_user_func_array($arResult['PROPERTY_LIST_FULL'][$propertyID]['GetPublicEditHTML'],
                                        array(
                                            $arResult['PROPERTY_LIST_FULL'][$propertyID],
                                            array(
                                                'VALUE' => $value,
                                                'DESCRIPTION' => $description,
                                            ),
                                            array(
                                                'VALUE' => 'PROPERTY['.$propertyID.']['.$i.'][VALUE]',
                                                'DESCRIPTION' => 'PROPERTY['.$propertyID.']['.$i.'][DESCRIPTION]',
                                                'FORM_NAME'=>'iblock_add',
                                            ),
                                        ));
                                    }
                                }
                            break;
                            case 'TAGS':
                                $APPLICATION->IncludeComponent(
                                    'bitrix:search.tags.input',
                                    '',
                                    array(
                                        'VALUE' => $arResult['ELEMENT'][$propertyID],
                                        'NAME' => 'PROPERTY['.$propertyID.'][0]',
                                        'TEXT' => 'size="'.$arResult['PROPERTY_LIST_FULL'][$propertyID]['COL_COUNT'].'"',
                                    ), null, array('HIDE_ICONS'=>'Y')
                                );
                                break;
                            case 'HTML':
                                $LHE = new CLightHTMLEditor;
                                $LHE->Show(array(
                                    'id' => preg_replace("/[^a-z0-9]/i", '', 'PROPERTY['.$propertyID.'][0]'),
                                    'width' => '100%',
                                    'height' => '200px',
                                    'inputName' => 'PROPERTY['.$propertyID.'][0]',
                                    'content' => $arResult['ELEMENT'][$propertyID],
                                    'bUseFileDialogs' => false,
                                    'bFloatingToolbar' => false,
                                    'bArisingToolbar' => false,
                                    'toolbarConfig' => array(
                                        'Bold', 'Italic', 'Underline', 'RemoveFormat',
                                        'CreateLink', 'DeleteLink', 'Image', 'Video',
                                        'BackColor', 'ForeColor',
                                        'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
                                        'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
                                        'StyleList', 'HeaderList',
                                        'FontList', 'FontSizeList',
                                    ),
                                ));
                                break;
                            case 'T':
                                for ($i = 0; $i<$inputNum; $i++)
                                {

                                    if ($arParams['ID'] > 0 || count($arResult['ERRORS']) > 0 || isset($arResult['ELEMENT_PROPERTIES'][$propertyID]) || isset($arResult['ELEMENT'][$propertyID]))
                                    {
                                        $value = intval($propertyID) > 0 ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE'] : $arResult['ELEMENT'][$propertyID];
                                    }
                                    elseif ($i == 0)
                                    {
                                        $value = intval($propertyID) > 0 ? '' : $arResult['PROPERTY_LIST_FULL'][$propertyID]['DEFAULT_VALUE'];
                                    }
                                    else
                                    {
                                        $value = '';
                                    }
                                    if ($isHidden) {?>
                                        <input type="hidden" name="PROPERTY[<?=$propertyID?>][<?=$i?>]" value="<?=$value?>" /><?
                                    } else {
                                ?>
                        <input type="text" name="PROPERTY[<?=$propertyID?>][<?=$i?>]" value="<?=$value?>" />
                                <?
                                    }
                                }
                            break;

                            case 'S':
                            case 'N':
                                for ($i = 0; $i<$inputNum; $i++)
                                {
                                    if ($arParams['ID'] > 0 || count($arResult['ERRORS']) > 0 || isset($arResult['ELEMENT_PROPERTIES'][$propertyID]) || isset($arResult['ELEMENT'][$propertyID]))
                                    {
                                        $value = intval($propertyID) > 0 ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE'] : $arResult['ELEMENT'][$propertyID];
                                    }
                                    elseif ($i == 0)
                                    {
                                        $value = intval($propertyID) <= 0 ? '' : $arResult['PROPERTY_LIST_FULL'][$propertyID]['DEFAULT_VALUE'];

                                    }
                                    else
                                    {
                                        $value = '';
                                    }
                                    if ($isHidden) {?>
                                        <input type="hidden" name="PROPERTY[<?=$propertyID?>][<?=$i?>]" value="<?=$value?>" /><?
                                    } else {
                                ?>
                                <input type="text" name="PROPERTY[<?=$propertyID?>][<?=$i?>]" size="25" value="<?=$value?>" /><br /><?
                                if($arResult['PROPERTY_LIST_FULL'][$propertyID]['USER_TYPE'] == 'DateTime'):?><?
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:main.calendar',
                                        '',
                                        array(
                                            'FORM_NAME' => 'iblock_add',
                                            'INPUT_NAME' => 'PROPERTY['.$propertyID.']['.$i.']',
                                            'INPUT_VALUE' => $value,
                                        ),
                                        null,
                                        array('HIDE_ICONS' => 'Y')
                                    );
                                    ?><br /><small><?=GetMessage('IBLOCK_FORM_DATE_FORMAT')?><?=FORMAT_DATETIME?></small><?
                                endif;
                                    }
                                }
                            break;

                            case 'F':
                                for ($i = 0; $i<$inputNum; $i++)
                                {
                                    $value = intval($propertyID) > 0 ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE'] : $arResult['ELEMENT'][$propertyID];
                                    ?>
                        <input type="hidden" name="PROPERTY[<?=$propertyID?>][<?=$arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE_ID'] ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE_ID'] : $i?>]" value="<?=$value?>" />
                        <input type="file" size="<?=$arResult['PROPERTY_LIST_FULL'][$propertyID]['COL_COUNT']?>"  name="PROPERTY_FILE_<?=$propertyID?>_<?=$arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE_ID'] ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE_ID'] : $i?>" /><br />
                                    <?

                                    if (!empty($value) && is_array($arResult['ELEMENT_FILES'][$value]))
                                    {
                                        ?>
                    <input type="checkbox" name="DELETE_FILE[<?=$propertyID?>][<?=$arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE_ID'] ? $arResult['ELEMENT_PROPERTIES'][$propertyID][$i]['VALUE_ID'] : $i?>]" id="file_delete_<?=$propertyID?>_<?=$i?>" value="Y" /><label for="file_delete_<?=$propertyID?>_<?=$i?>"><?=GetMessage('IBLOCK_FORM_FILE_DELETE')?></label><br />
                                        <?

                                        if ($arResult['ELEMENT_FILES'][$value]['IS_IMAGE'])
                                        {
                                            ?>
                    <img src="<?=$arResult['ELEMENT_FILES'][$value]['SRC']?>" height="<?=$arResult['ELEMENT_FILES'][$value]['HEIGHT']?>" width="<?=$arResult['ELEMENT_FILES'][$value]['WIDTH']?>" border="0" /><br />
                                            <?
                                        }
                                        else
                                        {
                                            ?>
                    <?=GetMessage('IBLOCK_FORM_FILE_NAME')?>: <?=$arResult['ELEMENT_FILES'][$value]['ORIGINAL_NAME']?><br />
                    <?=GetMessage('IBLOCK_FORM_FILE_SIZE')?>: <?=$arResult['ELEMENT_FILES'][$value]['FILE_SIZE']?> b<br />
                    [<a href="<?=$arResult['ELEMENT_FILES'][$value]['SRC']?>"><?=GetMessage('IBLOCK_FORM_FILE_DOWNLOAD')?></a>]<br />
                                            <?
                                        }
                                    }
                                }

                            break;
                            case 'L':

                                if ($arResult['PROPERTY_LIST_FULL'][$propertyID]['LIST_TYPE'] == 'C')
                                    $type = $arResult['PROPERTY_LIST_FULL'][$propertyID]['MULTIPLE'] == 'Y' ? 'checkbox' : 'radio';
                                else
                                    $type = $arResult['PROPERTY_LIST_FULL'][$propertyID]['MULTIPLE'] == 'Y' ? 'multiselect' : 'dropdown';

                                switch ($type):
                                    case 'checkbox':
                                    case 'radio':

                                        //echo "<pre>"; print_r($arResult["PROPERTY_LIST_FULL"][$propertyID]); echo "</pre>";

                                        foreach ($arResult['PROPERTY_LIST_FULL'][$propertyID]['ENUM'] as $key => $arEnum)
                                        {
                                            $checked = false;
                                            if ($arParams['ID'] > 0 || count($arResult['ERRORS']) > 0)
                                            {
                                                if (is_array($arResult['ELEMENT_PROPERTIES'][$propertyID]))
                                                {
                                                    foreach ($arResult['ELEMENT_PROPERTIES'][$propertyID] as $arElEnum)
                                                    {
                                                        if ($arElEnum['VALUE'] == $key) {$checked = true; break;}
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                if ($arEnum['DEF'] == 'Y') $checked = true;
                                            }

                                            ?>
                            <input type="<?=$type?>" name="PROPERTY[<?=$propertyID?>]<?=$type == 'checkbox' ? '['.$key.']' : ''?>" value="<?=$key?>" id="property_<?=$key?>"<?=$checked ? ' checked="checked"' : ''?> /><label for="property_<?=$key?>"><?=$arEnum['VALUE']?></label><br />
                                            <?
                                        }
                                    break;

                                    case 'dropdown':
                                    case 'multiselect':
                                    ?>
                            <select name="PROPERTY[<?=$propertyID?>]<?=$type=='multiselect' ? '[]" size="'.$arResult['PROPERTY_LIST_FULL'][$propertyID]['ROW_COUNT'].'" multiple="multiple' : ''?>">
                                    <?
                                        if (intval($propertyID) > 0) $sKey = 'ELEMENT_PROPERTIES';
                                        else $sKey = 'ELEMENT';

                                        foreach ($arResult['PROPERTY_LIST_FULL'][$propertyID]['ENUM'] as $key => $arEnum)
                                        {
                                            $checked = false;
                                            if ($arParams['ID'] > 0 || count($arResult['ERRORS']) > 0)
                                            {
                                                foreach ($arResult[$sKey][$propertyID] as $elKey => $arElEnum)
                                                {
                                                    if ($key == $arElEnum['VALUE']) {$checked = true; break;}
                                                }
                                            }
                                            else
                                            {
                                                if ($arEnum['DEF'] == 'Y') $checked = true;
                                            }
                                            ?>
                                <option value="<?=$key?>" <?=$checked ? ' selected="selected"' : ''?>><?=$arEnum['VALUE']?></option>
                                            <?
                                        }
                                    ?>
                            </select>
                                    <?
                                    break;

                                endswitch;
                            break;
                        endswitch;
                    if (!$isHidden) {
                        if (isset($arResult['ERRORS'][$propertyID])) {?>
                            <span class="help-inline"><?=implode('<br />', $arResult['ERRORS'][$propertyID]);?></span><?
                        }?>
                    </div>
                </div><?
                    }
            }
            if($arParams['USE_CAPTCHA'] == 'Y' && $arParams['ID'] <= 0):?>
                <div class="control-group">
                    <label class="control-label"><?=GetMessage('IBLOCK_FORM_CAPTCHA_TITLE')?></label>
                    <div class="controls">
                        <input type="hidden" name="captcha_sid" value="<?=$arResult['CAPTCHA_CODE']?>" />
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult['CAPTCHA_CODE']?>" width="180" height="40" alt="CAPTCHA" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?=GetMessage('IBLOCK_FORM_CAPTCHA_PROMPT')?><span class="starrequired">*</span>:</label>
                    <div class="controls"><input type="text" name="captcha_word" maxlength="50" value=""></div>
                </div>
            <?endif?>
        <?endif?>
        <div class="control-group">
            <div class="controls">
                <input type="submit" class="btn btn-primary" name="iblock_submit" value="<?=GetMessage('IBLOCK_FORM_SUBMIT')?>" />
            </div>
        </div><?
        if (defined('YAMETRIKA') && strlen(YAMETRIKA) > 0) {?>
        <script type="text/javascript">
            try {
                window.yaCounter<?=YAMETRIKA;?>.reachGoal('form_open', {
                    title: title
                });
            } catch(e) { }
        </script><?
        }
    }?>
</form>
