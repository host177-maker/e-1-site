<?php
 
 use Bitrix\Main\Config\Option;
 use Bitrix\Main\Loader;
 use Bitrix\Main\Web\HttpClient;
 use GuzzleHttp\Client;
 
 IncludeModuleLangFile(__FILE__);
 require_once  __DIR__ . '/full_text.php';
 require_once  __DIR__ . '/custom_all_search.php';

class CSearchAnmarto extends CSearchFullText
{
    public $arForumTopics = array();
    public $db = false;
    private static $typesMap = array(
        "timestamp" => "rt_attr_timestamp",
        "string" => "rt_attr_string",
        "bigint" => "rt_attr_bigint",
        "uint" => "rt_attr_uint",
        "field" => "rt_field",
        "mva" => "rt_attr_multi",
    );
    private $errorText = "";
    private $errorNumber = 0;
    private $recodeToUtf = false;
    public $tags = "";
    public $query = "";
    public $SITE_ID = "";
    public $host = '';
    public $token = '';
    public $connectionIndex = "";
    public $indexName = "b_search_content_text";
    private $baseUrl;
    /**
     * @var $client HttpClient|null
     * */
    protected $client = null;
    protected $headers = null;

    function __construct()
    {

        if (file_exists(__DIR__ . '/../install/vendor/autoload.php'))
            require_once __DIR__ . '/../install/vendor/autoload.php';

        if (class_exists('Cosmos\Config')) {
            $this->host = \Cosmos\Config::getInstance()->getParam("ABRSEARCH")['API_HOST'] ? \Cosmos\Config::getInstance()->getParam("ABRSEARCH")['API_HOST'] : 'http://185.103.132.28:81/api';
            $this->indexName =\Cosmos\Config::getInstance()->getParam("ABRSEARCH")['INDEX_NAME'] ? \Cosmos\Config::getInstance()->getParam("ABRSEARCH")['INDEX_NAME'] : 'b_search_content_text';
        } else {
            $this->host = Option::get('abr.search', 'API_HOST', 'http://185.103.132.28:81/api');
            $this->indexName = Option::get('abr.search', 'INDEX_NAME', 'b_search_content_text');
        }
        
        $this->baseUrl = rtrim($this->host, '/') . '/v1/index/' . $this->indexName . '/';
        $this->token = Option::get('abr.search', 'TOKEN');
        $this->client = $this->getClient();
    }

    public function connect($connectionIndex, $indexName = "", $ignoreErrors = false)
    {
        global $APPLICATION;
    }

    public function truncate()
    {
        $this->curlSendDataPost($this->baseUrl . 'delete');
        $this->curlSendDataPost($this->baseUrl . 'create');
    }

    public function deleteById($ID = null)
    {
        try {
            if (!empty($ID)) {
                $this->curlSendDataPost($this->baseUrl . 'delete/' . $ID);
            }
        } catch (Exception|\OpenSearch\Common\Exceptions\RuntimeException $exception) {
        }
    }


