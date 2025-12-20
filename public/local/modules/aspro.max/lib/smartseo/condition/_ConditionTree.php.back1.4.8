<?php

namespace Aspro\Max\Smartseo\Condition;

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

class ConditionTree extends \CGlobalCondTree
{
    private $errors = [];

    const BT_COND_BUILD_SMARTSEO = 'smartseo';

    /**
     * @var \CGlobalCondCtrl
     */
    protected $builds = [];
    protected $registerBuildControls = [];

    public function init($intMode, $mxEvent, $arParams = array())
    {
        if ($this->builds) {
            $this->initControlBuilds();
        }

        parent::Init($intMode, $mxEvent, $arParams);
    }

    protected function getEventList($buildId)
    {
        if ($buildId == self::BT_COND_BUILD_SMARTSEO) {
            return [
                'INTERFACE_ATOMS' => [
                    'MODULE_ID' => Smartseo\General\Smartseo::MODULE_ID,
                    'EVENT_ID' => 'onBuildConditionTreeInterfaceAtoms'
                ],
                'INTERFACE_CONTROLS' => [
                    'MODULE_ID' => Smartseo\General\Smartseo::MODULE_ID,
                    'EVENT_ID' => 'onBuildConditionTreeInterfaceControls'
                ],
                'ATOMS' => [
                    'MODULE_ID' => Smartseo\General\Smartseo::MODULE_ID,
                    'EVENT_ID' => 'onBuildConditionTreeAtoms'
                ],
                'CONTROLS' => [
                    'MODULE_ID' => Smartseo\General\Smartseo::MODULE_ID,
                    'EVENT_ID' => 'onBuildConditionTreeControls'
                ]
            ];
        }

        return parent::GetEventList($buildId);
    }

    public function addControlBuild(\CGlobalCondCtrl $controlBuild)
    {
        $this->builds[uniqid()] = $controlBuild;

        return $this;
    }

    public function initControlBuilds()
    {
        $result = [];

        foreach ($this->builds as $id => $build) {
            $result[$id] = $build->getBuild();

            if ($result[$id]['CONTROLS']) {
                $this->registerBuildControls($id, $result[$id]['CONTROLS']);
            } elseif ($result[$id]['ID']) {
                $this->registerBuildControl($id, $result[$id]['ID']);
            }
        }

        if ($result) {
            $this->initResult($result);
        }
    }

    public function showTreeConditions(array $conditions = [])
    {
        $this->arConditions = $conditions ?: $this->getDefaultConditions();

        $this->showScripts();

        $script = '';

        $script .= '<script type="text/javascript">' . "\n";
        $script .= 'var ' . $this->strJSName . ' = new BX.TreeConditions(' . "\n";
        $script .= $this->showParams() . ",\n";
        $script .= $this->getJSConditions() . ",\n";
        $script .= $this->getJSControls() . "\n";

        $script .= ');' . "\n";
        $script .= '</script>' . "\n";

        echo $script;
    }

    public function getDefaultConditions()
    {
        return [
            'CLASS_ID' => 'CondGroup',
            'DATA' => ['All' => 'AND'],
            'CHILDREN' => [],
        ];
    }

    protected function getJSConditions()
    {
        if ($this->boolError) {
            $this->setErrors($this->arMsg);

            return '';
        }

        if (!$this->arConditions) {
            $this->arConditions = $this->getDefaultConditions();
        }

        $result = $this->parseConditionForJS($this->arConditions);

        return \CUtil::PhpToJSObject(current($result));
    }

    protected function getJSControls()
    {
        if ($this->boolError) {
            $this->setErrors($this->arMsg);

            return '';
        }

        if (!$this->arShowControlList) {
            return '';
        }

        foreach ($this->arShowControlList as $funcArray) {
            $_class = $funcArray[0];
            $_method = $funcArray[1];

            if (!class_exists($_class) || !method_exists($_class, $_method)) {
                continue;
            }

            if (!$build = $this->findBuildByClass($_class)) {
                continue;
            }

            $control = $build::$_method([
                  'SHOW_IN_GROUPS' => $this->arShowInGroups,
            ]);

            if (!empty($control) && is_array($control)) {
                $this->fillForcedShow($control);

                if (isset($control['controlId']) || isset($control['controlgroup'])) {
                    $result[] = $control;
                } else {
                    foreach ($control as $childControl) {
                        $result[] = $childControl;
                    }
                }
            }
        }

        return \CUtil::PhpToJSObject($result);
    }

