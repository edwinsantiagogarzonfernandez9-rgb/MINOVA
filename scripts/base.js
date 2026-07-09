document.addEventListener("DOMContentLoaded", async () => {

    try {
        // Cache-busting: fuerza descarga fresca de base.html en cada carga
        const response = await fetch("../php/base.php?v=" + Date.now());
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const html = await response.text();

        document.getElementById("base-container").innerHTML = html;

        // ─────────────────────────────
        // MARCAR ITEM ACTIVO SIDEBAR
        // ─────────────────────────────
        const pagina = window.location.pathname.split('/').pop();


        const mapa = {
            'index.php': 'inicio',
            'equipos.php': 'equipos',
            'reportes.php': 'reportes',
            'uso_diario.php': 'diario',
            'usuarios.php': 'usuarios',
            'ubicaciones.php': 'ubicaciones',
            'maquinas.php': 'maquinas',
            'herramientas_con.php': 'consumibles',
            'acerca_de.php': 'acerca de',
            'ayuda.php': 'ayuda'
        };

        const activo = mapa[pagina];

        document.querySelectorAll('.sidebar li').forEach(li => {

            li.classList.remove('active');

            if (activo && li.textContent.trim().toLowerCase().includes(activo)) {
                li.classList.add('active');
            }

        });

        // ─────────────────────────────
        // INSERTAR CONTENIDO DE PÁGINA
        // ─────────────────────────────
        const plantilla = document.getElementById("page-content");

        if (plantilla) {

            document
                .getElementById("contenido")
                .appendChild(plantilla.content.cloneNode(true));

            // MOSTRAR FECHA DESPUÉS DE INSERTAR HTML
            mostrarFechaActual();

        }

        // Asignar eventos DESPUÉS de que todo el HTML esté en el DOM
        _iniciarEventos();

        _cargarMinovita();

        // Disparar evento para que otros scripts sepan que el contenido ya está listo
        document.dispatchEvent(new Event("baseLoaded"));

    } catch (error) {

        console.error("Error cargando base:", error);

    }

});


/* ─────────────────────────────────────────
   inyeccion minovita 
───────────────────────────────────────── */
function _cargarMinovita() {

    if (document.getElementById("chatToggle")) return; // ya inyectado

    const widgetHTML = `
        <div id="chatToggle" class="minovita-btn" title="MINOVITA IA">
            <div class="orbit orbit1"></div>
            <div class="orbit orbit2"></div>
            <div class="online-dot"></div>
            <div class="face"><span class="eye"></span><span class="eye"></span></div>
            <div class="notif-badge" id="notifBadge">1</div>
        </div>

        <div id="chatWindow" role="dialog" aria-label="Chat MINOVITA IA">
            <div class="cw-header">
                <div class="cw-avatar"><i class="fa-solid fa-robot"></i></div>
                <div class="cw-info">
                    <h4>Asistente MINOVITA</h4>
                    <p><span class="cw-dot"></span> en línea</p>
                </div>
                <div class="cw-header-actions">
                    <button class="cw-header-btn" id="cwClear" title="Limpiar conversación">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                    <button class="cw-header-btn" id="cwClose" title="Cerrar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="cw-messages" id="cwMessages"></div>
            <div class="cw-chips" id="cwChips" style="display:none;"></div>
            <div class="cw-input-area">
                <textarea id="cwInput" placeholder="Pregúntale algo a MINOVA IA…" rows="1"></textarea>
                <button id="cwSend" title="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
        <div id="toast">✅ Copiado</div>
    `;
    document.body.insertAdjacentHTML("beforeend", widgetHTML);

    // Cargar CSS del widget
    const css = document.createElement("link");
    css.id = "minovita-css";
    css.rel = "stylesheet";
    css.href = "../minovita/estilos_minovita.css";
    document.head.appendChild(css);

    // Cargar JS del widget en orden: config primero, luego la lógica
    const scriptConfig = document.createElement("script");
    scriptConfig.src = "../minovita/config_minovita.js";
    scriptConfig.onload = () => {
        const scriptWidget = document.createElement("script");
        scriptWidget.src = "../minovita/widget_minovita.js";
        document.body.appendChild(scriptWidget);
    };
    document.body.appendChild(scriptConfig);

}


