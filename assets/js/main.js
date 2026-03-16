// =============================================
// Al Bustan Suites - Main JavaScript
// =============================================

document.addEventListener('DOMContentLoaded', function() {

  // ---- Page Loader ----
  const loader = document.getElementById('pageLoader');
  if (loader) {
    window.addEventListener('load', () => {
      setTimeout(() => loader.classList.add('hidden'), 600);
    });
    // Fallback
    setTimeout(() => loader.classList.add('hidden'), 3000);
  }

  // ---- Sticky Nav scroll effect ----
  const nav = document.getElementById('mainNav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 80);
    });
  }

  // ---- Mobile Nav Toggle ----
  const toggle = document.getElementById('navToggle');
  const menu   = document.getElementById('navMenu');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      toggle.classList.toggle('open');
      menu.classList.toggle('open');
    });
    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!toggle.contains(e.target) && !menu.contains(e.target)) {
        toggle.classList.remove('open');
        menu.classList.remove('open');
      }
    });
    // Mobile dropdown toggle
    const drops = menu.querySelectorAll('.has-dropdown > a');
    drops.forEach(a => {
      a.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
          e.preventDefault();
          a.parentElement.classList.toggle('open');
        }
      });
    });
  }

  // ---- Hero Slider ----
  const slides = document.querySelectorAll('.hero-slide');
  if (slides.length > 1) {
    let current = 0;
    const indicators = document.querySelectorAll('.hero-indicator');
    function showSlide(idx) {
      slides.forEach((s, i) => s.classList.toggle('active', i === idx));
      indicators.forEach((d, i) => d.classList.toggle('active', i === idx));
      current = idx;
    }
    setInterval(() => showSlide((current + 1) % slides.length), 6000);
    indicators.forEach((d, i) => d.addEventListener('click', () => showSlide(i)));
  } else if (slides.length === 1) {
    slides[0].classList.add('active');
  }

  // ---- Back to Top ----
  const btn = document.getElementById('backToTop');
  if (btn) {
    window.addEventListener('scroll', () => btn.classList.toggle('visible', window.scrollY > 500));
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // ---- Animate on Scroll ----
  const animEls = document.querySelectorAll('.animate-on-scroll');
  if (animEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    animEls.forEach(el => io.observe(el));
  }

  // ---- Menu Tabs (restaurant menu) ----
  const tabs = document.querySelectorAll('.menu-tab');
  const panels = document.querySelectorAll('.menu-panel');
  if (tabs.length) {
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        panels.forEach(p => p.style.display = 'none');
        tab.classList.add('active');
        const target = document.querySelector('.menu-panel[data-cat="' + tab.dataset.cat + '"]');
        if (target) target.style.display = 'grid';
      });
    });
    // Show first tab
    if (tabs[0]) tabs[0].click();
  }

  // ---- Gallery Lightbox ----
  const galleryItems = document.querySelectorAll('.gallery-item');
  const lightbox = document.getElementById('lightbox');
  const lbImg = document.getElementById('lightboxImg');
  if (galleryItems.length && lightbox && lbImg) {
    let allImgs = Array.from(galleryItems).map(g => g.querySelector('img')?.src).filter(Boolean);
    let lbIdx = 0;
    function openLb(idx) {
      lbIdx = idx;
      lbImg.src = allImgs[lbIdx];
      lightbox.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeLb() {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
    }
    galleryItems.forEach((item, i) => item.addEventListener('click', () => openLb(i)));
    document.getElementById('lightboxClose')?.addEventListener('click', closeLb);
    document.getElementById('lightboxPrev')?.addEventListener('click', () => openLb((lbIdx - 1 + allImgs.length) % allImgs.length));
    document.getElementById('lightboxNext')?.addEventListener('click', () => openLb((lbIdx + 1) % allImgs.length));
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLb(); });
    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('open')) return;
      if (e.key === 'Escape') closeLb();
      if (e.key === 'ArrowLeft') openLb((lbIdx - 1 + allImgs.length) % allImgs.length);
      if (e.key === 'ArrowRight') openLb((lbIdx + 1) % allImgs.length);
    });
  }

  // ---- Star Rating ----
  const stars = document.querySelectorAll('.star-input');
  stars.forEach((star, i) => {
    star.addEventListener('click', () => {
      stars.forEach((s, j) => s.classList.toggle('active', j <= i));
      const input = document.querySelector('input[name="rating"]');
      if (input) input.value = i + 1;
    });
  });

  // ---- Flash message auto-hide ----
  const flashEls = document.querySelectorAll('.flash');
  flashEls.forEach(f => {
    setTimeout(() => {
      f.style.opacity = '0';
      setTimeout(() => f.remove(), 500);
    }, 5000);
  });

  // ---- Number counter animations ----
  const counters = document.querySelectorAll('.counter-num');
  if (counters.length) {
    const ioC = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const target = parseInt(el.textContent.replace(/\D/g,''));
          let current = 0;
          const duration = 2000;
          const step = target / (duration / 16);
          const timer = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = Math.floor(current) + (el.dataset.suffix || '');
          }, 16);
          ioC.unobserve(el);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(c => ioC.observe(c));
  }

  // ---- Smooth scroll for anchor links ----
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

});

// ---- Booking Availability Check ----
async function checkAvailabilityAjax(roomId, checkIn, checkOut) {
  if (!checkIn || !checkOut) return;
  const res = await fetch(`/albustansuites/api/check-availability.php?room_id=${roomId}&check_in=${checkIn}&check_out=${checkOut}`);
  const data = await res.json();
  const el = document.getElementById('availabilityResult');
  if (el) {
    el.style.display = 'block';
    if (data.available) {
      el.className = 'availability-box available';
      el.innerHTML = '<i class="fas fa-check-circle"></i> ' + (document.documentElement.lang === 'ar' ? 'الغرفة متاحة في التواريخ المحددة' : 'Room is available for selected dates');
    } else {
      el.className = 'availability-box unavailable';
      el.innerHTML = '<i class="fas fa-times-circle"></i> ' + (document.documentElement.lang === 'ar' ? 'الغرفة غير متاحة في هذه التواريخ' : 'Room is not available for these dates');
    }
  }
}
