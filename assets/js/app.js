/**
 * ==========================================================================
 * E-LIBRARY INTERACTIVE JAVASCRIPT (VANILLA JS)
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Informatika Universitas Muhammadiyah Malang
 * Front-End, UI/UX, Aksesibilitas & Validasi Klien (Tanggung Jawab: Robi)
 * ==========================================================================
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    /* ----------------------------------------------------------------------
       1. RESPONSIVE MOBILE NAVIGATION TOGGLE
       ---------------------------------------------------------------------- */
    const navbar = document.querySelector('.navbar');
    const navMenu = document.querySelector('.navbar-nav');

    if (navbar && navMenu) {
        let toggleBtn = navbar.querySelector('.navbar-toggle');

        // Jika tombol toggle belum ada di markup, buat secara dinamis
        if (!toggleBtn) {
            toggleBtn = document.createElement('button');
            toggleBtn.className = 'navbar-toggle';
            toggleBtn.type = 'button';
            toggleBtn.setAttribute('aria-label', 'Buka menu navigasi');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.innerHTML = '☰';
            navbar.insertBefore(toggleBtn, navMenu);
        }

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = navMenu.classList.toggle('is-open');
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggleBtn.innerHTML = isOpen ? '✕' : '☰';
            toggleBtn.setAttribute('aria-label', isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        });

        // Tutup menu saat klik di luar area navbar
        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target) && navMenu.classList.contains('is-open')) {
                navMenu.classList.remove('is-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.innerHTML = '☰';
            }
        });

        // Tutup menu saat tombol ESC ditekan
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.classList.contains('is-open')) {
                navMenu.classList.remove('is-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.innerHTML = '☰';
                toggleBtn.focus();
            }
        });
    }

    /* ----------------------------------------------------------------------
       2. PASSWORD VISIBILITY TOGGLE
       ---------------------------------------------------------------------- */
    const passwordToggles = document.querySelectorAll('.toggle-password-btn');
    passwordToggles.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);

            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.textContent = isPassword ? 'Sembunyikan' : 'Tampilkan';
                btn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
                input.focus();
            }
        });
    });

    /* ----------------------------------------------------------------------
       3. MODAL KONFIRMASI AKSESIBEL (Custom Modal Dialog)
       Menggantikan window.confirm() bawaan peramban dengan dialog modern.
       ---------------------------------------------------------------------- */
    function showConfirmModal({
        title = 'Konfirmasi Aksi',
        message = 'Apakah Anda yakin ingin melanjutkan?',
        confirmText = 'Ya, Lanjutkan',
        cancelText = 'Batal',
        isDanger = false,
        onConfirm = () => {}
    }) {
        // Hapus modal lama jika ada yang tertinggal
        const existing = document.getElementById('elibraryConfirmModal');
        if (existing) existing.remove();

        // Buat markup modal
        const backdrop = document.createElement('div');
        backdrop.id = 'elibraryConfirmModal';
        backdrop.className = 'modal-backdrop';
        backdrop.setAttribute('role', 'dialog');
        backdrop.setAttribute('aria-modal', 'true');
        backdrop.setAttribute('aria-labelledby', 'modalTitle');

        const confirmBtnClass = isDanger ? 'btn btn-danger btn-sm' : 'btn btn-primary btn-sm';
        const iconHeader = isDanger ? '⚠️' : '❓';

        backdrop.innerHTML = `
            <div class="modal-box">
                <div class="modal-header">
                    <h3 id="modalTitle" class="modal-title">
                        <span>${iconHeader}</span> ${escapeHtml(title)}
                    </h3>
                    <button type="button" class="modal-close-btn" aria-label="Tutup dialog">&times;</button>
                </div>
                <div class="modal-body">
                    <p>${escapeHtml(message).replace(/\n/g, '<br>')}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline btn-sm modal-cancel-btn">${escapeHtml(cancelText)}</button>
                    <button type="button" class="${confirmBtnClass} modal-confirm-btn">${escapeHtml(confirmText)}</button>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);

        // Memicu animasi fade-in
        requestAnimationFrame(() => {
            backdrop.classList.add('is-active');
            const confirmBtn = backdrop.querySelector('.modal-confirm-btn');
            if (confirmBtn) confirmBtn.focus();
        });

        const closeModal = () => {
            backdrop.classList.remove('is-active');
            setTimeout(() => {
                if (backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
            }, 200);
        };

        backdrop.querySelector('.modal-close-btn').addEventListener('click', closeModal);
        backdrop.querySelector('.modal-cancel-btn').addEventListener('click', closeModal);

        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) closeModal();
        });

        backdrop.querySelector('.modal-confirm-btn').addEventListener('click', () => {
            closeModal();
            onConfirm();
        });

        backdrop.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // A. Intersepsi Form Hapus (Buku / Kategori)
    const deleteForms = document.querySelectorAll('.form-confirm-delete');
    deleteForms.forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const itemName = form.getAttribute('data-item-name') || 'item ini';

            showConfirmModal({
                title: 'Konfirmasi Penghapusan',
                message: `Apakah Anda yakin ingin menghapus "${itemName}"?\nData yang dihapus tidak dapat dipulihkan kembali.`,
                confirmText: '🗑️ Ya, Hapus',
                cancelText: 'Batal',
                isDanger: true,
                onConfirm: () => {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Menghapus...';
                    }
                    form.submit();
                }
            });
        });
    });

    // B. Intersepsi Form Pengembalian Buku
    const returnForms = document.querySelectorAll('.form-confirm-return');
    returnForms.forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const bookTitle = form.getAttribute('data-item-name') || 'e-book ini';

            showConfirmModal({
                title: 'Konfirmasi Pengembalian E-Book',
                message: `Kembalikan e-book "${bookTitle}" ke perpustakaan sekarang?`,
                confirmText: '↩️ Ya, Kembalikan',
                cancelText: 'Batal',
                isDanger: false,
                onConfirm: () => {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Memproses...';
                    }
                    form.submit();
                }
            });
        });
    });

    // C. Intersepsi Form Peminjaman Buku pada Halaman Detail
    const borrowForm = document.querySelector('form[action*="borrow.php"]');
    if (borrowForm) {
        borrowForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const bookTitleEl = document.querySelector('.detail-title');
            const bookTitle = bookTitleEl ? bookTitleEl.textContent.trim() : 'e-book ini';

            showConfirmModal({
                title: 'Konfirmasi Peminjaman E-Book',
                message: `Anda akan meminjam e-book:\n"${bookTitle}"\n\nMasa peminjaman standar adalah 7 hari kalender. Lanjutkan?`,
                confirmText: '📥 Pinjam E-Book',
                cancelText: 'Batal',
                isDanger: false,
                onConfirm: () => {
                    const submitBtn = borrowForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Memproses Peminjaman...';
                    }
                    borrowForm.submit();
                }
            });
        });
    }

    /* ----------------------------------------------------------------------
       4. CLIENT-SIDE VALIDATION: FORM REGISTRASI
       ---------------------------------------------------------------------- */
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const confirmFeedback = document.getElementById('confirmPasswordFeedback');
        const submitBtn = document.getElementById('registerSubmitBtn');

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function checkPasswordMatch() {
            if (!passwordInput || !confirmInput || !confirmFeedback) return true;
            const p1 = passwordInput.value;
            const p2 = confirmInput.value;

            if (p2 === '') {
                confirmFeedback.textContent = '';
                confirmInput.classList.remove('is-invalid', 'is-valid');
                return false;
            }

            if (p1 !== p2) {
                confirmFeedback.textContent = '⚠️ Konfirmasi kata sandi tidak cocok.';
                confirmFeedback.className = 'form-feedback';
                confirmInput.classList.add('is-invalid');
                confirmInput.classList.remove('is-valid');
                return false;
            } else {
                confirmFeedback.textContent = '✓ Kata sandi cocok.';
                confirmFeedback.className = 'form-feedback is-success';
                confirmInput.classList.remove('is-invalid');
                confirmInput.classList.add('is-valid');
                return true;
            }
        }

        if (passwordInput && confirmInput) {
            passwordInput.addEventListener('input', checkPasswordMatch);
            confirmInput.addEventListener('input', checkPasswordMatch);
        }

        registerForm.addEventListener('submit', (e) => {
            let isValid = true;
            let firstInvalidField = null;

            // Validasi Nama Lengkap
            if (nameInput) {
                const nameVal = nameInput.value.trim();
                if (nameVal.length < 3) {
                    nameInput.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = nameInput;
                } else {
                    nameInput.classList.remove('is-invalid');
                }
            }

            // Validasi Email
            if (emailInput) {
                const emailVal = emailInput.value.trim();
                if (!emailRegex.test(emailVal)) {
                    emailInput.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = emailInput;
                } else {
                    emailInput.classList.remove('is-invalid');
                }
            }

            // Validasi Panjang Password
            if (passwordInput) {
                if (passwordInput.value.length < 6) {
                    passwordInput.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = passwordInput;
                } else {
                    passwordInput.classList.remove('is-invalid');
                }
            }

            // Validasi Kesesuaian Password
            if (passwordInput && confirmInput) {
                if (passwordInput.value !== confirmInput.value) {
                    confirmInput.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = confirmInput;
                }
            }

            if (!isValid) {
                e.preventDefault();
                if (firstInvalidField) firstInvalidField.focus();
                return;
            }

            // Status Loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mendaftarkan Akun...';
            }
        });
    }

    /* ----------------------------------------------------------------------
       5. CLIENT-SIDE VALIDATION: FORM LOGIN
       ---------------------------------------------------------------------- */
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('loginSubmitBtn');

        loginForm.addEventListener('submit', (e) => {
            let isValid = true;

            if (emailInput && !emailInput.value.trim()) {
                emailInput.classList.add('is-invalid');
                emailInput.focus();
                isValid = false;
            } else if (emailInput) {
                emailInput.classList.remove('is-invalid');
            }

            if (passwordInput && !passwordInput.value) {
                passwordInput.classList.add('is-invalid');
                if (isValid) passwordInput.focus();
                isValid = false;
            } else if (passwordInput) {
                passwordInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memverifikasi...';
            }
        });
    }

    /* ----------------------------------------------------------------------
       6. CLIENT-SIDE VALIDATION & PREVIEW: FORM BUKU ADMIN
       ---------------------------------------------------------------------- */
    const bookForm = document.getElementById('bookForm');
    if (bookForm) {
        const titleInput = document.getElementById('title');
        const authorInput = document.getElementById('author');
        const categorySelect = document.getElementById('category_id');
        const yearInput = document.getElementById('year');
        const descInput = document.getElementById('description');
        const coverInput = document.getElementById('cover');
        const coverPreview = document.getElementById('coverPreview');
        const saveBtn = document.getElementById('saveBookBtn');
        const currentYear = new Date().getFullYear();

        // Realtime Preview Sampul Buku
        if (coverInput && coverPreview) {
            const updateCover = () => {
                const url = coverInput.value.trim();
                if (url) {
                    coverPreview.src = url;
                } else {
                    coverPreview.src = '../assets/images/default-book.png';
                }
            };

            coverInput.addEventListener('input', updateCover);
            coverPreview.addEventListener('error', () => {
                coverPreview.src = '../assets/images/default-book.png';
            });
        }

        bookForm.addEventListener('submit', (e) => {
            let isValid = true;
            let firstInvalid = null;

            if (titleInput && titleInput.value.trim().length < 2) {
                titleInput.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalid) firstInvalid = titleInput;
            } else if (titleInput) {
                titleInput.classList.remove('is-invalid');
            }

            if (authorInput && authorInput.value.trim().length < 2) {
                authorInput.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalid) firstInvalid = authorInput;
            } else if (authorInput) {
                authorInput.classList.remove('is-invalid');
            }

            if (categorySelect && !categorySelect.value) {
                categorySelect.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalid) firstInvalid = categorySelect;
            } else if (categorySelect) {
                categorySelect.classList.remove('is-invalid');
            }

            if (yearInput) {
                const y = parseInt(yearInput.value, 10);
                if (isNaN(y) || y < 1500 || y > currentYear + 2) {
                    yearInput.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalid) firstInvalid = yearInput;
                } else {
                    yearInput.classList.remove('is-invalid');
                }
            }

            if (descInput && descInput.value.trim().length < 5) {
                descInput.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalid) firstInvalid = descInput;
            } else if (descInput) {
                descInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Menyimpan Data...';
            }
        });
    }

    /* ----------------------------------------------------------------------
       7. FLASH ALERT AUTO-DISMISS & CLOSE BUTTON
       ---------------------------------------------------------------------- */
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alertEl) => {
        // Tambahkan tombol tutup jika belum ada
        if (!alertEl.querySelector('.alert-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'alert-close';
            closeBtn.setAttribute('aria-label', 'Tutup pemberitahuan');
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', () => {
                alertEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                alertEl.style.opacity = '0';
                alertEl.style.transform = 'translateY(-6px)';
                setTimeout(() => alertEl.remove(), 300);
            });
            alertEl.appendChild(closeBtn);
        }

        // Auto dismiss untuk notifikasi sukses setelah 6 detik
        if (alertEl.classList.contains('alert-success')) {
            let timeoutId = setTimeout(() => {
                alertEl.style.transition = 'opacity 0.5s ease';
                alertEl.style.opacity = '0';
                setTimeout(() => alertEl.remove(), 500);
            }, 6000);

            // Jeda jika pengguna mengarahkan mouse ke alert
            alertEl.addEventListener('mouseenter', () => clearTimeout(timeoutId));
            alertEl.addEventListener('mouseleave', () => {
                timeoutId = setTimeout(() => {
                    alertEl.style.transition = 'opacity 0.5s ease';
                    alertEl.style.opacity = '0';
                    setTimeout(() => alertEl.remove(), 500);
                }, 3000);
            });
        }
    });

});
