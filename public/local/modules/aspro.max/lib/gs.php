<?
namespace Aspro\Max;
use	CMax as Solution;
use	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Web\Json,
	\Bitrix\Main\Web\HttpClient;

class GS {
	const OPTION_ENABLED = 'GS_ENABLED';
	const OPTION_COPY = 'GS_COPY';
	const OPTION_SK = 'GS_SK';
	const OPTION_LAST_PACKET = 'GS_LAST_PACKET';
	const NOTIFY_TAG = 'GS_#MODULE_ID#';
	const PACKET_DIR = 'gs';
	const PACKET_CHARSET = 'utf-8';
	const PACKET_TYPE_REGISTER = 1;
	const PACKET_TYPE_FILE = 2;
	const PACKET_TYPE_POST = 3;
	const SEND_URL = 'https://analytics.aspro.ru/add/';
	const SEND_TIMEOUT = 3;
	const SEND_USER_AGENT = 'gs-packet/bx/1';

	public static function enable(){
		if(!self::isEnabled()){
			self::clear();
			Option::set(self::getModuleId(), self::OPTION_ENABLED, 'Y');

			if(self::isRegistered()){
				// send enabled status
				$arData = self::mkData();
				self::sendData($arData, self::PACKET_TYPE_POST);
			}

			if(strpos($_SERVER['REQUEST_URI'], self::getNotifyUrl()) === false){
				self::showNotify();
			}
		}

		return true;
	}

	public static function disable(){
		if(self::isEnabled()){

			if(self::isRegistered()){
				// send disabled status
				self::mkData();
				self::sendData($arData, self::PACKET_TYPE_POST);
			}

			Option::set(self::getModuleId(), self::OPTION_ENABLED, 'N');
			self::clear();
			self::hideNotify();
		}

		return true;
	}

	public static function isEnabled(){
		return Option::get(self::getModuleId(), self::OPTION_ENABLED, 'Y') === 'Y';
	}

	public static function clear(){
		self::setLastPacket(array());

		$dbRes = \CFile::GetList(array(), array('MODULE_ID' => self::getModuleId()));
		while($arFile = $dbRes->Fetch()){
			\CFile::Delete($arFile['ID']);
		}

		$path = '/upload/'.self::getModuleId().'/'.self::PACKET_DIR;
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'].$path);
	}

	public static function truncate(){
		$arFilesIds = array();
		$dbRes = \CFile::GetList(array('ID' => 'DESC'), array('MODULE_ID' => self::getModuleId()));
		while($arFile = $dbRes->Fetch()){
			if(
				@file_exists($file = $_SERVER['DOCUMENT_ROOT'].\CFile::GetPath($arFile['ID'])) &&
				time() - filemtime($file) < 604800 // 7 days
			){
				$arFilesIds[$file] = $arFile['ID'];
			}
			else{
				\CFile::Delete($arFile['ID']);
			}
		}

		$arFilesSizes = array();
		$arDirs = array($_SERVER['DOCUMENT_ROOT'].'/upload/'.self::getModuleId().'/'.self::PACKET_DIR.'/');
		$i = 0;
		while($arDirs && ++$i < 100){
			$dir = array_pop($arDirs);
			$arDirExclude = array($dir.'.', $dir.'..');
			$bHasChilds = false;
			foreach(
				(array)glob($dir.'{,.}*', GLOB_NOSORT|GLOB_BRACE)
				as $file
			){
				if(is_dir($file)){
					if(!in_array($file, $arDirExclude)){
						if($i == 1){
							$arDirs[] = $file.'/';
						}
						else{
							\Bitrix\Main\IO\Directory::deleteDirectory($file);
						}
					}
				}
				else{
					if(!isset($arFilesIds[$file])){
						@unlink($file);
					}
					else{
						$arFilesSizes[$file] = @filesize($file);
						$bHasChilds = true;
					}
				}
			}

			if(!$bHasChilds){
				@rmdir($dir);
			}
		}

		$totalSize = 0;
		$arDirs = array();
		foreach($arFilesIds as $file => $id){
			if($totalSize >= 1048576){ // 1 Mb
				$arDirs[] = dirname($file);
				\CFile::Delete($id);
			}
			else{
				$totalSize += $arFilesSizes[$file];
			}
		}

		if($arDirs){
			$arDirs = array_unique($arDirs);
			foreach($arDirs as $dir){
				if(!glob($dir.'*', GLOB_NOSORT)){
					@rmdir($dir);
				}
			}
		}
	}