function _iniciarEventos() {

    // ── Notificaciones y perfil ──
    const btnCampana = document.getElementById('btnCampana');
    const btnPerfil = document.getElementById('btnPerfil');

    if (btnCampana) {

        btnCampana.addEventListener('click', () => {

            toggleOverlay('overlayAlertas', 'overlayPerfil');

        });

   }
   if (btnPerfil) {

    btnPerfil.addEventListener('click', () => {

        toggleOverlay('overlayPerfil', 'overlayAlertas');

    });

  }

    // Listener ESC — función nombrada para evitar duplicados si _iniciarEventos se re-invoca
    document.removeEventListener('keydown', _onEsc);
    document.addEventListener('keydown', _onEsc);

    // ── Modal genérico ──
    const overlay       = document.getElementById("overlay");
    const abrirModal    = document.getElementById("abrirModal");
    const cerrarModal   = document.getElementById("cerrarModal");
    const backdropModal = document.getElementById("backdropModal");

    if (overlay && abrirModal) {

        abrirModal.addEventListener("click", () => {

            overlay.classList.add("open");

            if (backdropModal) {

                backdropModal.classList.add("active");

            }

        });

    }
    if (overlay && cerrarModal) {
        cerrarModal.addEventListener("click", () => {
            overlay.classList.remove("open");
            if (backdropModal) backdropModal.classList.remove("active");
        });
    }
    if (overlay && backdropModal) {

        backdropModal.addEventListener("click", () => {

            overlay.classList.remove("open");
            backdropModal.classList.remove("active");
        });

    }

    // ── Sidebar toggle ──
    const btnSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("contenido");
    if (btnSidebar && sidebar) {
        btnSidebar.addEventListener("click", () => {
            sidebar.classList.toggle("cerrado");
            btnSidebar.classList.toggle("cerrado");
            main.classList.toggle("cerrado");
        });
    }


    // ── Menú desplegable ──
    document.querySelectorAll(".menu-titulo").forEach(menu => {
        menu.addEventListener("click", function () {
            this.parentElement.classList.toggle("activo");
        });
    });

    // ── Panel de accesibilidad ──
    const btnAccesibilidad = document.getElementById('btnAccesibilidad');
    const panelAccesibilidad = document.getElementById('panelAccesibilidad');
    const cerrarAccesibilidad = document.getElementById('cerrarAccesibilidad');

    if (btnAccesibilidad && panelAccesibilidad) {
        btnAccesibilidad.addEventListener('click', () => {
            panelAccesibilidad.classList.toggle('activo');
        });
    }

    if (cerrarAccesibilidad && panelAccesibilidad) {
        cerrarAccesibilidad.addEventListener('click', () => {
            panelAccesibilidad.classList.remove('activo');
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && panelAccesibilidad?.classList.contains('activo')) {
            panelAccesibilidad.classList.remove('activo');
        }
    });

    // ── desplegar control del texto ──
    const controlTexto = document.getElementById("contenedorTexto");

    controlTexto.addEventListener("click", function(e){

    // Evita que al pulsar + o - vuelva a cerrar el panel
    if(e.target.classList.contains("btn-texto")) return;

    this.classList.toggle("activo");

});

/*==========================================
=      PREFERENCIAS DE ACCESIBILIDAD       =
==========================================*/

const preferenciasDefecto = {

    tamanoTexto: 3,      // Nivel por defecto (16px)
    modoOscuro: false,
    altoContraste: false

};

const preferencias = {

    ...preferenciasDefecto

};

const guardadas = localStorage.getItem("accesibilidad");

if (guardadas) {

    Object.assign(preferencias, JSON.parse(guardadas));

}

function guardarPreferencias(){

    localStorage.setItem(
        "accesibilidad",
        JSON.stringify(preferencias)
    );

}

  
//constantes de cada boton de accesibilidad
const btnMas = document.getElementById("mas");
const btnMenos = document.getElementById("menos");
const indicador = document.getElementById("nivelTexto");
const btnModoOscuro = document.getElementById("modoOscuro");
const btnContraste =document.getElementById("altoContraste");
const btnReset = document.getElementById("reset");

const btnLeer = document.getElementById("leer");
  /*==========================================
=         CONTROL TAMAÑO DE TEXTO          =
==========================================*/


// Niveles disponibles
// 13px (-3)
// 14px (-2)
// 15px (-1)
// 16px (0)
// 17px (+1)
// 18px (+2)
// 19px (+3)
// 20px (+4)

const niveles = [13, 14, 15, 16, 17, 18, 19, 20];

function actualizarTexto(){

    document.documentElement.style.fontSize =
        niveles[preferencias.tamanoTexto] + "px";

    const nivel = preferencias.tamanoTexto - 3;

    indicador.textContent = nivel > 0 ? "+" + nivel : nivel;

    btnMenos.disabled = preferencias.tamanoTexto === 0;

    btnMas.disabled =
        preferencias.tamanoTexto === niveles.length-1;
    

    guardarPreferencias();

}


/* ---------- Aumentar ---------- */

btnMas.addEventListener("click",e=>{

    e.stopPropagation();

    if(preferencias.tamanoTexto < niveles.length-1){

        preferencias.tamanoTexto++;

        actualizarTexto();

    }

});

/* ---------- Disminuir ---------- */

btnMenos.addEventListener("click",e=>{

    e.stopPropagation();

    if(preferencias.tamanoTexto > 0){

        preferencias.tamanoTexto--;

        actualizarTexto();

    }

});

/* =========================
   Modo oscuro
   ========================= */
   if(btnModoOscuro){
    btnModoOscuro.addEventListener("click",()=>{

        preferencias.modoOscuro = !preferencias.modoOscuro;

        aplicarModoOscuro();
    });
   }


    //APLICAR MODO OSCUROOOOOOOOOOOO
    function aplicarModoOscuro(){

        document.documentElement.classList.toggle(
            "dark-theme",
            preferencias.modoOscuro
        );

          btnModoOscuro.classList.toggle(
            "activo",
            preferencias.modoOscuro
        );
        guardarPreferencias();
    }


/* =========================
   LEER / DETENER
========================= */

    let leyendo = false;

    if(btnLeer){
        btnLeer.addEventListener("click", ()=>{
            if(!leyendo){
                const texto = document.body.innerText;
                const voz = new SpeechSynthesisUtterance(texto);

                voz.onend = ()=> leyendo = false;

                speechSynthesis.speak(voz);
                leyendo = true;
            }else{
                speechSynthesis.cancel();
                leyendo = false;
            }
        });
    }

    /* =========================
   Alto contraste
========================= */

    btnContraste.addEventListener("click",()=>{

        preferencias.altoContraste =
            !preferencias.altoContraste;

            aplicarContraste();

        });

        function aplicarContraste(){

            document.documentElement.classList.toggle(
                "alto-contraste",
                preferencias.altoContraste
            );

            btnContraste.classList.toggle(
                "activo",
                preferencias.altoContraste
            );

            guardarPreferencias();
        }

         /* =========================
             RESETEAR ACCESIBILIDA
        ========================= */
        
        btnReset.addEventListener("click",restablecerPreferencias);
        function restablecerPreferencias(){

            Object.assign(
            preferencias,
            preferenciasDefecto
            );
            actualizarTexto();
            aplicarModoOscuro();
            aplicarContraste();
        };

       /* ---------- GUARDAR preferencias ---------- */

        actualizarTexto();
        aplicarModoOscuro();
        aplicarContraste();
    
    // ── Re-escanear iconos Font Awesome tras inyección dinámica ──
    if (window.FontAwesome) FontAwesome.dom.i2svg();
}


