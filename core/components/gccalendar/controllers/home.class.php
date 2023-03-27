<?php
class GcCalendarHomeManagerController extends GcCalendarManagerController
{
    public function process(array $scriptProperties = array())
    {
    }
    public function getPageTitle()
    {
        return $this->modx->lexicon('gccalendar');
    }
    public function loadCustomCssJs()
    {
        $this->addJavascript($this->gcc->config['managerPath'].'assets/modext/util/datetime.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'ensible/Extensible-config.js');
        /* If we want to use Tiny, we'll need some extra files. */

        $tRTEcorePath = $this->modx->getOption(
            'tinymcerte.core_path',
            null,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/tinymcerte/'
        );
        /** @var TinyMCERTE $tinymcerte */
        $tinymcerte = $this->modx->getService(
            'tinymcerte',
            'TinyMCERTE',
            $tRTEcorePath . 'model/tinymcerte/',
            array(
                'core_path' => $tRTEcorePath
            )
        );

        $className = 'TinyMCERTEOnRichTextEditorInit';
        $this->modx->loadClass(
            'TinyMCERTEPlugin',
            $tinymcerte->getOption('modelPath') . 'tinymcerte/events/',
            true,
            true
        );
        $this->modx->loadClass($className, $tinymcerte->getOption('modelPath') . 'tinymcerte/events/', true, true);
        if (class_exists($className)) {
            /** @var TinyMCERTEPlugin $handler */
            $handler = new $className($this->modx, $this->scriptProperties);
            $handler->run();
        }
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/utils/CheckColumn.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/cust/window.dates.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/cust/grid.dates.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.grid.js?v=20150109');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.calendars.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.categories.js');
        if ($this->modx->user->isMember('Administrator')) {
            $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.ensible.dev.js');
        } else {
            $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.ensible.js');
        }
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->gcc->config['jsUrl'].'mgr/sections/index.js');
    }
    public function getTemplateFile()
    {
        return $this->gcc->config['templatesPath'].'home.tpl';
    }
}
