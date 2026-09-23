document.addEventListener('DOMContentLoaded', () => {
  inicializarBuscadorTabla();
  inicializarModalEliminar('.btn-eliminar', 'modal-eliminar', 'modal-nombre', 'form-eliminar', 'modal-cancelar', 'index.php?accion=producto-eliminar');
  inicializarModalEliminar('.btn-eliminar-proveedor', 'modal-eliminar-proveedor', 'modal-nombre-proveedor', 'form-eliminar-proveedor', 'modal-cancelar-proveedor', 'index.php?accion=proveedor-eliminar');
  inicializarFilasDinamicas();
  inicializarPOS();
  inicializarModalImagen();
});

/**
 * Modal to view the product image in large size, without leaving the
 * page (it used to open the raw file in the browser, with no clear
 * way back).
 */
function inicializarModalImagen() {
  const modal = document.getElementById('modal-imagen');
  if (!modal) return;

  const img = document.getElementById('modal-imagen-img');
  const nombreEl = document.getElementById('modal-imagen-nombre');
  const btnCerrar = document.getElementById('modal-imagen-cerrar');

  document.querySelectorAll('.thumb-btn').forEach((boton) => {
    boton.addEventListener('click', () => {
      img.src = boton.dataset.imagen;
      nombreEl.textContent = boton.dataset.nombre;
      modal.hidden = false;
    });
  });

  const cerrar = () => { modal.hidden = true; img.src = ''; };
  btnCerrar.addEventListener('click', cerrar);
  modal.addEventListener('click', (e) => { if (e.target === modal) cerrar(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) cerrar(); });
}

/**
 * Live search over an already-loaded table (products), filtering by
 * the text stored in each row's data-texto attribute.
 */
function inicializarBuscadorTabla() {
  const input = document.getElementById('buscador');
  if (!input) return;

  const filas = document.querySelectorAll('#tabla-productos tbody tr[data-texto]');

  input.addEventListener('input', () => {
    const termino = input.value.trim().toLowerCase();
    filas.forEach((fila) => {
      const texto = fila.dataset.texto || '';
      fila.hidden = termino !== '' && !texto.includes(termino);
    });
  });
}

/**
 * Reusable, elegant confirmation modal for "Delete", instead of the
 * browser's native confirm().
 */
function inicializarModalEliminar(selectorBoton, idModal, idTexto, idForm, idCancelar, urlBase) {
  const modal = document.getElementById(idModal);
  if (!modal) return;

  const textoEl = document.getElementById(idTexto);
  const form = document.getElementById(idForm);
  const btnCancelar = document.getElementById(idCancelar);

  document.querySelectorAll(selectorBoton).forEach((boton) => {
    boton.addEventListener('click', () => {
      const id = boton.dataset.id;
      const nombre = boton.dataset.nombre;
      textoEl.textContent = `"${nombre}"`;
      form.action = `${urlBase}&id=${id}`;
      modal.hidden = false;
    });
  });

  const cerrar = () => { modal.hidden = true; };
  btnCancelar.addEventListener('click', cerrar);
  modal.addEventListener('click', (e) => { if (e.target === modal) cerrar(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) cerrar(); });
}

/**
 * Dynamic rows for the "Register stock-in" form: adds/removes
 * products, and auto-fills the suggested purchase price based on
 * the selected product.
 */
function inicializarFilasDinamicas() {
  const tabla = document.getElementById('tabla-items-entrada');
  const btnAgregar = document.getElementById('btn-agregar-fila');
  const plantilla = document.getElementById('plantilla-fila-entrada');
  if (!tabla || !btnAgregar || !plantilla) return;

  const cuerpo = tabla.querySelector('tbody');

  const autocompletarPrecio = (fila) => {
    const select = fila.querySelector('.select-producto');
    const inputPrecio = fila.querySelector('input[name="precio_compra[]"]');
    select.addEventListener('change', () => {
      const opcion = select.selectedOptions[0];
      const precio = opcion ? opcion.dataset.precio : '';
      if (precio && !inputPrecio.value) inputPrecio.value = precio;
    });
  };

  const activarQuitar = (fila) => {
    const btn = fila.querySelector('.btn-quitar-fila');
    btn.addEventListener('click', () => {
      if (cuerpo.querySelectorAll('.fila-item').length > 1) {
        fila.remove();
      }
    });
  };

  cuerpo.querySelectorAll('.fila-item').forEach((fila) => {
    autocompletarPrecio(fila);
    activarQuitar(fila);
  });

  btnAgregar.addEventListener('click', () => {
    const nodo = plantilla.content.cloneNode(true);
    const fila = nodo.querySelector('.fila-item');
    cuerpo.appendChild(fila);
    autocompletarPrecio(fila);
    activarQuitar(fila);
  });
}

/**
 * Point of sale (the "New sale" screen): live catalogue search,
 * in-memory cart, client-side stock validation (the real, final
 * validation always happens on the server inside a transaction —
 * this is only for quick feedback).
 */
