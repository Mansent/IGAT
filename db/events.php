<?php
/**
 * Registers the event observers for the igat plugin.
 */
$observers = array(
    array(
        'eventname'   => '\block_xp\event\user_leveledup',
        'callback'    => '\block_igat\event_processor::user_level_up',
    )
);
?>