	public static function sendData($arData, $type = false){
		$result = false;

		if(
			$arData &&
			is_array($arData) &&
			function_exists('openssl_encrypt')
		){
			if(!$type){
				$type = self::PACKET_TYPE_FILE;
			}

			if(
				$type >= self::PACKET_TYPE_REGISTER &&
				$type <= self::PACKET_TYPE_POST
			){
				if(
					$type == self::PACKET_TYPE_REGISTER ||
					(self::isEnabled() && self::isRegistered())
				){
					$data = Json::encode($arData);
					$hash = md5($data);

					if($arLastPacket = self::getLastPacket()){
						if($arLastPacket['hash'] === $hash){

							return true;
						}
					}

					$pk = self::generatePk();
					$copy = self::getCopy();

					$arFields = array(
						'name' => self::generateFileName($hash),
						'MODULE_ID' => self::getModuleId(),
						'content' => $data = self::pkData($data, $pk),
					);

					if($fileId = \CFile::SaveFile($arFields, self::getModuleId().'/'.self::PACKET_DIR)){
						$result = self::sendPacket(
							array(
								'type' => $type,
								'copy' => $copy,
								'hash' => $hash,
								'pk' => $pk,
								'data' => $data,
								'time' => time(),
								'fileId' => $fileId,
								'filePath' => \CFile::GetPath($fileId),
								'server_name' => $_SERVER['SERVER_NAME'],
								'ret' => $type == self::PACKET_TYPE_REGISTER,
							)
						);

						if($type !== self::PACKET_TYPE_FILE){
							\CFile::Delete($fileId);
						}
					}

					self::truncate();
				}
			}
		}

		return $result;
	}

	public static function storeData($arData){
		return self::sendData($arData, self::PACKET_TYPE_FILE);
	}

	public static function register(){
		if($arData = self::mkData(array('server', 'registry'))){
			if($result = self::sendData($arData, self::PACKET_TYPE_REGISTER)){
				if($arResult = json_decode($result, true)){
					if(is_array($arResult)){
						if(strlen($arResult['copy'])){
							self::setCopy($arResult['copy']);

							if(strlen($arResult['sk'])){
								self::setSk($arResult['sk']);
							}

							return $arResult['copy'];
						}
					}
				}
			}
		}

		return false;
	}

	public static function isRegistered(){
		return boolval(self::getCopy()) && boolval(self::getSk());
	}

	public static function showNotify(){
		if(self::isEnabled()){
			$ID = \CAdminNotify::Add(array(
				'MESSAGE' => Loc::getMessage('GS_ENABLED_NOTIFY_MESSAGE', array(
					'#URL#' => self::getNotifyUrl().'?lang='.LANGUAGE_ID,
				)),
				'TAG' => self::getNotifyTag(),
				'MODULE_ID' => strtoupper(self::getModuleId()),
				'ENABLE_CLOSE' => 'Y'
			));
		}
	}

	public static function hideNotify(){
		$dbRes = \CAdminNotify::GetList(array(), array('MODULE_ID' => self::getModuleId(), 'TAG' => self::getNotifyTag()));
		while($arNotify = $dbRes->Fetch()){
			\CAdminNotify::Delete($arNotify['ID']);
		}
	}

	private static function getCopy(){
		return Option::get(self::getModuleId(), self::OPTION_COPY, '');
	}

	private static function setCopy($copy){
		Option::set(self::getModuleId(), self::OPTION_COPY, $copy);
	}

	private static function getSk(){
		return Option::get(self::getModuleId(), self::OPTION_SK, '');
	}

	private static function setSk($sk){
		return Option::set(self::getModuleId(), self::OPTION_SK, $sk);
	}

