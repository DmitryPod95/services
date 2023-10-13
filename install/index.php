<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);


class services extends CModule
{

    private $IBlockType = "services";


    public function __construct()
    {

        $this->MODULE_ID = get_class($this);
        $this->MODULE_VERSION = "1.0.0";
        $this->MODULE_VERSION_DATE = "2023-10-02";
        $this->MODULE_NAME = Loc::getMessage('SERVICES_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SERVICES_DESCRIPTION');
    }


    public function DoInstall()
    {
        global $APPLICATION;

        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            $this->InstallEvent();
            $this->CreateIblockType();
            $this->InstallFiles();
            ModuleManager::registerModule($this->MODULE_ID);

        } else {
            CAdminMessage::showMessage(
                Loc::getMessage('SERVICES_INSTALL_ERROR')
            );
            return;
        }

        $APPLICATION->includeAdminFile(Loc::getMessage("SERVICES_INSTALL_TITLE") . '"' . Loc::getMessage("SERVICES_NAME") . '"', __DIR__ . "/step.php");
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $this->DelIblockType();
        $this->UnInstallEvent();
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->includeAdminFile(Loc::getMessage("SERVICES_UNINSTALL_TITLE") . '"' . Loc::getMessage("SERVICES_NAME") . '"', __DIR__ . "/unstep.php");

    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/services/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/services/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
    }

    /**
     * General creation IBlock Type
     * @return void
     */
    private function CreateIblockType()
    {
        $arFieldsForType = [
            'ID' => $this->IBlockType,
            'SECTIONS' => "Y",
            'IN_RSS' => "N",
            'SORT' => 500,
            'LANG' => [
                'ru' => [
                    'NAME' => Loc::getMessage("SERVICE_IBLOCK_TYPE_NAME"),
                ],
                'en' => [
                    'NAME' => "services"
                ],
            ],
        ];

        if ($this->AddIBlockType($arFieldsForType)) {

            $arFieldsForIBlock = [
                'ACTIVE' => "Y",
                'NAME' => Loc::getMessage("SERVICE_IBLOCK_NAME"),
                'CODE' => "services",
                'IBLOCK_TYPE_ID' => $arFieldsForType["ID"],
                'SITE_ID' => "s1",
                "GROUP_ID" => array("2" => "R"),
                'FIELDS' => [
                    "CODE" => [
                        "IS_REQUIRED" => "Y",
                        "DEFAULT_VALUE" => [
                            "TRANS_CASE" => "L",
                            "UNIQUE" => "Y",
                            "TRANSLITERATION" => "Y",
                            "TRANS_SPACE" => "-",
                            "TRANS_OTHER" => "-"
                        ],
                    ],
                    "SECTION_CODE" => [
                        "IS_REQUIRED" => "Y",
                        "DEFAULT_VALUE" => [
                            "TRANS_CASE" => "L",
                            "UNIQUE" => "Y",
                            "TRANSLITERATION" => "Y",
                            "TRANS_SPACE" => "-",
                            "TRANS_OTHER" => "-"
                        ],
                    ],
                ],
                "LIST_PAGE_URL" => "#SITE_DIR#/services/",
                "SECTION_PAGE_URL" => "#SITE_DIR#/services/#SECTION_CODE#/",
                "DETAIL_PAGE_URL" => "#SITE_DIR#/services/#SECTION_CODE#/#ELEMENT_CODE#/",
                "ELEMENT_NAME" => "Услуга",
                "ELEMENTS_NAME" => "Услуги",
                "ELEMENT_ADD" => "Добавить услугу",
                "ELEMENT_EDIT" => "Изменить услугу",
                "ELEMENT_DELETE" => "Удалить услугу",
                "SECTION_NAME" => "Разделы",
                "SECTIONS_NAME" => "Раздел",
                "SECTION_ADD" => "Добавить раздел",
                "SECTION_EDIT" => "Изменить раздел",
                "SECTION_DELETE" => "Удалить раздел",
            ];

            if($iblockID = $this->AddIBlock($arFieldsForIBlock)) {
                $arFieldsProperty = [
                    'NAME' => Loc::getMessage("IBLOCK_PROPERTY_PRISE_LIST"),
                    'SORT' => "100",
                    'MULTIPLE' => "Y",
                    'CODE' => "PROP_PRICE_LIST",
                    'PROPERTY_TYPE' => "S",
                    'USER_TYPE' => "multicolumn",
                    'IBLOCK_ID' => $iblockID
                ];

                $this->AddIBlockProps($arFieldsProperty);

            } else {
                CAdminMessage::ShowMessage(array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => GetMessage("SERVICE_IBLOCK_NOT_INSTALLED"),
                    "DETAILS" => "",
                    "HTML" => true
                ));
            }

        } else {
            CAdminMessage::ShowMessage(array(
                "TYPE" => "ERROR",
                "MESSAGE" => GetMessage("SERVICE_IBLOCK_TYPE_NOT_INSTALLED"),
                "DETAILS" => "",
                "HTML" => true
            ));
        }
    }

    /**
     * Delete IBlock Type
     * @return void
     */
    private function DelIblockType()
    {
        global $DB;

        CModule::IncludeModule('iblock');

        $DB->StartTransaction();

        if (!CIBlockType::Delete($this->IBlockType)) {
            $DB->Rollback();
            CAdminMessage::ShowMessage(array(
                "TYPE" => "ERROR",
                "MESSAGE" => GetMessage("SERVICE_IBLOCK_TYPE_DELETE_ERROR"),
                "DETAILS" => "",
                "HTML" => true
            ));
        }
        $DB->Commit();
    }

    /**
     * Creation IBlock Type
     * @param $arFieldsIBT
     * @return false|void
     */
    protected function AddIBlockType($arFieldsIBT)
    {
        global $DB;
        CModule::IncludeModule('iblock');

        $iblockType = $arFieldsIBT["ID"];

        $dbIblockType = CIBlockType::GetList(["SORT" => "ASC"], ["ID" => $iblockType]);

        if (!$arIblockType = $dbIblockType->Fetch()) {
            $obIBlockType = new CIBlockType();
            $DB->StartTransaction();
            $resIBT = $obIBlockType->Add($arFieldsIBT);
            if (!$resIBT) {
                $DB->Rollback();
                echo 'Error: ' . $obIBlockType->LAST_ERROR;
                die();
            } else {
                $DB->Commit();
            }
        } else {
            return false;
        }

        return $iblockType;

    }

    /**
     * Creation IBlock
     * @param $arFieldsIBlock
     * @return false|string
     */
    protected function AddIBlock($arFieldsIBlock)
    {
        CModule::IncludeModule('iblock');

        $ID = '';

        $iblockCode = $arFieldsIBlock["CODE"];
        $iblockType = $arFieldsIBlock["TYPE"];

        $iBlock = new CIBlock();

        $obIBlock = CIBlock::GetList([], ["TYPE" => $iblockType, "CODE" => $iblockCode]);
        if (!$arIBlock = $obIBlock->Fetch()) {
            $ID = $iBlock->Add($arFieldsIBlock);
        }
        return $ID;
    }

    /**
     * Create IBlock Property
     * @param $arFieldsProp
     * @return false|string
     */
    protected function AddIBlockProps($arFieldsProp){
        CModule::IncludeModule("iblock");
        $iblockProp = new CIBlockProperty();

        return $iblockProp->Add($arFieldsProp);
    }
    protected function InstallEvent() {
        RegisterModuleDependences('iblock','OnIBlockPropertyBuildList', $this->MODULE_ID,'\\services\\EventHandler','GetUserTypeProperty');
    }
    protected function UnInstallEvent() {
        UnRegisterModuleDependences('iblock','OnIBlockPropertyBuildList', $this->MODULE_ID,'\\services\\EventHandler','GetUserTypeProperty');
    }

}

