/**
 * Login Page JavaScript
 * Enhanced Features with Form Validation
 */

// ========== TOGGLE PASSWORD VISIBILITY ==========
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️';
    }
}

// ========== FILL DEMO CREDENTIALS ==========
function fillDemo(username, password) {
    document.getElementById('username').value = username;
    document.getElementById('password').value = password;
    
    // Add highlight effect
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    usernameInput.style.borderColor = '#52c97e';
    passwordInput.style.borderColor = '#52c97e';
    
    setTimeout(() => {
        usernameInput.style.borderColor = '';
        passwordInput.style.borderColor = '';
    }, 1000);
}

// ========== FORM SUBMISSION ==========
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Show loading state
            btnLogin.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-flex';
            btnLoader.style.alignItems = 'center';
            btnLoader.style.gap = '8px';

            // Form validation
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username) {
                e.preventDefault();
                showError('Username atau email harus diisi');
                resetButton();
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                showError('Password minimal 6 karakter');
                resetButton();
                return false;
            }

            // Let form submit naturally
            // Reset button will happen on page reload or error
        });
    }

    // ========== AUTO-HIDE ALERTS ==========
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // ========== INPUT FOCUS EFFECTS ==========
    const inputs = document.querySelectorAll('input[type="text"], input[type="password"], input[type="email"]');
    
    inputs.forEach(function(input) {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // ========== KEYBOARD SHORTCUTS ==========
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            if (loginForm) {
                loginForm.submit();
            }
        }

        // ESC to clear form
        if (e.key === 'Escape') {
            if (confirm('Kosongkan form login?')) {
                loginForm.reset();
                document.getElementById('username').focus();
            }
        }
    });

    // ========== PASSWORD STRENGTH INDICATOR ==========
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strength = getPasswordStrength(this.value);
            updatePasswordStrengthUI(strength);
        });
    }

    // ========== REMEMBER ME PERSISTENCE ==========
    const rememberCheckbox = document.querySelector('input[name="remember"]');
    const usernameInput = document.getElementById('username');

    // Load saved username if exists
    const savedUsername = localStorage.getItem('remembered_username');
    if (savedUsername && usernameInput) {
        usernameInput.value = savedUsername;
        if (rememberCheckbox) {
            rememberCheckbox.checked = true;
        }
    }

    // Save username on form submit if remember is checked
    if (loginForm && rememberCheckbox) {
        loginForm.addEventListener('submit', function() {
            if (rememberCheckbox.checked) {
                localStorage.setItem('remembered_username', usernameInput.value);
            } else {
                localStorage.removeItem('remembered_username');
            }
        });
    }

    // ========== CAPS LOCK WARNING ==========
    passwordInput.addEventListener('keyup', function(e) {
        if (e.getModifierState && e.getModifierState('CapsLock')) {
            showCapsLockWarning();
        } else {
            hideCapsLockWarning();
        }
    });

    console.log('%c🔐 Login Page Loaded', 'color: #e89a6b; font-size: 14px; font-weight: bold;');
    console.log('%c⌨️  Shortcuts:', 'color: #52c97e; font-weight: bold;');
    console.log('   Ctrl/Cmd + Enter: Submit form');
    console.log('   ESC: Clear form');
});

// ========== HELPER FUNCTIONS ==========

function resetButton() {
    const btnLogin = document.getElementById('btnLogin');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');

    btnLogin.disabled = false;
    btnText.style.display = 'inline';
    btnLoader.style.display = 'none';
}

function showError(message) {
    // Remove existing error
    const existingError = document.querySelector('.alert-error');
    if (existingError) {
        existingError.remove();
    }

    const alertHTML = `
        <div class="alert alert-error">
            <span class="alert-icon">⚠️</span>
            <span class="alert-message">${message}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
    `;

    const loginBox = document.querySelector('.login-box');
    const form = document.getElementById('loginForm');
    
    if (loginBox && form) {
        form.insertAdjacentHTML('beforebegin', alertHTML);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert-error');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    }
}

function getPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    return strength;
}

function updatePasswordStrengthUI(strength) {
    // Remove existing indicator
    let indicator = document.getElementById('passwordStrength');
    if (indicator) {
        indicator.remove();
    }

    const passwordWrapper = document.querySelector('.input-wrapper');
    if (!passwordWrapper || strength === 0) return;

    const colors = ['#e87d7d', '#f0a878', '#f4b891', '#52c97e', '#3da864'];
    const labels = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];

    const indicatorHTML = `
        <div id="passwordStrength" style="margin-top: 8px; font-size: 0.85rem; color: ${colors[strength - 1]};">
            <div style="display: flex; gap: 4px; margin-bottom: 4px;">
                ${Array(5).fill(0).map((_, i) => 
                    `<div style="flex: 1; height: 3px; background: ${i < strength ? colors[strength - 1] : '#e2e8f0'}; border-radius: 2px;"></div>`
                ).join('')}
            </div>
            <span style="font-weight: 600;">${labels[strength - 1]}</span>
        </div>
    `;

    passwordWrapper.insertAdjacentHTML('afterend', indicatorHTML);
}

function showCapsLockWarning() {
    let warning = document.getElementById('capsLockWarning');
    if (warning) return;

    const passwordWrapper = document.querySelector('input[name="password"]').closest('.input-wrapper');
    
    const warningHTML = `
        <div id="capsLockWarning" style="
            margin-top: 8px;
            padding: 8px 12px;
            background: #fff3cd;
            color: #856404;
            border-radius: 6px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        ">
            <span>⚠️</span>
            <span>Caps Lock aktif</span>
        </div>
    `;

    passwordWrapper.insertAdjacentHTML('afterend', warningHTML);
}

function hideCapsLockWarning() {
    const warning = document.getElementById('capsLockWarning');
    if (warning) {
        warning.remove();
    }
}

// ========== PREVENT DOUBLE SUBMIT ==========
let isSubmitting = false;

document.addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    
    isSubmitting = true;
    
    // Reset after 3 seconds (in case of error)
    setTimeout(function() {
        isSubmitting = false;
    }, 3000);
});

// ========== ANIMATED BACKGROUND GRADIENT ==========
let hue = 0;
setInterval(function() {
    hue += 0.5;
    if (hue >= 360) hue = 0;
    
    const leftPanel = document.querySelector('.login-left');
    if (leftPanel) {
        // Subtle hue shift animation
        // leftPanel.style.filter = `hue-rotate(${hue}deg)`;
    }
}, 100);