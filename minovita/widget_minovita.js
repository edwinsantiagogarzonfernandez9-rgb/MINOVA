/* ════════════════════════════════════════════════════════
   MINOVITA — LÓGICA DEL WIDGET DE CHAT
════════════════════════════════════════════════════════ */

/* ══════════════════════════════════════════
   ESTADO DEL CHAT
══════════════════════════════════════════ */
let historial = [];
let enviando  = false;
let abierto   = false;

const win    = document.getElementById("chatWindow");
const toggle = document.getElementById("chatToggle");
const msgs   = document.getElementById("cwMessages");
const input  = document.getElementById("cwInput");
const send   = document.getElementById("cwSend");

/* ══════════════════════════════════════════
   PERSISTENCIA DEL HISTORIAL
══════════════════════════════════════════ */
function guardarHistorial() {
    try {
        localStorage.setItem(HISTORIAL_KEY, JSON.stringify(historial));
    } catch(e) {}
}

function cargarHistorial() {
    try {
        const data = localStorage.getItem(HISTORIAL_KEY);
        if (data) {
            historial = JSON.parse(data);
            // Reconstruir burbujas en el DOM
            historial.forEach(msg => {
                const role = msg.role === "user" ? "user" : "ia";
                agregarMensaje(role, msg.content, false);
            });
            return true;
        }
    } catch(e) {}
    return false;
}

/* ══════════════════════════════════════════
   UI: MENSAJES
══════════════════════════════════════════ */
const hora = () => new Date().toLocaleTimeString("es-CO", {hour:"2-digit", minute:"2-digit"});

function agregarMensaje(role, texto, guardar = true, esAdvertencia = false) {
    const row  = document.createElement("div");
    row.className = `msg-row ${role}`;

    const ico  = document.createElement("div");
    ico.className = `msg-icon ${role}`;
    ico.innerHTML = role === "ia"
        ? '<i class="fa-solid fa-robot"></i>'
        : '<i class="fa-solid fa-user"></i>';

    const wrap = document.createElement("div");
    wrap.className = "msg-wrap";

    const bub  = document.createElement("div");
    bub.className = esAdvertencia ? "msg-bubble msg-bubble--warning" : "msg-bubble";
    bub.innerHTML = fmt(texto);

    // Botón copiar en burbujas de la IA (no en advertencias)
    if (role === "ia" && !esAdvertencia) {
        const btnCopy = document.createElement("button");
        btnCopy.className = "btn-copy";
        btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i>';
        btnCopy.title = "Copiar respuesta";
        btnCopy.onclick = () => {
            navigator.clipboard.writeText(texto).then(() => {
                btnCopy.innerHTML = '<i class="fa-solid fa-check"></i>';
                btnCopy.classList.add("copiado");
                mostrarToast();
                setTimeout(() => {
                    btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i>';
                    btnCopy.classList.remove("copiado");
                }, 2000);
            });
        };
        bub.appendChild(btnCopy);
    }

    const t = document.createElement("div");
    t.className = "msg-time";
    t.textContent = hora();

    wrap.appendChild(bub);
    wrap.appendChild(t);
    row.appendChild(ico);
    row.appendChild(wrap);
    msgs.appendChild(row);
    msgs.scrollTop = msgs.scrollHeight;
    return bub;
}

function mostrarToast() {
    const toast = document.getElementById("toast");
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 1800);
}

function showTyping() {
    const row = document.createElement("div");
    row.className = "msg-row ia"; row.id = "typing";
    const ico = document.createElement("div");
    ico.className = "msg-icon ia";
    ico.innerHTML = '<i class="fa-solid fa-robot"></i>';
    const bub = document.createElement("div");
    bub.className = "typing-bubble";
    bub.innerHTML = '<span class="t-dot"></span><span class="t-dot"></span><span class="t-dot"></span>';
    row.appendChild(ico); row.appendChild(bub);
    msgs.appendChild(row);
    msgs.scrollTop = msgs.scrollHeight;
}
function hideTyping() { document.getElementById("typing")?.remove(); }

/* Formato Markdown básico */
function fmt(t) {
    return t
        .replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")
        .replace(/\*\*(.+?)\*\*/g,"<strong>$1</strong>")
        .replace(/\*(.+?)\*/g,"<em>$1</em>")
        .replace(/`(.+?)`/g,"<code>$1</code>")
        .replace(/^- (.+)$/gm,"<li>$1</li>")
        .replace(/(<li>[\s\S]*<\/li>)/g,"<ul>$1</ul>")
        .replace(/\n/g,"<br>");
}

/* ══════════════════════════════════════════
   STREAMING DE RESPUESTA (llamada a la API)
══════════════════════════════════════════ */
async function llamarAPIStream(mensajes) {
    const res = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            messages: mensajes
        })
    });

    if (!res.ok) {
        const e = await res.json().catch(() => ({}));
        const err = new Error(e?.error?.message || `Error ${res.status}`);
        err.code = e?.error?.code || res.status;
        throw err;
    }

    // chat_minovita.php (Gemini) responde con un solo bloque JSON,
    // no como stream SSE — lo leemos directo, sin parsear "data: ".
    const data = await res.json();
    const textoAcumulado = data?.content?.[0]?.text || "";
    return textoAcumulado;
}

