<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/general/title.php");

class CAnmartoSearchTitle extends CDBResult
{

    function setMinWordLength($minLength)
    {
        $minLength = intval($minLength);
        if ($minLength > 0)
        {
            $this->minLength = $minLength;
        }
    }

    function searchTitle($phrase = '', $nTopCount = 5, $arParams = [], $bNotFilter = false, $order = '')
    {
        $DB = CDatabase::GetModuleConnection('search');
        $bOrderByRank = ($order == 'rank');

        $sqlHaving = [];
        $sqlWords = [];
        if (!empty($this->_arPhrase))
        {
            $last = true;
            foreach (array_reverse($this->_arPhrase, true) as $word => $pos)
            {
                if ($last && !preg_match("/[\\n\\r \\t]$/", $phrase))
                {
                    $last = false;
                    if (mb_strlen($word) >= $this->minLength)
                    {
                        $s = $sqlWords[] = "ct.WORD like '" . $DB->ForSQL($word) . "%'";
                    }
                    else
                    {
                        $s = '';
                    }
                }
                else
                {
                    $s = $sqlWords[] = "ct.WORD = '" . $DB->ForSQL($word) . "'";
                }

                if ($s)
                {
                    $sqlHaving[] = '(sum(case when ' . $s . ' then 1 else 0 end) > 0)';
                }
            }
        }

        if (!empty($sqlWords))
        {
            $bIncSites = false;
            $strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
            if ($bNotFilter)
            {
                if (!empty($strSqlWhere))
                {
                    $strSqlWhere = 'NOT (' . $strSqlWhere . ')';
                }
                else
                {
                    $strSqlWhere = '1=0';
                }
            }

            $strSql = "
				SELECT
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.TITLE
					,sc.PARAM1
					,sc.PARAM2
					,sc.DATE_CHANGE
					,sc.URL as URL
					,scsite.URL as SITE_URL
					,scsite.SITE_ID
					,case when position('" . $DB->ForSQL(mb_strtoupper($phrase)) . "' in upper(sc.TITLE)) > 0 then 1 else 0 end RANK1
					,count(1) RANK2
					,min(ct.POS) RANK3
				FROM
					b_search_content_title ct
					inner join b_search_content sc on sc.ID = ct.SEARCH_CONTENT_ID
					INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID and ct.SITE_ID = scsite.SITE_ID
				WHERE
					" . CSearch::CheckPermissions('sc.ID') . "
					AND ct.SITE_ID = '" . SITE_ID . "'
					AND (" . implode(' OR ', $sqlWords) . ')
					' . (!empty($strSqlWhere) ? 'AND ' . $strSqlWhere : '') . '
				GROUP BY
					sc.ID, sc.MODULE_ID, sc.ITEM_ID, sc.TITLE, sc.PARAM1, sc.PARAM2, sc.DATE_CHANGE, sc.URL, scsite.URL, scsite.SITE_ID
				' . (count($sqlHaving) > 1 ? 'HAVING ' . implode(' AND ', $sqlHaving) : '') . '
				ORDER BY ' . (
                $bOrderByRank ?
                    'RANK1 DESC, RANK2 DESC, RANK3 ASC, TITLE' :
                    'DATE_CHANGE DESC, RANK1 DESC, RANK2 DESC, RANK3 ASC, TITLE'
                ) . '
				LIMIT ' . ($nTopCount + 1) . '
			';

            $r = $DB->Query($strSql);
            parent::__construct($r);
            return true;
        }
        else
        {
            return false;
        }
    }

	function getRankFunction($phrase)
	{
		$DB = CDatabase::GetModuleConnection('search');
		return "if(locate('".$DB->ForSQL(mb_strtoupper($phrase))."', upper(sc.TITLE)) > 0, 1, 0)";
	}

	function getSqlOrder($bOrderByRank)
	{
		if ($bOrderByRank)
			return "RANK1 DESC, TITLE";
		else
			return "DATE_CHANGE DESC, RANK1 DESC, TITLE";
	}

    function Search($phrase = "", $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
    {
        $DB = CDatabase::GetModuleConnection('search');
        $this->_arPhrase = stemming_split($phrase, LANGUAGE_ID);
        if (!empty($this->_arPhrase))
        {
            $nTopCount = intval($nTopCount);
            if ($nTopCount <= 0)
                $nTopCount = 5;

            $arId = CSearchFullText::getInstance()->searchTitle($phrase, $this->_arPhrase, $nTopCount, $arParams, $bNotFilter, $order);
            if (!is_array($arId))
            {
                return $this->searchTitle($phrase, $nTopCount, $arParams, $bNotFilter, $order);
            }
            elseif (!empty($arId))
            {
                $orderBy = $this->getSqlOrder($order == "rank");
                if ($order == "rank" && !empty($arId)) {
                    $ids = implode(",", $arId);
                    $orderBy = "FIELD (sc.ID, {$ids})";
                }
                $strSql = "
					SELECT
						sc.ID
						,sc.MODULE_ID
						,sc.ITEM_ID
						,sc.TITLE
						,sc.PARAM1
						,sc.PARAM2
						,sc.DATE_CHANGE
						,sc.URL as URL
						,scsite.URL as SITE_URL
						,scsite.SITE_ID
						,".$this->getRankFunction($phrase)." RANK1
					FROM
						b_search_content sc
						INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID
					WHERE
						sc.ID in (".implode(",", $arId).")
						and scsite.SITE_ID = '".SITE_ID."'
					ORDER BY ".$orderBy."
				";

                $r = $DB->Query($DB->TopSql($strSql, $nTopCount + 1));
                parent::__construct($r);
                return true;
            }
        }
        else
        {
            return false;
        }
    }
}
