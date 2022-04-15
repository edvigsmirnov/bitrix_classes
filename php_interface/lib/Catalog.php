<?php

namespace lib;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use CIBlock;
use CFile;
use CIBlockSection;
use CIBlockElement;
use Bitrix\Highloadblock;
use Bitrix\Main\Entity;
use CCatalogDiscount;
use CSite;

Loader::includeModule("highloadblock");
Loader::includeModule('iblock');

class Catalog
{
    function getSectionList(): array
    {
        $arFilter = [
            'IBLOCK_ID' => 27,
            '<=DEPTH_LEVEL' => 2,
            'GLOBAL_ACTIVE' => 'Y'
        ];
        $arOrder = [
            'LEFT_MARGIN' => 'ASC'
        ];

        $arSelect = [
            'ID',
            'IBLOCK_SECTION_ID',
            'LEFT_MARGIN',
            'DEPTH_LEVEL',
            'NAME',
            'UF_SHOW_ON_MAIN',
            'UF_COMPACT',
            'PICTURE',
            'SECTION_PAGE_URL'
        ];

        $resSections = \CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);

        while ($arSection = $resSections->fetch()) {
            $sections[] = $arSection;
        }

        foreach ($sections as $section) {
            if ($section['DEPTH_LEVEL'] === 1) {
                $parent[] = [
                    'id' => $section['ID'],
                    'name' => $section['NAME'],
                    'url' => "/catalog/" . $section['CODE'] . "/",
                    'image' => CFile::getPath($section['PICTURE']),
                    'main' => $section['UF_SHOW_ON_MAIN'],
                    'compact' => $section['UF_COMPACT'],
                ];
            }
        }

        foreach ($parent as $section) {
            foreach ($sections as $subsection) {
                if ($section['id'] === $subsection['IBLOCK_SECTION_ID']) {
                    $subsections[] = [
                        'name' => $subsection['NAME'],
                        'url' => $section['url'] . $subsection['CODE'] . '/',
                    ];
                }
            }
            $section['child'] = $subsections;
            $result[] = $section;
            unset($subsections);
        }
        return $result;
    }

    public static function getBrands(): array
    {
        $hlblock = HighloadBlockTable::getList(
            array("filter" => array(
                'TABLE_NAME' => 'b_hlbd_producer',
            ))
        )->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $res = $entity_data_class::getList(array('filter' => array()));
        while ($item = $res->fetch()) {
            if ($item['UF_POPULAR'] === '1') {
                $brand[] = [
                    'name' => $item['UF_NAME'],
                    'image' => CFile::GetPath($item['UF_FILE']),
                    'link' => $item['UF_LINK'],
                ];
            }
        }
        return $brand;
    }

    public static function getSectionName($sectionID): array
    {
        $res = CIBlockSection::GetByID($sectionID);
        if ($ar_res = $res->GetNext()) {
            return [
                'name' => $ar_res['NAME'],
                'url' => $ar_res['SECTION_PAGE_URL'],
            ];
        }
        return [];
    }

    public static function getNewProducts(): array
    {
        $arOrder = [
            'created_date ' => 'DESC'
        ];
        $arFilter = [
            'IBLOCK_ID' => 27,
            'ACTIVE' => 'Y'
        ];
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'PREVIEW_PICTURE',
            'PROPERTY_ART_NUMBER',
            'NAME',
            'IBLOCK_SECTION_ID',
            'CATALOG_GROUP_1',
            'DATE_CREATE',
            'DETAIL_PAGE_URL'
        ];
        $arLimit = [
            'nTopCount' => 16,
        ];
        $res = CIBlockElement::GetList($arOrder, $arFilter, false, $arLimit, $arSelect);
        while ($ar_fields = $res->GetNext()) {
            $items[] = $ar_fields;
        }
        $arCount = count($items);
        if ($arCount > 3) {
            $limit = 3;
        } else {
            $limit = $arCount - 1;
        }

        shuffle($items);

        for ($i = 0; $i <= $limit; $i++) {
            $newItems[] = [
                'name' => $items[$i]['NAME'],
                'detail_page' => $items[$i]['DETAIL_PAGE_URL'],
                'image' => CFile::GetPath($items[$i]['PREVIEW_PICTURE']),
                'category' => self::getSectionName($items[$i]['IBLOCK_SECTION_ID'])['name'],
                'category_url' => self::getSectionName($items[$i]['IBLOCK_SECTION_ID'])['url'],
                'art_number' => $items[$i]['PROPERTY_ART_NUMBER_VALUE'],
                'price' => number_format($items[$i]['CATALOG_PRICE_1'], 0, '', ' '),
            ];
        }
        return $newItems;
    }

    public static function getDiscountedItems(): array
    {
        $arFilter = [
            'IBLOCK_ID' => 27,
            'ACTIVE' => 'Y'
        ];
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'PREVIEW_PICTURE',
            'PROPERTY_ART_NUMBER',
            'NAME',
            'IBLOCK_SECTION_ID',
            'CATALOG_GROUP_1',
            'DATE_CREATE',
            'DETAIL_PAGE_URL',
            'DISCOUNT_PRICE'
        ];

        $res = CIBlockElement::GetList(true, $arFilter, false, false, $arSelect);
        while ($ar_fields = $res->GetNext()) {
            $items[] = $ar_fields;
        }
        return $items;
    }
}