<?php

/**
 * CLASS: Student
 * a custom object that represents a Student
 * with an ID, firstname, lastname, and email.
 */
class Student {

    public $id;
    public $firstname;
    public $lastname;
    public $email;

    /**
     * Constructs an instance of a Student using parameters:
     * ID, firstname, lastname, and email.
     */
    public function __construct($id, $firstname, $lastname, $email) {

        $this->id = $id;

        // clean firstname data and capitalize
        $this->firstname = trim(ucfirst($firstname));

        // clean lastname data and capitalize
        $this->lastname = trim(ucfirst($lastname));

        // clean the email data and make lowercase
        $this->email = trim(strtolower($email));
    }
}
