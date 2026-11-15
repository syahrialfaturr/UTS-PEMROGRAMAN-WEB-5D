/**
 * E-Library - Vanilla JavaScript
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Interaktivitas Front-End & Validasi Form
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Password Visibility Toggle
    const toggleBtns = document.querySelectorAll('.toggle-password-btn');
    toggleBtns.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.textContent = 'Sembunyikan';
                    btn.setAttribute('aria-label', 'Sembunyikan password');
                } else {
                    input.type = 'password';
                    btn.textContent = 'Tampilkan';
                    btn.setAttribute('aria-label', 'Tampilkan password');
                }
            }
        });
    });

    // 2. Client-side Realtime Validation for Registration
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const feedbackEl = document.getElementById('confirmPasswordFeedback');
        const submitBtn = document.getElementById('registerSubmitBtn');

        function validatePasswordMatch() {
            if (!passwordInput || !confirmInput || !feedbackEl) return true;

            const p1 = passwordInput.value;
            const p2 = confirmInput.value;

            if (p2 === '') {
                feedbackEl.textContent = '';
                confirmInput.style.borderColor = '';
                return false;
            }

            if (p1 !== p2) {
                feedbackEl.textContent = '⚠️ Konfirmasi password tidak cocok.';
                feedbackEl.style.color = '#dc2626';
                confirmInput.style.borderColor = '#dc2626';
                return false;
            } else {
                feedbackEl.textContent = '✓ Password cocok.';
                feedbackEl.style.color = '#16a34a';
                confirmInput.style.borderColor = '#16a34a';
                return true;
            }
        }

        if (passwordInput && confirmInput) {
            passwordInput.addEventListener('input', validatePasswordMatch);
            confirmInput.addEventListener('input', validatePasswordMatch);
        }

        registerForm.addEventListener('submit', (e) => {
            if (passwordInput.value.length < 6) {
                alert('Password minimal harus memiliki 6 karakter.');
                e.preventDefault();
                passwordInput.focus();
                return;
            }

            if (passwordInput.value !== confirmInput.value) {
                alert('Konfirmasi password tidak sesuai dengan password.');
                e.preventDefault();
                confirmInput.focus();
                return;
            }

            // Tampilkan status loading jika valid
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memproses Pendaftaran...';
            }
        });
    }

    // 3. Login Form Submit Feedback
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            const submitBtn = document.getElementById('loginSubmitBtn');

            if (!emailInput.value.trim() || !passInput.value) {
                alert('Silakan lengkapi email dan password.');
                e.preventDefault();
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memverifikasi...';
            }
        });
    }
});
