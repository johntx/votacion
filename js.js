$(document).ready(function() {
    // Cargar opciones de Región
    $.ajax({
        url: 'load_options.php',
        type: 'GET',
        data: { action: 'get_regiones' },
        success: function(data) {
            $('#region').html("<option selected disabled>Seleccione</option>"+data);
        }
    });

    // Actualizar Comuna al cambiar Región
    $('#region').change(function() {
        var selectedRegion = $(this).val();
        $.ajax({
            url: 'load_options.php',
            type: 'GET',
            data: { action: 'get_comunas', region: selectedRegion },
            success: function(data) {
                $('#comuna').html("<option selected disabled>Seleccione</option>"+data);
            }
        });
    });

    // Cargar opciones de Candidato
    $.ajax({
        url: 'load_options.php',
        type: 'GET',
        data: { action: 'get_candidatos' },
        success: function(data) {
            $('#candidato').html("<option selected disabled>Seleccione</option>"+data);
        }
    });

    // Validaciones del formulario con jQuery
    $('#votingForm').submit(function(event) {
        event.preventDefault();
        // Validar Nombre y Apellido
        if ($('#nombreApellido').val().trim() === '') {
            alert('Error: Nombre y Apellido no pueden estar en blanco.');
            event.preventDefault();
            return;
        }

        // Validar Alias
        var alias = $('#alias').val().trim();
        if (alias.length <= 5 || !/^[a-zA-Z0-9]+$/.test(alias)) {
            alert('Error: Alias debe tener más de 5 caracteres y contener solo letras y números.');
            event.preventDefault();
            return;
        }

        // Validar RUT
        var rut = $('#rut').val().trim();
        if (!validarRut(rut)) {
            alert('Error: RUT no válido.');
            event.preventDefault();
            return;
        }

        // Validar Email
        var email = $('#email').val().trim();
        if (!isValidEmail(email)) {
            alert('Error: Email no válido.');
            event.preventDefault();
            return;
        }

        // Validar Región y Comuna
        if ($('#region').val() === '' || $('#comuna').val() === '') {
            alert('Error: Región y Comuna no pueden estar en blanco.');
            event.preventDefault();
            return;
        }

        // Validar Candidato
        if ($('#candidato').val() === '') {
            alert('Error: Debe seleccionar un candidato.');
            event.preventDefault();
            return;
        }

        // Validar Checkbox "Como se enteró de nosotros"
        var comoSeEnteroSeleccionados = $('input[name="comoSeEntero[]"]:checked').length;
        if (comoSeEnteroSeleccionados < 2) {
            alert('Error: Debe seleccionar al menos dos opciones en "Cómo se enteró de nosotros".');
            event.preventDefault();
            return;
        }
        $.ajax({
            url: 'process.php',
            type: 'post',
            data: $('#votingForm').serialize(), // Serializar los datos del formulario
            success: function(response) {
                // Mostrar mensaje de éxito o error
                alert(response);

                // Limpiar el formulario
                $('#votingForm')[0].reset();
            },
            error: function() {
                alert('Error al procesar el formulario.');
            }
        });
    });

    // Función para validar RUT (puedes implementar la lógica necesaria)
    function validarRut(rut) {
        rut = rut.replace(/[^0-9kK]/g, '');

        if (!rut || rut.length < 3) {
            return false;
        }

        var splitRut = rut.split('');
        var cuerpo = splitRut.slice(0, -1).join('');
        var dv = splitRut[splitRut.length - 1].toLowerCase();

        if (!/^[0-9]+[kK]?$/.test(rut)) {
            return false;
        }

        var suma = 0;
        var multiplicador = 2;

        for (var i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo.charAt(i)) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }

        var resultado = (11 - suma % 11) % 11;
        var resultadoEsK = (resultado === 10) ? 'k' : resultado.toString();

        return resultadoEsK === dv;
    }

    // Función para validar Email
    function isValidEmail(email){
        var caract = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/);
        if (caract.test(email) == false){
            return false;
        }else{
            return true;
        }
    }
});