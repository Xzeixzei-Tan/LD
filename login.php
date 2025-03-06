<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-Event Management System</title>
    <style>
        /* Reset some default styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body, html {
            height: 100%;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Main container styling */
        .container {
            display: flex;
        }

        .content-header h1 {
            font-size: 1.5rem;
            color:rgb(255, 255, 255);
            font-family: Wensley Demo;
            margin-left: 10%;
        }

        .content-header p {
            color: rgb(196, 195, 195);
            font-size: 1rem;
            margin-top: 3%;
            font-family: LT Cushion Light;
            margin-left: 22%;
        }

        .content-header img {
            float: left;
            margin-left: 5%;
            margin-right: 2%;
            margin-top: 2%;
            filter: drop-shadow(0px 4px 5px rgba(0, 0, 0, 0.3));
        }

        .logo {
            font-size: 3rem;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: .5rem;
            margin-top: -10%;
        }

        /* Form styling */
        .form-container {
            margin: auto;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 35%;
            text-align: center;
        }

        .form-container h1{
            color: #12753E;
            font-family: Tilt Warp;
            font-weight: lighter;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            border: 3px solid #B9B6B6;
            border-radius: 10px;
            box-sizing: border-box;
            font-family: Tilt Warp Regular;
            font-size: 15px;
            margin-top: 3%;
            text-align: center;
        }

        .form-container .btn {
            width: 95%;
            padding: 10px;
            background-color: #2B3A8F;
            color: #fff;
            border: 2px solid #2B3A8F;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            font-family: Tilt Warp Regular;
            letter-spacing: 1px;
            box-shadow: 0 4px 0 #373F70;
            margin-top: 7%;
        }

        .form-container .btn:hover {
            background-color: white;
            color: #373F70;
        }

        .form-container .register-link {
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
            color: black;
            font-family: Montserrat;
            font-weight: bold;
        }

        .form-container a{
            text-decoration: none;
            font-family: Montserrat;
            font-weight: bold;
        }

        #status {
            color: #E33629;
            font-family: Montserrat; 
        }        
    </style>
</head>
<body>

    <img alt="DepEd Division Office building" class="background-image" height="1080" src="styles/photos/login-signup.jpg" width="1920"/>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br><br><br>

        <div class="container">
            <div class="form-container">
                <h1>LOGIN</h1>
                <form method="POST" action="login_process.php"><br>
                    <?php 
                        if(isset($_SESSION['status']))
                        {
                            ?>
                                <div id="status">
    
                                    <?php echo $_SESSION['status']; ?>
                                      
                                </div>
                            <?php
                            unset($_SESSION['status']);
                        }
                    ?>

                    <input type="text" id="email" name="email" placeholder="Enter email" required><br>
                    <input type="password" id="password" name="password" placeholder="Enter password" required><br>

                    <center>
                    <input class="btn" type="submit" value="Login">

                    <p class="register-link">Don't have a account? <a style="color: #2B3A8F;" href="signup.php">Create an account!</a></p></center><br>           

            </form>
            </div>
        </div>
    </div>
</body>
</html>
