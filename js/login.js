document.getElementById('formLogin').addEventListener('submit', function(e){
    e.preventDefault();
    const mensaje = document.getElementById('mensaje');
    const boton = this.querySelector('button[type="submit"]');
    boton.disabled = true;
    boton.innerHTML = 'Validando...';
    fetch('validar_login.php', { method:'POST', body:new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                mensaje.innerHTML = '<div class="alert alert-success">Acceso correcto. Redirigiendo...</div>';
                setTimeout(() => window.location.href = 'dashboard.php', 600);
            }else{
                mensaje.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                boton.disabled = false;
                boton.innerHTML = 'Iniciar sesión';
            }
        })
        .catch(() => {
            mensaje.innerHTML = '<div class="alert alert-danger">No se pudo validar el acceso.</div>';
            boton.disabled = false;
            boton.innerHTML = 'Iniciar sesión';
        });
});
