<?php

namespace E_1;

/**
 * Интерфейс для пережатия картинок, в том числе массового, по заранее определенным типам
 *
 * работа осуществляется путем добавления именованных "типов пережатия" с последующим применением
 * этих типов пережатия над одним или многими элементами инфоблока
 * PREVIEW_POPUP_BIG
 *  $image = Resizer::resizeImageFile($path, 'PRODUCT_DETAIL_BIG', UPLOAD_PRODUCTS);
 * <?= \Sibirix\Base\Resizer::resizeImage($arResult["DETAIL_PICTURE"], 'NEWS_DETAIL'); ?>
 * <?= Resizer::resizeImage($article['PREVIEW_PICTURE']['ID'], 'SECTION_ARTICLES') ?>
 */
class Resizer {

    const ORDER_BASKET_PRODUCT     = 'ORDER_BASKET_PRODUCT';
    const CATALOG_ELEMENT_SMALL    = 'CATALOG_ELEMENT_SMALL';

    private static $imageTypes = [
        'MAIN_SLIDER_POINT' => [
            'width'  => 220,
            'height' => 220,
            'type'   => BX_RESIZE_IMAGE_PROPORTIONAL,
        ],

        'MAIN_ARTICLES_BIG' => [
            'width'   => 700,
            'height'  => 290,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'MAIN_ARTICLES_SMALL' => [
            'width'   => 220,
            'height'  => 145,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'MAIN_REVIEWS' => [
            'width'   => 140,
            'height'  => 134,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'MAIN_POPULAR' => [
            'width'   => 425,
            'height'  => 242,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'MAIN_BANNERS' => [
            'width'   => 280,
            'height'  => 145,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'MAIN_COLLECTIONS' => [
            'width'   => 280,
            'height'  => 210,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'MAIN_TASTY' => [
            'width'   => 280,
            'height'  => 210,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'NEWS_LIST_BIG' => [
            'width'   => 700,
            'height'  => 525,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'NEWS_LIST' => [
            'width'   => 340,
            'height'  => 255,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'NEWS_DETAIL' => [
            'width'   => 460,
            'height'  => 345,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'COMPANY_CERTIFICATE' => [
            'width'   => 205,
            'height'  => 205,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],

        'POPUP_DEALERS_BALLOON' => [
            'width'   => 76,
            'height'  => 77,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'POPUP_DEALERS_SMALL' => [
            'width'   => 121,
            'height'  => 74,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'POPUP_DEALERS_BIG' => [
            'width'   => 590,
            'height'  => 443,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'POPUP_SHOP_REVIEW' => [
            'width'   => 592,
            'height'  => 248,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'PRODUCT_LIST' => [
            'width'   => 275,
            'height'  => 206,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'PRODUCT_LIST_BIG' => [
            'width'   => 445,
            'height'  => 335,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'PRODUCT_LIST_XS' => [
            'width'   => 400,
            'height'  => 300,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'SECTION_ALL' => [
            'width'   => 275,
            'height'  => 206,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'PRODUCT_DETAIL_BIG' => [
            'width'   => 841,
            'height'  => 631,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'PRODUCT_DETAIL_THUMB' => [
            'width'   => 149,
            'height'  => 112,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'PRODUCT_DETAIL_COMPLECTED' => [
            'width'   => 179,
            'height'  => 134,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'PRODUCT_DETAIL_SIZES' => [
            'width'   => 300,
            'height'  => 300,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'PRODUCT_DETAIL_FEATURE' => [
            'width'   => 220,
            'height'  => 165,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'PRODUCTS_LIST_FEATURE' => [
            'width'   => 285,
            'height'  => 215,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'ALL_PRODUCT_MENU_THUMBNAIL' => [
            'width'   => 212,
            'height'  => 134,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],

        'BASKET_LIST' => [
            'width'   => 400,
            'height'  => 300,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],

        'PRODUCT_DETAIL_REVIEW' => [
            'width'   => 140,
            'height'  => 105,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],

        'PROFILE_AVATAR' => [
            'width'   => 135,
            'height'  => 140,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],

        'MAIN_TASTY_XS' => [
            'width'   => 290,
            'height'  => 195,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],

        'POPUP_GIFT' => [
            'width'   => 148,
            'height'  => 98,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],

        'DEALER_ORDERS_SET_PRODUCT' => [
            'width'   => 110,
            'height'  => 80,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],

        'DEALER_ORDERS_PRODUCT' => [
            'width'   => 220,
            'height'  => 118,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],

        'SERIES_DETAIL_SLIDER' => [
            'width'   => 960,
            'height'  => 720,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'SERIES_DETAIL_PAGINATOR' => [
            'width'   => 146,
            'height'  => 89,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'SERIES_FEATURE_IMG' => [
            'width'   => 240,
            'height'  => 180,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'SERIES_SETS_IMG' => [
            'width'   => 275,
            'height'  => 200,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'PREVIEW_POPUP_THUMB' => array(
            'width'   => 90,
            'height'  => 90,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ),

        'PREVIEW_POPUP_BIG' => array(
            'width'   => 640,
            'height'  => 480,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ),

        self::ORDER_BASKET_PRODUCT => [
            'width'   => 120,
            'height'  => 80,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],

        self::CATALOG_ELEMENT_SMALL => [
            'width'   => 178,
            'height'  => 134,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
        'SECTION_SLIDER_MAIN' => [
            'width'   => 520,
            'height'  => 390,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'SECTION_SLIDER_THUMB' => [
            'width'   => 96,
            'height'  => 72,
            'type'    => BX_RESIZE_IMAGE_EXACT
        ],
        'SECTION_ARTICLES' => [
            'width'   => 435,
            'height'  => 300,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL_ALT
        ],

        'CATALOG_ADV_CARD' => [
            'width'   => 300,
            'height'  => 600,
            'type'    => BX_RESIZE_IMAGE_PROPORTIONAL
        ],
    ];

    /**
     * Проверка что нужный тип пережатия существует
     * @param $name
     * @return bool
     */
    static function imageTypeExists($name) {
        return array_key_exists($name, self::$imageTypes);
    }

    /**
     * Добавление нового ТИПА ПЕРЕЖАТИЯ
     *
     * @param $name string название ТИПА ПЕРЕЖАТИЯ
     * @param $width int ширина пережатого изображения
     * @param $height int высота пережатого изображения
     * @param $type string Код типа пережатия битрикса
     */
    static function addImageType($name, $width, $height, $type) {
        Resizer::$imageTypes[$name] = array(
            'width'   => $width,
            'height'  => $height,
            'type'    => $type
        );
    }

    /**
     * Масштабирует изображения из инфоблока по заранее определенного шаблону определенному через Resizer::addImageType
     *
     * @param $file
     * @param $typeName string имя ТИПА ПЕРЕЖАТИЯ
     * @return string URL нового изображения
     */
    static function resizeImage($file, $typeName) {
        if (!self::imageTypeExists($typeName)) {
            die(sprintf('There is no resize type "%s"', $typeName));
        }

        $type = \E_1\Resizer::$imageTypes[$typeName];
        $cFile = new \CFile();
        $image = $cFile->ResizeImageGet(
            $file,
            array('width' => $type['width'], 'height' => $type['height']),
            $type['type']
        );

        if (empty($image['src'])) {
            return false;
        } else {
            $imgSrc = $image['src'];
        }

        return $imgSrc . '?' . static::filemtime($file);
    }

    /**
     * Масштабирует изображения из инфоблока по заранее определенного шаблону определенному через Resizer::addImageType
     *
     * @param $file
     * @param $typeName string имя ТИПА ПЕРЕЖАТИЯ
     * @return string URL нового изображения
     */
    static function resizeImageNotGif($file, $typeName) {
        if (!self::imageTypeExists($typeName)) {
            die(sprintf('There is no resize type "%s"', $typeName));
        }

        if (is_numeric($file)) {
            $file = Helper::getFileData($file);
        }

        $ext = mb_strtolower(pathinfo($file['SRC'], PATHINFO_EXTENSION));
        if ($ext == 'gif') return $file['SRC'] . '?' . static::filemtime($file['SRC']);

        $type = Resizer::$imageTypes[$typeName];
        $cFile = new \CFile();
        $image = $cFile->ResizeImageGet(
            $file,
            array('width' => $type['width'], 'height' => $type['height']),
            $type['type']
        );

        if (empty($image['src'])) {
            return false;
        } else {
            $imgSrc = $image['src'];
        }

        return $imgSrc . '?' . static::filemtime($file);
    }

    public static function convertPathCharset($path) {
        $fromCharset = 'UTF-8';
        if (isset($_SERVER['WINDIR']) || isset($_SERVER['windir'])) $fromCharset = 'cp1251';
        $res = iconv($fromCharset, 'UTF-8', $path);
        return $res ? $res : $path;
    }

    public static function filemtime($src) {
        if (is_numeric($src)) return '0';

        if (is_array($src) && isset($src['SRC'])) {
            $src = $src['SRC'];
        }

        $src = static::convertPathCharset($src);
        if (!file_exists($src)) $src = P_DR . $src;
        if (!file_exists($src)) return '0';
        return filemtime($src);
    }

    /**
     * @param string $filePathDirect
     * @param string $typeName
     * @return bool
     */
    public static function resizeImageFileDirect($filePathDirect, $typeName) {
        $filePathDirect = ltrim($filePathDirect, '/');

        list($_upload, $dir, $filePath) = explode("/", $filePathDirect, 3);
        if (0 == strlen($filePath) || 'upload' != $_upload) {
            return false;
        }

        return static::resizeImageFile('/' . $filePath, $typeName, $dir);
    }
        /**
     * @param $filePath
     * @param $typeName
     * @param string $dir
     * @return bool
     */
    public static function resizeImageFile($filePath, $typeName, $dir = UPLOAD_DEALERS) {
        if (!self::imageTypeExists($typeName)) {
            die(sprintf('There is no resize type "%s"', $typeName));
        }

        $type = Resizer::$imageTypes[$typeName];
        $filePath = ltrim($filePath, '/');
        $realpath = Helper::getRealPath(P_UPLOAD . '/' . $dir . '/' . $filePath);
        if (!file_exists($realpath)) return false;

        $sizes = getimagesize($realpath);
        $file = [
            'MODULE_ID' => 'hlblock',
            'WIDTH' => $sizes[0],
            'HEIGHT' => $sizes[1],
            'SUBDIR' => $dir,
            'FILE_NAME' => $filePath,
            'SRC' => $filePath,
        ];
        $cFile = new \CFile();
        $image = $cFile->ResizeImageGet(
            $file,
            array('width' => $type['width'], 'height' => $type['height']),
            $type['type']
        );

        if (empty($image['src'])) {
            return false;
        } else {
            $imgSrc = $image['src'];

            if ($imgSrc == $filePath) {
                $imgSrc = P_UPLOAD . '/' . $dir . '/' . $filePath;
            }
        }

        return str_replace(' ', '%20', $imgSrc . '?' . static::filemtime($realpath));
    }

    /**
     * Удалить ресайзы указанных файлов
     * @param $filePath
     * @param $typeName
     * @param string $dir
     */
    public static function clearResizeCache($filePath, $typeName, $dir = UPLOAD_DEALERS) {
        if (!is_array($typeName)) {
            $typeName = [$typeName];
        }

        foreach ($typeName as $type) {
            if (!self::imageTypeExists($type)) {
                die(sprintf('There is no resize type "%s"', $type));
            }
        }

        foreach ($typeName as $type) {
            $type = Resizer::$imageTypes[$type];
            $filePath = ltrim($filePath, '/');
            $fp = str_replace('upload/' . $dir . '/', '', $filePath);
            $cacheImageFile = P_UPLOAD . "resize_cache/" . $dir . "/" . $type["width"] . "_" . $type["height"] . "_" . $type['type'] . "/" . $fp;
            $realPath = Helper::getRealPath($cacheImageFile);
            if (file_exists($realPath)) {
                unlink($realPath);
            }
        }
    }

}
