<?php
require_once 'DatabaseRobot.php';
require_once 'DebugRobot.php';
require_once 'ViewRobot.php';

/**
 * CLASS: Registration Robot
 * this robot is used specifically for registering students.
 * It communicates with the database to perform any tasks related to
 * registration. It can also produce outputs that are related to
 * student registrations that exist in the database.
 */
class RegistrationRobot {

    /**
     * Maintain a static array of students, updated at the start
     * of every script this robot is used.
     */
    public static $student_list;

    /**
     * The main update function that refreshes the static list of students
     * from the database. It gets all available students from the student table.
     */
    public static function update_student_list() {
        self::$student_list = DatabaseRobot::get("SELECT * FROM table_students");
    }

    /**
     * Generates HTML code specific to tables (radio selection rows) using 
     * the student list data. This is used for selecting students in the main UI.
     * Uses an element from the associative array returned by the database.
     */
    public static function get_student_row_select($student_record) {

        // Extract student id from object
        $student_id = $student_record['student_id'];

        // Extract student fullname from object
        $student_fullname = $student_record['student_firstname'] . " " . $student_record['student_lastname'];

        // Extract student email from object
        $student_email = $student_record['student_email'];

        // Initalize checked attribute for selected row
        $checked = "";

        // Toggles the checked attribute on a specific row based on current post data
        if ($_POST['select-student-id'] == $student_id) {
            $checked = ViewRobot::toggle_attribute("checked", isset($_POST['select-student-id']));
        }

        // The HTML code used for generating the table row for the selection form.
        $code = <<<HTML
        <tr>
            <td>
                <input type='radio' id='select-student-id-$student_id' name='select-student-id' value='$student_id' class='student-select' onchange='this.form.submit();' required $checked>
                <label for='select-student-id-$student_id' class='student-select'>$student_fullname <span class='student-select-email'>($student_email)</span></label>
            </td>
        </tr>
        HTML;
        return $code;
    }

    /**
     * Produces all the rows required for the selection form table using 
     * the previous function and the maintained list of students.
     */
    public static function get_student_rows() {
        $student_record_list = RegistrationRobot::$student_list;
        foreach ($student_record_list as &$student_record) {
            echo RegistrationRobot::get_student_row_select($student_record);
        }
    }

    /**
     * Registers a new student to the database and returns the auto-incremented
     * primary key from the database as the student id.
     */
    public static function register_new_student(Student $student) {

        $sql_query_set = <<<SQL_SET
        INSERT INTO table_students (
                student_id,
                student_firstname,
                student_lastname,
                student_email
            )
        VALUES (
                DEFAULT,
                '$student->firstname',
                '$student->lastname',
                '$student->email'
            );
        SQL_SET;

        $sql_query_get = <<<SQL_GET
        SELECT  student_id
        FROM    table_students
        WHERE   student_email = '$student->email';
        SQL_GET;

        // Update the database
        DatabaseRobot::set($sql_query_set);

        // Get the new record's student ID
        $sql_get_result = DatabaseRobot::get($sql_query_get);
        $new_student_id = $sql_get_result[0]['student_id'];

        // Refresh the student list
        self::update_student_list();

        // Return the new student's ID
        return $new_student_id;
    }

    /**
     * Deletes a student from the database using a matching
     * Student ID from a provided student object parameter.
     */
    public static function delete_student(Student $student) {

        $sql_query_delete = <<<SQL_QUERY_STUDENT_DELETE
        DELETE FROM table_students
        WHERE student_id = '$student->id';
        SQL_QUERY_STUDENT_DELETE;

        // Update the database and the list
        DatabaseRobot::set($sql_query_delete);
        self::update_student_list();

        // Delete any reservations associated with the deleted student
        ReservationRobot::delete_reservation($student->id);
    }

    /**
     * Updates student information on the database. It uses the original student,
     * and a new updated student object to make the changes.
     */
    public static function update_student(Student $student, Student $student_updated) {

        // Get the cstudent ID of the student to be updated
        $student_id = $student->id;

        // Generate strings from the updated student object
        $student_new_firstname_string = "'" . $student_updated->firstname . "'";
        $student_new_lastname_string = "'" . $student_updated->lastname . "'";
        $student_new_email_string = "'" . $student_updated->email . "'";

        // Creates conditional arrays that contain parameters to modify the SQL code
        // If the provided field from the updated student is empty, the
        // associated query is deactivated using WHERE false.
        $student_firstname_parameters = empty($student_updated->firstname) ? array('student_firstname', 'false') : array($student_new_firstname_string, 'student_id = ' . $student_id);
        $student_lastname_parameters = empty($student_updated->lastname) ? array('student_firstname', 'false') : array($student_new_lastname_string, 'student_id = ' . $student_id);
        $student_email_parameters = empty($student_updated->email) ? array('student_firstname', 'false') : array($student_new_email_string, 'student_id = ' . $student_id);

        $sql_query_update = <<<SQL_QUERY_STUDENT_UPDATE
        UPDATE  table_students
        SET     student_firstname = $student_firstname_parameters[0]
        WHERE   $student_firstname_parameters[1];

        UPDATE  table_students
        SET     student_lastname = $student_lastname_parameters[0]
        WHERE   $student_lastname_parameters[1];

        UPDATE  table_students
        SET     student_email = $student_email_parameters[0]
        WHERE   $student_email_parameters[1];
        SQL_QUERY_STUDENT_UPDATE;

        // Update the database
        DatabaseRobot::set($sql_query_update);
    }
}