    public function replace($ID, $arFields)
    {
        global $DB;
        if (array_key_exists("~DATE_CHANGE", $arFields)) {
            $arFields["DATE_CHANGE"] = $arFields["~DATE_CHANGE"];
            unset($arFields["~DATE_CHANGE"]);
        } elseif (array_key_exists("LAST_MODIFIED", $arFields)) {
            $arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
            unset($arFields["LAST_MODIFIED"]);
        } elseif (array_key_exists("DATE_CHANGE", $arFields)) {
            $arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS", CLang::GetDateFormat());
        }

        if (empty($arFields['ITEM_ID']) && empty($arFields['MODULE_ID'])) {
            $rsSearch = $DB->Query("SELECT ID, ITEM_ID, MODULE_ID FROM b_search_content WHERE ID = " . $ID, false);

            if ($rsSearch->Fetch()) {
                $row = $rsSearch->Fetch();
                $arFields['ITEM_ID'] = $row['ID'];
                $arFields['MODULE_ID'] = $row['MODULE_ID'];
            }
        }

        $DATE_FROM = intval(MakeTimeStamp($arFields["DATE_FROM"]));
        if ($DATE_FROM > 0)
            $DATE_FROM -= CTimeZone::GetOffset();
        $DATE_TO = intval(MakeTimeStamp($arFields["DATE_TO"]));
        if ($DATE_TO > 0)
            $DATE_TO -= CTimeZone::GetOffset();
        $DATE_CHANGE = intval(MakeTimeStamp($arFields["DATE_CHANGE"]));
        if ($DATE_CHANGE > 0)
            $DATE_CHANGE -= CTimeZone::GetOffset();

        $BODY = CSearch::KillEntities($arFields["BODY"]) . "\r\n" . $arFields["TAGS"];

        $aBody = [
            'id' => $ID,
            'module_id' => intval(sprintf("%u", crc32($arFields["MODULE_ID"]))),
            'module' => $this->Escape($arFields["MODULE_ID"]),
            'url' => ($arFields["URL"]),
            'item_id' => intval(sprintf("%u", crc32($arFields["ITEM_ID"]))),
            'item' => $this->Escape($arFields["ITEM_ID"]),
            'param1_id' => intval(sprintf("%u", crc32($arFields["PARAM1"]))),
            'param1' => $this->Escape($arFields["PARAM1"]),
            'param2_id' => intval(sprintf("%u", crc32($arFields["PARAM2"]))),
            'param2' => $this->Escape($arFields["PARAM2"]),

            'date_change' => $DATE_CHANGE,
            'date_from' => $DATE_FROM,
            'date_to' => $DATE_TO,
            'custom_rank' => intval($arFields["CUSTOM_RANK"]),
            'tags' => $this->tags($arFields["SITE_ID"], $arFields["TAGS"]),
            'right' => intval($this->rights($arFields["PERMISSIONS"])),
            'site' => intval($this->sites($arFields["SITE_ID"])),
            'param' => $this->params($arFields["PARAMS"]),
            'title' => $this->recodeTo(($arFields["TITLE"])),
            'body' => $this->recodeTo(($BODY)),
        ];

        if ($arFields['MODULE_ID'] == 'iblock') {
            $this->addIblockEnrichment($aBody, $arFields);
        }

        $response = $this->curlSendDataPost($this->baseUrl . 'index', [
            'body' => $aBody
        ]);
    }

    public function replaceAll($arItems)
    {
        $aData = [];

        if(isAssociativeArray($arItems)) $arItems = [$arItems];

        foreach ($arItems as $arFields) {
            global $DB;
            if(!is_array($arFields)) continue;
            if (array_key_exists("~DATE_CHANGE", $arFields)) {
                $arFields["DATE_CHANGE"] = $arFields["~DATE_CHANGE"];
                unset($arFields["~DATE_CHANGE"]);
            } elseif (array_key_exists("LAST_MODIFIED", $arFields)) {
                $arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
                unset($arFields["LAST_MODIFIED"]);
            } elseif (array_key_exists("DATE_CHANGE", $arFields)) {
                $arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS", CLang::GetDateFormat());
            }

            $DATE_FROM = intval(MakeTimeStamp($arFields["DATE_FROM"]));
            if ($DATE_FROM > 0)
                $DATE_FROM -= CTimeZone::GetOffset();
            $DATE_TO = intval(MakeTimeStamp($arFields["DATE_TO"]));
            if ($DATE_TO > 0)
                $DATE_TO -= CTimeZone::GetOffset();
            $DATE_CHANGE = intval(MakeTimeStamp($arFields["DATE_CHANGE"]));
            if ($DATE_CHANGE > 0)
                $DATE_CHANGE -= CTimeZone::GetOffset();

            $BODY = CSearch::KillEntities($arFields["BODY"]) . "\r\n" . $arFields["TAGS"];
            if (empty($arFields['ID'])) continue;
            $aBody = [
                'id' => $arFields['ID'],
                'module_id' => intval(sprintf("%u", crc32($arFields["MODULE_ID"]))),
                'module' => $this->Escape($arFields["MODULE_ID"]),
                'url' => ($arFields["URL"]),
                'item_id' => intval(sprintf("%u", crc32($arFields["ITEM_ID"]))),
                'item' => $this->Escape($arFields["ITEM_ID"]),
                'param1_id' => intval(sprintf("%u", crc32($arFields["PARAM1"]))),
                'param1' => $this->Escape($arFields["PARAM1"]),
                'param2_id' => intval(sprintf("%u", crc32($arFields["PARAM2"]))),
                'param2' => $this->Escape($arFields["PARAM2"]),

                'date_change' => $DATE_CHANGE,
                'date_from' => $DATE_FROM,
                'date_to' => $DATE_TO,
                'custom_rank' => intval($arFields["CUSTOM_RANK"]),
                'tags' => $this->tags($arFields["SITE_ID"], $arFields["TAGS"]),
                'right' => intval($this->rights($arFields["PERMISSIONS"])),
                'site' => intval($this->sites($arFields["SITE_ID"])),
                'param' => $this->params($arFields["PARAMS"]),
                'title' => $this->recodeTo(($arFields["TITLE"])),
                'body' => $this->recodeTo(($BODY)),
            ];

            if ($arFields['MODULE_ID'] == 'iblock') {
                $this->addIblockEnrichment($aBody, $arFields);
            }

            $aData[] = $aBody;
        }

        if (empty($aData)) return;
        $startTimee = microtime(true);
        $response = $this->curlSendDataPost($this->baseUrl . 'index', [
            'body' => $aData
        ]);
    }

