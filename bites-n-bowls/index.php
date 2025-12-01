<?php
session_start();
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bites 'n Bowls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/images/favicon-32x32.png">

    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: Arial, sans-serif;
            position: relative;
            overflow: hidden; 
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .splash-image {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            object-position: center;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .form-signin {
            width: 450px;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: white;
            display: none;
            z-index: 2;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            text-align: center;
        }
       
        .form-floating input.form-control {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
       
        .form-floating input.form-control:focus {
            background-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.8);
        }
       
        .form-floating > label {
            color: rgba(0, 0, 0, 0.7) !important;
        }
       
        .brand {
            font-weight: bold;
            font-size: 2rem;
            margin-bottom: 5px;
            color: white;
        }

        .tagline, .default-credentials {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            font-style: italic;
            margin-bottom: 15px;
        }
       
        .btn:focus, .btn:active {
            outline: none !important;
            box-shadow: none !important;
        }

        .form-signin.show {
            display: block;
            opacity: 1;
        }

        .alert {
            margin-top: 1rem;
        }
    </style>
</head>
<body>

    <img src="assets/images/6.png" alt="Bites and Bowls" class="splash-image">

    <main class="form-signin" id="loginFormContainer">
        <div class="brand">Bites 'n Bowls</div>
        <div class="tagline">Your Pet's Happy Place</div>

        <div id="loginError" class="alert alert-danger" style="display: none;"></div>

        <form id="loginForm">
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>

        <div class="default-credentials text-center mt-2">
            Default Username: <strong>admin</strong><br>
            Default Password: <strong>1234</strong>
        </div>
    </main>

    <script>
        setTimeout(() => {
            document.getElementById("loginFormContainer").classList.add("show");
        }, 5000);

        document.getElementById("loginForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const errorDiv = document.getElementById('loginError');

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = "pages/dashboard.php";
                } else {
                    errorDiv.textContent = data.message || "Invalid username or password.";
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = "Connection error. Please try again.";
                errorDiv.style.display = 'block';
            }
        });
    </script>

</body>
</html>