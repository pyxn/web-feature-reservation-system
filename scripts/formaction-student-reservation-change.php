<?php

/**
 * This script handles a request to change a reservation associated with a student
 * It's in it's own file because the form associated with this
 * intent has two buttons, making it easier to handle the separate
 * intents from one form.
 */
require_once(__DIR__ . "/../robots/RegistrationRobot.php");
require_once(__DIR__ . "/../robots/SecretaryRobot.php");
require_once(__DIR__ . "/../robots/ReservationRobot.php");
require_once(__DIR__ . "/../types/Student.php");

// Only execute the code on POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Update the current student list
    RegistrationRobot::update_student_list();

    // Update the current reservations
    ReservationRobot::update_reservation_data();

    // Select student from the originating form
    SecretaryRobot::select_student($_POST['select-student-id']);

    // Generate the timeslot ID using the form data
    $reservation_timeslot_id = "'" . $_POST['reserve-timeslot-day'] . $_POST['reserve-timeslot-time'] . "'";

    // Get the student ID from the student object
    $reservation_student_id = SecretaryRobot::$assigned_student->id;

    // Check if the timeslot has not been reserved by someone else.
    // Don't do anything if it's already reserved.
    if (ReservationRobot::is_free_timeslot($reservation_timeslot_id) == true) {

        // Delete the current reservation if the timeslot is free
        ReservationRobot::delete_reservation($_POST['select-student-id']);

        // Make a new reservation if the timeslot is free
        ReservationRobot::reserve_timeslot($reservation_timeslot_id, $reservation_student_id);
    }

    // Return to the main page using hidden auto-submitting form
    // to reselect the current student and display the changes.
    $current_student_id = $_POST['select-student-id'];
    $hidden_html = <<<HTML
        <form id="form-student-reselect" method="POST" action="../index.php" hidden>
            <input type="hidden" name="submission-from-selection-form" value="1" hidden>
            <input type='hidden' name='select-student-id' value='$current_student_id' hidden>
        </form>
        <script type="text/javascript">
            document.getElementById('form-student-reselect').submit();
        </script>
    HTML;
    echo $hidden_html;
}
