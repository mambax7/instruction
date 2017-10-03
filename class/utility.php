<?php

use Xmf\Request;

require_once __DIR__ . '/common/traitversionchecks.php';
require_once __DIR__ . '/common/traitserverstats.php';
require_once __DIR__ . '/common/traitfilesmgmt.php';

require_once __DIR__ . '/../include/common.php';

/**
 * Class InstructionUtility
 */
class InstructionUtility
{
    use VersionChecks; //checkVerXoops, checkVerPhp Traits

    use ServerStats; // getServerStats Trait

    use FilesManagement; // Files Management Trait

    // Права
    public static function getItemIds($permtype = 'instruction_view')
    {
        //global $xoopsUser;
        static $permissions = [];
        // Если есть в статике
        if (is_array($permissions) && array_key_exists($permtype, $permissions)) {
            return $permissions[$permtype];
        }
        // Находим из базы
        $moduleHandler          = xoops_getHandler('module');
        $instrModule            = $moduleHandler->getByDirname('instruction');
        $groups                 = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        $gpermHandler           = xoops_getHandler('groupperm');
        $categories             = $gpermHandler->getItemIds($permtype, $groups, $instrModule->getVar('mid'));
        $permissions[$permtype] = $categories;
        return $categories;
    }

    // Редактор
    public static function getWysiwygForm($caption, $name, $value = '')
    {
        $editor                   = false;
        $editor_configs           = [];
        $editor_configs['name']   = $name;
        $editor_configs['value']  = $value;
        $editor_configs['rows']   = 35;
        $editor_configs['cols']   = 60;
        $editor_configs['width']  = '100%';
        $editor_configs['height'] = '350px';
        $editor_configs['editor'] = strtolower(xoops_getModuleOption('form_options', 'instruction'));

        $editor = new XoopsFormEditor($caption, $name, $editor_configs);
        return $editor;
    }

    // Получение значения переменной, переданной через GET или POST запрос
    public static function cleanVars(&$global, $key, $default = '', $type = 'int')
    {
        switch ($type) {
            case 'string':
                $ret = isset($global[$key]) ? $global[$key] : $default;
                break;
            case 'int':
            default:
                $ret = isset($global[$key]) ? (int)$global[$key] : (int)$default;
                break;
        }
        if (false === $ret) {
            return $default;
        }
        return $ret;
    }
}
