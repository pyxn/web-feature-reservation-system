<?php

/**
 * This script handles a request to delete a reservation associated with a student.
 * It's in it's own file because the form associated with this
 * intent has two buttons, making it easier to handle the separate
 * intents from one form.
 */
require_once(__DIR__ . "/../robots/RegistrationRobot.php");
require_once(__DIR__ . "/../robots/SecretaryRobot.php");
require_once(__DIR__ . "/../robots/ReservationRobot.php");
require_once(__DIR__ . "/../types/Student.php");

// Only execute the script on POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Update the current student list
    RegistrationRobot::update_student_list();

    // Update the current reservations
    ReservationRobot::update_reservation_data();

    // Select student based on oroginating form
    SecretaryRobot::select_student($_POST['select-student-id']);

    // Delete the reservation associated with the student ID.
    ReservationRobot::delete_reservation($_POST['select-student-id']);

    // Return to the main page using an auto-submitting form that
    // reselects the student so we can see the changes.
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
