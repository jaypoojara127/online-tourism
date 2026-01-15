// Admin Dashboard JavaScript

// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize data tables
    initializeDataTables();
    
    // Initialize form validations
    initializeFormValidations();
});

// Tooltip Initialization
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[title]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = this.getAttribute('title');
    document.body.appendChild(tooltip);
    
    const rect = this.getBoundingClientRect();
    tooltip.style.position = 'fixed';
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    tooltip.style.backgroundColor = '#333';
    tooltip.style.color = 'white';
    tooltip.style.padding = '5px 10px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '12px';
    tooltip.style.zIndex = '10000';
    
    this._tooltip = tooltip;
}

function hideTooltip() {
    if (this._tooltip) {
        document.body.removeChild(this._tooltip);
        delete this._tooltip;
    }
}

// Data Tables Initialization
function initializeDataTables() {
    const tables = document.querySelectorAll('.admin-table');
    tables.forEach(table => {
        if (!table.hasAttribute('data-initialized')) {
            makeTableSortable(table);
            table.setAttribute('data-initialized', 'true');
        }
    });
}

function makeTableSortable(table) {
    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
        if (header.textContent.trim() !== 'Actions') {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => sortTable(table, index));
        }
    });
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const isAscending = table.getAttribute('data-sort-order') === 'asc';
    
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();
        
        // Handle numeric values
        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // Handle dates
        const aDate = new Date(aValue);
        const bDate = new Date(bValue);
        
        if (!isNaN(aDate) && !isNaN(bDate)) {
            return isAscending ? aDate - bDate : bDate - aDate;
        }
        
        // Handle text
        return isAscending 
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
    });
    
    // Remove existing rows
    rows.forEach(row => tbody.removeChild(row));
    
    // Add sorted rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Toggle sort order
    table.setAttribute('data-sort-order', isAscending ? 'desc' : 'asc');
    
    // Update header indicators
    updateSortIndicators(table, columnIndex, !isAscending);
}

function updateSortIndicators(table, columnIndex, isAscending) {
    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
        header.classList.remove('sort-asc', 'sort-desc');
        if (index === columnIndex) {
            header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
        }
    });
}

// Form Validation
function initializeFormValidations() {
    const forms = document.querySelectorAll('.admin-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    // Clear previous errors
    clearFormErrors(form);
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                showFieldError(field, 'Please enter a valid email address');
                isValid = false;
            }
        }
        
        // Number validation
        if (field.type === 'number') {
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');
            
            if (min && parseFloat(field.value) < parseFloat(min)) {
                showFieldError(field, `Minimum value is ${min}`);
                isValid = false;
            }
            
            if (max && parseFloat(field.value) > parseFloat(max)) {
                showFieldError(field, `Maximum value is ${max}`);
                isValid = false;
            }
        }
        
        // File validation
        if (field.type === 'file') {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (field.files.length > 0) {
                const file = field.files[0];
                
                if (file.size > maxSize) {
                    showFieldError(field, 'File size must be less than 5MB');
                    isValid = false;
                }
                
                if (!allowedTypes.includes(file.type)) {
                    showFieldError(field, 'Please upload an image file (JPEG, PNG, GIF, WebP)');
                    isValid = false;
                }
            }
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.style.color = '#e74c3c';
    errorElement.style.fontSize = '0.85rem';
    errorElement.style.marginTop = '0.3rem';
    errorElement.textContent = message;
    
    field.style.borderColor = '#e74c3c';
    formGroup.appendChild(errorElement);
}

function clearFormErrors(form) {
    const errors = form.querySelectorAll('.field-error');
    errors.forEach(error => error.remove());
    
    const fields = form.querySelectorAll('input, select, textarea');
    fields.forEach(field => {
        field.style.borderColor = '#ddd';
    });
}

// Image Preview for File Inputs
document.addEventListener('change', function(e) {
    if (e.target.type === 'file' && e.target.accept.includes('image')) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewId = e.target.id + '-preview';
                let preview = document.getElementById(previewId);
                
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = previewId;
                    preview.style.maxWidth = '200px';
                    preview.style.marginTop = '10px';
                    preview.style.borderRadius = '4px';
                    e.target.parentNode.appendChild(preview);
                }
                
                preview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});

// Confirmation Dialogs
document.addEventListener('click', function(e) {
    if (e.target.closest('[data-confirm]')) {
        const message = e.target.closest('[data-confirm]').getAttribute('data-confirm') || 
                       'Are you sure you want to perform this action?';
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    }
});

// Notification Management
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close"><i class="fas fa-times"></i></button>
    `;
    
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.backgroundColor = type === 'success' ? '#2ecc71' : 
                                       type === 'error' ? '#e74c3c' : '#3498db';
    notification.style.color = 'white';
    notification.style.borderRadius = '4px';
    notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    notification.style.zIndex = '10000';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.gap = '0.8rem';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    // Close button
    notification.querySelector('.notification-close').addEventListener('click', () => {
        document.body.removeChild(notification);
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            document.body.removeChild(notification);
        }
    }, 5000);
}

// Export data functionality
function exportTableData(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('th, td');
        
        cells.forEach(cell => {
            // Skip action cells
            if (!cell.closest('.action-buttons')) {
                rowData.push(`"${cell.textContent.trim().replace(/"/g, '""')}"`);
            }
        });
        
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, filename);
    } else {
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Search functionality for tables
function initTableSearch(inputId, tableId) {
    const searchInput = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!searchInput || !table) return;
    
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

// Bulk actions
function initBulkActions() {
    const checkAll = document.querySelector('.check-all');
    const itemChecks = document.querySelectorAll('.item-check');
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            itemChecks.forEach(check => {
                check.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    itemChecks.forEach(check => {
        check.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.item-check:checked').length;
        if (checkedCount > 0) {
            bulkActions.style.display = 'block';
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

// Chart initialization (if using charts)
function initCharts() {
    // This would be used if you integrate charting libraries like Chart.js
    // Example: revenue chart, booking trends, etc.
    console.log('Initialize charts here if needed');
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeTooltips();
    initializeDataTables();
    initializeFormValidations();
    initBulkActions();
    initCharts();
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
});