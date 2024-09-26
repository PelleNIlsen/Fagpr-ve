function sortCategories() {

  // Hente alle filtrerings-knappene
  const catBtns = document.querySelectorAll('.kat-filter a');

  if (catBtns.length === 0) {
    return;
  }

  // Hente alle blogginnleggene
  const posts = document.querySelectorAll('.post-item');

  function selectCategory(cat) {
    // Hvis knappen som ble trykket på er "Se alle"
    if (cat.toLowerCase() === 'se alle' || cat.toLowerCase() === 'show all') {
      // Vis alle innleggene
      posts.forEach(post => post.style.display = 'block');
      return;
    }
    
    posts.forEach(post => {
      // For hvert innlegg
      const catLabel = post.querySelector('.cat-label');
      // Hvis det ikke har en kategori, hopp over til neste
      if (!catLabel) {
        return;
      }

      // Henter kategori navnet
      const postCategories = catLabel.textContent.toLowerCase().split(',').map(c => c.trim());
      const catLower = cat.toLowerCase().trim();

      // Hvis kategori navnet er lik knappen
      const matches = postCategories.some(postCat => postCat.includes(catLower));

      // Hvis er lik, vis, hvis ikke, skjul
      post.style.display = matches ? 'block' : 'none';
    });
  }

  catBtns.forEach((catBtn) => {
    catBtn.addEventListener('click', (e) => {
      // Kjør funksjon for hvert trykk
      e.preventDefault();
      
      if (e.currentTarget.classList.contains('kat-filter_show_all')) {
        // Hvis bruker klikker "Se alle"
        selectCategory('Se alle');
        return;
      }

      // Sjekker om knappen i det hele tatt har en kat-filter_ class
      const catClass = Array.from(e.currentTarget.classList).find(c => c.startsWith('kat-filter_'));
      if (!catClass) {
        return;
      }

      // Gjør om f.eks. "kat-filter_nyheter_og_prosjekter" til "nyheter og prosjekter"
      const catName = catClass.replace('kat-filter_', '').replace(/_/g, ' ');
      selectCategory(catName);
    });
  });
}
 
document.addEventListener('DOMContentLoaded', () => {
  // Kjør funksjonen etter alt er lastet inn
  sortCategories();
});
