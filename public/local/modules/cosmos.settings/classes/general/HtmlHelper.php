<?php
/**
 * @see https://github.com/yiisoft/yii/blob/master/framework/web/helpers/CHtml.php
 */

namespace Cosmos;

class HtmlHelper
{
    const ID_PREFIX = 'cw';
    public static $renderSpecialAttributesValue = true;
    public static $closeSingleTags = true;

    public static function renderSettings($settingList)
    {
        foreach ($settingList as $name => $setting): ?>
            <?php if ($setting['type'] == 'separator'): ?>
                <tr class="heading"><td colspan="2"><?= $setting['label'] ?>:</td></tr>
            <?php elseif ($setting['type'] == 'file'): ?>
                <tr>
                    <td style="width: 40%;">
                        <?= self::tag('label', [], $setting['label']) ?>
                    </td>
                    <td>
                        <table border="0" cellspacing="2" cellpadding="2">
                            <tr>
                                <td><?php echo \CFile::InputFile('PROP[' . $name . ']', 20, $name); ?></td>
                                <?php $fileId = \COption::GetOptionString('cosmos.settings', $name); ?>
                                <?php if ( $fileId ) : ?>
                                    <? $info = \CFile::MakeFileArray($fileId); ?>
                                    <td align="right"><input type="checkbox" name="PROP[<?=$name;?>_del]" value="1">&nbsp; Удалить</td>
                                    <td>
                                        <a href="<?=\CFile::GetPath($fileId);?>" target="_blank"><?=$info['name'];?></a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td style="width: 40%;">
                        <?= self::tag('label', [], $setting['label']) ?>
                    </td>
                    <td>
                        <?php if ($setting['type'] == 'checkbox'): ?>
                            <?= self::checkBox('PROP[' . $name . ']', \COption::GetOptionString('cosmos.settings', $name)) ?>
                        <?php elseif ($setting['type'] == 'select'): ?>
                            <?= self::dropDownList('PROP[' . $name . ']', \COption::GetOptionString('cosmos.settings', $name), $setting['items'], ['empty' => '']) ?>
                        <?php elseif ($setting['type'] == 'textarea'): ?>
                            <?= self::textArea('PROP[' . $name . ']', \COption::GetOptionString('cosmos.settings', $name), $setting['htmlOptions'] ?? []) ?>
                        <?php else: ?>
                            <?= self::textField('PROP[' . $name . ']', \COption::GetOptionString('cosmos.settings', $name), $setting['htmlOptions'] ?? []) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach;
    }

    public static function link($text, $url = '#', $htmlOptions = array())
    {
        $htmlOptions['href'] = $url;

        return self::tag('a', $htmlOptions, $text);
    }

    public static function checkBox($name, $checked = false, $htmlOptions = array())
    {
        if ($checked)
            $htmlOptions['checked'] = 'checked';
        else
            unset($htmlOptions['checked']);
        $value = isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;

        if (array_key_exists('uncheckValue', $htmlOptions)) {
            $uncheck = $htmlOptions['uncheckValue'];
            unset($htmlOptions['uncheckValue']);
        } else
            $uncheck = null;

        if ($uncheck !== null) {
            // add a hidden field so that if the check box is not checked, it still submits a value
            if (isset($htmlOptions['id']) && $htmlOptions['id'] !== false)
                $uncheckOptions = array('id' => self::ID_PREFIX . $htmlOptions['id']);
            else
                $uncheckOptions = array('id' => false);
            if (!empty($htmlOptions['disabled']))
                $uncheckOptions['disabled'] = $htmlOptions['disabled'];
            $hidden = self::hiddenField($name, $uncheck, $uncheckOptions);
        } else
            $hidden = '';

        // add a hidden field so that if the check box is not checked, it still submits a value
        return $hidden . self::inputField('checkbox', $name, $value, $htmlOptions);
    }

    public static function textArea($name, $value = '', $htmlOptions = array())
    {
        $htmlOptions['name'] = $name;
        if (!isset($htmlOptions['id']))
            $htmlOptions['id'] = self::getIdByName($name);
        elseif ($htmlOptions['id'] === false)
            unset($htmlOptions['id']);
        return self::tag('textarea', $htmlOptions, isset($htmlOptions['encode']) && !$htmlOptions['encode'] ? $value : self::encode($value));
    }

    public static function textField($name, $value = '', $htmlOptions = array())
    {
        return self::inputField('text', $name, $value, $htmlOptions);
    }

