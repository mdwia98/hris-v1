// HRIS System - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips and popovers
    initializeComponents();
    
    // Set up event listeners
    setupEventListeners();
});

// Initialize Bootstrap components
function initializeComponents() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Setup event listeners
function setupEventListeners() {
    // Toggle sidebar mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking on a link
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', closeSidebarOnMobile);
    });

    // Confirm delete
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', confirmDelete);
    });
}

// Toggle sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

// Close sidebar on mobile
function closeSidebarOnMobile() {
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('show');
        }
    }
}

// Confirm delete action
function confirmDelete(event) {
    event.preventDefault();
    const message = event.target.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini?';
    
    if (confirm(message)) {
        const deleteUrl = event.target.getAttribute('href') || event.target.getAttribute('data-url');
        if (deleteUrl) {
            window.location.href = deleteUrl;
        }
    }
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.alert-container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Validate email
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validate password strength
function validatePasswordStrength(password) {
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[!@#$%^&*]/.test(password);
    const isLongEnough = password.length >= 8;
    
    return hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar && isLongEnough;
}

// Show loading spinner
function showLoading() {
    const spinnerDiv = document.createElement('div');
    spinnerDiv.id = 'loadingSpinner';
    spinnerDiv.className = 'position-fixed top-50 start-50 translate-middle';
    spinnerDiv.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(spinnerDiv);
}

// Hide loading spinner
function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.remove();
    }
}

// Fetch data with loading
async function fetchData(url, options = {}) {
    try {
        showLoading();
        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        hideLoading();
        return data;
    } catch (error) {
        hideLoading();
        console.error('Error fetching data:', error);
        showAlert('Terjadi kesalahan saat memuat data', 'danger');
        return null;
    }
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push(col.innerText);
        });
        csv.push(csvRow.join(','));
    });
    
    downloadCSV(csv.join('\n'), filename);
}

// Download CSV
function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = filename;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Print element
function printElement(elementId) {
    const printWindow = window.open('', '', 'width=900,height=600');
    const printContent = document.getElementById(elementId).innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <link rel="stylesheet" href="${BASE_URL}assets/css/style.css">
        </head>
        <body onload="window.print()">
            ${printContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
}

// AJAX form submission
function submitForm(formId, actionUrl) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert(result.message, 'success');
                // Reload page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan', 'danger');
        }
    });
}

// Get URL parameter
function getUrlParameter(param) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(param);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
function setupSearch(inputId, tableId) {
    const searchInput = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!searchInput || !table) return;
    
    searchInput.addEventListener('keyup', debounce(function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }, 300));
}
