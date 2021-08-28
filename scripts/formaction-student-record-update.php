<?php

/**
 * This script handles a request to update a student record
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

    // Update the current reservation list
    ReservationRobot::update_reservation_data();

    // Select a student based on the student from the originating form
    SecretaryRobot::select_student($_POST['select-student-id']);

    // Create a new object to use as the updated data. The ID is null
    // because we updating an existing record with an existing ID.
    $updated_student = new Student(
        NULL,
        filter_var($_POST['update-student-firstname'], FILTER_SANITIZE_STRING), /* Sanitizes strings to prevent malicious code */
        filter_var($_POST['update-student-lastname'], FILTER_SANITIZE_STRING),
        filter_var($_POST['update-student-email'], FILTER_SANITIZE_STRING)
    );

    // Update the student in the database using the new information
    RegistrationRobot::update_student(SecretaryRobot::$assigned_student, $updated_student);

    // Return to the main page with the current selected student
    // So that we can see the changes in the control panels
    // This is done using a hidden form that auto submits itself after it is created.
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
