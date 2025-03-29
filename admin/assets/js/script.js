// View details function
function viewDetails(type, id) {
    fetch('process_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=view&type=${type}&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create modal with details
            let modalContent = `
                <div class="modal-header">
                    <h5 class="modal-title">${type.charAt(0).toUpperCase() + type.slice(1)} Details</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="details-container">
            `;

            if (type === 'employer') {
                modalContent += `
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${data.data.name || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Organization:</span>
                        <span class="detail-value">${data.data.organisation_name || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Position:</span>
                        <span class="detail-value">${data.data.position || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hiring Count:</span>
                        <span class="detail-value">${data.data.hiring_count || '0'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">${data.data.city_state || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${data.data.email || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${data.data.phone || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Requirements:</span>
                        <span class="detail-value">${data.data.requirements || 'N/A'}</span>
                    </div>
                `;
            } else {
                modalContent += `
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${data.data.name || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Position:</span>
                        <span class="detail-value">${data.data.position || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Experience:</span>
                        <span class="detail-value">${data.data.fresher_experienced || 'N/A'} (${data.data.experience_years || '0'} years)</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Skills/Degree:</span>
                        <span class="detail-value">${data.data.skills_degree || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Location Preference:</span>
                        <span class="detail-value">${data.data.location_preference || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${data.data.email || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${data.data.phone || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Comments:</span>
                        <span class="detail-value">${data.data.comments || 'No comments'}</span>
                    </div>
                `;
            }

            modalContent += `
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            `;

            // Create and show modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        ${modalContent}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            $(modal).modal('show');
            
            // Remove modal when hidden
            $(modal).on('hidden.bs.modal', function () {
                document.body.removeChild(modal);
            });
        } else {
            alert(data.error || 'Error loading details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading details');
    });
}

// Edit entry function
function editEntry(type, id) {
    fetch('process_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=view&type=${type}&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create edit form
            let formContent = `
                <div class="modal-header">
                    <h5 class="modal-title">Edit ${type.charAt(0).toUpperCase() + type.slice(1)}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="type" value="${type}">
                        <input type="hidden" name="id" value="${id}">
            `;

            if (type === 'employer') {
                formContent += `
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="${data.data.name || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Organization</label>
                        <input type="text" name="organisation_name" class="form-control" value="${data.data.organisation_name || ''}">
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" name="position" class="form-control" value="${data.data.position || ''}">
                    </div>
                    <div class="form-group">
                        <label>Hiring Count</label>
                        <input type="number" name="hiring_count" class="form-control" value="${data.data.hiring_count || ''}">
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="city_state" class="form-control" value="${data.data.city_state || ''}">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="${data.data.email || ''}">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" class="form-control" value="${data.data.phone || ''}">
                    </div>
                    <div class="form-group">
                        <label>Requirements</label>
                        <textarea name="requirements" class="form-control">${data.data.requirements || ''}</textarea>
                    </div>
                `;
            } else {
                formContent += `
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="${data.data.name || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" name="position" class="form-control" value="${data.data.position || ''}">
                    </div>
                    <div class="form-group">
                        <label>Experience</label>
                        <select name="fresher_experienced" class="form-control">
                            <option value="Fresher" ${data.data.fresher_experienced === 'Fresher' ? 'selected' : ''}>Fresher</option>
                            <option value="Experienced" ${data.data.fresher_experienced === 'Experienced' ? 'selected' : ''}>Experienced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Experience Years</label>
                        <input type="number" name="experience_years" class="form-control" value="${data.data.experience_years || '0'}">
                    </div>
                    <div class="form-group">
                        <label>Skills/Degree</label>
                        <input type="text" name="skills_degree" class="form-control" value="${data.data.skills_degree || ''}">
                    </div>
                    <div class="form-group">
                        <label>Location Preference</label>
                        <input type="text" name="location_preference" class="form-control" value="${data.data.location_preference || ''}">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="${data.data.email || ''}">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" class="form-control" value="${data.data.phone || ''}">
                    </div>
                    <div class="form-group">
                        <label>Comments</label>
                        <textarea name="comments" class="form-control">${data.data.comments || ''}</textarea>
                    </div>
                `;
            }

            formContent += `
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveEdit()">Save Changes</button>
                </div>
            `;

            // Create and show modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        ${formContent}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            $(modal).modal('show');
            
            // Remove modal when hidden
            $(modal).on('hidden.bs.modal', function () {
                document.body.removeChild(modal);
            });
        } else {
            alert(data.error || 'Error loading record for editing');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading record for editing');
    });
}

// Save edit function
function saveEdit() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    
    fetch('process_actions.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Record updated successfully');
            location.reload();
        } else {
            alert(data.error || 'Error updating record');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating record');
    });
}

// Delete entry function
function deleteEntry(type, id) {
    if (confirm(`Are you sure you want to delete this ${type} enquiry?`)) {
        fetch('process_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&type=${type}&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Record deleted successfully');
                location.reload();
            } else {
                alert(data.error || 'Error deleting record');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting record');
        });
    }
}

// Export to CSV function
function exportToCSV(type) {
    window.location.href = `export_csv.php?type=${type}`;
}

// Apply filters function
function applyFilters() {
    const params = new URLSearchParams();
    
    // Get active tab
    const activeTab = document.querySelector('.nav-link.active').getAttribute('href').split('=')[1];
    params.set('tab', activeTab);
    
    // Get filter values
    const positionFilter = document.getElementById('positionFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    if (positionFilter) params.set('position', positionFilter);
    if (dateFilter) params.set('date', dateFilter);
    
    // Reload page with filters
    window.location.href = `dashboard.php?${params.toString()}`;
}

// Reset filters function
function resetFilters() {
    const activeTab = document.querySelector('.nav-link.active').getAttribute('href').split('=')[1];
    window.location.href = `dashboard.php?tab=${activeTab}`;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if on analytics tab
    if (document.getElementById('enquiriesChart')) {
        initCharts();
    }
    
    // Search functionality
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const activeTable = document.querySelector('.data-table-container:not([style*="display: none"]) table');
            
            if (activeTable) {
                const rows = activeTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
});




// Dark Mode Functionality
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeIcon = darkModeToggle.querySelector('.fa-moon');
    const lightModeIcon = darkModeToggle.querySelector('.fa-sun');
    
    // Check for saved user preference
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'enabled') {
        enableDarkMode();
    }
    
    // Toggle dark mode
    darkModeToggle.addEventListener('click', function() {
        if (document.body.classList.contains('dark-mode')) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    });
    
    function enableDarkMode() {
        document.body.classList.add('dark-mode');
        darkModeIcon.style.display = 'none';
        lightModeIcon.style.display = 'inline-block';
        localStorage.setItem('darkMode', 'enabled');
    }
    
    function disableDarkMode() {
        document.body.classList.remove('dark-mode');
        darkModeIcon.style.display = 'inline-block';
        lightModeIcon.style.display = 'none';
        localStorage.setItem('darkMode', 'disabled');
    }
});