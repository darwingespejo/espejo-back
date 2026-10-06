<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Student Profile'; ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #10375c, #16697a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: #ffffff;
            border-radius: 14px;
            padding: 2.5rem 3rem;
            max-width: 560px;
            width: 100%;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        }
        h1 {
            color: #10375c;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #ffa62b;
            color: #10375c;
            font-weight: 700;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.75rem;
        }
        table td {
            padding: 0.55rem 0.4rem;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }
        table td.label {
            color: #16697a;
            font-weight: 600;
            width: 40%;
        }
        p.desc {
            color: #555;
            font-style: italic;
            text-align: center;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        nav {
            text-align: center;
        }
        nav a {
            display: inline-block;
            text-decoration: none;
            padding: 0.6rem 1.3rem;
            margin: 0 0.4rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        nav a.home {
            background: #16697a;
            color: #fff;
        }
        nav a.profile {
            background: #ffa62b;
            color: #10375c;
        }
        nav a:hover { opacity: 0.85; }
    </style>
</head>
<body>
    <div class="card">
        <div class="avatar">AD</div>
        <h1>Student Profile</h1>

        <table>
            <tr><td class="label">Student ID</td><td><?php echo $student['student_id']; ?></td></tr>
            <tr><td class="label">Name</td><td><?php echo $student['name']; ?></td></tr>
            <tr><td class="label">Course</td><td><?php echo $student['course']; ?></td></tr>
            <tr><td class="label">Year Level</td><td><?php echo $student['year']; ?></td></tr>
            <tr><td class="label">Section</td><td><?php echo $student['section']; ?></td></tr>
            <tr><td class="label">Email</td><td><?php echo $student['email']; ?></td></tr>
            <tr><td class="label">Address</td><td><?php echo $student['address']; ?></td></tr>
            <tr><td class="label">Contact No.</td><td><?php echo $student['contact_no']; ?></td></tr>
            <tr><td class="label">Hobbies</td><td><?php echo $student['hobbies']; ?></td></tr>
        </table>

        <p class="desc">"<?php echo $student['description']; ?>"</p>

        <nav>
            <a class="home" href="<?php echo site_url('student'); ?>">Home</a>
            <a class="profile" href="<?php echo site_url('student/profile'); ?>">Student Profile</a>
        </nav>
    </div>
</body>
</html>
