let isShown = false;
function extraFilter() {
  const hiddenFilters = document.querySelectorAll('#extrafilter');
  const btn = document.getElementById('extraFilterBtn');

  if (isShown) {

    hiddenFilters.forEach(filter => {
      filter.style.display = "none";
    });
    isShown = false;
    btn.innerHTML = "See more";
  } else {
    hiddenFilters.forEach(filter => {
      filter.style.display = "block";
    });
    isShown = true; 
    btn.innerHTML = "See less";
  }
}

