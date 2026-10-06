<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Student Home'; ?></title>
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
            max-width: 480px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        }
        h1 {
            color: #10375c;
            margin-bottom: 0.75rem;
            font-size: 1.9rem;
        }
        p.lead {
            color: #444;
            margin-bottom: 1.75rem;
            line-height: 1.6;
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
        .badge {
            display: inline-block;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #16697a;
            background: #e6f2f2;
            padding: 0.3rem 0.8rem;
            border-radius: 999px;
        }
        .notice {
            background: #fdecea;
            color: #a3231f;
            border: 1px solid #f4b6b3;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🎓 Welcome to My Student Info Page</h1>
        <p class="lead">
            This is the home page of the Student Information application built
            with the LavaLust PHP Framework. Visiting this page unlocks access
            to the protected Student Profile page for your session.
        </p>
        <?php if (!empty($access_message)): ?>
            <div class="notice">⚠️ <?php echo $access_message; ?></div>
        <?php endif; ?>
        <nav>
            <a class="home" href="<?php echo site_url('student'); ?>">Home</a>
            <a class="profile" href="<?php echo site_url('student/profile'); ?>">Student Profile</a>
        </nav>
        <div class="badge">Route → Controller → View demo</div>
    </div>
</body>
</html>