    public function update($ID, $arFields)
    {
        $this->replace($ID, $arFields);
    }

    public function search($arParams, $aSort, $aParamsEx, $bTagsCloud)
    {

        if (!$this->client) return [];
        $this->errorText = "";
        $this->errorNumber = 0;

        $this->tags = trim($arParams["TAGS"]);

        if (is_array($aParamsEx) && isset($aParamsEx["LIMIT"])) {
            $limit = intval($aParamsEx["LIMIT"]);
            unset($aParamsEx["LIMIT"]);
        }

        $offset = 0;
        if (is_array($aParamsEx) && isset($aParamsEx["OFFSET"])) {
            $offset = intval($aParamsEx["OFFSET"]);
            unset($aParamsEx["OFFSET"]);
        }

        if (is_array($aParamsEx) && !empty($aParamsEx)) {
            $aParamsEx["LOGIC"] = "OR";
            $arParams[] = $aParamsEx;
        }

        $this->SITE_ID = $arParams["SITE_ID"];

        $arParams['QUERY'] = trim($arParams['QUERY']);

        if (empty($arParams['QUERY'])) {
            return [];
        }

        $filter[0][0] = $aParamsEx[0] ?? [];
        if(!empty($filter[0]) && !empty($aParamsEx['LOGIC'])) {
            $filter[0]['LOGIC'] = $aParamsEx['LOGIC'];
        }
        $response = $this->queryToElasticFull($arParams['QUERY'], $offset, $limit ?: 100, $filter);
        $aResult = [];
        global $DB;

        if (!empty($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                $data = [
                    'ID' => $hit['_source']['id'],
                    'DATE_CHANGE' => date('Y-m-d H:i:s', $hit['_source']['date_change']),
                    'MODULE_ID' => $hit['_source']['module'],
                    'ITEM_ID' => $hit['_source']['item'],
                    'CUSTOM_RANK' => $hit['_score'],
                    'RANK' => $hit['_score'],
                    'URL' => $hit['_source']['url'],
                    'TITLE' => $hit['_source']['title'],
                    'BODY' => $hit['_source']['body'],
                    'TAGS' => $hit['_source']['tags'],
                    'PARAM1' => $hit['_source']['param1'],
                    'PARAM2' => $hit['_source']['param2'],
                ];

                if (isset($hit['_source']['param3'])) {
                    $data['PARAM3'] = $hit['_source']['param3'];
                }
                if (isset($hit['_source']['categories'])) {
                    $data['CATEGORIES'] = $hit['_source']['categories'];
                }
                if (isset($hit['_source']['categories_full'])) {
                    $data['CATEGORIES_FULL'] = $hit['_source']['categories_full'];
                }
                if (isset($hit['_source']['image'])) {
                    $data['IMAGE'] = $hit['_source']['image'];
                }
                if (isset($hit['_source']['price'])) {
                    $data['PRICE'] = $hit['_source']['price'];
                }
                if (isset($hit['_source']['old_price'])) {
                    $data['OLD_PRICE'] = $hit['_source']['old_price'];
                }
                if (isset($hit['_source']['labels'])) {
                    $data['LABELS'] = $hit['_source']['labels'];
                }
                if (isset($hit['_source']['quantity'])) {
                    $data['QUANTITY'] = $hit['_source']['quantity'];
                }

                $aResult[] = $data;
            }
        }

        return $aResult;
    }

