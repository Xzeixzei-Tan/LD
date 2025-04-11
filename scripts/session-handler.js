
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if user was deleted by admin (redirected with query parameter)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('account_deleted')) {
        alert('Your account has been deleted by an administrator. You will be redirected to the signup page.');
        window.location.href = 'signup.php';
        return;
    }
});
</script>