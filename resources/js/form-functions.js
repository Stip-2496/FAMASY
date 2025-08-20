/**
 * Sistema de Manejo de Formularios - FAMASY
 * 
 * Este script centraliza todas las funciones de validación, formateo y sincronización
 * para formularios en el sistema. Incluye equivalentes JavaScript de funciones PHP.
 */

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('form')) {
        initFormValidations();
        initFieldFormatting();
        initPHPLikeValidations();
    }
});

document.addEventListener('livewire:initialized', () => {
    initLivewireSync();
});

/**
 * Equivalentes JavaScript de funciones PHP para validación
 */
const PHPFunctions = {
    // Equivalente a trim() - Elimina espacios al inicio y final
    trim: (str) => str.replace(/^\s+|\s+$/g, ''),
    
    // Equivalente a ucwords() - Capitaliza cada palabra
    ucwords: (str) => str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase()),
    
    // Equivalente a strpos() - Encuentra posición de subcadena
    strpos: (haystack, needle, offset = 0) => haystack.indexOf(needle, offset),
    
    // Equivalente a filter_var() para emails
    filter_var_email: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
    
    // Equivalente a preg_match() - Validación con regex
    preg_match: (pattern, str) => new RegExp(pattern).test(str),
    
    // Equivalente a htmlspecialchars() - Escapa caracteres HTML
    htmlspecialchars: (str) => str.replace(/[&<>"']/g, 
        char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char])),
    
    // Equivalente a strip_tags() - Elimina etiquetas HTML
    strip_tags: (str) => str.replace(/<[^>]*>/g, ''),
    
    // Equivalente a mb_strtolower() - Minúsculas considerando caracteres especiales
    mb_strtolower: (str) => str.toLowerCase(),
    
    // Equivalente a mb_strtoupper() - Mayúsculas considerando caracteres especiales
    mb_strtoupper: (str) => str.toUpperCase()
};

/**
 * Inicializa validaciones similares a las de PHP
 */
function initPHPLikeValidations() {
    // Validación de campos con funciones tipo PHP
    document.querySelectorAll('[data-php-validation]').forEach(field => {
        const validationType = field.dataset.phpValidation;
        
        field.addEventListener('blur', () => {
            const value = field.value;
            let isValid = true;
            let message = '';
            
            switch(validationType) {
                case 'email':
                    isValid = PHPFunctions.filter_var_email(value);
                    message = 'Correo electrónico inválido';
                    break;
                    
                case 'text-only':
                    isValid = PHPFunctions.preg_match('^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$', value);
                    message = 'Solo se permiten letras y espacios';
                    break;
                    
                case 'no-html':
                    isValid = value === PHPFunctions.strip_tags(value);
                    message = 'No se permiten etiquetas HTML';
                    break;
                    
                case 'no-special-chars':
                    isValid = value === PHPFunctions.htmlspecialchars(value);
                    message = 'Caracteres especiales no permitidos';
                    break;
            }
            
            if (!isValid && value) {
                showError(field, message);
            } else {
                clearError(field);
            }
        });
    });
}

/**
 * Inicializa las validaciones en tiempo real para todos los formularios
 */
function initFormValidations() {
    // Mapeo de tipos de campo a sus expresiones regulares y mensajes de error
    const fieldValidations = {
        'email': {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Por favor ingrese un correo electrónico válido'
        },
        'phone': {
            pattern: /^[\d\s\-\(\)]{7,15}$/,
            message: 'Ingrese un número de teléfono válido'
        },
        'document': {
            pattern: /^[0-9]{6,10}$/,
            message: 'Documento inválido (Solo maximo 10 numeros)'
        },
        'text-only': {
            pattern: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
            message: 'Solo se permiten letras y espacios'
        },
        'password': {
            pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/,
            message: 'Mínimo 8 caracteres, 1 mayúscula, 1 minúscula y 1 número'
        }
    };

    // Aplicar validaciones a campos con data-validation
    document.querySelectorAll('[data-validation]').forEach(field => {
        const validationType = field.dataset.validation;
        const validation = fieldValidations[validationType];
        
        if (validation) {
            field.addEventListener('blur', () => validateField(field, validation));
            field.addEventListener('input', () => clearError(field));
        }
    });

    // Validación de confirmación de contraseña
    document.querySelectorAll('[data-confirm]').forEach(field => {
        const originalFieldId = field.dataset.confirm;
        const originalField = document.getElementById(originalFieldId);
        
        if (originalField) {
            field.addEventListener('blur', () => {
                if (field.value !== originalField.value) {
                    showError(field, 'Las contraseñas no coinciden');
                }
            });
        }
    });
}

/**
 * Inicializa el formateo automático de campos
 */
function initFieldFormatting() {
    // Formatear nombres propios (usando ucwords similar a PHP)
    document.querySelectorAll('[data-format="capitalize"]').forEach(field => {
        field.addEventListener('blur', () => {
            if (field.value) {
                field.value = PHPFunctions.ucwords(PHPFunctions.trim(field.value));
            }
        });
    });

    // Formatear documentos (agregar guiones)
    document.querySelectorAll('[data-format="document"]').forEach(field => {
        field.addEventListener('blur', () => {
            if (field.value) {
                const cleanValue = field.value.replace(/\D/g, '');
                if (cleanValue.length >= 6) {
                    field.value = cleanValue.replace(/(\d{3})(\d{3})(\d+)/, '$1-$2-$3');
                }
            }
        });
    });

    // Auto-trim de campos (similar a PHP trim())
    document.querySelectorAll('[data-trim="true"]').forEach(field => {
        field.addEventListener('blur', () => {
            field.value = PHPFunctions.trim(field.value);
        });
    });
}

