<?php

require_once 'DatabaseRobot.php';

/**
 * CLASS: Reservation Robot
 * a conceptual robot that handles all tasks related to making reservations
 * and getting information from these reservations to and from the database.
 */
class ReservationRobot {

    // Subdivide all the reservations by day because
    // The associative array returned by the database is
    // indexed by INT and not by the alphanumeric keys 
    // the timeslot records.
    private static array $all_reservations_on_mon;
    private static array $all_reservations_on_tue;
    private static array $all_reservations_on_wed;
    private static array $all_reservations_on_thu;
    private static array $all_reservations_on_fri;

    // Data of all reservations that have an associated student to them
    private static array $all_reservations_reserved;

    /**
     * Updates all static variables at the start of every form this robot is used
     * from the timeslots table.
     */
    public static function update_reservation_data() {
        self::$all_reservations_on_mon = self::get_reservations_on_day('Monday');
        self::$all_reservations_on_tue = self::get_reservations_on_day('Tuesday');
        self::$all_reservations_on_wed = self::get_reservations_on_day('Wednesday');
        self::$all_reservations_on_thu = self::get_reservations_on_day('Thursday');
        self::$all_reservations_on_fri = self::get_reservations_on_day('Friday');
        self::$all_reservations_reserved = self::get_reservations_reserved();
    }

    /**
     * Gets all reservations from the database, regardless if they are reserved or not.
     */
    public static function get_reservations() {
        $sql_query_reservations = <<<SQL_QUERY_GET_RESERVATIONS
        SELECT  * 
        FROM    table_timeslots
        SQL_QUERY_GET_RESERVATIONS;
        return DatabaseRobot::get($sql_query_reservations);
    }

    /**
     * Gets reservations based on a specific day
     */
    public static function get_reservations_on_day($day) {
        $sql_query_get_reservations_on_day = <<<SQL_QUERY_GET_RESERVATIONS_ON_DAY
        SELECT *
        FROM table_timeslots
        WHERE timeslot_day = '$day'
        ORDER BY timeslot_id;
        SQL_QUERY_GET_RESERVATIONS_ON_DAY;
        return DatabaseRobot::get($sql_query_get_reservations_on_day);
    }

    /**
     * Gets reservations that have been reserved by a student
     */
    public static function get_reservations_reserved() {
        $sql_query_reservations_reserved = <<<SQL_QUERY_GET_RESERVATIONS_RESERVED
        SELECT table_timeslots.timeslot_id,
            table_students.student_firstname,
            table_students.student_lastname,
            table_timeslots.timeslot_day,
            table_timeslots.timeslot_start,
            table_timeslots.timeslot_end,
            table_timeslots.timeslot_reserved_id,
            table_students.student_id
        FROM table_students
            INNER JOIN table_timeslots ON table_students.student_id = table_timeslots.timeslot_reserved_id
        ORDER BY timeslot_id;
        SQL_QUERY_GET_RESERVATIONS_RESERVED;
        return DatabaseRobot::get($sql_query_reservations_reserved);
    }

    /**
     * Reserves a timeslot by associating a student ID with it
     */
    public static function reserve_timeslot($timeslot_id, $student_id) {
        if (ReservationRobot::has_reservation($student_id)) {
            return;
        }
        $reservation_sql_query = <<<SQL_QUERY_RESERVE_TIMESLOT
        UPDATE table_timeslots
        SET    timeslot_reserved_id = $student_id
        WHERE  timeslot_id = $timeslot_id AND timeslot_reserved_id IS NULL;
        SQL_QUERY_RESERVE_TIMESLOT;
        DatabaseRobot::set($reservation_sql_query);
    }

    /**
     * Deletes a timeslot reservation by resetting it's associated student
     * reservation ID to NULL.
     */
    public static function delete_reservation($student_id) {
        $sql_query_delete_reservation = <<<SQL_QUERY_DELETE_RESERVATION
        UPDATE  table_timeslots
        SET     timeslot_reserved_id = NULL
        WHERE   timeslot_reserved_id = $student_id;
        SQL_QUERY_DELETE_RESERVATION;
        DatabaseRobot::set($sql_query_delete_reservation);
    }

