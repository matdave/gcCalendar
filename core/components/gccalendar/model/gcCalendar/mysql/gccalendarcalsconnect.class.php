<?php
/**
 * @package gcCalendar
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/gccalendarcalsconnect.class.php');
class GcCalendarCalsConnect_mysql extends GcCalendarCalsConnect {}
?>