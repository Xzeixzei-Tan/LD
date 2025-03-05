<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
	<title>Sample Landing Page</title>
</head>
<style type="text/css">
	* {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body, html {
        height: 100%;
    }

    hr{
        border: 1px solid white;
    }

    .content {
        flex: 1;
        background-color: #ffffff;
        padding: 4rem;
    }

    .content-header h1 {
        font-size: 1.5rem;
        color: #333333;
        font-family: Wensley Demo;
        margin-left: 32%;
    }

    .content-header p {
        color: #999;
        font-size: 1rem;
        margin-top: -3%;
        font-family: LT Cushion Light;
        margin-left: 44%;
    }

    .content-header img {
        float: left;
        margin-left: 22%;
        margin-top: -1%;
        filter: drop-shadow(0px 4px 5px rgba(0, 0, 0, 0.3));
    }

    .content-body h1{
    	font-family: Montserrat;
    	font-size: 2rem;
    	padding: 10px;
    }

    .content-body hr{
    	border: 1px solid #95A613;
    }

    .join-btn{
    	display: flex;
    	padding: 10px;
    	padding-left: 15px;
    	padding-right: 15px;
        width: 40%;
        font-family: Montserrat;
        font-weight: bold;
        font-size: 12px;
        color: white;
        text-align: center;
        text-decoration: none;
        background-color: #12753E;
        border-radius: 5px;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
    }

    .card1 {
    	margin-bottom: 2%;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 500px;
        height: 100px;
        max-width: 1000px;
        align-items: center;
        justify-content: center;
    }

</style>
<body>

<div class="container">

    <div class="content">
    	<div class="content-header">
	    	<img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
	    	<p>Department of Education</p>
	    	<h1>Division Office of General Trias City</h1>
    	</div><br><br><br><br><br>

    	<div class="content-body">
	    	<h1>Welcome!</h1>
	    	<hr><br><br>

	    	<div class="cards">
                <div class="card1">
                    <center>
                        <a class="join-btn" href="admin-dashboard.php">Learning and Development</a>
                    </center>
                </div>
            </div>
    	</div>
    </div>
</div>
</body>


</html>