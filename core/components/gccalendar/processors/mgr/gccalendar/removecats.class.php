<?php
class GcCalendarRemoveCategoryProcessor extends modObjectRemoveProcessor {
    public $classKey = 'GcCalendarCats';
    public $languageTopics = array('gccalendar:default');
    public $objectType = 'gccalendar.GcCalendarCats';
}
return 'GcCalendarRemoveCategoryProcessor';