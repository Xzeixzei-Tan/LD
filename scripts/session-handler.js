// Updated session-handler.js with new endpoint
document.addEventListener('DOMContentLoaded', function() {
    // Check if user was deleted by admin (via cookie or query parameter)
    const urlParams = new URLSearchParams(window.location.search);
    const deletedCookie = document.cookie.split('; ').find(row => row.startsWith('account_deleted='));
    
    if ((urlParams.has('account_deleted') || deletedCookie) && window.location.pathname !== '/signup.php') {
        // Clear the cookie if it exists
        if (deletedCookie) {
            document.cookie = "account_deleted=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }
        
        alert('Your account has been deleted by an administrator. You will be redirected to the signup page.');
        window.location.href = 'signup.php?account_deleted=true';
        return;
    }
    
    // Set up periodic session check
    setInterval(checkSessionStatus, 15000); // Check every 15 seconds
});

function checkSessionStatus() {
    fetch('session_manager.php?action=check')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'deleted') {
                alert('Your account has been deleted by an administrator. You will be redirected to the signup page.');
                window.location.href = 'signup.php?account_deleted=true';
            } else if (data.status === 'invalid') {
                // Session expired or invalid for other reasons
                window.location.href = 'login.php?session_expired=true';
            }
        })
        .catch(error => console.error('Error checking session status:', error));
}