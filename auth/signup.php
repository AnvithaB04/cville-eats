<?php
session_start();
require_once "../includes/connect-db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $email === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {
        $checkSql = "
            SELECT User_ID
            FROM User
            WHERE Username = :username OR Email = :email
            LIMIT 1
        ";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            'username' => $username,
            'email' => $email
        ]);

        if ($checkStmt->fetch()) {
            $error = "That username or email already exists.";
        } else {
            $insertSql = "
                INSERT INTO User (Username, Email, Password_Hash)
                VALUES (:username, :email, :password)
            ";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);

            $success = "Account created successfully. You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CvilleEats</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: white;
            width: 420px;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.10);
        }

        h1 {
            margin-top: 0;
            color: #1f3c88;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #1f3c88;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
        }

        .error {
            background: #fdeaea;
            color: #a12626;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .success {
            background: #eaf8ec;
            color: #1d6b2c;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .links {
            margin-top: 18px;
            text-align: center;
        }

        .links a {
            color: #1f3c88;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <h1>Create Account</h1>

            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>

                <button type="submit">Create Account</button>
            </form>

            <div class="links">
                <p><a href="login.php">I already have an account</a></p>
            </div>
        </div>
    </div>
</body>
</html>