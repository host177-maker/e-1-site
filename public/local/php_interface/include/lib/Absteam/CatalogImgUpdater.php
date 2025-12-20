<?php
namespace Absteam;

use CModule;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Exception\FtpClientException;
use Lazzard\FtpClient\FtpClient;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Класс для скачивания картинок товаро по FTP и последующей загрузки в элементы инфоблоков
 */
final class CatalogImgUpdater {
    private $ftpConnection;
    private $ftpClient;
    private $ftpWrapper;
    private $imgList;
    private $elementList;
    private $offersList;

    public function __construct(string $host, string $username, string $password) {
        try {
            if (!extension_loaded('ftp')) {
                throw new \RuntimeException("FTP extension not loaded.");
            }

            $this->ftpConnection = new FtpConnection($host, $username, $password);
            $this->ftpConnection->open();

            $this->ftpWrapper = new FtpWrapper($this->ftpConnection);

            $config = new FtpConfig($this->ftpConnection);
            $config->setWrapper($this->ftpWrapper);

            $this->ftpWrapper->set_option(FTP_USEPASVADDRESS, false);
            $config->setPassive(true);

            $this->ftpClient = new FtpClient($this->ftpConnection);

        } catch (\Throwable $ex) {
            file_put_contents(
                $_SERVER['DOCUMENT_ROOT'] . "/CatalogImgUpdater_" . date('d-m-Y') . ".log",
                "\n\n" . print_r([date('d-m-Y H:m'), $ex->getMessage()], true) . "\n",
                FILE_APPEND
            );
        }
    }

    /**
     * Удаляем все файлы из папки обновления картинок каталога
     * На всякий случай, имеет смысл принудительно закрывать соединение после окончания загрузки
     */
    public function finish($destinationDir) {
        // удаляем из дериктории предыдущие загрузки
        $includes = new \FilesystemIterator($destinationDir);

        foreach ($includes as $include) {

            if (is_dir($include) && !is_link($include)) {

                Helper::recursiveRemoveDir($include);
            }
        }

        // $this->ftpConnection->close();
    }

    public function dowloadImagesFromFTP(string $sourceDir, string $destinationDir) {
        // удаляем из дериктории предыдущие загрузки
        $includes = new \FilesystemIterator($destinationDir);

        foreach ($includes as $include) {

            if (is_dir($include) && !is_link($include)) {

                Helper::recursiveRemoveDir($include);
            }
        }
        // загружаем файлы из ftp-источника
        try {
            $dirs = $this->ftpClient->listDir($sourceDir, FtpClient::DIR_TYPE, false);
            if (empty($dirs))
                return false;
        } catch (\Throwable $th) {
            return false;
        }

        $arResult = [];
        foreach ($dirs as $key => $dir) {
            if ($dir !== '..')
                $arResult[str_replace('/', '', $dir)]['files'] = $this->ftpClient->listDir($sourceDir . DIRECTORY_SEPARATOR . $dir, FtpClient::FILE_TYPE, false);
        }

        foreach ($arResult as $dir => $files) {
            $this->elementList = $dir;

            foreach ($files['files'] as $file) {
                if (!file_exists($destinationDir . DIRECTORY_SEPARATOR . $dir)) {
                    mkdir($destinationDir . DIRECTORY_SEPARATOR . $dir, 0755);
                }

                // Используем Wrapper потому что FtpClient содержит ошибку в методе isExist, 
                // который используется в методе FtpClient::download()
                if (!$this->ftpWrapper->get(
                    $destinationDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file,
                    $sourceDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file,
                    FtpWrapper::BINARY, 0)
                ) {
                    throw new FtpClientException("Unable to retrieve the file [{$file}].");
                }
            }
        }
        return true;
    }

    public function updateCatalogImages(string $sourceDir) {
        $this->_buildTreeOfImages($sourceDir);
        $this->_findElementsForUpdate();
        $this->_updateElements();
    }

    private function _buildTreeOfImages(string $sourceDir) {
        $this->imgList = $this->_getDirContents($sourceDir);
    }

    private function _findElementsForUpdate() {
        foreach ($this->imgList as $key => $value) {
            $this->offersList[explode('.', $value->getFilename())[0]] = $value->getPathname();
        }
    }

    private function _updateElements() {
        CModule::IncludeModule("iblock");
        CModule::IncludeModule("catalog");
        CModule::IncludeModule("sale");

        if (empty($this->offersList) || !is_array($this->offersList)) {
            return;
        }

        $offersKeyList = array_keys($this->offersList);

        if (!empty($offersKeyList)) {
            $arFilter = array("IBLOCK_ID" => 49, 'XML_ID' => $offersKeyList);
            $rsOffer  = \CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'XML_ID', 'PROPERTY_BASIC_CONFIGURATION', 'PROPERTY_CML2_LINK']);
            while ($arOffer = $rsOffer->Fetch()) {
                $arOffers[$arOffer['XML_ID']] = $arOffer;
            }

            $el = new \CIBlockElement;
            foreach ($arOffers as $sXmlId => $arOffer) {
                $fileArray = \CFile::MakeFileArray($this->offersList[$sXmlId]);

                if (!empty($fileArray)) {
                    $fileArray['name']                  = str_replace('#', '_', $fileArray['name']);
                    $arFields['DETAIL_PICTURE']         = $fileArray;
                    $arFields['PREVIEW_PICTURE']        = $fileArray;
                    $arFields['DETAIL_PICTURE']["del"]  = "Y";
                    $arFields['PREVIEW_PICTURE']["del"] = "Y";

                    if ($el->Update($arOffer['ID'], $arFields)) {
                        if ($arOffer['PROPERTY_BASIC_CONFIGURATION_VALUE'] === 'Да') {
                            $el->Update(intval($arOffer['PROPERTY_CML2_LINK_VALUE']), $arFields);
                        }
                    }
                }
            }
        }
    }

    private function _getDirContents($sourceDir, &$results = []) {

        $includes = new \FilesystemIterator($sourceDir);

        foreach ($includes as $include) {

            if (is_dir($include) && !is_link($include)) {

                $this->_getDirContents($include, $results);
            } else {

                $results[] = $include;
            }
        }

        return $results;
    }

    public function removeImgesFromFTP($sourceDir) {
        $dirs = $this->ftpClient->listDir($sourceDir, FtpClient::DIR_TYPE, false);

        foreach ($dirs as $key => $dir) {
            $this->ftpClient->removeDir($dir);
        }
    }
}