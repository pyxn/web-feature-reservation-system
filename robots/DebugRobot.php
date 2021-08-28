<?php

/**
 * CLASS: DebugRobot
 * a robot used specificall for debugging. It outputs preformatted code
 * useful for visualizing nested objects and arrays.
 */
class DebugRobot {

    /**
     * Prints a preformatted version of any kind of variable or object.
     */
    public static function print_preformatted($variable) {
        echo "<pre>";
        print_r($variable);
        echo "</pre>";
    }
}
