<?php

/**
 * CLASS: View Robot
 * a simple robot that can change certain HTML elements such as
 * hidden and disabled tags. It can also display any POST data that
 * may or may not be set.
 */
class ViewRobot {
    public static function toggle_attribute($attribute_string, $is_set) {
        if ($is_set == true) {
            return $attribute_string;
        } else {
            return '';
        }
    }

    public static function get_post_data($post_id_string) {
        if (isset($_POST[$post_id_string])) {
            echo $_POST[$post_id_string];
        }
    }
}
