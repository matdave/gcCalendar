<?php
class GcCalendarGetCalendarProcessor extends modObjectGetListProcessor {
    public $classKey = 'GcCalendarCals';
    public $languageTopics = array('gccalendar:default');
    public $defaultSortField = 'title';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'gccalendar.GcCalendarCals';

}
return 'GcCalendarGetCalendarProcessor';
