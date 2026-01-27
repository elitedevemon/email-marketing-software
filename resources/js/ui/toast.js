function ensureRoot() {
  let root = document.getElementById('toastRoot');
  if (!root) {
    root = document.createElement('div');
    root.id = 'toastRoot';
    root.className = 'fixed top-4 right-4 z-[100] flex flex-col gap-2 w-[340px] max-w-[calc(100vw-2rem)]';
    document.body.appendChild(root);
  }
  return root;
}

export function toast(message, variant = 'info') {
  const root = ensureRoot();
  const el = document.createElement('div');

  const variants = {
    info: 'border-border bg-card text-card-fg',
    success: 'border-border bg-card text-card-fg',
    danger: 'border-danger/40 bg-card text-card-fg',
  };

  el.className =
    `rounded-2xl border shadow-lg px-4 py-3 text-sm ${variants[variant] || variants.info}` +
    ' opacity-0 translate-y-1 transition';
  el.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="min-w-0 flex-1">
                <div class="font-semibold">${variant === 'danger' ? 'Error' : 'Notice'}</div>
                <div class="text-muted-fg mt-0.5">${message}</div>
            </div>
            <button type="button" class="h-8 w-8 rounded-xl hover:bg-muted/40 transition grid place-items-center" aria-label="Close">✕</button>
        </div>
    `;

  root.appendChild(el);
  requestAnimationFrame(() => {
    el.classList.remove('opacity-0', 'translate-y-1');
  });

  const close = () => {
    el.classList.add('opacity-0', 'translate-y-1');
    setTimeout(() => el.remove(), 180);
  };

  el.querySelector('button')?.addEventListener('click', close);
  setTimeout(close, 4200);
}