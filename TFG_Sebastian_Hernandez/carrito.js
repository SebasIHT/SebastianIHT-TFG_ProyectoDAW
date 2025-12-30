const productosSimulados = [
    { id: 1, nombre: "Vestido Floreado", precio: 29.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Vestido+Floreado" },
    { id: 2, nombre: "Camisa Blanca", precio: 24.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Camisa+Blanca" },
    { id: 3, nombre: "Pantalón Chino", precio: 34.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Pantal%C3%B3n+Chino" },
    { id: 4, nombre: "Bolso de Mano", precio: 19.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Bolso+de+Mano" },
    { id: 5, nombre: "Zapatillas Deportivas", precio: 49.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Zapatillas" },
    { id: 6, nombre: "Gafas de Sol", precio: 15.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Gafas+de+Sol" },
    { id: 7, nombre: "Vestido Negro Elegante", precio: 39.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Vestido+Negro" },
    { id: 8, nombre: "Sudadera con Capucha", precio: 28.99, imagen: "https://via.placeholder.com/250x300/f5f5f5/333333?text=Sudadera" }
];

let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

function renderCarrito() {
    const contenedor = document.getElementById('cart-container');
    const emptyMsg = document.getElementById('empty-cart');

    if (carrito.length === 0) {
        contenedor.innerHTML = '';
        emptyMsg.style.display = 'block';
        return;
    }

    emptyMsg.style.display = 'none';

    let total = 0;
    const html = carrito.map(item => {
        const prod = productosSimulados.find(p => p.id === item.id);
        if (!prod) return '';

        const subtotal = prod.precio * item.cantidad;
        total += subtotal;

        return `
            <div class="cart-item" data-id="${item.id}" data-talla="${item.talla}">
                <img src="${prod.imagen}" alt="${prod.nombre}">
                <div class="item-info">
                    <h3>${prod.nombre} <small>(Talla: ${item.talla})</small></h3>
                    <p class="price">€${prod.precio.toFixed(2)}</p>
                    <div class="quantity-controls">
                        <button class="decrease">-</button>
                        <span>${item.cantidad}</span>
                        <button class="increase">+</button>
                    </div>
                    <button class="remove-btn">Eliminar</button>
                </div>
            </div>
        `;
    }).join('');

    contenedor.innerHTML = html + 
        `<div class="cart-total">Total: €${total.toFixed(2)}</div>` +
        `<button id="btn-comprar" class="checkout-btn">Finalizar Compra</button>`;

    document.querySelectorAll('.increase').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = parseInt(btn.closest('.cart-item').dataset.id);
            const talla = btn.closest('.cart-item').dataset.talla;
            updateCantidad(id, talla, 1);
        });
    });

    document.querySelectorAll('.decrease').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = parseInt(btn.closest('.cart-item').dataset.id);
            const talla = btn.closest('.cart-item').dataset.talla;
            updateCantidad(id, talla, -1);
        });
    });

    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = parseInt(btn.closest('.cart-item').dataset.id);
            const talla = btn.closest('.cart-item').dataset.talla;
            carrito = carrito.filter(i => !(i.id === id && i.talla === talla));
            guardarYRenderizar();
        });
    });

    document.getElementById('btn-comprar')?.addEventListener('click', async () => {
        if (carrito.length === 0) {
            alert('Tu carrito está vacío.');
            return;
        }

        try {
            const res = await fetch('procesar_compra.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ carrito })
            });

            const data = await res.json();

            if (data.success) {
                alert(`${data.message}\nID de pedido: ${data.id_compra}`);
                carrito = [];
                guardarYRenderizar();
            } else {
                alert('' + (data.message || 'No se pudo procesar la compra.'));
            }
        } catch (error) {
            console.error('Error de red:', error);
            alert('Error de conexión. Asegúrate de estar en http://localhost/...');
        }
    });
}

function updateCantidad(id, talla, cambio) {
    const item = carrito.find(i => i.id === id && i.talla === talla);
    if (item) {
        item.cantidad += cambio;
        if (item.cantidad <= 0) {
            carrito = carrito.filter(i => !(i.id === id && i.talla === talla));
        }
        guardarYRenderizar();
    }
}

function guardarYRenderizar() {
    localStorage.setItem('carrito', JSON.stringify(carrito));
    renderCarrito();
}

document.addEventListener('DOMContentLoaded', renderCarrito);