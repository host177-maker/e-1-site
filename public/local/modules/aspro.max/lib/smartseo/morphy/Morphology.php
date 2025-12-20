<?php
namespace Aspro\Max\Smartseo\Morphy;

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

define('SMARTSEO_MORPHOLOGY_MASCULINUM', Loc::getMessage('SMARTSEO_MORPHOLOGY_MASCULINUM'));
define('SMARTSEO_MORPHOLOGY_FEMINUM', Loc::getMessage('SMARTSEO_MORPHOLOGY_FEMINUM'));
define('SMARTSEO_MORPHOLOGY_NEUTRUM', Loc::getMessage('SMARTSEO_MORPHOLOGY_NEUTRUM'));

define('SMARTSEO_MORPHOLOGY_NOUN', Loc::getMessage('SMARTSEO_MORPHOLOGY_NOUN'));
define('SMARTSEO_MORPHOLOGY_ADJ_FULL', Loc::getMessage('SMARTSEO_MORPHOLOGY_ADJ_FULL'));
define('SMARTSEO_MORPHOLOGY_ADJ_SHORT', Loc::getMessage('SMARTSEO_MORPHOLOGY_ADJ_SHORT'));
define('SMARTSEO_MORPHOLOGY_VERB', Loc::getMessage('SMARTSEO_MORPHOLOGY_VERB'));
define('SMARTSEO_MORPHOLOGY_PARTICIPLE', Loc::getMessage('SMARTSEO_MORPHOLOGY_PARTICIPLE'));
define('SMARTSEO_MORPHOLOGY_PARTICIPLE_SHORT', Loc::getMessage('SMARTSEO_MORPHOLOGY_PARTICIPLE_SHORT'));

define('SMARTSEO_MORPHOLOGY_SINGULAR', Loc::getMessage('SMARTSEO_MORPHOLOGY_SINGULAR'));
define('SMARTSEO_MORPHOLOGY_PLURAL', Loc::getMessage('SMARTSEO_MORPHOLOGY_PLURAL'));

define('SMARTSEO_MORPHOLOGY_NOMINATIV', Loc::getMessage('SMARTSEO_MORPHOLOGY_NOMINATIV'));
define('SMARTSEO_MORPHOLOGY_GENITIV', Loc::getMessage('SMARTSEO_MORPHOLOGY_GENITIV'));
define('SMARTSEO_MORPHOLOGY_DATIV', Loc::getMessage('SMARTSEO_MORPHOLOGY_DATIV'));
define('SMARTSEO_MORPHOLOGY_ACCUSATIV', Loc::getMessage('SMARTSEO_MORPHOLOGY_ACCUSATIV'));
define('SMARTSEO_MORPHOLOGY_INSTRUMENTALIS', Loc::getMessage('SMARTSEO_MORPHOLOGY_INSTRUMENTALIS'));
define('SMARTSEO_MORPHOLOGY_LOCATIV', Loc::getMessage('SMARTSEO_MORPHOLOGY_LOCATIV'));
define('SMARTSEO_MORPHOLOGY_VOCATIV', Loc::getMessage('SMARTSEO_MORPHOLOGY_VOCATIV'));
define('SMARTSEO_MORPHOLOGY_SECOND_CASE', Loc::getMessage('SMARTSEO_MORPHOLOGY_SECOND_CASE'));

class Morphology
{
    private static $instance;

    private $morphy = null;

    private $lang = 'ru_RU';

    private $words = [];

    function __construct()
    {
        require_once($this->getLibraryCommonFile());

        try {
            $this->morphy = new \phpMorphy($this->getDictionaryDir(), $this->getLang(), $this->getOptions());
        } catch (phpMorphy_Exception $e) {

        }
    }

