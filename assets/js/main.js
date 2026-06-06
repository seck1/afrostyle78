/* AfroStyle — Main JS */

// Couple reveal animation: drawing → photo
document.addEventListener('DOMContentLoaded', () => {
  const drawing = document.getElementById('coupleDrawing');
  const photo   = document.getElementById('couplePhoto');
  if (!drawing || !photo) return;

  // Après 5.5s : fade out dessin, fade in photo
  setTimeout(() => {
    drawing.classList.add('hidden');
    photo.classList.add('visible');

    // Après 4s supplémentaires : remettre le dessin, cacher la photo (boucle)
    setTimeout(() => {
      drawing.classList.remove('hidden');
      photo.classList.remove('visible');

      // Relancer les animations SVG en retirant/remettant les classes
      drawing.querySelectorAll('[class*="cd"], [class*="cdB"]').forEach(el => {
        el.style.animation = 'none';
        el.offsetHeight; // reflow
        el.style.animation = '';
      });

      // Reboucler
      setTimeout(() => {
        drawing.classList.add('hidden');
        photo.classList.add('visible');
      }, 5500);
    }, 4000);
  }, 5500);
});

// Navbar scroll effect
const navbar = document.getElementById('navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  });
}

// Mobile menu
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  if (menu) menu.classList.toggle('open');
  document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
}

function toggleMobileSubmenu(el) {
  const submenu = el.nextElementSibling;
  const icon    = el.querySelector('.mobile-submenu-icon');
  if (submenu) submenu.classList.toggle('open');
  if (icon)    icon.classList.toggle('open');
}

// Search overlay
function toggleSearch() {
  const overlay = document.getElementById('searchOverlay');
  if (overlay) {
    overlay.classList.toggle('open');
    document.body.style.overflow = overlay.classList.contains('open') ? 'hidden' : '';
    if (overlay.classList.contains('open')) {
      setTimeout(() => overlay.querySelector('input')?.focus(), 100);
    }
  }
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.getElementById('searchOverlay')?.classList.remove('open');
    document.getElementById('mobileMenu')?.classList.remove('open');
    document.body.style.overflow = '';
  }
});

// Product size selection
document.querySelectorAll('.size-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    if (this.classList.contains('out-of-stock')) return;
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
    this.classList.add('selected');
    const sizeInput = document.getElementById('selected_size');
    if (sizeInput) sizeInput.value = this.dataset.size;
  });
});

// Custom measure toggle
const measureToggle = document.getElementById('measureToggle');
const measureForm = document.getElementById('measureForm');
if (measureToggle && measureForm) {
  measureToggle.addEventListener('click', function() {
    const isOpen = measureForm.classList.toggle('open');
    this.classList.toggle('active', isOpen);
    const checkbox = document.getElementById('is_custom_measure');
    if (checkbox) checkbox.checked = isOpen;
    if (isOpen) {
      document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
      const sizeInput = document.getElementById('selected_size');
      if (sizeInput) sizeInput.value = 'SUR-MESURE';
    }
  });
}

// Quantity selector
function changeQty(delta) {
  const input = document.querySelector('.qty-input');
  if (!input) return;
  let val = parseInt(input.value) || 1;
  val = Math.max(1, Math.min(99, val + delta));
  input.value = val;
}

// Gallery thumbnails
document.querySelectorAll('.gallery-thumb').forEach(thumb => {
  thumb.addEventListener('click', function() {
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    const mainImg = document.querySelector('.gallery-main img');
    if (mainImg && this.dataset.src) mainImg.src = this.dataset.src;
  });
});

// Animate on scroll
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.animationPlayState = 'running';
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('[data-animate]').forEach(el => {
  el.style.animationPlayState = 'paused';
  observer.observe(el);
});

// Cart quantity update
document.querySelectorAll('.cart-qty-input').forEach(input => {
  input.addEventListener('change', function() {
    const form = this.closest('form');
    if (form) form.submit();
  });
});

// Flash messages auto-hide
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(el => {
    el.style.transition = 'opacity 0.5s ease';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  });
}, 4000);

// Product image lazy load placeholder
document.querySelectorAll('.product-image-wrap img').forEach(img => {
  img.addEventListener('error', function() {
    this.style.display = 'none';
    const placeholder = this.parentElement.querySelector('.product-placeholder');
    if (!placeholder) {
      const ph = document.createElement('div');
      ph.className = 'product-placeholder';
      ph.textContent = '👗';
      this.parentElement.appendChild(ph);
    }
  });
});
