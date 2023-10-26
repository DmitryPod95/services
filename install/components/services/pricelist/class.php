<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
// класс для всех исключений в системе
use Bitrix\Main\SystemException;
// класс для загрузки необходимых файлов, классов, модулей
use Bitrix\Main\Loader;

use  Bitrix\Main\Entity\DataManager;

class CIblocList extends CBitrixComponent
{

    public function executeComponent()
    {
        try {

            $this->checkModules();
            $this->getResult();
        } catch (SystemException $e) {
            ShowError($e->getMessage());
        }
    }

    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('iblock'))
            throw new SystemException(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    }


    public function onPrepareComponentParams($arParams)
    {

        if (!isset($arParams['CACHE_TIME'])) {
            $arParams['CACHE_TIME'] = 3600000;
        } else {
            $arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
        }

        $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"] ?? '');
        if (empty($arParams["IBLOCK_TYPE"]))
        {
            $arParams["IBLOCK_TYPE"] = "services";
        }
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['NEWS_COUNT'] = (int)($arParams['NEWS_COUNT'] ?? 0);


        return $arParams;
    }

    protected function getResult()
    {

        if ($this->startResultCache()) {

            $this->arResult["PRICE_LIST"] = self::getElements($this->arParams['IBLOCK_ID']);

            $this->IncludeComponentTemplate();

        } else {

            $this->AbortResultCache();
            \Bitrix\Iblock\Component\Tools::process404(
                Loc::getMessage('PAGE_NOT_FOUND'),
                true,
                true
            );
        }
    }

    protected static function getSection($iBlock) {

        if(!$iBlock) {
            return false;
        }

        $arSection = [];

        $dataIblock = \Bitrix\Iblock\SectionTable::getList([
            'select' => ["ID","NAME", "DEPTH_LEVEL", "IBLOCK_SECTION_ID"],
            'filter' => [
                "IBLOCK_ID" => $iBlock,
                "ACTIVE" => "Y",
            ]
        ]);


        $arSectionID = [];
        $arSections = [];
        $arSectionsLV2 = [];
        while($rsSections = $dataIblock->Fetch()) {
            $arSectionID[$rsSections["ID"]] = $rsSections["ID"];

            if(empty($rsSections["IBLOCK_SECTION_ID"])) {
                $arSections[$rsSections["ID"]] = $rsSections;
            } else {
                $arSectionsLV2[$rsSections["ID"]] = $rsSections;
            }
        }

        foreach ($arSections as &$section) {
            foreach ($arSectionsLV2 as $subSection) {
                if(strcasecmp($section["ID"],$subSection["IBLOCK_SECTION_ID"]) == 0) {
                    $arSections[$section["ID"]]["SUB"][$subSection["ID"]] = $subSection;
                }
            }
        }

        if(!$arSections) {
            return false;
        }
        return [
            'IDS' => $arSectionID,
            "SECTIONS" => $arSections
        ];
    }

    /**
     * Get array Result (sections,elements)
     * @param $iBlock - IBLOCK_ID
     * @return array|false
     */
    protected static function getElements($iBlock) {


        if(!$iBlock) {
            return false;
        }

        //добавить проверку
        $arSections = self::getSection($iBlock);


        $arFilter = [
            "IBLOCK_ID"     => $iBlock,
            "SECTION_ID"    => array_values($arSections["IDS"]),
            "ACTIVE"        => "Y",
        ];

        $obElement = CIBlockElement::GetList([],$arFilter,false, false,[]);

        while($rElement = $obElement->GetNextElement()) {
            $arFields = $rElement->GetFields();
            $arFields["PROP"] = $rElement->GetProperties()["PROP_PRICE_LIST"];

            foreach ($arSections["SECTIONS"] as &$sections) {
                foreach ($sections["SUB"] as &$section) {

                    if(strcasecmp($section["ID"], $arFields["IBLOCK_SECTION_ID"]) == 0) {
                        $section["ELEMENTS"][$arFields["ID"]] = [
                            "ID"                => $arFields["ID"],
                            "IBLOCK_ID"         => $arFields["IBLOCK_ID"],
                            "NAME"              => $arFields["NAME"],
                            "PROPERTY"          => $arFields["PROP"]["VALUE"],
                        ];
                    }
                }
            }
        }

        if(!$arSections["SECTIONS"]) {
            return false;
        }

        return $arSections["SECTIONS"];
    }
}
