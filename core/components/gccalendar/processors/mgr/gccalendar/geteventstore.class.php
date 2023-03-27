<?php
class GcCalendarGetEventsProcessor extends modObjectGetListProcessor {
    public $classKey = 'GcCalendarEvents';
    public $languageTopics = array('gccalendar:default');
    public $defaultSortField = 'start';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'gccalendar.GcCalendarEvents';
    public $limit = 9999;

}
return 'GcCalendarGetEventsProcessor';
