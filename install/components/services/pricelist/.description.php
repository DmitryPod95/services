<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(

    'NAME' => GetMessage("COMPONENT_NAME"),
    'DESCRIPTION' => GetMessage("COMPONENT_DESCRIPTION"),
    'CACHE_PATH' => 'Y',
    "SORT" => "20",
    'PATH' => array(
        'ID' => 'services',
        "NAME" => GetMessage("T_IBLOCK_DESC_SERVICES"),
    )
);