    function searchTitle($phrase = "", $arPhrase = array(), $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
    {
        if (!$this->client) return [];
        $result = array();
        $this->errorText = "";
        $this->errorNumber = 0;
        $this->tags = trim($arParams["TAGS"]);
        $this->SITE_ID = $arParams["SITE_ID"];
        $queryPhrase = $phrase;

        $response = $this->curlSendDataPost($this->baseUrl . 'search', [
            'body' => [
                'query' => $queryPhrase,
                'fields' => ['title'],
            ],
            'filter' => $this->prepareFilter($arParams),
            'limit' => $nTopCount,
        ]);
        global $DB;
        $ids = [];
        if (!empty($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                $ids[] = $hit['_source']['id'];
            }
        }

        if (!empty($arParams['IBLOCK_ID'])) {

            $data = CIBlockElement::GetList([], ['IBLOCK_ID' => $arParams['IBLOCK_ID'], '=ID' => $ids], false, false, ['ID']);
            $filteredIds = [];
            while ($row = $data->Fetch()) {
                $filteredIds[] = $row['ID'];
            }

            $ids = $filteredIds;
        }
        return $ids;
    }

    public function getErrorText()
    {
        return "";
    }

    public function getErrorNumber()
    {
        return 0;
    }

    function getRowFormatter()
    {
        return null;
    }

    protected function getClient()
    {
        try {
            $this->baseUrl = rtrim($this->host, '/') . '/v1/index/' . $this->indexName . '/';
            $this->headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ];
            $client = new HttpClient([
                'waitResponse' => true
            ]);
//            $client->setHeaders($headers);

            return $client;
        } catch (Exception $exception) {
            return null;
        }
    }

    public function Escape($str)
    {
        static $search = array(
            "\\",
            "'",
            "/",
            ")",
            "(",
            "$",
            "~",
            "!",
            "@",
            "^",
            "-",
            "|",
            "<",
            "\x0",
            "=",
        );
        static $replace = array(
            "\\\\",
            "\\'",
            "\\\\/",
            "\\\\)",
            "\\\\(",
            "\\\\\$",
            "\\\\~",
            "\\\\!",
            "\\\\@",
            "\\\\^",
            "\\\\-",
            "\\\\|",
            "\\\\<",
            " ",
            " ",
        );

        $str = str_replace($search, $replace, $str);

        $stat = count_chars($str, 1);
        if (isset($stat[ord('"')]) && $stat[ord('"')] % 2 === 1)
            $str = str_replace('"', '\\\"', $str);

        return $str;
    }


    function tags($arLID, $sContent)
    {
        $tags = array();
        if (is_array($arLID)) {
            foreach ($arLID as $site_id => $url) {
                $arTags = tags_prepare($sContent, $site_id);
                foreach ($arTags as $tag) {
                    $tags[] = sprintf("%u", crc32($tag));
                }
            }
        }
        return implode(",", $tags);
    }

    function rights($arRights)
    {
        $rights = array();
        if (is_array($arRights)) {
            foreach ($arRights as $group_id) {
                if (is_numeric($group_id))
                    $rights[$group_id] = sprintf("%u", crc32("G" . intval($group_id)));
                else
                    $rights[$group_id] = sprintf("%u", crc32($group_id));
            }
        }
        return implode(",", $rights);
    }

    function sites($arSites)
    {
        $sites = array();
        if (is_array($arSites)) {
            foreach ($arSites as $site_id => $url) {
                $sites[$site_id] = sprintf("%u", crc32($site_id));
            }
        } else {
            $sites[$arSites] = sprintf("%u", crc32($arSites));
        }
        return implode(",", $sites);
    }

    function params($arParams)
    {
        $params = array();
        if (is_array($arParams)) {
            foreach ($arParams as $k1 => $v1) {
                $name = trim($k1);
                if ($name != "") {
                    if (!is_array($v1))
                        $v1 = array($v1);

                    foreach ($v1 as $v2) {
                        $value = trim($v2);
                        if ($value != "") {
                            $params[] = sprintf("%u", crc32(urlencode($name) . "=" . urlencode($value)));
                        }
                    }
                }
            }
        }
        return implode(",", $params);
    }

