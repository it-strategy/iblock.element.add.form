<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
if (is_array($arResult['PROPERTY_LIST']) && !empty($arResult['PROPERTY_LIST'])) {
    foreach ($arResult['PROPERTY_LIST'] as $propertyID) {
        if ($arResult['PROPERTY_LIST_FULL'][$propertyID]['PROPERTY_TYPE'] == 'E' &&
                $arResult['PROPERTY_LIST_FULL'][$propertyID]['USER_TYPE'] == 'EAutocomplete') {
            $APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
            $arResult['PROPERTY_LIST_FULL'][$propertyID]['GetPublicEditHTML'] = 'CIBlockPropertyElementAutoComplete::GetPropertyFieldHtml';
        }
    }
}
