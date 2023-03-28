<?php
/**
 * @package gcCalendar
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/gccalendarcals.class.php');
class GcCalendarCals_mysql extends GcCalendarCals {}
?>