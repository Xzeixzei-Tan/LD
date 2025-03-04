<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>
   Index
  </title>
  <style>
   body {
        margin: 0;
        font-family: Arial, sans-serif;
        color: white;
        background-color: #b71c1c;
        position: relative;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
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
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        position: relative;
        z-index: 2;
    }
    .header .buttons {
        display: flex;
        align-items: center;
    }
    .header .buttons button,
    .header .buttons a {
        margin-right: 10px;
        margin-left: 15px;
        margin-top: 10px;
        padding: 10px 20px;
        font-weight: bold;
        border-radius: 25px;
        text-decoration: none;
        font-family: Montserrat;
    }
    .header .buttons .btn {
        background-color: white;
        color: #388e3c;
        border: none;
    }
    .header .buttons a {
        color: white;
    }
    .main-content h1 {
        font-size: 2.5rem;
        margin-bottom: 20px;
        font-family: Montserrat;
    }
    .main-content p {
        font-size: 1.25rem;
        margin-bottom: 40px;
        font-family: Montserrat;
    }
    .main-content .btn {
        font-family: Montserrat;
        text-decoration: none;
        background-color: white;
        color: #388e3c;
        padding: 10px 20px;
        font-weight: bold;
        border: none;
        border-radius: 25px;
        margin-left: 40px;
        margin-top: -2%;
    }
    .logo-section {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        z-index: 2;
    }
    .logo-section img {
        width: 50px;
        height: 50px;
        margin-left: 10px;
    }
    .logo-section p {
        text-align: right;
        font-weight: bold;
    }

    .main-content h1 {
        font-size: 3rem;
    }
    .main-content p {
        font-size: 1.5rem;
    }
    .logo-section img {
        width: 70px;
        height: 70px;
        filter: drop-shadow(0px 4px 8px rgba(0, 0, 0, 0.3));
    }
    .logo-section h1{
        font-size: 20px;
        font-family: Wensley Demo;
    }
    .logo-section h5{
        font-family: LT Cushion Light;
        letter-spacing: 1.8px;
    }
    
    .main-content h1 {
        font-size: 4rem;
    }
    .main-content p {
        font-size: 2rem;
        color: rgb(196, 195, 195);
    }
    .logo-section img {
        width: 80px;
        height: 80px;
    }
  </style>
 </head>
 <body>
    <img alt="DepEd Division Office building" class="background-image" height="1080" src="index.jpg" width="1920"/>
        
        <div class="header">
            <div class="buttons">
            <br><br>
            <a class="btn" href="login.php">Login</a>
            <a href="#">About Us</a>
            </div>
        </div>
  
        <div class="content">
            <div class="main-content">
            <h1 style="font-size: 50px;  margin-left: 40px;">Plan and manage all your</h1>
            <h1 style="font-size: 50px; margin-top:-1.5%; margin-left: 40px;">events in one platform.</h1>
            <p style="font-size: 20px;  margin-left: 40px;">Track every detail from planning to execution in real-time.</p>
            <a class="btn" href="signup.php">Get Started</a>
            </div>
        </div>

        <div class="logo-section">
            <div>
                <h5 style="margin-left: 100px;">DEPARTMENT OF EDUCATION</h5>
                <h1 style="margin-right: 50px; margin-top: -4%;">DIVISION OF GENERAL TRIAS CITY</h1>
            </div>
        <img alt="DepEd Division of General Trias logo" style="margin-right: 140px;" height="100" src="DO-LOGO.png" width="100"/>