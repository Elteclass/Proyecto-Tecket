document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const steps = document.querySelectorAll('.step');
    const formSteps = document.querySelectorAll('.form-step');
    const requestForm = document.getElementById('request-form');
    const verifyForm = document.getElementById('verify-form');
    const resetForm = document.getElementById('reset-form');
    
    // Elementos de navegación
    const backToStep1 = document.getElementById('back-to-step-1');
    const backToStep2 = document.getElementById('back-to-step-2');
    
    // Elementos de verificación - ACTUALIZADO PARA 4 DÍGITOS
    const verificationDigits = document.querySelectorAll('.verification-digit');
    const timerElement = document.getElementById('timer');
    const resendBtn = document.getElementById('resend-btn');
    
    // Elementos de contraseña
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');
    const passwordMatch = document.getElementById('password-match');
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    
    // Elementos de requisitos
    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number')
    };
    
    let currentStep = 1;
    let timerInterval;
    
    // Función para cambiar de paso
    function goToStep(stepNumber) {
        // Actualizar indicadores de paso
        steps.forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index + 1 < stepNumber) {
                step.classList.add('completed');
            } else if (index + 1 === stepNumber) {
                step.classList.add('active');
            }
        });
        
        // Mostrar formulario correspondiente
        formSteps.forEach((formStep, index) => {
            formStep.classList.remove('active');
            if (index + 1 === stepNumber || (stepNumber === 4 && index === 3)) {
                formStep.classList.add('active');
            }
        });
        
        currentStep = stepNumber;
    }
    
    // Función para mostrar notificaciones
    function showNotification(message, type = 'info') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }
    
    // Manejar envío del formulario de solicitud
    requestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        
        // Aquí iría la llamada AJAX al backend
        // Por ahora, simulamos el éxito
        document.getElementById('sent-email').textContent = email;
        goToStep(2);
        startTimer(300); // 5 minutos
        showNotification('Código de 4 dígitos enviado exitosamente', 'success');
        
        // Enfocar el primer dígito
        verificationDigits[0].focus();
    });
    
    // Manejar envío del formulario de verificación - ACTUALIZADO PARA 4 DÍGITOS
    verifyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obtener código completo (4 dígitos)
        let code = '';
        verificationDigits.forEach(digit => {
            code += digit.value;
        });
        
        if (code.length !== 4) {
            showNotification('Por favor, ingresa el código completo de 4 dígitos', 'error');
            // Agregar efecto de error a los campos vacíos
            verificationDigits.forEach(digit => {
                if (!digit.value) {
                    digit.parentElement.classList.add('error');
                    setTimeout(() => {
                        digit.parentElement.classList.remove('error');
                    }, 500);
                }
            });
            return;
        }
        
        // Validar que todos sean números
        if (!/^\d{4}$/.test(code)) {
            showNotification('El código debe contener solo números', 'error');
            return;
        }
        
        // Aquí iría la verificación del código
        // Por ahora, simulamos el éxito
        goToStep(3);
        showNotification('Código de 4 dígitos verificado correctamente', 'success');
    });
    
    // Manejar envío del formulario de restablecimiento
    resetForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (newPassword !== confirmPassword) {
            showNotification('Las contraseñas no coinciden', 'error');
            return;
        }
        
        if (!isPasswordValid(newPassword)) {
            showNotification('La contraseña no cumple con los requisitos', 'error');
            return;
        }
        
        // Aquí iría la actualización de la contraseña
        // Por ahora, simulamos el éxito
        goToStep(4);
        showNotification('Contraseña restablecida exitosamente', 'success');
    });
    
    // Navegación entre pasos
    backToStep1.addEventListener('click', () => {
        goToStep(1);
        // Limpiar campos de verificación
        verificationDigits.forEach(digit => {
            digit.value = '';
            digit.classList.remove('filled');
        });
    });
    
    backToStep2.addEventListener('click', () => goToStep(2));
    
    // Manejar inputs de verificación - ACTUALIZADO PARA 4 DÍGITOS
    verificationDigits.forEach((digit, index) => {
        // Solo permitir números
        digit.addEventListener('input', function(e) {
            // Remover cualquier carácter que no sea número
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value.length === 1) {
                this.classList.add('filled');
                // Auto-avanzar al siguiente campo
                if (index < verificationDigits.length - 1) {
                    verificationDigits[index + 1].focus();
                } else {
                    // Si es el último dígito, verificar si el código está completo
                    let code = '';
                    verificationDigits.forEach(d => code += d.value);
                    if (code.length === 4) {
                        // Código completo, podrías auto-enviar aquí si quieres
                        showNotification('Código completo ingresado', 'info');
                    }
                }
            } else {
                this.classList.remove('filled');
            }
        });
        
        // Manejar teclas especiales
        digit.addEventListener('keydown', function(e) {
            // Permitir: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            
            // Asegurar que solo sean números (0-9)
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
            
            // Manejar backspace
            if (e.key === 'Backspace') {
                if (this.value.length === 0 && index > 0) {
                    verificationDigits[index - 1].focus();
                    verificationDigits[index - 1].value = '';
                    verificationDigits[index - 1].classList.remove('filled');
                } else {
                    this.classList.remove('filled');
                }
            }
            
            // Manejar flechas de navegación
            if (e.key === 'ArrowLeft' && index > 0) {
                verificationDigits[index - 1].focus();
            }
            if (e.key === 'ArrowRight' && index < verificationDigits.length - 1) {
                verificationDigits[index + 1].focus();
            }
        });
        
        // Manejar pegado de código completo
        digit.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            const numbers = pastedData.replace(/[^0-9]/g, '');
            
            if (numbers.length === 4) {
                verificationDigits.forEach((d, i) => {
                    d.value = numbers[i] || '';
                    if (d.value) {
                        d.classList.add('filled');
                    }
                });
                verificationDigits[3].focus(); // Enfocar el último dígito
                showNotification('Código pegado correctamente', 'success');
            }
        });
    });
    
    // Temporizador para reenvío
    function startTimer(seconds) {
        let timeLeft = seconds;
        resendBtn.disabled = true;
        
        timerInterval = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                resendBtn.disabled = false;
                timerElement.textContent = '00:00';
            }
            
            timeLeft--;
        }, 1000);
    }
    
    // Reenviar código
    resendBtn.addEventListener('click', function() {
        if (!this.disabled) {
            // Limpiar campos de verificación
            verificationDigits.forEach(digit => {
                digit.value = '';
                digit.classList.remove('filled');
            });
            
            startTimer(300);
            showNotification('Nuevo código de 4 dígitos enviado', 'success');
            verificationDigits[0].focus();
        }
    });
    
    // Mostrar/ocultar contraseña
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('ion-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('name', 'eye-off-outline');
            } else {
                input.type = 'password';
                icon.setAttribute('name', 'eye-outline');
            }
        });
    });
    
    // Validación de fortaleza de contraseña
    function checkPasswordStrength(password) {
        let strength = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password) || /[^a-zA-Z0-9]/.test(password)
        };
        
        Object.values(checks).forEach(check => {
            if (check) strength += 25;
        });
        
        return { strength, checks };
    }
    
    function isPasswordValid(password) {
        const { checks } = checkPasswordStrength(password);
        return Object.values(checks).every(check => check);
    }
    
    // Actualizar indicador de fortaleza
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const { strength, checks } = checkPasswordStrength(password);
        
        // Actualizar barra de fortaleza
        strengthFill.style.width = strength + '%';
        
        if (strength <= 25) {
            strengthFill.style.background = '#F44336';
            strengthText.textContent = 'Muy débil';
            strengthText.style.color = '#F44336';
        } else if (strength <= 50) {
            strengthFill.style.background = '#FF9800';
            strengthText.textContent = 'Débil';
            strengthText.style.color = '#FF9800';
        } else if (strength <= 75) {
            strengthFill.style.background = '#2196F3';
            strengthText.textContent = 'Buena';
            strengthText.style.color = '#2196F3';
        } else {
            strengthFill.style.background = '#4CAF50';
            strengthText.textContent = 'Fuerte';
            strengthText.style.color = '#4CAF50';
        }
        
        // Actualizar requisitos
        Object.keys(checks).forEach(key => {
            const requirement = requirements[key];
            const icon = requirement.querySelector('ion-icon');
            
            if (checks[key]) {
                requirement.classList.add('valid');
                icon.setAttribute('name', 'checkmark-circle-outline');
            } else {
                requirement.classList.remove('valid');
                icon.setAttribute('name', 'close-circle-outline');
            }
        });
    });
    
    // Verificar coincidencia de contraseñas
    confirmPasswordInput.addEventListener('input', function() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = this.value;
        
        if (confirmPassword === '') {
            passwordMatch.textContent = '';
            passwordMatch.className = 'password-match';
        } else if (newPassword === confirmPassword) {
            passwordMatch.textContent = '✓ Las contraseñas coinciden';
            passwordMatch.className = 'password-match valid';
        } else {
            passwordMatch.textContent = '✗ Las contraseñas no coinciden';
            passwordMatch.className = 'password-match invalid';
        }
    });
});