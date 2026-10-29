<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cricket Auction - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= base_url('public/assets/AppTeqLogo.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #caccc1 0%, #6294bcff 50%, #caccc1 100%);
            /* background: linear-gradient(180deg, #6294bcff, #caccc1); */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .login-wrapper {
            width: 100%;
            max-width: 900px;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-title {
            text-align: center;
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .login-container {
            background: rgba(0, 0, 0, 0.4);
            /* more transparent */
            backdrop-filter: blur(20px);
            padding: 2.5rem;
            border-radius: 38px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            /* soften shadow */
            display: flex;
            align-items: center;
            gap: 2rem;
            color: white;
            animation: fadeIn 0.8s ease-in-out;
            flex-wrap: wrap;
        }


        .login-image {
            width: 150px;
            /* adjust size */
            height: 150px;
            object-fit: contain;
            border-radius: 50%;
            /* makes it circular */
            border: #E77726;
            /* gold ring */
            padding: 5px;
            /* space inside ring */
            background-color: white;
            /* optional, so ring doesn’t overlap image */
        }


        .login-left {
            flex: 0 0 auto;
        }

        .login-content {
            flex: 1;
            min-width: 250px;
        }


        .login-header h1 {
            font-size: 2rem;
            font-family: "Gill Sans", sans-serif;
            background: #ffffffff;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .login-header p {
            color: #bbbbbb;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            /* dark transparent background */
            border: 1px solid #555;
            color: white;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 1rem;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.95);
            /* light background on focus */
            color: #000;
            /* dark text */
            border-color: #eaea66;
            outline: none;
        }


        .btn-primary {
            width: 100%;
            padding: 14px;
            background: white;
            border: none;
            border-radius: 8px;
            color: black;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: black;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                text-align: center;
            }

        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left: Logo only -->
        <div class="login-left">
            <img src="public/assets/AppTeqLogo.png" alt="Logo" class="login-image">
        </div>

        <!-- Right: Title + Form -->
        <div class="login-content">
            <div class="login-header text-center text-md-start mb-4">
                <h1>AppTeq Auction System</h1>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger" id="error-message">
                    <?= session()->getFlashdata('error') ?>
                </div>
                <script>
                    // Auto-hide error message after 3 seconds
                    setTimeout(function() {
                        const errorMessage = document.getElementById('error-message');
                        if (errorMessage) {
                            errorMessage.style.transition = 'opacity 0.5s ease-out';
                            errorMessage.style.opacity = '0';
                            setTimeout(function() {
                                errorMessage.style.display = 'none';
                            }, 500); // Additional 0.5s for fade out effect
                        }
                    }, 3000); // 3 seconds
                </script>
            <?php endif; ?>


            <form method="post" action="<?= base_url('/login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>

</body>

</html>