    private function extractFirstSiteId($siteField): ?string
    {
        if (is_array($siteField)) {
            $keys = array_keys($siteField);
            return $keys ? (string)$keys[0] : null;
        }

        $siteId = (string)$siteField;
        return $siteId !== '' ? $siteId : null;
    }

    private function getServerNameBySiteId($siteField): string
    {
        $siteId = $this->extractFirstSiteId($siteField);
        $serverName = '';

        if ($siteId) {
            $site = \CSite::GetByID($siteId)->Fetch();
            if (is_array($site) && !empty($site['SERVER_NAME'])) {
                $serverName = (string)$site['SERVER_NAME'];
            }
        }

        if ($serverName === '') {
            $serverName = (string)Option::get('main', 'server_name', '');
        }

        return $serverName;
    }

    private function makeAbsoluteUrl(string $url, $siteField = null): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }

        $serverName = trim($this->getServerNameBySiteId($siteField));
        if ($serverName === '') {
            return $url;
        }

        $serverName = rtrim($serverName, '/');
        if (!preg_match('~^https?://~i', $serverName)) {
            $serverName = 'https://' . $serverName;
        }

        if ($url[0] !== '/') {
            $url = '/' . $url;
        }

        return $serverName . $url;
    }

    private function getPreferredOfferId(int $productId, int $productIblockId): ?int
    {
        if ($productId <= 0 || $productIblockId <= 0) {
            return null;
        }

        if (!Loader::includeModule('catalog')) {
            return null;
        }

        $skuInfo = \CCatalogSKU::GetInfoByProductIBlock($productIblockId);
        if (empty($skuInfo['IBLOCK_ID']) || empty($skuInfo['SKU_PROPERTY_ID'])) {
            return null;
        }

        $offerIblockId = (int)$skuInfo['IBLOCK_ID'];
        $skuPropertyId = (int)$skuInfo['SKU_PROPERTY_ID'];
        if ($offerIblockId <= 0 || $skuPropertyId <= 0) {
            return null;
        }

        $rs = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $offerIblockId,
                'ACTIVE' => 'Y',
                'PROPERTY_' . $skuPropertyId => $productId,
            ],
            false,
            ['nTopCount' => 50],
            ['ID', 'PROPERTY_BASIC_CONFIGURATION']
        );

        $firstId = null;
        while ($row = $rs->Fetch()) {
            $id = (int)($row['ID'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            if ($firstId === null) {
                $firstId = $id;
            }
            if ((string)($row['PROPERTY_BASIC_CONFIGURATION_VALUE'] ?? '') === 'Да') {
                return $id;
            }
        }

        return $firstId;
    }

    private function getQuantityText(int $productId, int $productIblockId): ?string
    {
        if (!class_exists('\\E_1\\Quantities')) {
            $path = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/') . '/local/php_interface/include/lib/E_1/Quantities.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (!class_exists('\\E_1\\Quantities')) {
            return null;
        }

        $offerId = $this->getPreferredOfferId($productId, $productIblockId);
        $targetId = $offerId ?: $productId;

        try {
            $data = \E_1\Quantities::GetProductQuantity($targetId);
            $text = is_array($data) ? (string)($data['TEXT'] ?? '') : '';
            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getElementPropertyAggregated(int $iblockId, int $elementId, string $code): ?array
    {
        if ($iblockId <= 0 || $elementId <= 0 || $code === '') {
            return null;
        }

        $rs = \CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc'], ['CODE' => $code]);
        $base = null;
        $values = [];
        $xmlIds = [];

        while ($row = $rs->Fetch()) {
            if ($base === null) {
                $base = $row;
            }
            $displayValue = $row['VALUE_ENUM'] ?? null;
            if ($displayValue === null || $displayValue === '') {
                $displayValue = $row['VALUE'];
            }
            if ($displayValue !== null && $displayValue !== '') {
                $values[] = $displayValue;
                $xmlIds[] = $row['VALUE_XML_ID'];
            }
        }

        if ($base === null) {
            return null;
        }

        if (count($values) > 1) {
            $base['VALUE'] = $values;
            $base['VALUE_XML_ID'] = $xmlIds;
        } elseif (count($values) === 1) {
            $base['VALUE'] = $values[0];
            $base['VALUE_XML_ID'] = $xmlIds[0];
        }

        return $base;
    }

    private function getCatalogPrices(int $productId, int $productIblockId): array
    {
        $result = [
            'price' => 0,
            'old_price' => 0,
        ];

        if ($productId <= 0 || $productIblockId <= 0) {
            return $result;
        }

        if (!Loader::includeModule('catalog')) {
            return $result;
        }

        global $USER;
        $groups = (is_object($USER) ? (array)$USER->GetUserGroupArray() : [2]);

        $targetId = $this->getPreferredOfferId($productId, $productIblockId) ?: $productId;
        $priceData = \CCatalogProduct::GetOptimalPrice($targetId, 1, $groups, 'N');
        if (!is_array($priceData) || empty($priceData['RESULT_PRICE'])) {
            return $result;
        }

        $base = (float)($priceData['RESULT_PRICE']['BASE_PRICE'] ?? 0);
        $discount = (float)($priceData['RESULT_PRICE']['DISCOUNT_PRICE'] ?? 0);
        if ($base <= 0 && $discount > 0) {
            $base = $discount;
        }

        $decorDiscount = 0.0;
        $offDecor = $this->getElementPropertyAggregated($productIblockId, $productId, 'OFF_DECOR_DISCOUNT');
        if (!is_array($offDecor) || (string)($offDecor['VALUE'] ?? '') !== 'Y') {
            $percent = $this->getElementPropertyAggregated($productIblockId, $productId, 'PROTSENT_SKIDKI');
            if (is_array($percent) && (string)($percent['VALUE'] ?? '') !== '') {
                $decorDiscount = (float)$percent['VALUE'];
            }
        }

        $realPercent = 0.0;
        if ($base > 0 && $discount > 0 && $base > $discount) {
            $realPercent = (($base - $discount) / $base) * 100.0;
        }

        $totalPercent = $realPercent + $decorDiscount;
        if ($totalPercent < 0) {
            $totalPercent = 0;
        }

        $old = $base;
        if ($decorDiscount > 0 && $discount > 0 && $totalPercent < 100) {
            $old = round($discount / (1 - $totalPercent / 100));
        }

        $result['price'] = (int)round($discount);
        $result['old_price'] = (int)round($old);

        return $result;
    }

    private function stickerClassToColor(string $class): string
    {
        $class = strtolower($class);
        if (strpos($class, 'sale_text') !== false) {
            return 'red';
        }
        if (strpos($class, 'stock') !== false || strpos($class, 'aktsiya') !== false) {
            return 'yellow';
        }
        if (strpos($class, 'recommend') !== false || strpos($class, 'sovetuem') !== false) {
            return 'purple';
        }
        if (strpos($class, 'hit') !== false || strpos($class, 'khit') !== false) {
            return 'blue';
        }
        return 'blue';
    }

    private function buildLabels(int $iblockId, int $elementId, array $prices): array
    {
        $labels = [];

        $saleText = $this->getElementPropertyAggregated($iblockId, $elementId, 'SALE_TEXT');
        if (is_array($saleText) && (string)($saleText['VALUE'] ?? '') !== '') {
            $labels[] = ['color' => 'red', 'name' => (string)$saleText['VALUE']];
        } elseif (!empty($prices['price']) && !empty($prices['old_price']) && (int)$prices['old_price'] > (int)$prices['price']) {
            $old = (float)$prices['old_price'];
            $price = (float)$prices['price'];
            $percent = ($old > 0 && $old > $price) ? (int)round((($old - $price) / $old) * 100) : 0;
            if ($percent > 0) {
                $labels[] = ['color' => 'red', 'name' => '-' . $percent . '%'];
            }
        }

        $fav = $this->getElementPropertyAggregated($iblockId, $elementId, 'FAVORIT_ITEM');
        if (is_array($fav) && !empty($fav['VALUE'])) {
            $labels[] = ['color' => 'red', 'name' => (string)($fav['NAME'] ?? '')];
        }

        $finalPrice = $this->getElementPropertyAggregated($iblockId, $elementId, 'FINAL_PRICE');
        if (is_array($finalPrice) && !empty($finalPrice['VALUE'])) {
            $labels[] = ['color' => 'red', 'name' => (string)($finalPrice['NAME'] ?? '')];
        }

        if (class_exists('\\CMax')) {
            $hit = $this->getElementPropertyAggregated($iblockId, $elementId, 'HIT');
            if (is_array($hit)) {
                foreach (\CMax::GetItemStickers($hit) as $sticker) {
                    $value = (string)($sticker['VALUE'] ?? '');
                    if ($value === '') {
                        continue;
                    }
                    $labels[] = [
                        'color' => $this->stickerClassToColor((string)($sticker['CLASS'] ?? '')),
                        'name' => $value,
                    ];
                }
            }
        }

        $uniq = [];
        foreach ($labels as $label) {
            $name = (string)($label['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $uniq[$name] = ['color' => (string)($label['color'] ?? 'blue'), 'name' => $name];
        }

        return array_values($uniq);
    }

    private function buildCategories(int $iblockId, int $elementId, $siteField = null): array
    {
        $result = [
            'categories' => '',
            'categories_full' => [],
        ];

        $sectionsById = [];
        $groups = (new \CIBlockElement())->GetElementGroups($elementId);
        while ($group = $groups->Fetch()) {
            $sectionId = (int)($group['ID'] ?? 0);
            if ($sectionId <= 0) {
                continue;
            }
            $chain = \CIBlockSection::GetNavChain($iblockId, $sectionId, ['ID', 'NAME', 'SECTION_PAGE_URL', 'PICTURE']);
            while ($section = $chain->GetNext()) {
                $sid = (int)($section['ID'] ?? 0);
                if ($sid <= 0) {
                    continue;
                }
                if (!isset($sectionsById[$sid])) {
                    $sectionsById[$sid] = $section;
                }
            }
        }

        if (!$sectionsById) {
            return $result;
        }

        $names = [];
        foreach ($sectionsById as $sid => $section) {
            $name = (string)($section['NAME'] ?? '');
            if ($name === '') {
                continue;
            }
            $names[] = $name;

            $img = '';
            $pictureId = (int)($section['PICTURE'] ?? 0);
            if ($pictureId > 0) {
                $resizeType = defined('BX_RESIZE_IMAGE_EXACT') ? BX_RESIZE_IMAGE_EXACT : 2;
                $resized = \CFile::ResizeImageGet($pictureId, ['width' => 120, 'height' => 120], $resizeType, true);
                if (is_array($resized) && !empty($resized['src'])) {
                    $img = $this->makeAbsoluteUrl((string)$resized['src'], $siteField);
                }
            }

            $result['categories_full'][] = json_encode(
                [
                    'name' => $name,
                    'url' => (string)($section['SECTION_PAGE_URL'] ?? ''),
                    'img' => $img,
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        $result['categories'] = implode(', ', $names);
        return $result;
    }

    private function addIblockEnrichment(array &$aBody, array $arFields): void
    {
        if (!Loader::includeModule('iblock')) {
            return;
        }

        $elementId = (int)($arFields['ITEM_ID'] ?? 0);
        if ($elementId <= 0) {
            return;
        }

        $element = \CIBlockElement::GetByID($elementId)->Fetch();
        if (!is_array($element) || empty($element['IBLOCK_ID'])) {
            return;
        }

        $iblockId = (int)$element['IBLOCK_ID'];
        $aBody['param3_id'] = intval(sprintf("%u", crc32((string)$iblockId)));
        $aBody['param3'] = $this->Escape((string)$iblockId);

        $siteField = $arFields['SITE_ID'] ?? null;
        $categories = $this->buildCategories($iblockId, $elementId, $siteField);
        if (!empty($categories['categories'])) {
            $aBody['categories'] = $categories['categories'];
        }
        if (!empty($categories['categories_full'])) {
            $aBody['categories_full'] = $categories['categories_full'];
        }

        $imgId = (int)($element['PREVIEW_PICTURE'] ?? 0);
        if ($imgId <= 0) {
            $imgId = (int)($element['DETAIL_PICTURE'] ?? 0);
        }
        $img = $imgId > 0 ? (string)\CFile::GetPath($imgId) : '';
        if ($img !== '') {
            $aBody['image'] = $this->makeAbsoluteUrl($img, $siteField);
        }

        $prices = $this->getCatalogPrices($elementId, $iblockId);
        $aBody['price'] = (int)($prices['price'] ?? 0);
        $aBody['old_price'] = (int)($prices['old_price'] ?? 0);

        $labels = $this->buildLabels($iblockId, $elementId, $prices);
        if ($labels) {
            $aBody['labels'] = $labels;
        }

        $quantity = $this->getQuantityText($elementId, $iblockId);
        if ($quantity !== null) {
            $aBody['quantity'] = $quantity;
        }
    }

    public function recodeTo($text)
    {
        if ($this->recodeToUtf) {
            $error = "";
            $result = \Bitrix\Main\Text\Encoding::convertEncoding($text, SITE_CHARSET, "UTF-8", $error);
            if (!$result && !empty($error))
                #$this->ThrowException($error, "ERR_CHAR_BX_CONVERT");
                return $text;

            return $result;
        } else {
            return $text;
        }
    }

    private function queryToElasticFull($queryPhrase, $offset, $limit, $filter)
    {
        return $this->curlSendDataPost($this->baseUrl . 'search', [
            'body' => [
                'query' => $queryPhrase,
                'fields' => ['title^5', 'body'],
            ],
            'limit' => $limit,
            'filter' => $this->prepareFilter($filter),
        ]);
    }

    public function curlSendDataPost($url, $data = [])
    {
        $client = new Client([
            'headers' => $this->headers
        ]);
        $response = $client->post($url, [
            GuzzleHttp\RequestOptions::JSON => $data
        ]);

        if($response->getStatusCode() === 200){
            return json_decode($response->getBody()->getContents(), true);
        } else {
            throw new \Exception("Error: " . $response->getBody()->getContents());
        }
    }

    protected function prepareFilter(array $arParams): array
    {
        $outputArray = [
            "bool" => []
        ];

        $replaces = [
            'PARAM1' => 'param1',
            'PARAM2' => 'param2',
            'MODULE_ID' => 'module',
        ];

        $logic = isset($arParams[0]['LOGIC']) ? strtolower($arParams[0]['LOGIC']) : 'or';
        unset($arParams[0]['LOGIC']); // Удаляем ключ "LOGIC", чтобы не мешал в дальнейшем

        if ($logic === 'or') {
            $outputArray["bool"]["should"] = [];
        } else {
            $outputArray["bool"]["must"] = [];
        }

        foreach ($arParams[0] as $conditions) {
            $mustArray = [];
            foreach ($conditions as $field => $value) {
                // Убираем знак "=" перед названием поля
                $cleanField = ltrim($field, '=');
                if(!empty($replaces[$cleanField])) {
                    $cleanField = $replaces[$cleanField];
                    if(is_array($value)) $value = current($value);
                    $mustArray[] = ["term" => [$cleanField => $value]];
                }
            }

            if ($logic === 'or') {
                $outputArray["bool"]["should"][] = ["bool" => ["must" => $mustArray]];
            } else {
                $outputArray["bool"]["must"][] = ["bool" => ["must" => $mustArray]];
            }
        }

        if(empty($outputArray['bool'])) $outputArray = [];

        return $outputArray;
    }
}

if (!function_exists('mb_str_split')) {
    function mb_str_split($string = '', $length = 1, $encoding = null)
    {
        if (!empty($string)) {
            $split = array();
            $mb_strlen = mb_strlen($string, $encoding);
            for ($pi = 0; $pi < $mb_strlen; $pi += $length) {
                $substr = mb_substr($string, $pi, $length, $encoding);
                if (!empty($substr)) {
                    $split[] = $substr;
                }
            }
        }
        return $split;
    }
}

if(!function_exists('isAssociativeArray')){
    function isAssociativeArray(array $array) {
        // Проверяем, является ли массив пустым
        if (empty($array)) {
            return false;
        }

        // Перебираем ключи массива
        foreach (array_keys($array) as $key) {
            // Если хотя бы один ключ не является целым числом, массив ассоциативный
            if (!is_int($key)) {
                return true;
            }
        }

        // Если все ключи являются целыми числами, массив не ассоциативный
        return false;
    }
}