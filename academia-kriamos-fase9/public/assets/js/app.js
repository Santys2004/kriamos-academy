document.addEventListener('DOMContentLoaded', function () {
    const tipoEmpleado = document.getElementById('tipoEmpleado');
    const tipoContratista = document.getElementById('tipoContratista');
    const employeeFields = document.querySelectorAll('.employee-field');
    const companyInput = document.querySelector('input[name="company"]');
    const departmentSelect = document.querySelector('select[name="department"]');

    function toggleEmployeeFields() {
        const isEmployee = tipoEmpleado && tipoEmpleado.checked;
        employeeFields.forEach(field => {
            field.style.display = isEmployee ? '' : 'none';
        });
        if (departmentSelect) {
            departmentSelect.required = isEmployee;
            if (!isEmployee) departmentSelect.value = '';
        }
        if (companyInput && isEmployee && companyInput.value.trim() === '') {
            companyInput.value = 'KRIAMOS S.A.S.';
        }
    }

    if (tipoEmpleado && tipoContratista) {
        tipoEmpleado.addEventListener('change', toggleEmployeeFields);
        tipoContratista.addEventListener('change', toggleEmployeeFields);
        toggleEmployeeFields();
    }

    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
