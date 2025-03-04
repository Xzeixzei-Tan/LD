<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
	<title>Create Events</title>
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

    .sidebar {
        position: fixed;
        width: 250px;
        height: 100vh;
        background-color: #2b3a8f;
        color: #ffffff;
        padding: 2rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .sidebar .logo {
        margin-bottom: 1rem;
        margin-left: 5%;
    }

    hr{
        border: 1px solid white;
    }

    .sidebar .menu {
    	margin-top: 50%;
        display: flex;
        flex-direction: column;
        margin-bottom: 18rem;
    }

    .sidebar .menu a {
        color: #ffffff;
        text-decoration: none;
        padding: 1rem;
        display: flex;
        align-items: center;
        font-size: 1rem;
        border-radius: 5px;
        transition: background 0.3s;
        font-family: Tilt Warp Regular;
        margin-bottom: .5rem;
    }

    .sidebar .menu a:hover, .sidebar .menu a.active {
        background-color: white;
        color: #2b3a8f;
    }

    .sidebar .menu a i {
        margin-right: 0.5rem;
    }

    .content {
        flex: 1;
        background-color: #ffffff;
        padding: 4rem;
        margin-left: 250px;
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
    
    .form-container {
        background-color: #f9f9f9;    
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .form-container h3 {
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        color: #95A613;
        margin-bottom: 1.5rem;
    }
    
    .form-container form {
        display: flex;
        flex-direction: column;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-weight: bold;
        color: #4f5663;
        margin-bottom: 0.5rem;
    }
    
    .form-group p {
        margin-bottom: 0.5rem;
        color: #555;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="datetime-local"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .radio-group,
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    
    .radio-group label,
    .checkbox-group label {
        display: flex;
        align-items: center;
        margin-right: 1.5rem;
        margin-bottom: 0.5rem;
        font-weight: normal;
    }
    
    .radio-group input,
    .checkbox-group input {
        margin-right: 0.5rem;
    }
    
    .form-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    
    .form-col {
        flex: 1;
        min-width: 250px;
    }
    
    .meal-plan {
        background-color: #fef3c7;
        padding: 1rem;
        border-radius: 0.25rem;
        margin-top: 0.5rem;
    }
    
    .meal-day {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .meal-day span {
        font-weight: bold;
        margin-right: 1rem;
        min-width: 50px;
    }
    
    .certificate-template {
        border: 2px dashed #16a34a;
        padding: 2rem;
        border-radius: 0.25rem;
        text-align: center;
        margin-top: 0.5rem;
    }
    
    .certificate-template i {
        font-size: 2.5rem;
        color: #16a34a;
        margin-bottom: 1rem;
    }
    
    .certificate-template p {
        margin-bottom: 1rem;
    }
    
    .certificate-template button {
        background-color: #95A613;
        color: white;
        padding: 0.5rem 1.5rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
    }
    
    .certificate-template button:hover {
        background-color: #7a8a0f;
    }
    
    .submit-btn {
        text-align: center;
        margin-top: 1.5rem;
    }
    
    .submit-btn button {
        background-color: #16a34a;
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        font-weight: bold;
        font-size: 1rem;
        transition: background-color 0.3s;
    }
    
    .submit-btn button:hover {
        background-color: #15803d;
    }
    
    .section-title {
        font-weight: bold;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #ddd;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .content-header h1 {
            margin-left: 20%;
        }
        
        .content-header p {
            margin-left: 30%;
        }
    }
    
    @media (max-width: 768px) {
        .sidebar {
            width: 200px;
        }
        
        .content {
            margin-left: 200px;
            padding: 1.5rem;
        }
        
        .content-header img {
            margin-left: 10%;
        }
        
        .content-header h1 {
            margin-left: 15%;
        }
        
        .content-header p {
            margin-left: 25%;
        }
        
        .form-row {
            flex-direction: column;
        }
        
        .form-col {
            width: 100%;
        }
    }
    
    @media (max-width: 576px) {
        .sidebar {
            width: 70px;
            padding: 1rem 0.5rem;
        }
        
        .sidebar .menu a span {
            display: none;
        }
        
        .content {
            margin-left: 70px;
            padding: 1rem;
        }
        
        .content-header {
            text-align: center;
        }
        
        .content-header img {
            float: none;
            margin: 0 auto 1rem;
            display: block;
        }
        
        .content-header h1,
        .content-header p {
            margin-left: 0;
        }
    }
</style>
<body>
	<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="menu">
                <a href="dashboard-admin.php"><i class="fas fa-home mr-3"></i>Home</a>
                <a href="events-admin.php" class="active" ><i class="fas fa-calendar-alt mr-3"></i>Events</a>
                <a href="users-admin.php"><i class="fas fa-users mr-3"></i>Users</a>
                <a href="notif-admin.php"><i class="fas fa-bell mr-3"></i>Notification</a> 
                <br><br><br><br><br><br><br><br><br><br><br><br><br>
                <a href="progile-admin.php"><i class="fas fa-user-circle mr-3"></i>Profile</a>
            </div>
        </div>

        <div class="content">
            <div class="content-header">
                <img src="DO-LOGO.png" width="70px" height="70px">
                <p>Learning and Development</p>
                <h1>EVENT MANAGEMENT SYSTEM</h1>
            </div><br><br><br><br><br>

            <div class="content-body">
                <h1>EVENTS</h1>
                <hr><br><br>

                <div class="form-container">
                    <h3>CREATE AN EVENT</h3>
                    <form>
                        <!-- Personnel Selection -->
                        <div class="form-group">
                            <div class="radio-group">
                                <label><input type="radio" name="personnel" value="school"> <i>School Personnel</i></label>
                                <label><input type="radio" name="personnel" value="division"><i>Division Personnel</i></label>
                            </div>
                        </div>

                        <!-- Basic Event Details -->
                        <div class="form-group">
                            <div class="section-title">Basic Event Details</div>
                            
                            <label>Event Title:</label>
                            <input type="text" placeholder="Enter event title">
                            
                            <label>Description:</label>
                            <textarea placeholder="Enter event description"></textarea>
                            
                            <label>Event Mode:</label>
                            <select>
                                <option value="">Select event mode</option>
                                <option value="face-to-face">Face-to-face</option>
                                <option value="online">Online</option>
                            </select>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <label>Start Date/Time:</label>
                                    <input type="datetime-local">
                                </div>
                                
                                <div class="form-col">
                                    <label>End Date/Time:</label>
                                    <input type="datetime-local">
                                </div>
                            </div>
                            
                            <label>Venue/Location:</label>
                            <input type="text" placeholder="Enter venue or location">
                        </div>

                        <!-- Organizers & Trainers -->
                        <div class="form-group">
                            <div class="section-title">Organizers & Trainers</div>
                            
                            <label>Organizer Name:</label>
                            <input type="text" placeholder="Enter organizer name">
                            
                            <label>Speaker/Resource Person:</label>
                            <input type="text" placeholder="Enter speaker/resource person">
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <label>Event Contact Person:</label>
                                    <input type="text" placeholder="Enter contact person">
                                </div>
                                
                                <div class="form-col">
                                    <label>Email:</label>
                                    <input type="email" placeholder="Enter email address">
                                </div>
                                
                                <div class="form-col">
                                    <label>Mobile No:</label>
                                    <input type="text" placeholder="Enter mobile number">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <br><br>

                        <!-- Eligible Participants -->
                        <div class="form-group">
                            <div class="section-title">Eligible Participants</div>
                            
                            <label>School:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="school" value="public"> Public</label>
                                <label><input type="radio" name="school" value="private"> Private</label>
                            </div>
                            
                            <label>Educational Level:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="level" value="elementary"> Elementary</label>
                                <label><input type="radio" name="level" value="junior"> Junior High School</label>
                                <label><input type="radio" name="level" value="senior"> Senior High School</label>
                            </div>
                            
                            <label>Target Participants:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="participants" value="teaching"> Teaching</label>
                                <label><input type="radio" name="participants" value="teaching-related"> Teaching-related</label>
                                <label><input type="radio" name="participants" value="non-teaching"> Non-teaching</label>
                            </div>
                            
                            <label>Specialization (Elementary):</label>
                            <div class="radio-group">
                                <div class="form-col">
                                    <label><input type="radio" name="specialization" value="mtb-mle"> Mother Tongue-Based Multilingual Education (MTB-MLE)</label>
                                    <label><input type="radio" name="specialization" value="filipino"> Filipino</label>
                                    <label><input type="radio" name="specialization" value="english"> English</label>
                                    <label><input type="radio" name="specialization" value="mathematics"> Mathematics</label>
                                    <label><input type="radio" name="specialization" value="science"> Science</label>
                                </div>
                                <div class="form-col">
                                    <label><input type="radio" name="specialization" value="esp"> Edukasyon sa Pagpapakatao (EsP)</label>
                                    <label><input type="radio" name="specialization" value="mapeh"> Music, Arts, PE & Health (MAPEH)</label>
                                    <label><input type="radio" name="specialization" value="epp"> Edukasyong Pantahanan at Pangkabuhayan</label>
                                    <label><input type="radio" name="specialization" value="non-teaching"> N/A (for non-teaching)</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Meal Plan -->
                        <div class="form-group">
                            <div class="section-title">Meal Plan</div>
                            <div class="meal-plan">
                                <div class="meal-day">
                                    <span>Day 1:</span>
                                    <div class="checkbox-group">
                                        <label><input type="checkbox" name="meal1" value="breakfast"> Breakfast</label>
                                        <label><input type="checkbox" name="meal1" value="am-snack"> AM Snack</label>
                                        <label><input type="checkbox" name="meal1" value="lunch"> Lunch</label>
                                        <label><input type="checkbox" name="meal1" value="pm-snack"> PM Snack</label>
                                        <label><input type="checkbox" name="meal1" value="dinner"> Dinner</label>
                                    </div>
                                </div>
                                <div class="meal-day">
                                    <span>Day 2:</span>
                                    <div class="checkbox-group">
                                        <label><input type="checkbox" name="meal2" value="breakfast"> Breakfast</label>
                                        <label><input type="checkbox" name="meal2" value="am-snack"> AM Snack</label>
                                        <label><input type="checkbox" name="meal2" value="lunch"> Lunch</label>
                                        <label><input type="checkbox" name="meal2" value="pm-snack"> PM Snack</label>
                                        <label><input type="checkbox" name="meal2" value="dinner"> Dinner</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Certificate Template -->
                        <div class="form-group">
                            <div class="section-title">Certificate Template</div>
                            <div class="certificate-template">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Drag & Drop here</p>
                                <p>or</p>
                                <button type="button">Select file</button>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="submit-btn">
                            <button type="submit">CREATE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>