    protected function parseConditionForJS($condition, $isFirst = true, $index = 0)
    {
        $result = [];

        if (!$condition['CLASS_ID'] || !isset($this->arControlList[$condition['CLASS_ID']])) {
            return [];
        }

        $control = $this->arControlList[$condition['CLASS_ID']];
        $class = $control['GetConditionShow'][0];
        $method = $control['GetConditionShow'][1];

        if (!class_exists($class) || !method_exists($class, $method)) {
            return [];
        }

        $build = $this->findBuildByControlId($control['ID']);

        $data = $build::$method([
              'COND_NUM' => $index,
              'ID' => $control['ID'],
              'DATA' => $condition['DATA'],
        ]);

        if (!$data) {
            return [];
        }

        if ($isFirst && 'Y' == $control['GROUP']) {
            $data['children'] = [];
        }

        if ('Y' == $control['GROUP'] && $condition['CHILDREN']) {
            $num = 0;
            foreach ($condition['CHILDREN'] as $conditionChild) {
                $child = $this->parseConditionForJS($conditionChild, false, $num);
                if ($child) {
                    $data['children'][] = $child;
                    $num++;
                }
            }
        }

        if ($isFirst) {
            $result[] = $data;
        } else {
            $result = $data;
        }

        return $result;
    }

    protected function getErrors()
    {
        return $this->errors;
    }

    protected function hasErrors()
    {
        return $this->errors ? true : false;
    }

    protected function setErrors($errors)
    {
        if (is_array($errors)) {
            $this->errors = array_map(function($item) {
                return $item;
            }, $errors);
        } else {
            $this->errors[] = $errors;
        }
    }

    protected function addError($error)
    {
        $this->errors[] = $error;
    }

    private function registerBuildControl($build, $controlId)
    {
        $this->registerBuildControls[$controlId] = $build;
    }

    private function registerBuildControls($build, $controls)
    {
        foreach ($controls as $field) {
            $this->registerBuildControls[$field['ID']] = $build;
        }
    }

    /**
     * @return \CGlobalCondCtrl
     */
    private function findBuildByControlId($fieldId)
    {
        $build = $this->registerBuildControls[$fieldId];

        if (isset($this->builds[$build])) {
            return $this->builds[$build];
        }

        return null;
    }

    /**
     * @return \CGlobalCondCtrl
     */
    private function findBuildByClass($class)
    {
        foreach ($this->builds as $build) {
            if ($build instanceof $class) {
                return $build;
            }
        }

        return null;
    }

