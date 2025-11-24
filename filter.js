var products = document.querySelectorAll("a[name=product]");
var checkboxes = document.querySelectorAll("input[type=checkbox][name=filter]");
 products.forEach(function(product) {
      product.style.display = "block";
    });
let enabledSettings = [];


checkboxes.forEach(function(checkbox) {
  checkbox.addEventListener('change', function() {

    enabledSettings = Array.from(checkboxes)
      .filter(i => i.checked)
      .map(i => i.value);
    

    products.forEach(function(product) {
      const productCategory = product.getAttribute("value");
      if (enabledSettings.length === 0 || enabledSettings.includes(productCategory)) {
        product.style.display = "block";
      } else {
        product.style.display = "none";

      }
    });
  });
});

function cart() {
  const cart = document.getElementById('cart');
  if (!cart) return;


  const isVisible = cart.style.display !== 'none';
  if (isVisible) {
    cart.style.display = 'none';
    return;
  }
  cart.style.display = 'block';



}