    /**
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Morphology();
        }

        return self::$instance;
    }

    public function libInstance()
    {
        return $this->morphy;
    }

    public function castWord($word, $grammems)
    {
        if($this->morphy === null) {
            return $word;
        }

        $_word = mb_strtoupper($word);

        $gramInfo = $this->getGramInfo($_word);

        if($gramInfo['grammems']) {
            $grammems = $this->mergeGrammems($gramInfo['grammems'], $grammems);
        }

        if(!in_array($gramInfo['pos'], $this->getSpeechPartsGrammems())) {
            return $word;
        }

        $result = $this->libInstance()->castFormByGramInfo($_word, null, $grammems, true);

        if($result && is_array($result)) {
            return mb_strtolower($result[0]);
        } else {
            return $word;
        }
    }

    public function getGramInfo($word, $isFirst = true)
    {
        if($this->words[$word]) {
            return $this->words[$word];
        }

        if($this->morphy === null) {
            return $word;
        }

        $_word = mb_strtoupper($word);

        $result = $this->libInstance()->getGramInfo($_word);

        if(!$result) {
            return null;
        }

        if($isFirst) {
            $this->words[$_word] = $result[0][0];

            return $result[0][0];
        }

        $this->words[$_word] = $result;

        return $result;
    }

    public function getGenderByWord($word)
    {
        $result = $this->getGramInfo($word);

        if(in_array(SMARTSEO_MORPHOLOGY_MASCULINUM, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_MASCULINUM;
        }

        if(in_array(SMARTSEO_MORPHOLOGY_FEMINUM, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_FEMINUM;
        }

        if(in_array(SMARTSEO_MORPHOLOGY_NEUTRUM, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_NEUTRUM;
        }
    }

    public function isGender($grammem)
    {
        $grammem = mb_strtoupper($grammem);

        if(in_array($grammem, $this->getGenderGrammems())) {
            return $grammem;
        }

        return false;
    }

    public function isGrammaticalNumber($grammem)
    {
        $grammem = mb_strtoupper($grammem);

        if(in_array($grammem, $this->getGrammaticalNumberGrammems())) {
            return $grammem;
        }

        return false;
    }

    public function isPluralGrammaticalNumber($grammem)
    {
        $grammem = mb_strtoupper($grammem);

        if($grammem == SMARTSEO_MORPHOLOGY_PLURAL) {
            return true;
        }

        return false;
    }

    public function isCase($grammem)
    {
        $grammem = mb_strtoupper($grammem);

        if(in_array($grammem, $this->getCaseGrammems())) {
            return $grammem;
        }

        return false;
    }

    public function getGrammaticalNumberGrammems()
    {
        return [
            SMARTSEO_MORPHOLOGY_SINGULAR,
            SMARTSEO_MORPHOLOGY_PLURAL
        ];
    }

    public function getGenderGrammems()
    {
        return [
            SMARTSEO_MORPHOLOGY_MASCULINUM,
            SMARTSEO_MORPHOLOGY_FEMINUM,
            SMARTSEO_MORPHOLOGY_NEUTRUM,
        ];
    }

    public function getSpeechPartsGrammems()
    {
        return [
            SMARTSEO_MORPHOLOGY_NOUN,
            SMARTSEO_MORPHOLOGY_ADJ_FULL,
            SMARTSEO_MORPHOLOGY_ADJ_SHORT,
            SMARTSEO_MORPHOLOGY_VERB,
            SMARTSEO_MORPHOLOGY_PARTICIPLE,
            SMARTSEO_MORPHOLOGY_PARTICIPLE_SHORT,
        ];
    }

    public function getCaseGrammems()
    {
        return [
            SMARTSEO_MORPHOLOGY_NOMINATIV,
            SMARTSEO_MORPHOLOGY_GENITIV,
            SMARTSEO_MORPHOLOGY_DATIV,
            SMARTSEO_MORPHOLOGY_ACCUSATIV,
            SMARTSEO_MORPHOLOGY_INSTRUMENTALIS,
            SMARTSEO_MORPHOLOGY_LOCATIV,
            SMARTSEO_MORPHOLOGY_VOCATIV,
            SMARTSEO_MORPHOLOGY_SECOND_CASE
        ];
    }

    protected function getOptions()
    {
        return [
            'storage' => PHPMORPHY_STORAGE_FILE,
        ];
    }

    protected function getDictionaryDir()
    {
        $dictionary = null;
        if(mb_strtolower(LANG_CHARSET) === 'windows-1251' || mb_strtolower(LANG_CHARSET) === 'cp1251') {
            $dictionary = 'windows-1251';
        } else {
            $dictionary = 'utf-8';
        }

        return  Smartseo\General\Smartseo::getModulePath() . 'vendors/phpmorphy/dictionary/' . $dictionary . '/';
    }

    protected function getLibraryCommonFile()
    {
        return Smartseo\General\Smartseo::getModulePath() . 'vendors/phpmorphy/phpmorphy-0.3.7/src/common.php';
    }

    protected function getLang()
    {
        return $this->lang;
    }

    protected function mergeGrammems(array $grammems1, array $grammems2)
    {
        $hasGrammaticalNumber = false;
        $hasPluralGrammaticalNumber = false;
        $hasGender = false;
        $hasCase = false;
        foreach ($grammems2 as $key => $grammem) {
            if($this->isGrammaticalNumber($grammem)) {
                $hasGrammaticalNumber = true;
            }

            if($this->isPluralGrammaticalNumber($grammem)) {
                $hasPluralGrammaticalNumber = true;
            }

            if($this->isGender($grammem)) {
                $hasGender = true;
            }

            if($this->isCase($grammem)) {
                $hasCase = true;
            }
        }

        foreach ($grammems1 as $key => $grammem) {
            if($hasGrammaticalNumber && $this->isGrammaticalNumber($grammem)) {
                unset($grammems1[$key]);
            }

            if($hasGender && $this->isGender($grammem)) {
                unset($grammems1[$key]);
            }

            if($hasCase && $this->isCase($grammem)) {
                unset($grammems1[$key]);
            }
        }

        $grammems = array_merge($grammems1, $grammems2);

        if($hasPluralGrammaticalNumber) {
            foreach ($grammems as $key => $grammem) {
                if($this->isGender($grammem)) {
                    unset($grammems[$key]);
                }
            }
        }

        return $grammems;
    }
}
