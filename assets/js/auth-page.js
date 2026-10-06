/**
 * Auth pages — email OTP sign-in
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-auth-page]');
    if (!page) {
        return;
    }

    const config = window.EDDILICIOUS_AUTH || {};
    const mode = page.dataset.authMode || 'signin';
    const redirect = page.dataset.authRedirect || 'account.php';

    const emailStep = page.querySelector('[data-auth-step="email"]');
    const otpStep = page.querySelector('[data-auth-step="otp"]');
    const alertEl = page.querySelector('[data-auth-alert]');
    const emailForm = page.querySelector('[data-auth-email-form]');
    const emailInput = page.querySelector('[data-auth-email]');
    const emailSubmit = page.querySelector('[data-auth-email-submit]');
    const otpEmailEl = page.querySelector('[data-auth-otp-email]');
    const otpDigits = Array.from(page.querySelectorAll('[data-auth-otp-digit]'));
    const otpHint = page.querySelector('[data-auth-otp-hint]');
    const resendBtn = page.querySelector('[data-auth-resend]');
    const resendTimer = page.querySelector('[data-auth-resend-timer]');
    const backBtn = page.querySelector('[data-auth-back]');

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

        return apiPost('api/auth/send-otp.php', {
            email: email,
            purpose: mode,
        }).then(function (result) {
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

        return apiPost('api/auth/verify-otp.php', {
            email: currentEmail,
            code: code,
            purpose: mode,
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
            showAlert('We could not verify the code. Try again.');
        });
    }

    function getOtpValue() {
        return otpDigits.map(function (input) {
            return input.value.trim();
        }).join('');
    }

    function handleOtpInput(index, event) {
        const input = otpDigits[index];
        const value = input.value.replace(/\D/g, '');
        input.value = value.slice(-1);
        input.classList.toggle('is-filled', input.value !== '');
        input.classList.remove('is-error');

        if (value && index < otpDigits.length - 1) {
            otpDigits[index + 1].focus();
        }

        const code = getOtpValue();
        if (code.length === otpDigits.length) {
            verifyOtp(code);
        }
    }

    function handleOtpKeydown(index, event) {
        if (event.key === 'Backspace' && !otpDigits[index].value && index > 0) {
            otpDigits[index - 1].focus();
        }
    }

    function handleOtpPaste(event) {
        event.preventDefault();
        const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);

        pasted.split('').forEach(function (char, i) {
            if (otpDigits[i]) {
                otpDigits[i].value = char;
                otpDigits[i].classList.add('is-filled');
            }
        });

        if (pasted.length === 6) {
            verifyOtp(pasted);
        } else if (otpDigits[pasted.length]) {
            otpDigits[pasted.length].focus();
        }
    }

    if (emailForm) {
        emailForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const email = (emailInput?.value || '').trim();
            if (!email) {
                showAlert('Enter your email address.');
                return;
            }

            sendOtp(email);
        });
    }

    otpDigits.forEach(function (input, index) {
        input.addEventListener('input', function (event) {
            handleOtpInput(index, event);
        });
        input.addEventListener('keydown', function (event) {
            handleOtpKeydown(index, event);
        });
    });

    if (otpDigits[0]) {
        otpDigits[0].addEventListener('paste', handleOtpPaste);
    }

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            showStep('email');
        });
    }

    if (resendBtn) {
        resendBtn.addEventListener('click', function () {
            if (!currentEmail || resendBtn.disabled) {
                return;
            }

            sendOtp(currentEmail);
        });
    }
})();
