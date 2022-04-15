<?php

namespace lib;

use \Bitrix\Main\Service\GeoIp\Manager as GeoIP;
use Bitrix\Sale;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Main\Loader;

Loader::includeModule("sale");

class City
{
    public static function getCityList(): array
    {
        $res = LocationTable::getList(array(
            'filter' => array('=TYPE.ID' => '5', '=NAME.LANGUAGE_ID' => LANGUAGE_ID),
            'select' => array('NAME_RU' => 'NAME.NAME')
        ));
        while ($item = $res->fetch()) {
            $cities[] = $item['NAME_RU'];
        }
        return $cities;
    }

    public static function getUserCity()
    {
        $ip = GeoIP::getRealIp();
        $geoIpData = GeoIP::getDataResult($ip, LANGUAGE_ID);
        return $geoIpData->getGeoData()->cityName;
    }

    public static function setCityCookie($cityName): bool
    {
        setcookie('user_city', $cityName);
        self::setCityAccept('true');
        return true;
    }

    public static function checkCityCookie()
    {
        return $_COOKIE['user_city'] ?? self::getUserCity();
    }

    public static function setCityAccept($status): bool
    {
        setcookie('city_accept', $status);
        return true;
    }

    public static function checkCityAccept()
    {
        if ($_COOKIE['city_accept'] === 'true') {
            return self::checkCityCookie();
        }
        return false;
    }
}