    /**
     * The code is taken from the method $this->OnConditionControlBuildList()
     */
    private function initResult($result)
    {
        $this->arShowControlList = [];
        $this->arInitControlList = [];

        $rawControls = array();
        $controlIndex = 0;
        foreach ($result as $arRes) {
            if (empty($arRes) || !is_array($arRes))
                continue;
            if (isset($arRes['ID'])) {
                if (isset($arRes['EXIST_HANDLER']) && $arRes['EXIST_HANDLER'] === 'Y') {
                    if (!isset($arRes['MODULE_ID']) && !isset($arRes['EXT_FILE']))
                        continue;
                }
                else {
                    $arRes['MODULE_ID'] = '';
                    $arRes['EXT_FILE'] = '';
                }
                if (array_key_exists('EXIST_HANDLER', $arRes))
                    unset($arRes['EXIST_HANDLER']);
                $arRes['GROUP'] = (isset($arRes['GROUP']) && $arRes['GROUP'] == 'Y' ? 'Y' : 'N');
                if (isset($this->arControlList[$arRes['ID']])) {
                    $this->arMsg[] = array('id' => 'CONTROLS', 'text' => str_replace('#CONTROL#', $arRes['ID'], Loc::getMessage('BT_MOD_COND_ERR_CONTROL_DOUBLE')));
                    $this->boolError = true;
                } else {
                    if (!$this->CheckControl($arRes))
                        continue;
                    $this->arControlList[$arRes["ID"]] = $arRes;
                    if ($arRes['GROUP'] == 'Y') {
                        if (empty($arRes['FORCED_SHOW_LIST'])) {
                            $this->arShowInGroups[] = $arRes['ID'];
                        } else {
                            $forcedList = $arRes['FORCED_SHOW_LIST'];
                            if (!is_array($forcedList))
                                $forcedList = array($forcedList);
                            foreach ($forcedList as $forcedId) {
                                if (is_array($forcedId))
                                    continue;
                                $forcedId = trim($forcedId);
                                if ($forcedId == '')
                                    continue;
                                if (!isset($this->forcedShowInGroup[$forcedId]))
                                    $this->forcedShowInGroup[$forcedId] = array();
                                $this->forcedShowInGroup[$forcedId][] = $arRes['ID'];
                            }
                            unset($forcedId, $forcedList);
                        }
                    }
                    if (isset($arRes['GetControlShow']) && !empty($arRes['GetControlShow'])) {
                        if (!in_array($arRes['GetControlShow'], $this->arShowControlList)) {
                            $this->arShowControlList[] = $arRes['GetControlShow'];
                            $showDescription = array(
                                'CONTROL' => $arRes['GetControlShow'],
                            );
                            if (isset($arRes['SORT']) && (int) $arRes['SORT'] > 0) {
                                $showDescription['SORT'] = (int) $arRes['SORT'];
                                $showDescription['INDEX'] = 1;
                            } else {
                                $showDescription['SORT'] = INF;
                                $showDescription['INDEX'] = $controlIndex;
                                $controlIndex++;
                            }
                            $rawControls[] = $showDescription;
                            unset($showDescription);
                        }
                    }
                    if (isset($arRes['InitParams']) && !empty($arRes['InitParams'])) {
                        if (!in_array($arRes['InitParams'], $this->arInitControlList))
                            $this->arInitControlList[] = $arRes['InitParams'];
                    }
                }
            }
            elseif (isset($arRes['COMPLEX']) && 'Y' == $arRes['COMPLEX']) {
                $complexModuleID = '';
                $complexExtFiles = '';
                if (isset($arRes['EXIST_HANDLER']) && $arRes['EXIST_HANDLER'] === 'Y') {
                    if (isset($arRes['MODULE_ID']))
                        $complexModuleID = $arRes['MODULE_ID'];
                    if (isset($arRes['EXT_FILE']))
                        $complexExtFiles = $arRes['EXT_FILE'];
                }
                if (isset($arRes['CONTROLS']) && !empty($arRes['CONTROLS']) && is_array($arRes['CONTROLS'])) {
                    if (array_key_exists('EXIST_HANDLER', $arRes))
                        unset($arRes['EXIST_HANDLER']);
                    $arInfo = $arRes;
                    unset($arInfo['COMPLEX'], $arInfo['CONTROLS']);
                    foreach ($arRes['CONTROLS'] as &$arOneControl) {
                        if (isset($arOneControl['ID'])) {
                            if (isset($arOneControl['EXIST_HANDLER']) && $arOneControl['EXIST_HANDLER'] === 'Y') {
                                if (!isset($arOneControl['MODULE_ID']) && !isset($arOneControl['EXT_FILE']))
                                    continue;
                            }
                            $arInfo['GROUP'] = 'N';
                            $arInfo['MODULE_ID'] = isset($arOneControl['MODULE_ID']) ? $arOneControl['MODULE_ID'] : $complexModuleID;
                            $arInfo['EXT_FILE'] = isset($arOneControl['EXT_FILE']) ? $arOneControl['EXT_FILE'] : $complexExtFiles;
                            $control = array_merge($arOneControl, $arInfo);
                            if (isset($this->arControlList[$control['ID']])) {
                                $this->arMsg[] = array('id' => 'CONTROLS', 'text' => str_replace('#CONTROL#', $control['ID'], Loc::getMessage('BT_MOD_COND_ERR_CONTROL_DOUBLE')));
                                $this->boolError = true;
                            } else {
                                if (!$this->CheckControl($control))
                                    continue;
                                $this->arControlList[$control['ID']] = $control;
                            }
                            unset($control);
                        }
                    }
                    if (isset($arOneControl))
                        unset($arOneControl);
                    if (isset($arRes['GetControlShow']) && !empty($arRes['GetControlShow'])) {
                        if (!in_array($arRes['GetControlShow'], $this->arShowControlList)) {
                            $this->arShowControlList[] = $arRes['GetControlShow'];
                            $showDescription = array(
                                'CONTROL' => $arRes['GetControlShow'],
                            );
                            if (isset($arRes['SORT']) && (int) $arRes['SORT'] > 0) {
                                $showDescription['SORT'] = (int) $arRes['SORT'];
                                $showDescription['INDEX'] = 1;
                            } else {
                                $showDescription['SORT'] = INF;
                                $showDescription['INDEX'] = $controlIndex;
                                $controlIndex++;
                            }
                            $rawControls[] = $showDescription;
                            unset($showDescription);
                        }
                    }
                    if (isset($arRes['InitParams']) && !empty($arRes['InitParams'])) {
                        if (!in_array($arRes['InitParams'], $this->arInitControlList))
                            $this->arInitControlList[] = $arRes['InitParams'];
                    }
                }
            }
            else {
                foreach ($arRes as &$arOneRes) {
                    if (is_array($arOneRes) && isset($arOneRes['ID'])) {
                        if (isset($arOneRes['EXIST_HANDLER']) && $arOneRes['EXIST_HANDLER'] === 'Y') {
                            if (!isset($arOneRes['MODULE_ID']) && !isset($arOneRes['EXT_FILE']))
                                continue;
                        }
                        else {
                            $arOneRes['MODULE_ID'] = '';
                            $arOneRes['EXT_FILE'] = '';
                        }
                        if (array_key_exists('EXIST_HANDLER', $arOneRes))
                            unset($arOneRes['EXIST_HANDLER']);
                        $arOneRes['GROUP'] = (isset($arOneRes['GROUP']) && $arOneRes['GROUP'] == 'Y' ? 'Y' : 'N');
                        if (isset($this->arControlList[$arOneRes['ID']])) {
                            $this->arMsg[] = array('id' => 'CONTROLS', 'text' => str_replace('#CONTROL#', $arOneRes['ID'], Loc::getMessage('BT_MOD_COND_ERR_CONTROL_DOUBLE')));
                            $this->boolError = true;
                        } else {
                            if (!$this->CheckControl($arOneRes))
                                continue;
                            $this->arControlList[$arOneRes['ID']] = $arOneRes;
                            if ($arOneRes['GROUP'] == 'Y') {
                                if (empty($arOneRes['FORCED_SHOW_LIST'])) {
                                    $this->arShowInGroups[] = $arOneRes['ID'];
                                } else {
                                    $forcedList = (!is_array($arOneRes['FORCED_SHOW_LIST']) ? array($arOneRes['FORCED_SHOW_LIST'])
                                          : $arOneRes['FORCED_SHOW_LIST']);
                                    foreach ($forcedList as &$forcedId) {
                                        if (is_array($forcedId))
                                            continue;
                                        $forcedId = trim($forcedId);
                                        if ($forcedId == '')
                                            continue;
                                        if (!isset($this->forcedShowInGroup[$forcedId]))
                                            $this->forcedShowInGroup[$forcedId] = array();
                                        $this->forcedShowInGroup[$forcedId][] = $arOneRes['ID'];
                                    }
                                    unset($forcedId);
                                }
                            }
                            if (isset($arOneRes['GetControlShow']) && !empty($arOneRes['GetControlShow'])) {
                                if (!in_array($arOneRes['GetControlShow'], $this->arShowControlList)) {
                                    $this->arShowControlList[] = $arOneRes['GetControlShow'];
                                    $showDescription = array(
                                        'CONTROL' => $arOneRes['GetControlShow'],
                                    );
                                    if (isset($arOneRes['SORT']) && (int) $arOneRes['SORT'] > 0) {
                                        $showDescription['SORT'] = (int) $arOneRes['SORT'];
                                        $showDescription['INDEX'] = 1;
                                    } else {
                                        $showDescription['SORT'] = INF;
                                        $showDescription['INDEX'] = $controlIndex;
                                        $controlIndex++;
                                    }
                                    $rawControls[] = $showDescription;
                                    unset($showDescription);
                                }
                            }
                            if (isset($arOneRes['InitParams']) && !empty($arOneRes['InitParams'])) {
                                if (!in_array($arOneRes['InitParams'], $this->arInitControlList))
                                    $this->arInitControlList[] = $arOneRes['InitParams'];
                            }
                        }
                    }
                }
                unset($arOneRes);
            }
        }
        unset($arRes);

        if (!empty($rawControls)) {
            $this->arShowControlList = array();
            \Bitrix\Main\Type\Collection::sortByColumn($rawControls, array('SORT' => SORT_ASC, 'INDEX' => SORT_ASC));
            foreach ($rawControls as $row)
                $this->arShowControlList[] = $row['CONTROL'];
            unset($row);
        }
        unset($controlIndex, $rawControls);
    }