	private static function getNotifyTag(){
		return str_replace('#MODULE_ID#', self::getModuleId(), self::NOTIFY_TAG);
	}

	private static function getNotifyUrl(){
		return '/bitrix/admin/'.self::getModuleId().'_gs.php';
	}

	private static function getModuleId(){
		return Solution::moduleID;
	}

	private static function generatePk(){
		$pk = '';
		while(strlen($pk) < 16){
			$rs = new \Bitrix\Main\Type\RandomSequence(time().(random_int(1, 10000) + $i * random_int(1, 10000)).__FILE__);
			$pk .= preg_replace('/[^A-F0-9]/', '', $rs->randString(32));
		}
		$pk = substr($pk, 0, 16);

		return $pk;
	}

	private static function generateFileName($hash){
		$rs = new \Bitrix\Main\Type\RandomSequence($hash);

		return $rs->randString(32);
	}

	private static function sendPacket($arPacket){
		if(
			$arPacket['type'] === self::PACKET_TYPE_REGISTER ||
			$arPacket['type'] === self::PACKET_TYPE_FILE
		){
			unset($arPacket['data']);
		}

		$client = new HttpClient(array(
			'socketTimeout' => self::SEND_TIMEOUT,
			'streamTimeout' => self::SEND_TIMEOUT,
			'version' => HttpClient::HTTP_1_1,
			'charset' => self::PACKET_CHARSET,
		));
		$client->setHeader('User-Agent', self::SEND_USER_AGENT, true);

		$client->query(HttpClient::HTTP_POST, self::SEND_URL, array(
			'packet' => $arPacket,
		));

		$bSuccess = $client->getStatus() == 200;

		if($bSuccess){
			if($arPacket['ret']){

				return $client->getResult();
			}

			unset($arPacket['ret'], $arPacket['pk'], $arPacket['data'], $arPacket['filePath'], $arPacket['fileId'], $arPacket['domain']);
			self::setLastPacket($arPacket);
		}
		else{
			\CFile::Delete($arPacket['fileId']);
		}

		return $bSuccess;
	}

	private static function getLastPacket(){
		$arPacket = Json::decode(Option::get(self::getModuleId(), self::OPTION_LAST_PACKET, '[]'));

		return $arPacket = is_array($arPacket) ? $arPacket : array();
	}

	private static function setLastPacket($arPacket){
		$arPacket = is_array($arPacket) ? $arPacket : array();
		Option::set(self::getModuleId(), self::OPTION_LAST_PACKET, Json::encode($arPacket));
	}

	private static function pkData($data, $pk){
		$data = iconv(LANG_CHARSET, self::PACKET_CHARSET.'//IGNORE', $data);
		$sign = $pk.self::getSk();
		$sign = substr($sign, 0, 32);
		$ivLength = openssl_cipher_iv_length($method = 'AES-128-CBC');
		$ivBytes = openssl_random_pseudo_bytes($ivLength);
		$raw = openssl_encrypt($data, $method, $sign, OPENSSL_RAW_DATA, $ivBytes);
		$hmac = hash_hmac('sha256', $raw, $sign, true);

		return $ivBytes.$hmac.$raw;
	}

