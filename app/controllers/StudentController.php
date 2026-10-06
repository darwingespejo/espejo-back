<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * StudentController
 *
 * Handles the Student Home page and the Student Profile page
 * for the Web Systems and Technologies laboratory activity.
 */
class StudentController extends Controller
{
    /**
     * Student Home Page
     * Route: /student
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unique access condition for this activity: simply visiting the
        // student home page grants a session flag that unlocks the
        // profile page for the rest of the browsing session.
        $_SESSION['student_access'] = true;

        $data['page_title']     = 'Student Home | De Villa Info Page';
        $data['access_message'] = $_SESSION['access_message'] ?? null;
        unset($_SESSION['access_message']);

        $this->call->view('student', $data);
    }

    /**
     * Student Profile Page
     * Protected by StudentMiddleware
     * Route: /student/profile
     */
    public function profile()
    {
        // Associative array containing student information.
        // This is passed to the view instead of hardcoding the HTML.
        $student = [
            'student_id'  => '2026-0452',
            'name'        => 'Angela Mae R. De Villa',
            'course'      => 'BS Information Technology',
            'year'        => '3rd Year',
            'section'     => 'IT-3A',
            'email'       => 'angela.devilla@example.edu.ph',
            'address'     => 'Calamba City, Laguna',
            'contact_no'  => '0917-123-4567',
            'hobbies'     => 'Sketching, Badminton, Building small web apps',
            'description' => 'An IT student who enjoys turning class projects into working, presentable applications.'
        ];

        $data['page_title'] = 'Student Profile | De Villa Info Page';
        $data['student']    = $student;

        $this->call->view('student_profile', $data);
    }
}
?>
