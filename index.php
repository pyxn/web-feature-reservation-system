<?php

/** ------------------------------------------------------------------------------------
 * Reservation System by Pao Yu
 * ------------------------------------------------------------------------------------*/

/**
 * This file reprents the main entry point of the program
 */
require_once 'robots/DatabaseRobot.php';
require_once 'robots/DebugRobot.php';
require_once 'robots/RegistrationRobot.php';
require_once 'robots/SecretaryRobot.php';
require_once 'robots/ReservationRobot.php';
require_once 'robots/ViewRobot.php';

require_once 'types/Student.php';

// Generate required tables if they don't exist. Includes test data
DatabaseRobot::regenerate_test_tables();

// Update the current student list
RegistrationRobot::update_student_list();

// Update the current reservations
ReservationRobot::update_reservation_data();

// Only executes on POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // If a student selection is made, the SecretaryRobot assigns itself the student.
    if (isset($_POST['submission-from-selection-form'])) {
        unset($_POST['submission-from-selection-form']);
        SecretaryRobot::select_student($_POST['select-student-id']);
    }

    // If the registration form is used, create a new object and register it
    // in the database using the Registration robot.
    if (isset($_POST['submission-from-registration-form'])) {
        unset($_POST['submission-from-registration-form']);

        // create new student with sanitized data
        $new_student = new Student(
            NULL,
            filter_var($_POST['register-student-firstname'], FILTER_SANITIZE_STRING), /* Sanitize strings to prevent malicious code */
            filter_var($_POST['register-student-lastname'], FILTER_SANITIZE_STRING),
            filter_var($_POST['register-student-email'], FILTER_SANITIZE_STRING)
        );

        // Register a new student and get its ID from the database
        $_POST['select-student-id'] = RegistrationRobot::register_new_student($new_student);

        // Assign the student to the Secretary robot
        SecretaryRobot::select_student($_POST['select-student-id']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>COMP3340 | Assignment #5 | Pao Yu</title>
    <link rel="stylesheet" href="styles/main.css">
</head>

<body>

    <nav id="reservation-navigation">
        <h3>Lab Reservation System</h3>
    </nav>

    <main id="reservation-main">
        <aside id="reservation-sidebar">
            <h3>Student List</h3>
            <!-- -------------------------------------------------
                FORM: STUDENT SELECTION
                Displays a list of radio selection rows generated
                from the database. Selects a student on select.
            ------------------------------------------------------>
            <form id="form-students" method="POST">
                <input type="hidden" name="submission-from-selection-form" value="1">
                <table id="reservation-sidebar-table">
                    <colgroup>
                        <col span="1">
                    </colgroup>
                    <tbody>
                        <?php RegistrationRobot::get_student_rows(); ?>
                    </tbody>
                </table>
            </form>
        </aside>

        <section id="reservation-content">

            <!-- -------------------------------------------------
                SECTION: RESERVATION HEADER
                Shows the current selected student and the
                associated reservation they have on the database.
            ------------------------------------------------------>
            <section id="section-reservation-header">
                <fieldset id="reservation-header-fieldset">
                    <legend>Current Student</legend>
                    <table>
                        <colgroup>
                            <col width="144px">
                            <col width="233px">
                        </colgroup>
                        <tr>
                            <th>Student Name</td>
                            <td>
                                <?php SecretaryRobot::show_current_student_name(); ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Reserved Timeslot</th>
                            <td><?php ReservationRobot::get_reservation_from_student_id($_POST['select-student-id']) ?></td>
                        </tr>
                    </table>
                </fieldset>

            </section>

            <section id="section-reservation-forms">
                <!-- -------------------------------------------------
                    FORM: STUDENT REGISTRATION
                    Allows to register new students in the database
                    all fields are required.
                ------------------------------------------------------>
                <form method="POST">
                    <input type="hidden" name="submission-from-registration-form" value="1">
                    <fieldset id="reservation-registration-fieldset">
                        <legend>Register New Student</legend>
                        <table>
                            <tr>
                                <th><label for="register-student-firstname">First Name<abbr title="required" aria-label="required"> *</abbr></label></th>
                                <td>
                                    <input type="text" id="register-student-firstname" name="register-student-firstname" placeholder="Enter first name" required>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="register-student-lastname">Last Name<abbr title="required" aria-label="required"> *</abbr></label></th>
                                <td>
                                    <input type="text" id="register-student-lastname" name="register-student-lastname" placeholder="Enter last name" required>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="register-student-email">Email<abbr title="required" aria-label="required"> *</abbr></label></th>
                                <td>
                                    <input type="string" id="register-student-email" name="register-student-email" placeholder="Enter email" required>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" value="Submit">Register</button>
                                </td>
                            </tr>
                        </table>

                    </fieldset>
                </form>
                <!-- -------------------------------------------------
                    FORM: STUDENT RESERVATION
                    Allows creating, changing, and deleting
                    reservations from the database. Hides elements
                    based on the student's reservation state.
                ------------------------------------------------------>
                <form method="POST">
                    <input type="hidden" name="submission-from-reservation-form" value="1">
                    <input type='hidden' name='select-student-id' value="<?php echo ViewRobot::get_post_data('select-student-id'); ?>">
                    <fieldset id="reservation-reservation-fieldset">
                        <legend>Reserve Student Timeslot</legend>
                        <table <?php echo ViewRobot::toggle_attribute("hidden", !isset($_POST['select-student-id'])) ?>>
                            <!-- -------------------------------------------------
                                Show current reservation timeslot
                            ------------------------------------------------------>
                            <tr>
                                <th><label for="reserve-student-current-reservation">Current RSV.</label></th>
                                <td><input id="reserve-student-current-reservation" type="text" disabled value="<?php ReservationRobot::get_reservation_from_student_id($_POST['select-student-id']) ?>"></td>
                            </tr>
                            <!-- -------------------------------------------------
                                Select from a predetermined set of day options
                            ------------------------------------------------------>
                            <tr>
                                <th><label for="reserve-timeslot-day">Choose a day</label></th>
                                <td><select name="reserve-timeslot-day" id="reserve-timeslot-day">
                                        <option value="MON">Monday</option>
                                        <option value="TUE">Tuesday</option>
                                        <option value="WED">Wednesday</option>
                                        <option value="THU">Thursday</option>
                                        <option value="FRI">Friday</option>
                                    </select></td>
                            </tr>
                            <!-- -------------------------------------------------
                                Select from a predetermined set of time options
                            ------------------------------------------------------>
                            <tr>
                                <th><label for="reserve-timeslot-time">Choose a Time</label></th>
                                <td><select name="reserve-timeslot-time" id="reserve-timeslot-time">
                                        <option value="1">08:30AM - 09:30AM</option>
                                        <option value="2">09:30AM - 10:30AM</option>
                                        <option value="3">10:30AM - 11:30AM</option>
                                        <option value="4">11:30AM - 12:30PM</option>
                                        <option value="5">12:30PM - 01:30PM</option>
                                        <option value="6">01:30PM - 02:30PM</option>
                                        <option value="7">02:30PM - 03:30PM</option>
                                        <option value="8">03:30PM - 04:30PM</option>
                                        <option value="9">04:30PM - 05:30PM</option>
                                    </select>
                                </td>
                            </tr>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <td class="dual-button-cell">
                                        <!-- -------------------------------------------------
                                            Button for deleting a reservation
                                        ------------------------------------------------------>
                                        <button type="submit" name="delete-student-reservation" value="delete-student-reservation" class="secondary" formaction="scripts/formaction-student-reservation-delete.php" <?php echo ViewRobot::toggle_attribute("hidden", !isset($_POST['select-student-id'])) ?>>Delete</button>

                                        <!-- -------------------------------------------------
                                            Button for creating a reservation OR
                                            Button for changing a reservation. Changes
                                            on state of student's reservation.
                                        ------------------------------------------------------>
                                        <?php

                                        if (isset($_POST['select-student-id']) && !ReservationRobot::has_reservation($_POST['select-student-id'])) {
                                            $hidden = ViewRobot::toggle_attribute("hidden", !isset($_POST['select-student-id']));
                                            $button_reserve = <<<HTML_BUTTON_RESERVE
                                            <button type="submit" name="update-student-reservation" value="update-student-reservation" formaction="scripts/formaction-student-reservation-create.php">Reserve</button>
                                            HTML_BUTTON_RESERVE;
                                            echo $button_reserve;
                                        } else {
                                            $hidden = ViewRobot::toggle_attribute("hidden", !isset($_POST['select-student-id']));
                                            $button_edit = <<<HTML_BUTTON_EDIT
                                            <button type="submit" name="change-student-reservation" value="change-student-reservation" formaction="scripts/formaction-student-reservation-change.php" $hidden>Change</button>
                                            HTML_BUTTON_EDIT;
                                            echo $button_edit;
                                        }

                                        ?>


                                    </td>
                                </tr>

                            </tfoot>
                        </table>
                    </fieldset>
                </form>
                <!-- -------------------------------------------------
                    FORM: STUDENT REVISION
                    Allows updating student information
                ------------------------------------------------------>
                <form method="POST" onkeydown="return event.key != 'Enter';">
                    <input type="hidden" name="submission-from-revision-form" value="1">
                    <input type='hidden' name='select-student-id' value="<?php ViewRobot::get_post_data('select-student-id'); ?>">
                    <fieldset id="reservation-revision-fieldset">
                        <legend>Edit Current Student</legend>
                        <table <?php echo ViewRobot::toggle_attribute("hidden", !isset($_POST['select-student-id'])); ?>>
                            <tbody>
                                <!-- -------------------------------------------------
                                        Edit student first name
                                ------------------------------------------------------>
                                <tr>
                                    <th><label for="update-student-firstname">First Name<abbr title="required" aria-label="required">*</abbr></label></th>
                                    <td><input type="text" value="<?php echo SecretaryRobot::get_student_firstname(); ?>" disabled></input></td>
                                    <td> <input type="text" id="update-student-firstname" name="update-student-firstname" placeholder="Enter new first name"></td>
                                </tr>
                                <!-- -------------------------------------------------
                                        Edit student last name
                                ------------------------------------------------------>
                                <tr>
                                    <th><label for="update-student-lastname">Last Name<abbr title="required" aria-label="required">*</abbr></label></th>
                                    <td><input type="text" value="<?php echo SecretaryRobot::get_student_lastname(); ?>" disabled></input></td>
                                    <td><input type="text" id="update-student-lastname" name="update-student-lastname" placeholder="Enter new last name"></td>
                                </tr>
                                <!-- -------------------------------------------------
                                        Edit student email
                                ------------------------------------------------------>
                                <tr>
                                    <th><label for="update-student-email">Email<abbr title="required" aria-label="required">*</abbr></label></th>
                                    <td><input type="text" value="<?php echo SecretaryRobot::get_student_email(); ?>" disabled></input></td>
                                    <td><input type="email" id="update-student-email" name="update-student-email" placeholder="Enter new email"></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <td>
                                        <!-- -------------------------------------------------
                                            Button to delete student record
                                        ------------------------------------------------------>
                                        <button type="submit" name="delete-student-record" value="delete-student-record" formaction="scripts/formaction-student-record-delete.php" class="secondary">Delete</button>

                                    </td>
                                    <td>
                                        <!-- -------------------------------------------------
                                            Button to update student record
                                        ------------------------------------------------------>
                                        <button type="submit" name="update-student-record" value="update-student-record" formaction="scripts/formaction-student-record-update.php">Update</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                </form>
            </section>

            <!-- -------------------------------------------------
                TABLE: STUDENT AND TIMESLOT RESERVATIONS
                Shows all reservation data based on day and
                time in a table with conditional cells
                depending on the state of the reservation and
                who has reserved it.
            ------------------------------------------------------>
            <section id="section-reservation-table">
                <fieldset id="reservation-table-fieldset">
                    <legend>Lab Reservations</legend>
                    <table id="table-reservations">
                        <colgroup>
                            <col span="1" width="89px">
                            <col span="1" width="144px">
                            <col span="1" width="144px">
                            <col span="1" width="144px">
                            <col span="1" width="144px">
                            <col span="1" width="144px">
                        </colgroup>
                        <thead>
                            <tr id="table-reservations-row">
                                <th id="table-reservations-header">Timeslot</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 1
                            ------------------------------------------------------>
                            <tr>
                                <th>08:30 AM - 09:30 AM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 1);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 1);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 1);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 1);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 1);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 2
                            ------------------------------------------------------>
                            <tr>
                                <th>09:30 AM - 10:30 AM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 2);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 2);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 2);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 2);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 2);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 3
                            ------------------------------------------------------>
                            <tr>
                                <th>10:30 AM - 11:30 AM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 3);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 3);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 3);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 3);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 3);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 4
                            ------------------------------------------------------>
                            <tr>
                                <th>11:30 AM - 12:30 PM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 4);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 4);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 4);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 4);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 4);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 5
                            ------------------------------------------------------>
                            <tr>
                                <th>12:30 PM - 01:30 PM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 5);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 5);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 5);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 5);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 5);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 6
                            ------------------------------------------------------>
                            <tr>
                                <th>01:30 PM - 02:30 PM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 6);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 6);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 6);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 6);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 6);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 7
                            ------------------------------------------------------>
                            <tr>
                                <th>02:30 PM - 03:30 PM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 7);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 7);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 7);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 7);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 7);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 8
                            ------------------------------------------------------>
                            <tr>
                                <th>03:30 PM - 04:30 PM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 8);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 8);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 8);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 8);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 8);
                                ?>
                            </tr>
                            <!-- -------------------------------------------------
                                Show all reservation data for interval 9
                            ------------------------------------------------------>
                            <tr>
                                <th>04:30 PM - 05:30 PM</th>
                                <?php
                                echo ReservationRobot::get_reservation_table_cell("Monday", 9);
                                echo ReservationRobot::get_reservation_table_cell("Tuesday", 9);
                                echo ReservationRobot::get_reservation_table_cell("Wednesday", 9);
                                echo ReservationRobot::get_reservation_table_cell("Thursday", 9);
                                echo ReservationRobot::get_reservation_table_cell("Friday", 9);
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </section>
        </section>

    </main>

</body>

</html>