<?php
IncludeModuleLangFile(__FILE__);

class CAllSiteMapG extends CDBResult
{
	public $m_href="";		//URL for result
	public $m_error="";	//Error message
	public $m_events=array();	//Events cache
	public $m_errors_count=0;	//Number of wrong URLs
	public $m_errors_href="";	//URL for errors file

	public function Create($site_id, $max_execution_time, $NS, $arOptions = array())
	{
		@set_time_limit(0);
		if (!is_array($NS)) {
			$NS = array(
				"ID"=>0,
				"CNT"=>0,
				"FILE_SIZE"=>0,
				"FILE_ID"=>0,
				"FILE_URL_CNT"=>0,
				"ERROR_CNT"=>0,
				"PARAM2"=>0,
			);
		} else {
			$NS = array(
				"ID"=>intval($NS["ID"]),
				"CNT"=>intval($NS["CNT"]),
				"FILE_SIZE"=>intval($NS["FILE_SIZE"]),
				"FILE_ID"=>intval($NS["FILE_ID"]),
				"FILE_URL_CNT"=>intval($NS["FILE_URL_CNT"]),
				"ERROR_CNT"=>intval($NS["ERROR_CNT"]),
				"PARAM2"=>intval($NS["ID"]),
			);
		}

		if (is_array($max_execution_time)) {
			$record_limit = $max_execution_time[1];
			$max_execution_time = $max_execution_time[0];
		} else {
			$record_limit = 5000;
		}

		if ($max_execution_time > 0) {
			$end_of_execution = time() + $max_execution_time;
		} else {
			$end_of_execution = 0;
		}

		if (is_array($arOptions) && ($arOptions["FORUM_TOPICS_ONLY"] == "Y")) {
			$bForumTopicsOnly = CModule::IncludeModule("forum");
		} else {
			$bForumTopicsOnly = false;
		}

		if (is_array($arOptions) && ($arOptions["BLOG_NO_COMMENTS"] == "Y")) {
			$bBlogNoComments = CModule::IncludeModule("blog");
		} else {
			$bBlogNoComments = false;
		}

		if (is_array($arOptions) && ($arOptions["USE_HTTPS"] == "Y")) {
			$strProto = "https://";
		} else {
			$strProto = "http://";
		}

		$rsSite=CSite::GetByID($site_id);
		if ($arSite=$rsSite->Fetch()) {            
			$SERVER_NAME = trim($arSite["SERVER_NAME"]);            
			if (strlen($SERVER_NAME) <= 0) {
				$this->m_error=GetMessage("SEARCH_ERROR_SERVER_NAME", array("#SITE_ID#" => '<a href="site_edit.php?LID='.urlencode($site_id).'&lang='.urlencode(LANGUAGE_ID).'">'.htmlspecialchars($site_id).'</a>'))."<br>";
				return false;
			}
			//Cache events
			$events = GetModuleEvents("search", "OnSearchGetURL");
			while ($arEvent = $events->Fetch()) {
				$this->m_events[]=$arEvent;
			}            
			//Clear error file
			if ($NS["ID"]==0 && $NS["CNT"]==0) {
				$e=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_errors.xml", "w");
				$strBegin="<?xml version='1.0' encoding='UTF-8'?>\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
				fwrite($e, $strBegin);
			}
			//Or open it for append
			else {
				$e=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_errors.xml", "a");
			}
			if (!$e) {
				$this->m_error=GetMessage("SEARCH_ERROR_OPEN_FILE")." ".$arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_errors.xml"."<br>";
				return false;
			}
			
			//Open current sitemap file
			if ($NS["FILE_SIZE"]==0) {
					$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_".sprintf("%03d",$NS["FILE_ID"]).".xml", "w");                
				//$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap.xml", "w");
				$strBegin="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
				fwrite($f, $strBegin);
				$NS["FILE_SIZE"]+=strlen($strBegin);
			} else {
					$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_".sprintf("%03d",$NS["FILE_ID"]).".xml", "a");
				//$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap.xml", "a");
			}
			if (!$f) {
					$this->m_error=GetMessage("SEARCH_ERROR_OPEN_FILE")." ".$arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_".sprintf("%03d",$NS["FILE_ID"]).".xml"."<br>";
				//$this->m_error=GetMessage("SEARCH_ERROR_OPEN_FILE")." ".$arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap.xml"."<br>";
				return false;
			}

			$inc = IntVal($arOptions["INC"]) != 1 && IntVal($arOptions["INC"]) != 2? 0: IntVal($arOptions["INC"]);
			$mask = explode(";", $arOptions["MASK"]);

			$this->GetURLs($site_id, $NS["ID"], $record_limit);
			$bFileIsFull=false;
			
			while (!$bFileIsFull && $ar=$this->Fetch()) {
				$record_limit--;
				$NS["ID"]=$ar["ID"];
				if (strlen($ar["URL"]) < 1) {
					continue;
				}

				if ($inc == 1) {
					$exs = false;
					
					foreach ($mask as $_url) {
						$_url = preg_replace("/\//", "\/", $_url);
						$_url = preg_replace("/\./", "\.", $_url);
						$_url = preg_replace("/\*/", ".*", $_url);
						$_url = preg_replace("/\?/", ".", $_url);
						if (preg_match("/^".$_url."/", $ar["URL"])) {

							$exs = true;
							continue;
						}
					}
					
					if (!$exs) {
						continue;
					}
				}

				if ($inc == 2) {
					foreach ($mask as $_url) {
						$_url = preg_replace("/\//", "\/", $_url);
						$_url = preg_replace("/\./", "\.", $_url);
						$_url = preg_replace("/\*/", ".*", $_url);
						$_url = preg_replace("/\?/", ".", $_url);

						if (preg_match("/^".$_url."/", $ar["URL"])) {
							continue 2;
						}
					}
				}

				if ($bForumTopicsOnly && ($ar["MODULE_ID"] == "forum")) {
					//Forum topic ID
					$PARAM2 = intval($ar["PARAM2"]);
					if ($NS["PARAM2"] < $PARAM2) {
						$NS["PARAM2"] = $PARAM2;
						$arTopic = CForumTopic::GetByIDEx($PARAM2);
						if ($arTopic) {
							$ar["FULL_DATE_CHANGE"] = $arTopic["LAST_POST_DATE"];
						}
					} else {
						continue;
					}
				}

				if ($bBlogNoComments && ($ar["MODULE_ID"] == "blog")) {
					if (substr($ar["ITEM_ID"], 0, 1) === "C") {
						continue;
					}
				}
				$urlExcludeCount = 0;
				$aSitemapEnable = \Cosmos\Config::getInstance()->getParam("SITEMAP");
				// Убираем все, кроме нужных урл в каталоге
				if (!empty($aSitemapEnable['ENABLE_DIR'])) {
					foreach ($aSitemapEnable['ENABLE_DIR'] as $strDir) {
						if ((stripos($ar["URL"], $strDir) !== false) && (strpos($ar["URL"], '/catalog/') !== false)) {
							$urlExcludeCount++;
						}
					}
				}
				if ($urlExcludeCount <= 0 || (preg_match("/^\?oid=.*?/", $ar["URL"]))) {
					continue;
				}
				$strURL = $this->LocationEncode($strProto.$ar["SERVER_NAME"].$ar["URL"]);
				$strTime = $this->TimeEncode(MakeTimeStamp(ConvertDateTime($ar["FULL_DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS"), "DD.MM.YYYY HH:MI:SS"));
				$dPriority = 1 - 0.2*(count(array_unique(explode('/', $strURL)))-3);
				if ($dPriority < 0.2) {
					$dPriority = 0.2;
				}
				$strToWrite="\t<url>\n\t\t<loc>".$strURL."</loc>\n\t\t<lastmod>".$strTime."</lastmod>\n\t\t<changefreq>".$this->getFreq($ar["URL"])."</changefreq>\n\t\t<priority>".$dPriority."</priority>\n\t</url>\n";
				//$strToWrite="\t<url>\n\t\t<loc>".$strURL."</loc>\n\t</url>\n";
				
				if (strlen($strURL)>2048 || preg_match("/[\\x00-\\x20\\x7F-\\xFF]/", $strURL)) {
					fwrite($e, $strToWrite);
					$NS["ERROR_CNT"]++;
				} else {
					fwrite($f, $strToWrite);
					$NS["CNT"]++;
					$NS["FILE_SIZE"]+=strlen($strToWrite);
					$NS["FILE_URL_CNT"]++;
				}
				//Next File on file size or url count limit
				if ($NS["FILE_SIZE"]>9000000 || $NS["FILE_URL_CNT"]>=50000) {
					$bFileIsFull=true;
				} elseif ($end_of_execution) {
					if (time() > $end_of_execution) {
						fclose($e);
						fclose($f);
						return $NS;
					}
				}
			}
			if ($bFileIsFull) {
				fwrite($e, "</urlset>\n");
				fclose($e);
				fwrite($f, "</urlset>\n");
				fclose($f);

				$NS["FILE_SIZE"]=0;
				$NS["FILE_URL_CNT"]=0;
				$NS["FILE_ID"]++;
				return $NS;
			} elseif ($record_limit<=0) {
				return $NS;
			} else {
				fwrite($e, "</urlset>\n");
				fclose($e);
				fwrite($f, "</urlset>\n");
				fclose($f);
			}
			//WRITE INDEX FILE HERE
			$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_index.xml", "w");
			if (!$f) {
				$this->m_error=GetMessage("SEARCH_ERROR_OPEN_FILE")." ".$arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_index.xml"."<br>";
				return false;
			}
			$strBegin="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
			fwrite($f, $strBegin);
			for ($i = 0; $i <= $NS["FILE_ID"]; $i++) {
					$strFile = $arSite["DIR"]."sitemap_".sprintf("%03d",$i).".xml";
				//$strFile = $arSite["DIR"]."sitemap.xml";
				$strTime = $this->TimeEncode(filemtime($arSite["ABS_DOC_ROOT"].$strFile));
				$dPriority = 1;
				fwrite($f, "\t<sitemap>\n\t\t<loc>".$strProto.$arSite["SERVER_NAME"].$strFile."</loc>\n\t\t<lastmod>".$strTime."</lastmod>\n\t\t<changefreq>monthly</changefreq>\n\t\t<priority>".$dPriority."</priority>\n\t</sitemap>\n");
				//fwrite($f,"\t<sitemap>\n\t\t<loc>".$strProto.$arSite["SERVER_NAME"].$strFile."</loc>\n\t</sitemap>\n");
			}
			fwrite($f, "</sitemapindex>\n");
			fclose($f);
			$this->m_errors_count=$NS["ERROR_CNT"];
			$this->m_errors_href=$strProto.$arSite["SERVER_NAME"].$arSite["DIR"]."sitemap_errors.xml";
			$this->m_href=$strProto.$arSite["SERVER_NAME"].$arSite["DIR"]."sitemap_index.xml";
			return true;
		} else {
			$this->m_error=GetMessage("SEARCH_ERROR_SITE_ID")."<br>";
			return false;
		}
	}

	public function Fetch()
	{
		static $index = false;

		$r = parent::Fetch();
		if ($r) {
			if (strlen($r["SITE_URL"])>0) {
				$r["URL"] = $r["SITE_URL"];
			}

			if (substr($r["URL"], 0, 1)=="=") {
				foreach ($this->m_events as $arEvent) {
					$r["URL"] = ExecuteModuleEventEx($arEvent, array($r));
				}
			}
			$r["URL"] = str_replace(
				array("#LANG#", "#SITE_DIR#", "#SERVER_NAME#"),
				array($r["DIR"], $r["DIR"], $r["SERVER_NAME"]),
				$r["URL"]
			);
			$r["URL"] = preg_replace("'/+'s", "/", $r["URL"]);
			if (defined("BX_DISABLE_INDEX_PAGE") && BX_DISABLE_INDEX_PAGE) {
				if (!$index) {
					$index = "#/(".str_replace(" ", "|", preg_quote(implode(" ", GetDirIndexArray()), "#")).")$#";
				}
				$r["URL"] = preg_replace($index, "/", $r["URL"]);
			}

			//Remove anchor otherwise Google will ignore this link
			$p = strpos($r["URL"], "#");
			if ($p !== false) {
				$r["URL"] = substr($r["URL"], 0, $p);
			}
		}
		return $r;
	}

	public function getFreq($str)
	{//freq filter
		if (preg_match("#^/(?!news|search|login|catalog-sort|sitemap)(.*)/#", $str)) {
			return "daily";
		} else {
			return "monthly";
		}
	}
	
	public function LocationEncode($str)
	{
		static $search = array("&", "'", "\"", ">", "<");
		static $replace = array("&amp;", "&apos;", "&quot;", "&gt;", "&lt;");
		return str_replace($search, $replace, $str);
	}

	public function TimeEncode($iTime)
	{
		$iTZ = date("Z", $iTime);
		$iTZHour = intval(abs($iTZ)/3600);
		$iTZMinutes = intval((abs($iTZ)-$iTZHour*3600)/60);
		$strTZ = ($iTZ<0? "-": "+").sprintf("%02d:%02d", $iTZHour, $iTZMinutes);
		return date("Y-m-d", $iTime)."T".date("H:i:s", $iTime).$strTZ;
	}
}
