document.addEventListener('DOMContentLoaded', function() {
    // Elementos del formulario
    const paymentMethodSelect = document.getElementById('payment_method');
    const cardFields = document.getElementById('cardFields');
    const transferFields = document.getElementById('transferFields');
    const cardNumberInput = document.getElementById('card_number');
    const expiryDateInput = document.getElementById('expiry_date');
    const cvvInput = document.getElementById('cvv');
    const transferReceiptInput = document.getElementById('transfer_receipt');
    const paymentForm = document.getElementById('paymentForm');

    // Mostrar/ocultar campos según método de pago
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const method = this.value;
            
            // Ocultar todos los campos
            cardFields.style.display = 'none';
            transferFields.style.display = 'none';
            
            // Mostrar campos según método seleccionado
            if (method === 'credit_card' || method === 'debit_card') {
                cardFields.style.display = 'block';
            } else if (method === 'bank_transfer') {
                transferFields.style.display = 'block';
            }
        });
    }

    // Formatear número de tarjeta
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }

    // Formatear fecha de expiración
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }

    // Validación del formulario de pago
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            if (!validatePaymentForm()) {
                e.preventDefault();
            }
        });
    }

    function validatePaymentForm() {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const paymentMethod = document.getElementById('payment_method').value;
        const membershipId = document.getElementById('membership_id').value;

        // Validar información básica
        if (name === '') {
            alert('Por favor ingresa tu nombre completo');
            return false;
        }

        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Por favor ingresa un email válido');
            return false;
        }

        // Validar teléfono
        if (phone === '') {
            alert('Por favor ingresa tu número de teléfono');
            return false;
        }

        // Validar plan seleccionado
        if (membershipId === '') {
            alert('Por favor selecciona un plan');
            return false;
        }

        // Validar método de pago
        if (paymentMethod === '') {
            alert('Por favor selecciona un método de pago');
            return false;
        }

        // Validaciones específicas por método de pago
        if (paymentMethod === 'credit_card' || paymentMethod === 'debit_card') {
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            const expiryDate = document.getElementById('expiry_date').value;
            const cvv = document.getElementById('cvv').value.trim();

            if (cardNumber.length < 16) {
                alert('Por favor ingresa un número de tarjeta válido');
                return false;
            }

            if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                alert('Por favor ingresa una fecha de expiración válida (MM/AA)');
                return false;
            }

            if (cvv.length < 3) {
                alert('Por favor ingresa un CVV válido');
                return false;
            }
        } else if (paymentMethod === 'bank_transfer') {
            const transferReceipt = document.getElementById('transfer_receipt');
            
            if (!transferReceipt.files || transferReceipt.files.length === 0) {
                alert('Por favor sube el comprobante de transferencia');
                return false;
            }
            
            const file = transferReceipt.files[0];
            const allowedTypes = ['application/pdf'];
            
            if (allowedTypes.indexOf(file.type) === -1) {
                alert('Solo se aceptan archivos PDF');
                return false;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('El archivo es demasiado grande (máximo 5MB)');
                return false;
            }
        }

        return true;
    }

    // Actualizar resumen cuando cambia el plan
    const membershipSelect = document.getElementById('membership_id');
    if (membershipSelect) {
        membershipSelect.addEventListener('change', updateOrderSummary);
    }

    function updateOrderSummary() {
        // Esta función se puede expandir para actualizar dinámicamente el resumen
        console.log('Plan seleccionado:', membershipSelect.value);
    }
});