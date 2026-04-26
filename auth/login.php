<?php
session_start();
require_once "../includes/connect-db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {
        $sql = "
            SELECT User_ID, Username, Email, Password_Hash, Is_Admin
            FROM User
            WHERE Username = :username OR Email = :email
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'email' => $username
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $storedPassword = $user["Password_Hash"];
            $isValidLogin = false;

            if (password_verify($password, $storedPassword)) {
                $isValidLogin = true;
            } elseif (!preg_match('/^\$2[aby]\$/', $storedPassword) && $password === $storedPassword) {
                // One-time fallback for legacy plaintext seeds, then migrate to hash.
                $isValidLogin = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upgradeStmt = $pdo->prepare("UPDATE User SET Password_Hash = :hash WHERE User_ID = :id");
                $upgradeStmt->execute([
                    'hash' => $newHash,
                    'id' => $user["User_ID"]
                ]);
            }

            if ($isValidLogin) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["User_ID"];
                $_SESSION["username"] = $user["Username"];
                $_SESSION["is_admin"] = $user["Is_Admin"];
                header("Location: ../home.php");
                exit;
            }
        }

        $error = "Invalid username/email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CvilleEats</title>
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

        .login-card {
            background: white;
            width: 380px;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.10);
        }

        h1 {
            margin-top: 0;
            color: #1f3c88;
            margin-bottom: 8px;
        }

        p.subtitle {
            margin-top: 0;
            color: #666;
            margin-bottom: 24px;
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

        button:hover {
            background: #16306d;
        }

        .error {
            background: #fdeaea;
            color: #a12626;
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

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="login-card">
            <h1>CvilleEats</h1>
            <p class="subtitle">Login to explore Charlottesville restaurants</p>

            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="username">Username or Email</label>
                <input type="text" name="username" id="username" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>

                <button type="submit">Login</button>
            </form>

            <div class="links">
                <p><a href="signup.php">Create New Account</a></p>
            </div>
        </div>
    </div>
</body>
</html>