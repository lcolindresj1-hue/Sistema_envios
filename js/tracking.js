let intervaloTracking = null;

document.getElementById('formTracking').addEventListener('submit', function(e){
    e.preventDefault();
    iniciarTracking();
});

function iniciarTracking(){

    consultarTracking();

    if(intervaloTracking !== null){
        clearInterval(intervaloTracking);
    }

    intervaloTracking = setInterval(consultarTracking, 3000);
}

function consultarTracking(){

    const codigo = document.getElementById('codigo_guia').value.trim();

    const mensaje = document.getElementById('mensaje');

    if(codigo === ''){

        mensaje.innerHTML = `
            <div class="alert alert-warning">
                Ingrese un código de guía.
            </div>
        `;

        return;
    }

    fetch('api_tracking.php?codigo=' + encodeURIComponent(codigo))

        .then(res => res.json())

        .then(data => {

            if(!data.success){

                mensaje.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message}
                    </div>
                `;

                document.getElementById('resultado').style.display = 'none';

                return;
            }

            mensaje.innerHTML = `
                <div class="alert alert-success">
                    Tracking actualizado automáticamente.
                </div>
            `;

            document.getElementById('resultado').style.display = 'block';

            document.getElementById('codigo').innerText =
                data.envio.codigo_guia;

            document.getElementById('destinatario').innerText =
                data.envio.nombre_destinatario;

            document.getElementById('descripcion').innerText =
                data.envio.descripcion_paquete;

            document.getElementById('fecha').innerText =
                data.envio.fecha_registro;

            document.getElementById('estado').innerText =
                data.envio.nombre_estado;

            actualizarBarra(data.envio.nombre_estado);

            let filas = '';

            data.historial.forEach(item => {

                filas += `
                    <tr>
                        <td>${item.fecha_hora}</td>

                        <td>
                            <span class="badge-status">
                                ${item.nombre_estado}
                            </span>
                        </td>

                        <td>${item.comentario ?? ''}</td>
                    </tr>
                `;
            });

            document.getElementById('historial').innerHTML = filas;
        })

        .catch(error => {

            console.error(error);

            mensaje.innerHTML = `
                <div class="alert alert-danger">
                    Error al consultar el tracking.
                </div>
            `;
        });
}

function actualizarBarra(estado){

    let porcentaje = 0;

    switch(estado){

        case 'Paquete registrado':
            porcentaje = 15;
            break;

        case 'En oficina':
            porcentaje = 30;
            break;

        case 'En proceso de ruta':
            porcentaje = 45;
            break;

        case 'En ruta':
            porcentaje = 65;
            break;

        case 'En sede para recoger':
            porcentaje = 80;
            break;

        case 'Entregado a usuario':
            porcentaje = 100;
            break;

        case 'Entregado en sede':
            porcentaje = 100;
            break;

        case 'Cancelado':
            porcentaje = 100;
            break;

        default:
            porcentaje = 0;
            break;
    }

    const barra = document.getElementById('barra_progreso');

    barra.style.width = porcentaje + '%';

    barra.innerText = porcentaje + '%';

    if(estado === 'Cancelado'){
        barra.classList.remove('bg-success');
        barra.classList.add('bg-danger');
    } else {
        barra.classList.remove('bg-danger');
        barra.classList.add('bg-success');
    }
}
