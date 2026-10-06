<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * StudentMiddleware
 *
 * Protects the /student/profile route.
 *
 * Unique access condition for this activity:
 *   Visiting the Student Home page (/student) sets
 *   $_SESSION['student_access'] = true for the current browsing
 *   session. The profile route checks this flag before allowing
 *   the request through. If the flag is missing (e.g. the visitor
 *   jumps straight to /student/profile in a fresh session/incognito
 *   window), the request is redirected back to the Home page with
 *   a denied-access message.
 */
class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure $next
     * @return mixed
     */
    public function handle($next)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check whether access has been granted for this session.
        if (!empty($_SESSION['student_access']) && $_SESSION['student_access'] === true) {
            return $next();
        }

        // Access denied: send the visitor back to the student home page.
        $_SESSION['access_message'] = 'Access denied: please visit the Home page first to unlock the Student Profile page.';
        redirect('student');
    }
}
?>
