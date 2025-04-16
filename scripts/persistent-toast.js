// persistent-toast.js - Persistent toast notification system that works across pages

// Create or get toast container
function createToastContainer() {
    if (!document.getElementById('toast-container')) {
        const toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    return document.getElementById('toast-container');
}

// Show toast notification with progress bar
function showProgressToast(title, message, type = 'info', id = 'progress-toast') {
    const container = createToastContainer();

    // Check if toast already exists
    let toast = document.getElementById(id);
    if (toast) {
        // Update existing toast
        updateToastMessage(message, type, id);
        return toast;
    }

    // Create toast element
    toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.id = id;

    // Create toast content
    toast.innerHTML = `
        <div class="toast-header">
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <strong>${title}</strong>
        <button type="button" class="toast-close" onclick="closeToast('${id}')">&times;</button>
        </div>
        <div class="toast-body">
        <p id="${id}-message">${message}</p>
        <div class="progress-container">
            <div class="progress-bar" id="${id}-progress-bar" style="width: 0%;">0%</div>
        </div>
        <p id="${id}-counter" class="email-counter">Emails sent: 0</p>
        </div>
    `;

    // Add to container and show
    container.appendChild(toast);

    // Apply show animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Save toast data to localStorage
    saveToastData(id, {
        title,
        message,
        type,
        percent: 0,
        emailsSent: 0,
        visible: true,
        timestamp: Date.now()
    });

    return toast;
}

// Close toast and remove from storage
function closeToast(id) {
    const toast = document.getElementById(id);
    if (toast) {
        toast.classList.remove('show');

        // Remove from DOM after animation completes
        setTimeout(() => toast.remove(), 300);

        // Remove from storage
        localStorage.removeItem(`toast_${id}`);
    }
}

// Update progress bar
function updateProgress(percent, emailsSent, id = 'progress-toast') {
    const progressBar = document.getElementById(`${id}-progress-bar`);
    const emailCounter = document.getElementById(`${id}-counter`);

    if (progressBar) {
        progressBar.style.width = `${percent}%`;
        progressBar.textContent = `${Math.round(percent)}%`;
    }

    if (emailCounter) {
        emailCounter.textContent = `Emails sent: ${emailsSent}`;
    }

    // Update storage
    const toastData = getToastData(id);
    if (toastData) {
        toastData.percent = percent;
        toastData.emailsSent = emailsSent;
        saveToastData(id, toastData);
    }
}

// Update toast message and type
function updateToastMessage(message, type = 'info', id = 'progress-toast') {
    const toast = document.getElementById(id);
    const messageElement = document.getElementById(`${id}-message`);

    if (toast && messageElement) {
        // Update message
        messageElement.textContent = message;

        // Update toast type
        toast.className = `toast toast-${type} show`;

        // Update icon
        const iconElement = toast.querySelector('.toast-header i');
        if (iconElement) {
            iconElement.className = `fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}`;
        }

        // Update storage
        const toastData = getToastData(id);
        if (toastData) {
            toastData.message = message;
            toastData.type = type;
            saveToastData(id, toastData);
        }
    }
}

// Save toast data to localStorage
function saveToastData(id, data) {
    localStorage.setItem(`toast_${id}`, JSON.stringify(data));
}

// Get toast data from localStorage
function getToastData(id) {
    const data = localStorage.getItem(`toast_${id}`);
    return data ? JSON.parse(data) : null;
}

// Load all active toasts from storage when page loads
function loadActiveToasts() {
    // Find all toast data in localStorage
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key.startsWith('toast_')) {
            try {
                const id = key.replace('toast_', '');
                const data = JSON.parse(localStorage.getItem(key));

                // Check if toast should still be shown (not expired)
                const currentTime = Date.now();
                const toastAge = currentTime - data.timestamp;
                const maxToastAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

                if (data.visible && toastAge < maxToastAge) {
                    // Recreate the toast
                    const toast = showProgressToast(data.title, data.message, data.type, id);

                    // Update progress
                    updateProgress(data.percent, data.emailsSent, id);
                } else {
                    // Remove expired toast data
                    localStorage.removeItem(key);
                }
            } catch (error) {
                console.error('Error loading toast from storage:', error);
            }
        }
    }
}

// Auto-remove toast after a specified time
function autoRemoveToast(id, delay = 5000) {
    setTimeout(() => closeToast(id), delay);
}

// Initialize toasts when DOM is ready
document.addEventListener('DOMContentLoaded', loadActiveToasts);

// Export functions for use in other files
window.PersistentToast = {
    show: showProgressToast,
    update: updateToastMessage,
    updateProgress: updateProgress,
    close: closeToast,
    autoRemove: autoRemoveToast
};
