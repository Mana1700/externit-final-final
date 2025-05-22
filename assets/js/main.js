// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});

// File upload preview
function previewFile(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.querySelector(previewElement);
            if (preview) {
                if (input.files[0].type.startsWith('image/')) {
                    preview.src = e.target.result;
                } else {
                    preview.textContent = input.files[0].name;
                }
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Dynamic form fields
function addFormField(containerId, template) {
    const container = document.getElementById(containerId);
    if (container) {
        const newField = template.cloneNode(true);
        container.appendChild(newField);
    }
}

function removeFormField(element) {
    const field = element.closest('.form-field');
    if (field) {
        field.remove();
    }
}

// Toast notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} show`;
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">${message}</div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Confirmation dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// AJAX helper function
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
} 