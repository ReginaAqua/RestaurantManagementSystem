// htmlFiles/orders.js

// List of available menu items
const menuItems = [
    "Simple Pizza",
    "Special Pizza",
    "Pasta",
    "Carbonara",
    "Salad",
    "Greek Salad",
    "Trash"
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
    const query = searchInput.value.trim().toLowerCase();
    if (!query) {
      suggestionsDiv.style.display = "none";
      return;
    }
  
    const matches = menuItems.filter(item =>
      item.toLowerCase().includes(query)
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
      const existingLi = [...orderList.children]
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
      const existingLi = [...orderList.children]
        .find(li => li.dataset.item === item);
      existingLi.querySelector(".count").textContent = orderCounts[item];
    } else {
      delete orderCounts[item];
      const existingLi = [...orderList.children]
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
  