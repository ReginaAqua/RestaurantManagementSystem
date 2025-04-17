// htmlFiles/orders.js

// List of available menu items
const menuItems = [
    "Bruschetta",
    "Garlic Bread",
    "Stuffed Mushrooms",
    "Arancini",
    "Caprese Salad",
    "Caesar Salad",
    "Italian House Salad",
    "Margherita",
    "Pepperoni",
    "Quattro Formaggi",
    "Prosciutto e Rucola",
    "Vegetariana",
    "Diavola",
    "Spaghetti Bolognese",
    "Penne Arrabbiata",
    "Fettuccine Alfredo",
    "Lasagna al Forno",
    "Gnocchi Pesto",
    "Chicken Parmigiana",
    "Veal Milanese",
    "Grilled Salmon",
    "Eggplant Parmigiana",
    "French Fries",
    "Grilled Vegetables",
    "Side Salad",
    "Tiramisu",
    "Panna Cotta",
    "Cannoli",
    "Gelato",
    "Espresso",
    "Cappuccino",
    "Limonata",
    "House Red Wine",
    "House White Wine",
    "Mineral Water"
  ];
  
  // DOM references
  const searchInput      = document.getElementById("orderSearch");
  const suggestionsDiv   = document.getElementById("suggestions");
  const orderList        = document.getElementById("orderList");
  const hiddenOrderInput = document.getElementById("order");
  
  // Object to track counts
  let orderCounts = {};
  
  // Show matching suggestions as you type
  searchInput.addEventListener("input", () => {
    const rawQuery = searchInput.value.trim();
    const query    = rawQuery.toLowerCase();
    if (!query) {
      suggestionsDiv.style.display = "none";
      return;
    }
  
    // Match items that start with the query, case‑insensitive
    const matches = menuItems.filter(item =>
      item.toLowerCase().startsWith(query)
    );
  
    if (matches.length) {
      suggestionsDiv.innerHTML = "<ul>" +
        matches.map(item => `<li>${item}</li>`).join("") +
        "</ul>";
      suggestionsDiv.style.display = "block";
  
      // Attach click handlers
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
  
  // Add an item (or increment its count)
  function addItem(item) {
    if (orderCounts[item]) {
      // Already in list → increment
      orderCounts[item]++;
      const existingLi = Array.from(orderList.children)
        .find(li => li.dataset.item === item);
      existingLi.querySelector(".count").textContent = orderCounts[item];
    } else {
      // New entry
      orderCounts[item] = 1;
      const li = document.createElement("li");
      li.dataset.item = item;
      li.innerHTML =
        `<span class="count">1</span> x ` +
        `<span class="item-name">${item}</span> ` +
        `<span class="remove" title="Remove or decrement">×</span>`;
      orderList.appendChild(li);
  
      // Remove button handler
      li.querySelector(".remove").addEventListener("click", () => {
        removeItem(item);
      });
    }
    updateHiddenInput();
  }
  
  // Remove/decrement an item when the “×” is clicked
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
  
  // Update the hidden <input name="order"> with “count x item” comma‑separated
  function updateHiddenInput() {
    const parts = Object.entries(orderCounts).map(
      ([item, count]) => `${count} x ${item}`
    );
    hiddenOrderInput.value = parts.join(", ");
  }
  