    /**
     * Returns a boolean value depending on if a timeslot is
     * reserved or not. This is used to return from uneccesary queries early.
     */
    public static function is_free_timeslot($timeslot_id) {
        $sql_query_is_free_timeslot = <<<SQL_QUERY_IS_FREE_TIMESLOT
        SELECT *
        FROM table_timeslots
        WHERE EXISTS (
                SELECT *
                FROM table_timeslots
                WHERE table_timeslots.timeslot_id = $timeslot_id
                    AND table_timeslots.timeslot_reserved_id IS NOT NULL
            )
        LIMIT 1;
        SQL_QUERY_IS_FREE_TIMESLOT;
        $result_rows = DatabaseRobot::get($sql_query_is_free_timeslot);
        $count = count($result_rows);
        if ($count == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a boolean value depending on if a provided student has
     * reserved a specific timeslot on the database. Like the is_free_timeslot(), 
     * this is used for controlling when certain database operations should happen or not.
     */
    public static function has_reservation($student_id) {
        $sql_query_has_reservation = <<<SQL_QUERY_HAS_RESERVATION
        SELECT * FROM table_timeslots
        WHERE NOT EXISTS (SELECT * FROM table_timeslots WHERE table_timeslots.timeslot_reserved_id = $student_id) 
        LIMIT 1;
        SQL_QUERY_HAS_RESERVATION;
        $result_rows = DatabaseRobot::get($sql_query_has_reservation);
        $count = count($result_rows);
        if ($count == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generates HTML code specific to table cells. Each table cell is formatted with a 
     * specific style depending on its contents.
     */
    public static function get_reservation_table_cell($timeslot_day, $timeslot_number) {

        // Select the table field to be used for the logic
        $table_field = "timeslot_reserved_id";

        // Generate empty cell content
        $table_cell_content = "";

        // Set a counter to zero to track if there is content in a cell
        $table_cell_data_length = 0;

        // Gets the associated student from the reservation list (by day)
        // using their ID and uses their name as the cell content.
        switch ($timeslot_day) {
            case 'Monday':
                $timeslot_reserved_id = self::$all_reservations_on_mon[$timeslot_number - 1][$table_field];
                $table_cell_content .= self::get_reservation_student_name($timeslot_reserved_id);
                break;
            case 'Tuesday';
                $timeslot_reserved_id = self::$all_reservations_on_tue[$timeslot_number - 1][$table_field];
                $table_cell_content .= self::get_reservation_student_name($timeslot_reserved_id);
                break;
            case 'Wednesday';
                $timeslot_reserved_id = self::$all_reservations_on_wed[$timeslot_number - 1][$table_field];
                $table_cell_content .= self::get_reservation_student_name($timeslot_reserved_id);
                break;
            case 'Thursday';
                $timeslot_reserved_id = self::$all_reservations_on_thu[$timeslot_number - 1][$table_field];
                $table_cell_content .= self::get_reservation_student_name($timeslot_reserved_id);
                break;
            case 'Friday';
                $timeslot_reserved_id = self::$all_reservations_on_fri[$timeslot_number - 1][$table_field];
                $table_cell_content .= self::get_reservation_student_name($timeslot_reserved_id);
                break;
            default:
                break;
        }

        // Set the counter to the length of the new cell content
        $table_cell_data_length = strlen($table_cell_content);

        // Create an empty string for styling the cell
        $table_cell_style = "";

        // Style the cell red if it's taken, green if it's empty
        if ($table_cell_data_length > 0) {
            $table_cell_style = <<<CSS_RESERVATION_TABLE_CELL_RESERVED
            style="
                background-color: mistyrose;
                color: maroon;
            "
            CSS_RESERVATION_TABLE_CELL_RESERVED;
        } else {
            $table_cell_style = <<<CSS_RESERVATION_TABLE_CELL_FREE
            style="
                border: 1px solid white;
                background-color: #e5f8e5;
            "
            CSS_RESERVATION_TABLE_CELL_FREE;
        }

        // Return the code for the table cell
        $table_cell_code = "<td $table_cell_style>" . $table_cell_content . "</td>";
        return $table_cell_code;
    }

    /**
     * Gets the name of the student associated with a particular
     * timeslot reservation. Only the first letter of the first name is used
     * and the full last name is used.
     */
    public static function get_reservation_student_name($timeslot_reserved_id) {

        // Get the current reservation list
        $reservations_reserved = self::$all_reservations_reserved;

        // For each reservation, find the student name who has the same ID as the one we provided
        foreach ($reservations_reserved as &$reservation) {
            if ($timeslot_reserved_id == $reservation['timeslot_reserved_id']) {
                $student_first_initial = substr($reservation['student_firstname'], 0, 1);
                $student_last_name = $reservation['student_lastname'];
                return $student_first_initial . " " . $student_last_name;
            }
        }
        return "";
    }

    /**
     * Gets the time interval from a timeslot reservation record associated
     * with a provided student ID.
     */
    public static function get_reservation_from_student_id($student_id) {
        // If the student ID is empty, don't show any reservation time
        if (empty($student_id)) {
            echo "";
        } else {
            // Get the current reservation list
            $reservations_reserved = self::$all_reservations_reserved;

            // For each reservation, find the reservation time interval
            // That has the same student ID as the one we provided
            foreach ($reservations_reserved as &$reservation) {
                if ($reservation['student_id'] == $student_id) {
                    $timeslot_string  = $reservation['timeslot_day'] . " ";
                    $timeslot_string .= $reservation['timeslot_start'] . " - ";
                    $timeslot_string .= $reservation['timeslot_end'];
                    echo $timeslot_string;
                }
            }
        }
    }
}
