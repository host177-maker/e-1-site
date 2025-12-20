<?php
namespace Absteam;

use Absteam\SpeedMeter\SpeedMeterBuilder;
use Absteam\Handlers;
use Absteam\PriceUpdater;
use \Bitrix\Main\Service\GeoIp;
use Dadata\DadataClient;
use FilesystemIterator;
use PDO;

final class Helper
{
    static $arRegion;

    public static function recursiveRemoveDir($dir)
    {

        $includes = new FilesystemIterator($dir);

        foreach ($includes as $include) {

            if (is_dir($include) && !is_link($include)) {

                self::recursiveRemoveDir($include);
            } else {

                unlink($include);
            }
        }

        rmdir($dir);
    }

    /**
     * Создает экзепляр измерителя
     */
    public static function getMeter($chanelName = 'mainlog')
    {
        static $meter = null;
        $configuration = \Bitrix\Main\Config\Configuration::getInstance();
        $configConnections = $configuration->get("connections");
        $pathPDO = !empty(\Cosmos\Config::getInstance()->getParam("PDO")['PATH']) ? \Cosmos\Config::getInstance()->getParam("PDO")['PATH'] : '/var/lib/mysqld/mysqld.sock';
        $pdo = new PDO(
            "mysql:unix_socket=" . $pathPDO . ";dbname=" . $configConnections['default']['database'] . ";host=" . $configConnections['default']['host'] . ";",
            $configConnections['default']['login'],
            $configConnections['default']['password']
        );

        if (!isset($meter[$chanelName])) {
            $meter[$chanelName] = (new SpeedMeterBuilder($pdo, $chanelName))
                ->addWebProcessor() // закидывае в лог URL
                ->addMemoryUsageProcessor() // закидываем в лог количество использованной в скрипте памяти
                ->build();
        }
        return $meter[$chanelName];
    }

    /**
     * Создает экземпляр клиента DaData
     */
    public static function getDadataClient(): DadataClient
    {
        static $client = null;

        if (!$client) {
            $token = "4542e0caeea12db2407b984a328ec4a65f9205d8";
            $secret = "4f2283dce5a714b8504bf1c511594a35b93a91c0";
            $client = new DadataClient($token, $secret);
        }

        return $client;
    }

