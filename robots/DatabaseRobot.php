<?php

/**
 * CLASS: DatabaseRobot
 * a conceptual robot that can connect to a database and do
 * get and set operations on the connected database. This robot
 * also regenerates the test tables required for this website.
 */
require_once 'DebugRobot.php';

class DatabaseRobot {

    public static $db_hostname = 'localhost';               // The hostname of the database
    public static $db_database = 'yupao_COMP3340A5';        // The name of the database
    public static $db_username = 'yupao_COMP3340A5';        // The main database user
    public static $db_password = 'yupao_COMP3340A5_SWIFT';  // The main database user password

    /**
     * Queries the database using an SQL query parameter and
     * returns the result as an associative array.
     */
    public static function get($sql_query) {
        // Attempt database connection and then execute the query
        try {
            $data_source_hostname = self::$db_hostname;
            $data_source_database = self::$db_database;
            $data_source_username = self::$db_username;
            $data_source_password = self::$db_password;
            $sql_connection = new PDO("mysql:host=$data_source_hostname;dbname=$data_source_database", $data_source_username, $data_source_password);
            $sql_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql_statement = $sql_connection->prepare($sql_query);
            $sql_statement->execute();
            $sql_result_array = $sql_statement->fetchAll(\PDO::FETCH_ASSOC);
            // Return an error message on exception
        } catch (PDOException $exception) {
            echo "Exception: " . $exception->getMessage();
            $sql_connection = null;
        }
        $sql_connection = null;
        return $sql_result_array;
    }

    /**
     * Queries the database but does not return anything.
     * This function is used to make changes to the database.
     */
    public static function set($sql_query) {

        try {
            $data_source_hostname = self::$db_hostname;
            $data_source_database = self::$db_database;
            $data_source_username = self::$db_username;
            $data_source_password = self::$db_password;
            $sql_connection = new PDO("mysql:host=$data_source_hostname;dbname=$data_source_database", $data_source_username, $data_source_password);
            $sql_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql_connection->exec($sql_query);
        } catch (PDOException $exception) {
            echo $sql_query . "<br>" . $exception->getMessage();
        }

        $sql_connection = null;
    }

