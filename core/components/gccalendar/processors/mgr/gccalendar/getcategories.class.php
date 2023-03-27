<?php
class GcCalendarGetCategoryProcessor extends modObjectGetListProcessor {
    public $classKey = 'GcCalendarCats';
    public $languageTopics = array('gccalendar:default');
    public $defaultSortField = 'ctitle';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'gccalendar.GcCalendarCats';

}
return 'GcCalendarGetCategoryProcessor';