    /**
     * Вернет IP-адрес клиента или false
     */
    public static function getRealIp()
    {
        $ip = !empty($_SERVER['HTTP_CLIENT_IP'])
            ? $_SERVER['HTTP_CLIENT_IP']
            : (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])
                ? $_SERVER['HTTP_X_FORWARDED_FOR']
                : (!empty($_SERVER['REMOTE_ADDR'])
                    ? $_SERVER['REMOTE_ADDR']
                    : false));
        return $ip;
    }

    /**
     * Получает гео-данные по IP
     */
    public static function getGeoByIp($ip = null)
    {
        if (!$ip) {
            $ip = self::getRealIp();
        }
        if ($ip === false)
            return null;

        $result = null;
        $cache = new \CPHPCache();
        $cache_time = 86400;
        $cache_path = 'ip_geo_data';
        $cache_id = md5($ip);
        if (empty(self::$arRegion)) {
            if ($cache->InitCache($cache_time, $cache_id, $cache_path)) {
                $result = $cache->GetVars()['geo'];
            } else {
                if (!self::isLocalNetworkIp($ip)) {
                    try {
                        $dadata = self::getDadataClient();
                        $response = $dadata->iplocate($ip);

                        if ($response !== null) {
                            $result = self::getGeoAsBxData($response);
                        } else {
                            $ob = new GeoIp\SypexGeo();

                            $result = $ob->getDataResult(self::getRealIp(), 'ru')->getGeoData();

                            if (!$result->cityName) {
                                $response = self::$defaultGeo;
                                $result = self::getGeoAsBxData($response);
                            }
                        }

                    } catch (\Throwable $th) {
                        $response = self::$defaultGeo;
                        $result = self::getGeoAsBxData($response);
                    }
                } else {
                    $response = self::$defaultGeo;
                    $result = self::getGeoAsBxData($response);
                }
                $cache->StartDataCache($cache_time, $cache_id, $cache_path);
                $cache->EndDataCache(array("geo" => $result, 'dadata' => $response));
            }
            self::$arRegion = $result;
        }
        return self::$arRegion;
    }

    static function isLocalNetworkIp($ip)
    {
        // Проверяем, является ли IP-адрес действительным
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        // Преобразуем IP-адрес в целое число для сравнения
        $ipLong = ip2long($ip);

        // Диапазоны частных IP-адресов
        $privateRanges = [
            ['start' => ip2long('10.0.0.0'), 'end' => ip2long('10.255.255.255')],
            ['start' => ip2long('172.16.0.0'), 'end' => ip2long('172.31.255.255')],
            ['start' => ip2long('192.168.0.0'), 'end' => ip2long('192.168.255.255')],
        ];

        // Проверяем, попадает ли IP-адрес в один из частных диапазонов
        foreach ($privateRanges as $range) {
            if ($ipLong >= $range['start'] && $ipLong <= $range['end']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получает наименование города из массива гео-данных от Дадата
     */
    public static function getCityFromGeoData(array $data)
    {
        return empty($data['data']['city']) ? null : $data['data']['city'];
    }

    /**
     * Преобразует данные из формата ДаДаты в формат Bitrix Data
     */
    public static function getGeoAsBxData(array $data)
    {
        if (empty($data['data']))
            return new GeoIp\Data();

        $data = $data['data'];
        $bxData = new GeoIp\Data();

        $bxData->ip = self::getRealIp();
        $bxData->lang = LANG;
        $bxData->countryName = $data['country'];
        $bxData->regionName = $data['region'] . ' ' . $data['region_type_full'];
        $bxData->cityName = $data['city'];
        $bxData->countryCode = $data['country_iso_code'];
        $bxData->regionCode = $data['region_iso_code'];
        $bxData->zipCode = $data['postal_code'];
        $bxData->latitude = $data['geo_lat'];
        $bxData->longitude = $data['geo_lon'];
        $bxData->timezone = $data['timezone'];

        return $bxData;
    }

    /**
     * Выдает гео-данные в формате Bitrix Result
     */
    public static function getGeoAsBxResult($ip)
    {
        if (!$ip)
            return null;

        $bxData = self::getGeoByIp($ip);
        if (!$bxData)
            return null;
        $result = new GeoIp\Result();
        $result->setGeoData($bxData);

        return $result;

    }

    /**
     * Возвращает код региона из БД.
     *
     */
    public static function getBXLocationCode($ip = false, $lang = LANGUAGE_ID)
    {
        if (!$ip)
            return null;

        $fields = array();
        $geoData = self::getGeoAsBxResult($ip);

        if ($geoData)
            $fields = \Bitrix\Sale\Location\GeoIp::getLocationFields($geoData, $lang);

        return $fields['CODE'] <> '' ? $fields['CODE'] : '';
    }

    public static function CatalogDataChangeAgent()
    {
        if (\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog")) {
            PriceUpdater::Update(); // добавить любую строчку в параметр, для пересчета всего каталога
        }

        return '\Absteam\Helper::CatalogDataChangeAgent();';
    }

    public static function CatalogImgUpdaterAgent()
    {
        if (\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog")) {

            $destinationDir = $_SERVER["DOCUMENT_ROOT"] . "/upload/upload_img_offers";

            $ftp_host = \Cosmos\Config::getInstance()->getParam('COMMON')['ftp_host'];
            $ftp_user = \Cosmos\Config::getInstance()->getParam('COMMON')['ftp_user'];
            $ftp_password = \Cosmos\Config::getInstance()->getParam('COMMON')['ftp_password'];
            $imgUpdater = new CatalogImgUpdater($ftp_host, $ftp_user, $ftp_password);

            try {
                $imgUpdater->updateCatalogImages($destinationDir);
                $imgUpdater->finish($destinationDir);
            } catch (\Throwable $ex) {
                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . "/CatalogImgUpdater.log",
                    "\n\n" . print_r([date('d-m-Y H:m'), $ex->getMessage()], true) . "\n",
                    FILE_APPEND
                );
            }
        }

        return '\Absteam\Helper::CatalogImgUpdaterAgent();';
    }

    public static function getAssetHash($relativePath)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
        if (file_exists($path)) {
            return filemtime($path);
        }
        return "";
    }


    private static $defaultGeo = [
        "value" => "г Москва",
        "unrestricted_value" => "101000, г Москва",
        "data" => [
            "postal_code" => "101000",
            "country" => "Россия",
            "country_iso_code" => "RU",
            "federal_district" => "Центральный",
            "region_fias_id" => "0c5b2444-70a0-4932-980c-b4dc0d3f02b5",
            "region_kladr_id" => "7700000000000",
            "region_iso_code" => "RU-MOW",
            "region_with_type" => "г Москва",
            "region_type" => "г",
            "region_type_full" => "город",
            "region" => "Москва",
            "area_fias_id" => null,
            "area_kladr_id" => null,
            "area_with_type" => null,
            "area_type" => null,
            "area_type_full" => null,
            "area" => null,
            "city_fias_id" => "0c5b2444-70a0-4932-980c-b4dc0d3f02b5",
            "city_kladr_id" => "7700000000000",
            "city_with_type" => "г Москва",
            "city_type" => "г",
            "city_type_full" => "город",
            "city" => "Москва",
            "city_area" => null,
            "city_district_fias_id" => null,
            "city_district_kladr_id" => null,
            "city_district_with_type" => null,
            "city_district_type" => null,
            "city_district_type_full" => null,
            "city_district" => null,
            "settlement_fias_id" => null,
            "settlement_kladr_id" => null,
            "settlement_with_type" => null,
            "settlement_type" => null,
            "settlement_type_full" => null,
            "settlement" => null,
            "street_fias_id" => null,
            "street_kladr_id" => null,
            "street_with_type" => null,
            "street_type" => null,
            "street_type_full" => null,
            "street" => null,
            "stead_fias_id" => null,
            "stead_cadnum" => null,
            "stead_type" => null,
            "stead_type_full" => null,
            "stead" => null,
            "house_fias_id" => null,
            "house_kladr_id" => null,
            "house_cadnum" => null,
            "house_type" => null,
            "house_type_full" => null,
            "house" => null,
            "block_type" => null,
            "block_type_full" => null,
            "block" => null,
            "entrance" => null,
            "floor" => null,
            "flat_fias_id" => null,
            "flat_cadnum" => null,
            "flat_type" => null,
            "flat_type_full" => null,
            "flat" => null,
            "flat_area" => null,
            "square_meter_price" => null,
            "flat_price" => null,
            "room_fias_id" => null,
            "room_cadnum" => null,
            "room_type" => null,
            "room_type_full" => null,
            "room" => null,
            "postal_box" => null,
            "fias_id" => "0c5b2444-70a0-4932-980c-b4dc0d3f02b5",
            "fias_code" => null,
            "fias_level" => "1",
            "fias_actuality_state" => "0",
            "kladr_id" => "7700000000000",
            "geoname_id" => "524901",
            "capital_marker" => "0",
            "okato" => "45000000000",
            "oktmo" => "45000000",
            "tax_office" => "7700",
            "tax_office_legal" => "7700",
            "timezone" => "UTC+3",
            "geo_lat" => "55.75396",
            "geo_lon" => "37.620393",
            "beltway_hit" => null,
            "beltway_distance" => null,
            "metro" => null,
            "divisions" => null,
            "qc_geo" => "4",
            "qc_complete" => null,
            "qc_house" => null,
            "history_values" => null,
            "unparsed_parts" => null,
            "source" => null,
            "qc" => null
        ]
    ];


}