/* ══════════════════════════════════════════
   ENVIAR MENSAJE
══════════════════════════════════════════ */
function enviarChip(txt) { input.value = txt; enviar(); }

input.addEventListener("input", () => {
    input.style.height = "auto";
    input.style.height = Math.min(input.scrollHeight, 96) + "px";
});
input.addEventListener("keydown", e => {
    if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); enviar(); }
});
send.addEventListener("click", enviar);

async function enviar() {
    const txt = input.value.trim();
    if (!txt || enviando) return;
    enviando = true;
    send.disabled = true;
    input.value = "";
    input.style.height = "auto";

    agregarMensaje("user", txt);
    historial.push({ role: "user", content: txt });
    showTyping();

    // Activar animación "hablando" en el botón
    toggle.classList.add("speaking");

    try {
        // Esperar la respuesta MIENTRAS se ve la animación de "escribiendo…"
        const respuesta = await llamarAPIStream(historial);

        // Recién ahora quitamos los puntitos y mostramos la burbuja con el texto
        hideTyping();
        const row  = document.createElement("div");
        row.className = "msg-row ia";
        const ico  = document.createElement("div");
        ico.className = "msg-icon ia";
        ico.innerHTML = '<i class="fa-solid fa-robot"></i>';
        const wrap = document.createElement("div");
        wrap.className = "msg-wrap";
        const bub  = document.createElement("div");
        bub.className = "msg-bubble";
        bub.innerHTML = fmt(respuesta);
        const t    = document.createElement("div");
        t.className = "msg-time";
        t.textContent = hora();
        wrap.appendChild(bub); wrap.appendChild(t);
        row.appendChild(ico); row.appendChild(wrap);
        msgs.appendChild(row);
        msgs.scrollTop = msgs.scrollHeight;

        // Agregar botón copiar
        const btnCopy = document.createElement("button");
        btnCopy.className = "btn-copy";
        btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i>';
        btnCopy.title = "Copiar respuesta";
        btnCopy.onclick = () => {
            navigator.clipboard.writeText(respuesta).then(() => {
                btnCopy.innerHTML = '<i class="fa-solid fa-check"></i>';
                btnCopy.classList.add("copiado");
                mostrarToast();
                setTimeout(() => {
                    btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i>';
                    btnCopy.classList.remove("copiado");
                }, 2000);
            });
        };
        bub.appendChild(btnCopy);

        historial.push({ role: "assistant", content: respuesta });
        if (historial.length > 30) historial = historial.slice(-30);
        guardarHistorial();

    } catch(err) {
        hideTyping();
        let msg;
        if (err.code === 429) {
            msg = "⏳ Se agotaron las respuestas gratuitas disponibles por ahora. Espera un minuto e intenta de nuevo.";
        } else if (err.code === 401) {
            msg = "⚠️ API key inválida. Configura tu clave.";
        } else {
            msg = "⚠️ Error al conectar con el servidor. Verifica tu conexión e intenta de nuevo.";
        }
        agregarMensaje("ia", msg, true, true);
    } finally {
        enviando = false;
        send.disabled = false;
        toggle.classList.remove("speaking");
        input.focus();
    }
}

/* ══════════════════════════════════════════
   LIMPIAR CONVERSACIÓN
══════════════════════════════════════════ */
document.getElementById("cwClear").addEventListener("click", () => {
    if (!confirm("¿Limpiar la conversación? Se borrará el historial.")) return;
    historial = [];
    msgs.innerHTML = "";
    document.getElementById("cwChips").style.display = "none";
    localStorage.removeItem(HISTORIAL_KEY);
    mensajeBienvenida();
});

/* ══════════════════════════════════════════
   ABRIR / CERRAR
══════════════════════════════════════════ */
document.getElementById("cwClose").addEventListener("click", () => {
    abierto = false;
    win.classList.remove("open");
    toggle.classList.remove("chat-open");
});

function mensajeBienvenida() {
    agregarMensaje("ia", "¡Hola! Soy **MINOVITA** 👷\n\nPuedo ayudarte con preguntas sobre minería, maquinaria, gases, seguridad, matemáticas aplicadas y más.\n\n¿En qué puedo ayudarte hoy?", false);
}

toggle.addEventListener("click", () => {
    abierto = !abierto;

    if (abierto) {
        const btnRect   = toggle.getBoundingClientRect();
        const anchoChat = win.offsetWidth || 288;
        const altoChat  = win.offsetHeight || 384;
        let left = btnRect.left - anchoChat + 80;
        let top  = btnRect.top  - altoChat + 60; // baja un poco la ventana respecto al botón
        if (left < 10) left = 10;
        if (top  < 10) top  = 10;
        win.style.left   = left + "px";
        win.style.top    = top  + "px";
        win.style.right  = "auto";
        win.style.bottom = "auto";

        // Ocultar badge de notificación
        document.getElementById("notifBadge").classList.remove("show");
    }

    win.classList.toggle("open", abierto);
    toggle.classList.toggle("chat-open", abierto);

    if (abierto) {
        if (historial.length === 0) {
            const tienePersistido = cargarHistorial();
            if (!tienePersistido) mensajeBienvenida();
        }
        setTimeout(() => input.focus(), 300);
    }
});

// Mostrar badge de notificación al iniciar (simula mensaje pendiente)
setTimeout(() => {
    if (!abierto) document.getElementById("notifBadge").classList.add("show");
}, 2000);