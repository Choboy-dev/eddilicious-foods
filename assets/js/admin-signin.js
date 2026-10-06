(function () {
    'use strict';

    const page = document.querySelector('[data-admin-signin]');
    if (!page) {
        return;
    }

    const config = window.EDDILICIOUS_ADMIN_AUTH || {};
    const redirect = page.dataset.adminRedirect || config.redirect || 'index.php';

    const emailStep = page.querySelector('[data-admin-auth-step="email"]');
    const otpStep = page.querySelector('[data-admin-auth-step="otp"]');
    const alertEl = page.querySelector('[data-admin-auth-alert]');
    const emailForm = page.querySelector('[data-admin-auth-email-form]');
    const emailInput = page.querySelector('[data-admin-auth-email]');
    const emailSubmit = page.querySelector('[data-admin-auth-email-submit]');
    const otpEmailEl = page.querySelector('[data-admin-auth-otp-email]');
    const otpDigits = Array.from(page.querySelectorAll('[data-admin-auth-otp-digit]'));
    const otpHint = page.querySelector('[data-admin-auth-otp-hint]');
    const resendBtn = page.querySelector('[data-admin-auth-resend]');
    const resendTimer = page.querySelector('[data-admin-auth-resend-timer]');
    const backBtn = page.querySelector('[data-admin-auth-back]');
    const customerSignOut = page.querySelector('[data-admin-customer-signout]');

    let currentEmail = '';
    let resendCountdown = null;

    function csrf() {
        return config.csrf || '';
    }

    function showAlert(message) {
        if (!alertEl) {
            return;
        }

        if (!message) {
            alertEl.hidden = true;
            alertEl.textContent = '';
            return;
        }

        alertEl.textContent = message;
        alertEl.hidden = false;
    }

    function setLoading(button, loading) {
        if (!button) {
            return;
        }

        button.disabled = loading;
        button.classList.toggle('is-loading', loading);
    }

    function showStep(step) {
        if (emailStep) {
            emailStep.hidden = step !== 'email';
        }
        if (otpStep) {
            otpStep.hidden = step !== 'otp';
        }
        showAlert('');
    }

    function apiPost(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(Object.assign({ csrf: csrf() }, payload)),
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            });
        });
    }

    function startResendCooldown(seconds) {
        if (!resendBtn || !resendTimer) {
            return;
        }

        let remaining = seconds;
        resendBtn.disabled = true;
        resendTimer.textContent = 'Resend available in ' + remaining + 's';

        if (resendCountdown) {
            window.clearInterval(resendCountdown);
        }

        resendCountdown = window.setInterval(function () {
            remaining -= 1;
            if (remaining <= 0) {
                window.clearInterval(resendCountdown);
                resendCountdown = null;
                resendBtn.disabled = false;
                resendTimer.textContent = '';
                return;
            }

            resendTimer.textContent = 'Resend available in ' + remaining + 's';
        }, 1000);
    }

    function sendOtp(email) {
        setLoading(emailSubmit, true);
        showAlert('');

        return apiPost('../api/admin/send-otp.php', { email: email }).then(function (result) {
            setLoading(emailSubmit, false);

            if (!result.data.ok) {
                showAlert(result.data.error || 'Something went wrong. Try again.');
                return false;
            }

            currentEmail = email;
            if (otpEmailEl) {
                otpEmailEl.textContent = email;
            }
            if (otpHint && result.data.expires_in) {
                otpHint.textContent = 'Code expires in ' + result.data.expires_in + ' minutes.';
            }

            otpDigits.forEach(function (input) {
                input.value = '';
                input.classList.remove('is-filled', 'is-error');
            });

            showStep('otp');
            startResendCooldown(30);
            if (otpDigits[0]) {
                otpDigits[0].focus();
            }

            return true;
        }).catch(function () {
            setLoading(emailSubmit, false);
            showAlert('We could not reach the server. Check your connection and try again.');
            return false;
        });
    }

    function verifyOtp(code) {
        showAlert('');
        otpDigits.forEach(function (input) {
            input.disabled = true;
        });

        return apiPost('../api/admin/verify-otp.php', {
            email: currentEmail,
            code: code,
            redirect: redirect,
        }).then(function (result) {
            otpDigits.forEach(function (input) {
                input.disabled = false;
            });

            if (!result.data.ok) {
                otpDigits.forEach(function (input) {
                    input.classList.add('is-error');
                });
                showAlert(result.data.error || 'That code did not work. Try again.');
                return;
            }

            window.location.href = result.data.redirect || redirect;
        }).catch(function () {
            otpDigits.forEach(function (input) {
                input.disabled = false;
            });
            showAlert('We could not reach the server. Check your connection and try again.');
        });
    }

    emailForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const email = (emailInput?.value || '').trim();
        if (!email) {
            showAlert('Enter your admin email.');
            return;
        }
        sendOtp(email);
    });

    backBtn?.addEventListener('click', function () {
        showStep('email');
    });

    resendBtn?.addEventListener('click', function () {
        if (currentEmail) {
            sendOtp(currentEmail);
        }
    });

    otpDigits.forEach(function (input, index) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            input.classList.toggle('is-filled', input.value !== '');
            input.classList.remove('is-error');

            if (input.value && otpDigits[index + 1]) {
                otpDigits[index + 1].focus();
            }

            const code = otpDigits.map(function (digit) {
                return digit.value;
            }).join('');

            if (code.length === 6) {
                verifyOtp(code);
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Backspace' && !input.value && otpDigits[index - 1]) {
                otpDigits[index - 1].focus();
            }
        });

        input.addEventListener('paste', function (event) {
            event.preventDefault();
            const pasted = (event.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 6);
            if (!pasted) {
                return;
            }

            pasted.split('').forEach(function (char, i) {
                if (otpDigits[i]) {
                    otpDigits[i].value = char;
                    otpDigits[i].classList.add('is-filled');
                }
            });

            if (pasted.length === 6) {
                verifyOtp(pasted);
            }
        });
    });

    customerSignOut?.addEventListener('submit', async function (event) {
        event.preventDefault();
        try {
            await fetch('../api/auth/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf(),
                },
                body: '{}',
            });
        } catch (error) {
            // Continue to reload.
        }
        window.location.reload();
    });
})();
