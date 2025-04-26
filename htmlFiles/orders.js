// htmlFiles/orders.js

// List of available menu items
const menuItems = [
  "Bruschetta","Garlic Bread","Stuffed Mushrooms","Arancini",
  "Caprese Salad","Caesar Salad","Italian House Salad","Margherita",
  "Pepperoni","Quattro Formaggi","Prosciutto e Rucola","Vegetariana",
  "Diavola","Spaghetti Bolognese","Penne Arrabbiata","Fettuccine Alfredo",
  "Lasagna al Forno","Gnocchi Pesto","Chicken Parmigiana","Veal Milanese",
  "Grilled Salmon","Eggplant Parmigiana","French Fries","Grilled Vegetables",
  "Side Salad","Tiramisu","Panna Cotta","Cannoli","Gelato","Espresso",
  "Cappuccino","Limonata","House Red Wine","House White Wine","Mineral Water"
];

// DOM refs
const searchInput      = document.getElementById("orderSearch");
const suggestionsDiv   = document.getElementById("suggestions");
const orderList        = document.getElementById("orderList");
const hiddenOrderInput = document.getElementById("order");

// Track counts
let orderCounts = {};

// Suggest only items starting with the typed letters (case-insensitive)
searchInput.addEventListener("input", () => {
  const query = searchInput.value.trim().toLowerCase();
  if (!query) {
    suggestionsDiv.style.display = "none";
    return;
  }
  const matches = menuItems.filter(item =>
    item.toLowerCase().startsWith(query)
  );
  if (matches.length) {
    suggestionsDiv.innerHTML = "<ul>" +
      matches.map(item => `<li>${item}</li>`).join("") +
      "</ul>";
    suggestionsDiv.style.display = "block";
    suggestionsDiv.querySelectorAll("li").forEach(li => {
      li.addEventListener("click", () => {
        addItem(li.textContent);
        suggestionsDiv.style.display = "none";
        searchInput.value = "";
      });
    });
  } else {
    suggestionsDiv.style.display = "none";
  }
});

// Add or increment item
function addItem(item) {
  if (orderCounts[item]) {
    orderCounts[item]++;
    const existingLi = Array.from(orderList.children)
      .find(li => li.dataset.item === item);
    existingLi.querySelector(".count").textContent = orderCounts[item];
  } else {
    orderCounts[item] = 1;
    const li = document.createElement("li");
    li.dataset.item = item;
    li.innerHTML =
      `<span class="count">1</span> x ` +
      `<span class="item-name">${item}</span> ` +
      `<span class="remove" title="Remove or decrement">×</span>`;
    orderList.appendChild(li);
    li.querySelector(".remove").addEventListener("click", () => {
      removeItem(item);
    });
  }
  updateHiddenInput();
}

// Decrement or remove item
function removeItem(item) {
  if (orderCounts[item] > 1) {
    orderCounts[item]--;
    const existingLi = Array.from(orderList.children)
      .find(li => li.dataset.item === item);
    existingLi.querySelector(".count").textContent = orderCounts[item];
  } else {
    delete orderCounts[item];
    const existingLi = Array.from(orderList.children)
      .find(li => li.dataset.item === item);
    existingLi.remove();
  }
  updateHiddenInput();
}

// Update hidden <input name="order">
function updateHiddenInput() {
  const parts = Object.entries(orderCounts).map(
    ([item, count]) => `${count} x ${item}`
  );
  hiddenOrderInput.value = parts.join(", ");
}