/**
 * Inicializa la sincronización de campos en Livewire
 */
function initLivewireSync() {
    const syncFields = (sourceField, targetField) => {
        if (sourceField && targetField) {
            sourceField.addEventListener('input', () => {
                targetField.value = sourceField.value;
                if (targetField.hasAttribute('wire:model')) {
                    targetField.dispatchEvent(new Event('input'));
                }
            });
            
            if (sourceField.value) {
                targetField.value = sourceField.value;
            }
        }
    };
    
    document.querySelectorAll('[data-sync-from]').forEach(targetField => {
        const sourceModel = targetField.dataset.syncFrom;
        const sourceField = document.querySelector(`[wire\\:model="${sourceModel}"]`);
        syncFields(sourceField, targetField);
    });
    
    document.querySelectorAll('[data-sync-to]').forEach(sourceField => {
        const targetId = sourceField.dataset.syncTo;
        const targetField = document.getElementById(targetId);
        syncFields(sourceField, targetField);
    });
}

/**
 * Valida un campo individual contra una expresión regular
 */
function validateField(field, validation) {
    if (field.value && !validation.pattern.test(field.value)) {
        showError(field, validation.message);
        return false;
    }
    clearError(field);
    return true;
}

/**
 * Muestra un mensaje de error para un campo
 */
function showError(field, message) {
    clearError(field);
    
    const errorElement = document.createElement('p');
    errorElement.className = 'mt-1 text-sm text-red-600';
    errorElement.textContent = message;
    errorElement.id = `${field.id}-error`;
    
    field.classList.add('border-red-500');
    field.insertAdjacentElement('afterend', errorElement);
}

/**
 * Limpia los errores de un campo
 */
function clearError(field) {
    const errorElement = document.getElementById(`${field.id}-error`);
    if (errorElement) {
        errorElement.remove();
    }
    field.classList.remove('border-red-500');
}

/**
 * Funciones utilitarias para ser llamadas desde otros scripts
 */
export const FormUtils = {
    /**
     * Valida todos los campos de un formulario
     */
    validateForm: (form) => {
        let isValid = true;
        
        form.querySelectorAll('[data-validation], [data-php-validation]').forEach(field => {
            if (field.dataset.validation) {
                const validationType = field.dataset.validation;
                const validation = fieldValidations[validationType];
                if (validation && !validateField(field, validation)) {
                    isValid = false;
                }
            } else if (field.dataset.phpValidation) {
                // Validación tipo PHP
                const value = field.value;
                const validationType = field.dataset.phpValidation;
                let fieldValid = true;
                
                switch(validationType) {
                    case 'email':
                        fieldValid = PHPFunctions.filter_var_email(value);
                        break;
                    case 'text-only':
                        fieldValid = PHPFunctions.preg_match('^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$', value);
                        break;
                    case 'no-html':
                        fieldValid = value === PHPFunctions.strip_tags(value);
                        break;
                    case 'no-special-chars':
                        fieldValid = value === PHPFunctions.htmlspecialchars(value);
                        break;
                }
                
                if (!fieldValid && value) {
                    showError(field, `Validación de ${validationType} falló`);
                    isValid = false;
                }
            }
        });
        
        return isValid;
    },
    
    /**
     * Formatea todos los campos de un formulario
     */
    formatFormFields: (form) => {
        form.querySelectorAll('[data-format]').forEach(field => {
            const formatType = field.dataset.format;
            
            if (formatType === 'capitalize' && field.value) {
                field.value = PHPFunctions.ucwords(PHPFunctions.trim(field.value));
            }
            
            if (formatType === 'document' && field.value) {
                const cleanValue = field.value.replace(/\D/g, '');
                if (cleanValue.length >= 6) {
                    field.value = cleanValue.replace(/(\d{3})(\d{3})(\d+)/, '$1-$2-$3');
                }
            }
        });
    },
    
    /**
     * Limpia los valores de un formulario (trim)
     */
    cleanFormFields: (form) => {
        form.querySelectorAll('[data-trim="true"]').forEach(field => {
            field.value = PHPFunctions.trim(field.value);
        });
    },
    
    /**
     * Escapa caracteres especiales (htmlspecialchars)
     */
    escapeHtml: (form) => {
        form.querySelectorAll('[data-escape="true"]').forEach(field => {
            field.value = PHPFunctions.htmlspecialchars(field.value);
        });
    },
    
    /**
     * Elimina etiquetas HTML (strip_tags)
     */
    stripTags: (form) => {
        form.querySelectorAll('[data-strip-tags="true"]').forEach(field => {
            field.value = PHPFunctions.strip_tags(field.value);
        });
    }
};

/**
 * Hook global para asegurar limpieza antes de enviar
 * Funciona tanto en formularios normales como en Livewire Volt (wire:submit.prevent)
 */
document.addEventListener('submit', function (e) {
    if (e.target.tagName.toLowerCase() === 'form') {
        FormUtils.cleanFormFields(e.target);
        FormUtils.formatFormFields(e.target);
    }
}, true);

// Exportar funciones PHP-like para uso en otros módulos
export const PHPJS = PHPFunctions;