function inicializarPOS() {
  const buscador = document.getElementById('buscador-pos');
  if (!buscador) return;

  const datosEl = document.getElementById('datos-productos');
  const productos = JSON.parse(datosEl.textContent);

  const listaEl = document.getElementById('pos-lista');
  const carritoEl = document.getElementById('carrito-items');
  const vacioEl = document.getElementById('carrito-vacio');
  const totalEl = document.getElementById('carrito-total-valor');
  const inputsContainer = document.getElementById('inputs-carrito');
  const btnConfirmar = document.getElementById('btn-confirmar-venta');

  /** cart: Map<productId, {producto, cantidad}> */
  const carrito = new Map();

  function renderLista(termino) {
    listaEl.innerHTML = '';
    if (!termino) return;

    const t = termino.toLowerCase();
    const coincidencias = productos.filter((p) =>
      p.nombre.toLowerCase().includes(t) || (p.codigo && p.codigo.includes(t))
    ).slice(0, 8);

    if (coincidencias.length === 0) {
      listaEl.innerHTML = '<p class="empty-inline">No results.</p>';
      return;
    }

    coincidencias.forEach((p) => {
      const div = document.createElement('div');
      div.className = 'pos-item';
      const sinStock = p.stock <= 0;
      div.innerHTML = `
        <div class="pos-item-info">
          <span class="pos-item-nombre">${escaparHtml(p.nombre)}</span>
          <span class="pos-item-meta">$${p.precio.toFixed(2)} · stock: ${p.stock} ${escaparHtml(p.unidad)}</span>
        </div>
        <div class="pos-item-accion">
          <input type="number" class="pos-item-cantidad" value="1" min="1" max="${p.stock}" ${sinStock ? 'disabled' : ''}>
          <button type="button" class="btn btn-sm ${sinStock ? 'btn-ghost' : 'btn-primary'}" ${sinStock ? 'disabled' : ''}>
            ${sinStock ? 'Out of stock' : '+ Add'}
          </button>
        </div>
      `;
      if (!sinStock) {
        const inputCant = div.querySelector('.pos-item-cantidad');
        div.querySelector('button').addEventListener('click', () => {
          const cantidad = Math.max(1, parseInt(inputCant.value, 10) || 1);
          agregarAlCarrito(p, cantidad);
        });
        // Enter inside the quantity field also adds the item, so the
        // cashier doesn't have to reach for the button — faster.
        inputCant.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            const cantidad = Math.max(1, parseInt(inputCant.value, 10) || 1);
            agregarAlCarrito(p, cantidad);
          }
        });
      }
      listaEl.appendChild(div);
    });
  }

  function agregarAlCarrito(producto, cantidadAgregar = 1) {
    const actual = carrito.get(producto.id);
    const cantidadActual = actual ? actual.cantidad : 0;
    const nuevaCantidad = cantidadActual + cantidadAgregar;

    if (nuevaCantidad > producto.stock) {
      alert(`Only ${producto.stock} units of "${producto.nombre}" are available.`);
      carrito.set(producto.id, { producto, cantidad: producto.stock });
      renderCarrito();
      return;
    }

    carrito.set(producto.id, { producto, cantidad: nuevaCantidad });
    renderCarrito();
  }

  function cambiarCantidad(idProducto, delta) {
    const item = carrito.get(idProducto);
    if (!item) return;
    establecerCantidad(idProducto, item.cantidad + delta);
  }

  /** Lets the user type the quantity directly instead of clicking multiple times. */
  function establecerCantidad(idProducto, cantidadDeseada) {
    const item = carrito.get(idProducto);
    if (!item) return;

    if (!Number.isFinite(cantidadDeseada) || cantidadDeseada <= 0) {
      carrito.delete(idProducto);
      renderCarrito();
      return;
    }

    if (cantidadDeseada > item.producto.stock) {
      alert(`Only ${item.producto.stock} units of "${item.producto.nombre}" are available.`);
      item.cantidad = item.producto.stock;
    } else {
      item.cantidad = cantidadDeseada;
    }

    renderCarrito();
  }

  function renderCarrito() {
    carritoEl.innerHTML = '';

    if (carrito.size === 0) {
      carritoEl.appendChild(vacioEl);
      vacioEl.hidden = false;
      btnConfirmar.disabled = true;
      totalEl.textContent = '$0.00';
      inputsContainer.innerHTML = '';
      return;
    }

    vacioEl.hidden = true;
    btnConfirmar.disabled = false;

    let total = 0;
    let inputsHtml = '';

    carrito.forEach(({ producto, cantidad }) => {
      const subtotal = producto.precio * cantidad;
      total += subtotal;

      const fila = document.createElement('div');
      fila.className = 'carrito-fila';
      fila.innerHTML = `
        <div class="carrito-fila-info">
          <span class="carrito-fila-nombre">${escaparHtml(producto.nombre)}</span>
          <span class="carrito-fila-precio">$${producto.precio.toFixed(2)} each</span>
        </div>
        <div class="carrito-fila-controles">
          <button type="button" class="btn-stepper" data-accion="restar">−</button>
          <input
            type="number"
            class="carrito-fila-input"
            value="${cantidad}"
            min="1"
            max="${producto.stock}"
            step="1"
          >
          <button type="button" class="btn-stepper" data-accion="sumar">+</button>
        </div>
        <span class="carrito-fila-subtotal">$${subtotal.toFixed(2)}</span>
      `;
      fila.querySelector('[data-accion="restar"]').addEventListener('click', () => cambiarCantidad(producto.id, -1));
      fila.querySelector('[data-accion="sumar"]').addEventListener('click', () => cambiarCantidad(producto.id, 1));

      const inputCantidad = fila.querySelector('.carrito-fila-input');
      inputCantidad.addEventListener('change', () => {
        const valor = parseInt(inputCantidad.value, 10);
        establecerCantidad(producto.id, valor);
      });

      carritoEl.appendChild(fila);

      inputsHtml += `
        <input type="hidden" name="id_producto[]" value="${producto.id}">
        <input type="hidden" name="cantidad[]" value="${cantidad}">
        <input type="hidden" name="precio_venta[]" value="${producto.precio}">
      `;
    });

    inputsContainer.innerHTML = inputsHtml;
    totalEl.textContent = `$${total.toFixed(2)}`;
  }

  function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
  }

  const inputCliente = document.getElementById('input-cliente-nombre');
  const clienteHint = document.getElementById('cliente-hint');

  if (inputCliente && clienteHint) {
    inputCliente.addEventListener('blur', async () => {
      const nombre = inputCliente.value.trim();
      if (!nombre) { clienteHint.hidden = true; return; }

      try {
        const resp = await fetch(`index.php?accion=cliente-info&nombre=${encodeURIComponent(nombre)}`);
        const info = await resp.json();

        if (info.frecuente) {
          clienteHint.textContent = `🎉 Frequent customer (${info.compras} purchases) — a ${info.descuento}% discount will be applied`;
          clienteHint.className = 'cliente-hint cliente-hint-ok';
        } else if (info.nuevo) {
          clienteHint.textContent = '👋 New customer — will be saved for future purchases';
          clienteHint.className = 'cliente-hint';
        } else {
          const faltan = 3 - info.compras;
          clienteHint.textContent = `${faltan} more purchase(s) needed to become a frequent customer`;
          clienteHint.className = 'cliente-hint';
        }
        clienteHint.hidden = false;
      } catch (err) {
        clienteHint.hidden = true;
      }
    });
  }

  buscador.addEventListener('input', () => renderLista(buscador.value.trim()));

  // The sale is submitted via fetch (not as a normal form post) so we
  // can tell "it was confirmed" (open the invoice in a new tab and
  // clear the cart) apart from "there was an error, e.g. no stock"
  // (show the error right here, without losing what the cashier
  // already had in the cart).
  const formSalida = document.getElementById('form-salida');
  const errorBox = document.getElementById('error-venta-js');
  const errorTexto = document.getElementById('error-venta-js-texto');

  if (formSalida) {
    formSalida.addEventListener('submit', async (e) => {
      e.preventDefault();
      btnConfirmar.disabled = true;
      btnConfirmar.textContent = 'Processing...';

      try {
        const respuesta = await fetch(formSalida.action, {
          method: 'POST',
          body: new FormData(formSalida),
        });

        if (respuesta.redirected && respuesta.url.includes('accion=factura')) {
          // Sale confirmed: the invoice is already saved on the server,
          // it opens in a new tab and the cart is cleared so the
          // cashier can help the next customer right away.
          window.open(respuesta.url, '_blank');
          errorBox.hidden = true;
          carrito.clear();
          if (inputCliente) inputCliente.value = '';
          if (clienteHint) clienteHint.hidden = true;
          renderCarrito();
          buscador.value = '';
          listaEl.innerHTML = '';
          buscador.focus();
        } else {
          // There was an error (e.g. not enough stock): the server
          // returned the same page with the message. We extract it
          // and show it here, without losing the current cart.
          const html = await respuesta.text();
          const doc = new DOMParser().parseFromString(html, 'text/html');
          const errorServidor = doc.querySelector('.alert-error p');
          errorTexto.textContent = errorServidor
            ? errorServidor.textContent
            : 'Could not complete the sale. Please try again.';
          errorBox.hidden = false;
          errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
          renderCarrito();
        }
      } catch (err) {
        errorTexto.textContent = 'Could not connect to the server. Please try again.';
        errorBox.hidden = false;
      } finally {
        btnConfirmar.textContent = 'Confirm sale';
        btnConfirmar.disabled = carrito.size === 0;
      }
    });
  }
}
