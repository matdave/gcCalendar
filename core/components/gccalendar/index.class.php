<?php
require_once dirname(__FILE__) . '/model/gccalendar/gccalendar.class.php';

abstract class GcCalendarManagerController extends modExtraManagerController
{
    /** @var GcCalendar $gcc */
    public $gcc;
    public function initialize()
    {
        $this->gcc = new GcCalendar($this->modx);
        $this->addCss($this->gcc->config['cssUrl'].'mgr.css');
        $this->addJavascript($this->gcc->config['jsUrl'].'mgr/gccalendar.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            gcCalendar.config = '.$this->modx->toJSON($this->gcc->config).';
        });
        
         Ext.ns("TinyMCERTE");
            TinyMCERTE.editorConfig = {
    "plugins": "advlist autolink lists charmap print preview anchor visualblocks searchreplace code fullscreen insertdatetime media table contextmenu paste link",
    "toolbar1": "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link",
    "toolbar2": "",
    "toolbar3": "",
    "modxlinkSearch": "\/assets\/components\/tinymcerte\/js\/vendor\/tinymce\/plugins\/modxlink\/search.php",
    "language": "en",
    "directionality": "ltr",
    "menubar": "file edit insert view format table tools",
    "statusbar": true,
    "image_advtab": true,
    "paste_as_text": false,
    "style_formats_merge": false,
    "object_resizing": true,
    "link_class_list": "",
    "browser_spellcheck": false,
    "content_css": [],
    "image_class_list": "",
    "skin": "modx",
    "relative_urls": true,
    "remove_script_host": true,
    "entity_encoding": "named",
    "branding": false,
    "style_formats": [
        {
            "title": "Headers",
            "items": [
                {
                    "title": "Header 1",
                    "format": "h1"
                },
                {
                    "title": "Header 2",
                    "format": "h2"
                },
                {
                    "title": "Header 3",
                    "format": "h3"
                },
                {
                    "title": "Header 4",
                    "format": "h4"
                },
                {
                    "title": "Header 5",
                    "format": "h5"
                },
                {
                    "title": "Header 6",
                    "format": "h6"
                }
            ]
        },
        {
            "title": "Inline",
            "items": [
                {
                    "title": "Bold",
                    "icon": "bold",
                    "format": "bold"
                },
                {
                    "title": "Italic",
                    "icon": "italic",
                    "format": "italic"
                },
                {
                    "title": "Underline",
                    "icon": "underline",
                    "format": "underline"
                },
                {
                    "title": "Strikethrough",
                    "icon": "strikethrough",
                    "format": "strikethrough"
                },
                {
                    "title": "Superscript",
                    "icon": "superscript",
                    "format": "superscript"
                },
                {
                    "title": "Subscript",
                    "icon": "subscript",
                    "format": "subscript"
                },
                {
                    "title": "Code",
                    "icon": "code",
                    "format": "code"
                }
            ]
        },
        {
            "title": "Blocks",
            "items": [
                {
                    "title": "Paragraph",
                    "format": "p"
                },
                {
                    "title": "Blockquote",
                    "format": "blockquote"
                },
                {
                    "title": "Div",
                    "format": "div"
                },
                {
                    "title": "Pre",
                    "format": "pre"
                }
            ]
        },
        {
            "title": "Alignment",
            "items": [
                {
                    "title": "Left",
                    "icon": "alignleft",
                    "format": "alignleft"
                },
                {
                    "title": "Center",
                    "icon": "aligncenter",
                    "format": "aligncenter"
                },
                {
                    "title": "Right",
                    "icon": "alignright",
                    "format": "alignright"
                },
                {
                    "title": "Justify",
                    "icon": "alignjustify",
                    "format": "alignjustify"
                }
            ]
        }
    ]
};
            Ext.onReady(function(){
                TinyMCERTE.loadForTVs();
            });
        
        </script>');
        return parent::initialize();
    }
    public function getLanguageTopics()
    {
        return array('gccalendar:default');
    }
    public function checkPermissions()
    {
        return true;
    }
}
class IndexManagerController extends GcCalendarManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}
