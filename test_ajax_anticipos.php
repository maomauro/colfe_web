<!DOCTYPE html>
<html>
<head>
    <title>Test AJAX Anticipos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Test AJAX Anticipos</h2>
    
    <button id="testBtn">Probar AJAX Anticipo ID 3</button>
    
    <div id="resultado"></div>
    
    <script>
        $(document).ready(function() {
            $('#testBtn').click(function() {
                var datos = new FormData();
                datos.append("idAnticipo", "3");
                
                $.ajax({
                    url: "ajax/anticipos.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    beforeSend: function() {
                        console.log("Enviando datos:", datos);
                        $('#resultado').html("Enviando datos...");
                    },
                    success: function(respuesta) {
                        console.log("Respuesta recibida:", respuesta);
                        $('#resultado').html("<pre>" + JSON.stringify(respuesta, null, 2) + "</pre>");
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en AJAX:", error);
                        console.error("Status:", status);
                        console.error("Response:", xhr.responseText);
                        $('#resultado').html("<p style='color: red;'>Error: " + error + "</p><pre>" + xhr.responseText + "</pre>");
                    }
                });
            });
        });
    </script>
</body>
</html>
