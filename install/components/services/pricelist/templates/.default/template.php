<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

?>

<?if($arResult["PRICE_LIST"]):?>
    <div class="container">
        <div class="sections">
            <?foreach ($arResult["PRICE_LIST"] as $sections):?>
                <?if($sections["SUB"]):?>
                    <div class="section-list">
                        <div class="section-list-title">
                            <?=$sections["NAME"];?>
                            <div class="plus">+</div>
                        </div>
                        <div class="section-list__sub">
                            <?foreach ($sections["SUB"] as $section):?>
                                <div class="sub-items">
                                    <?=$section["NAME"];?>
                                    <div class="plus">+</div>
                                </div>
                                <?if($section["ELEMENTS"]):?>
                                    <div class="element-list">
                                        <?foreach ($section["ELEMENTS"] as $item):?>
                                            <div class="element">
                                                <div class="element-title"><?=$item["NAME"];?></div>
                                                <?if($item["PROPERTY"]):?>
                                                    <div class="info">
                                                        <?foreach ($item["PROPERTY"] as $price):?>
                                                            <div class="price-info">
                                                                <div class="price-name">
                                                                    <?=$price[0];?>
                                                                </div>
                                                                <div class="price-value">
                                                                    <?=$price[1]?>
                                                                </div>
                                                            </div>
                                                        <?endforeach;?>
                                                    </div>
                                                <?endif;?>
                                            </div>
                                        <?endforeach;?>
                                    </div>
                                <?endif;?>
                            <?endforeach;?>
                        </div>
                    </div>
                <?endif;?>
            <?endforeach;?>
        </div>
    </div>
<?endif;?>

