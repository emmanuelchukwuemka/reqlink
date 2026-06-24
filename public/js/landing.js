// ===== ResQLink Landing Page JS =====
document.addEventListener('DOMContentLoaded', () => {

  // Nav scroll effect
  const nav = document.querySelector('.nav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 50);
  });

  // Mobile menu
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  if (hamburger) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      hamburger.classList.toggle('active');
    });

    // Mobile dropdown toggle — handle all dropdown triggers
    navLinks.querySelectorAll('.nav-dropdown-trigger').forEach(trigger => {
      trigger.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
          e.preventDefault();
          const thisDropdown = trigger.closest('.nav-dropdown');
          // Close other open dropdowns
          navLinks.querySelectorAll('.nav-dropdown').forEach(d => {
            if (d !== thisDropdown) d.classList.remove('open');
          });
          thisDropdown.classList.toggle('open');
        }
      });
    });

    // Close menu on link click (except dropdown triggers on mobile)
    navLinks.querySelectorAll('a').forEach(a => {
      if (a.classList.contains('nav-dropdown-trigger')) return;
      a.addEventListener('click', () => {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
        navLinks.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('open'));
      });
    });
  }

  // Scroll animations
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

  // Funding bars animation
  const fundObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.querySelectorAll('.fund-bar-fill').forEach(bar => {
          bar.style.width = bar.dataset.width;
        });
      }
    });
  }, { threshold: 0.3 });

  const fundingSection = document.querySelector('.funding-bars');
  if (fundingSection) fundObserver.observe(fundingSection);

  // Counter animation
  const countObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const counters = entry.target.querySelectorAll('[data-count]');
        counters.forEach(counter => {
          const target = parseInt(counter.dataset.count);
          const suffix = counter.dataset.suffix || '';
          const prefix = counter.dataset.prefix || '';
          let current = 0;
          const step = Math.ceil(target / 60);
          const timer = setInterval(() => {
            current += step;
            if (current >= target) {
              current = target;
              clearInterval(timer);
            }
            counter.textContent = prefix + current.toLocaleString() + suffix;
          }, 25);
        });
        countObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  const statsSection = document.querySelector('.hero-stats');
  if (statsSection) countObserver.observe(statsSection);

  // Smooth scroll for nav links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) target.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // Features Carousel
  (function() {
    const track   = document.getElementById('featTrack');
    if (!track) return;
    const outer   = track.parentElement;           // .feat-carousel-track-outer
    const prevBtn = document.getElementById('featPrev');
    const nextBtn = document.getElementById('featNext');
    const dotsWrap= document.getElementById('featDots');
    const cards   = Array.from(track.querySelectorAll('.feat-card'));
    const TOTAL   = cards.length;
    const GAP     = 24;
    let idx = 0, timer;

    const visible = () => window.innerWidth <= 768 ? 1 : window.innerWidth <= 1024 ? 2 : 3;
    const maxIdx  = () => TOTAL - visible();

    // Set every card to the same width based on outer container
    const sizeCards = () => {
      const w = Math.floor((outer.offsetWidth - (visible()-1)*GAP) / visible());
      cards.forEach(c => { c.style.width = w + 'px'; c.style.minWidth = w + 'px'; });
      return w;
    };

    // Slide to position idx with CSS transform
    const slide = (to) => {
      idx = Math.max(0, Math.min(to, maxIdx()));
      const w = sizeCards();
      track.style.transform = 'translateX(-' + idx * (w + GAP) + 'px)';
      if (prevBtn) prevBtn.disabled = idx === 0;
      if (nextBtn) nextBtn.disabled = idx >= maxIdx();
      // dots
      if (dotsWrap) dotsWrap.querySelectorAll('.carousel-dot')
        .forEach((d,i) => d.classList.toggle('active', i === idx));
    };

    // Build dot buttons
    const buildDots = () => {
      if (!dotsWrap) return;
      dotsWrap.innerHTML = '';
      for (let i = 0; i <= maxIdx(); i++) {
        const d = document.createElement('button');
        d.className = 'carousel-dot' + (i === idx ? ' active' : '');
        d.onclick = () => { slide(i); kick(); };
        dotsWrap.appendChild(d);
      }
    };

    const kick = () => {
      clearInterval(timer);
      timer = setInterval(() => slide(idx >= maxIdx() ? 0 : idx + 1), 4000);
    };

    if (prevBtn) prevBtn.onclick = () => { slide(idx - 1); kick(); };
    if (nextBtn) nextBtn.onclick = () => { slide(idx + 1); kick(); };
    window.addEventListener('resize', () => { buildDots(); slide(idx); });

    // Init — retry until the outer element has a real width
    const init = () => {
      if (outer.offsetWidth === 0) { setTimeout(init, 100); return; }
      buildDots();
      slide(0);
      kick();
    };
    init();
  })();

  // Contact form
  const form = document.getElementById('contact-form');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      btn.textContent = 'Sending...';
      btn.disabled = true;
      setTimeout(() => {
        btn.textContent = '✓ Message Sent!';
        btn.style.background = '#22c55e';
        form.reset();
        setTimeout(() => {
          btn.textContent = 'Send Message';
          btn.style.background = '';
          btn.disabled = false;
        }, 3000);
      }, 1500);
    });
  }
  // Typing Animation
  const typingElement = document.getElementById('typing-text');
  if (typingElement) {
    const phrases = [
      "Saving Lives Through Instant Connection",
      "Every Second Counts in an Emergency",
      "Bridging the Gap Between You and Help"
    ];
    let phraseIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typeSpeed = 100;

    function type() {
      const currentPhrase = phrases[phraseIndex];
      
      if (isDeleting) {
        typingElement.textContent = currentPhrase.substring(0, charIndex - 1);
        charIndex--;
        typeSpeed = 50;
      } else {
        typingElement.textContent = currentPhrase.substring(0, charIndex + 1);
        charIndex++;
        typeSpeed = 100;
      }

      if (!isDeleting && charIndex === currentPhrase.length) {
        isDeleting = true;
        typeSpeed = 2000; // Pause at end
      } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        phraseIndex = (phraseIndex + 1) % phrases.length;
        typeSpeed = 500; // Pause before next
      }

      setTimeout(type, typeSpeed);
    }

    type();
  }
});
