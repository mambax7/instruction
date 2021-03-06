<?php

use Xmf\Request;
use Xoopsmodules\instruction;

require_once __DIR__ . '/header.php';
// Подключаем трей
include_once __DIR__ . '/class/Tree.php';

// Объявляем объекты
//$instructionHandler = xoops_getModuleHandler('instruction', 'instruction');
//$categoryHandler   = xoops_getModuleHandler('category', 'instruction');
//$pageHandler  = xoops_getModuleHandler('page', 'instruction');

$instrid = Request::getInt('id', 0, 'GET');

// Существует ли такая инструкция
$criteria = new \CriteriaCompo();
$criteria->add(new \Criteria('instrid', $instrid));
$criteria->add(new \Criteria('status ', '0', '>'));
if (0 == $instructionHandler->getCount($criteria)) {
    redirect_header('index.php', 3, _MD_INSTRUCTION_INSTRNOTEXIST);
    exit();
}
//
unset($criteria);

// Находим данные об инструкции
$objInsinstr = $instructionHandler->get($instrid);

// Задание тайтла
$xoopsOption['xoops_pagetitle'] = $GLOBALS['xoopsModule']->name() . ' - ' . $objInsinstr->getVar('title');
// Шаблон
$GLOBALS['xoopsOption']['template_main'] = $moduleDirName . '_instr.tpl';
// Заголовок
include_once $GLOBALS['xoops']->path('header.php');
// Стили
$xoTheme->addStylesheet(XOOPS_URL . '/modules/' . $moduleDirName . '/assets/css/style.css');
// Скрипты
$xoTheme->addScript(XOOPS_URL . '/modules/' . $moduleDirName . '/assets/js/tree.js');

// Права на просмотр инструкции
$categories = Xoopsmodules\instruction\Utility::getItemIds();
if (!in_array($objInsinstr->getVar('cid'), $categories)) {
    redirect_header(XOOPS_URL . '/modules/' . $moduleDirName . '/', 3, _NOPERM);
    exit();
}

// Массив данных об инструкции
$instrs = [];
// ID инструкции
$instrs['instrid'] = $objInsinstr->getVar('instrid');
// Название страницы
$instrs['title'] = $objInsinstr->getVar('title');
// Описание
$instrs['description'] = $objInsinstr->getVar('description');
// Если админ, рисуем админлинк
if (($GLOBALS['xoopsUser'] instanceof \XoopsUser) && $GLOBALS['xoopsUser']->isAdmin($GLOBALS['xoopsModule']->mid())) {
    $instrs['adminlink'] = '&nbsp;<a href="'
                           . XOOPS_URL
                           . '/modules/'
                           . $moduleDirName
                           . '/admin/instr.php?op=editinstr&instrid='
                           . $instrid
                           . '"><img style="width:16px;" src="'. $pathIcon16 . '/edit.png" alt='
                           . _EDIT
                           . ' title='
                           . _EDIT
                           . '></a>&nbsp;<a href="'
                           . XOOPS_URL
                           . '/modules/'
                           . $moduleDirName
                           . '/admin/instr.php?op=delinstr&instrid='
                           . $instrid
                           . '"><img style="width:16px;" src="'. $pathIcon16 . '/delete.png" alt='
                           . _DELETE
                           . ' title='
                           . _DELETE
                           . '></a>&nbsp;';
} else {
    $instrs['adminlink'] = '';
}

// Выводим в шаблон
$GLOBALS['xoopsTpl']->assign('insInstr', $instrs);

// Мета теги
$xoTheme->addMeta('meta', 'keywords', $objInsinstr->getVar('metakeywords'));
$xoTheme->addMeta('meta', 'description', $objInsinstr->getVar('metadescription'));

// Находим данные об категории
$objInscat = $categoryHandler->get($objInsinstr->getVar('cid'));

// Навигация
$criteria = new \CriteriaCompo();
$criteria->setSort('weight ASC, title');
$criteria->setOrder('ASC');
$inscat_arr    = $categoryHandler->getall($criteria);
$mytree        = new \XoopsObjectTree($inscat_arr, 'cid', 'pid');
$nav_parent_id = $mytree->getAllParent($objInsinstr->getVar('cid'));
$titre_page    = $nav_parent_id;
$nav_parent_id = array_reverse($nav_parent_id);
$navigation    = '<a href="' . XOOPS_URL . '/modules/' . $moduleDirName . '/">' . $GLOBALS['xoopsModule']->name() . '</a>&nbsp;:&nbsp;';
foreach (array_keys($nav_parent_id) as $i) {
    $navigation .= '<a href="' . XOOPS_URL . '/modules/' . $moduleDirName . '/index.php?cid=' . $nav_parent_id[$i]->getVar('cid') . '">' . $nav_parent_id[$i]->getVar('title') . '</a>&nbsp;:&nbsp;';
}
$navigation .= '<a href="' . XOOPS_URL . '/modules/' . $moduleDirName . '/index.php?cid=' . $objInscat->getVar('cid') . '">' . $objInscat->getVar('title') . '</a>&nbsp;:&nbsp;';
$navigation .= $objInsinstr->getVar('title');
$xoopsTpl->assign('insNav', $navigation);
//
unset($criteria);

// Список страниц в данной инструкции
$criteria = new \CriteriaCompo();
$criteria->add(new \Criteria('instrid', $instrid, '='));
$criteria->add(new \Criteria('status ', '0', '>'));
$criteria->setSort('weight');
$criteria->setOrder('ASC');
$ins_page = $pageHandler->getall($criteria);
unset($criteria);
// Инициализируем
$instree = new Xoopsmodules\instruction\Tree($ins_page, 'pageid', 'pid');
// Выводим список страниц в шаблон
$GLOBALS['xoopsTpl']->assign('insListPage', $instree->makePagesUser());

// Языковые константы
$xoopsTpl->assign('lang_listpages', _MD_INSTRUCTION_LISTPAGES);
$xoopsTpl->assign('lang_menu', _MD_INSTRUCTION_MENU);

// Теги
if (xoops_getModuleOption('usetag', 'instruction')) {
    include_once $GLOBALS['xoops']->path('modules/tag/include/tagbar.php');
    $xoopsTpl->assign('tags', true);
    $xoopsTpl->assign('tagbar', tagBar($instrid, 0));
} else {
    $xoopsTpl->assign('tags', false);
}

// Рейтинг
if (xoops_getModuleOption('userat', 'instruction')) {
    $xoopsTpl->assign('insUserat', true);
} else {
    $xoopsTpl->assign('insUserat', false);
}

// Подвал
include_once $GLOBALS['xoops']->path('footer.php');
