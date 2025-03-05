<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup-Event Management System</title>
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
            position: fixed;
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
            width: 68%;
            text-align: center;
            margin-bottom: 5%;
        }

        .form-container h1{
            color: #12753E;
            font-family: Tilt Warp;
            font-weight: lighter;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            border: 2px solid #B9B6B6;
            border-radius: 10px;
            box-sizing: border-box;
            font-family: Tilt Warp Regular;
            font-size: 12px;
            margin-top: 1%;
            margin-bottom: 5%; 
            text-align: center;
        }

        .form-container label{
            display: block;
            text-align: left;
            font-family: Tilt Warp Regular;
            font-weight: lighter;
            font-size: 13px;
            color: #555;
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

        .form-container .login-link {
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

        .form-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .form-col {
            flex: 1;
            padding: 0 10px;
        }
        
        .divider {
            width: 2px;
            background-color: #ddd;
        }
        
        .form-container select {
            width: 100%;
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            margin-top: 1%;
            margin-bottom: 5%;
            font-family: Tilt Warp; 
        }
        
        select {
            font-family: Tilt Warp;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
            padding-right: 30px;
        }
        
        .signup-btn {
            width: 100%;
            padding: 10px;
            background-color: #233876;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        /* Simple responsive layout */
        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
            }
            
            .form-col {
                margin-bottom: 15px;
            }
            
            .divider {
                display: none;
            }
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
                <h1>SIGNUP</h1>
                <form method="POST" action="signup_process.php"><br>
                <div class="form-row">
                <div class="form-col">
                    <label for="firstName">First Name :</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Enter First Name">
                    
                    <label for="lastName">Last Name :</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Enter Last Name">
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="sex">Sex :</label>
                            <select id="sex" name="sex">
                                <option value="female" selected>Female</option>
                                <option value="male">Male</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label for="contact">Contact Number :</label>
                            <input type="tel" id="contact" name="contact" placeholder="Enter Contact Number">
                        </div>
                    </div>
                    
                    <label for="email">E-mail :</label>
                    <input type="email" id="email" name="email" placeholder="Enter e-mail">
                    
                    <label for="password">Password :</label>
                    <input type="password" id="password" name="password" placeholder="Enter password">
                </div>
                
                <div class="divider"></div>
                
                <div class="form-col">
                    <label for="school">School Assignment :</label>
                    <input type="text" id="school" name="school" placeholder="Enter School Assignment">
                    
                    <label for="position">Position / Designation :</label>
                    <input type="text" id="position" name="position" placeholder="Enter Position / Designation">
                    
                    <label for="classification">Classification :</label>
                    <select id="classification" name="classification">
                        <option value="teaching" selected>Teaching</option>
                        <option value="non-teaching">Non - Teaching</option>
                        <option value="teaching-related">Teaching Related</option>
                    </select>
                    
                    <center>
                    <input class="btn" type="submit" value="Signup">
                    <p class="login-link">Already have an account? <a style="color: #2B3A8F;" href="login.php">Login!</a></p></center><br>           
            </form>
            </div>
        </div>
    </div>
</body>
</html>
