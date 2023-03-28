<?php
/**
 * @package gcCalendar
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/gccalendardates.class.php');
class GcCalendarDates_mysql extends GcCalendarDates {}
?>