<?php
/**
 * @package gcCalendar
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/gccalendarcats.class.php');
class GcCalendarCats_mysql extends GcCalendarCats {}
?>