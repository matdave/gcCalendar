<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';
//$modx = new modX();
//$modx->initialize('mgr');

$corePath = $modx->getOption('gccalendar.core_path', null, $modx->getOption('core_path') . 'components/gccalendar/');
require_once $corePath . 'model/gccalendar/gccalendar.class.php';
$modx->gcCalendar = new GcCalendar($modx);

$modx->lexicon->load('gccalendar:default');

/* handle request */
$path = $modx->getOption('processorsPath', $modx->gcCalendar->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));