function _onEsc(e) {
    if (e.key === 'Escape') cerrarTodos();
}

function toggleOverlay(idAbrir, idCerrar) {

    const abrir = document.getElementById(idAbrir);
    const cerrar = document.getElementById(idCerrar);
    const backdrop = document.getElementById('backdrop');

    if (!abrir || !cerrar || !backdrop) return;

    const yaAbierto = abrir.classList.contains('open');

    cerrar.classList.remove('open');

    if (yaAbierto) {

        abrir.classList.remove('open');
        backdrop.classList.remove('active');

    } else {

        abrir.classList.add('open');
        backdrop.classList.add('active');

    }

}

/* ─────────────────────────────────────────
   CERRAR TODOS LOS OVERLAYS
───────────────────────────────────────── */
function cerrarTodos() {

    const overlayAlertas = document.getElementById('overlayAlertas');
    const overlayPerfil = document.getElementById('overlayPerfil');
    const backdrop = document.getElementById('backdrop');

    if (overlayAlertas) {
        overlayAlertas.classList.remove('open');
    }

    if (overlayPerfil) {
        overlayPerfil.classList.remove('open');
    }

    if (backdrop) {
        backdrop.classList.remove('active');
    }

}

function marcarTodas() {

    document.querySelectorAll('.alert-row.unread').forEach(r => {

        r.classList.remove('unread');

    });

    const badge = document.getElementById('bellBadge');

    if (badge) {

        badge.style.display = 'none';

    }

}
function irPerfil() {
    window.location.href = 'perfil.php';
    cerrarTodos();

}

function irSuperadmin() {
    window.location.href = 'superadmin.php';
    cerrarTodos();

}

function cerrarSesion() {

    if (confirm('¿Deseas cerrar sesión?')) {
        window.location.href = '../php/Iniciar_sesion.php';
    }

}

function mostrarFechaActual() {

    const fecha = new Date();

    const opciones = {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        weekday: 'long'
    };
    
    const fechaFormateada = fecha.toLocaleDateString('es-ES', opciones);

    const elementoFecha = document.getElementById('fechaActual');

    if (elementoFecha) {

        elementoFecha.textContent = fechaFormateada;

    }

}
function toggleFaq(item) {
            item.classList.toggle('open');
        }