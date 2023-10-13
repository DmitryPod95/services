<?php

namespace services;

use Bitrix\Main\Localization\Loc;

class EventHandler
{
    public function GetUserTypeProperty()
    {
        return array(
            "PROPERTY_TYPE"        => "S", #-----один из стандартных типов
            "USER_TYPE"            => "multicolumn", #-----идентификатор типа свойства
            "DESCRIPTION"          => Loc::getMessage("USER_TYPE_PROPERTY_DESCRIPTION"),
            "GetPropertyFieldHtml" => array("CIBlockNewProperty", "GetPropertyFieldHtml"),
            "ConvertToDB" => array(__CLASS__, "ConvertToDB"), #-----функция конвертирования данных перед сохранением в базу данных
            "ConvertFromDB" => array(__CLASS__, "ConvertFromDB"),
        );
    }

    /*--------- вывод поля свойства на странице редактирования ---------*/
    public function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        return '<textarea style="width: 49%;height: 100px;box-sizing: border-box;" type="text" name="'.$strHTMLControlName["VALUE"].'[0]">'.$value['VALUE'][0].'</textarea> 
                <textarea style="width: 49%;height: 100px;box-sizing: border-box;" type="text" name="'.$strHTMLControlName["VALUE"].'[1]">'.$value['VALUE'][1].'</textarea>';
    }

    public static function ConvertToDB($arProperty, $arValue)
    {
        if (strlen($arValue['VALUE'][0]) && strlen($arValue['VALUE'][1])) {

            $arValue['VALUE'] = $arValue['VALUE'][0].'###'.$arValue['VALUE'][1];
        }

        return $arValue;
    }

    public static function ConvertFromDB($arProperty, $arValue)
    {
        if ($arValue['VALUE']) {
            $arr = explode('###', $arValue['VALUE']);
            $arValue['VALUE'] = $arr;
        }
        return $arValue;
    }
}