	public function mkData($arParts = array()){
		$arData = array(
			'copy' => self::getCopy(),
			'module' => self::getModuleId(),
			'enabled' => self::isEnabled(),
		);

		if(!in_array('registry', $arParts)){
			include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/license_key.php');
			$arData['license'] = array(
				'key' => $LICENSE_KEY,
			);
		}

		if(
			$arParts &&
			is_array($arParts)
		){
			//server
			if(in_array('server', $arParts)){
				$arData['server'] = array(
					'port' => $_SERVER['SERVER_PORT'],
					'addr' => $_SERVER['SERVER_ADDR'],
					'name' => $_SERVER['SERVER_NAME'],
				);
			}

			// license
			if(in_array('registry', $arParts)){
				if(@file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client.php')){
					include_once($file);
					if(strlen($license_key = \CUpdateClient::GetLicenseKey())){
						$arUpdateList = \CUpdateClient::GetUpdatesList($errorMessage, 'en', 'Y');
						$arClient = $arUpdateList['CLIENT'][0]['@'];
						if($arClient && is_array($arClient)){
							$arData['license'] = array(
								'key' => $license_key,
								'date_to' => strtotime($arClient['DATE_TO']),
								'partner_id' => $arClient['PARTNER_ID'],
							);
						}
					}
				}
			}

			// sites
			if(
				in_array('sites', $arParts) ||
				in_array('options', $arParts)
			){
				$arSitesIds = array();

				if(in_array('sites', $arParts)){
					$arData['sites'] = $arOriginalTemplates = array();
				}

				$dbRes = \CSite::GetList($by = 'sort', $order = 'desc', array('ACTIVE' => 'Y'));
				while($arSite = $dbRes->Fetch()){
					$arHosts = explode(PHP_EOL, $arSite['DOMAINS']);
					$arHosts[] = $arSite['SERVER_NAME'];
					foreach($arHosts as $i => &$host){
						$host = trim(str_replace(array('https://', 'http://'), '', $host));
						if(!strlen($host)){
							unset($arHosts[$i]);
						}
					}

					if($arHosts = array_unique($arHosts)){
						$arSitesIds[] = $arSite['LID'];

						if(in_array('sites', $arParts)){
							$bCustomTemplate = $bOriginalTemplate = false;
							$dbResT = \CSite::GetTemplateList($arSite['LID']);
							while($arSiteTemplate = $dbResT->Fetch()){
								if(!$arOriginalTemplates[$arSiteTemplate['TEMPLATE']]){
									$arTemplate = \CSiteTemplate::GetByID($arSiteTemplate['TEMPLATE'])->Fetch();
									$arOriginalTemplates[$arSiteTemplate['TEMPLATE']] = $arTemplate['PATH'] === '/bitrix/templates/'.Solution::templateName;
								}

								$bOriginalTemplate |= $arOriginalTemplates[$arSiteTemplate['TEMPLATE']];
								$bCustomTemplate |= !$arOriginalTemplates[$arSiteTemplate['TEMPLATE']];
							}

							$arData['sites'][$arSite['LID']] = array(
								'charset' => $arSite['CHARSET'],
								'lang' => $arSite['LANGUAGE_ID'],
								'hosts' => array_values($arHosts),
								'templates' => (($bOriginalTemplate || $bCustomTemplate) ? ($bCustomTemplate ? ($bOriginalTemplate ? 'mixed' : 'custom') : 'original') : 'none'),
							);
						}
					}
				}
			}

			if(
				in_array('modules', $arParts) ||
				in_array('options', $arParts)
			){
				$arSqlModules = array();

				if(in_array('modules', $arParts)){
					$arData['modules'] = array();
				}

				foreach(
					array(
						'/local/modules/',
						'/bitrix/modules/'
				) as $dir){
					foreach(
						(array)glob($_SERVER['DOCUMENT_ROOT'].$dir.'*', GLOB_ONLYDIR)
						as $moduleDir
					){
						if($obModule = \CModule::CreateModuleObject(basename($moduleDir))){
							$moduleId = $obModule->MODULE_ID;

							if(
								strpos($moduleId, 'aspro.') === 0 ||
								strpos($moduleId, 'centino.') === 0
							){
								if($bCanGS = Option::get($moduleId, self::OPTION_ENABLED, 'Y') !== 'N'){
									$arSqlModules[] = '\''.$moduleId.'\'';
								}
							}

							if(in_array('modules', $arParts)){
								$arData['modules'][$moduleId] = array(
									'name' => $obModule->MODULE_NAME,
									'version' => $obModule->MODULE_VERSION,
									'installed' => $obModule->IsInstalled(),
									'partner' => $obModule->PARTNER_NAME,
									'partner_uri' => $obModule->PARTNER_URI,
								);
							}
						}
					}
				}

				if(in_array('options', $arParts)){
					$arData['options'] = array();

					$connection = \Bitrix\Main\Application::getConnection();
					$bOptionSiteTableExists = $connection->queryScalar("SELECT COUNT(TABLE_NAME) FROM information_schema.tables WHERE table_name = 'b_option_site'");

					if($arSqlModules){
						$arExclude = array(
							'GROUP_DEFAULT_RIGHT',
							self::OPTION_ENABLED,
							self::OPTION_COPY,
							self::OPTION_SK,
							self::OPTION_LAST_PACKET,
							'NeedGenerateCustomTheme',
							'NeedGenerateCustomThemeBG',
							'NeedGenerateThemes',
							'SITE_INSTALLED',
							'LastGeneratedBaseColorBGCustom',
							'LastGeneratedBaseColorCustom',
							'YANDEX_MARKET_COUNT_REVIEWS_HINT',
							'YANDEX_MARKET_TOKEN_REVIEWS_HINT',
							'YA_GOLAS',
						);
						$arOnlyFill = array(
							'API_TOKEN_INSTAGRAMM',
							'APPLE_TOUCH_ICON_IMAGE',
							'CONTACTS_ADDRESS',
							'CONTACTS_DESCRIPTION12',
							'CONTACTS_EMAIL',
							'CONTACTS_MAP',
							'CONTACTS_PHONE',
							'CONTACTS_REGIONAL_DESCRIPTION34',
							'CONTACTS_REGIONAL_DESCRIPTION5',
							'CONTACTS_REGIONAL_PHONE',
							'CONTACTS_SCHEDULE12',
							'FAVICON_IMAGE',
							'GOOGLE_RECAPTCHA_PRIVATE_KEY',
							'GOOGLE_RECAPTCHA_PUBLIC_KEY',
							'MIN_ORDER_PRICE_TEXT',
							'PWA_ICON_144',
							'PWA_ICON_192',
							'PWA_ICON_36',
							'PWA_ICON_48',
							'PWA_ICON_72',
							'PWA_ICON_96',
							'PWA_START_URL',
							'SITEMAP_URL',
							'SOCIAL_FACEBOOK',
							'SOCIAL_GOOGLEPLUS',
							'SOCIAL_INSTAGRAM',
							'SOCIAL_LINKEDIN',
							'SOCIAL_MAIL',
							'SOCIAL_ODNOKLASSNIKI',
							'SOCIAL_PINTEREST',
							'SOCIAL_SNAPCHAT',
							'SOCIAL_TELEGRAM',
							'SOCIAL_TIKTOK',
							'SOCIAL_TWITTER',
							'SOCIAL_VIBER',
							'SOCIAL_VK',
							'SOCIAL_WHATS',
							'SOCIAL_YOUTUBE',
							'SOCIAL_ZEN',
							'YA_COUNTER_ID',
							'YANDEX_MARKET_TOKEN_REVIEWS',
						);

						if($bOptionSiteTableExists){
							$options = $connection->query("(SELECT MODULE_ID,NAME,SITE_ID,VALUE FROM b_option WHERE MODULE_ID in (".implode(',', $arSqlModules).")) UNION (SELECT MODULE_ID,NAME,SITE_ID,VALUE FROM b_option_site WHERE MODULE_ID in (".implode(',', $arSqlModules)."))");
						}
						else{
							$options = $connection->query("SELECT MODULE_ID,NAME,SITE_ID,VALUE FROM b_option WHERE MODULE_ID in (".implode(',', $arSqlModules).")");
						}
						while($arOption = $options->fetch()){
							if(strlen($arOption['NAME'])){
								if(
									in_array($arOption['NAME'], $arExclude) ||
									preg_match('/_NOTE$/', $arOption['NAME'], $match) ||
									preg_match('/_HINT$/', $arOption['NAME'], $match)
								){
									continue;
								}

								if(
									strpos($arOption['VALUE'], ':{') !== false &&
									strpos($arOption['VALUE'], '}') !== false
								){
									$tmp = unserialize($arOption['VALUE'], ['allowed_classes' => false]);
									if($tmp !== false){
										$arOption['VALUE'] = $tmp;
									}
								}

								if(in_array($arOption['NAME'], $arOnlyFill)){
									if(is_array($arOption['VALUE'])){
										$arOption['VALUE'] = $arOption['VALUE'] ? 'Y' : 'N';
									}
									else{
										$arOption['VALUE'] = strlen($arOption['VALUE']) > 0 ? 'Y' : 'N';
									}
								}

								if(!isset($arData['options'][$arOption['MODULE_ID']])){
									$arData['options'][$arOption['MODULE_ID']] = array();
								}

								if(strpos($arOption['NAME'], 'HEADER_PHONES_array_') === 0){
									if(!isset($arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_VALUE'])){
										$arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_VALUE'] =
										$arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_DESCRIPTION'] = array();
									}

									if(!isset($arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_VALUE'][$arOption['SITE_ID']])){
										$arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_VALUE'][$arOption['SITE_ID']] = 0;
										$arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_DESCRIPTION'][$arOption['SITE_ID']] = 0;
									}

									if(strpos($arOption['NAME'], '_VALUE_') !== false){
										if($arOption['VALUE']){
											++$arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_VALUE'][$arOption['SITE_ID']];
										}
									}
									elseif(strpos($arOption['NAME'], '_DESCRIPTION_') !== false){
										if($arOption['VALUE']){
											++$arData['options'][$arOption['MODULE_ID']]['HEADER_PHONES_DESCRIPTION'][$arOption['SITE_ID']];
										}
									}

									continue;
								}

								if(is_array($arOption['VALUE'])){
									foreach($arOption['VALUE'] as $subCode => $subValue){
										$subName = $arOption['NAME'].'_'.$subCode;

										if(!isset($arData['options'][$arOption['MODULE_ID']][$subName])){
											$arData['options'][$arOption['MODULE_ID']][$subName] = array();
										}

										$arData['options'][$arOption['MODULE_ID']][$subName][$arOption['SITE_ID']] = $subValue;
									}
								}
								else{
									if(!isset($arData['options'][$arOption['MODULE_ID']][$arOption['NAME']])){
										$arData['options'][$arOption['MODULE_ID']][$arOption['NAME']] = array();
									}

									$arData['options'][$arOption['MODULE_ID']][$arOption['NAME']][$arOption['SITE_ID']] = $arOption['VALUE'];
								}
							}
						}
					}

					foreach(
						array(
							'main' => array(
								'update_devsrv',
								'update_site',
								'stable_versions_only',
								'update_autocheck',
								'new_user_registration',
								'captcha_registration',
								'captcha_restoring_password',
								'new_user_phone_auth',
								'new_user_phone_required',
								'new_user_email_auth',
								'new_user_email_required',
								'new_user_registration_email_confirmation',
								'new_user_email_uniq_check',
								'save_original_file_name',
								'translit_original_file_name',
								'optimize_css_files',
								'optimize_js_files',
								'use_minified_assets',
								'move_js_to_body',
								'compres_css_js_files',
							),
						) as $moduleId => $arOptions
					){
						if($arOptions){
							$arSqlOptions = array();
							foreach($arOptions as $code){
								$arSqlOptions[] = "'".$code."'";
							}

							$arData['options'][$moduleId] = array();

							if($bOptionSiteTableExists){
								$options = $connection->query("(SELECT NAME,SITE_ID,VALUE FROM b_option WHERE MODULE_ID = '".$moduleId."' AND NAME IN (".implode(',', $arSqlOptions).")) UNION (SELECT NAME,SITE_ID,VALUE FROM b_option_site WHERE MODULE_ID = '".$moduleId."' AND NAME IN (".implode(',', $arSqlOptions)."))");
							}
							else{
								$options = $connection->query("SELECT NAME,SITE_ID,VALUE FROM b_option WHERE MODULE_ID = '".$moduleId."' AND NAME IN (".implode(',', $arSqlOptions).")");
							}
							while($arOption = $options->fetch()){
								if(strlen($arOption['NAME'])){
									if(!isset($arData['options'][$moduleId][$arOption['NAME']])){
										$arData['options'][$moduleId][$arOption['NAME']] = array();
									}

									$arData['options'][$moduleId][$arOption['NAME']][$arOption['SITE_ID']] = $arOption['VALUE'];
								}
							}
						}
					}
				}
			}
		}

		return $arData;
	}
}
?>