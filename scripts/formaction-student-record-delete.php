<?php

/**
 * This script handles a request to delete a student record.
 * It's in it's own file because the form associated with this
 * intent has two buttons, making it easier to handle the separate
 * intents from one form.
 */
require_once(__DIR__ . "/../robots/RegistrationRobot.php");
require_once(__DIR__ . "/../robots/SecretaryRobot.php");
require_once(__DIR__ . "/../robots/ReservationRobot.php");
require_once(__DIR__ . "/../types/Student.php");

// Only execute the script on a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Update the current student list from the database
    RegistrationRobot::update_student_list();

    // Update the current reservations from the database
    ReservationRobot::update_reservation_data();

    // Select the specified student from the form this script was executed on
    SecretaryRobot::select_student($_POST['select-student-id']);

    // Delete the selected student from the student list in the database
    RegistrationRobot::delete_student(SecretaryRobot::$assigned_student);

    // Return to the main page and clear the headers
    header("Location: ../index.php", true, 303);
    exit();
}
