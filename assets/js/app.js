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

    // 4. Admin Book Form Validation & UX
    const bookForm = document.getElementById('bookForm');
    if (bookForm) {
        const titleInput = document.getElementById('title');
        const authorInput = document.getElementById('author');
        const categorySelect = document.getElementById('category_id');
        const yearInput = document.getElementById('year');
        const descInput = document.getElementById('description');
        const coverInput = document.getElementById('cover');
        const coverPreview = document.getElementById('coverPreview');
        const currentYear = new Date().getFullYear();

        // Realtime Cover Preview
        if (coverInput && coverPreview) {
            coverInput.addEventListener('input', () => {
                const val = coverInput.value.trim();
                if (val) {
                    coverPreview.src = val;
                } else {
                    coverPreview.src = '../assets/images/default-book.png';
                }
            });
            coverPreview.addEventListener('error', () => {
                coverPreview.src = '../assets/images/default-book.png';
            });
        }

        bookForm.addEventListener('submit', (e) => {
            let isValid = true;
            let errorMsg = '';

            if (!titleInput.value.trim() || titleInput.value.trim().length < 2) {
                errorMsg = 'Judul e-book minimal harus memiliki 2 karakter.';
                titleInput.focus();
                isValid = false;
            } else if (!authorInput.value.trim() || authorInput.value.trim().length < 2) {
                errorMsg = 'Nama penulis minimal harus memiliki 2 karakter.';
                authorInput.focus();
                isValid = false;
            } else if (!categorySelect.value) {
                errorMsg = 'Silakan pilih kategori e-book.';
                categorySelect.focus();
                isValid = false;
            } else if (!yearInput.value || parseInt(yearInput.value) < 1500 || parseInt(yearInput.value) > currentYear + 2) {
                errorMsg = `Tahun terbit harus berupa angka yang valid (1500 - ${currentYear + 2}).`;
                yearInput.focus();
                isValid = false;
            } else if (!descInput.value.trim() || descInput.value.trim().length < 5) {
                errorMsg = 'Deskripsi e-book minimal harus memiliki 5 karakter.';
                descInput.focus();
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('⚠️ Validasi Form: ' + errorMsg);
            }
        });
    }

    // 5. Global Action Confirmation (Hapus Buku / Kategori)
    const deleteForms = document.querySelectorAll('.form-confirm-delete');
    deleteForms.forEach((form) => {
        form.addEventListener('submit', (e) => {
            const itemName = form.getAttribute('data-item-name') || 'item ini';
            const confirmed = confirm(`⚠️ Konfirmasi Penghapusan:\nApakah Anda yakin ingin menghapus "${itemName}"?\n\nData yang telah dihapus tidak dapat dipulihkan.`);
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });

    // 6. Flash Alert Auto Dismiss (opsional setelah 6 detik)
    const flashAlerts = document.querySelectorAll('.alert');
    flashAlerts.forEach((alertEl) => {
        setTimeout(() => {
            alertEl.style.transition = 'opacity 0.5s ease';
            alertEl.style.opacity = '0.4';
        }, 6000);
    });
});

