(() => {
  const STORAGE_KEY = 'smart-ahorro-theme';
  const root = document.documentElement;

  const updateThemeControls = (theme) => {
    const isDark = theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
      const icon = btn.querySelector('[data-theme-icon]');
      const text = btn.querySelector('[data-theme-text]');
      if (icon) {
        icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
      }
      if (text) {
        text.textContent = isDark ? 'Modo claro' : 'Modo oscuro';
      }
      btn.setAttribute('aria-label', isDark ? 'Activar modo claro' : 'Activar modo oscuro');
    });
  };

  const applyTheme = (theme) => {
    root.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);
    updateThemeControls(theme);
    window.dispatchEvent(new CustomEvent('smart-ahorro-theme-changed', { detail: theme }));
  };

  const initialTheme = root.getAttribute('data-theme') || localStorage.getItem(STORAGE_KEY) || 'light';
  updateThemeControls(initialTheme);

  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      applyTheme(nextTheme);
    });
  });

  const parseAmount = (value) => {
    let clean = String(value ?? '').replace(/[^0-9.,]/g, '');
    if (!clean) return '';

    if (clean.includes(',')) {
      clean = clean.replace(/\./g, '').replace(',', '.');
    } else if (clean.includes('.')) {
      const parts = clean.split('.');
      const lastPart = parts[parts.length - 1];
      if (parts.length === 2 && lastPart.length <= 2 && lastPart.length > 0 && parts[0].length <= 3) {
        clean = parts[0] + '.' + lastPart;
      } else if (parts.length > 2 || lastPart.length === 3) {
        clean = parts.join('');
      } else if (lastPart.length <= 2) {
        clean = parts.slice(0, -1).join('') + '.' + lastPart;
      } else {
        clean = parts.join('');
      }
    }

    const numericParts = clean.split('.');
    if (numericParts.length > 2) {
      clean = numericParts[0] + '.' + numericParts.slice(1).join('');
    }
    return clean;
  };

  const formatCOP = (value) => {
    const clean = parseAmount(value);
    if (!clean || clean === '.') return '';
    const [integerPart, decimalPart] = clean.split('.');
    const cleanInt = (integerPart || '0').replace(/^0+(?=\d)/, '');
    const grouped = cleanInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (decimalPart !== undefined && decimalPart.length > 0 && Number(decimalPart) > 0) {
      return grouped + ',' + decimalPart.slice(0, 2);
    }
    return grouped;
  };

  document.querySelectorAll('[data-currency-input]').forEach((input) => {
    input.type = 'text';
    input.inputMode = 'decimal';
    input.autocomplete = 'off';

    if (input.value !== '') {
      input.value = formatCOP(input.value);
    }

    input.addEventListener('input', () => {
      const raw = input.value.replace(/[^0-9.,]/g, '');
      const hasComma = raw.endsWith(',');
      if (hasComma) {
        const base = formatCOP(raw.slice(0, -1));
        input.value = (base || '0') + ',';
        return;
      }
      if (raw.includes(',')) {
        const [intRaw, decRaw] = raw.split(',');
        const formattedInt = formatCOP(intRaw) || '0';
        input.value = formattedInt + ',' + decRaw.replace(/[^0-9]/g, '').slice(0, 2);
        return;
      }
      input.value = formatCOP(raw);
    });

    input.addEventListener('blur', () => {
      input.value = formatCOP(input.value);
    });

    const form = input.closest('form');
    if (form && !form.dataset.currencySubmitBound) {
      form.dataset.currencySubmitBound = 'true';
      form.addEventListener('submit', () => {
        form.querySelectorAll('[data-currency-input]').forEach((field) => {
          field.value = parseAmount(field.value);
        });
      });
    }
  });
})();