    public static function listOptions($selection, $listData, &$htmlOptions)
    {
        $raw = isset($htmlOptions['encode']) && !$htmlOptions['encode'];
        $content = '';
        if (isset($htmlOptions['prompt'])) {
            $content .= '<option value="">' . strtr($htmlOptions['prompt'], array('<' => '&lt;', '>' => '&gt;')) . "</option>\n";
            unset($htmlOptions['prompt']);
        }
        if (isset($htmlOptions['empty'])) {
            if (!is_array($htmlOptions['empty']))
                $htmlOptions['empty'] = array('' => $htmlOptions['empty']);
            foreach ($htmlOptions['empty'] as $value => $label)
                $content .= '<option value="' . self::encode($value) . '">' . strtr($label, array('<' => '&lt;', '>' => '&gt;')) . "</option>\n";
            unset($htmlOptions['empty']);
        }

        if (isset($htmlOptions['options'])) {
            $options = $htmlOptions['options'];
            unset($htmlOptions['options']);
        } else
            $options = array();

        $key = isset($htmlOptions['key']) ? $htmlOptions['key'] : 'primaryKey';
        if (is_array($selection)) {
            foreach ($selection as $i => $item) {
                if (is_object($item))
                    $selection[$i] = $item->$key;
            }
        } elseif (is_object($selection))
            $selection = $selection->$key;

        foreach ($listData as $key => $value) {
            if (is_array($value)) {
                $content .= '<optgroup label="' . ($raw ? $key : self::encode($key)) . "\">\n";
                $dummy = array('options' => $options);
                if (isset($htmlOptions['encode']))
                    $dummy['encode'] = $htmlOptions['encode'];
                $content .= self::listOptions($selection, $value, $dummy);
                $content .= '</optgroup>' . "\n";
            } else {
                $attributes = array('value' => (string)$key, 'encode' => !$raw);
                if (!is_array($selection) && !strcmp($key, $selection) || is_array($selection) && in_array($key, $selection))
                    $attributes['selected'] = 'selected';
                if (isset($options[$key]))
                    $attributes = array_merge($attributes, $options[$key]);
                $content .= self::tag('option', $attributes, $raw ? (string)$value : self::encode((string)$value)) . "\n";
            }
        }

        unset($htmlOptions['key']);

        return $content;
    }

    public static function dropDownList($name, $select, $data, $htmlOptions = array())
    {
        if (empty($htmlOptions['name'])) {
            $htmlOptions['name'] = $name;
        }

        if (!isset($htmlOptions['id']))
            $htmlOptions['id'] = self::getIdByName($name);
        elseif ($htmlOptions['id'] === false)
            unset($htmlOptions['id']);

        $options = "\n" . self::listOptions($select, $data, $htmlOptions);
        $hidden = '';

        if (!empty($htmlOptions['multiple'])) {
            if (substr($htmlOptions['name'], -2) !== '[]')
                $htmlOptions['name'] .= '[]';

            if (isset($htmlOptions['unselectValue'])) {
                $hiddenOptions = isset($htmlOptions['id']) ? array('id' => self::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
                if (!empty($htmlOptions['disabled']))
                    $hiddenOptions['disabled'] = $htmlOptions['disabled'];
                $hidden = self::hiddenField(substr($htmlOptions['name'], 0, -2), $htmlOptions['unselectValue'], $hiddenOptions);
                unset($htmlOptions['unselectValue']);
            }
        }
        // add a hidden field so that if the option is not selected, it still submits a value
        return $hidden . self::tag('select', $htmlOptions, $options);
    }

    public static function getIdByName($name)
    {
        return str_replace(array('[]', '][', '[', ']', ' '), array('', '_', '_', '', '_'), $name);
    }

    public static function hiddenField($name, $value = '', $htmlOptions = array())
    {
        return self::inputField('hidden', $name, $value, $htmlOptions);
    }

    public static function tag($tag, $htmlOptions = array(), $content = false, $closeTag = true)
    {
        $html = '<' . $tag . self::renderAttributes($htmlOptions);
        if ($content === false)
            return $closeTag && self::$closeSingleTags ? $html . ' />' : $html . '>';
        else
            return $closeTag ? $html . '>' . $content . '</' . $tag . '>' : $html . '>' . $content;
    }

    public static function renderAttributes($htmlOptions)
    {
        static $specialAttributes = array(
          'autofocus' => 1,
          'autoplay' => 1,
          'async' => 1,
          'checked' => 1,
          'controls' => 1,
          'declare' => 1,
          'default' => 1,
          'defer' => 1,
          'disabled' => 1,
          'formnovalidate' => 1,
          'hidden' => 1,
          'ismap' => 1,
          'itemscope' => 1,
          'loop' => 1,
          'multiple' => 1,
          'muted' => 1,
          'nohref' => 1,
          'noresize' => 1,
          'novalidate' => 1,
          'open' => 1,
          'readonly' => 1,
          'required' => 1,
          'reversed' => 1,
          'scoped' => 1,
          'seamless' => 1,
          'selected' => 1,
          'typemustmatch' => 1,
        );

        if ($htmlOptions === array())
            return '';

        $html = '';
        if (isset($htmlOptions['encode'])) {
            $raw = !$htmlOptions['encode'];
            unset($htmlOptions['encode']);
        } else
            $raw = false;

        foreach ($htmlOptions as $name => $value) {
            if (isset($specialAttributes[$name])) {
                if ($value === false && $name === 'async') {
                    $html .= ' ' . $name . '="false"';
                } elseif ($value) {
                    $html .= ' ' . $name;
                    if (self::$renderSpecialAttributesValue)
                        $html .= '="' . $name . '"';
                }
            } elseif ($value !== null)
                $html .= ' ' . $name . '="' . ($raw ? $value : self::encode($value)) . '"';
        }

        return $html;
    }

    public static function encode($text)
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }

    protected static function inputField($type, $name, $value, $htmlOptions)
    {
        $htmlOptions['type'] = $type;
        $htmlOptions['value'] = $value;
        $htmlOptions['name'] = $name;
        if (!isset($htmlOptions['id']))
            $htmlOptions['id'] = self::getIdByName($name);
        elseif ($htmlOptions['id'] === false)
            unset($htmlOptions['id']);
        return self::tag('input', $htmlOptions);
    }

    public static function openTag($tag, $htmlOptions = array())
    {
        return '<' . $tag . self::renderAttributes($htmlOptions) . '>';
    }

    public static function closeTag($tag)
    {
        return '</' . $tag . '>';
    }

    public static function cdata($text)
    {
        return '<![CDATA[' . $text . ']]>';
    }
}
?>
