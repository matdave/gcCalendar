<?php
// get the index.class.php file
require_once dirname(__FILE__, 2) . '/index.class.php';
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

        $tRTEcorePath = $this->modx->getOption('tinymcerte.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/tinymcerte/');
        /** @var TinyMCERTE $tinymcerte */
        $tinymcerte = $this->modx->getService(
            'tinymcerte',
            'TinyMCERTE',
            $tRTEcorePath . 'model/tinymcerte/',
            [
                'core_path' => $tRTEcorePath
            ]
        );

        if ($tinymcerte) {
            /** @var TinyMCERTEPlugin $handler */
            $options = [
                'plugins' => $tinymcerte->getOption('plugins', [], 'advlist autolink lists charmap print preview anchor visualblocks searchreplace code fullscreen insertdatetime media table paste modximage'),
                'toolbar1' => $tinymcerte->getOption('toolbar1', [], 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'),
                'toolbar2' => $tinymcerte->getOption('toolbar2', [], ''),
                'toolbar3' => $tinymcerte->getOption('toolbar3', [], ''),
                'connector_url' => $tinymcerte->getOption('connectorUrl'),
                'language' => $tinymcerte->getLanguageCode($this->modx->getOption('manager_language')),
                'directionality' => $this->modx->getOption('manager_direction', [], 'ltr'),
                'menubar' => $tinymcerte->getOption('menubar', [], 'file edit insert view format table tools'),
                'statusbar' => $tinymcerte->getOption('statusbar', [], 1) == 1,
                'image_advtab' => $tinymcerte->getOption('image_advtab', [], true) == 1,
                'paste_as_text' => $tinymcerte->getOption('paste_as_text', [], false) == 1,
                'style_formats_merge' => $tinymcerte->getOption('style_formats_merge', [], false) == 1,
                'object_resizing' => $tinymcerte->getOption('object_resizing', [], '1'),
                'link_class_list' => json_decode($tinymcerte->getOption('link_class_list', [], '[]'), true),
                'browser_spellcheck' => $tinymcerte->getOption('browser_spellcheck', [], false) == 1,
                'content_css' => $tinymcerte->explodeAndClean($tinymcerte->getOption('content_css', [], '')),
                'image_class_list' => json_decode($tinymcerte->getOption('image_class_list', [], '[]'), true),
                'skin' => $tinymcerte->getOption('skin', [], 'modx'),
                'relative_urls' => $tinymcerte->getOption('relative_urls', [], true) == 1,
                'document_base_url' => $this->modx->getOption('site_url'),
                'remove_script_host' => $tinymcerte->getOption('remove_script_host', [], true) == 1,
                'entity_encoding' => $tinymcerte->getOption('entity_encoding', [], 'named'),
                'enable_link_list' => $tinymcerte->getOption('enable_link_list', [], true) == 1,
                'branding' => $tinymcerte->getOption('branding', [], false) == 1,
                'cache_suffix' => '?v=' . $tinymcerte->version,
                'templates' => null
            ];

            $externalConfig = $tinymcerte->getOption('external_config');
            if (!empty($externalConfig)) {
                $externalConfig = str_replace([
                    '{base_path}',
                    '{core_path}',
                    '{assets_path}',
                ], [
                    $this->modx->getOption('base_path'),
                    $this->modx->getOption('core_path'),
                    $this->modx->getOption('assets_path'),
                ], $externalConfig);
                if (file_exists($externalConfig) && is_readable($externalConfig)) {
                    $externalConfig = file_get_contents($externalConfig);
                    $externalConfig = json_decode($externalConfig, true);
                    if (is_array($externalConfig)) {
                        $options = array_replace_recursive($options, $externalConfig);
                    }
                }
            }

            $this->addHtml('<script type="text/javascript">
                Ext.ns("TinyMCERTE");
                TinyMCERTE.editorConfig = ' . json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ';
                Ext.onReady(function(){
                    TinyMCERTE.loadForTVs();
                });
            </script>');
            $this->addJavascript($tinymcerte->getOption('jsUrl') . 'vendor/tinymce/tinymce.min.js?v=' . $tinymcerte->version);
            $this->addJavascript($tinymcerte->getOption('jsUrl') . 'mgr/tinymcerte.min.js?v=' . $tinymcerte->version);
            $this->addCss($tinymcerte->getOption('cssUrl') . 'mgr/tinymcerte.css?v=' . $tinymcerte->version);
        }
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/utils/CheckColumn.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/cust/window.dates.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/cust/grid.dates.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.window.create.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.window.update.js');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/widgets/gccalendar.grid.js');
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
