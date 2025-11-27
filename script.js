 document.addEventListener('DOMContentLoaded', () => {

  // Project filter
  (function projectFilter() {
    const buttons = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.project-card');
    if (!buttons.length || !cards.length) return;

    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = (btn.dataset.filter || 'all').toLowerCase();
        cards.forEach(card => {
          const tags = (card.dataset.tags || '').toLowerCase().split(/\s+/);
          card.style.display = filter === 'all' || tags.includes(filter) ? '' : 'none';
        });
      });
    });
  })();

  // Contact form
  (function contactForm() {
    const form = document.getElementById('contact-form');
    if (!form) return;
    const nameEl = document.getElementById('name');
    const emailEl = document.getElementById('email');
    const messageEl = document.getElementById('message');
    const statusEl = document.getElementById('form-status');
    const DRAFT_KEY = 'tsion_contact_draft_v1';

    // Load draft
    try {
      const raw = localStorage.getItem(DRAFT_KEY);
      if (raw) {
        const draft = JSON.parse(raw);
        if (draft.name) nameEl.value = draft.name;
        if (draft._replyto) emailEl.value = draft._replyto;
        if (draft.message) messageEl.value = draft.message;
      }
    } catch (e) { console.warn('Draft load failed', e); }

    // Save draft
    let t;
    const saveDraft = () => {
      clearTimeout(t);
      t = setTimeout(() => {
        localStorage.setItem(DRAFT_KEY, JSON.stringify({ name: nameEl.value, _replyto: emailEl.value, message: messageEl.value }));
      }, 250);
    };
    [nameEl, emailEl, messageEl].forEach(i => i && i.addEventListener('input', saveDraft));

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      statusEl.textContent = 'Sending…';
      const data = new FormData(form);
      try {
        const response = await fetch(form.action, { method: form.method.toUpperCase(), body: data, headers: { 'Accept': 'application/json' }});
        if (response.ok) {
          statusEl.textContent = 'Message sent — thank you! 🎉';
          form.reset();
          localStorage.removeItem(DRAFT_KEY);
        } else {
          statusEl.textContent = 'Failed to send. Try again later.';
        }
      } catch (err) {
        statusEl.textContent = 'Network error — try again later.';
      }
    });
  })();

  // Nav highlight on scroll
  (function activeNavOnScroll() {
    const navLinks = Array.from(document.querySelectorAll('.site-nav a'));
    if (!navLinks.length) return;
    const sections = navLinks.map(a => a.getAttribute('href').startsWith('#') ? document.querySelector(a.getAttribute('href')) : null);

    const onScroll = () => {
      const scrollPos = window.scrollY + 140;
      let found = -1;
      sections.forEach((sec, idx) => { if(sec && scrollPos >= sec.offsetTop && scrollPos < sec.offsetTop + sec.offsetHeight) found = idx; });
      navLinks.forEach((link, idx) => link.classList.toggle('active', idx === found));
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    setTimeout(onScroll, 50);
  })();

  // Mobile hamburger toggle
  (function mobileNav() {
    const hamburger = document.querySelector('.hamburger');
    const nav = document.querySelector('.site-nav');
    if (!hamburger || !nav) return;

    hamburger.addEventListener('click', () => { nav.classList.toggle('nav-open'); hamburger.classList.toggle('open'); });
    nav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => { nav.classList.remove('nav-open'); hamburger.classList.remove('open'); }));
  })();

  // Dark mode toggle
  (function darkModeToggle() {
    const toggleBtn = document.getElementById('dark-mode-toggle');
    if (!toggleBtn) return;

    if (localStorage.getItem('darkMode') === 'enabled') {
      document.body.classList.add('dark-mode');
      toggleBtn.textContent = '☀️';
    }

    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
        toggleBtn.textContent = '☀️';
      } else {
        localStorage.setItem('darkMode', 'disabled');
        toggleBtn.textContent = '🌙';
      }
    });
  })();

  // Animate skills bars on scroll
  (function animateSkills() {
    const skills = document.querySelectorAll('.fill');
    if (!skills.length) return;

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const fill = entry.target;
          const width = fill.getAttribute('style').match(/width:\s*(\d+)%/i);
          if (width) {
            fill.style.width = width[1] + '%';
          }
          observer.unobserve(fill);
        }
      });
    }, { threshold: 0.5 });

    skills.forEach(skill => {
      skill.style.width = '0';
      observer.observe(skill);
    });
  })();

});