    /**
     * Checks if the provided table name exists in the database.
     */
    public static function table_does_not_exist($table_name) {
        $result_rows = DatabaseRobot::get("SHOW TABLES LIKE '%$table_name%'; ");
        $count = count($result_rows);
        if ($count == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Regenerates the initial test table data if the 
     * required tables don't exist.
     */
    public static function regenerate_test_tables() {

        $sql_test_table_students = <<<SQL_TABLE_STUDENTS
        -- phpMyAdmin SQL Dump
        -- version 4.9.7
        -- https://www.phpmyadmin.net/
        --
        -- Host: localhost
        -- Generation Time: Jul 18, 2021 at 01:33 AM
        -- Server version: 10.2.38-MariaDB-log
        -- PHP Version: 7.4.20

        SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
        SET AUTOCOMMIT = 0;
        START TRANSACTION;
        SET time_zone = "+00:00";


        /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
        /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
        /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
        /*!40101 SET NAMES utf8mb4 */;

        --
        -- Database: `yupao_COMP3340A5`
        --

        -- --------------------------------------------------------

        --
        -- Table structure for table `table_students`
        --

        CREATE TABLE `table_students` (
        `student_id` int(32) NOT NULL,
        `student_firstname` varchar(21) COLLATE utf8_unicode_ci NOT NULL,
        `student_lastname` varchar(21) COLLATE utf8_unicode_ci NOT NULL,
        `student_email` varchar(55) COLLATE utf8_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

        --
        -- Dumping data for table `table_students`
        --

        INSERT INTO `table_students` (`student_id`, `student_firstname`, `student_lastname`, `student_email`) VALUES
        (142, 'Jeff', 'Bezos', 'jeff@amazon.com'),
        (147, 'Tim', 'Cook', 'tim@apple.com'),
        (164, 'Satya', 'Nadella', 'satya@microsoft.com'),
        (165, 'Bill', 'Gates', 'bill@microsoft.com'),
        (166, 'Jack', 'Ma', 'jack@alibaba.com'),
        (168, 'Tony', 'Robbins', 'tony@robbinsresearch.com'),
        (172, 'Elon', 'Musk', 'elon@tesla.com'),
        (177, 'Mark', 'Zuckerberg', 'mark@facebook.com'),
        (178, 'Warren', 'Buffet', 'warren@berkshire.com'),
        (179, 'Masayoshi', 'Son', 'masa@softbank.com'),
        (180, 'Jack', 'Dorsey', 'jack@twitter.com');

        --
        -- Indexes for dumped tables
        --

        --
        -- Indexes for table `table_students`
        --
        ALTER TABLE `table_students`
        ADD PRIMARY KEY (`student_id`);

        --
        -- AUTO_INCREMENT for dumped tables
        --

        --
        -- AUTO_INCREMENT for table `table_students`
        --
        ALTER TABLE `table_students`
        MODIFY `student_id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;
        COMMIT;

        /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
        /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
        /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

        SQL_TABLE_STUDENTS;

        $sql_test_table_timeslots = <<<SQL_TABLE_TIMESLOTS
        -- phpMyAdmin SQL Dump
        -- version 4.9.7
        -- https://www.phpmyadmin.net/
        --
        -- Host: localhost
        -- Generation Time: Jul 18, 2021 at 01:34 AM
        -- Server version: 10.2.38-MariaDB-log
        -- PHP Version: 7.4.20

        SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
        SET AUTOCOMMIT = 0;
        START TRANSACTION;
        SET time_zone = "+00:00";


        /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
        /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
        /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
        /*!40101 SET NAMES utf8mb4 */;

        --
        -- Database: `yupao_COMP3340A5`
        --

        -- --------------------------------------------------------

        --
        -- Table structure for table `table_timeslots`
        --

        CREATE TABLE `table_timeslots` (
        `timeslot_id` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
        `timeslot_day` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
        `timeslot_start` varchar(21) COLLATE utf8_unicode_ci NOT NULL,
        `timeslot_end` varchar(21) COLLATE utf8_unicode_ci NOT NULL,
        `timeslot_reserved_id` int(64) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

        --
        -- Dumping data for table `table_timeslots`
        --

        INSERT INTO `table_timeslots` (`timeslot_id`, `timeslot_day`, `timeslot_start`, `timeslot_end`, `timeslot_reserved_id`) VALUES
        ('FRI1', 'Friday', '08:30 AM', '09:30 AM', 147),
        ('FRI2', 'Friday', '09:30 AM', '10:30 AM', NULL),
        ('FRI3', 'Friday', '10:30 AM', '11:30 AM', NULL),
        ('FRI4', 'Friday', '11:30 AM', '12:30 PM', NULL),
        ('FRI5', 'Friday', '12:30 PM', '01:30 PM', NULL),
        ('FRI6', 'Friday', '01:30 PM', '02:30 PM', NULL),
        ('FRI7', 'Friday', '02:30 PM', '03:30 PM', NULL),
        ('FRI8', 'Friday', '03:30 PM', '04:30 PM', NULL),
        ('FRI9', 'Friday', '04:30 PM', '05:30 PM', 168),
        ('MON1', 'Monday', '08:30 AM', '09:30 AM', NULL),
        ('MON2', 'Monday', '09:30 AM', '10:30 AM', 142),
        ('MON3', 'Monday', '10:30 AM', '11:30 AM', NULL),
        ('MON4', 'Monday', '11:30 AM', '12:30 PM', NULL),
        ('MON5', 'Monday', '12:30 PM', '01:30 PM', NULL),
        ('MON6', 'Monday', '01:30 PM', '02:30 PM', NULL),
        ('MON7', 'Monday', '02:30 PM', '03:30 PM', NULL),
        ('MON8', 'Monday', '03:30 PM', '04:30 PM', NULL),
        ('MON9', 'Monday', '04:30 PM', '05:30 PM', NULL),
        ('THU1', 'Thursday', '08:30 AM', '09:30 AM', NULL),
        ('THU2', 'Thursday', '09:30 AM', '10:30 AM', NULL),
        ('THU3', 'Thursday', '10:30 AM', '11:30 AM', 165),
        ('THU4', 'Thursday', '11:30 AM', '12:30 PM', NULL),
        ('THU5', 'Thursday', '12:30 PM', '01:30 PM', NULL),
        ('THU6', 'Thursday', '01:30 PM', '02:30 PM', NULL),
        ('THU7', 'Thursday', '02:30 PM', '03:30 PM', NULL),
        ('THU8', 'Thursday', '03:30 PM', '04:30 PM', NULL),
        ('THU9', 'Thursday', '04:30 PM', '05:30 PM', NULL),
        ('TUE1', 'Tuesday', '08:30 AM', '09:30 AM', 164),
        ('TUE2', 'Tuesday', '09:30 AM', '10:30 AM', NULL),
        ('TUE3', 'Tuesday', '10:30 AM', '11:30 AM', NULL),
        ('TUE4', 'Tuesday', '11:30 AM', '12:30 PM', NULL),
        ('TUE5', 'Tuesday', '12:30 PM', '01:30 PM', 172),
        ('TUE6', 'Tuesday', '01:30 PM', '02:30 PM', NULL),
        ('TUE7', 'Tuesday', '02:30 PM', '03:30 PM', NULL),
        ('TUE8', 'Tuesday', '03:30 PM', '04:30 PM', NULL),
        ('TUE9', 'Tuesday', '04:30 PM', '05:30 PM', 166),
        ('WED1', 'Wednesday', '08:30 AM', '09:30 AM', NULL),
        ('WED2', 'Wednesday', '09:30 AM', '10:30 AM', NULL),
        ('WED3', 'Wednesday', '10:30 AM', '11:30 AM', NULL),
        ('WED4', 'Wednesday', '11:30 AM', '12:30 PM', NULL),
        ('WED5', 'Wednesday', '12:30 PM', '01:30 PM', NULL),
        ('WED6', 'Wednesday', '01:30 PM', '02:30 PM', NULL),
        ('WED7', 'Wednesday', '02:30 PM', '03:30 PM', NULL),
        ('WED8', 'Wednesday', '03:30 PM', '04:30 PM', NULL),
        ('WED9', 'Wednesday', '04:30 PM', '05:30 PM', NULL);

        --
        -- Indexes for dumped tables
        --

        --
        -- Indexes for table `table_timeslots`
        --
        ALTER TABLE `table_timeslots`
        ADD PRIMARY KEY (`timeslot_id`);
        COMMIT;

        /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
        /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
        /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

        SQL_TABLE_TIMESLOTS;

        if (self::table_does_not_exist("table_students")) {
            self::set($sql_test_table_students);
        }

        if (self::table_does_not_exist("table_timeslots")) {
            self::set($sql_test_table_timeslots);
        }
    }
}
