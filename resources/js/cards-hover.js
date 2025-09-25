document.addEventListener('DOMContentLoaded', () => {
  const cards = document.querySelectorAll('.card-abbr');

  cards.forEach(card => {
    const abbrText = card.getAttribute('data-abbr');
    const fullText = card.getAttribute('data-full');
    const abbrEl = card.querySelector('.abbr-text');

    card.addEventListener('mouseenter', () => {
      if (abbrEl && fullText) abbrEl.textContent = fullText;
    });

    card.addEventListener('mouseleave', () => {
      if (abbrEl && abbrText) abbrEl.textContent = abbrText;
    });
  });
});
