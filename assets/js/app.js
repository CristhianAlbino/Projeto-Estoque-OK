'use strict';
(function () {
  const openButton = document.getElementById('open-cart');
  const closeButton = document.getElementById('close-cart');
  const drawer = document.getElementById('cart-drawer');
  const backdrop = document.getElementById('cart-backdrop');
  if (!openButton || !closeButton || !drawer || !backdrop) return;
  const storageKey = 'autopecas-selecao-v1';
  const formatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
  let cart = [];
  try {
    const restored = JSON.parse(localStorage.getItem(storageKey) || '[]');
    if (Array.isArray(restored)) {
      cart = restored.filter(item => Number.isInteger(item.id) && item.id > 0 &&
        typeof item.name === 'string' && Number.isFinite(item.price) && item.price >= 0 &&
        Number.isInteger(item.qty) && item.qty > 0).slice(0, 100);
    }
  } catch (_) { cart = []; }
  const count = document.getElementById('cart-count');
  const items = document.getElementById('cart-items');
  const total = document.getElementById('cart-total');
  function save() {
    try { localStorage.setItem(storageKey, JSON.stringify(cart)); } catch (_) { /* Storage unavailable */ }
  }
  function render() {
    count.textContent = String(cart.reduce((sum, item) => sum + item.qty, 0));
    items.replaceChildren();
    if (!cart.length) {
      const empty = document.createElement('p');
      empty.className = 'drawer-helper';
      empty.textContent = 'Sua seleção ainda está vazia.';
      items.appendChild(empty);
    }
    let sum = 0;
    cart.forEach(item => {
      sum += item.price * item.qty;
      const line = document.createElement('div');
      line.className = 'cart-line';
      const info = document.createElement('div');
      const title = document.createElement('strong');
      title.textContent = item.name;
      const subtitle = document.createElement('small');
      subtitle.textContent = String(item.qty) + ' × ' + formatter.format(item.price);
      info.append(title, subtitle);
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.setAttribute('aria-label', 'Remover ' + item.name);
      remove.textContent = 'Remover';
      remove.addEventListener('click', () => {
        cart = cart.filter(saved => saved.id !== item.id);
        save();
        render();
      });
      line.append(info, remove);
      items.appendChild(line);
    });
    total.textContent = formatter.format(sum);
  }
  function toggle(open) {
    drawer.classList.toggle('open', open);
    drawer.setAttribute('aria-hidden', String(!open));
    backdrop.hidden = !open;
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) closeButton.focus();
    else openButton.focus();
  }
  openButton.addEventListener('click', () => toggle(true));
  closeButton.addEventListener('click', () => toggle(false));
  backdrop.addEventListener('click', () => toggle(false));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && drawer.classList.contains('open')) toggle(false);
  });
  document.querySelectorAll('.add-to-selection').forEach(button => {
    button.addEventListener('click', () => {
      const id = Number(button.dataset.id);
      const price = Number(button.dataset.price);
      const stock = Number(button.dataset.stock);
      const name = button.dataset.name || '';
      if (!Number.isInteger(id) || id < 1 || !Number.isFinite(price) ||
          price < 0 || !Number.isInteger(stock) || stock < 1 || !name) return;
      const existing = cart.find(item => item.id === id);
      if (existing) {
        if (existing.qty >= stock) {
          window.alert('Você já selecionou toda a quantidade disponível.');
          return;
        }
        existing.qty += 1;
      } else {
        cart.push({ id, name, price, qty: 1 });
      }
      save();
      render();
      toggle(true);
    });
  });
  document.getElementById('clear-cart').addEventListener('click', () => {
    cart = [];
    save();
    render();
  });
  render();
})();
