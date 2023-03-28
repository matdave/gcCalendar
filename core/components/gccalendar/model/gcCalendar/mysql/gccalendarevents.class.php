<?php
/**
 * @package gcCalendar
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/gccalendarevents.class.php');
class GcCalendarEvents_mysql extends GcCalendarEvents {}
?>