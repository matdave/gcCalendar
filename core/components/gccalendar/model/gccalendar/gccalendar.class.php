<?php

class GcCalendar
{
    public $modx;
    public $config = array();
    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $basePath = $this->modx->getOption(
            'gccalendar.core_path',
            $config,
            $this->modx->getOption('core_path').'components/gccalendar/'
        );
        $assetsUrl = $this->modx->getOption(
            'gccalendar.assets_url',
            $config,
            $this->modx->getOption('assets_url').'components/gccalendar/'
        );
        $managerURL = $this->modx->getOption('manager_url');
        $this->config = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'managerPath' => $managerURL,
            'modelPath' => $basePath.'model/',
            'processorsPath' => $basePath.'processors/',
            'templatesPath' => $basePath.'templates/',
            'chunksPath' => $basePath.'elements/chunks/',
            'jsUrl' => $assetsUrl.'js/',
            'cssUrl' => $assetsUrl.'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl.'connector.php',
        ), $config);
        $this->modx->addPackage('gccalendar', $this->config['modelPath']);
    }
}
