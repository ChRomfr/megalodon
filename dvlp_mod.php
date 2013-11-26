<?php
$registry->smarty->assign('registry',$registry);
$registry->smarty->assign('infosdev',$registry->smarty->fetch(ROOT_PATH . 'themes' . DS . $config->config['theme'] . DS . 'dvlp.tpl'));