    /**
     *
     */
    public function getConditionValues($arConditions)
    {
        $arResult = false;
        if (!$this->boolError) {
            if (!empty($arConditions) && is_array($arConditions)) {
                $arValues = array();
                $this->GetConditionValuesLevel($arConditions, $arValues, true);
                $arResult = $arValues;
            }
        }

        return $arResult;
    }

    /**
     *
     */
    public function getConditionValuesLevel(&$arLevel, &$arResult, $boolFirst = false)
    {
        $boolFirst = ($boolFirst === true);
        if (is_array($arLevel) && !empty($arLevel)) {
            if ($boolFirst) {
                if (isset($arLevel['CLASS_ID']) && !empty($arLevel['CLASS_ID'])) {
                    if (isset($this->arControlList[$arLevel['CLASS_ID']])) {
                        $arOneControl = $this->arControlList[$arLevel['CLASS_ID']];
                        if ('Y' == $arOneControl['GROUP']) {
                            if (call_user_func_array($arOneControl['ApplyValues'], array($arLevel['DATA'], $arLevel['CLASS_ID']))) {
                                $this->GetConditionValuesLevel($arLevel['CHILDREN'], $arResult, false);
                            }
                        } else {
                            $arCondInfo = call_user_func_array($arOneControl['ApplyValues'], array($arLevel['DATA'], $arLevel['CLASS_ID'])
                            );
                            if (!empty($arCondInfo) && is_array($arCondInfo)) {
                                if (!isset($arResult[$arLevel['CLASS_ID']]) || empty($arResult[$arLevel['CLASS_ID']]) || !is_array($arResult[$arLevel['CLASS_ID']])) {
                                    $arResult[$arLevel['CLASS_ID']] = $arCondInfo;
                                    $arResult[$arLevel['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']] = $arCondInfo['VALUES'];
                                } else {
                                    $arResult[$arLevel['CLASS_ID']]['VALUES'] = array_merge($arResult[$arLevel['CLASS_ID']]['VALUES'], $arCondInfo['VALUES']);
                                    $arResult[$arLevel['CLASS_ID']]['DISPLAY_VALUES'] = array_merge($arResult[$arLevel['CLASS_ID']]['DISPLAY_VALUES'], $arCondInfo['DISPLAY_VALUES']);
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($arLevel as &$arOneCondition) {
                    if (isset($arOneCondition['CLASS_ID']) && !empty($arOneCondition['CLASS_ID'])) {
                        if (isset($this->arControlList[$arOneCondition['CLASS_ID']])) {
                            $arOneControl = $this->arControlList[$arOneCondition['CLASS_ID']];
                            if ('Y' == $arOneControl['GROUP']) {
                                if (call_user_func_array($arOneControl['ApplyValues'], array($arOneCondition['DATA'], $arOneCondition['CLASS_ID']))) {
                                    $this->GetConditionValuesLevel($arOneCondition['CHILDREN'], $arResult, false);
                                }
                            } else {
                                $arCondInfo = call_user_func_array($arOneControl['ApplyValues'], array($arOneCondition['DATA'],
                                    $arOneCondition['CLASS_ID'])
                                );
                                if (!empty($arCondInfo) && is_array($arCondInfo)) {
                                    if (!isset($arResult[$arOneCondition['CLASS_ID']]) || empty($arResult[$arOneCondition['CLASS_ID']]) || !is_array($arResult[$arOneCondition['CLASS_ID']])) {
                                        $arResult[$arOneCondition['CLASS_ID']] = $arCondInfo;
                                        $arResult[$arOneCondition['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']] = [
                                            'LOGIC' => $arCondInfo['LOGIC'],
                                            'LOGIC_LABEL' => $arCondInfo['FILED_LOGIC'],
                                            'VALUES' => $arCondInfo['VALUES'],
                                            'DISPLAY_VALUES' => $arCondInfo['DISPLAY_VALUES'],
                                        ];
                                    } else {
                                        $arResult[$arOneCondition['CLASS_ID']]['VALUES'] = array_merge($arResult[$arOneCondition['CLASS_ID']]['VALUES'], $arCondInfo['VALUES']);
                                        $arResult[$arOneCondition['CLASS_ID']]['DISPLAY_VALUES'] = array_merge($arResult[$arOneCondition['CLASS_ID']]['DISPLAY_VALUES'], $arCondInfo['DISPLAY_VALUES']);

                                        if ($arResult[$arOneCondition['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']]) {
                                            $arResult[$arOneCondition['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']]['VALUES'] = array_merge($arResult[$arOneCondition['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']]['VALUES'], $arCondInfo['VALUES']);
                                            $arResult[$arOneCondition['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']]['DISPLAY_VALUES'] = array_merge($arResult[$arOneCondition['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']]['DISPLAY_VALUES'], $arCondInfo['DISPLAY_VALUES']);
                                        } else {
                                            $arResult[$arOneCondition['CLASS_ID']]['CONDITIONS'][$arCondInfo['LOGIC']] = [
                                                'LOGIC' => $arCondInfo['LOGIC'],
                                                'LOGIC_LABEL' => $arCondInfo['FILED_LOGIC'],
                                                'VALUES' => $arCondInfo['VALUES'],
                                                'DISPLAY_VALUES' => $arCondInfo['DISPLAY_VALUES']
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    unset(
                      $arResult[$arOneCondition['CLASS_ID']]['LOGIC'],
                      $arResult[$arOneCondition['CLASS_ID']]['FILED_LOGIC']
                    );
                }
                if (isset($arOneCondition))
                    unset($arOneCondition);
            }
        }
    }

    public function getItemTree(array $conditions)
    {
        $result = false;
        if (!$this->boolError) {
            if (!empty($conditions) && is_array($conditions)) {
                $values = array();
                $this->getItemTreeLevel($conditions, true);
            }
        }

        return [$conditions];
    }

    protected function getItemTreeLevel(&$conditionLevel, $isFirst = false)
    {
        $isFirst = ($isFirst === true);

        if(!$conditionLevel) {
            return;
        }

        if($isFirst) {
            if (!isset($conditionLevel['CLASS_ID']) || empty($conditionLevel['CLASS_ID'])) {
               return;
            }

            if (!isset($this->arControlList[$conditionLevel['CLASS_ID']])) {
               return;
            }

            $control = $this->arControlList[$conditionLevel['CLASS_ID']];

             if ('Y' == $control['GROUP']) {
                $controlApplyValues = call_user_func_array($control['getApplyControl'], [$conditionLevel['DATA'], $conditionLevel['CLASS_ID']]);
                $controlApplyValues['GROUP'] = 'Y';

                $this->addArrayItemIndexOf($conditionLevel, $controlApplyValues, 1);

                unset($conditionLevel['DATA']);

                $this->getItemTreeLevel($conditionLevel['CHILDREN'], false);
            }

            return;
        }

        foreach ($conditionLevel as &$_conditionLevel) {
            if (!isset($_conditionLevel['CLASS_ID']) || empty($_conditionLevel['CLASS_ID'])) {
               return;
            }

            if (!isset($this->arControlList[$_conditionLevel['CLASS_ID']])) {
               return;
            }

            $control = $this->arControlList[$_conditionLevel['CLASS_ID']];

            $controlApplyValues = call_user_func_array($control['getApplyControl'], [$_conditionLevel['DATA'], $_conditionLevel['CLASS_ID']]);
            $controlApplyValues['GROUP'] = $control['GROUP'];

            if(!$controlApplyValues) {
                return;
            }

            unset($_conditionLevel['DATA']);

            $this->addArrayItemIndexOf($_conditionLevel, $controlApplyValues, 1);

            if ('Y' == $control['GROUP']) {
               $this->getItemTreeLevel($_conditionLevel['CHILDREN'], false);
            }
        }
    }

    protected function addArrayItemIndexOf(&$toArray, $item, $indexOf)
    {
        $result = array_slice($toArray, 0, $indexOf, true) + $item + array_slice($toArray, $indexOf, count($toArray) - 1, true);
        $toArray = $result;
    }

}
